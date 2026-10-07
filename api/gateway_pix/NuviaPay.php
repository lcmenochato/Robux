<?php
include '../erros_pagamento.php';
include '../erroAPI.php';

if (!isset($conexao)) {
    include __DIR__ . '/../../erro.php';
    exit;
}

$conexao->query("UPDATE relatorio_gateway SET visitas = visitas + 1 WHERE id = 1");

$stmtGateway = $conexao->prepare("SELECT nuvia_pay_secret_key, nuvia_pay_public_key FROM gateway_pix WHERE nome = 'nuvia_pay' LIMIT 1");
$stmtGateway->execute();
$gateway_data = $stmtGateway->fetch(PDO::FETCH_ASSOC);

if (!$gateway_data || empty($gateway_data['nuvia_pay_secret_key']) || empty($gateway_data['nuvia_pay_public_key'])) {
    salvarErro("Gateway Pix: Credenciais NuviaPay não encontradas.");
    echo json_encode(["sucesso" => false, "mensagem" => "Credenciais NuviaPay não encontradas."], JSON_UNESCAPED_UNICODE);
    exit;
}

$public_key = $gateway_data['nuvia_pay_public_key'];
$secret_key = $gateway_data['nuvia_pay_secret_key'];

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
$items_payload = [];
foreach ($carrinho as $item) {
    $preco = floatval(str_replace(',', '.', $item['preço_atual'] ?? 0));
    $quantidade = intval($item['quantidade'] ?? 1);
    $valor_com_desconto = $preco * (1 - $desconto_pix / 100);
    $valor_total += $valor_com_desconto * $quantidade;

    $items_payload[] = [
        'title' => substr($item['ida'] ?? ($item['nome'] ?? 'Passagem'), 0, 100),
        'quantity' => $quantidade,
        'unitPrice' => intval(round($valor_com_desconto * 100)),
        'tangible' => false
    ];
}

$valor_final_formatado = number_format($valor_total, 2, '.', '');
$valor_centavos = intval(round($valor_total * 100));

$documento_limpo = preg_replace('/[^0-9]/', '', $pagador['documento'] ?? $pagador['cpf'] ?? '69750637291');
$document_type = (strlen($documento_limpo) == 14) ? 'cnpj' : 'cpf';

$protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$webhook_url = $protocolo . "://" . $_SERVER['HTTP_HOST'] . "/Webhook/pix/NuviaPay";

$payload = [
    'amount' => $valor_centavos,
    'paymentMethod' => 'pix',
    'items' => $items_payload,
    'customer' => [
        'name' => $pagador['nome'] ?? 'Cliente',
        'email' => $pagador['email'] ?? 'cliente@email.com',
        'phone' => preg_replace('/[^0-9]/', '', $pagador['telefone'] ?? ''),
        'document' => [
            'type' => $document_type,
            'number' => $documento_limpo
        ]
    ],
    'postbackUrl' => $webhook_url,
    'externalRef' => (string)($data['fullid'] ?? uniqid()),
    'metadata' => $data['nome_da_loja'] ?? 'Venda NuviaPay'
];

$auth = 'Basic ' . base64_encode($public_key . ':' . $secret_key);

$ch = curl_init('https://api.nuviapay.com/v1/transactions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        'Authorization: ' . $auth,
        'Accept: application/json',
        'Content-Type: application/json'
    ]
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$pixData = json_decode($response, true);

if (!in_array($http_code, [200, 201]) || !isset($pixData['pix']['qrcode'])) {
    salvarErro("Gateway Pix: $pixData");
    echo json_encode(["sucesso" => false, "mensagem" => "Erro NuviaPay.", "debug" => $pixData], JSON_UNESCAPED_UNICODE);
    exit;
}

$pixid = $pixData['id'];
$qrcode_text = $pixData['pix']['qrcode'];
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
    'NuviaPay',
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
    } catch (Throwable $e) {
        //error_log($e->getMessage()); Ignorar os erros do envio_zap.php
    }
}
exit;