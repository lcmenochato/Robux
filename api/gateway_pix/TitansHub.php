<?php
include '../erros_pagamento.php';
include '../erroAPI.php';

if (!isset($conexao)) {
    include __DIR__ . '/../../erro.php';
    exit;
}

$conexao->query("UPDATE relatorio_gateway SET visitas = visitas + 1 WHERE id = 1");

$stmtGateway = $conexao->prepare("SELECT titans_hub_secret, titans_hub_public FROM gateway_pix WHERE nome = 'titans_hub' LIMIT 1");
$stmtGateway->execute();
$gateway_data = $stmtGateway->fetch(PDO::FETCH_ASSOC);

if (!$gateway_data || empty($gateway_data['titans_hub_secret']) || empty($gateway_data['titans_hub_public'])) {
    salvarErro("Gateway Pix: Credenciais TitansHub não encontradas.");
    echo json_encode(["sucesso" => false, "mensagem" => "Credenciais TitansHub não encontradas."], JSON_UNESCAPED_UNICODE);
    exit;
}

$secret_key = $gateway_data['titans_hub_secret'];
$public_key = $gateway_data['titans_hub_public'];

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

foreach ($carrinho as $item) {
    $preco = floatval(str_replace(',', '.', $item['preço_atual'] ?? 0));
    $quantidade = intval($item['quantidade'] ?? 1);
    $valor_com_desconto = $preco * (1 - $desconto_pix / 100);
    $preco_centavos = intval(round($valor_com_desconto * 100));
    
    $valor_total_centavos += ($preco_centavos * $quantidade);

    $items_payload[] = [
        'title' => substr($item['ida'] ?? ($item['nome'] ?? 'Passagem'), 0, 100),
        'quantity' => $quantidade,
        'unitPrice' => $preco_centavos
    ];
}

$documento_limpo = preg_replace('/[^0-9]/', '', $pagador['documento'] ?? $pagador['cpf'] ?? '69750637291');
$document_type = (strlen($documento_limpo) == 14) ? 'cnpj' : 'cpf';

$protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$webhook_url = $protocolo . "://" . $_SERVER['HTTP_HOST'] . "/Webhook/pix/TitansHub";

$payload = [
    'amount' => $valor_total_centavos,
    'currency' => 'BRL',
    'paymentMethod' => 'pix',
    'items' => $items_payload,
    'customer' => [
        'name' => $pagador['nome'] ?? 'Cliente',
        'document' => [
            'type' => $document_type,
            'number' => $documento_limpo
        ],
        'email' => $pagador['email'] ?? 'cliente@email.com',
        'phone' => preg_replace('/[^0-9]/', '', $pagador['telefone'] ?? '')
    ],
    'postbackUrl' => $webhook_url,
    'externalRef' => (string)($item_principal['fullid'] ?? uniqid()),
    'metadata' => $data['descricao'] ?? 'Pagamento Passagem TitansHub'
];

$auth = 'Basic ' . base64_encode($public_key . ':' . $secret_key);
$ch = curl_init('https://api.titanshub.io/v1/transactions'); 
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
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
    salvarErro("Gateway Pix: $response");
    echo json_encode(["sucesso" => false, "mensagem" => "Erro TitansHub.", "debug" => $response], JSON_UNESCAPED_UNICODE);
    exit;
}

$pixid = $pixData['id'];
$qrcode_text = $pixData['pix']['qrcode'];
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
    'TitansHub',
    $item_principal['data_de_ida'] ?? '',
    $item_principal['data_de_volta'] ?? ''
]);

$qr_base64 = $pixData['pix']['base64'] ?? ('data:image/png;base64,' . base64_encode(@file_get_contents("https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qrcode_text))));

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