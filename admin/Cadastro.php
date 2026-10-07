<?php
   session_start();
   
   if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
       header("Location: ./");
       exit;
   }
   require_once "config.php";
   
   $id = 1;
   
   if (isset($_POST['atualizar_email'])) {
   
       $email = trim($_POST['email']);
   
       if (!empty($email)) {
   
           $sql = "UPDATE users SET email = ? WHERE id = ?";
           $stmt = $conn->prepare($sql);
           $stmt->bind_param("si", $email, $id);
   
           if ($stmt->execute()) {
   
               echo '
   <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
   <script>
   $(function(){
       toastr.options.timeOut = false;
       toastr.options.closeButton = true;
       toastr.options.positionClass = "toast-top-right";
       toastr.success("Email atualizado com sucesso!");
       setTimeout(function(){
           window.location.href = "?COMON=/ADMIN&acesso=Cadastro";
       },2000);
   });
   </script>';
           }
       }
   }
   
   if (isset($_POST['atualizar_whatsapp'])) {
   
       $whatsapp = trim($_POST['whatsapp']);
   
       if (!empty($whatsapp)) {
   
           $sql = "UPDATE users SET WhatsApp = ? WHERE id = ?";
           $stmt = $conn->prepare($sql);
           $stmt->bind_param("si", $whatsapp, $id);
   
           if ($stmt->execute()) {
   
               echo '
   <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
   <script>
   $(function(){
       toastr.options.timeOut = false;
       toastr.options.closeButton = true;
       toastr.options.positionClass = "toast-top-right";
       toastr.success("WhatsApp atualizado com sucesso!");
       setTimeout(function(){
           window.location.href = "?COMON=/ADMIN&acesso=Cadastro";
       },2000);
   });
   </script>';
           }
       }
   }
   
   if (isset($_POST['atualizar_senha'])) {
   
       $nova_senha = trim($_POST['nova_senha']);
   
       if (!empty($nova_senha)) {
   
           $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
   
           $sql = "UPDATE users SET password = ? WHERE id = ?";
           $stmt = $conn->prepare($sql);
           $stmt->bind_param("si", $senha_hash, $id);
   
           if ($stmt->execute()) {
   
               echo '
   <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
   <script>
   $(function(){
       toastr.options.timeOut = false;
       toastr.options.closeButton = true;
       toastr.options.positionClass = "toast-top-right";
       toastr.success("Senha atualizada com sucesso!");
       setTimeout(function(){
           window.location.href = "?COMON=/ADMIN&acesso=Cadastro";
       },2000);
   });
   </script>';
           }
       }
   }
   
   $id = 1;
   
   $sql = "SELECT * FROM users WHERE id = ?";
   $stmt = $conn->prepare($sql);
   $stmt->bind_param("i", $id);
   $stmt->execute();
   $result = $stmt->get_result();
   $user = $result->fetch_assoc();
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
                     <h1>Olá  admin, você está em  / Cadastro</h1>
                     <span>TXT_JPGI1 - @TXT_JPGI1 <== no telegram!  </span>
                  </div>
               </div>
            </div>
            <div class="block">
               <div class="title">
                  <strong class="d-block">Cadastro</strong>
               </div>
               <br>
               <div class="block-body">
                  <div class="row">
                     <div class="col-md-6 form-group">
                        <label class="form-control-label">Usuário</label>
                        <span class="form-control"><?php echo htmlspecialchars($user['username']); ?></span>
                     </div>
                     <div class="col-md-6 form-group">
                        <label class="form-control-label">Situação</label>
                        <span class="form-control"><?php echo htmlspecialchars($user['situacao']); ?></span>
                     </div>
                     <div class="col-md-6 form-group">
                        <label class="form-control-label">Início</label>
                        <span class="form-control"><?php echo htmlspecialchars($user['inicio']); ?></span>
                     </div>
                     <div class="col-md-6 form-group">
                        <label class="form-control-label">Vencimento</label>
                        <span class="form-control"><?php echo htmlspecialchars($user['vencimento']); ?></span>
                     </div>
                     <div class="col-md-6 form-group">
                        <label class="form-control-label">Status do Servidor</label>
                        <span class="form-control"><?php echo htmlspecialchars($user['status']); ?></span>
                     </div>
                  </div>
                  <hr>
                  <form method="POST" action="">
                     <!--<div class="form-group">
                        <label class="form-control-label">Email</label>
                        <div class="input-group">
                            <input type="text" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>">
                            <div class="input-group-append">
                                <button type="submit" name="atualizar_email" class="btn btn-primary btn-sm">Alterar</button>
                            </div>
                        </div>
                        </div>
                        
                        <div class="form-group">
                        <label class="form-control-label">WhatsApp</label>
                        <div class="input-group">
                            <input type="text" name="whatsapp" class="form-control" value="<?php echo htmlspecialchars($user['WhatsApp']); ?>">
                            <div class="input-group-append">
                                <button type="submit" name="atualizar_whatsapp" class="btn btn-primary btn-sm">Alterar</button>
                            </div>
                        </div>
                        </div>-->
                     <div class="form-group">
                        <label class="form-control-label">Nova Senha</label>
                        <div class="input-group">
                           <input type="password" name="nova_senha" class="form-control">
                           <div class="input-group-append">
                              <button type="submit" name="atualizar_senha" class="btn btn-primary btn-sm">Alterar</button>
                           </div>
                        </div>
                     </div>
                  </form>
               </div>
            </div>
            <hr>
            <div class="block bg-black p-3">
               <div class="title mb-2"><strong>Suporte</strong></div>
               <div class="row">
                  <div class="col-md-6">
                     <small class="text-muted d-block">Telegran para contato</small>
                     <strong id="telegram_do_txt">@TXT_JPGI1</strong>
                  </div>
               </div>
            </div>
         </div>
      </div>