<?php
ini_set('display_errors', 0);
error_reporting(0);

require_once $_SERVER['DOCUMENT_ROOT'] . '/config/config.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$transactionId = $data['id'] ?? $data['transaction']['id'] ?? $_GET['id'] ?? null;

$stmtGateway = $conexao->prepare("SELECT nuvia_pay_public_key, nuvia_pay_secret_key FROM gateway_pix WHERE nome = 'nuvia_pay' LIMIT 1");
$stmtGateway->execute();
$gateway = $stmtGateway->fetch(PDO::FETCH_ASSOC);

if (!$gateway || empty($gateway['nuvia_pay_public_key']) || empty($gateway['nuvia_pay_secret_key'])) {
    http_response_code(200);
    echo 'OK';
    exit;
}

$authHeader = 'Basic ' . base64_encode(
    $gateway['nuvia_pay_public_key'] . ':' . $gateway['nuvia_pay_secret_key']
);

function enviarPixelFacebook($conexao, $pixid) {
    $stmtInfo = $conexao->prepare("SELECT valor, email, utm_campaign, utm_source, utm_medium, utm_content, utm_term FROM infospix WHERE pixid = ? AND gateway = 'NuviaPay' LIMIT 1");
    $stmtInfo->execute([$pixid]);
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
            'event_id'      => $pixid,
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
            'order_id'     => $pixid,
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

function consultarTransacao($authHeader, $pixid) {
    $ch = curl_init("https://api.nuviapay.com/v1/transactions/" . intval($pixid));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Authorization: ' . $authHeader
        ],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_TIMEOUT => 20
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        return false;
    }

    return json_decode($response, true);
}

function atualizarStatus($conexao, $pixid, $statusApi) {
    if ($statusApi !== 'paid') {
        return false;
    }

    $stmt = $conexao->prepare("UPDATE infospix SET status = 'Pago' WHERE pixid = ? AND gateway = 'NuviaPay' AND status = 'pendente'");
    $stmt->execute([$pixid]);

    if ($stmt->rowCount() > 0) {
        enviarPixelFacebook($conexao, $pixid);
        return true;
    }

    return false;
}

if ($transactionId) {
    $transaction = consultarTransacao($authHeader, $transactionId);

    if ($transaction && isset($transaction['status'])) {
        atualizarStatus($conexao, $transactionId, $transaction['status']);
    }

    http_response_code(200);
    echo 'OK';
    exit;
}

$stmt = $conexao->prepare("SELECT pixid FROM infospix WHERE status = 'pendente' AND gateway = 'NuviaPay' LIMIT 20");
$stmt->execute();
$pendentes = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($pendentes as $pixid) {
    $transaction = consultarTransacao($authHeader, $pixid);

    if ($transaction && isset($transaction['status'])) {
        atualizarStatus($conexao, $pixid, $transaction['status']);
    }

    usleep(300000);
}

http_response_code(200);
echo 'OK';
exit;