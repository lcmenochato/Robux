<?php
require_once "../config/config.php";

$dados = $GLOBALS['dadosParazap'] 
    ?? (isset($__JSON_INPUT__) ? json_decode($__JSON_INPUT__, true) : null) 
    ?? json_decode(file_get_contents("php://input"), true);

if (!$dados || !is_array($dados)) {
    exit;
}

$nome        = $dados['nome'] ?? '';
$valor_final = $dados['valor_final'] ?? ($dados['valor'] ?? 0);
$codigoPix   = $dados['pix'] ?? ($dados['codigo_pix'] ?? ($dados['qrcode_text'] ?? ''));
$qrCodeBase64 = $dados['qrcode'] ?? ($dados['qr_code'] ?? '');
$pedido      = $dados['pedido'] ?? null;
$cpf         = $dados['cpf'] ?? null;
$telefone    = $dados['telefone'] ?? '';

$telefone = preg_replace('/[^0-9]/', '', $telefone);
if ($telefone && substr($telefone, 0, 2) !== '55') {
    $telefone = '55' . $telefone;
}

$stmtToken = $conexao->prepare("SELECT instance_id, token, token_seguranca FROM token_zap WHERE id = 1");
$stmtToken->execute();
$configZap = $stmtToken->fetch(PDO::FETCH_ASSOC);

$statusInicial = 'Pendente';

if ($configZap && !empty($configZap['instance_id']) && !empty($configZap['token']) && !empty($configZap['token_seguranca'])) {
    $statusInicial = 'Não enviado';
}

$stmtLog = $conexao->prepare("INSERT INTO enviar_whatsapp (nome, cpf, valor, pedido, status, telefone, pix, qrcode) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmtLog->execute([
    $nome,
    $cpf,
    $valor_final,
    $pedido,
    $statusInicial,
    $telefone,
    $codigoPix,
    $qrCodeBase64
]);

$logId = $conexao->lastInsertId();

if ($statusInicial === 'Pendente') {
    exit;
}

$instance_id     = $configZap['instance_id'];
$token           = $configZap['token'];
$token_seguranca = $configZap['token_seguranca'];

$mensagem1 = "Olá $nome, devido a altas taxas bancárias essa promoção só é válida para pagamento via Pix\n\nCaso tenha alguma dúvida basta nos enviar uma mensagem que lhe ajudaremos o mais rápido possivel.\n\nAtenciosamente SAC 123Milhas.\n\nPague com Pix lendo o QR Code ou com o código Pix Copia e Cola";
$mensagem2 = "Qrcode 👆\nPix Copia e cola 👇";
$mensagem3 = $codigoPix;

$url_msg = "https://api.z-api.io/instances/$instance_id/token/$token/send-text";

$success_msg1 = false;
$success_img  = false;
$success_msg3 = false;

$ch_msg = curl_init($url_msg);
curl_setopt($ch_msg, CURLOPT_HTTPHEADER, ['Content-Type: application/json', "Client-Token: $token_seguranca"]);
curl_setopt($ch_msg, CURLOPT_POST, true);
curl_setopt($ch_msg, CURLOPT_POSTFIELDS, json_encode([
    "phone" => $telefone,
    "message" => $mensagem1
]));
curl_setopt($ch_msg, CURLOPT_RETURNTRANSFER, true);
$response_msg = curl_exec($ch_msg);
$httpCode_msg = curl_getinfo($ch_msg, CURLINFO_HTTP_CODE);
curl_close($ch_msg);

if ($httpCode_msg == 200 || $httpCode_msg == 201) {
    $success_msg1 = true;
}

if (!empty($qrCodeBase64)) {
    $url_img = "https://api.z-api.io/instances/$instance_id/token/$token/send-image";
    $ch_img = curl_init($url_img);
    curl_setopt($ch_img, CURLOPT_HTTPHEADER, ['Content-Type: application/json', "Client-Token: $token_seguranca"]);
    curl_setopt($ch_img, CURLOPT_POST, true);
    curl_setopt($ch_img, CURLOPT_POSTFIELDS, json_encode([
        "phone" => $telefone,
        "image" => $qrCodeBase64,
        "caption" => $mensagem2
    ]));
    curl_setopt($ch_img, CURLOPT_RETURNTRANSFER, true);
    $response_img = curl_exec($ch_img);
    $httpCode_img = curl_getinfo($ch_img, CURLINFO_HTTP_CODE);
    curl_close($ch_img);

    if ($httpCode_img == 200 || $httpCode_img == 201) {
        $success_img = true;
    }
} else {
    $success_img = true;
}

if (!empty($mensagem3)) {
    $ch_msg3 = curl_init($url_msg);
    curl_setopt($ch_msg3, CURLOPT_HTTPHEADER, ['Content-Type: application/json', "Client-Token: $token_seguranca"]);
    curl_setopt($ch_msg3, CURLOPT_POST, true);
    curl_setopt($ch_msg3, CURLOPT_POSTFIELDS, json_encode([
        "phone" => $telefone,
        "message" => $mensagem3
    ]));
    curl_setopt($ch_msg3, CURLOPT_RETURNTRANSFER, true);
    $response_msg3 = curl_exec($ch_msg3);
    $httpCode_msg3 = curl_getinfo($ch_msg3, CURLINFO_HTTP_CODE);
    curl_close($ch_msg3);

    if ($httpCode_msg3 == 200 || $httpCode_msg3 == 201) {
        $success_msg3 = true;
    }
}

$statusFinal = 'Não Enviado';

if ($success_msg1 && $success_img && $success_msg3) {
    $statusFinal = 'Enviado';
}

$stmtUpdate = $conexao->prepare("UPDATE enviar_whatsapp SET status = ? WHERE id = ?");
$stmtUpdate->execute([$statusFinal, $logId]);
?>
