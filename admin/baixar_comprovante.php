<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ./");
    exit;
}
if (isset($_GET['acesso']) && $_GET['acesso'] === 'baixar_comprovante') {

    if (!isset($_GET['file']) || empty($_GET['file'])) {
        exit('Arquivo inválido');
    }

    $url = $_GET['file'];

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        exit('URL inválida');
    }

    $conteudo = @file_get_contents($url);
    if ($conteudo === false) {
        exit('Não foi possível baixar o arquivo');
    }

    $nome = basename(parse_url($url, PHP_URL_PATH));
    if (!$nome) {
        $nome = 'comprovante.png';
    }

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $nome . '"');
    header('Content-Length: ' . strlen($conteudo));
    echo $conteudo;
    exit;
}

