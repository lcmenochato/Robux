<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/config.php';

$input = file_get_contents('php://input');
file_put_contents(__DIR__ . '/22.txt', date('Y-m-d H:i:s') . "\n" . $input . "\n\n", FILE_APPEND);
$data = json_decode($input, true);

$stmtGateway = $conexao->prepare("SELECT free_pay_public, free_pay_secret FROM gateway_pix WHERE nome = 'free_pay' LIMIT 1");
$stmtGateway->execute();
$gateway = $stmtGateway->fetch(PDO::FETCH_ASSOC);

if (!$gateway || empty($gateway['free_pay_public']) || empty($gateway['free_pay_secret'])) {
    http_response_code(200);
    echo 'OK';
    exit;
}

$authHeader = 'Basic ' . base64_encode($gateway['free_pay_public'] . ':' . $gateway['free_pay_secret']);

function enviarPixelFacebook($conexao, $pixid) {
    $stmtInfo = $conexao->prepare("SELECT valor, email, utm_campaign, utm_source, utm_medium, utm_content, utm_term FROM infospix WHERE pixid = ? AND gateway = 'FreePay' LIMIT 1");
    $stmtInfo->execute([$pixid]);
    $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);

    if (!$info) return;

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

    if (!$pixel || empty($pixel['pixelid']) || empty($pixel['pixel_token'])) return;

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
    $ch = curl_init("https://api.freepaybrasil.com/v1/payment-transaction/info/" . urlencode($pixid));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Authorization: ' . $authHeader
        ],
        CURLOPT_TIMEOUT => 20,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) return false;

    return json_decode($response, true);
}

function mapearStatus($statusApi) {
    $map = [
        'PAID'          => 'Pago',
        'REFUNDED'      => 'Estornado',
        'REFUSED'       => 'Recusado',
        'CHARGEBACK'    => 'Chargeback',
        'PRECHARGEBACK' => 'Pré-Chargeback',
        'EXPIRED'       => 'Expirado',
        'ERROR'         => 'Erro',
    ];

    return $map[strtoupper(trim($statusApi))] ?? null;
}

function atualizarStatus($conexao, $pixid, $statusApi) {
    $statusFinal = mapearStatus($statusApi);

    if (!$statusFinal) return false;

    $stmt = $conexao->prepare("UPDATE infospix SET status = ? WHERE pixid = ? AND gateway = 'FreePay' AND status != ?");
    $stmt->execute([$statusFinal, $pixid, $statusFinal]);

    if ($stmt->rowCount() > 0) {
        if ($statusFinal === 'Pago') {
            enviarPixelFacebook($conexao, $pixid);
        }
        return true;
    }

    return false;
}

if (!empty($data['Id']) && !empty($data['Status'])) {
    atualizarStatus($conexao, $data['Id'], $data['Status']);
    http_response_code(200);
    echo 'OK';
    exit;
}

if (!empty($data['Id'])) {
    $transaction = consultarTransacao($authHeader, $data['Id']);
    if ($transaction && !empty($transaction['Status'])) {
        atualizarStatus($conexao, $data['Id'], $transaction['Status']);
    }
    http_response_code(200);
    echo 'OK';
    exit;
}

$stmt = $conexao->prepare("SELECT pixid FROM infospix WHERE status = 'pendente' AND gateway = 'FreePay' LIMIT 20");
$stmt->execute();
$pendentes = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($pendentes as $pixid) {
    $transaction = consultarTransacao($authHeader, $pixid);
    if ($transaction && !empty($transaction['Status'])) {
        atualizarStatus($conexao, $pixid, $transaction['Status']);
    }
    usleep(300000);
}

http_response_code(200);
echo 'OK';
exit;