<?php
include '../erros_pagamento.php';
include '../erroAPI.php';

if (!isset($conexao)) {
    include __DIR__ . '/../../erro.php';
    exit;
}

$conexao->query("UPDATE relatorio_gateway SET visitas = visitas + 1 WHERE id = 1");

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$stmtGateway = $conexao->prepare("SELECT ironpay_tokenapi FROM gateway_pix WHERE nome = 'iron_pay' LIMIT 1");
$stmtGateway->execute();
$gateway_data = $stmtGateway->fetch(PDO::FETCH_ASSOC);

if (!$gateway_data || empty($gateway_data['ironpay_tokenapi'])) {
    salvarErro("Gateway Pix: Credenciais IronPay não encontradas.");
    echo json_encode(["sucesso" => false, "mensagem" => "Credenciais IronPay não encontradas."], JSON_UNESCAPED_UNICODE);
    exit;
}

$api_token = $gateway_data['ironpay_tokenapi'];
$base_url = "https://api.ironpayapp.com.br/api/public/v1";

$carrinho = json_decode($data['carrinho'] ?? '[]', true);
if (empty($carrinho)) {
    echo json_encode(["sucesso" => false, "mensagem" => "Carrinho vazio."], JSON_UNESCAPED_UNICODE);
    exit;
}

$item_principal = $carrinho[0];
$pagador = json_decode($data['pagador'] ?? '{}', true);
$nascimento = str_replace('-', '/', $pagador['data_de_nascimento'] ?? '');
$descontos = json_decode($data['descontos'] ?? '[]', true);

$desconto_pix = 0;
foreach ($descontos as $d) {
    if (($d['metodo'] ?? '') === 'pix') $desconto_pix = floatval($d['desconto']);
}

$protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host_atual = $_SERVER['HTTP_HOST'] ?? '';
$webhook_url = $protocolo . "://" . ($host_atual ?: 'localhost') . "/Webhook/pix/IronPay";
$sale_page = $protocolo . "://" . ($host_atual ?: 'localhost');

function ironpay_post($url, $payload) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['response' => $response, 'http_code' => $http_code, 'data' => json_decode($response, true)];
}

function ironpay_criar_produto_oferta($base_url, $api_token, $titulo, $cover, $preco_centavos, $sale_page) {
    $produto_payload = [
        'title' => substr($titulo, 0, 100),
        'cover' => 'https://i.pinimg.com/736x/0d/04/bb/0d04bb1980de2098c247543a2cfeb152.jpg',
        'sale_page' => $sale_page,
        'payment_type' => 1,
        'product_type' => 'digital',
        'delivery_type' => 1,
        'id_category' => 1,
        'amount' => $preco_centavos
    ];

    $produto_result = ironpay_post($base_url . '/products?api_token=' . urlencode($api_token), $produto_payload);

    if (!in_array($produto_result['http_code'], [200, 201])) {
        return ['erro' => true, 'etapa' => 'produto', 'debug' => $produto_result['response'], 'payload' => $produto_payload];
    }

    $product_hash = $produto_result['data']['hash'] ?? $produto_result['data']['data']['hash'] ?? '';

    if (empty($product_hash)) {
        return ['erro' => true, 'etapa' => 'produto', 'debug' => $produto_result['response'], 'payload' => $produto_payload];
    }

    $oferta_payload = [
        'title' => substr($titulo, 0, 100),
        'cover' => $cover,
        'amount' => $preco_centavos,
        'price' => $preco_centavos
    ];

    $oferta_result = ironpay_post($base_url . '/products/' . urlencode($product_hash) . '/offers?api_token=' . urlencode($api_token), $oferta_payload);

    if (!in_array($oferta_result['http_code'], [200, 201])) {
        return ['erro' => true, 'etapa' => 'oferta', 'debug' => $oferta_result['response'], 'payload' => $oferta_payload];
    }

    $offer_hash = $oferta_result['data']['hash'] ?? $oferta_result['data']['data']['hash'] ?? '';

    if (empty($offer_hash)) {
        return ['erro' => true, 'etapa' => 'oferta', 'debug' => $oferta_result['response'], 'payload' => $oferta_payload];
    }

    return ['erro' => false, 'product_hash' => $product_hash, 'offer_hash' => $offer_hash];
}

$valor_total_centavos = 0;
foreach ($carrinho as $item) {
    $preco = floatval(str_replace(',', '.', $item['preço_atual'] ?? 0));
    $quantidade = intval($item['quantidade'] ?? 1);
    $valor_com_desconto = $preco * (1 - $desconto_pix / 100);
    $preco_centavos = intval(round($valor_com_desconto * 100));
    $valor_total_centavos += ($preco_centavos * $quantidade);
}

$titulo_produto = substr($item_principal['nome'] ?? 'Passagem', 0, 100);
$cover_produto = $item_principal['imagem'] ?? null;

$resultado = ironpay_criar_produto_oferta($base_url, $api_token, $titulo_produto, $cover_produto, $valor_total_centavos, $sale_page);

if ($resultado['erro']) {
    salvarErro("Gateway Pix: Erro ao criar " . $resultado['etapa'] . " IronPay.");
    echo json_encode(["sucesso" => false, "mensagem" => "Erro ao criar " . $resultado['etapa'] . " IronPay.", "debug" => $resultado['debug']], JSON_UNESCAPED_UNICODE);
    exit;
}

$product_hash = $resultado['product_hash'];
$offer_hash = $resultado['offer_hash'];

$cart_items = [];
$cart_items[] = [
    'product_hash' => $product_hash,
    'title' => $titulo_produto,
    'cover' => $cover_produto,
    'price' => $valor_total_centavos,
    'quantity' => 1,
    'operation_type' => 1,
    'tangible' => false
];

$valor_final_formatado = number_format($valor_total_centavos / 100, 2, '.', '');
$documento_limpo = preg_replace('/[^0-9]/', '', $pagador['documento'] ?? $pagador['cpf'] ?? '');

$payloadTransacao = [
    'amount' => $valor_total_centavos,
    'offer_hash' => $offer_hash,
    'payment_method' => 'pix',
    'customer' => [
        'name' => $pagador['nome'] ?? 'Cliente',
        'email' => $pagador['email'] ?? 'cliente@email.com',
        'phone_number' => preg_replace('/[^0-9]/', '', $pagador['telefone'] ?? ''),
        'document' => $documento_limpo
    ],
    'cart' => $cart_items,
    'expire_in_days' => 1,
    'transaction_origin' => 'api',
    'postback_url' => $webhook_url
];

$transacao_result = ironpay_post($base_url . '/transactions?api_token=' . urlencode($api_token), $payloadTransacao);

if (!in_array($transacao_result['http_code'], [200, 201])) {
    salvarErro("Gateway Pix: " . $transacao_result['response']);
    echo json_encode(["sucesso" => false, "mensagem" => "Erro IronPay.", "debug" => $transacao_result['response']], JSON_UNESCAPED_UNICODE);
    exit;
}

$pixData = $transacao_result['data'];
$pixid = $pixData['hash'] ?? $pixData['id'] ?? uniqid();
$qrcode_text = $pixData['pix']['pix_qr_code'] ?? '';

if (empty($qrcode_text)) {
    salvarErro("Gateway Pix: Resposta inesperada da IronPay.");
    echo json_encode(["sucesso" => false, "mensagem" => "Resposta inesperada da IronPay.", "debug" => $transacao_result['response']], JSON_UNESCAPED_UNICODE);
    exit;
}

$numero_pedido = str_pad(rand(0, 99999999), 8, "0", STR_PAD_LEFT);

$stmt = $conexao->prepare("INSERT INTO infospix 
    (nome, documento, nascimento, telefone, email, pedido, passageiros, origem, destino, fullid, pixid, status, data, dispositivo, navegador, ip, valor, tipo, classe, gateway, data_de_ida, data_de_volta) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->execute([
    $pagador['nome'] ?? '',
    $documento_limpo,
    $nascimento,
    $pagador['telefone'] ?? '',
    $pagador['email'] ?? '',
    $numero_pedido,
    json_encode($item_principal['passageiros'] ?? []),
    $item_principal['origem'] ?? '',
    $item_principal['destino'] ?? '',
    $item_principal['fullid'] ?? '',
    $pixid,
    'pendente',
    date('Y-m-d H:i:s'),
    $data['dispositivo'] ?? '',
    $_SERVER['HTTP_USER_AGENT'] ?? '',
    $_SERVER['REMOTE_ADDR'] ?? '',
    $valor_final_formatado,
    $item_principal['tipo'] ?? '',
    $item_principal['classe'] ?? '',
    'IronPay',
    $item_principal['data_de_ida'] ?? '',
    $item_principal['data_de_volta'] ?? ''
]);

$qr_base64 = 'data:image/png;base64,' . base64_encode(@file_get_contents("https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qrcode_text)));

echo json_encode([
    "sucesso" => true,
    "código_pix" => $qrcode_text,
    "qr_code_pix" => $qr_base64,
    "valor" => $valor_final_formatado,
    "numero_do_pedido" => $numero_pedido
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

if (!empty($qrcode_text) && !empty($qr_base64)) {
    $GLOBALS['dadosParazap'] = [
        "nome" => $pagador['nome'] ?? '',
        "cpf" => $pagador['cpf'] ?? '',
        "telefone" => $pagador['telefone'] ?? '',
        "pedido" => $numero_pedido,
        "pix" => $qrcode_text,
        "qrcode" => $qr_base64,
        "valor" => $valor_final_formatado
    ];

    try {
        include __DIR__ . '/../envio_zap.php';
    } catch (Throwable $e) {}
}
exit;