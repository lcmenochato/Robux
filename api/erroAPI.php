<?php
error_reporting(0);
ini_set('display_errors', 0);

function erroAPI($mensagemErro, $arquivoLog = "erros_api.txt") {
    //http_response_code(500);
    $log = "[" . date("Y-m-d H:i:s") . "] " . $mensagemErro . PHP_EOL;
    file_put_contents($arquivoLog, $log, FILE_APPEND);
    echo json_encode([
        "sucesso" => false,
        "erro" => "Erro interno ao processar a solicitação."
    ]);

    exit;
}