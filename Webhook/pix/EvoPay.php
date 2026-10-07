<?php
ini_set('display_errors', 0);
error_reporting(0);

require_once $_SERVER['DOCUMENT_ROOT'] . '/config/config.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$transactionId = $data['id'] ?? $_GET['id'] ?? null;

if (!$transactionId) {
    http_response_code(200);
    echo 'OK';
    exit;
}

$stmtGateway = $conexao->prepare("SELECT apikey FROM gateway_pix WHERE nome = 'evopay' LIMIT 1");
$stmtGateway->execute();
$gateway_info = $stmtGateway->fetch(PDO::FETCH_ASSOC);

if (!$gateway_info || empty($gateway_info['apikey'])) {
    http_response_code(200);
    echo 'OK';
    exit;
}

$apiKey = $gateway_info['apikey'];

function enviarPixelFacebook($conexao, $transactionId) {
    $stmtInfo = $conexao->prepare("SELECT valor, email, utm_campaign, utm_source, utm_medium, utm_content, utm_term FROM infospix WHERE pixid = ? AND gateway = 'evopay' LIMIT 1");
    $stmtInfo->execute([$transactionId]);
    $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);

    if (!$info) {
        return;
    }

    $valor       = isset($info['valor']) ? (float)$info['valor'] : 0;
    $utmCampaign = $info['utm_campaign'] ?? '';
    $utmSource   = $info['utm_source'] ?? '';
    $utmMedium   = $info['utm_medium'] ?? '';
    $utmContent  = $info['utm_content'] ?? '';
    $utmTerm     = $info['utm_term'] ?? '';
    $emailHash   = !empty($info['email']) ? hash('sha256', strtolower(trim($info['email']))) : null;

    $stmtPixel = $conexao->prepare("SELECT pixelid, pixel_token FROM pixel WHERE purchase = 1 ORDER BY id DESC LIMIT 1");
    $stmtPixel->execute();
    $pixel = $stmtPixel->fetch(PDO::FETCH_ASSOC);

    if (!$pixel || empty($pixel['pixelid']) || empty($pixel['pixel_token'])) {
        return;
    }

    $payloadPixel = [
        'data' => [[
            'event_name'    => 'Purchase',
            'event_time'    => time(),
            'action_source' => 'website',
            'event_id'      => $transactionId,
            'user_data'     => [
                'em'                => $emailHash ? [$emailHash] : [],
                'client_ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                'client_user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ],
            'custom_data'   => [
                'currency'     => 'BRL',
                'value'        => $valor,
                'utm_campaign' => $utmCampaign,
                'utm_source'   => $utmSource,
                'utm_medium'   => $utmMedium,
                'utm_content'  => $utmContent,
                'utm_term'     => $utmTerm
            ]
        ]]
    ];

    $chPixel = curl_init("https://graph.facebook.com/v18.0/{$pixel['pixelid']}/events?access_token={$pixel['pixel_token']}");
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
            'event'        => 'purchase',
            'value'        => $valor,
            'order_id'     => $transactionId,
            'utm_campaign' => $utmCampaign,
            'utm_source'   => $utmSource,
            'utm_medium'   => $utmMedium,
            'utm_content'  => $utmContent,
            'utm_term'     => $utmTerm
        ];

        $chUtm = curl_init("https://api.utmify.com.br/v1/events?token=" . $utm['token']);
        curl_setopt($chUtm, CURLOPT_POST, true);
        curl_setopt($chUtm, CURLOPT_POSTFIELDS, json_encode($utmPayload));
        curl_setopt($chUtm, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($chUtm, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chUtm, CURLOPT_TIMEOUT, 10);
        curl_exec($chUtm);
        curl_close($chUtm);
    }
}

function verificarPagamento($conexao, $apiKey, $pixid) {
    $ch = curl_init("https://pix.evopay.cash/v1/pix?id=" . urlencode($pixid));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'API-Key: ' . $apiKey
        ],
        CURLOPT_TIMEOUT => 20,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200 || !$response) {
        return false;
    }

    $res = json_decode($response, true);

    if (!isset($res['status'])) {
        return false;
    }

    $status = strtoupper($res['status']);
    $statusFinal = null;

    if ($status === 'COMPLETED') {
        $statusFinal = 'pago';
    }

    if ($status === 'CANCELED') {
        $statusFinal = 'cancelado';
    }

    if (!$statusFinal) {
        return false;
    }

    $stmt = $conexao->prepare("UPDATE infospix SET status = ? WHERE pixid = ? AND gateway = 'evopay' AND status != ?");
    $stmt->execute([$statusFinal, $pixid, $statusFinal]);

    if ($statusFinal === 'pago' && $stmt->rowCount() > 0) {
        enviarPixelFacebook($conexao, $pixid);
    }

    return true;
}

verificarPagamento($conexao, $apiKey, $transactionId);

http_response_code(200);
echo 'OK';
exit;