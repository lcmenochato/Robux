<?php

if (!isset($conexao)) {
    include __DIR__ . '/../../erro.php';
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$stmtGateway = $conexao->prepare("SELECT podpay_public, podpay_secret FROM gateway_pix WHERE nome = 'podpay'");
$stmtGateway->execute();
$gateway_data = $stmtGateway->fetch(PDO::FETCH_ASSOC);

if (!$gateway_data || empty($gateway_data['podpay_secret'])) {
    echo json_encode(["sucesso" => false, "mensagem" => "Credenciais PodPay não encontradas."], JSON_UNESCAPED_UNICODE);
    exit;
}

$api_key = $gateway_data['podpay_secret'];

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
$valor_final_centavos = intval(round($valor_total * 100));

if ($valor_final_centavos < 100) {
    echo json_encode(["sucesso" => false, "mensagem" => "Valor abaixo do mínimo permitido."], JSON_UNESCAPED_UNICODE);
    exit;
}

$documento_limpo = preg_replace('/[^0-9]/', '', $pagador['documento'] ?? $pagador['cpf'] ?? '69750637291');
$document_type = (strlen($documento_limpo) == 14) ? 'cnpj' : 'cpf';

$host_atual = $_SERVER['HTTP_HOST'] ?? '';
$webhook_url = 'https://' . $host_atual . "/Webhook/pix/PodPay";

function url_publica_valida($url) {
    $parts = parse_url($url);
    $host = $parts['host'] ?? '';
    $scheme = $parts['scheme'] ?? '';
    if ($scheme !== 'https') return false;
    if (!$host) return false;
    if (strtolower($host) === 'localhost') return false;
    if (strpos($host, '.') === false) return false;
    if (filter_var($host, FILTER_VALIDATE_IP)) {
        if (!filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return false;
        }
    }
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

function gerar_uuid_v4() {
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function podpay_post($url, $payload, $api_key, $idempotency_key) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'x-api-key: ' . $api_key,
        'X-Idempotency-Key: ' . $idempotency_key,
        'accept: application/json',
        'content-type: application/json'
    ]);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    return [
        'response' => $response,
        'http_code' => $http_code,
        'curl_error' => $curl_error,
        'data' => json_decode($response, true)
    ];
}

$payload = [
    'paymentMethod' => 'pix',
    'customer' => [
        'document' => [
            'type' => $document_type,
            'number' => $documento_limpo
        ],
        'name' => $pagador['nome'] ?? 'Cliente',
        'email' => $pagador['email'] ?? 'email@provedor.com',
        'phone' => preg_replace('/[^0-9]/', '', $pagador['telefone'] ?? '')
    ],
    'amount' => $valor_final_centavos,
    'items' => $items_payload
];

if (url_publica_valida($webhook_url)) {
    $payload['postbackUrl'] = $webhook_url;
}

$max_tentativas = 3;
$tentativa = 0;
$transacao_result = null;

do {
    $tentativa++;
    $idempotency_key = gerar_uuid_v4();
    $transacao_result = podpay_post('https://api.podpay.app/v1/transactions', $payload, $api_key, $idempotency_key);

    if (!empty($transacao_result['curl_error'])) {
        if ($tentativa < $max_tentativas) {
            sleep(2 * $tentativa);
            continue;
        }
        break;
    }

    $codigo_erro = $transacao_result['data']['error']['code'] ?? '';
    if (in_array($codigo_erro, ['API_KEY_SCOPE_FORBIDDEN', 'API_KEY_INVALID', 'UNAUTHORIZED', 'BAD_REQUEST'])) {
        break;
    }

    $status_transacao = $transacao_result['data']['data']['status'] ?? '';
    $retryable = $transacao_result['data']['data']['integrationError']['retryable'] ?? false;

    if ($status_transacao !== 'failed' || !$retryable) {
        break;
    }

    if ($tentativa < $max_tentativas) {
        sleep(2 * $tentativa);
    }
} while ($tentativa < $max_tentativas);

$pixData = $transacao_result['data'];

if (!empty($transacao_result['curl_error'])) {
    salvarErro("Gateway Pix: " . $transacao_result['curl_error']);
    echo json_encode(["sucesso" => false, "mensagem" => "Erro de conexão com a PodPay: " . $transacao_result['curl_error']], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!($pixData['success'] ?? false)) {
    $mensagem_erro = $pixData['error']['message'] ?? 'Erro ao gerar PIX PodPay.';
    salvarErro("Gateway Pix: " . $transacao_result['response']);
    echo json_encode(["sucesso" => false, "mensagem" => "Erro PodPay: " . $mensagem_erro, "debug" => $transacao_result['response']], JSON_UNESCAPED_UNICODE);
    exit;
}

if (($pixData['data']['status'] ?? '') === 'failed') {
    $motivo = $pixData['data']['failedReason'] ?? 'Motivo não informado.';
    salvarErro("Gateway Pix: " . $transacao_result['response']);
    echo json_encode(["sucesso" => false, "mensagem" => "Pagamento PIX falhou na PodPay: " . $motivo, "debug" => $transacao_result['response']], JSON_UNESCAPED_UNICODE);
    exit;
}

$pixid = $pixData['data']['id'] ?? '';
$qrcode_text = $pixData['data']['pixQrCode'] ?? '';

if (empty($pixid) || empty($qrcode_text)) {
    salvarErro("Gateway Pix: " . $transacao_result['response']);
    echo json_encode(["sucesso" => false, "mensagem" => "Resposta inesperada da PodPay.", "debug" => $transacao_result['response']], JSON_UNESCAPED_UNICODE);
    exit;
}

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
    'PodPay',
    $item_principal['data_de_ida'] ?? '',
    $item_principal['data_de_volta'] ?? ''
]);

$qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qrcode_text);
$qr_content = @file_get_contents($qr_code_url);
$qr_base64 = $qr_content ? ('data:image/png;base64,' . base64_encode($qr_content)) : '';

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
    }
}
exit;