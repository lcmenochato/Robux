<?php
$comon = $_GET['COMON'] ?? '';
$acesso = $_GET['acesso'] ?? '';

if ($comon === '/ADMIN') {
    if (empty($acesso)) {
        include 'ADMIN.php';
        exit;
    }

    $arquivo = __DIR__ . '/' . basename($acesso) . '.php';

    if (file_exists($arquivo)) {
        include $arquivo;
        exit;
    } else {
        include __DIR__ . '/../erro.php';
    exit;
    }
}

echo "Rota inválida.";

?>
