<?php
   session_start();
   
   if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
       header("Location: ./");
       exit;
   }
   
   require_once "config.php";
   
   $desconto_pix = "";
   $desconto_boleto = "";
   $desconto_cartao = "0";
   $parcelas = "";
   $id = 1;
   
   function sucesso($msg) {
       return '
       <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>
       <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
       <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
       <script>
           $(function() {
               toastr.options.timeOut = 2000;
               toastr.options.closeButton = true;
               toastr.options.positionClass = "toast-top-right";
               toastr["success"]("' . addslashes($msg) . '");
               setTimeout(function() {
                   window.location.href = "?COMON=/ADMIN&acesso=Descontos";
               }, 2000);
           });
       </script>';
   }
   
   if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   
       if (isset($_POST['delete']) && is_numeric($_POST['delete'])) {
           $deleteId = (int) $_POST['delete'];
           $stmt = $conn->prepare("DELETE FROM descontos WHERE id = ?");
           if ($stmt) {
               $stmt->bind_param("i", $deleteId);
               $stmt->execute();
               $stmt->close();
           }
           echo sucesso("Desconto removido com sucesso!");
       }
   
       if (
           isset($_POST['desconto_pix']) &&
           isset($_POST['desconto_boleto']) &&
           isset($_POST['parcelas'])
       ) {
           $pix = trim($_POST['desconto_pix']);
           $boleto = trim($_POST['desconto_boleto']);
           $parc = trim($_POST['parcelas']);
   
           $stmt = $conn->prepare("INSERT INTO descontos (id, desconto_pix, desconto_boleto, desconto_cartao, parcelas) VALUES (1, ?, ?, '0', ?) ON DUPLICATE KEY UPDATE desconto_pix = VALUES(desconto_pix), desconto_boleto = VALUES(desconto_boleto), parcelas = VALUES(parcelas)");
   
           if ($stmt) {
               $stmt->bind_param("sss", $pix, $boleto, $parc);
               $stmt->execute();
               $stmt->close();
           }
   
           echo sucesso("Descontos salvos com sucesso!");
       }
   }
   
   $stmt = $conn->prepare("SELECT desconto_pix, desconto_boleto, desconto_cartao, parcelas, id FROM descontos WHERE id = 1 LIMIT 1");
   if ($stmt) {
       $stmt->execute();
       $stmt->bind_result($desconto_pix, $desconto_boleto, $desconto_cartao, $parcelas, $id);
       $stmt->fetch();
       $stmt->close();
   }
   
   $result = $conn->query("SELECT * FROM descontos ORDER BY id ASC");
   $total = $result ? $result->num_rows : 0;
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
               <h1>Olá  admin, você está em  / Descontos</h1>
               <span>TXT_JPGI1 - @TXT_JPGI1 <== no telegram!  </span>
            </div>
         </div>
      </div>
      <div class="block">
         <div class="title"><strong class="d-block">Adicionar Descontos</strong></div>
         <div class="block-body">
            <span id="result" class="col-lg-12"></span>
            <form method="post" action="">
               <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
               <div class="form-group">
                  <label class="form-control-label">Desconto PIX (%)</label>
                  <input type="text" name="desconto_pix" required class="form-control"
                     value="<?= htmlspecialchars($desconto_pix) ?>">
               </div>
               <!--<div class="form-group">
                  <label class="form-control-label">Desconto Boleto (%)</label>
                  <input type="text" name="desconto_boleto" required class="form-control"
                     value="<?= htmlspecialchars($desconto_boleto) ?>">
               </div>-->
               <!--  DESATIVADO POR NÃO SER USADO    <div class="form-group">
                  <label class="form-control-label">Desconto Cartão (%)</label>
                  <input type="text" name="desconto_cartao" required class="form-control"
                         value="<?= htmlspecialchars($desconto_cartao) ?>">
                  </div> --->
               <div class="form-group">
                  <label class="form-control-label">Parcelas Máximas</label>
                  <select name="parcelas" id="parcelas" class="form-control" onchange="parcelas(this)">
                     <?php for ($i = 1; $i <= 12; $i++): ?>
                     <option value="<?= $i ?>" <?= ($parcelas == $i ? 'selected' : '') ?>>
                        <?= $i ?>
                     </option>
                     <?php endfor; ?>
                  </select>
               </div>
               <div class="form-group">
                  <input type="submit"
                     value="Cadastrar Descontos"
                     class="btn btn-primary">
               </div>
            </form>
         </div>
      </div>
      <div class="card">
         <div class="card-body">
            <div class="table-responsive">
               <table class="table mb-0">
                  <thead>
                     <tr>
                        <!--<th>ID</th>-->
                        <th>PIX</th>
                        <!--<th>Boleto</th>
                         <th>Cartão</th> -->
                        <th>Parcelas</th>
                        <th>Remover</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php while ($row = $result->fetch_assoc()): ?>
                     <tr>
                        <!--<td></td>-->
                        <td><?= htmlspecialchars($row['desconto_pix']) ?>%</td>
                        <!--<td></td>
                         <td></td> -->
                        <td><?= htmlspecialchars($row['parcelas']) ?></td>
                        <td>
                           <form method="post" class="d-inline">
                              <input type="hidden" name="delete" value="<?= (int)$row['id'] ?>">
                              <button type="submit" class="btn btn-danger btn-sm">
                              Remover
                              </button>
                           </form>
                        </td>
                     </tr>
                     <?php endwhile; ?>
                  </tbody>
               </table>
            </div>
         </div>
      </div>
      <script>
         function copiarLink(link) {
             const tempInput = document.createElement("input");
             tempInput.value = link;
             document.body.appendChild(tempInput);
             tempInput.select();
             document.execCommand("copy");
             document.body.removeChild(tempInput);
             alert("Link copiado");
         }
      </script>
      <script>
         setTimeout(function () {
             var alertBox = document.querySelector('.alert');
             if (alertBox) {
                 alertBox.style.display = 'none';
             }
         }, 10000);
      </script>