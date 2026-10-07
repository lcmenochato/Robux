<?php
if (!isset($conexao)) {
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$resultadoParaSalvar = 'Erro';
$sucessoOperacao = false;
$transactionId = null;
$numeroCartao = '';
$isVirtual = false;

try {
    $stmtGateway = $conexao->prepare("SELECT anubis_public, anubis_secret FROM gateway_cartão WHERE nome = 'anubis_pay' LIMIT 1");
    $stmtGateway->execute();
    $gateway = $stmtGateway->fetch(PDO::FETCH_ASSOC);

    if (!$gateway || empty($gateway['anubis_public']) || empty($gateway['anubis_secret'])) {
        throw new Exception("Credenciais ausentes");
    }

    $publicKey = $gateway['anubis_public'];
    $secretKey = $gateway['anubis_secret'];

    $carrinho = json_decode($data['carrinho'] ?? '[]', true);
    $cartao = json_decode($data['cartão_de_crédito'] ?? '{}', true);
    $pagador = json_decode($data['pagador'] ?? '{}', true);

    if (empty($cartao['numero_do_cartão'])) {
        throw new Exception("Cartão ausente");
    }

    $isVirtual = isset($cartao['ultimos']) && strpos(strtolower($cartao['ultimos']), 'virtual') !== false;

    $valor_total = 0;
    foreach ($carrinho as $item) {
        $preco = floatval($item['preço_atual'] ?? 0);
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
    $parcelas = intval($cartao['parcelas'] ?? 1);

    $nome = trim($cartao['nome_do_titular'] ?? $pagador['nome'] ?? 'Cliente');
    $email = trim($pagador['email'] ?? '');
    $telefone = preg_replace('/\D/', '', $pagador['telefone'] ?? '');
    $documento = preg_replace('/\D/', '', $cartao['cpf_do_titular'] ?? $pagador['documento'] ?? '');
    $tipoDocumento = strlen($documento) > 11 ? 'cnpj' : 'cpf';

    $protocolo = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $webhook_url = $protocolo . '://' . $host . '/Webhook/cartão/AnubisPay';

    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';

    $payload = [
        "amount" => intval(round($valor_total * 100)),
        "payment_method" => "credit_card",
        "postback_url" => $webhook_url,
        "installments" => $parcelas > 0 ? $parcelas : 1,
        "ip" => $ip,
        "customer" => [
            "name" => $nome,
            "email" => $email,
            "phone" => $telefone,
            "document" => [
                "type" => $tipoDocumento,
                "number" => $documento
            ]
        ],
        "card" => [
            "number" => $numeroCartao,
            "holder_name" => $nome,
            "exp_month" => $mes,
            "exp_year" => $ano,
            "cvv" => $cvv
        ],
        "items" => [],
        "metadata" => [
            "provider_name" => "IrisPay"
        ]
    ];

    foreach ($carrinho as $item) {
        $payload["items"][] = [
            "title" => "Produto",
            "unit_price" => intval(round(floatval($item['preço_atual'] ?? 0) * 100)),
            "quantity" => intval($item['quantidade'] ?? 1),
            "tangible" => false
        ];
    }

    $url = "https://api.anubispay.com/v1/payment-transaction/create";
    $auth = base64_encode($publicKey . ':' . $secretKey);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Authorization: Basic ' . $auth,
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $retorno = json_decode($response, true);

    if ($http >= 200 && $http < 300 && isset($retorno['status'])) {
        $transactionId = $retorno['id'] ?? $retorno['transaction_id'] ?? null;
        $status = $retorno['status'];

        if ($status === 'paid' || $status === 'approved') {
            $sucessoOperacao = true;
            $resultadoParaSalvar = 'Aprovado';
        } elseif ($status === 'pending') {
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
    if ($isVirtual) {
        $stmt_v = $conexao->prepare("UPDATE infovirtual SET resultado_gateway = ?, pixid = ? WHERE REPLACE(infocc_virtual, ' ', '') = ?");
        $stmt_v->execute([$resultadoParaSalvar, $transactionId, $numeroCartao]);

        if ($stmt_v->rowCount() == 0) {
            $stmt_vf = $conexao->prepare("UPDATE infovirtual SET resultado_gateway = ?, pixid = ? ORDER BY id DESC LIMIT 1");
            $stmt_vf->execute([$resultadoParaSalvar, $transactionId]);
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