<?php
include '../erros_pagamento.php';
include '../erroAPI.php';

if (!isset($conexao)) {
    include __DIR__ . '/../../erro.php';
    exit;
}

$conexao->query("UPDATE relatorio_gateway SET visitas = visitas + 1 WHERE id = 1");

$stmtGateway = $conexao->prepare("SELECT apikey FROM gateway_pix WHERE nome = 'evopay'");
$stmtGateway->execute();
$gateway_data = $stmtGateway->fetch(PDO::FETCH_ASSOC);

if (!$gateway_data || empty($gateway_data['apikey'])) {
    salvarErro("Gateway Pix: API Key da EvoPay não encontrada.");
    echo json_encode(["sucesso" => false, "mensagem" => "API Key da EvoPay não encontrada."]);
    exit;
}

$apiKey = $gateway_data['apikey'];

$carrinho = json_decode($data['carrinho'] ?? '[]', true);
if (empty($carrinho)) {
    echo json_encode(["sucesso" => false, "mensagem" => "Carrinho vazio."]);
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
$callbackUrl = $protocolo . "://" . $_SERVER['HTTP_HOST'] . "/Webhook/pix/EvoPay";

$payload = [
    'amount' => floatval($valor_final_formatado),
    'callbackUrl' => $callbackUrl,
    'payerName' => substr($pagador['nome'] ?? 'Cliente', 0, 100),
    'payerDocument' => $documento_limpo,
    'payerEmail' => $pagador['email'] ?? ''
];

$ch = curl_init('https://pix.evopay.cash/v1/pix');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'API-Key: ' . $apiKey
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$responseData = json_decode($response, true);

if (($http_code == 200 || $http_code == 201) && isset($responseData['qrCodeText'])) {
    $qrcode_text = $responseData['qrCodeText'];
    $base64png = "data:image/png;base64," . ($responseData['qrCodeBase64'] ?? '');
    $pixid = $responseData['id'] ?? null;
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
        'EvoPay',
        $item_principal['data_de_ida'] ?? '',
        $item_principal['data_de_volta'] ?? ''
    ]);

    echo json_encode([
        "sucesso" => true,
        "código_pix" => $qrcode_text,
        "qr_code_pix" => $base64png,
        "valor" => $valor_final_formatado,
        "numero_do_pedido" => $numero_pedido
    ]);

    if (!empty($qrcode_text) && !empty($base64png)) {
    $GLOBALS['dadosParazap'] = [
        "nome" => $pagador['nome'] ?? '',
        "cpf" => $pagador['cpf'] ?? '',
        "telefone" => $pagador['telefone'] ?? '',
        "pedido" => $numero_pedido,
        "pix" => $qrcode_text,
        "qrcode" => $base64png,
        "valor" => $valor_final_formatado
    ];

    try {
        include __DIR__ . '/../envio_zap.php';
    } catch (Throwable $e) {
        //error_log($e->getMessage()); Ignorar os erros do envio_zap.php
    }}} else {
    echo json_encode([
        "sucesso" => false, 
        "mensagem" => "Erro EvoPay: " . ($responseData['message'] ?? $http_code),
        "debug" => $responseData
    ]);
}
exit;