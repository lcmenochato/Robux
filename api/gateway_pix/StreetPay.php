<?php
include '../erros_pagamento.php';
include '../erroAPI.php';

if (!isset($conexao)) {
    include __DIR__ . '/../../erro.php';
    exit;
}

$conexao->query("UPDATE relatorio_gateway SET visitas = visitas + 1 WHERE id = 1");

$stmtGateway = $conexao->prepare("SELECT street_secret FROM gateway_pix WHERE nome = 'street_pay'");
$stmtGateway->execute();
$gateway_data = $stmtGateway->fetch(PDO::FETCH_ASSOC);

if (!$gateway_data || empty($gateway_data['street_secret'])) {
    salvarErro("Gateway Pix: Credenciais StreetPay não encontradas.");
    echo json_encode(["sucesso" => false, "mensagem" => "Credenciais StreetPay não encontradas."], JSON_UNESCAPED_UNICODE);
    exit;
}

$api_token = $gateway_data['street_secret'];

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

$valor_total_centavos = 0;
$items_payload = [];
$nomes_produtos = [];

foreach ($carrinho as $item) {
    $preco = floatval(str_replace(',', '.', $item['preço_atual'] ?? 0));
    $quantidade = intval($item['quantidade'] ?? 1);
    $valor_com_desconto = $preco * (1 - $desconto_pix / 100);
    $preco_centavos = intval(round($valor_com_desconto * 100));

    $valor_total_centavos += ($preco_centavos * $quantidade);

    $nome_item = $item['ida'] ?? ($item['nome'] ?? 'Passagem');
    $nomes_produtos[] = $nome_item;

    $items_payload[] = [
        'quantity' => $quantidade,
        'name' => substr($nome_item, 0, 100),
        'price' => $preco_centavos,
        'type' => 'DIGITAL'
    ];
}

$documento_limpo = preg_replace('/[^0-9]/', '', $pagador['documento'] ?? $pagador['cpf'] ?? '');

$protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host_atual = $_SERVER['HTTP_HOST'] ?? '';
$webhook_url = $protocolo . "://" . ($host_atual ?: 'localhost') . "/Webhook/pix/StreetPay";

function ip_valido($ip) {
    $ip = trim($ip ?? '');
    if ($ip === '::1' || $ip === '' || !filter_var($ip, FILTER_VALIDATE_IP)) {
        return '127.0.0.1';
    }
    return $ip;
}

$payload = [
    'amount' => $valor_total_centavos,
    'currency' => 'BRL',
    'method' => 'PIX',
    'description' => implode(', ', $nomes_produtos),
    'externalRef' => (string)($item_principal['fullid'] ?? uniqid()),
    'notificationUrl' => $webhook_url,
    'ip' => ip_valido($_SERVER['REMOTE_ADDR'] ?? ''),
    'payer' => [
        'name' => $pagador['nome'] ?? 'Cliente',
        'taxId' => $documento_limpo,
        'email' => $pagador['email'] ?? 'email@provedor.com',
        'phone' => preg_replace('/[^0-9]/', '', $pagador['telefone'] ?? '')
    ],
    'items' => $items_payload
];

$ch = curl_init('https://api.streetpays.com.br/v1/payment');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $api_token,
    'accept: application/json',
    'content-type: application/json'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$pixData = json_decode($response, true);

if (!in_array($http_code, [200, 201]) || empty($pixData['id'])) {
    salvarErro("Gateway Pix: $response");
    echo json_encode(["sucesso" => false, "mensagem" => "Erro ao gerar PIX StreetPay.", "debug" => $response, "payload_enviado" => $payload], JSON_UNESCAPED_UNICODE);
    exit;
}

$pixid = $pixData['id'] ?? '';
$qrcode_text = $pixData['data']['copypaste'] ?? '';

if (empty($pixid) || empty($qrcode_text)) {
    salvarErro("Gateway Pix: $response");
    echo json_encode(["sucesso" => false, "mensagem" => "Resposta inesperada da StreetPay.", "debug" => $response], JSON_UNESCAPED_UNICODE);
    exit;
}

$numero_pedido = str_pad(rand(0, 99999999), 8, "0", STR_PAD_LEFT);
$valor_final_formatado = number_format($valor_total_centavos / 100, 2, '.', '');

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
    'StreetPay',
    $item_principal['data_de_ida'] ?? '',
    $item_principal['data_de_volta'] ?? ''
]);

$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qrcode_text);
$qr_base64 = 'data:image/png;base64,' . base64_encode(@file_get_contents($qr_url));

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