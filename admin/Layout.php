<?php
   session_start();
   
   if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
       header("Location: ./");
       exit;
   }
   
   require_once "config.php";
   
   $id = 1;
   
   $nome = $empresa = $cnpj = $endereco = $titulo = $logo = $favicon = $whatsapp = $iconezap = $texto1 = $texto2 = "";
   
   if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   
       if (isset($_POST['delete'])) {
           $stmt = $conn->prepare("DELETE FROM layout WHERE id = 1");
           $stmt->execute();
           $stmt->close();
           echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
           <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
           <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
           <script>
           $(function() {
               toastr.success("Layout deletado com sucesso!");
               setTimeout(() => location.href="?COMON=/ADMIN&acesso=Layout", 2000);
           });
           </script>';
   
       }
   
       $id = 1;
       $nome = $_POST['nome'] ?? '';
       $empresa = $_POST['empresa'] ?? '';
       $cnpj = $_POST['cnpj'] ?? '';
       $endereco = $_POST['endereco'] ?? null;
       $titulo = $_POST['titulo'] ?? '';
       $logo = $_POST['logo'] ?? '';
       $favicon = $_POST['favicon'] ?? null;
       $whatsapp = $_POST['whatsapp'] ?? null;
       $iconezap = $_POST['iconezap'] ?? null;
       $texto1 = $_POST['Texto1'] ?? null;
       $texto2 = $_POST['Texto2'] ?? null;
   
       $check = $conn->prepare("SELECT id FROM layout WHERE id = 1");
       $check->execute();
       $result = $check->get_result();
   
       if ($result->num_rows > 0) {
           $stmt = $conn->prepare("
               UPDATE layout 
               SET nome=?, empresa=?, cnpj=?, endereco=?, titulo=?, logo=?, favicon=?, whatsapp=?, iconezap=?, texto1=?, texto2=?
               WHERE id=1
           ");
           $stmt->bind_param(
               "sssssssssss",
               $nome,
               $empresa,
               $cnpj,
               $endereco,
               $titulo,
               $logo,
               $favicon,
               $whatsapp,
               $iconezap,
               $texto1,
               $texto2
           );
           $stmt->execute();
           $stmt->close();
       } else {
           $stmt = $conn->prepare("
               INSERT INTO layout (id, nome, empresa, cnpj, endereco, titulo, logo, favicon, whatsapp, iconezap, texto1, texto2)
               VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
           ");
           $stmt->bind_param(
               "sssssssssss",
               $nome,
               $empresa,
               $cnpj,
               $endereco,
               $titulo,
               $logo,
               $favicon,
               $whatsapp,
               $iconezap,
               $texto1,
               $texto2
           );
           $stmt->execute();
           $stmt->close();
       }
   
       echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
           <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
           <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
           <script>
           $(function() {
               toastr.success("Layout deletado com sucesso!");
               setTimeout(() => location.href="?COMON=/ADMIN&acesso=Layout", 2000);
           });
           </script>';
   }
   
   $stmt = $conn->prepare("SELECT * FROM layout WHERE id = 1");
   $stmt->execute();
   $data = $stmt->get_result()->fetch_assoc();
   $stmt->close();
   
   if ($data) {
       $nome = $data['nome'];
       $empresa = $data['empresa'];
       $cnpj = $data['cnpj'];
       $endereco = $data['endereco'];
       $titulo = $data['titulo'];
       $logo = $data['logo'];
       $favicon = $data['favicon'];
       $whatsapp = $data['whatsapp'];
       $iconezap = $data['iconezap'];
       $texto1 = $data['texto1'];
       $texto2 = $data['texto2'];
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
               <h1>Olá  admin, você está em  / Layout</h1>
               <span>TXT_JPGI1 - @TXT_JPGI1 <== no telegram!  </span>
            </div>
         </div>
      </div>
      <div class="block">
      <div class="title"><strong class="d-block">Adicionar Layout</strong></div>
      <div class="block-body">
      <br>
      <form method="post" action="">
         <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
         <div class="form-group">
            <label class="form-control-label">Nome</label>
            <input type="text" required name="nome" class="form-control" value="<?= htmlspecialchars($nome) ?>">
         </div>
         <div class="form-group">
            <label class="form-control-label">Empresa</label>
            <input type="text" required name="empresa" class="form-control" value="<?= htmlspecialchars($empresa) ?>">
         </div>
         <div class="form-group">
            <label class="form-control-label">CNPJ</label>
            <input type="text" required name="cnpj" class="form-control" value="<?= htmlspecialchars($cnpj) ?>">
         </div>
         <div class="form-group">
            <label class="form-control-label">Título</label>
            <input type="text" required name="titulo" class="form-control" value="<?= htmlspecialchars($titulo) ?>">
         </div>
         <div class="form-group">
            <label class="form-control-label">Logo</label>
            <input type="text" required name="logo" class="form-control" value="<?= htmlspecialchars($logo) ?>">
            <small class="form-text text-muted">Adicione o link da imagem no https://imgur.com</small>
         </div>
         <div class="form-group">
            <label class="form-control-label">Favicon (URL)</label>
            <input type="text" name="favicon" class="form-control" value="<?= htmlspecialchars($favicon) ?>">
            <small class="form-text text-muted">Adicione o link da imagem no https://imgur.com</small>
         </div>
         <div class="form-group">
            <label class="form-control-label">WhatsApp</label>
            <input type="text" name="whatsapp" class="form-control" value="<?= htmlspecialchars($whatsapp) ?>">
            <small class="form-text text-muted"></small>
         </div>
         <!--<div class="form-group">
            <label class="form-control-label">Icone WhatsApp</label>
            <input type="text" name="iconezap" class="form-control" value="<?= htmlspecialchars($iconezap) ?>">
            <small class="form-text text-muted">Adicione o link da imagem no https://imgur.com</small>
         </div>
         <strong class="d-block">Editar Textos</strong><br>
            <div class="form-group">
                <label class="form-control-label">Texto1</label>
                <textarea name="" class="form-control"><?= htmlspecialchars($texto1) ?></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-control-label">Texto2</label>
                <textarea name="" class="form-control"><?= htmlspecialchars($texto2) ?></textarea>
            </div>-->
         <div class="form-group">
            <input type="submit" value="Salvar Layout" class="btn btn-primary">
         </div>
      </form>
      <script>document.querySelector('input[name="whatsapp"]').addEventListener('input',function(e){let v=e.target.value.replace(/\D/g,'');if(v.length>11)v=v.slice(0,11);if(v.length>2)v='('+v.slice(0,2)+') '+v.slice(2);if(v.length>10)v=v.slice(0,10)+'-'+v.slice(10);e.target.value=v});</script>