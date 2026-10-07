<?php
include '../erros_pagamento.php';
include '../erroAPI.php';
// refazer php depois 
               
if (!isset($conexao)) {
    include __DIR__ . '/../../erro.php';
    exit;
}

$conexao->query("UPDATE relatorio_gateway SET visitas = visitas + 1 WHERE id = 1");

$public_key = 'HrYs7jEPQsvXekD';
$secret_key = 'SHJZczdqRVBRc3ZYZWtEOjoxNzY1Nzg2ODI3';

$auth_header = 'Basic ' . base64_encode($public_key . ':' . $secret_key);
$ch_auth = curl_init('https://api.tryplopay.com/auth');
curl_setopt_array($ch_auth, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => [
        'scope: invoice.write, customer.write, webhook.write', 
        'Authorization: ' . $auth_header
    ]
]);
$auth_response = curl_exec($ch_auth);
$http_code_auth = curl_getinfo($ch_auth, CURLINFO_HTTP_CODE);
curl_close($ch_auth);

$auth_data = json_decode($auth_response, true);
if ($http_code_auth !== 200 || !isset($auth_data['access_token'])) {
    echo json_encode(["sucesso" => false, "mensagem" => "Erro na autenticação TryploPay.", "debug" => $auth_data], JSON_UNESCAPED_UNICODE);
    exit;
}

$access_token = $auth_data['access_token'];

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
$produtos_array = [];
foreach ($carrinho as $item) {
    $preco = floatval(str_replace(',', '.', $item['preço_atual'] ?? 0));
    $quantidade = intval($item['quantidade'] ?? 1);
    $valor_com_desconto = $preco * (1 - $desconto_pix / 100);
    $valor_total += $valor_com_desconto * $quantidade;
    
    $produtos_array[] = [
        'id' => (string)($item['id'] ?? rand(1, 999)),
        'title' => substr($item['ida'] ?? ($item['nome'] ?? 'Passagem'), 0, 100),
        'qnt' => $quantidade,
        'discount' => 0,
        'amount' => round($valor_com_desconto, 2)
    ];
}

$documento_limpo = preg_replace('/[^0-9]/', '', $pagador['documento'] ?? $pagador['cpf'] ?? '69750637291');
$referrer_id = 'TP' . uniqid();
$protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$webhook_url = "{$protocolo}://{$host}/Webhook/pix/TryploPay";

$payload = [
    'client' => [
        'name' => $pagador['nome'] ?? 'Cliente',
        'phone' => preg_replace('/[^0-9]/', '', $pagador['telefone'] ?? '11999999999'),
        'document' => $documento_limpo,
        'email' => $pagador['email'] ?? 'cliente@email.com',
        'address' => [
            'street' => 'Praça da Sé',
            'number' => '123',
            'district' => 'Sé',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zipcode' => '01001000',
            'country' => 'BRA'
        ],
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
    ],
    'ecommerce' => [
        'store_id' => 0,
        'checkout' => 0
    ],
    'payment' => [
        'product_type' => 2,
        'id' => $referrer_id,
        'type' => 3,
        'due_at' => date('Y-m-d', strtotime('+1 day')),
        'referrer' => $referrer_id,
        'installments' => 1,
        'order_url' => "{$protocolo}://{$host}",
        'store_url' => "{$protocolo}://{$host}",
        'webhook' => $webhook_url,
        'discount' => 0,
        'products' => $produtos_array
    ],
    'shipping' => ['amount' => 0]
];

$ch = curl_init('https://api.tryplopay.com/invoices');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $access_token, 
        'Content-Type: application/json'
    ]
]);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$pixData = json_decode($response, true);
if (!in_array($http_code, [200, 201]) || !isset($pixData['invoice']['pix']['payload'])) {
    echo json_encode(["sucesso" => false, "mensagem" => "Erro ao gerar cobrança TryploPay.", "debug" => $pixData], JSON_UNESCAPED_UNICODE);
    exit;
}

$pixid = $pixData['invoice']['id'];
$qrcode_text = $pixData['invoice']['pix']['payload'];
$valor_final_formatado = number_format($valor_total, 2, '.', '');
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
    'TryploPay',
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
    } catch (Throwable $e) {
        //error_log($e->getMessage()); Ignorar os erros do envio_zap.php
    }
}
exit;