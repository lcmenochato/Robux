<?php
include '../erros_pagamento.php';
include '../erroAPI.php';

if (!isset($conexao)) {
    include __DIR__ . '/../../erro.php';
    exit;
}

$conexao->query("UPDATE relatorio_gateway SET visitas = visitas + 1 WHERE id = 1");

$stmtGateway = $conexao->prepare("SELECT cliente_public, cliente_privada FROM gateway_pix WHERE nome = 'sigilopay' LIMIT 1");
$stmtGateway->execute();
$gateway_data = $stmtGateway->fetch(PDO::FETCH_ASSOC);

if (!$gateway_data || empty($gateway_data['cliente_public']) || empty($gateway_data['cliente_privada'])) {
    salvarErro("Gateway Pix: Credenciais Sigilopay não encontradas.");
    echo json_encode(["sucesso" => false, "mensagem" => "Credenciais Sigilopay não encontradas."], JSON_UNESCAPED_UNICODE);
    exit;
}

$public_key = $gateway_data['cliente_public'];
$secret_key = $gateway_data['cliente_privada'];

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

$products_data = [];
$valor_total = 0;

foreach ($carrinho as $item) {
    $preco = floatval(str_replace(',', '.', $item['preço_atual'] ?? 0));
    $quantidade = intval($item['quantidade'] ?? 1);
    $valor_com_desconto = $preco * (1 - $desconto_pix / 100);
    $valor_total += $valor_com_desconto * $quantidade;

    $products_data[] = [
        'id' => $item['fullid'] ?? uniqid(),
        'name' => substr($item['ida'] ?? ($item['nome'] ?? 'Passagem'), 0, 100),
        'quantity' => $quantidade,
        'price' => round($valor_com_desconto, 2)
    ];
}

$documento_limpo = preg_replace('/[^0-9]/', '', $pagador['documento'] ?? $pagador['cpf'] ?? '69750637291');
$valor_final = round($valor_total, 2);
$valor_final_formatado = number_format($valor_final, 2, '.', '');

$protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$webhook_url = $protocolo . "://" . $_SERVER['HTTP_HOST'] . "/Webhook/pix/Sigilopay";

$payload = [
    'identifier' => uniqid('sigilo_'),
    'amount' => $valor_final,
    'client' => [
        'name' => $pagador['nome'] ?? 'Cliente',
        'email' => $pagador['email'] ?? 'cliente@email.com',
        'phone' => preg_replace('/[^0-9]/', '', $pagador['telefone'] ?? ''),
        'document' => $documento_limpo
    ],
    'products' => $products_data,
    'dueDate' => date('Y-m-d', strtotime('+1 day')),
    'callbackUrl' => $webhook_url
];

$ch = curl_init('https://app.sigilopay.com.br/api/v1/gateway/pix/receive');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'x-public-key: ' . $public_key,
        'x-secret-key: ' . $secret_key
    ],
    CURLOPT_SSL_VERIFYPEER => false
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$responseData = json_decode($response, true);

if (!in_array($http_code, [200, 201]) || !isset($responseData['pix']['code'])) {
    salvarErro("Gateway Pix: $response");
    echo json_encode(["sucesso" => false, "mensagem" => "Erro na SigiloPay.", "debug" => $response], JSON_UNESCAPED_UNICODE);
    exit;
}

$pixid = $responseData['transactionId'] ?? uniqid();
$qrcode_text = $responseData['pix']['code'];
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
    'Sigilopay',
    $item_principal['data_de_ida'] ?? '',
    $item_principal['data_de_volta'] ?? ''
]);

$qr_base64_raw = $responseData['pix']['base64'] ?? null;

if (!empty($qr_base64_raw)) {
    $qr_base64 = str_starts_with($qr_base64_raw, 'data:image')
        ? $qr_base64_raw
        : 'data:image/png;base64,' . $qr_base64_raw;
} else {
    $qr_base64 = 'data:image/png;base64,' . base64_encode(
        @file_get_contents(
            "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qrcode_text)
        )
    );
}

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