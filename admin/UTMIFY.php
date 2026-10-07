<?php
   session_start();
   
   if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
       header("Location: ./");
       exit;
   }
   include 'config.php';
   
   if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['acao'])) {
   
       $id = (int) $_POST['id'];
   
       if ($_POST['acao'] === 'deletar') {
           if ($conn->query("DELETE FROM utmify WHERE id = $id")) {
               echo '
               <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
               <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
               <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
               <script>
                   $(function() {
                       toastr.options.timeOut = false;
                       toastr.options.closeButton = true;
                       toastr.options.positionClass = "toast-top-right";
                       toastr.success("UTMIFY removida com sucesso!");
                       setTimeout(function() {
                           window.location.href = "?COMON=/ADMIN&acesso=UTMIFY";
                       }, 2000);
                   });
               </script>';
           }
       }
   
       if ($_POST['acao'] === 'alterar' && isset($_POST['usar'])) {
           $usar = $_POST['usar'] === '1' ? 1 : 0;
           if ($conn->query("UPDATE utmify SET usar = $usar WHERE id = $id")) {
               $mensagem = $usar ? "UTMIFY ativada com sucesso!" : "UTMIFY desativada com sucesso!";
               echo '
               <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
               <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
               <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
               <script>
                   $(function() {
                       toastr.options.timeOut = false;
                       toastr.options.closeButton = true;
                       toastr.options.positionClass = "toast-top-right";
                       toastr.success("' . $mensagem . '");
                       setTimeout(function() {
                           window.location.href = "?COMON=/ADMIN&acesso=UTMIFY";
                       }, 2000);
                   });
               </script>';
           } else {
               echo '
               <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
               <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
               <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
               <script>
                   $(function() {
                       toastr.options.timeOut = false;
                       toastr.options.closeButton = true;
                       toastr.options.positionClass = "toast-top-right";
                       toastr.error("Erro ao atualizar UTMIFY!");
                   });
               </script>';
           }
       }
   }
   
   if ($_SERVER['REQUEST_METHOD'] === 'POST') {
       $nome = trim($_POST['nome'] ?? '');
       $token = trim($_POST['token'] ?? '');
       if ($nome !== '' && $token !== '') {
           $nome = $conn->real_escape_string($nome);
           $token = $conn->real_escape_string($token);
           if ($conn->query("INSERT INTO utmify (nome, token, usar) VALUES ('$nome', '$token', 1)")) {
               echo '
               <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
               <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
               <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
               <script>
                   $(function() {
                       toastr.options.timeOut = false;
                       toastr.options.closeButton = true;
                       toastr.options.positionClass = "toast-top-right";
                       toastr.success("UTMIFY adicionada com sucesso e ativada!");
                       setTimeout(function() {
                           window.location.href = "?COMON=/ADMIN&acesso=UTMIFY";
                       }, 2000);
                   });
               </script>';
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
               <h1>Olá  admin, você está em  / UTMIFY</h1>
               <span>TXT_JPGI1 - @TXT_JPGI1 <== no telegram!  </span>
            </div>
         </div>
      </div>
      <h3>UTMIFY</h3>
      <form method="post" action="">
         <div class="col-12 col-md-9">
            <label>Nome</label>
            <input class="form-control" type="text" name="nome" required>
         </div>
         <br>
         <div class="col-12 col-md-9">
            <label>API Token</label>
            <input class="form-control" type="text" name="token" required>
         </div>
         <br>
         <button type="submit" class="btn btn-primary btn-sm">Adicionar</button>
      </form>
      <br>
      <?php
         include 'config.php';
         $result = $conn->query("SELECT * FROM UTMIFY ORDER BY id DESC");
         ?>
      <div class="col-lg-12">
         <div class="card">
            <div class="card-header">
               <strong class="card-title">UTMIFY Cadastrados</strong>
            </div>
            <div class="card-body">
               <div class="table-responsive">
                  <table class="table mb-0">
                     <thead>
                        <tr>
                           <!--<th>ID</th>-->
                           <th>Nome</th>
                           <th>Token</th>
                           <th>Usar</th>
                           <th>Ações</th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($cc = $result->fetch_assoc()): ?>
                        <?php $usar = isset($cc['usar']) && $cc['usar'] == 1 ? 1 : 0; ?>
                        <tr>
                           <!--<td></td>-->
                           <td><?= htmlspecialchars($cc['nome']) ?></td>
                           <td><?= htmlspecialchars($cc['token']) ?></td>
                           <td>
                              <form method="post" action="?COMON=/ADMIN&acesso=UTMIFY">
                                 <input type="hidden" name="id" value="<?= (int)$cc['id'] ?>">
                                 <input type="hidden" name="acao" value="alterar">
                                 <select name="usar"
                                    class="form-control form-control-sm"
                                    onchange="this.form.submit()">
                                    <option value="1" <?= $usar === 1 ? 'selected' : '' ?>>Sim</option>
                                    <option value="0" <?= $usar === 0 ? 'selected' : '' ?>>Não</option>
                                 </select>
                              </form>
                           </td>
                           <td>
                              <form method="post"
                                 action="?COMON=/ADMIN&acesso=UTMIFY"
                                 class="d-inline">
                                 <input type="hidden" name="id" value="<?= (int)$cc['id'] ?>">
                                 <input type="hidden" name="acao" value="deletar">
                                 <button type="submit"
                                    class="btn btn-sm btn-default"
                                    title="Deletar">
                                 <i class="fa fa-trash"></i>
                                 </button>
                              </form>
                           </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php endif; ?>
                     </tbody>
                  </table>
               </div>
            </div>
         </div>
      </div>