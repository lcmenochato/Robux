<?php
ini_set('display_errors', 0);
error_reporting(0);

require_once $_SERVER['DOCUMENT_ROOT'] . '/config/config.php';

function enviarPixelFacebook($conexao, $pixid) {
    $stmtInfo = $conexao->prepare("SELECT valor, email, utm_campaign, utm_source, utm_medium, utm_content, utm_term FROM infospix WHERE pixid = ? AND gateway = 'Plumify' LIMIT 1");
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

    if ($pixel && !empty($pixel['pixelid']) && !empty($pixel['pixel_token'])) {
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
    }

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

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$transactionHash = null;

if (isset($data['transaction_hash'])) {
    $transactionHash = $data['transaction_hash'];
} elseif (isset($data['hash'])) {
    $transactionHash = $data['hash'];
} elseif (isset($data['id'])) {
    $transactionHash = $data['id'];
} elseif (isset($_GET['id'])) {
    $transactionHash = $_GET['id'];
}

$stmtGateway = $conexao->prepare("SELECT plumify_api_token_api FROM gateway_pix WHERE nome = 'plumify' LIMIT 1");
$stmtGateway->execute();
$gateway = $stmtGateway->fetch(PDO::FETCH_ASSOC);

if (!$gateway || empty($gateway['plumify_api_token_api'])) {
    http_response_code(200);
    echo 'OK';
    exit;
}

$api_token = $gateway['plumify_api_token_api'];

function consultarTransacao($api_token, $transactionHash) {
    $url = "https://api.plumify.com.br/api/public/v1/transactions/" . urlencode($transactionHash) . "?api_token=" . urlencode($api_token);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
        CURLOPT_TIMEOUT => 20,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        return false;
    }

    return json_decode($response, true);
}

function mapearStatus($status) {
    $status = strtolower($status);

    switch ($status) {
        case 'paid':
        case 'approved':
        case 'captured':
            return 'pago';
        case 'pending':
        case 'waiting_payment':
            return 'pendente';
        case 'cancelled':
        case 'expired':
        case 'refused':
            return 'cancelado';
        case 'refunded':
            return 'reembolsado';
        case 'chargeback':
            return 'chargeback';
        default:
            return 'pendente';
    }
}

function atualizarBanco($conexao, $transactionHash, $statusFinal) {
    $stmt = $conexao->prepare("UPDATE infospix SET status = ? WHERE pixid = ? AND gateway = 'Plumify' AND status <> ?");
    $stmt->execute([$statusFinal, $transactionHash, $statusFinal]);

    if ($stmt->rowCount() > 0 && strtolower($statusFinal) === 'pago') {
        enviarPixelFacebook($conexao, $transactionHash);
    }
}

if ($transactionHash) {

    if (isset($data['status'])) {
        $statusFinal = mapearStatus($data['status']);
        atualizarBanco($conexao, $transactionHash, $statusFinal);
    } else {
        $transaction = consultarTransacao($api_token, $transactionHash);
        if ($transaction && isset($transaction['payment_status'])) {
            $statusFinal = mapearStatus($transaction['payment_status']);
            atualizarBanco($conexao, $transactionHash, $statusFinal);
        }
    }

} else {

    $stmt = $conexao->prepare("SELECT pixid FROM infospix WHERE status IN ('pendente','aguardando') AND gateway = 'Plumify' LIMIT 20");
    $stmt->execute();
    $pendentes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($pendentes as $pixid) {
        $transaction = consultarTransacao($api_token, $pixid);
        if ($transaction && isset($transaction['payment_status'])) {
            $statusFinal = mapearStatus($transaction['payment_status']);
            atualizarBanco($conexao, $pixid, $statusFinal);
        }
        usleep(400000);
    }
}

http_response_code(200);
echo 'OK';
exit;