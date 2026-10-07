<?php
session_start();
   
   if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
       header("Location: ./");
       exit;
   }
   
   require_once "config.php";
   
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
                   window.location.href = "?COMON=/ADMIN&acesso=Tarifas";
               }, 2000);
           });
       </script>';
   }
   
   if ($_SERVER['REQUEST_METHOD'] === 'POST') {
       if (isset($_POST['tarifas']) && is_array($_POST['tarifas'])) {
           $stmt = $conn->prepare("UPDATE tarifas SET acrescimo = ? WHERE id = ?");
           if ($stmt) {
               foreach ($_POST['tarifas'] as $id => $acrescimo) {
                   $id = (int) $id;
                   $acrescimo = floatval(str_replace(',', '.', $acrescimo));
                   $stmt->bind_param("di", $acrescimo, $id);
                   $stmt->execute();
               }
               $stmt->close();
           }
           echo sucesso("Tarifas atualizadas com sucesso!");
       }
   }
   
   $result = $conn->query("SELECT id, titulo, slug, acrescimo FROM tarifas ORDER BY id ASC");
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
               <h1>Olá  admin, você está em  / Tarifas</h1>
               <span>TXT_JPGI1 - @TXT_JPGI1 <== no telegram!  </span>
            </div>
         </div>
      </div>
      <div class="block">
         <div class="title"><strong class="d-block">Editar Tarifas</strong></div>
         <div class="block-body">
            <span id="result" class="col-lg-12"></span>
            <form method="post" action="">
               <div class="card">
                  <div class="card-body">
                     <div class="table-responsive">
                        <table class="table mb-0">
                           <thead>
                              <tr>
                                 <!--<th>ID</th>-->
                                 <th>Título</th>
                                 <th></th>
                                 <th>Acréscimo (%)</th>
                              </tr>
                           </thead>
                           <tbody>
                              <?php while ($row = $result->fetch_assoc()): ?>
                              <tr>
                                 <!--<td></td>-->
                                 <td><?= htmlspecialchars($row['titulo']) ?></td>
                                 <td></td>
                                 <td>
                                    <input type="text" step="0.01" min="0" name="tarifas[<?= (int)$row['id'] ?>]" value="<?= htmlspecialchars($row['acrescimo']) ?>" class="form-control" style="max-width: 120px;"></td>
                              </tr>
                              <?php endwhile; ?>
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
               <div class="form-group mt-3">
                  <input type="submit" value="Salvar Tarifas" class="btn btn-primary">
               </div>
            </form>
         </div>
      </div>
      <script>
         setTimeout(function () {
             var alertBox = document.querySelector('.alert');
             if (alertBox) {
                 alertBox.style.display = 'none';
             }
         }, 10000);
      </script>
      </div>
      </div>
      </div>
      </div>
   </body>
</html>