<?php
ini_set('display_errors', 0);
error_reporting(0);
ignore_user_abort(true);

require_once $_SERVER['DOCUMENT_ROOT'] . '/config/config.php';

function enviarPixelFacebook($conexao, $pixid) {
    $stmtInfo = $conexao->prepare("SELECT valor, email, utm_campaign, utm_source, utm_medium, utm_content, utm_term FROM infospix WHERE pixid = ? AND gateway = 'TryploPay' LIMIT 1");
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

    $payload = [
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
    curl_setopt($chPixel, CURLOPT_POSTFIELDS, json_encode($payload));
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

function verificarFatura($conexao, $apiToken, $invoiceId) {
    $ch = curl_init("https://api.tryplopay.com/invoices?id=" . urlencode($invoiceId));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer {$apiToken}",
            "Accept: application/json"
        ],
        CURLOPT_TIMEOUT => 20
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        return;
    }

    $result = json_decode($response, true);

    if (!isset($result['invoices'][0]['status']['code'])) {
        return;
    }

    $statusCode = (int)$result['invoices'][0]['status']['code'];

    $statusMap = [
        1  => 'aguardando',
        5  => 'Pago',
        6  => 'Cancelado',
        15 => 'Vencido',
        16 => 'Erro'
    ];

    if (!isset($statusMap[$statusCode])) {
        return;
    }

    $novoStatus = $statusMap[$statusCode];

    $stmt = $conexao->prepare("UPDATE infospix SET status = ? WHERE pixid = ? AND gateway = 'TryploPay' AND status <> ?");
    $stmt->execute([$novoStatus, $invoiceId, $novoStatus]);

    if ($novoStatus === 'Pago' && $stmt->rowCount() > 0) {
        enviarPixelFacebook($conexao, $invoiceId);
    }
}

$webhookHeader = $_SERVER['HTTP_WEBHOOK'] ?? null;

if (!$webhookHeader) {
    http_response_code(401);
    echo 'Missing webhook header.';
    exit;
}

$stmtGateway = $conexao->prepare("SELECT tryplo_chave_secreta, tryplo_token FROM gateway_pix WHERE nome = 'tryplo_pay' LIMIT 1");
$stmtGateway->execute();
$gateway = $stmtGateway->fetch(PDO::FETCH_ASSOC);

if (!$gateway || empty($gateway['tryplo_chave_secreta']) || empty($gateway['tryplo_token'])) {
    http_response_code(503);
    echo 'Gateway not configured.';
    exit;
}

$secretKey = $gateway['tryplo_chave_secreta'];
$apiToken  = $gateway['tryplo_token'];

$decoded = base64_decode($webhookHeader, true);

if (!$decoded || !str_contains($decoded, ':')) {
    http_response_code(401);
    echo 'Invalid webhook format.';
    exit;
}

list($userToken, $tryploToken) = explode(':', $decoded, 2);

if ($userToken !== $secretKey) {
    http_response_code(401);
    echo 'Token mismatch.';
    exit;
}

$ch = curl_init("https://api.tryplopay.com/webhooks");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST  => "OPTIONS",
    CURLOPT_POSTFIELDS     => json_encode(['token' => $tryploToken]),
    CURLOPT_HTTPHEADER     => [
        "Authorization: Bearer {$apiToken}",
        "Content-Type: application/json"
    ],
    CURLOPT_TIMEOUT => 15
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    http_response_code(401);
    echo 'Invalid Tryplo token.';
    exit;
}

$input = file_get_contents('php://input');
$data  = json_decode($input, true);

$invoiceId = $data['id'] ?? null;

if (!$invoiceId) {
    http_response_code(200);
    echo 'OK';
    exit;
}

verificarFatura($conexao, $apiToken, $invoiceId);

http_response_code(200);
echo 'OK';
exit;