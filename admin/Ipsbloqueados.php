<?php
   session_start();
   ini_set('display_errors', 1);
   ini_set('display_startup_errors', 1);
   error_reporting(E_ALL);
   
   require_once 'config.php';
   
   if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
       header("Location: ./");
       exit;
   }
   
   if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tipo'], $_POST['id'])) {
   
       $tipo = $_POST['tipo'];
       $id   = (int) $_POST['id'];
   
       if ($tipo === 'Del_ip') {
           $stmt = $conn->prepare("DELETE FROM acesso_ip WHERE id = ?");
           $stmt->bind_param("i", $id);
           $stmt->execute();
           $stmt->close();
   
           $msg = "IP deletado com sucesso!";
       }
   
       if ($tipo === 'bloquear') {
           $stmt = $conn->prepare("UPDATE acesso_ip SET bloqueado = 1 WHERE id = ?");
           $stmt->bind_param("i", $id);
           $stmt->execute();
           $stmt->close();
   
           $msg = "IP bloqueado com sucesso!";
       }
   
       if ($tipo === 'desbloquear') {
       $stmt = $conn->prepare("UPDATE acesso_ip SET bloqueado = 0 WHERE id = ?");
       $stmt->bind_param("i", $id);
       $stmt->execute();
       $stmt->close();
   
       $msg = "IP desbloqueado com sucesso!";
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
               toastr.success("'.$msg.'");
               setTimeout(function() {
                   window.location.href = "?COMON=/ADMIN&acesso=Ipsbloqueados";
               }, 1200);
           });
       </script>';
   }
   
   if (isset($_POST['add_ip'])) {
   
       $ip = trim($_POST['ip']);
       if ($ip === '') exit;
   
       $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
   
       if (stripos($ua, 'mobile') !== false) {
           $dispositivo = 'Mobile';
       } elseif (stripos($ua, 'windows') !== false) {
           $dispositivo = 'Windows';
       } elseif (stripos($ua, 'linux') !== false) {
           $dispositivo = 'Linux';
       } elseif (stripos($ua, 'mac') !== false) {
           $dispositivo = 'Mac';
       } else {
           $dispositivo = 'Não informado';
       }
   
       $origem = 'Não informado';
   
       $url = "http://ip-api.com/json/{$ip}?fields=status,country,regionName,city";
       $ch = curl_init($url);
       curl_setopt_array($ch, [
           CURLOPT_RETURNTRANSFER => true,
           CURLOPT_TIMEOUT => 5
       ]);
       $response = curl_exec($ch);
       curl_close($ch);
   
       if ($response) {
           $data = json_decode($response, true);
           if ($data['status'] === 'success') {
               $origem = trim(
                   ($data['city'] ?? '') . ' / ' .
                   ($data['regionName'] ?? '') . ' / ' .
                   ($data['country'] ?? '')
               );
           }
       }
   
       $stmt = $conn->prepare("
           INSERT INTO acesso_ip 
           (ip, dispositivo, momento, bloqueado, acessou, origem)
           VALUES (?, ?, NOW(), 1, '', ?)
       ");
       $stmt->bind_param("sss", $ip, $dispositivo, $origem);
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
               toastr.success("IP adicionado e bloqueado!");
               setTimeout(function() {
                   window.location.href = "?COMON=/ADMIN&acesso=Ipsbloqueados";
               }, 1500);
           });
       </script>';
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
               <h1>Olá  admin, você está em  / Bloquear IPS</h1>
               <span>TXT_JPGI1 - @TXT_JPGI1 <== no telegram!  </span>
            </div>
         </div>
      </div>
      <h3>Bloquear IPS</h3>
      <span>Evite bloquear muitos ips e apague os antigos de vez em quando.</span>
      <form method="POST" action="">
         <div class="form-group"><br>
            <input type="text" name="ip" id="ips_bloqueados_ip" class="form-control" required>
         </div>
         <br>
         <button type="submit" name="add_ip" class="btn btn-primary btn-sm">
         Adicionar
         </button>
      </form>
      <br><br><br>
      <div class="table-responsive">
         <table class="table mb-0">
            <thead>
               <tr>
                  <th>IP</th>
                  <th>Dispositivo</th>
                  <th>Origem</th>
                  <th>Bloqueado</th>
                  <th>Bloquear</th>
                  <th>Deletar</th>
               </tr>
            </thead>
            <tbody>
               <?php 
                  $q = $conn->query("
                      SELECT a.*
                      FROM acesso_ip a
                      INNER JOIN (
                          SELECT ip, MAX(id) AS max_id
                          FROM acesso_ip
                          GROUP BY ip
                      ) b ON a.ip = b.ip AND a.id = b.max_id
                      ORDER BY a.id DESC
                  ");
                  
                  while ($ip = $q->fetch_assoc()):
                  ?>
               <tr>
                  <td><?= htmlspecialchars($ip['ip'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= ucfirst(htmlspecialchars($ip['dispositivo'])) ?></td>
                  <td><?= $ip['origem'] ? ucfirst(htmlspecialchars($ip['origem'])) : '-' ?></td>
                  <td><?= ((int)$ip['bloqueado'] === 1 ? 'Sim' : 'Não') ?></td>
                  <td>
                     <?php if ((int)$ip['bloqueado'] === 0): ?>
                     <form method="post" class="d-inline">
                        <input type="hidden" name="tipo" value="bloquear">
                        <input type="hidden" name="id" value="<?= (int)$ip['id'] ?>">
                        <button class="btn btn-primary btn-sm" title="Bloquear IP">
                        <i class="fa fa-ban"></i>
                        </button>
                     </form>
                     <?php else: ?>
                     <form method="post" class="d-inline">
                        <input type="hidden" name="tipo" value="desbloquear">
                        <input type="hidden" name="id" value="<?= (int)$ip['id'] ?>">
                        <button class="btn btn-primary btn-sm" title="Desbloquear IP">
                        <i class="fa fa-unlock"></i>
                        </button>
                     </form>
                     <?php endif; ?>
                  </td>
                  <td>
                     <form method="post" class="d-inline">
                        <input type="hidden" name="tipo" value="Del_ip">
                        <input type="hidden" name="id" value="<?= (int)$ip['id'] ?>">
                        <button class="btn btn-primary btn-sm" title="Deletar IP">
                        <i class="fa fa-trash"></i>
                        </button>
                     </form>
                  </td>
               </tr>
               <?php endwhile; ?>
            </tbody>
         </table>
      </div>