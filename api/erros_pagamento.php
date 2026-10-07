<?php
require_once "../config/config.php";
//require_once("erroAPI.php");
if (!isset($conexao)) {
    die('❌ Conexão não encontrada.');
    exit;
}

date_default_timezone_set('America/Sao_Paulo');

function salvarErro($mensagem) {
        global $conexao;
        try {
            $dataBrasil = date('Y-m-d H:i:s');
            $sql = "INSERT INTO erros_pagamento (motivo, data) VALUES (:motivo, :data)";
            $stmt = $conexao->prepare($sql);
            $stmt->bindParam(':motivo', $mensagem);
            $stmt->bindParam(':data', $dataBrasil);
            $stmt->execute();
        } catch (PDOException $e) {
            erroAPI("Erro ao salvar no banco: " . $e->getMessage());
        }
}
