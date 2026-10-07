<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/config.php';

$stmtGateway = $conexao->prepare("SELECT anubis_public, anubis_secret FROM gateway_pix WHERE nome = 'anubis_pay' LIMIT 1");
$stmtGateway->execute();
$gateway = $stmtGateway->fetch(PDO::FETCH_ASSOC);

if (!$gateway || empty($gateway['anubis_public']) || empty($gateway['anubis_secret'])) {
    http_response_code(500);
    exit;
}

$publicKey  = $gateway['anubis_public'];
$secretKey  = $gateway['anubis_secret'];
$authHeader = 'Basic ' . base64_encode($publicKey . ':' . $secretKey);

$input = file_get_contents('php://input');
$data  = json_decode($input, true);

$transactionId = $data['Id'] ?? $data['id'] ?? null;

if (!$transactionId) {
    http_response_code(400);
    exit;
}

$ch = curl_init("https://api.anubispay.com/v1/payment-transaction/info/" . urlencode($transactionId));
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'accept: application/json',
        'authorization: ' . $authHeader
    ],
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_SSL_VERIFYPEER => true
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || !$response) {
    http_response_code(400);
    exit;
}

$transaction = json_decode($response, true);
$statusApi   = isset($transaction['Status']) ? strtoupper($transaction['Status']) : (isset($transaction['status']) ? strtoupper($transaction['status']) : '');

if ($statusApi !== 'PAID') {
    http_response_code(200);
    exit;
}

try {
    $stmtCheck = $conexao->prepare("SELECT status, valor, email, utm_campaign, utm_source, utm_medium, utm_content, utm_term FROM infospix WHERE pixid = ? AND gateway = 'AnubisPay' LIMIT 1");
    $stmtCheck->execute([$transactionId]);
    $order = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$order || $order['status'] === 'Pago') {
        http_response_code(200);
        exit;
    }

    $stmtUpdate = $conexao->prepare("UPDATE infospix SET status = 'Pago' WHERE pixid = ? AND gateway = 'AnubisPay'");
    $stmtUpdate->execute([$transactionId]);

    $valor       = (float)$order['valor'];
    $email       = $order['email'];
    $utmCampaign = $order['utm_campaign'] ?? '';
    $utmSource   = $order['utm_source']   ?? '';
    $utmMedium   = $order['utm_medium']   ?? '';
    $utmContent  = $order['utm_content']  ?? '';
    $utmTerm     = $order['utm_term']     ?? '';

    $stmtPixel = $conexao->prepare("SELECT pixelid, pixel_token FROM pixel WHERE purchase = 1 ORDER BY id DESC LIMIT 1");
    $stmtPixel->execute();
    $pixel = $stmtPixel->fetch(PDO::FETCH_ASSOC);

    if ($pixel && !empty($pixel['pixelid']) && !empty($pixel['pixel_token'])) {
        $hashedEmail = hash('sha256', strtolower(trim($email)));
        $fbPayload = [
            'data' => [[
                'event_name'    => 'Purchase',
                'event_time'    => time(),
                'action_source' => 'website',
                'event_id'      => 'anubis_' . $transactionId,
                'user_data'     => [
                    'em'                => [$hashedEmail],
                    'client_ip_address' => $_SERVER['REMOTE_ADDR']     ?? '',
                    'client_user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
                ],
                'custom_data' => [
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

        $chFB = curl_init("https://graph.facebook.com/v18.0/{$pixel['pixelid']}/events?access_token={$pixel['pixel_token']}");
        curl_setopt_array($chFB, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($fbPayload),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        curl_exec($chFB);
        curl_close($chFB);
    }

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

        $chUtm = curl_init("https://api.utmify.com.br/v1/events?token=" . urlencode($utm['token']));
        curl_setopt_array($chUtm, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($utmPayload),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        curl_exec($chUtm);
        curl_close($chUtm);
    }

    http_response_code(200);
    echo "OK";

} catch (PDOException $e) {
    http_response_code(500);
}