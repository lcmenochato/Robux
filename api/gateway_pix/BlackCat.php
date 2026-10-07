<?php
include '../erros_pagamento.php';
include '../erroAPI.php';

if (!isset($conexao)) {
    include __DIR__ . '/../../erro.php';
    exit;
}

$conexao->query("UPDATE relatorio_gateway SET visitas = visitas + 1 WHERE id = 1");

$stmtGateway = $conexao->prepare("SELECT blackcat_secret, blackcat_public FROM gateway_pix WHERE nome = 'blackcatpagamentos.online'");
$stmtGateway->execute();
$gateway_data = $stmtGateway->fetch(PDO::FETCH_ASSOC);

if (!$gateway_data || empty($gateway_data['blackcat_secret'])) {
    salvarErro("Gateway Pix: Credenciais BlackCat Online não encontradas.");
    echo json_encode(["sucesso" => false, "mensagem" => "Credenciais BlackCat Online não encontradas."], JSON_UNESCAPED_UNICODE);
    exit;
}

$secret_key = $gateway_data['blackcat_secret']; 

$carrinho = json_decode($data['carrinho'] ?? '[]', true);
if (empty($carrinho)) {
    echo json_encode(["sucesso" => false, "mensagem" => "Carrinho vazio."], JSON_UNESCAPED_UNICODE);
    exit;
}

$item_principal = $carrinho[0];
$pagador = json_decode($data['pagador'] ?? '{}', true);
$endereco = json_decode($data['endereço_de_entrega'] ?? '{}', true);
$descontos = json_decode($data['descontos'] ?? '[]', true);
$desconto_pix = 0;
foreach ($descontos as $d) {
    if (($d['metodo'] ?? '') === 'pix') $desconto_pix = floatval($d['desconto']);
}

$items_array = [];
$valor_produtos = 0;

foreach ($carrinho as $item) {
    $preco_base = floatval(str_replace(',', '.', $item['preço_atual'] ?? 0));
    $quantidade = intval($item['quantidade'] ?? 1);
    $valor_com_desconto = $preco_base * (1 - ($desconto_pix / 100));
    $valor_produtos += $valor_com_desconto * $quantidade;

    $items_array[] = [
        'title' => substr($item['ida'] ?? 'Passagem Aérea', 0, 100),
        'unitPrice' => intval(round($valor_com_desconto * 100)),
        'quantity' => $quantidade,
        'tangible' => false
    ];
}

$formas_de_entrega = json_decode($data['formas_de_entrega'] ?? '[]', true);
$forma_escolhida_id = intval($data['forma_de_entrega_escolhida'] ?? 1);
$frete_valor = 0;

foreach ($formas_de_entrega as $forma) {
    if (intval($forma['id'] ?? 0) === $forma_escolhida_id) {
        $frete_valor = floatval(str_replace(',', '.', $forma['valor'] ?? 0));
        break;
    }
}

if ($frete_valor > 0) {
    $items_array[] = [
        'title' => 'Taxas de Serviço',
        'unitPrice' => intval(round($frete_valor * 100)),
        'quantity' => 1,
        'tangible' => false
    ];
}

$valor_final_float = $valor_produtos + $frete_valor;
$valor_final_centavos = intval(round($valor_final_float * 100));
$valor_final_formatado = number_format($valor_final_float, 2, '.', '');

$documento_limpo = preg_replace('/[^0-9]/', '', $pagador['documento'] ?? $pagador['cpf'] ?? '69750637291');
$document_type = (strlen($documento_limpo) == 14) ? 'cnpj' : 'cpf';
$nascimento = str_replace('-', '/', $pagador['data_de_nascimento'] ?? '');

$protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$webhook_url = $protocolo . "://" . $_SERVER['HTTP_HOST'] . "/Webhook/pix/BlackCat";

$payload = [
    'amount' => $valor_final_centavos,
    'currency' => 'BRL',
    'paymentMethod' => 'pix',
    'items' => $items_array,
    'customer' => [
        'name' => $pagador['nome'] ?? 'Cliente',
        'email' => $pagador['email'] ?? '',
        'phone' => preg_replace('/[^0-9]/', '', $pagador['telefone'] ?? ''),
        'document' => [
            'number' => $documento_limpo,
            'type' => $document_type
        ]
    ],
    'pix' => ['expiresInDays' => 1],
    'postbackUrl' => $webhook_url,
    'externalRef' => $data['fullid'] ?? uniqid(),
    'metadata' => $item_principal['origem'] ?? 'Venda BlackCat'
];

if (!empty($endereco['logradouro'])) {
    $payload['shipping'] = [
        'name' => $pagador['nome'] ?? 'Cliente',
        'street' => $endereco['logradouro'] ?? '',
        'number' => $endereco['numero'] ?? 'S/N',
        'complement' => $endereco['complemento'] ?? '',
        'neighborhood' => $endereco['bairro'] ?? '',
        'city' => $endereco['cidade'] ?? '',
        'state' => $endereco['estado'] ?? '',
        'zipCode' => preg_replace('/[^0-9]/', '', $endereco['cep'] ?? '')
    ];
}

$ch = curl_init('https://api.blackcatpagamentos.online/api/sales/create-sale');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-API-Key: ' . $secret_key,
    'Accept: application/json',
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$pixData = json_decode($response, true);

if (!in_array($http_code, [200, 201]) || !($pixData['success'] ?? false)) {
    salvarErro("Gateway Pix: $response");
    echo json_encode(["sucesso" => false, "mensagem" => "Erro ao gerar PIX BlackCat Online.", "debug" => $response], JSON_UNESCAPED_UNICODE);
    exit;
}

$pixid = $pixData['data']['transactionId'] ?? '';
$qrcode_text = $pixData['data']['paymentData']['copyPaste'] ?? $pixData['data']['paymentData']['qrCode'] ?? '';
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
    'BlackcatOnline',
    $item_principal['data_de_ida'] ?? '',
    $item_principal['data_de_volta'] ?? ''
]);

$qr_base64 = $pixData['data']['paymentData']['qrCodeBase64'] ?? '';
if (empty($qr_base64)) {
    $qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qrcode_text);
    $qr_base64 = 'data:image/png;base64,' . base64_encode(@file_get_contents($qr_code_url));
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