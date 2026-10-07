<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($conexao) || !($conexao instanceof PDO)) {
    http_response_code(404);
    exit;
}

$stmt = $conexao->prepare("SELECT * FROM cloaker WHERE id = 1 LIMIT 1");
$stmt->execute();
$cfg = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cfg) {
    http_response_code(500);
    exit;
}

if (!function_exists('bloquear')) {
    function bloquear($cfg = []) {
        if (!empty($cfg['cloaker_redirecionar']) && (int)$cfg['cloaker_redirecionar'] === 1 && !empty($cfg['cloaker_redirecionar_para'])) {
            header('Location: ' . $cfg['cloaker_redirecionar_para']);
            exit;
        }
        $erro_path = __DIR__ . '/erro.php';
        if (file_exists($erro_path)) {
            include $erro_path;
        } else {
            http_response_code(403);
            echo "Acesso negado.";
        }
        exit;
    }
}

if (!function_exists('obterIpReal')) {
    function obterIpReal() {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];
        foreach ($headers as $header) {
            if (isset($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if ($header === 'HTTP_X_FORWARDED_FOR') {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}

if (!function_exists('verificarLista')) {
    function verificarLista($lista, $valor, $tipo = 'exato') {
        if (empty($lista)) {
            return false;
        }
        $itens = array_map('trim', explode("\n", $lista));
        $itens = array_filter($itens);
        if ($tipo === 'exato') {
            return in_array($valor, $itens);
        } else {
            foreach ($itens as $item) {
                if (stripos($valor, $item) !== false) {
                    return true;
                }
            }
        }
        return false;
    }
}

if (!function_exists('consultarIpInfo')) {
    function consultarIpInfo($ip) {
        $ch = curl_init("http://ip-api.com/json/{$ip}?fields=status,countryCode,as,isp,proxy");
        curl_setopt_array($ch, [
            \CURLOPT_RETURNTRANSFER => true,
            \CURLOPT_TIMEOUT => 5,
            \CURLOPT_CONNECTTIMEOUT => 3,
            \CURLOPT_SSL_VERIFYPEER => false,
            \CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; Cloaker/1.0)'
        ]);
        $res = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($err) {
            return null;
        }
        $data = json_decode($res, true);
        if (isset($data['status']) && $data['status'] === 'success') {
            return $data;
        }
        return null;
    }
}

$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$isMobile = preg_match('/Mobile|Android|Silk\/|Kindle|BlackBerry|Opera Mini|Opera Mobi|IEMobile|webOS|iPhone|iPad|iPod/i', $userAgent);
$isAndroid = stripos($userAgent, 'Android') !== false;
$isIOS = preg_match('/iPhone|iPad|iPod/i', $userAgent);

if ((int)$cfg['bloquear_mobile'] === 1 && $isMobile) bloquear($cfg);
if ((int)$cfg['bloquear_desktop'] === 1 && !$isMobile) bloquear($cfg);
if ((int)$cfg['bloquear_android'] === 1 && $isAndroid) bloquear($cfg);
if ((int)$cfg['bloquear_ios'] === 1 && $isIOS) bloquear($cfg);

if (!empty($cfg['user_agents'])) {
    if (verificarLista($cfg['user_agents'], $userAgent, 'contem')) {
        bloquear($cfg);
    }
}

$ip = obterIpReal();

if (!empty($cfg['ips'])) {
    if (verificarLista($cfg['ips'], $ip)) {
        bloquear($cfg);
    }
}

if ((int)$cfg['consultar_ip'] === 1) {
    $paisesPermitidos = [];
    $paisesRaw = trim($cfg['paises_permitidos'] ?? '');
    if (!empty($paisesRaw) && $paisesRaw !== '0') {
        $paisesPermitidos = array_map('strtoupper', array_map('trim', explode(',', $paisesRaw)));
        $paisesPermitidos = array_filter($paisesPermitidos);
    }
    $paisUsuario = isset($_SERVER['HTTP_CF_IPCOUNTRY']) ? strtoupper($_SERVER['HTTP_CF_IPCOUNTRY']) : '';
    if (empty($paisUsuario) || $paisUsuario === 'XX' || $paisUsuario === 'T1') {
        $ipInfo = consultarIpInfo($ip);
        if ($ipInfo) {
            $paisUsuario = strtoupper($ipInfo['countryCode'] ?? '');
            if (!empty($cfg['asns']) && isset($ipInfo['as'])) {
                $asNumber = preg_replace('/[^0-9]/', '', $ipInfo['as']);
                if (verificarLista($cfg['asns'], $asNumber)) {
                    bloquear($cfg);
                }
            }
            if (!empty($cfg['isps']) && isset($ipInfo['isp'])) {
                if (verificarLista($cfg['isps'], $ipInfo['isp'], 'contem')) {
                    bloquear($cfg);
                }
            }
            if (isset($ipInfo['proxy']) && $ipInfo['proxy']) {
                bloquear($cfg);
            }
        } else {
            bloquear($cfg);
        }
    }
    if (!empty($paisesPermitidos) && ($paisUsuario === '' || !in_array($paisUsuario, $paisesPermitidos, true))) {
        bloquear($cfg);
    }
}