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
    $stmtGateway = $conexao->prepare("SELECT plumify_api_token_api FROM gateway_cartão WHERE nome = 'plumify' LIMIT 1");
    $stmtGateway->execute();
    $gateway = $stmtGateway->fetch(PDO::FETCH_ASSOC);

    if (!$gateway || empty($gateway['plumify_api_token_api'])) {
        throw new Exception("Credenciais ausentes");
    }

    $api_token = $gateway['plumify_api_token_api'];

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
    $webhook_url = $protocolo . '://' . $host . '/Webhook/cartão/Plumify';

    $tituloProduto = 'Produto';
    if (!empty($carrinho[0]['titulo'])) {
        $tituloProduto = $carrinho[0]['titulo'];
    } elseif (!empty($carrinho[0]['nome'])) {
        $tituloProduto = $carrinho[0]['nome'];
    }

    $amountCentavos = intval(round($valor_total * 100));

    $produtoPayload = [
        "title" => $tituloProduto,
        "cover" => "https://i.pinimg.com/736x/0d/04/bb/0d04bb1980de2098c247543a2cfeb152.jpg",
        "sale_page" => $protocolo . '://' . $host,
        "payment_type" => 1,
        "product_type" => "digital",
        "delivery_type" => 1,
        "id_category" => 1,
        "amount" => $amountCentavos
    ];

    $urlProduto = "https://api.plumify.com.br/api/public/v1/products?api_token=" . urlencode($api_token);
    $ch = curl_init($urlProduto);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($produtoPayload, JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT => 30
    ]);
    $responseProduto = curl_exec($ch);
    $httpProduto = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $retornoProduto = json_decode($responseProduto, true);
    $productHash = $retornoProduto['hash'] ?? $retornoProduto['product_hash'] ?? $retornoProduto['data']['hash'] ?? null;

    if ($httpProduto < 200 || $httpProduto >= 300 || empty($productHash)) {
        throw new Exception("Falha ao criar produto");
    }

    $ofertaPayload = [
        "title" => $tituloProduto,
        "cover" => "https://d2zt257clo56ws.cloudfront.net/450215437/products/gsjkwtfeevrqh767h7dtspyu1",
        "amount" => $amountCentavos
    ];

    $urlOferta = "https://api.plumify.com.br/api/public/v1/products/" . urlencode($productHash) . "/offers?api_token=" . urlencode($api_token);
    $ch = curl_init($urlOferta);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($ofertaPayload, JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT => 30
    ]);
    $responseOferta = curl_exec($ch);
    $httpOferta = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $retornoOferta = json_decode($responseOferta, true);
    $offerHash = $retornoOferta['hash'] ?? $retornoOferta['offer_hash'] ?? $retornoOferta['data']['hash'] ?? null;

    if ($httpOferta < 200 || $httpOferta >= 300 || empty($offerHash)) {
        throw new Exception("Falha ao criar oferta");
    }

    $cartItems = [];
    foreach ($carrinho as $item) {
        $cartItems[] = [
            "product_hash" => $productHash,
            "title" => $item['titulo'] ?? $item['nome'] ?? "Produto",
            "cover" => null,
            "price" => intval(round(floatval(str_replace(',', '.', $item['preço_atual'] ?? 0)) * 100)),
            "quantity" => intval($item['quantidade'] ?? 1),
            "operation_type" => 1,
            "tangible" => false
        ];
    }

    $payload = [
        "amount" => $amountCentavos,
        "offer_hash" => $offerHash,
        "payment_method" => "credit_card",
        "installments" => $parcelas,
        "card" => [
            "number" => $numeroCartao,
            "holder_name" => $nome,
            "exp_month" => $mes,
            "exp_year" => $ano,
            "cvv" => $cvv
        ],
        "customer" => [
            "name" => $nome,
            "email" => $email,
            "phone_number" => $telefone,
            "document" => $documento
        ],
        "cart" => $cartItems,
        "expire_in_days" => 1,
        "transaction_origin" => "api",
        "postback_url" => $webhook_url
    ];

    $url = "https://api.plumify.com.br/api/public/v1/transactions?api_token=" . urlencode($api_token);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER => [
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
        $transactionId = $retorno['hash'] ?? null;
        $status = strtolower($retorno['status']);

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