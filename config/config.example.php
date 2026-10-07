<?php
/**
 * Arquivo de Configuração de Exemplo
 * 
 * Copie este arquivo para config.php e configure com suas credenciais
 * 
 * IMPORTANTE: NUNCA commite o arquivo config.php com credenciais reais!
 */

// Configurações do Banco de Dados
$host = 'localhost';           // Host do banco de dados
$user = 'root';                // Usuário do banco de dados
$password = '';                // Senha do banco de dados
$dbname = 'latam';             // Nome do banco de dados

// Tipo de conexão: 'mysqli' ou 'pdo'
$conexao_tipo = 'pdo';

// Conexão MySQLi
if ($conexao_tipo == 'mysqli') {
    $mysqli_conexao = new mysqli($host, $user, $password, $dbname);
    if ($mysqli_conexao->connect_error) {
        die("Erro de conexão MySQLi: " . $mysqli_conexao->connect_error);
    }
    $conexao = $mysqli_conexao; 
} 
// Conexão PDO (Recomendado)
elseif ($conexao_tipo == 'pdo') {
    try {
        $pdo_conexao = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
        $pdo_conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo_conexao->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo_conexao->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $conexao = $pdo_conexao;
    } catch (PDOException $e) {
        die("Erro de conexão PDO: " . $e->getMessage());
    }
}

?>
