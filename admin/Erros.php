<?php
   session_start();
   ini_set('display_errors', 1);
   ini_set('display_startup_errors', 1);
   error_reporting(E_ALL);
   date_default_timezone_set('America/Sao_Paulo');
   
   require_once 'config.php';
   
   if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
       header("Location: ./");
       exit;
   }
   
   $erros = [];
   $agora = new DateTime();
   $limiteMinutos = 15;
   
   $sql = "SELECT * FROM erros_pagamento";
   $res = $conn->query($sql);
   
   if ($res && $res->num_rows > 0) {
       while ($row = $res->fetch_assoc()) {
           $id     = $row['id'];
           $motivo = $row['motivo'];
           $visto  = (int)$row['visto'];
           $data   = $row['data'];
           $mostrar = false;
           if ($visto === 0 || empty($data)) {
               $mostrar = true;
               $stmt = $conn->prepare("UPDATE erros_pagamento SET visto = 1, data = ? WHERE id = ?");
               $dataAtual = $agora->format('Y-m-d H:i:s');
               $stmt->bind_param("si", $dataAtual, $id);
               $stmt->execute();
               $stmt->close();
           } else {
               $dataErro = DateTime::createFromFormat('Y-m-d H:i:s', $data);
   
               if ($dataErro) {
                   $intervalo = $agora->getTimestamp() - $dataErro->getTimestamp();
                   $minutosPassados = $intervalo / 60;
   
                   if ($minutosPassados <= $limiteMinutos) {
                       $mostrar = true;
                   }
               }
           }
   
           if ($mostrar) {
               $erros[] = $motivo;
           }
       }
   }
   ?>
<!doctype html>
<html lang="pt-br" data-theme="dark">
   <?php include('head.php'); ?>
   <body data-theme="light">
      <div id="body" class="theme-cyan">
      <?php include('theme.php'); ?>
      <div class="overlay"></div>
      <div id="wrapper">
      <?php include('submenu.php'); ?>
      <?php include('menu.php'); ?>
      <div id="main-content">
      <div class="container-fluid">
      <div class="block-header">
         <div class="row clearfix">
            <div class="col-lg-4 col-md-12 col-sm-12">
               <h1>Olá  admin, você está em  / Erros de Pagamentos</h1>
               <span>TXT_JPGI1 - @TXT_JPGI1 <== no telegram!  </span>
            </div>
         </div>
      </div>
      <?php if (count($erros) > 0): ?>
      <?php foreach ($erros as $erro): ?>
      <span id="erros_de_pagamento_conteudo" class="form-control"><?php echo htmlspecialchars($erro, ENT_QUOTES, 'UTF-8'); ?> <br>Data: HOJE</span>
      <p>
         <?php endforeach; ?>
         <?php else: ?>
      <div id="erros_de_pagamento_conteudo" class="form-control">Nenhum erro de pagamento encontrado.</div>
      <?php endif; ?>