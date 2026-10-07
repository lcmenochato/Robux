<?php
include '../erros_pagamento.php';
include '../erroAPI.php';

if (!isset($conexao)) {
    include __DIR__ . '/../../erro.php';
    exit;
}

$conexao->query("UPDATE relatorio_gateway SET visitas = visitas + 1 WHERE id = 1");

$stmtGateway = $conexao->prepare("SELECT anubis_secret, anubis_public FROM gateway_pix WHERE nome = 'anubis_pay'");
$stmtGateway->execute();
$gateway_data = $stmtGateway->fetch(PDO::FETCH_ASSOC);

if (!$gateway_data || empty($gateway_data['anubis_secret']) || empty($gateway_data['anubis_public'])) {
    salvarErro("Gateway Pix: Credenciais AnubisPay não encontradas.");
    echo json_encode(["sucesso" => false, "mensagem" => "Credenciais AnubisPay não encontradas."], JSON_UNESCAPED_UNICODE);
    exit;
}

$public_key = $gateway_data['anubis_public'];
$secret_key = $gateway_data['anubis_secret'];
$auth_token = base64_encode($public_key . ':' . $secret_key);

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

$valor_centavos = 0;
$items_array = [];

foreach ($carrinho as $item) {
    $preco = floatval(str_replace(',', '.', $item['preço_atual'] ?? 0));
    $quantidade = intval($item['quantidade'] ?? 1);
    $valor_com_desconto = $preco * (1 - $desconto_pix / 100);
    $unit_price_centavos = intval(round($valor_com_desconto * 100));

    $valor_centavos += ($unit_price_centavos * $quantidade);

    $items_array[] = [
        'title' => substr($item['ida'] ?? ($item['nome'] ?? 'Passagem Aérea'), 0, 100),
        'unit_price' => $unit_price_centavos,
        'quantity' => $quantidade,
        'tangible' => false
    ];
}

$documento_limpo = preg_replace('/[^0-9]/', '', $pagador['documento'] ?? $pagador['cpf'] ?? '');
$document_type = (strlen($documento_limpo) == 14) ? 'cnpj' : 'cpf';
$telefone = preg_replace('/[^0-9]/', '', $pagador['telefone'] ?? '');
$external_ref = (string)($item_principal['fullid'] ?? uniqid());

$protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host_atual = $_SERVER['HTTP_HOST'] ?? '';
$webhook_url = $protocolo . "://" . ($host_atual ?: 'localhost') . "/Webhook/pix/Anubispay";

$payload = [
    'amount' => $valor_centavos,
    'payment_method' => 'pix',
    'postback_url' => $webhook_url,
    'customer' => [
        'name' => $pagador['nome'] ?? 'Cliente',
        'email' => $pagador['email'] ?? '',
        'phone' => $telefone,
        'document' => [
            'number' => $documento_limpo,
            'type' => $document_type
        ]
    ],
    'items' => $items_array,
    'pix' => [
        'expiresInDays' => 1
    ],
    'metadata' => [
        'provider_name' => 'AnubisPay',
        'externalRef' => $external_ref
    ],
    'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
];

$ch = curl_init('https://api.anubispay.com/v1/payment-transaction/create');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'authorization: Basic ' . $auth_token,
    'accept: application/json',
    'content-type: application/json'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$pixData = json_decode($response, true);

if (!($pixData['success'] ?? false)) {
    salvarErro("Gateway Pix: Erro no gateway. $response");
    echo json_encode(["sucesso" => false, "mensagem" => "Erro ao gerar PIX AnubisPay.", "debug" => $response, "payload_enviado" => $payload], JSON_UNESCAPED_UNICODE);
    exit;
}

$pixid = $pixData['data']['id'] ?? '';
$qrcode_text = $pixData['data']['pix']['qr_code'] ?? '';

if (empty($pixid) || empty($qrcode_text)) {
    salvarErro("Gateway Pix: Resposta inesperada. $response");
    echo json_encode(["sucesso" => false, "mensagem" => "Resposta inesperada da AnubisPay.", "debug" => $response], JSON_UNESCAPED_UNICODE);
    exit;
}

$numero_pedido = str_pad(rand(0, 99999999), 8, "0", STR_PAD_LEFT);
$data_registro = date('Y-m-d H:i:s');
$valor_final_formatado = number_format($valor_centavos / 100, 2, '.', '');

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
    'AnubisPay',
    $item_principal['data_de_ida'] ?? '',
    $item_principal['data_de_volta'] ?? ''
]);

$qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qrcode_text);
$qr_base64 = 'data:image/png;base64,' . base64_encode(@file_get_contents($qr_code_url));

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
        "telefone" => $telefone,
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