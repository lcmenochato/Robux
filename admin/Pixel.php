<?php
   session_start();
   
   if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
       header("Location: ./");
       exit;
   }
   
   include 'config.php';
   
   if (isset($_POST['pixelid'], $_POST['pixel_token'], $_POST['evento_purchase_do_pixel'])) {
       $pixelid = $conn->real_escape_string($_POST['pixelid']);
       $nome = $conn->real_escape_string($_POST['nome']);
       $pixel_token = $conn->real_escape_string($_POST['pixel_token']);
       $evento = $conn->real_escape_string($_POST['evento_purchase_do_pixel']);
   
       $check = $conn->query("SELECT id FROM pixel WHERE id = 1");
   
       if ($check->num_rows > 0) {
           $conn->query("UPDATE pixel SET pixelid='$pixelid', nome='$nome', pixel_token='$pixel_token', evento_purchase_do_pixel='$evento', page_view=1, view_content=1, add_to_cart=1, initiate_checkout=1, purchase=1 WHERE id = 1");
       } else {
           $conn->query("INSERT INTO pixel (id, pixelid, nome, pixel_token, evento_purchase_do_pixel, page_view, view_content, add_to_cart, initiate_checkout, purchase) VALUES (1,'$pixelid', '$nome', '$pixel_token','$evento', 1, 1, 1, 1, 1)");
       }
   
       echo '
   <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
   <script src="https://code.jquery.com/jquery-3.6.0.min.js "></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
   
   <script>
   $(function() {
       toastr.options.timeOut = false;
       toastr.options.closeButton = true;
       toastr.options.positionClass = "toast-top-right";
       toastr["success"]("Pixel adicionada com sucesso!");
       setTimeout(function() {
           window.location.href = "?COMON=/ADMIN&acesso=Pixel";
       }, 7000);
   });
   </script>';
   }
   
   if (isset($_POST['delete'])) {
       $conn->query("DELETE FROM pixel WHERE id = 1");
   
       echo '
   <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
   
   <script>
   $(function() {
       toastr.options.timeOut = false;
       toastr.options.closeButton = true;
       toastr.options.positionClass = "toast-top-right";
       toastr["success"]("Pixel Deletado com sucesso!");
       setTimeout(function() {
           window.location.href = "?COMON=/ADMIN&acesso=Pixel";
       }, 7000);
   });
   </script>';
   }
   
   if (isset($_POST['id']) && !empty($_POST['eventos'])) {
       $id = (int) $_POST['id'];
       $eventos = $_POST['eventos'];
   
       $mapa = [
           'PageView' => 'page_view',
           'ViewContent' => 'view_content',
           'AddToCart' => 'add_to_cart',
           'InitiateCheckout' => 'initiate_checkout',
           'Purchase' => 'purchase'
       ];
   
       $stmt_reset = $conn->prepare("UPDATE pixel SET page_view = 0, view_content = 0, add_to_cart = 0, initiate_checkout = 0, purchase = 0 WHERE id = ?");
       $stmt_reset->bind_param("i", $id);
       $stmt_reset->execute();
   
       foreach ($eventos as $evento) {
           if (isset($mapa[$evento])) {
               $coluna = $mapa[$evento];
               $stmt = $conn->prepare("UPDATE pixel SET `$coluna` = 1 WHERE id = ?");
               $stmt->bind_param("i", $id);
               $stmt->execute();
           }
       }
   
       echo '
   <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
   
   <script>
   $(function() {
       toastr.options.timeOut = 1500;
       toastr.options.closeButton = true;
       toastr.options.positionClass = "toast-top-right";
       toastr.success("Evento do Pixel ativado com sucesso!");
       setTimeout(function() {
           window.location.href = "?COMON=/ADMIN&acesso=Pixel";
       }, 1500);
   });
   </script>';
   }
   
   $pixel = [];
   $res_pixel = $conn->query("SELECT * FROM pixel WHERE id = 1");
   if ($res_pixel && $res_pixel->num_rows > 0) {
       $pixel = $res_pixel->fetch_assoc();
   }
   $evento_purchase = $pixel['evento_purchase_do_pixel'] ?? '';
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
               <h1>Olá admin, você está em / Pixel pixel</h1>
               <span>TXT_JPGI1 - @TXT_JPGI1 <== no telegram!</span>
            </div>
         </div>
      </div>
      <h3>Pixel da Meta(Facebook)</h3>
      <form method="post" action="?COMON=/ADMIN&acesso=Pixel">
         <label>Disparar Evento Purchase</label>
         <select name="evento_purchase_do_pixel" class="form-control" required>
            <option value="Ao Gerar Pedido" <?= ($evento_purchase === 'Ao Gerar Pedido') ? 'selected' : '' ?>>Ao Gerar Pedido</option>
            <option value="Ao Confirmar Pagamento" <?= ($evento_purchase === 'Ao Confirmar Pagamento') ? 'selected' : '' ?>>Ao Confirmar Pagamento</option>
         </select>
         <br>
         <div style="display:flex;gap:15px;">
            <div>
               <label class="form-control-label">Nome</label>
               <input type='text' name="nome" class="form-control" value="<?= htmlspecialchars($pixel['nome'] ?? '') ?>" required>
            </div>
            <div>
               <label class="form-control-label">Pixel ID</label>
               <input type='text' name="pixelid" class="form-control" value="<?= htmlspecialchars($pixel['pixelid'] ?? '') ?>" required>
            </div>
            <div>
               <label class="form-control-label">Pixel Token</label>
               <input type='text' name="pixel_token" class="form-control" value="<?= htmlspecialchars($pixel['pixel_token'] ?? '') ?>" required>
            </div>
         </div>
         <br>
         <button type="submit" class="btn btn-primary btn-sm">Adicionar</button>
      </form>
      <br><br>
      <?php
         $pixeis = [];
         $res = $conn->query("
             SELECT 
                 id,
                 pixelid,
                 nome,
                 pixel_token,
                 evento_purchase_do_pixel,
                 page_view,
                 view_content,
                 add_to_cart,
                 initiate_checkout,
                 purchase
             FROM pixel
             ORDER BY id DESC
         ");
         
         if ($res && $res->num_rows > 0) {
             while ($row = $res->fetch_assoc()) {
                 $pixeis[] = $row;
             }
         }
         ?>
      <?php if(!empty($pixeis)): ?>
      <div class="col-lg-12">
         <div class="card">
            <div class="card-header">
               <strong class="card-title">Pixeis Cadastrados</strong>
            </div>
            <div class="table-stats order-table">
               <div class="table-responsive" style="overflow-x:auto; -webkit-overflow-scrolling: touch;">
                  <table class="table table-striped table-bordered" style="min-width: 1300px; white-space: nowrap;">
                     <thead>
                        <tr>
                           <th>Nome</th>
                           <th>Pixel ID</th>
                           <th>Pixel Token</th>
                           <th>Page View</th>
                           <th>Add To Cart</th>
                           <th>Initiate Checkout</th>
                           <th>Purchase</th>
                           <th>View Content</th>
                           <th>Ações</th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php foreach ($pixeis as $p) { ?>
                        <tr>
                           <td><?= htmlspecialchars($p['nome']) ?></td>
                           <td><?= htmlspecialchars($p['pixelid']) ?></td>
                           <td style="max-width:260px;">
                              <div class="token-pixel"><?= htmlspecialchars($p['pixel_token']) ?></div>
                           </td>
                           <td>
                              <form method="post" action="?COMON=/ADMIN&acesso=Pixel" style="margin:0;">
                                 <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                 <input type="hidden" name="eventos[]" value="PageView" id="pageview_<?= $p['id'] ?>">
                                 <input type="checkbox"class="evento_do_pixel_do_Pixel_2" <?= (($p['page_view'] ?? 0) == 1 ? 'checked' : '') ?> onchange="document.getElementById('pageview_<?= $p['id'] ?>').value = this.checked ? 'PageView' : ''; this.form.submit()">
                              </form>
                           </td>
                           <td>
                              <form method="post" action="?COMON=/ADMIN&acesso=Pixel" style="margin:0;">
                                 <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                 <input type="hidden" name="eventos[]" value="AddToCart" id="addtocart_<?= $p['id'] ?>">
                                 <input type="checkbox" class="evento_do_pixel_do_Pixel_2" <?= (($p['add_to_cart'] ?? 0) == 1 ? 'checked' : '') ?> onchange="document.getElementById('addtocart_<?= $p['id'] ?>').value = this.checked ? 'AddToCart' : ''; this.form.submit()">
                              </form>
                           </td>
                           <td>
                              <form method="post" action="?COMON=/ADMIN&acesso=Pixel" style="margin:0;">
                                 <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                 <input type="hidden" name="eventos[]" value="InitiateCheckout" id="checkout_<?= $p['id'] ?>">
                                 <input type="checkbox" class="evento_do_pixel_do_Pixel_2" <?= (($p['initiate_checkout'] ?? 0) == 1 ? 'checked' : '') ?> onchange="document.getElementById('checkout_<?= $p['id'] ?>').value = this.checked ? 'InitiateCheckout' : ''; this.form.submit()">
                              </form>
                           </td>
                           <td>
                              <form method="post" action="?COMON=/ADMIN&acesso=Pixel" style="margin:0;">
                                 <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                 <input type="hidden" name="eventos[]" value="Purchase" id="purchase_<?= $p['id'] ?>">
                                 <input type="checkbox" class="evento_do_pixel_do_Pixel_2" <?= (($p['purchase'] ?? 0) == 1 ? 'checked' : '') ?> onchange="document.getElementById('purchase_<?= $p['id'] ?>').value = this.checked ? 'Purchase' : ''; this.form.submit()">
                              </form>
                           </td>
                           <td>
                              <form method="post" action="?COMON=/ADMIN&acesso=Pixel" style="margin:0;">
                                 <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                 <input type="hidden" name="eventos[]" value="ViewContent" id="viewcontent_<?= $p['id'] ?>">
                                 <input type="checkbox" class="evento_do_pixel_do_Pixel_2" <?= (($p['view_content'] ?? 0) == 1 ? 'checked' : '') ?> onchange="document.getElementById('viewcontent_<?= $p['id'] ?>').value = this.checked ? 'ViewContent' : ''; this.form.submit()">
                              </form>
                           </td>
                           <td>
                              <form method="post" action="?COMON=/ADMIN&acesso=Pixel" style="display:inline; margin:0;"><input type="hidden" name="delete" value="<?= $p['id'] ?>"><button type="submit" class="btn btn-sm btn-default"><i class="fa fa-trash"></i></button></form>
                           </td>
                        </tr>
                        <?php } ?>
                     </tbody>
                  </table>
               </div>
            </div>
            <?php else: ?>
            <div class="alert alert-info text-center" role="alert"></div>
            <?php endif; ?>
         </div>
      </div>