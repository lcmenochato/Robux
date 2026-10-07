<?php
   session_start();
   
   if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
       header("Location: ./");
       exit;
   }
   include 'config.php';
   
   if (isset($_POST['AtualizaPreco'])) {
       $instance_id = $_POST['instance_id'];
       $token = $_POST['token'];
       $token_seguranca = $_POST['token_seguranca'];
       $ida = (int)$_POST['id'];
   
       $stmt = $conn->prepare("UPDATE token_zap SET instance_id = ?, token = ?, token_seguranca = ? WHERE id = ?");
       $stmt->bind_param("sssi", $instance_id, $token, $token_seguranca, $ida);
       $stmt->execute();
   
       echo '
               <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>
               <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
               <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
               <script>
                   $(function() {
                       toastr.options.timeOut = false;
                       toastr.options.closeButton = true;
                       toastr.options.positionClass = "toast-top-right";
                       toastr.success("Token alterado com sucesso!");
                       setTimeout(function() {
                           window.location.href = "?COMON=/ADMIN&acesso=Whatsapp";
                       }, 1200);
                   });
               </script>';
   }
   
   if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tipo'], $_POST['id'])) {
       $tipo = $_POST['tipo'];
       $id = (int) $_POST['id'];
   
       if ($tipo === 'DELZAP') {
           $stmt = $conn->prepare("DELETE FROM token_zap WHERE id = ?");
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
                       toastr.success("Token deletado com sucesso!");
                       setTimeout(function() {
                           window.location.href = "?COMON=/ADMIN&acesso=Whatsapp";
                       }, 1200);
                   });
               </script>';
           } else {
               echo "Erro ao excluir o token.";
           }
   
           $stmt->close();
       }
   }
   
   if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check'])) {
       $ida = (int)$_POST['id'];
       $numero_teste = preg_replace('/\D/', '', $_POST['zaptest']); 
   
       $stmt = $conn->prepare("SELECT * FROM token_zap WHERE id = ?");
       $stmt->bind_param("i", $ida);
       $stmt->execute();
       $res = $stmt->get_result();
   
       if ($res->num_rows === 0) {
           echo '<script>alert("Não foi possível testar! Token não encontrado.");</script>';
           exit;
       }
   
       $zap = $res->fetch_assoc();
       $instance_id = $zap['instance_id'];
       $token = $zap['token'];
       $token_seguranca = $zap['token_seguranca'];
   
       $mensagem1 = "Mensagem de teste: seu token do WhatsApp está funcionando!";
   
       $url_msg = "https://api.z-api.io/instances/$instance_id/token/$token/send-text";
   
       $data_msg = [
           "phone" => $numero_teste,
           "message" => $mensagem1
       ];
   
       $payload_msg = json_encode($data_msg);
   
       $ch_msg = curl_init($url_msg);
       curl_setopt($ch_msg, CURLOPT_HTTPHEADER, [
           'Content-Type: application/json',
           "Client-Token: $token_seguranca"
       ]);
       curl_setopt($ch_msg, CURLOPT_POST, true);
       curl_setopt($ch_msg, CURLOPT_POSTFIELDS, $payload_msg);
       curl_setopt($ch_msg, CURLOPT_RETURNTRANSFER, true);
   
       $response_msg = curl_exec($ch_msg);
   $httpCode_msg = curl_getinfo($ch_msg, CURLINFO_HTTP_CODE);
   curl_close($ch_msg);
   
   if ($httpCode_msg == 200) {
       echo '
       <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>
       <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
       <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
       <script>
       $(function() {
           toastr.options.timeOut = false;
           toastr.options.closeButton = true;
           toastr.options.positionClass = "toast-top-right";
           toastr.success("Mensagem enviada com sucesso!");
           setTimeout(function() {
               window.location.href = "?COMON=/ADMIN&acesso=Whatsapp";
           }, 2000);
       });
       </script>';
   } else {
   
       $response_msg_js = addslashes($response_msg);
   
       echo '
       <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>
       <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
       <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
       <script>
           $(function() {
               toastr.options.timeOut = 7000;
               toastr.options.closeButton = true;
               toastr.options.positionClass = "toast-top-right";
               toastr.error("Erro ao enviar mensagem. Resposta da API: ' . $response_msg_js . '");
               setTimeout(function() {
                   window.location.href = "?COMON=/ADMIN&acesso=Whatsapp";
               }, 7000);
           });
       </script>';
       }
   }
   
   
   if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['check']) && isset($_POST['instance_id'], $_POST['token'])) {
       $instance_id = $_POST['instance_id'];
       $token = $_POST['token'];
       $token_seguranca = $_POST['token_seguranca'] ?? null;
   
       $sql = "INSERT INTO token_zap (id, instance_id, token, token_seguranca) VALUES (1, ?, ?, ?)
               ON DUPLICATE KEY UPDATE
                   instance_id = VALUES(instance_id),
                   token = VALUES(token),
                   token_seguranca = VALUES(token_seguranca)";
       $stmt = $conn->prepare($sql);
       $stmt->bind_param("sss", $instance_id, $token, $token_seguranca);
       $stmt->execute();
       $stmt->close();
   
       echo '
   <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
   <script>
   $(function() {
       toastr.options.timeOut = false;
       toastr.options.closeButton = true;
       toastr.options.positionClass = "toast-top-right";
       toastr.success("Token Whatsapp Adicionado com sucesso!");
       setTimeout(function() {
           window.location.href = "?COMON=/ADMIN&acesso=Whatsapp";
       }, 2000);
   });
   </script>';
   }
   
   $sql = "SELECT id, instance_id, token, token_seguranca FROM token_zap ORDER BY id DESC";
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
               <h1>Olá  admin, você está em  / Whatsapp</h1>
               <span>TXT_JPGI1 - @TXT_JPGI1 &lt;== no telegram!</span>
            </div>
         </div>
      </div>
      <div class="card">
         <div class="card-header">
            <strong>Cadastrar Conta Whatsapp</strong>
         </div>
         <div class="card-body card-block">
            <form action="?COMON=/ADMIN&acesso=Whatsapp" method="post" name="formu" class="form-horizontal">
               <div class="row form-group">
                  <div class="col col-md-3"><label for="text-input" class=" form-control-label">Instance ID ou Endpoint</label></div>
                  <div class="col-12 col-md-9"><input type="text" class="form-control" name="instance_id" required="required"></div>
               </div>
               <div class="row form-group">
                  <div class="col col-md-3"><label for="text-input" class=" form-control-label">Token</label></div>
                  <div class="col-12 col-md-9"><input type="text" class="form-control" name="token" required="required"></div>
               </div>
               <div class="row form-group">
                  <div class="col col-md-3"><label for="text-input" class=" form-control-label">Token de segurança (Z-API)</label></div>
                  <div class="col-12 col-md-9"><input type="text" class="form-control" name="token_seguranca" required="required"></div>
               </div>
               <div class="card-footer">
                  <button type="submit" class="btn btn-primary btn-sm">
                  <i class="fa fa-dot-circle-o"></i> Adicionar
                  </button>
               </div>
               <input type="hidden" value="1" name="Cadastrazap">								
            </form>
         </div>
      </div>
      <div class="card">
         <div class="card-header"></div>
         <div class="card-body">
            <div class="table-responsive">
               <table class="table mb-0">
                  <thead>
                     <tr>
                        <th class="serial">ID</th>
                        <th>INSTANCE ID</th>
                        <th>TOKEN</th>
                        <th>TOKEN DE SEGURANÇA</th>
                        <th>Salvar Alterações</th>
                        <th>Testar Whatsapp</th>
                        <th>Deletar</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php if ($result && $result->num_rows > 0): ?>
                     <?php while ($cc = $result->fetch_assoc()): ?>
                     <tr>
                        <form method="post" action="?COMON=/ADMIN&acesso=Whatsapp">
                           <input type="hidden" name="id" value="<?= $cc['id'] ?>">
                           <input type="hidden" name="AtualizaPreco" value="1">
                           <td class="id"><?= (int)$cc['id'] ?></td>
                           <td><input type="text" class="form-control form-control-sm" name="instance_id" required value="<?= htmlspecialchars($cc['instance_id']) ?>"></td>
                           <td><input type="text" class="form-control form-control-sm" name="token" required value="<?= htmlspecialchars($cc['token']) ?>"></td>
                           <td><input type="text" class="form-control form-control-sm" name="token_seguranca" required value="<?= htmlspecialchars($cc['token_seguranca']) ?>"></td>
                           <td>
                              <button type="submit" class="btn btn-primary btn-sm">Salvar</button>
                           </td>
                        </form>
                        <td>
                           <form method="post" action="?COMON=/ADMIN&acesso=Whatsapp" class="d-inline">
                              <input type="text" class="form-control form-control-sm mb-1" required name="zaptest" placeholder="ex: 5563928676928">
                              <input type="hidden" name="id" value="<?= $cc['id'] ?>">
                              <input type="hidden" name="check" value="1">
                              <button type="submit" class="btn btn-primary btn-sm">Testar</button>
                           </form>
                        </td>
                        <td>
                           <form method="post" class="d-inline">
                              <input type="hidden" name="tipo" value="DELZAP">
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