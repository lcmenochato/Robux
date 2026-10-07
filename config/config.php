<?php

$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'latam';

$conexao_tipo = 'pdo';

if ($conexao_tipo == 'mysqli') {
    $mysqli_conexao = new mysqli($host, $user, $password, $dbname);
    if ($mysqli_conexao->connect_error) {
        die("Erro de conexão MySQLi: " . $mysqli_conexao->connect_error);
    }
    $conexao = $mysqli_conexao; 
} elseif ($conexao_tipo == 'pdo') {
    try {
        $pdo_conexao = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
        $pdo_conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conexao = $pdo_conexao;
    } catch (PDOException $e) {
        die("Erro de conexão PDO: " . $e->getMessage());
    }
}

?>
