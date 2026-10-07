<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/config.php';

$headers = function_exists('getallheaders') ? getallheaders() : [];

if (!isset($headers['X-Webhook-Source']) || strtolower($headers['X-Webhook-Source']) !== 'blackcat-api') {
    http_response_code(403);
    echo 'OK';
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(200);
    echo 'OK';
    exit;
}

$event = $data['event'] ?? null;
$transactionId = $data['transactionId'] ?? null;
$statusApi = $data['status'] ?? null;

if (!$transactionId) {
    http_response_code(200);
    echo 'OK';
    exit;
}

$stmtGateway = $conexao->prepare("SELECT blackcat_secret, blackcat_public FROM gateway_pix WHERE nome = 'blackcatpagamentos.online'");
$stmtGateway->execute();
$gateway_data = $stmtGateway->fetch(PDO::FETCH_ASSOC);

if (!$gateway_data || empty($gateway_data['blackcat_secret'])) {
    echo 'OK';
    exit;
}

$secret_key = $gateway_data['blackcat_secret'];

$chStatus = curl_init("https://api.blackcatpay.com.br/api/sales/{$transactionId}/status");
curl_setopt($chStatus, CURLOPT_RETURNTRANSFER, true);
curl_setopt($chStatus, CURLOPT_HTTPHEADER, [
    "X-API-Key: {$secret_key}"
]);
curl_setopt($chStatus, CURLOPT_TIMEOUT, 10);
$responseStatus = curl_exec($chStatus);
curl_close($chStatus);

$statusData = json_decode($responseStatus, true);

if (isset($statusData['data']['status'])) {
    $statusApi = $statusData['data']['status'];
}

$statusApi = strtoupper($statusApi);
$statusFinal = null;

if ($statusApi === 'PAID') {
    $statusFinal = 'Pago';
}

if ($statusApi === 'CANCELLED') {
    $statusFinal = 'Cancelado';
}

if ($statusApi === 'REFUNDED') {
    $statusFinal = 'Estornado';
}

if (isset($data['utm'])) {
    $utm = $data['utm'];

    $stmtUtmUpdate = $conexao->prepare("UPDATE infospix SET 
        utm_source = ?, 
        utm_medium = ?, 
        utm_campaign = ?, 
        utm_content = ?, 
        utm_term = ? 
        WHERE pixid = ? AND gateway = 'Blackcat'");

    $stmtUtmUpdate->execute([
        $utm['utm_source'] ?? null,
        $utm['utm_medium'] ?? null,
        $utm['utm_campaign'] ?? null,
        $utm['utm_content'] ?? null,
        $utm['utm_term'] ?? null,
        $transactionId
    ]);
}

function enviarPixelFacebook($conexao, $transactionId) {
    $stmtInfo = $conexao->prepare("SELECT valor, email, utm_source, utm_medium, utm_campaign, utm_content, utm_term FROM infospix WHERE pixid = ? AND gateway = 'Blackcat' LIMIT 1");
    $stmtInfo->execute([$transactionId]);
    $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);

    if (!$info) {
        return;
    }

    $stmtPixel = $conexao->prepare("SELECT pixelid, pixel_token FROM pixel WHERE purchase = 1 ORDER BY id DESC LIMIT 1");
    $stmtPixel->execute();
    $pixel = $stmtPixel->fetch(PDO::FETCH_ASSOC);

    if (!$pixel || empty($pixel['pixelid']) || empty($pixel['pixel_token'])) {
        return;
    }

    $pixelId = $pixel['pixelid'];
    $pixelToken = $pixel['pixel_token'];
    $valor = isset($info['valor']) ? (float)$info['valor'] : 0;
    $email = isset($info['email']) ? hash('sha256', strtolower(trim($info['email']))) : null;

    $payloadPixel = [
        'data' => [
            [
                'event_name' => 'Purchase',
                'event_time' => time(),
                'action_source' => 'website',
                'event_id' => $transactionId,
                'user_data' => [
                    'em' => $email ? [$email] : [],
                    'client_ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                    'client_user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
                ],
                'custom_data' => [
                    'currency' => 'BRL',
                    'value' => $valor,
                    'utm_source' => $info['utm_source'] ?? null,
                    'utm_medium' => $info['utm_medium'] ?? null,
                    'utm_campaign' => $info['utm_campaign'] ?? null,
                    'utm_content' => $info['utm_content'] ?? null,
                    'utm_term' => $info['utm_term'] ?? null
                ]
            ]
        ]
    ];

    $urlPixel = "https://graph.facebook.com/v18.0/{$pixelId}/events?access_token={$pixelToken}";

    $chPixel = curl_init($urlPixel);
    curl_setopt($chPixel, CURLOPT_POST, true);
    curl_setopt($chPixel, CURLOPT_POSTFIELDS, json_encode($payloadPixel));
    curl_setopt($chPixel, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($chPixel, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($chPixel, CURLOPT_TIMEOUT, 20);
    curl_exec($chPixel);
    curl_close($chPixel);

    $stmtUtm = $conexao->prepare("SELECT token FROM utmify WHERE usar = 1 ORDER BY id DESC LIMIT 1");
    $stmtUtm->execute();
    $utm = $stmtUtm->fetch(PDO::FETCH_ASSOC);

    if ($utm && !empty($utm['token'])) {
        $utmPayload = [
            'event'  => 'purchase',
            'value'  => $valor,
            'order_id' => $transactionId
        ];

        $ch = curl_init("https://api.utmify.com.br/v1/events?token=" . $utm['token']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($utmPayload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_exec($ch);
        curl_close($ch);
    }
}

if ($statusFinal) {
    $stmt = $conexao->prepare("UPDATE infospix SET status = ? WHERE pixid = ? AND gateway = 'Blackcat' AND status != ?");
    $stmt->execute([$statusFinal, $transactionId, $statusFinal]);

    if ($statusFinal === 'Pago') {
        enviarPixelFacebook($conexao, $transactionId);
    }
}

http_response_code(200);
echo 'OK';
exit;