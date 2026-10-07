<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'config/config.php';

$ip_atual = $_SERVER['HTTP_CF_CONNECTING_IP']?? $_SERVER['HTTP_X_FORWARDED_FOR']?? $_SERVER['REMOTE_ADDR']?? '';

$ip_atual = explode(',', $ip_atual)[0];
$ip_atual = trim($ip_atual);

$stmt = $conexao->prepare("SELECT 1 FROM acesso_ip WHERE ip = :ip AND bloqueado = 1 LIMIT 1");

$stmt->bindParam(':ip', $ip_atual, PDO::PARAM_STR);
$stmt->execute();

if ($stmt->fetch()) {
    include 'erro.php';
    exit;
}
