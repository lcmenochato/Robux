<?php
include '../erros_pagamento.php';
include '../erroAPI.php';

if (!isset($conexao)) {
    include __DIR__ . '/../../erro.php';
    exit;
}

$conexao->query("UPDATE relatorio_gateway SET visitas = visitas + 1 WHERE id = 1");

$stmtGateway = $conexao->prepare("SELECT pay_shark_secret FROM gateway_pix WHERE nome = 'pay_shark' LIMIT 1");
$stmtGateway->execute();
$gateway_data = $stmtGateway->fetch(PDO::FETCH_ASSOC);

if (!$gateway_data || empty($gateway_data['pay_shark_secret'])) {
    salvarErro("Gateway Pix: Credenciais PayShark não encontradas.");
    echo json_encode(["sucesso" => false, "mensagem" => "Credenciais PayShark não encontradas."], JSON_UNESCAPED_UNICODE);
    exit;
}

$api_token = $gateway_data['pay_shark_secret'];

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

$items_array = [];
$valor_total_centavos = 0;

foreach ($carrinho as $item) {
    $preco = floatval(str_replace(',', '.', $item['preço_atual'] ?? 0));
    $quantidade = intval($item['quantidade'] ?? 1);
    $valor_com_desconto = $preco * (1 - $desconto_pix / 100);
    $unit_price_centavos = intval(round($valor_com_desconto * 100));

    $nome_item = substr($item['ida'] ?? ($item['nome'] ?? 'Passagem'), 0, 100);

    $items_array[] = [
        'quantity' => $quantidade,
        'name' => $nome_item,
        'price' => $unit_price_centavos,
        'type' => 'DIGITAL'
    ];

    $valor_total_centavos += $unit_price_centavos * $quantidade;
}

$valor_final_formatado = number_format($valor_total_centavos / 100, 2, '.', '');

$documento_limpo = preg_replace('/[^0-9]/', '', $pagador['documento'] ?? $pagador['cpf'] ?? '69750637291');

$protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host_atual = $_SERVER['HTTP_HOST'] ?? '';
$webhook_url = $protocolo . "://" . ($host_atual ?: 'localhost') . "/Webhook/pix/PayShark";

$fullid_principal = $item_principal['fullid'] ?? ($data['fullid'] ?? uniqid());

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
    'description' => $data['nome_da_loja'] ?? 'Venda PayShark',
    'externalRef' => $fullid_principal,
    'notificationUrl' => $webhook_url,
    'ip' => ip_valido($_SERVER['REMOTE_ADDR'] ?? ''),
    'payer' => [
        'name' => $pagador['nome'] ?? 'Cliente',
        'taxId' => $documento_limpo,
        'email' => $pagador['email'] ?? 'cliente@email.com',
        'phone' => preg_replace('/[^0-9]/', '', $pagador['telefone'] ?? '')
    ],
    'items' => $items_array
];

$ch = curl_init('https://api.gatewaypayshark.com.br/v1/payment');
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
    echo json_encode(["sucesso" => false, "mensagem" => "Erro ao gerar PIX PayShark.", "debug" => $response], JSON_UNESCAPED_UNICODE);
    exit;
}

$pixid = $pixData['id'] ?? '';
$qrcode_text = $pixData['data']['copypaste'] ?? '';

if (empty($pixid) || empty($qrcode_text)) {
    echo json_encode(["sucesso" => false, "mensagem" => "Resposta inesperada da PayShark.", "debug" => $response], JSON_UNESCAPED_UNICODE);
    exit;
}

$numero_pedido = str_pad(rand(0, 99999999), 8, "0", STR_PAD_LEFT);
$data_registro = date('Y-m-d H:i:s');

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
    $data_registro,
    $data['dispositivo'] ?? '',
    $_SERVER['HTTP_USER_AGENT'] ?? '',
    $_SERVER['REMOTE_ADDR'] ?? '',
    $valor_final_formatado,
    $item_principal['tipo'] ?? '',
    $item_principal['classe'] ?? '',
    'PayShark',
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