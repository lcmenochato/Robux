<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/api/erroAPI.php';

$uriPath = strtok($_SERVER['REQUEST_URI'], '?');
$uriSegments = array_values(array_filter(explode('/', $uriPath), fn($s) => $s !== ''));
$uriSegments = array_map('urldecode', $uriSegments);

$firstSegment = $uriSegments[0] ?? null;
$secondSegment = $uriSegments[1] ?? null;
$queryString = $_SERVER['QUERY_STRING'] ?? '';

function show404() {
    http_response_code(404);
    include __DIR__ . '/erro.php';
    exit;
}

/*

DESATIVADO POR JA USAR O .htaccess.bak
function expirado() {
    include __DIR__ . '/expirado.php';
    exit;
}

*/

$allowedPaths = ['inicio', 'ofertas', 'passageiros', 'pagamento'];

function fullidValidoNoBanco($conexao, $fullId) {
    try {
        $stmt = $conexao->prepare("SELECT * FROM links WHERE fullid = :fullid LIMIT 1");
        $stmt->execute([':fullid' => $fullId]);
        $link = $stmt->fetch(PDO::FETCH_ASSOC);
        return $link ? $link : false;
    } catch (PDOException $e) {
        erroAPI("Erro ao salvar no banco: " . $e->getMessage());
    }
}

if (isset($_GET['utm_source'])) {
    $_SESSION['utm_source'] = $_GET['utm_source'];
    setcookie('utm_source', $_GET['utm_source'], time() + (86400 * 30), "/");
}

if (isset($_GET['utm_campaign'])) {
    $_SESSION['utm_campaign'] = $_GET['utm_campaign'];
    setcookie('utm_campaign', $_GET['utm_campaign'], time() + (86400 * 30), "/");
}

if (isset($_GET['utm_medium'])) {
    $_SESSION['utm_medium'] = $_GET['utm_medium'];
    setcookie('utm_medium', $_GET['utm_medium'], time() + (86400 * 30), "/");
}

if (isset($_GET['utm_content'])) {
    $_SESSION['utm_content'] = $_GET['utm_content'];
    setcookie('utm_content', $_GET['utm_content'], time() + (86400 * 30), "/");
}

if (isset($_GET['utm_term'])) {
    $_SESSION['utm_term'] = $_GET['utm_term'];
    setcookie('utm_term', $_GET['utm_term'], time() + (86400 * 30), "/");
}

if ($firstSegment && preg_match('/^\d{9}$/', $firstSegment)) {
    $fullId = $firstSegment;

    $link = fullidValidoNoBanco($conexao, $fullId);
    if (!$link) {
        show404();
    }

    $_SESSION['fullid'] = $fullId;

    if (is_null($secondSegment)) {
        if ($queryString !== '') {
            include __DIR__ . '/inicio.php';
            exit;
        }

        header('Location: /' . $fullId . '/inicio', true, 302);
        exit;
    }

    if (in_array($secondSegment, $allowedPaths, true)) {
        $map = [
            'inicio' => __DIR__ . '/inicio.php',
            'ofertas' => __DIR__ . '/ofertas.php',
            'passageiros' => __DIR__ . '/passageiros.php',
            'pagamento' => __DIR__ . '/pagamento.php',
        ];

        include $map[$secondSegment];
        exit;
    }

    show404();
} else {
    show404();
}