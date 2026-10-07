<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ./");
    exit;
}

if (isset($_POST['DeletarGateway'])) {
    $conn->query("DELETE FROM gateway_pix WHERE id = 1");
    echo '
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
    $(function(){
        toastr.options.timeOut = 2000;
        toastr.options.closeButton = true;
        toastr.options.positionClass = "toast-top-right";
        toastr["success"]("Gateway Deletado com sucesso!");
        setTimeout(function(){ window.location.href="?COMON=/ADMIN&acesso=Gateway_pix"; }, 2000);
    });
    </script>';
}

$res = $conn->query("SELECT * FROM gateway_pix WHERE id = 1 LIMIT 1");

$url = $nome = $cliente_public = $cliente_privada = $cliente_id = $cliente_secret = $apikey = $token = "";
$asaas_token = $paggue_client_key = $paggue_client_secret = $paggue_token = $tryplo_token = $tryplo_chave_secreta = "";
$free_pay_secret = $free_pay_public = $titans_hub_secret = $titans_hub_public = $payevo_secret = $payevo_company_id = "";
$podpay_secret = $podpay_public = $infinity_secret = $infinity_public = $street_secret = $street_company_id = "";
$clyptpay_secret = $clyptpay_public = $quantum_secret = $quantum_company_id = $grapefy_secret = $grapefy_public = "";
$pague_x_secret = $pague_x_public = $pay_shark_secret = $pay_shark_public = $blackcat_secret = $blackcat_public = "";
$monetrix_secret = $monetrix_public = $anubis_secret = $anubis_public = "";
$pronttus_id = $pronttus_secret = $apitoken_unicoCash = $plumify_api_token_api = "";
$zyntra_pay_secret_key = $zyntra_pay_company_id = $asset_secret_key = $asset_public_key = $asset_api_token = "";
$paggue_secret_key = $paggue_public_key = $optimus_pay_secret_key = $optimus_pay_public_key = "";
$nuvia_pay_secret_key = $nuvia_pay_public_key = $bynet_api_token = "";
$centurion_pay_secret_key = $centurion_pay_public_key = "";
$lxpay_api_pix_credencial_1 = $lxpay_api_pix_credencial_2 = "";
$marcha_api_pix_credencial_1 = $marcha_api_pix_credencial_2 = "";
$tribo_pay_api_pix_access_token = "";
$carthero_secret = $carthero_public = "";
$ironpay_tokenapi = $ironpay_offerhash = "";
$rokify_secret = $rokity_id = "";
$plumify_offerhash = "";
$tibo_offerhash = "";

if ($res && $res->num_rows > 0) {
    $gat = $res->fetch_assoc();
    foreach ($gat as $key => $value) {
        if (isset($$key)) {
            $$key = $value ?? '';
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST['DeletarGateway'])) {
    $nome = $_POST['nome'] ?? '';
    $token                  = $_POST['mercado_pago_token'] ?? '';
    $cliente_id             = $_POST['client_pixup'] ?? '';
    $cliente_secret         = $_POST['client_secretpixup'] ?? '';
    $apikey                 = $_POST['apikey_evopay'] ?? '';
    $cliente_public         = $_POST['keypublc_sigilo'] ?? '';
    $cliente_privada        = $_POST['keypriv_sigilo'] ?? '';
    $pronttus_id            = $_POST['clienteid_pronttus'] ?? '';
    $pronttus_secret        = $_POST['clientsecret_pronttus'] ?? '';
    $apitoken_unicoCash     = $_POST['token_unicoCas'] ?? '';
    $asaas_token            = $_POST['asaas_api_pix_access_token'] ?? '';
    $tribo_pay_api_pix_access_token = $_POST['tribo_pay_api_pix_access_token'] ?? '';
    $rokify_secret          = $_POST['rokify_secret'] ?? '';
    $rokity_id              = $_POST['rokity_id'] ?? '';
    $ironpay_tokenapi       = $_POST['ironpay_tokenapi'] ?? '';
    $ironpay_offerhash      = $_POST['ironpay_offerhash'] ?? '';
    $carthero_secret        = $_POST['carthero_secret'] ?? '';
    $carthero_public        = $_POST['carthero_public'] ?? '';
    $plumify_offerhash      = $_POST['plumify_offerhash'] ?? '';
    $tibo_offerhash         = $_POST['tibo_offerhash'] ?? '';
    $paggue_client_key      = $_POST['paggue_api_pix_client_key'] ?? '';
    $paggue_client_secret   = $_POST['paggue_api_pix_client_secret'] ?? '';
    $paggue_token           = $_POST['paggue_api_pix_token'] ?? '';
    $tryplo_token           = $_POST['tryplo_pay_api_pix_token'] ?? '';
    $tryplo_chave_secreta   = $_POST['tryplo_pay_api_pix_chave_secreta'] ?? '';
    $free_pay_secret        = $_POST['free_pay_api_pix_secret_key'] ?? '';
    $free_pay_public        = $_POST['free_pay_api_pix_public_key'] ?? '';
    $titans_hub_secret      = $_POST['titans_hub_api_pix_secret_key'] ?? '';
    $titans_hub_public      = $_POST['titans_hub_api_pix_public_key'] ?? '';
    $payevo_secret          = $_POST['payevo_api_pix_secret_key'] ?? '';
    $payevo_company_id      = $_POST['payevo_api_pix_company_id'] ?? '';
    $podpay_secret          = $_POST['podpay_api_pix_secret_key'] ?? '';
    $podpay_public          = $_POST['podpay_api_pix_public_key'] ?? '';
    $infinity_secret        = $_POST['infinity_bank_api_pix_secret_key'] ?? '';
    $infinity_public        = $_POST['infinity_bank_api_pix_public_key'] ?? '';
    $street_secret          = $_POST['street_pay_api_pix_secret_key'] ?? '';
    $street_company_id      = $_POST['street_pay_api_pix_company_id'] ?? '';
    $clyptpay_secret        = $_POST['clyptpay_api_pix_secret_key'] ?? '';
    $clyptpay_public        = $_POST['clyptpay_api_pix_public_key'] ?? '';
    $quantum_secret         = $_POST['quantum_pay_api_pix_secret_key'] ?? '';
    $quantum_company_id     = $_POST['quantum_pay_api_pix_company_id'] ?? '';
    $grapefy_secret         = $_POST['grapefy_api_pix_secret_key'] ?? '';
    $grapefy_public         = $_POST['grapefy_api_pix_public_key'] ?? '';
    $pague_x_secret         = $_POST['pague_x_api_pix_secret_key'] ?? '';
    $pague_x_public         = $_POST['pague_x_api_pix_public_key'] ?? '';
    $pay_shark_secret       = $_POST['pay_shark_api_pix_secret_key'] ?? '';
    $pay_shark_public       = $_POST['pay_shark_api_pix_public_key'] ?? '';

    $blackcat_secret        = $_POST['blackcat_api_pix_secret_key2'] ?? $_POST['blackcat_api_pix_secret_key'] ?? '';
    $blackcat_public        = $_POST['blackcat_api_pix_public_key2'] ?? $_POST['blackcat_api_pix_public_key'] ?? '';

    $monetrix_secret        = $_POST['monetrix_api_pix_secret_key'] ?? '';
    $monetrix_public        = $_POST['monetrix_api_pix_public_key'] ?? '';
    $anubis_secret          = $_POST['anubis_pay_api_pix_secret_key'] ?? '';
    $anubis_public          = $_POST['anubis_pay_api_pix_public_key'] ?? '';
    $plumify_api_token_api  = $_POST['plumify_api_pix_token'] ?? '';
    $zyntra_pay_secret_key  = $_POST['zyntra_pay_api_pix_secret_key'] ?? '';
    $zyntra_pay_company_id  = $_POST['zyntra_pay_api_pix_company_id'] ?? '';
    $asset_secret_key       = $_POST['asset_api_pix_secret_key'] ?? '';
    $asset_public_key       = $_POST['asset_api_pix_public_key'] ?? '';
    $asset_api_token        = $_POST['asset_api_pix_api_token'] ?? '';
    $paggue_secret_key      = $_POST['paggue_api_pix_secret_key'] ?? '';
    $paggue_public_key      = $_POST['paggue_api_pix_public_key'] ?? '';
    $optimus_pay_secret_key = $_POST['optimus_pay_api_pix_secret_key'] ?? '';
    $optimus_pay_public_key = $_POST['optimus_pay_api_pix_public_key'] ?? '';
    $nuvia_pay_secret_key   = $_POST['nuvia_pay_api_pix_secret_key'] ?? '';
    $nuvia_pay_public_key   = $_POST['nuvia_pay_api_pix_public_key'] ?? '';
    $bynet_api_token        = $_POST['bynet_api_pix_secret_key'] ?? '';
    $centurion_pay_secret_key = $_POST['centurion_pay_api_pix_secret_key'] ?? '';
    $centurion_pay_public_key = $_POST['centurion_pay_api_pix_public_key'] ?? '';
    $lxpay_api_pix_credencial_1 = $_POST['lxpay_api_pix_credencial_1'] ?? '';
    $lxpay_api_pix_credencial_2 = $_POST['lxpay_api_pix_credencial_2'] ?? '';
    $marcha_api_pix_credencial_1 = $_POST['marcha_api_pix_credencial_1'] ?? '';
    $marcha_api_pix_credencial_2 = $_POST['marcha_api_pix_credencial_2'] ?? '';

    $check = $conn->query("SELECT id FROM gateway_pix WHERE id = 1 LIMIT 1");

    $bind_params = [
        $url, $token, $nome, $cliente_public, $cliente_privada, $cliente_id, $cliente_secret, $apikey,
        $asaas_token, $paggue_client_key, $paggue_client_secret, $paggue_token, $tryplo_token, $tryplo_chave_secreta,
        $free_pay_secret, $titans_hub_secret, $titans_hub_public, $payevo_secret, $payevo_company_id,
        $podpay_secret, $podpay_public, $infinity_secret, $infinity_public, $street_secret, $street_company_id,
        $clyptpay_secret, $clyptpay_public, $quantum_secret, $quantum_company_id, $grapefy_secret, $grapefy_public,
        $pague_x_secret, $pague_x_public, $pay_shark_secret, $pay_shark_public,
        $blackcat_secret, $blackcat_public, $monetrix_secret, $monetrix_public, $anubis_secret, $anubis_public,
        $free_pay_public, $pronttus_id, $pronttus_secret, $apitoken_unicoCash,
        $plumify_api_token_api, $zyntra_pay_secret_key, $zyntra_pay_company_id,
        $asset_secret_key, $asset_public_key, $asset_api_token,
        $paggue_secret_key, $paggue_public_key, $optimus_pay_secret_key, $optimus_pay_public_key,
        $nuvia_pay_secret_key, $nuvia_pay_public_key, $bynet_api_token,
        $centurion_pay_secret_key, $centurion_pay_public_key,
        $lxpay_api_pix_credencial_1, $lxpay_api_pix_credencial_2,
        $marcha_api_pix_credencial_1, $marcha_api_pix_credencial_2,
        $tribo_pay_api_pix_access_token,
        $carthero_secret, $carthero_public,
        $ironpay_tokenapi, $ironpay_offerhash,
        $rokify_secret, $rokity_id,
        $plumify_offerhash, $tibo_offerhash
    ];

    $bind_types = str_repeat("s", count($bind_params));

    if ($check && $check->num_rows > 0) {
        // UPDATE
        $sql = "UPDATE gateway_pix SET 
            url=?, token=?, nome=?, cliente_public=?, cliente_privada=?, cliente_id=?, cliente_secret=?, apikey=?,
            asaas_token=?, paggue_client_key=?, paggue_client_secret=?, paggue_token=?, tryplo_token=?, tryplo_chave_secreta=?,
            free_pay_secret=?, titans_hub_secret=?, titans_hub_public=?, payevo_secret=?, payevo_company_id=?,
            podpay_secret=?, podpay_public=?, infinity_secret=?, infinity_public=?, street_secret=?, street_company_id=?,
            clyptpay_secret=?, clyptpay_public=?, quantum_secret=?, quantum_company_id=?, grapefy_secret=?, grapefy_public=?,
            pague_x_secret=?, pague_x_public=?, pay_shark_secret=?, pay_shark_public=?,
            blackcat_secret=?, blackcat_public=?, monetrix_secret=?, monetrix_public=?, anubis_secret=?, anubis_public=?,
            free_pay_public=?, pronttus_id=?, pronttus_secret=?, apitoken_unicoCash=?,
            plumify_api_token_api=?, zyntra_pay_secret_key=?, zyntra_pay_company_id=?,
            asset_secret_key=?, asset_public_key=?, asset_api_token=?,
            paggue_secret_key=?, paggue_public_key=?, optimus_pay_secret_key=?, optimus_pay_public_key=?,
            nuvia_pay_secret_key=?, nuvia_pay_public_key=?, bynet_api_token=?,
            centurion_pay_secret_key=?, centurion_pay_public_key=?,
            lxpay_api_pix_credencial_1=?, lxpay_api_pix_credencial_2=?,
            marcha_api_pix_credencial_1=?, marcha_api_pix_credencial_2=?,
            tribo_pay_api_pix_access_token=?,
            carthero_secret=?, carthero_public=?,
            ironpay_tokenapi=?, ironpay_offerhash=?,
            rokify_secret=?, rokity_id=?,
            plumify_offerhash=?, tibo_offerhash=?
            WHERE id=1";
    } else {
        // INSERT
        $sql = "INSERT INTO gateway_pix (id, url, token, nome, cliente_public, cliente_privada, cliente_id, cliente_secret, apikey,
            asaas_token, paggue_client_key, paggue_client_secret, paggue_token, tryplo_token, tryplo_chave_secreta,
            free_pay_secret, titans_hub_secret, titans_hub_public, payevo_secret, payevo_company_id,
            podpay_secret, podpay_public, infinity_secret, infinity_public, street_secret, street_company_id,
            clyptpay_secret, clyptpay_public, quantum_secret, quantum_company_id, grapefy_secret, grapefy_public,
            pague_x_secret, pague_x_public, pay_shark_secret, pay_shark_public,
            blackcat_secret, blackcat_public, monetrix_secret, monetrix_public, anubis_secret, anubis_public,
            free_pay_public, pronttus_id, pronttus_secret, apitoken_unicoCash,
            plumify_api_token_api, zyntra_pay_secret_key, zyntra_pay_company_id,
            asset_secret_key, asset_public_key, asset_api_token,
            paggue_secret_key, paggue_public_key, optimus_pay_secret_key, optimus_pay_public_key,
            nuvia_pay_secret_key, nuvia_pay_public_key, bynet_api_token,
            centurion_pay_secret_key, centurion_pay_public_key,
            lxpay_api_pix_credencial_1, lxpay_api_pix_credencial_2,
            marcha_api_pix_credencial_1, marcha_api_pix_credencial_2,
            tribo_pay_api_pix_access_token,
            carthero_secret, carthero_public,
            ironpay_tokenapi, ironpay_offerhash,
            rokify_secret, rokity_id,
            plumify_offerhash, tibo_offerhash) 
            VALUES(1, " . str_repeat("?,", count($bind_params)-1) . "?)";
    }

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param($bind_types, ...$bind_params);
        if ($stmt->execute()) {
            echo '
            <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
            <script>
            $(function(){
                toastr.options.timeOut = 2000;
                toastr.options.closeButton = true;
                toastr.options.positionClass = "toast-top-right";
                toastr["success"]("Gateway salvo com sucesso!");
                setTimeout(function(){ window.location.href="?COMON=/ADMIN&acesso=Gateway_pix"; }, 2000);
            });
            </script>';
        } else {
            echo "Erro ao executar: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Erro ao preparar: " . $conn->error;
    }
}

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
       'blackcatpagamentos.online' => 'BlackCatOnline',
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
       'blackcatpagamentos.online' => 'BlackCatOnline',
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
   
   $gatewaysBoleto = [
       'payevo'        => 'Payevo',
       'lxpay'         => 'LxPay',
       'centurion_pay' => 'CenturionPay',
       'blackcatpagamentos.online' => 'BlackCatOnline',
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
   
   $nomeCartao = $gatecartao && isset($gatewaysCartao[strtolower($gatecartao)]) ? $gatewaysCartao[strtolower($gatecartao)] : $gatecartao;
   $nomeBoleto = $gateboleto && isset($gatewaysBoleto[strtolower($gateboleto)]) ? $gatewaysBoleto[strtolower($gateboleto)] : $gateboleto;


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
               <h1>Olá  admin, você está em  / Gateway Pix</h1>
               <span>TXT_JPGI1 - @TXT_JPGI1 <== no telegram!  </span>
            </div>
         </div>
      </div>
      <div class="block">
         <div class="title"><strong class="d-block">Adicionar Gateway</strong><br><p></p></div>
         <div class="block-body">
            <span id="result" class="col-lg-12"></span>
            <form method="post" action="">
               <div class="form-group">
                  <label class="form-control-label">Plataforma Gateway</label>
                  <select name="nome" class="form-control" required>
                     <option selected disabled>Selecione</option>
                     <option value="mercado_pago" <?= ($nome == "mercado_pago" ? "selected" : "") ?>>Mercado Pago</option>
                     <option value="evopay" <?= ($nome == "evopay" ? "selected" : "") ?>>Evopay</option>
                     <option value="tryplo_pay" <?= ($nome == "tryplo_pay" ? "selected" : "") ?>>Tryplo Pay</option>
                     <option value="free_pay" <?= ($nome == "free_pay" ? "selected" : "") ?>>FreePay</option>
                     <!--<option value="freepaybrasil.com.br">FreePay(freepaybrasil.com.br)</option>-->
                     <option value="titans_hub" <?= ($nome == "titans_hub" ? "selected" : "") ?>>Titans HUB</option>
                     <option value="podpay" <?= ($nome == "podpay" ? "selected" : "") ?>>PodPay</option>
                     <option value="street_pay" <?= ($nome == "street_pay" ? "selected" : "") ?>>Street Pay</option>
                     <option value="pay_shark" <?= ($nome == "pay_shark" ? "selected" : "") ?>>PayShark</option>
                     <option value="blackcatpagamentos.online" <?= ($nome == "blackcatpagamentos.online" ? "selected" : "") ?>>Blackcat </option>
                     <option value="anubis_pay" <?= ($nome == "anubis_pay" ? "selected" : "") ?>>Anubis Pay</option>
                     <option value="nuvia_pay" <?= ($nome == "nuvia_pay" ? "selected" : "") ?>>Nuvia Pay</option>
                     <option value="sigilopay" <?= ($nome == "sigilopay" ? "selected" : "") ?>>Sigilopay</option>
                     <option value="plumify" <?= ($nome == "plumify" ? "selected" : "") ?>>Plumify</option>
                     <option value="tribo_pay" <?= ($nome == "tribo_pay" ? "selected" : "") ?>>TriboPay</option>
                     <option value="iron_pay" <?= ($nome == "iron_pay" ? "selected" : "") ?>>Iron Pay</option>
                  </select>
               </div>
               <div id="plataforma_api_pix_mercado_pago" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Access Token (Mercado Pago)</label>
                     <input type="text" name="mercado_pago_token" class="form-control" value="<?= htmlspecialchars($token) ?>">
                  </div>
               </div>
               <div id="plataforma_api_pix_pixup" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Client ID (Pix UP)</label>
                     <input type="text" name="client_pixup" class="form-control" value="<?= htmlspecialchars($cliente_id) ?>">
                  </div>
                  <div class="form-group">
                     <label class="form-control-label">Client Secret (Pix UP)</label>
                     <input type="text" name="client_secretpixup" class="form-control" value="<?= htmlspecialchars($cliente_secret) ?>">
                  </div>
               </div>
               <div id="plataforma_api_pix_evopay" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">API Key (EvoPay)</label>
                     <input type="text" name="apikey_evopay" class="form-control" value="<?= htmlspecialchars($apikey) ?>">
                  </div>
               </div>
               <div id="plataforma_api_pix_sigilopay" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Chave Pública (Sigilopay)</label>
                     <input type="text" name="keypublc_sigilo" class="form-control" value="<?= htmlspecialchars($cliente_public) ?>">
                  </div>
                  <div class="form-group">
                     <label class="form-control-label">Chave Privada (Sigilopay)</label>
                     <input type="text" name="keypriv_sigilo" class="form-control" value="<?= htmlspecialchars($cliente_privada) ?>">
                  </div>
               </div>
               <div id="plataforma_api_pix_rokify" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Chave Pública (Rokify)</label>
                     <input type="text" name="rokify_secret" class="form-control" value="<?= htmlspecialchars($rokify_secret) ?>">
                  </div>
                  <div class="form-group">
                     <label class="form-control-label">Company ID (Rokify)</label>
                     <input type="text" name="rokity_id" class="form-control" value="<?= htmlspecialchars($rokity_id) ?>">
                  </div>
               </div>
               <div id="plataforma_api_pix_iron_pay" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Token da API (Iron Pay)</label>
                     <input type="text" name="ironpay_tokenapi" class="form-control" value="<?= htmlspecialchars($ironpay_tokenapi) ?>">
                  </div>
                  <!--<div class="form-group">
                     <label class="form-control-label">Offer Hash (Iron Pay)</label>
                     <input type="text" name="ironpay_offerhash" class="form-control" value="<?= htmlspecialchars($ironpay_offerhash) ?>">
                  </div>-->
               </div>
               <div id="plataforma_api_pix_cart_hero" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Secret Key (CartHero)</label>
                     <input type="text" name="carthero_secret" class="form-control" value="<?= htmlspecialchars($carthero_secret) ?>">
                  </div>
                  <div class="form-group">
                     <label class="form-control-label">Public Key (CartHero)</label>
                     <input type="text" name="carthero_public" class="form-control" value="<?= htmlspecialchars($carthero_public) ?>">
                  </div>
               </div>
               <div id="plataforma_api_pix_LxPay" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Chave Pública (LXPAY)</label>
                     <input type="text" name="lxpay_api_pix_credencial_1" class="form-control" value="<?= htmlspecialchars($lxpay_api_pix_credencial_2) ?>">
                  </div>
                  <div class="form-group">
                     <label class="form-control-label">Chave Privada (LXPAY)</label>
                     <input type="text" name="lxpay_api_pix_credencial_2" class="form-control" value="<?= htmlspecialchars($lxpay_api_pix_credencial_2) ?>">
                  </div>
               </div>
               <div id="plataforma_api_pix_pronttus" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Client ID (Pronttus)</label>
                     <input type="text" name="clienteid_pronttus" class="form-control" value="<?= htmlspecialchars($pronttus_id) ?>">
                  </div>
                  <div class="form-group">
                     <label class="form-control-label">Client Secret (Pronttus)</label>
                     <input type="text" name="clientsecret_pronttus" class="form-control" value="<?= htmlspecialchars($pronttus_secret) ?>">
                  </div>
               </div>
               <div id="plataforma_api_pix_unicocash" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Token (UnicoCash)</label>
                     <input type="text" name="token_unicoCas" class="form-control" value="<?= htmlspecialchars($apitoken_unicoCash) ?>">
                  </div>
               </div>
               <div id="plataforma_api_pix_asaas" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Access Token (Asaas)</label>
                     <input type="text" name="asaas_api_pix_access_token" class="form-control" value="<?= htmlspecialchars($asaas_token) ?>">
                  </div>
               </div>
               <div id="plataforma_api_pix_tribo_pay" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Token de API (TriboPay)</label>
                     <input type="text" name="tribo_pay_api_pix_access_token" class="form-control" value="<?= htmlspecialchars($tribo_pay_api_pix_access_token) ?>">
                  </div>
                  <!--<div class="form-group">
                     <label class="form-control-label">Offer Hash (TriboPay)</label>
                     <input type="text" name="tibo_offerhash" class="form-control" value="<?= htmlspecialchars($tibo_offerhash) ?>">
                  </div>-->
               </div>
               <div id="plataforma_api_pix_Marcha" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Chave Privada (Marcha)</label>
                     <input type="text" name="marcha_api_pix_credencial_1" class="form-control" value="<?= htmlspecialchars($marcha_api_pix_credencial_1) ?>">
                  </div>
                  <div class="form-group">
                     <label class="form-control-label">Chave Pública (Marcha)</label>
                     <input type="text" name="marcha_api_pix_credencial_2" class="form-control" value="<?= htmlspecialchars($marcha_api_pix_credencial_2) ?>">
                  </div>
               </div>
               <div id="plataforma_api_pix_paggue" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Client Key (Paggue)</label>
                     <input type="text" name="paggue_api_pix_client_key" class="form-control" value="<?= htmlspecialchars($paggue_client_key) ?>">
                  </div>
                  <div class="form-group">
                     <label class="form-control-label">Client Secret (Paggue)</label>
                     <input type="text" name="paggue_api_pix_client_secret" class="form-control" value="<?= htmlspecialchars($paggue_client_secret) ?>">
                  </div>
                  <div class="form-group">
                     <label class="form-control-label">Token (Paggue)</label>
                     <input type="text" name="paggue_api_pix_token" class="form-control" value="<?= htmlspecialchars($paggue_token) ?>">
                  </div>
               </div>
               <div id="plataforma_api_pix_tryplo_pay" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Token (Tryplo Pay)</label>
                     <input type="text" name="tryplo_pay_api_pix_token" class="form-control" value="<?= htmlspecialchars($tryplo_token) ?>">
                  </div>
                  <div class="form-group">
                     <label class="form-control-label">Chave Secreta (Tryplo Pay)</label>
                     <input type="text" name="tryplo_pay_api_pix_chave_secreta" class="form-control" value="<?= htmlspecialchars($tryplo_chave_secreta) ?>">
                  </div>
               </div>
               <div id="plataforma_api_pix_free_pay" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Secret Key (Free Pay)</label>
                     <input type="text" name="free_pay_api_pix_secret_key" class="form-control" value="<?= htmlspecialchars($free_pay_secret) ?>">
                  </div>
                  <div class="form-group">
                     <label class="form-control-label">Public Key (Free Pay)</label>
                     <input type="text" name="free_pay_api_pix_public_key" class="form-control" value="<?= htmlspecialchars($free_pay_public) ?>">
                  </div>
               </div>
               <div id="plataforma_api_pix_titans_hub" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Secret Key (Titans Hub)</label>
                     <input type="text" name="titans_hub_api_pix_secret_key" class="form-control" value="<?= htmlspecialchars($titans_hub_secret) ?>">
                  </div>
                  <div class="form-group">
                     <label class="form-control-label">Public Key (Titans Hub)</label>
                     <input type="text" name="titans_hub_api_pix_public_key" class="form-control" value="<?= htmlspecialchars($titans_hub_public) ?>">
                  </div>
               </div>
               <div id="plataforma_api_pix_payevo" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Secret Key (Payevo)</label>
                     <input type="text" name="payevo_api_pix_secret_key" class="form-control" value="<?= htmlspecialchars($payevo_secret) ?>">
                  </div>
                  <div class="form-group">
                     <label class="form-control-label">Company ID (Payevo)</label>
                     <input type="text" name="payevo_api_pix_company_id" class="form-control" value="<?= htmlspecialchars($payevo_company_id) ?>">
                  </div>
               </div>
               <div id="plataforma_api_pix_podpay" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Secret Key (PodPay)</label>
                     <input type="text" name="podpay_api_pix_secret_key" class="form-control" value="<?= htmlspecialchars($podpay_secret) ?>">
                  </div>
                  <div class="form-group">
                     <label class="form-control-label">Public Key (PodPay)</label>
                     <input type="text" name="podpay_api_pix_public_key" class="form-control" value="<?= htmlspecialchars($podpay_public) ?>">
                  </div>
               </div>
               <div id="plataforma_api_pix_infinity" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Secret Key (Infinity Bank)</label>
                     <input type="text" name="infinity_bank_api_pix_secret_key" class="form-control" value="<?= htmlspecialchars($infinity_secret) ?>">
                  </div>
                  <div class="form-group">
                     <label class="form-control-label">Public Key (Infinity Bank)</label>
                     <input type="text" name="infinity_bank_api_pix_public_key" class="form-control" value="<?= htmlspecialchars($infinity_public) ?>">
                  </div>
               </div>
               <div id="plataforma_api_pix_street_pay" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Secret Key (Street Pay)</label>
                     <input type="text" name="street_pay_api_pix_secret_key" class="form-control" value="<?= htmlspecialchars($street_secret) ?>">
                  </div>
                  <!--<div class="form-group">
                     <label class="form-control-label">Company ID (Street Pay)</label>
                     <input type="text" name="street_pay_api_pix_company_id" class="form-control" value="<?= htmlspecialchars($street_company_id) ?>">
                  </div>-->
               </div>
               <div id="plataforma_api_pix_clyptpay" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Secret Key (ClyptPay)</label>
                     <input type="text" name="clyptpay_api_pix_secret_key" class="form-control" value="<?= htmlspecialchars($clyptpay_secret) ?>">
                  </div>
                  <div class="form-group">
                     <label class="form-control-label">Public Key (ClyptPay)</label>
                     <input type="text" name="clyptpay_api_pix_public_key" class="form-control" value="<?= htmlspecialchars($clyptpay_public) ?>">
                  </div>
               </div>
               <div id="plataforma_api_pix_quantum_pay" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Secret Key (Quantum Pay)</label>
                     <input type="text" name="quantum_pay_api_pix_secret_key" class="form-control" value="<?= htmlspecialchars($quantum_secret) ?>">
                  </div>
                  <div class="form-group">
                     <label class="form-control-label">Company ID (Quantum Pay)</label>
                     <input type="text" name="quantum_pay_api_pix_company_id" class="form-control" value="<?= htmlspecialchars($quantum_company_id) ?>">
                  </div>
               </div>
               <div id="plataforma_api_pix_grapefy" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Secret Key (Grapefy)</label>
                     <input type="text" name="grapefy_api_pix_secret_key" class="form-control" value="<?= htmlspecialchars($grapefy_secret) ?>">
                  </div>
                  <div class="form-group">
                     <label class="form-control-label">Public Key (Grapefy)</label>
                     <input type="text" name="grapefy_api_pix_public_key" class="form-control" value="<?= htmlspecialchars($grapefy_public) ?>">
                  </div>
               </div>
               <div id="plataforma_api_pix_pague_x" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Secret Key (Pague X)</label>
                     <input type="text" name="pague_x_api_pix_secret_key" class="form-control" value="<?= htmlspecialchars($pague_x_secret) ?>">
                  </div>
                  <div class="form-group">
                     <label class="form-control-label">Public Key (Pague X)</label>
                     <input type="text" name="pague_x_api_pix_public_key" class="form-control" value="<?= htmlspecialchars($pague_x_public) ?>">
                  </div>
               </div>
               <div id="plataforma_api_pix_pay_shark" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Secret Key (Pay Shark)</label>
                     <input type="text" name="pay_shark_api_pix_secret_key" class="form-control" value="<?= htmlspecialchars($pay_shark_secret) ?>">
                  </div>
                  <!--<div class="form-group">
                     <label class="form-control-label">Public Key (Pay Shark)</label>
                     <input type="text" name="pay_shark_api_pix_public_key" class="form-control" value="<?= htmlspecialchars($pay_shark_public) ?>">
                  </div>-->
               </div>
               <div id="plataforma_api_pix_blackcat" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Secret Key (BlackCat)</label>
                     <input type="text" name="blackcat_api_pix_secret_key" class="form-control" value="<?= htmlspecialchars($blackcat_secret) ?>">
                  </div>
                  <div class="form-group">
                     <label class="form-control-label">Public Key (BlackCat)</label>
                     <input type="text" name="blackcat_api_pix_public_key" class="form-control" value="<?= htmlspecialchars($blackcat_public) ?>">
                  </div>
               </div>
               <div id="plataforma_api_pix_blackcatpagamentos.online" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Secret Key (BlackCat)</label>
                     <input type="text" name="blackcat_api_pix_secret_key2" class="form-control" value="<?= htmlspecialchars($blackcat_secret) ?>">
                  </div>
                  <div class="form-group">
                     <label class="form-control-label">Public Key (BlackCat)</label>
                     <input type="text" name="blackcat_api_pix_public_key2" class="form-control" value="<?= htmlspecialchars($blackcat_public) ?>">
                  </div>
               </div>
               <div id="plataforma_api_pix_monetrix" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Secret Key (Monetrix)</label>
                     <input type="text" name="monetrix_api_pix_secret_key" class="form-control" value="<?= htmlspecialchars($monetrix_secret) ?>">
                  </div>
                  <div class="form-group">
                     <label class="form-control-label">Public Key (Monetrix)</label>
                     <input type="text" name="monetrix_api_pix_public_key" class="form-control" value="<?= htmlspecialchars($monetrix_public) ?>">
                  </div>
               </div>
               <div id="plataforma_api_pix_anubis_pay" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Secret Key (Anubis Pay)</label>
                     <input type="text" name="anubis_pay_api_pix_secret_key" class="form-control" value="<?= htmlspecialchars($anubis_secret) ?>">
                  </div>
                  <div class="form-group">
                     <label class="form-control-label">Public Key (Anubis Pay)</label>
                     <input type="text" name="anubis_pay_api_pix_public_key" class="form-control" value="<?= htmlspecialchars($anubis_public) ?>">
                  </div>
               </div>
               <div id="plataforma_api_pix_plumify" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">API Token (Plumify)</label>
                     <input type="text" name="plumify_api_pix_token" class="form-control" value="<?= htmlspecialchars($plumify_api_token_api) ?>">
                  </div>
                  <!--<div class="form-group">
                     <label class="form-control-label">Offer Hash  (Plumify)</label>
                     <input type="text" name="plumify_offerhash" class="form-control" value="<?= htmlspecialchars($plumify_offerhash) ?>">
                  </div>-->
               </div>
               <div id="plataforma_api_pix_zyntra_pay" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Secret Key (Zyntra Pay)</label>
                     <input type="text" name="zyntra_pay_api_pix_secret_key" class="form-control" value="<?= htmlspecialchars($zyntra_pay_secret_key) ?>">
                  </div>
                  <div class="form-group">
                     <label class="form-control-label">Company ID (Zyntra Pay)</label>
                     <input type="text" name="zyntra_pay_api_pix_company_id" class="form-control" value="<?= htmlspecialchars($zyntra_pay_company_id) ?>">
                  </div>
               </div>
               <div id="plataforma_api_pix_asset" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Secret Key (Asset)</label>
                     <input type="text" name="asset_api_pix_secret_key" class="form-control" value="<?= htmlspecialchars($asset_secret_key) ?>">
                  </div>
                  <div class="form-group">
                     <label class="form-control-label">Company ID (Asset)</label>
                     <input type="text" name="asset_api_pix_public_key" class="form-control" value="<?= htmlspecialchars($asset_public_key) ?>">
                  </div>
               </div>
               <div id="plataforma_api_pix_assetpay.com.br" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">API Token (Asset)</label>
                     <input type="text" name="asset_api_pix_api_token" class="form-control" value="<?= htmlspecialchars($asset_api_token) ?>">
                  </div>
               </div>
               <div id="plataforma_api_pix_paggue2" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Secret Key (Paggue)</label>
                     <input type="text" name="paggue_api_pix_secret_key" class="form-control" value="<?= htmlspecialchars($paggue_secret_key) ?>">
                  </div>
                  <div class="form-group">
                     <label class="form-control-label">Public Key (Paggue)</label>
                     <input type="text" name="paggue_api_pix_public_key" class="form-control" value="<?= htmlspecialchars($paggue_public_key) ?>">
                  </div>
               </div>
               <div id="plataforma_api_pix_optimus_pay" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Secret Key (Optimus Pay)</label>
                     <input type="text" name="optimus_pay_api_pix_secret_key" class="form-control" value="<?= htmlspecialchars($optimus_pay_secret_key) ?>">
                  </div>
                  <div class="form-group">
                     <label class="form-control-label">Public Key (Optimus Pay)</label>
                     <input type="text" name="optimus_pay_api_pix_public_key" class="form-control" value="<?= htmlspecialchars($optimus_pay_public_key) ?>">
                  </div>
               </div>
               <div id="plataforma_api_pix_nuvia_pay" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Secret Key (Nuvia Pay)</label>
                     <input type="text" name="nuvia_pay_api_pix_secret_key" class="form-control" value="<?= htmlspecialchars($nuvia_pay_secret_key) ?>">
                  </div>
                  <div class="form-group">
                     <label class="form-control-label">Public Key (Nuvia Pay)</label>
                     <input type="text" name="nuvia_pay_api_pix_public_key" class="form-control" value="<?= htmlspecialchars($nuvia_pay_public_key) ?>">
                  </div>
               </div>
               <div id="plataforma_api_pix_bynet" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">API Token (Bynet)</label>
                     <input type="text" name="bynet_api_pix_secret_key" class="form-control" value="<?= htmlspecialchars($bynet_api_token) ?>">
                  </div>
               </div>
               <div id="plataforma_api_pix_centurion_pay" class="div_api_pix" style="display: none;">
                  <div class="form-group">
                     <label class="form-control-label">Secret Key (Centurion Pay)</label>
                     <input type="text" name="centurion_pay_api_pix_secret_key" class="form-control" value="<?= htmlspecialchars($centurion_pay_secret_key) ?>">
                  </div>
                  <div class="form-group">
                     <label class="form-control-label">Company ID (Centurion Pay)</label>
                     <input type="text" name="centurion_pay_api_pix_public_key" class="form-control" value="<?= htmlspecialchars($centurion_pay_public_key) ?>">
                  </div>
               </div>
               <div class="form-group">
                  <input type="submit" value="Adicionar Gate" class="btn btn-primary">
               </div>
            </form>
         </div>
      </div>
      <script>
         document.addEventListener("DOMContentLoaded", function () {
             const select = document.querySelector('select[name="nome"]');
         
             function atualizarCampos() {
                 const selecionado = select.value;
         
                 document.querySelectorAll(".div_api_pix").forEach(div => {
                     div.style.display = "none";
                 });
         
                 if (selecionado) {
                     const idDiv = `plataforma_api_pix_${selecionado}`;
                     const divSelecionado = document.getElementById(idDiv);
                     if (divSelecionado) {
                         divSelecionado.style.display = "block";
                     }
                 }
             }
         
             atualizarCampos();
         
             select.addEventListener("change", atualizarCampos);
         });
      </script>
     <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong class="card-title">
            Gateway Cadastrado
        </strong>

        <?php if ($res && $res->num_rows > 0): ?>
        <form method="post" class="mb-0">
            <input type="hidden" name="DeletarGateway" value="1">
            <button type="submit" class="btn btn-danger btn-sm">
                <i class="fa fa-trash"></i> Deletar Gateway
            </button>
        </form>
        <?php endif; ?>
    </div>

    <div class="card-body">
        <?php if ($res && $res->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered mb-0">
                <tbody>
                    <tr>
                        <td style="width:35%;"><strong>Nome do Gateway</strong></td><td><?= htmlspecialchars($gatewayNome) ?></td>
                    </tr>
                    <?php
                    $titulos_campos = [
                        "token"                     => "Token (Mercado Pago)",
                        "cliente_id"                => "Client ID (Pixup)",
                        "cliente_secret"            => "Client Secret (Pixup)",
                        "apikey"                    => "API Key (Evopay)",
                        "cliente_public"            => "Chave Pública (Sigilopay)",
                        "cliente_privada"           => "Chave Privada (Sigilopay)",
                        "pronttus_id"               => "Client ID (Pronttus)",
                        "pronttus_secret"           => "Client Secret (Pronttus)",
                        "apitoken_unicoCash"        => "Token (UnicoCash)",
                        "asaas_token"               => "Access Token (Asaas)",
                        "tribo_pay_api_pix_access_token" => "Token de API (TriboPay)",
                        "marcha_api_pix_credencial_1"   => "Chave Privada (Marcha)",
                        "marcha_api_pix_credencial_2"   => "Chave Pública (Marcha)",
                        "lxpay_api_pix_credencial_1"    => "Chave Pública (LxPay)",
                        "lxpay_api_pix_credencial_2"    => "Chave Privada (LxPay)",
                        "rokify_secret"             => "Chave Secreta (Rokify)",
                        "rokity_id"                 => "Company ID (Rokify)",
                        "ironpay_tokenapi"          => "Token da API (Iron Pay)",
                        "ironpay_offerhash"         => "Offer Hash (Iron Pay)",
                        "carthero_secret"           => "Secret Key (CartHero)",
                        "carthero_public"           => "Public Key (CartHero)",
                        "plumify_api_token_api"     => "API Token (Plumify)",
                        "plumify_offerhash"         => "Offer Hash (Plumify)",
                        "paggue_client_key"         => "Client Key (Paggue)",
                        "paggue_client_secret"      => "Client Secret (Paggue)",
                        "paggue_token"              => "Token (Paggue)",
                        "paggue_secret_key"         => "Secret Key (Paggue)",
                        "paggue_public_key"         => "Public Key (Paggue)",
                        "tryplo_token"              => "Token (Tryplo Pay)",
                        "tryplo_chave_secreta"      => "Chave Secreta (Tryplo Pay)",
                        "free_pay_secret"           => "Secret Key (Free Pay)",
                        "free_pay_public"           => "Public Key (Free Pay)",
                        "titans_hub_secret"         => "Secret Key (Titans Hub)",
                        "titans_hub_public"         => "Public Key (Titans Hub)",
                        "payevo_secret"             => "Secret Key (Payevo)",
                        "payevo_company_id"         => "Company ID (Payevo)",
                        "podpay_secret"             => "Secret Key (PodPay)",
                        "podpay_public"             => "Public Key (PodPay)",
                        "infinity_secret"           => "Secret Key (Infinity Bank)",
                        "infinity_public"           => "Public Key (Infinity Bank)",
                        "street_secret"             => "Secret Key (Street Pay)",
                        "street_company_id"         => "Company ID (Street Pay)",
                        "clyptpay_secret"           => "Secret Key (ClyptPay)",
                        "clyptpay_public"           => "Public Key (ClyptPay)",
                        "quantum_secret"            => "Secret Key (Quantum Pay)",
                        "quantum_company_id"        => "Company ID (Quantum Pay)",
                        "grapefy_secret"            => "Secret Key (Grapefy)",
                        "grapefy_public"            => "Public Key (Grapefy)",
                        "pague_x_secret"            => "Secret Key (Pague X)",
                        "pague_x_public"            => "Public Key (Pague X)",
                        "pay_shark_secret"          => "Secret Key (Pay Shark)",
                        "pay_shark_public"          => "Public Key (Pay Shark)",
                        "blackcat_secret"           => "Secret Key (BlackCat)",
                        "blackcat_public"           => "Public Key (BlackCat)",
                        "monetrix_secret"           => "Secret Key (Monetrix)",
                        "monetrix_public"           => "Public Key (Monetrix)",
                        "anubis_secret"             => "Secret Key (Anubis Pay)",
                        "anubis_public"             => "Public Key (Anubis Pay)",
                        "zyntra_pay_secret_key"     => "Secret Key (Zyntra Pay)",
                        "zyntra_pay_company_id"     => "Company ID (Zyntra Pay)",
                        "asset_secret_key"          => "Secret Key (Asset)",
                        "asset_public_key"          => "Public Key (Asset)",
                        "asset_api_token"           => "API Token (Asset)",
                        "optimus_pay_secret_key"    => "Secret Key (Optimus Pay)",
                        "optimus_pay_public_key"    => "Public Key (Optimus Pay)",
                        "nuvia_pay_secret_key"      => "Secret Key (Nuvia Pay)",
                        "nuvia_pay_public_key"      => "Public Key (Nuvia Pay)",
                        "bynet_api_token"           => "API Token (Bynet)",
                        "centurion_pay_secret_key"  => "Secret Key (Centurion Pay)",
                        "centurion_pay_public_key"  => "Public Key (Centurion Pay)"
                    ];

                    foreach ($titulos_campos as $coluna => $titulo):
                        if (isset($gat[$coluna]) && !empty($gat[$coluna])):
                    ?>
                    <tr>
                        <td style="width:35%;">
                            <strong><?= htmlspecialchars($titulo) ?></strong>
                        </td>
                        <td><?= htmlspecialchars($gat[$coluna]) ?></td>
                    </tr>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info text-center" role="alert">
            Nenhum gateway cadastrado
        </div>
        <?php endif; ?>
    </div>
</div>