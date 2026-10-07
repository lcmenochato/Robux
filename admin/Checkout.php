<?php
   session_start();
   require_once 'config.php';
   
   if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
       header("Location: ./");
       exit;
   }
   
   function toast($msg) {
       echo '
       <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>
       <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
       <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
       <script>
           $(function() {
               toastr.options.timeOut = 2000;
               toastr.options.closeButton = true;
               toastr.options.positionClass = "toast-top-right";
               toastr.success("'.$msg.'");
               setTimeout(function() {
                   window.location.href = "?COMON=/ADMIN&acesso=Checkout";
               }, 1500);
           });
       </script>';
   }
   
   if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'alterar') {
       $id = intval($_POST['id']);
       $usar = intval($_POST['usar']);
   
       $stmt = $conn->prepare("UPDATE checkout SET ativo = ? WHERE id = ?");
       if ($stmt) {
           $stmt->bind_param("ii", $usar, $id);
           if ($stmt->execute()) {
               toast("Status atualizado com sucesso!");
           } else {
               echo "Erro ao atualizar";
           }
           $stmt->close();
       }
   }
   
   if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout_link'])) {
   
       $link = trim($_POST['checkout_link']);
       $nome = trim($_POST['name_checkout']);
       $acionamento = trim($_POST['exibicao']);
       $ativo = intval($_POST['ativo']);
       $nova_aba = intval($_POST['nova_aba']);
   
       $pOriginal = 0;
       $pAtual = 0;
   
       $stmt = $conn->prepare("
           INSERT INTO checkout 
           (ativo, link, acionamento, nova_aba, nome, preçoOriginal, preçoAtual)
           VALUES (?, ?, ?, ?, ?, ?, ?)
       ");
   
       $stmt->bind_param(
           "issisdd",
           $ativo,
           $link,
           $acionamento,
           $nova_aba,
           $nome,
           $pOriginal,
           $pAtual
       );
   
       if ($stmt->execute()) {
           toast("Checkout cadastrado com sucesso!");
       }
   
       $stmt->close();
   }
   
   if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_checkout_id'])) {
       $id = intval($_POST['delete_checkout_id']);
   
       $stmt = $conn->prepare("DELETE FROM checkout WHERE id = ?");
       $stmt->bind_param("i", $id);
       if ($stmt->execute()) {
           toast("Checkout deletado com sucesso!");
       }
       $stmt->close();
   }
   
   $exibicao_salva = '';
   
   if (isset($_GET['id'])) {
       $id = intval($_GET['id']);
       $stmt = $conn->prepare("SELECT acionamento FROM checkout WHERE id = ?");
       $stmt->bind_param("i", $id);
       $stmt->execute();
       $stmt->bind_result($exibicao_salva);
       $stmt->fetch();
       $stmt->close();
   }
   
   $checkouts = $conn->query("SELECT * FROM checkout ORDER BY id DESC");
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
            <div class="col-lg-4 col-md-12 col-sm-12">
               <h1>Olá  admin, você está em  / Checkout</h1>
               <span>TXT_JPGI1 - @TXT_JPGI1 <== no telegram! </span>
            </div>
         </div>
      </div>
      <div class="row clearfix">
         <?php
            include 'config.php';
            
            $sql = "SELECT id, link, nome, ativo FROM checkout ORDER BY id DESC";
            $result = $conn->query($sql);
            
            if (!$result) {
                die("Erro na query: " . $conn->error);
            }
            ?>
         <div class="col-lg-12">
            <div class="card">
               <div class="card-header">
                  <strong>Cadastrar Checkout</strong>
               </div>
               <div class="card-body card-block">
                  <form action="" method="post" class="form-horizontal">
                     <div class="row form-group">
                        <div class="col col-md-3">
                           <label class="form-control-label">Link Checkout</label>
                        </div>
                        <div class="col-12 col-md-9">
                           <input required type="text" name="checkout_link" placeholder="https://checkout.com" class="form-control">
                        </div>
                     </div>
                     <div class="row form-group">
                        <div class="col col-md-3">
                           <label class="form-control-label">Onde o Checkout vai aparecer?</label>
                        </div>
                        <div class="col-12 col-md-9">
                           <select name="exibicao" class="form-control" required>
                              <option value="Ao clicar em pedir agora na página inicio"
                                 <?= $exibicao_salva == 'Ao clicar em pedir agora na página inicio' ? 'selected' : '' ?>>
                                 No botão de inicio
                              </option>
                           </select>
                        </div>
                     </div>
                     <div class="row form-group">
                        <div class="col col-md-3">
                           <label class="form-control-label">Nome Checkout (Opcional)</label>
                        </div>
                        <div class="col-12 col-md-9">
                           <input type="text" name="name_checkout" placeholder="MERCADO PAGO" class="form-control">
                        </div>
                     </div>
                     <div class="row form-group">
                        <div class="col col-md-3">
                           <label class="form-control-label">Status</label>
                        </div>
                        <div class="col-12 col-md-9">
                           <select required name="ativo" class="form-control">
                              <option value="1">Ativo</option>
                              <option value="0">Desativado</option>
                           </select>
                        </div>
                     </div>
                     <div class="row form-group">
                        <div class="col col-md-3">
                           <label class="form-control-label">Abrir em nova aba?</label>
                        </div>
                        <div class="col-12 col-md-9">
                           <select required name="nova_aba" class="form-control">
                              <option value="1">Sim</option>
                              <option value="0">Não</option>
                           </select>
                        </div>
                     </div>
                     <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-dot-circle-o"></i> Cadastrar Checkout
                        </button>
                     </div>
                  </form>
               </div>
            </div>
         </div>
      </div>
      <div class="card">
         <div class="card-body">
            <div class="table-responsive">
               <table class="table mb-0">
                  <thead>
                     <tr>
                        <th>Nome Do Checkout</th>
                        <th>Link Do Checkout</th>
                        <th>Usar Checkout</th>
                        <th>Deletar</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php if ($result && $result->num_rows > 0): ?>
                     <?php while ($row = $result->fetch_assoc()): ?>
                     <?php $usar = (int)$row['ativo']; ?>
                     <tr>
                        <td>
                           <?= !empty($row['nome']) ? htmlspecialchars($row['nome']) : '-' ?>
                        </td>
                        <td><?= htmlspecialchars($row['link']) ?></td>
                        <td>
                           <form method="post" action="?COMON=/ADMIN&acesso=Checkout" class="mb-0">
                              <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                              <input type="hidden" name="acao" value="alterar">
                              <select name="usar"
                                 class="form-control form-control-sm"
                                 onchange="this.form.submit()">
                                 <option value="1" <?= $usar === 1 ? 'selected' : '' ?>>Sim</option>
                                 <option value="0" <?= $usar === 0 ? 'selected' : '' ?>>Não</option>
                              </select>
                              <small class="form-text text-muted">
                              Defina se este checkout será usado
                              </small>
                           </form>
                        </td>
                        <td>
                           <form method="post" class="d-inline">
                              <input type="hidden" name="delete_checkout_id" value="<?= (int)$row['id'] ?>">
                              <button type="submit" class="btn btn-danger btn-sm">
                              Deletar
                              </button>
                           </form>
                        </td>
                     </tr>
                     <?php endwhile; ?>
                     <?php else: ?>
                     <tr>
                        <td colspan="4" class="text-center">
                           Nenhum checkout
                        </td>
                     </tr>
                     <?php endif; ?>
                  </tbody>
               </table>
            </div>
         </div>
      </div>