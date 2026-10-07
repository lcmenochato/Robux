<?php
   session_start();
   
   if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
       header("Location: ./");
       exit;
   }
   
   require_once 'config.php';
   
   $mapaPix = [
       'payevo'        => 'Payevo',
       'LxPay'        => 'LxPay',
       'centurion_pay'        => 'CenturionPay',
       'blackcatpagamentos.online'        => 'Blackcat',
       'anubis_pay'    => 'Anubispay',
       'blackcat'      => 'Blackcat',
       'pague_x'       => 'PagueX',
       'optimus_pay'   => 'OptimusPay',
       'nuvia_pay'     => 'NuviaPay',
       'clyptpay'      => 'ClyptPay',
       'evopay'        => 'EvoPay',
       'free_pay'      => 'FreePay',
       'pay_shark'     => 'PayShark',
       'pixup'         => 'PixUp',
       'podpay'        => 'PodPay',
       'asaas'       => 'Asaas',
       'pronttus'      => 'Pronttus',
       'quantum_pay'   => 'QuantumPay',
       'sigilopay'     => 'SigiloPay',
       'street_pay'    => 'StreetPay',
       'titans_hub'    => 'TitansHub',
       'MP'          => 'MercadoPago',
       'tryplo_pay'    => 'TryploPay',
       'payevo '  => 'Payevo',
       'unicocash'     => 'UnicoCash',
       'plumify'     => 'Plumify',
       'Marcha'       => 'Marcha',
       'tribo_pay'       => 'TriboPay',
       'asset'       => 'Asset',
       'assetpay.com.br'       => 'AssetPay'
   ];
   
   $mapaCartao = [
      'payevo'        => 'Payevo',
       'LxPay'        => 'LxPay',
       'centurion_pay'        => 'CenturionPay',
       'blackcatpagamentos.online'        => 'Blackcat',
       'anubis_pay'    => 'Anubispay',
       'blackcat'      => 'Blackcat',
       'pague_x'       => 'PagueX',
       'optimus_pay'   => 'OptimusPay',
       'nuvia_pay'     => 'NuviaPay',
       'clyptpay'      => 'ClyptPay',
       'evopay'        => 'EvoPay',
       'free_pay'      => 'FreePay',
       'pay_shark'     => 'PayShark',
       'pixup'         => 'PixUp',
       'podpay'        => 'PodPay',
       'asaas'       => 'Asaas',
       'pronttus'      => 'Pronttus',
       'quantum_pay'   => 'QuantumPay',
       'sigilopay'     => 'SigiloPay',
       'street_pay'    => 'StreetPay',
       'titans_hub'    => 'TitansHub',
       'MP'          => 'MercadoPago',
       'tryplo_pay'    => 'TryploPay',
       'payevo '  => 'Payevo',
       'unicocash'     => 'UnicoCash',
       'plumify'     => 'Plumify',
       'Marcha'       => 'Marcha',
       'tribo_pay'       => 'TriboPay',
       'asset'       => 'Asset',
       'assetpay.com.br'       => 'AssetPay'
   ];
   
   $mapaBoleto = [
       'payevo'        => 'Payevo',
       'LxPay'        => 'LxPay',
       'centurion_pay'        => 'CenturionPay',
       'blackcatpagamentos.online'        => 'Blackcat',
       'anubis_pay'    => 'Anubispay',
       'blackcat'      => 'Blackcat',
       'pague_x'       => 'PagueX',
       'optimus_pay'   => 'OptimusPay',
       'nuvia_pay'     => 'NuviaPay',
       'clyptpay'      => 'ClyptPay',
       'evopay'        => 'EvoPay',
       'free_pay'      => 'FreePay',
       'pay_shark'     => 'PayShark',
       'pixup'         => 'PixUp',
       'podpay'        => 'PodPay',
       'asaas'       => 'Asaas',
       'pronttus'      => 'Pronttus',
       'quantum_pay'   => 'QuantumPay',
       'sigilopay'     => 'SigiloPay',
       'street_pay'    => 'StreetPay',
       'titans_hub'    => 'TitansHub',
       'MP'          => 'MercadoPago',
       'tryplo_pay'    => 'TryploPay',
       'payevo '  => 'Payevo',
       'unicocash'     => 'UnicoCash',
       'plumify'     => 'Plumify',
       'Marcha'       => 'Marcha',
       'tribo_pay'       => 'TriboPay',
       'asset'       => 'Asset',
       'assetpay.com.br'       => 'AssetPay'
   ];
   
   function gerarWebhook($conn, $tabela, $tipo, $mapa) {
   
       $result = $conn->query("SELECT nome FROM {$tabela} LIMIT 1");
   
       if (!$result || $result->num_rows === 0) {
           return '';
       }
   
       $row = $result->fetch_assoc();
       $gateway = $row['nome'];
   
       if (!isset($mapa[$gateway])) {
           return '';
       }
   
       $host = $_SERVER['HTTP_HOST'];
       return "https://{$host}/Webhook/{$tipo}/{$mapa[$gateway]}";
   }
   
   $webhookPix     = gerarWebhook($conn, 'gateway_pix', 'pix', $mapaPix);
   $webhookCartao  = gerarWebhook($conn, 'gateway_cartão', 'cartão', $mapaCartao);
   //$webhookBoleto  = gerarWebhook($conn, 'gateway_boleto', 'boleto', $mapaBoleto);
   
   $gatepix = '';
   $gatecartao = '';
   $gateboleto = '';
   
   $result = $conn->query("SELECT nome FROM gateway_pix LIMIT 1");
   
   $gatepix = null;
   
   if ($result && $result->num_rows > 0) {
       $gatepix = trim($result->fetch_assoc()['nome']);
   }
   
   $gateways = [
       'payevo'        => 'Payevo',
       'lxpay'         => 'LxPay',
       'centurion_pay' => 'CenturionPay',
       'blackcatpagamentos.online' => 'Blackcat',
       'anubis_pay'    => 'AnubisPay',
       'blackcat'      => 'BlackCat',
       'pague_x'       => 'PagueX',
       'optioma_pay'   => 'OptiomaPay',
       'optimus_pay'   => 'OptimusPay',
       'nuvia_pay'     => 'NuviaPay',
       'clyptpay'      => 'ClyptPay',
       'evopay'        => 'EvoPay',
       'free_pay'      => 'FreePay',
       'pay_shark'     => 'PayShark',
       'pixup'         => 'PixUp',
       'podpay'        => 'PodPay',
       'asaas'         => 'Asaas',
       'pronttus'      => 'Pronttus',
       'quantum_pay'   => 'QuantumPay',
       'sigilopay'     => 'SigiloPay',
       'street_pay'    => 'StreetPay',
       'titans_hub'    => 'TitansHub',
       'mercado_pago'            => 'MercadoPago',
       'tryplo_pay'    => 'TryploPay',
       'unicocash'     => 'UnicoCash',
       'Marcha'       => 'Marcha',
       'plumify'       => 'Plumify',
       'tribo_pay'       => 'TriboPay',
       'cart_hero'       => 'CartHero',
       'iron_pay'       => 'IronPay',
       'rokify'       => 'Rokify',
       'apx_pay'       => 'ApxPay',
       'asset'       => 'Asset',
       'assetpay.com.br'       => 'AssetPay'
   ];
   
   $gatewayNome = $gatepix && isset($gateways[strtolower($gatepix)]) ? $gateways[strtolower($gatepix)] : $gatepix;
   $result = $conn->query("SELECT nome FROM gateway_cartão LIMIT 1");
   $gatecartao = null;
   
   if ($result && $result->num_rows > 0) {
       $gatecartao = trim($result->fetch_assoc()['nome']);
   }
   
   $gatewaysCartao = [
       'payevo'        => 'Payevo',
       'lxpay'         => 'LxPay',
       'centurion_pay' => 'CenturionPay',
       'blackcatpagamentos.online' => 'Blackcat',
       'anubis_pay'    => 'AnubisPay',
       'blackcat'      => 'BlackCat',
       'pague_x'       => 'PagueX',
       'optioma_pay'   => 'OptiomaPay',
       'optimus_pay'   => 'OptimusPay',
       'nuvia_pay'     => 'NuviaPay',
       'clyptpay'      => 'ClyptPay',
       'evopay'        => 'EvoPay',
       'free_pay'      => 'FreePay',
       'pay_shark'     => 'PayShark',
       'pixup'         => 'PixUp',
       'podpay'        => 'PodPay',
       'asaas'         => 'Asaas',
       'pronttus'      => 'Pronttus',
       'quantum_pay'   => 'QuantumPay',
       'sigilopay'     => 'SigiloPay',
       'street_pay'    => 'StreetPay',
       'titans_hub'    => 'TitansHub',
       'mp'            => 'MercadoPago',
       'tryplo_pay'    => 'TryploPay',
       'unicocash'     => 'UnicoCash',
       'Marcha'       => 'Marcha',
       'plumify'       => 'Plumify',
       'tribo_pay'       => 'TriboPay',
       'cart_hero'       => 'CartHero',
       'iron_pay'       => 'IronPay',
       'rokify'       => 'Rokify',
       'apx_pay'       => 'ApxPay',
       'asset'       => 'Asset',
       'assetpay.com.br'       => 'AssetPay'
   ];
   
   $gatewaysBoleto = [
       'payevo'        => 'Payevo',
       'lxpay'         => 'LxPay',
       'centurion_pay' => 'CenturionPay',
       'blackcatpagamentos.online' => 'Blackcat',
       'anubis_pay'    => 'AnubisPay',
       'blackcat'      => 'BlackCat',
       'pague_x'       => 'PagueX',
       'optioma_pay'   => 'OptiomaPay',
       'optimus_pay'   => 'OptimusPay',
       'nuvia_pay'     => 'NuviaPay',
       'clyptpay'      => 'ClyptPay',
       'evopay'        => 'EvoPay',
       'free_pay'      => 'FreePay',
       'pay_shark'     => 'PayShark',
       'pixup'         => 'PixUp',
       'podpay'        => 'PodPay',
       'asaas'         => 'Asaas',
       'pronttus'      => 'Pronttus',
       'quantum_pay'   => 'QuantumPay',
       'sigilopay'     => 'SigiloPay',
       'street_pay'    => 'StreetPay',
       'titans_hub'    => 'TitansHub',
       'mp'            => 'MercadoPago',
       'tryplo_pay'    => 'TryploPay',
       'unicocash'     => 'UnicoCash',
       'Marcha'       => 'Marcha',
       'plumify'       => 'Plumify',
       'tribo_pay'       => 'TriboPay',
       'cart_hero'       => 'CartHero',
       'iron_pay'       => 'IronPay',
       'rokify'       => 'Rokify',
       'apx_pay'       => 'ApxPay',
       'asset'       => 'Asset',
       'assetpay.com.br'       => 'AssetPay'
   ];
   
   $nomeCartao = $gatecartao && isset($gatewaysCartao[strtolower($gatecartao)]) ? $gatewaysCartao[strtolower($gatecartao)] : $gatecartao;
   $nomeBoleto = $gateboleto && isset($gatewaysBoleto[strtolower($gateboleto)]) ? $gatewaysBoleto[strtolower($gateboleto)] : $gateboleto;
   $conn->close();
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
               <h1>Olá  admin, você está em  / Webhook</h1>
               <span>TXT_JPGI1 - @TXT_JPGI1 <== no telegram!  </span>
            </div>
         </div>
      </div>
      <div class="card">
         <div class="card-header">
            <strong>Adicionar Webhook</strong>
         </div>
         <div class="card-body card-block">
            <div class="row form-group">
               <div class="col col-md-3"><label class="form-control-label">Webhook Pix</label></div>
               <div class="col-12 col-md-8">
                  <input type="text" id="webhookPix" class="form-control dinheiro" disabled value="<?= $webhookPix ?>">
                  <small class="form-text text-muted">Webhook Gateway: <b><?= $gatewayNome ?></b></small>
               </div>
               <div class="col-12 col-md-1">
                  <button type="button" class="btn btn-primary btn-sm" onclick="copiarWebhook('webhookPix')">Copiar</button>
               </div>
            </div>
            <!--<div class="row form-group">
               <div class="col col-md-3"><label class="form-control-label">Webhook Boleto</label></div>
               <div class="col-12 col-md-8">
                  <input type="text" id="webhookBoleto" class="form-control dinheiro" disabled value="">
                  <small class="form-text text-muted">Webhook Gateway: <b></b></small>
               </div>
               <div class="col-12 col-md-1">
                  <button type="button" class="btn btn-primary btn-sm" onclick="copiarWebhook('')">Copiar</button>
               </div>
            </div>-->
            <div class="row form-group">
               <div class="col col-md-3"><label class="form-control-label">Webhook Cartão</label></div>
               <div class="col-12 col-md-8">
                  <input type="text" id="webhookCartao" class="form-control" disabled value="<?= $webhookCartao ?>">
                  <small class="form-text text-muted">Webhook Gateway: <b><?= $nomeCartao ?></b></small>
               </div>
               <div class="col-12 col-md-1">
                  <button type="button" class="btn btn-primary btn-sm" onclick="copiarWebhook('webhookCartao')">Copiar</button>
               </div>
            </div>
         </div>
      </div>
      <div id="sucesso" class="toast-sucesso"></div>
      <script>
         function copiarWebhook(id) {
             const input = document.getElementById(id);
         
             navigator.clipboard.writeText(input.value).then(() => {
                 const sucessoDiv = document.getElementById('sucesso');
         
                 sucessoDiv.textContent = 'Webhook copiado com sucesso!';
                 sucessoDiv.classList.add('mostrar');
         
                 setTimeout(() => {
                     sucessoDiv.classList.remove('mostrar');
                 }, 3000);
             }).catch(err => {
                 console.error('Erro ao copiar: ', err);
             });
         }
      </script>