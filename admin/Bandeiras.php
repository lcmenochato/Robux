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
                   window.location.href = "?COMON=/ADMIN&acesso=Bandeiras";
               }, 2000);
           });
       </script>';
   }
   
   if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['del_bandeira'])) {
       $id = intval($_POST['del_bandeira']);
       $stmt = $conn->prepare("DELETE FROM bandeiras WHERE id = ?");
       if ($stmt) {
           $stmt->bind_param("i", $id);
           if ($stmt->execute()) {
               toast("Bandeira deletado com sucesso!");
           } else {
               echo "<p style='color:red;'>Erro ao deletar: " . $stmt->error . "</p>";
           }
           $stmt->close();
       } else {
           echo "<p style='color:red;'>Erro na preparação do DELETE: " . $conn->error . "</p>";
       }
   }
   
   if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bandeiras_bandeira']) && isset($_POST['bandeiras_nome'])) {
   
       $bandeiras_bandeira = $conn->real_escape_string($_POST['bandeiras_bandeira']);
       $bandeiras_nome     = $conn->real_escape_string($_POST['bandeiras_nome']);
       $bandeiras_icone    = $conn->real_escape_string($_POST['bandeiras_icone']);
   
       if ($bandeiras_bandeira === '' || $bandeiras_nome === '') {
           echo "<p style='color:red;'>Erro: preencha todos os campos.</p>";
           exit;
       }
   
       $stmt = $conn->prepare("INSERT INTO bandeiras (bandeira, nome, icone) VALUES (?, ?, ?)");
       $stmt->bind_param("sss", $bandeiras_bandeira, $bandeiras_nome, $bandeiras_icone);
   
       if ($stmt->execute()) {
           toast("bandeira cadastrado com sucesso!");
       } else {
           echo "Erro ao cadastrar bandeira: " . $stmt->error;
       }
   
       $stmt->close();
   }
   
   $sql = "SELECT id, bandeira, nome, icone FROM bandeiras ORDER BY id DESC";
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
               <h1>Olá  admin, você está em  / bandeiras</h1>
               <span>TXT_JPGI1 - @TXT_JPGI1 <== no telegram!  </span>
            </div>
         </div>
      </div>
      <h3>bandeiras</h3>
      <div id="div_novo_bandeira" class="div_size_desktop">
         <form method="POST" action="?COMON=/ADMIN&acesso=bandeiras">
            <div class="form-group">
               <label class="form-control-label">Bandeira</label>
               <input type="text" id="bandeiras_bandeira" name="bandeiras_bandeira" class="form-control" required>
            </div>
            <div class="form-group">
               <label class="form-control-label">Nome</label>
               <input type="text" id="bandeiras_nome" name="bandeiras_nome" class="form-control" required>
            </div>
            <div class="form-group">
               <label class="form-control-label">Ícone ( Opcional )</label>
               <input type="text" id="bandeiras_icone" name="bandeiras_icone" class="form-control">
               <small class="form-text text-muted">Adicione o link da imagem no https://imgur.com</small>
            </div>
            <button type="submit" class="btn btn-primary">Adicionar</button>
         </form>
      </div>
      <br>
      <div class="card">
         <div class="card-header">
            <strong>Bandeiras Total</strong>
         </div>
         <div class="card-body">
            <div class="table-responsive">
               <table class="table mb-0">
                  <thead>
                     <tr>
                        <!--<th>ID</th>-->
                        <th>Bandeira</th>
                        <th>Nome</th>
                        <th>Ícone</th>
                        <th>Ações</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php if ($result->num_rows > 0): ?>
                     <?php while ($row = $result->fetch_assoc()): ?>
                     <tr>
                        <!--<td></td>-->
                        <td><?= htmlspecialchars($row['bandeira']) ?></td>
                        <td><?= htmlspecialchars($row['nome']) ?></td>
                        <td>
                           <img src="<?= htmlspecialchars($row['icone']) ?>"
                              height="40"
                              alt="<?= htmlspecialchars($row['nome']) ?>">
                        </td>
                        <td>
                           <form method="post" class="d-inline">
                              <input type="hidden" name="del_bandeira" value="<?= (int)$row['id'] ?>">
                              <button type="submit" class="btn btn-danger btn-sm">
                              Deletar
                              </button>
                           </form>
                        </td>
                     </tr>
                     <?php endwhile; ?>
                     <?php else: ?>
                     <tr>
                        <td colspan="5" class="text-center">
                           Nenhuma bandeira cadastrada
                        </td>
                     </tr>
                     <?php endif; ?>
                  </tbody>
               </table>
            </div>
         </div>
      </div>