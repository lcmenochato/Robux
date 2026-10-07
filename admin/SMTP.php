<?php
   session_start();
   
   if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
       header("Location: ./");
       exit;
   }
   
   require_once 'config.php'; 
   
   if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tipo'], $_POST['id'])) {
       $tipo = $_POST['tipo'];
       $id = (int) $_POST['id'];
   
       if ($tipo === 'DELSMTP') {
           $stmt = $conn->prepare("DELETE FROM smtp WHERE id = ?");
           $stmt->bind_param("i", $id);
   
           if ($stmt->execute()) {
               echo '
               <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>
               <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
               <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
               <script>
                   $(function() {
                       toastr.options.timeOut = false;
                       toastr.options.closeButton = true;
                       toastr.options.positionClass = "toast-top-right";
                       toastr.success("SMTP deletado com sucesso!");
                       setTimeout(function() {
                           window.location.href = "?COMON=/ADMIN&acesso=SMTP";
                       }, 1200);
                   });
               </script>';
           } else {
               echo "Erro ao excluir o smtp.";
           }
   
           $stmt->close();
       }
   }
   
   if (isset($_POST['Cadastrasmtp'])) {
   
       $host    = $_POST['host'];
       $usuario = $_POST['user'];
       $senha   = $_POST['senha'];
       $porta   = $_POST['porta'];
   
       $stmt = $conn->prepare("INSERT INTO smtp (host, usuario, senha, porta) VALUES (?, ?, ?, ?)");
       $stmt->bind_param("ssss", $host, $usuario, $senha, $porta);
   
       if ($stmt->execute()) {
           echo '
   <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
   <script>
       $(function() {
           toastr.options.timeOut = false;
           toastr.options.closeButton = true;
           toastr.options.positionClass = "toast-top-right";
           toastr.success("Conta SMTP cadastrada com sucesso!");
           setTimeout(function() {
               window.location.href = "?COMON=/ADMIN&acesso=SMTP";
           }, 2000);
       });
   </script>';
       }
   }
   
   if (isset($_POST['AtualizaPreco'])) {
   
       $hostsmtp  = $_POST['host'];
       $usersmtp  = $_POST['user'];
       $senhasmtp = $_POST['senha'];
       $portasmtp = $_POST['porta'];
       $ida       = (int)$_POST['id'];
   
       $stmt = $conn->prepare("UPDATE smtp SET host = ?, usuario = ?, senha = ?, porta = ? WHERE id = ?");
       $stmt->bind_param("ssssi", $hostsmtp, $usersmtp, $senhasmtp, $portasmtp, $ida);
       $stmt->execute();
   
       echo '
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
   <script>
   $(function() {
       toastr.options.timeOut = false;
       toastr.options.closeButton = true;
       toastr.options.positionClass = "toast-top-right";
       toastr.success("SMTP alterado com sucesso!");
   });
   </script>
   <meta http-equiv="refresh" content="2;URL=?COMON=/ADMIN&acesso=SMTP">';
   }
   
   if (isset($_POST['check'])) {
   
       $ida        = (int)$_POST['id'];
       $emailtest  = $_POST['emailtest'];
   
       $stmt = $conn->prepare("SELECT * FROM smtp WHERE id = ?");
       $stmt->bind_param("i", $ida);
       $stmt->execute();
       $res = $stmt->get_result();
   
       if ($res->num_rows === 0) {
   
           echo '
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
   <script>
   $(function() {
       toastr.options.timeOut = false;
       toastr.options.closeButton = true;
       toastr.options.positionClass = "toast-top-right";
       toastr.warning("Não foi possível testar!");
   });
   </script>';
       }
   
       $smtp = $res->fetch_assoc();
   
       $hostsm  = $smtp["host"];
       $usersm  = $smtp["usuario"];
       $passsm  = $smtp["senha"];
       $portasm = $smtp["porta"];
   
       require 'vendor/autoload.php';
   
       $mail = new PHPMailer\PHPMailer\PHPMailer(true);
   
       try {
   
           $mail->isSMTP();
           $mail->Host       = $hostsm;
           $mail->SMTPAuth   = true;
           $mail->Username   = $usersm;
           $mail->Password   = $passsm;
           $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
           $mail->Port       = $portasm;
           $mail->setLanguage('pt_br');
   
           $mail->setFrom($usersm, 'SMTP TESTE DE ENVIO');
           $mail->addAddress($emailtest, 'Apache');
   
           $mail->addAttachment('./boletos/02-895563336.pdf');
   
           $mail->isHTML(true);
           $mail->Subject = 'Check SMTP Apache';
           $mail->Body    = 'Seu SMTP está <b>OK!</b>';
           $mail->AltBody = 'Seu SMTP está OK';
   
           $mail->send();
   
           echo '
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
   <script>
   $(function() {
       toastr.options.timeOut = false;
       toastr.options.closeButton = true;
       toastr.options.positionClass = "toast-top-right";
       toastr.success("SMTP enviado com sucesso!");
   });
   </script>
   <meta http-equiv="refresh" content="2;URL=?COMON=/ADMIN&acesso=SMTP">';
   
       } catch (Exception $e) {
   
           echo "<script>alert('Erro ao enviar: {$mail->ErrorInfo}');</script>";
           echo '<meta http-equiv="refresh" content="1;URL=?COMON=/ADMIN&acesso=SMTP">';
       }
   }
   
   $sql = "SELECT id, host, usuario, senha, porta FROM smtp ORDER BY id DESC";
   $result = $conn->query($sql);
   $total  = $result ? $result->num_rows : 0;
   
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
               <h1>Olá  admin, você está em  / SMTP</h1>
               <span>TXT_JPGI1 - @TXT_JPGI1 &lt;== no telegram!</span>
            </div>
         </div>
      </div>
      <div class="card">
         <div class="card-header">
            <strong>Cadastrar Conta SMTP</strong>
         </div>
         <div class="card-body card-block">
            <form action="?COMON=/ADMIN&acesso=SMTP" method="post" name="formu" class="form-horizontal">
               <div class="row form-group">
                  <div class="col col-md-3"><label for="text-input" class=" form-control-label">HOST SMTP</label></div>
                  <div class="col-12 col-md-9"><input type="text" class="form-control" name="host" required="required" placeholder="smtp.provedor.com"><small class="form-text text-muted">ENDEREÇO HOST</small></div>
               </div>
               <div class="row form-group">
                  <div class="col col-md-3"><label for="text-input" class=" form-control-label">USUÁRIO SMTP</label></div>
                  <div class="col-12 col-md-9"><input type="text" class="form-control" name="user" required="required" placeholder="user@provedor.com"><small class="form-text text-muted">NOME DE USUÁRIO </small></div>
               </div>
               <div class="row form-group">
                  <div class="col col-md-3"><label for="text-input" class=" form-control-label">SENHA SMTP</label></div>
                  <div class="col-12 col-md-9"><input type="text" class="form-control" name="senha" required="required" placeholder="xxxxxxx"><small class="form-text text-muted">SENHA USUÁRIO HOST</small></div>
               </div>
               <div class="row form-group">
                  <div class="col col-md-3"><label for="text-input" class=" form-control-label">PORTA SMTP</label></div>
                  <div class="col-12 col-md-9"><input type="text" class="form-control" value="465" name="porta" required="required" placeholder="porta smtp"><small class="form-text text-muted">PORTA SMTP 465, 587 </small></div>
               </div>
               <div class="card-footer">
                  <button type="submit" class="btn btn-primary btn-sm">
                  <i class="fa fa-dot-circle-o"></i> Cadastrar conta SMTP
                  </button>
               </div>
               <input type="hidden" value="1" name="Cadastrasmtp">								
            </form>
         </div>
      </div>
      <div class="card">
         <div class="card-header">
            <strong class="card-title">Contas SMTP Cadastradas</strong>
         </div>
         <div class="card-body">
            <div class="table-responsive">
               <table class="table mb-0">
                  <thead>
                     <tr>
                        <th class="serial">ID</th>
                        <th>HOST</th>
                        <th>Usuário</th>
                        <th>Senha</th>
                        <th>PORTA</th>
                        <th>Salvar Alterações</th>
                        <th>Testar SMTP</th>
                        <th>Deletar</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php if ($result && $result->num_rows > 0): ?>
                     <?php while ($cc = $result->fetch_assoc()): ?>
                     <tr>
                        <form method="post" action="?COMON=/ADMIN&acesso=SMTP">
                           <input type="hidden" name="id" value="<?= $cc['id'] ?>">
                           <input type="hidden" name="AtualizaPreco" value="1">
                           <td class="id"><?= (int)$cc['id'] ?></td>
                           <td><input type="text" class="form-control form-control-sm" name="host" required value="<?= htmlspecialchars($cc['host']) ?>"></td>
                           <td><input type="text" class="form-control form-control-sm" name="user" required value="<?= htmlspecialchars($cc['usuario']) ?>"></td>
                           <td><input type="text" class="form-control form-control-sm" name="senha" required value="<?= htmlspecialchars($cc['senha']) ?>"></td>
                           <td><input type="text" class="form-control form-control-sm" name="porta" required value="<?= htmlspecialchars($cc['porta']) ?>"></td>
                           <td>
                              <button type="submit" class="btn btn-primary btn-sm">Salvar</button>
                           </td>
                        </form>
                        <td>
                           <form method="post" action="?COMON=/ADMIN&acesso=SMTP" class="d-inline">
                              <input type="text" class="form-control form-control-sm mb-1" required name="emailtest" placeholder="email para teste">
                              <input type="hidden" name="id" value="<?= $cc['id'] ?>">
                              <input type="hidden" name="check" value="1">
                              <button type="submit" class="btn btn-primary btn-sm">Testar</button>
                           </form>
                        </td>
                        <td>
                           <form method="post" class="d-inline">
                              <input type="hidden" name="tipo" value="DELSMTP">
                              <input type="hidden" name="id" value="<?= $cc['id'] ?>">
                              <button type="submit" class="btn btn-sm btn-default" title="Deletar">
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