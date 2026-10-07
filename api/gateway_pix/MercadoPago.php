<?php
include '../erros_pagamento.php';
include '../erroAPI.php';

if (!isset($conexao)) {
    include __DIR__ . '/../../erro.php';
    exit;
}

$conexao->query("UPDATE relatorio_gateway SET visitas = visitas + 1 WHERE id = 1");

$stmtGateway = $conexao->prepare("SELECT token FROM gateway_pix WHERE nome = 'mercado_pago'");
$stmtGateway->execute();
$gateway_data = $stmtGateway->fetch(PDO::FETCH_ASSOC);

if (!$gateway_data || empty($gateway_data['token'])) {
    salvarErro("Gateway Pix: Credenciais Mercado Pago não encontradas.");
    echo json_encode(["sucesso" => false, "mensagem" => "Credenciais Mercado Pago não encontradas."], JSON_UNESCAPED_UNICODE);
    exit;
}

$access_token = $gateway_data['token'];

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

$valor_total = 0;
foreach ($carrinho as $item) {
    $preco = floatval(str_replace(',', '.', $item['preço_atual'] ?? 0));
    $quantidade = intval($item['quantidade'] ?? 1);
    $valor_total += ($preco * (1 - $desconto_pix / 100)) * $quantidade;
}

$valor_final_formatado = number_format($valor_total, 2, '.', '');
$documento_limpo = preg_replace('/[^0-9]/', '', $pagador['documento'] ?? $pagador['cpf'] ?? '69750637291');

$protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$webhook_url = $protocolo . "://" . $_SERVER['HTTP_HOST'] . "/Webhook/pix/MercadoPago";

$nome_completo = $pagador['nome'] ?? 'Cliente Novo';
$nome_partes = explode(' ', $nome_completo, 2);
$first_name = $nome_partes[0];
$last_name = $nome_partes[1] ?? 'da Silva';

$payload = [
    'transaction_amount' => round(floatval($valor_final_formatado), 2),
    'description' => substr($item_principal['origem'] ?? 'Reserva de Passagem', 0, 60),
    'payment_method_id' => 'pix',
    'external_reference' => (string)($data['fullid'] ?? uniqid()),
    'notification_url' => $webhook_url,
    'payer' => [
        'email' => $pagador['email'] ?? 'cliente@email.com',
        'first_name' => $first_name,
        'last_name' => $last_name,
        'identification' => [
            'type' => 'CPF',
            'number' => $documento_limpo
        ]
    ]
];

$ch = curl_init('https://api.mercadopago.com/v1/payments');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $access_token,
    'Content-Type: application/json',
    'X-Idempotency-Key: ' . uniqid()
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$pixData = json_decode($response, true);

if (!in_array($http_code, [200, 201]) || !isset($pixData['point_of_interaction']['transaction_data']['qr_code'])) {
    salvarErro("Gateway Pix: $response");
    echo json_encode(["sucesso" => false, "mensagem" => "Erro Mercado Pago.", "debug" => $response], JSON_UNESCAPED_UNICODE);
    exit;
}

$pixid = $pixData['id'];
$qrcode_text = $pixData['point_of_interaction']['transaction_data']['qr_code'];
$qr_base64 = 'data:image/png;base64,' . $pixData['point_of_interaction']['transaction_data']['qr_code_base64'];
$numero_pedido = str_pad(rand(0, 99999999), 8, "0", STR_PAD_LEFT);
$data_registro = date('Y-m-d H:i:s');

$stmt = $conexao->prepare("INSERT INTO infospix 
    (nome, documento, nascimento, telefone, email, pedido, passageiros, origem, destino, fullid, pixid, status, data, dispositivo, navegador, ip, valor, tipo, classe, gateway, data_de_ida, data_de_volta) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->execute([
    $nome_completo,
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
    'MercadoPago',
    $item_principal['data_de_ida'] ?? '',
    $item_principal['data_de_volta'] ?? ''
]);

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
    } catch (Throwable $e) {
        //error_log($e->getMessage()); Ignorar os erros do envio_zap.php
    }
}
exit;