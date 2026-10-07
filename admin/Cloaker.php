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

$paises_lista = [
    'BR','AR','CL','PT','ES',
    'US','IN','RU','CN','UA',
    'VN','ID','TH','BD','PK',
    'NG','RO','PL','IR','TR'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['target'])) {

        $target = $_POST['target'];
        $value = $_POST['value'] ?? '';

        $permitidos = [
            'bloquear_sem_parametro_de_url',
            'parametro_de_url',
            'consultar_ip',
            'bloquear_mobile',
            'bloquear_desktop',
            'bloquear_android',
            'bloquear_ios',
            'asns',
            'isps',
            'ips',
            'user_agents',
            'paises_permitidos',
            'redirecionar',
            'redirecionar_para'
        ];

        if (in_array($target, $permitidos)) {

            if (in_array($target, [
                'bloquear_sem_parametro_de_url',
                'consultar_ip',
                'bloquear_mobile',
                'bloquear_desktop',
                'bloquear_android',
                'bloquear_ios',
                'redirecionar'
            ])) {
                $value = (int)$value;
            } else {
                $value = $conn->real_escape_string($value);
            }

            $col = ($target === 'redirecionar' || $target === 'redirecionar_para') ? 'cloaker_' . $target : $target;

            $conn->query("UPDATE cloaker SET `$col` = '$value' WHERE id = 1");

            echo json_encode(['status' => 'ok']);
            exit;
        }

        echo json_encode(['status' => 'error']);
        exit;
    }

    $bloquear_sem_parametro = (int)($_POST['bloquear_sem_parametro_de_url'] ?? 0);
    $parametro = $conn->real_escape_string($_POST['parametro_de_url'] ?? '');
    $paises = isset($_POST['paises_permitidos'])
        ? $conn->real_escape_string(implode(',', $_POST['paises_permitidos']))
        : '';
    $bloquear_mobile = (int)($_POST['bloquear_mobile'] ?? 0);
    $bloquear_desktop = (int)($_POST['bloquear_desktop'] ?? 0);
    $bloquear_android = (int)($_POST['bloquear_android'] ?? 0);
    $bloquear_ios = (int)($_POST['bloquear_ios'] ?? 0);
    $consultar_ip = (int)($_POST['consultar_ip'] ?? 0);
    $asns = $conn->real_escape_string($_POST['asns'] ?? '');
    $isps = $conn->real_escape_string($_POST['isps'] ?? '');
    $ips = $conn->real_escape_string($_POST['ips'] ?? '');
    $user_agents = $conn->real_escape_string($_POST['user_agents'] ?? '');
    $redirecionar = (int)($_POST['redirecionar'] ?? 0);
    $redirecionar_para = $conn->real_escape_string($_POST['redirecionar_para'] ?? '');

    $conn->query("UPDATE cloaker SET 
        bloquear_sem_parametro_de_url = '$bloquear_sem_parametro',
        parametro_de_url = '$parametro',
        paises_permitidos = '$paises',
        bloquear_mobile = '$bloquear_mobile',
        bloquear_desktop = '$bloquear_desktop',
        bloquear_android = '$bloquear_android',
        bloquear_ios = '$bloquear_ios',
        consultar_ip = '$consultar_ip',
        asns = '$asns',
        isps = '$isps',
        ips = '$ips',
        user_agents = '$user_agents',
        cloaker_redirecionar = '$redirecionar',
        cloaker_redirecionar_para = '$redirecionar_para'
        WHERE id = 1");

    echo '
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
$(function(){
    toastr.options.timeOut = false;
    toastr.options.closeButton = true;
    toastr.options.positionClass = "toast-top-right";
    toastr.success("Cloaker atualizado com sucesso!");
    setTimeout(function(){
        window.location.href = "?COMON=/ADMIN&acesso=Cloaker";
    },2000);
});
</script>';
}

$res = $conn->query("SELECT * FROM cloaker WHERE id = 1");
$cfg = $res->fetch_assoc();
$paises_salvos = array_filter(explode(',', $cfg['paises_permitidos']));
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
            <h1>Olá  admin, você está em  / Cloaker</h1>
            <label>TXT_JPGI1 - @TXT_JPGI1 <== no telegram!  </label>
         </div>
      </div>
   </div>
   <form method="POST">
      <small class="form-text alerta-critico"><b>Tenha muito cuidado com o que você vai fazer aqui, se errar em algo pode bloquear todos acessos, só altere algo aqui se souber o que você está fazendo<br>O cloaker já é configurado automaticamente para barrar as principais redes sociais.<b></small>
      <br>
      <div class="col-12 col-md-9"><br>
         <div class="form-group">
            <label>Bloquear acesso sem parametro de URL</label><br>
            <span>Ao ativar isso, seus links só serão acessados se tiver o parametro de URL abaixo.</span>
            <select name="bloquear_sem_parametro_de_url" class="form-control" onchange="this.form.submit()">
               <option value='0' <?= $cfg['bloquear_sem_parametro_de_url']==0?'selected':'' ?>>Não</option>
               <option value='1' <?= $cfg['bloquear_sem_parametro_de_url']==1?'selected':'' ?>>Sim</option>
            </select>
            <label>Parametro de URL</label><br>
            <span>Insira apenas letras e numeros, padrão aceito: parametro=valor</span>
            <input name="parametro_de_url" type='text' class="form-control" value="<?= htmlspecialchars($cfg['parametro_de_url']) ?>">
         </div>
      </div>
      <div class="col-12 col-md-9"><br>
         <div class="form-group">
            <label>Consultar IP</label><br>
            <span>Faz a consulta do IP do clique para identificar se é uma pessoa real.<br>Altamente recomendado deixar ativado.<br>Se desativar ganha desempenho quando a pessoa clica no link, mas seu dominio corre mais riscos de cair.</span>
            <select name="consultar_ip" class="form-control" onchange="this.form.submit()">
               <option value='0' <?= $cfg['consultar_ip']==0?'selected':'' ?>>Não</option>
               <option value='1' <?= $cfg['consultar_ip']==1?'selected':'' ?>>Sim</option>
            </select>
         </div>
      </div>
      <div class="col-12 col-md-9"><br>
         <div class="form-group">
            <label>Redirecionar ao bloquear</label><br>
            <span>Redirecionar para algum link especifico quando bloquear alguem.</span>
            <select name="redirecionar" class="form-control" onchange="this.form.submit()">
               <option value='0' <?= $cfg['cloaker_redirecionar']==0?'selected':'' ?>>Não</option>
               <option value='1' <?= $cfg['cloaker_redirecionar']==1?'selected':'' ?>>Sim</option>
            </select><br>
            <label>Link para redirecionar</label>
            <input name="redirecionar_para" type='text' class="form-control" value="<?= htmlspecialchars($cfg['cloaker_redirecionar_para'] ?? '') ?>">
         </div>
      </div>
      <div class="col-12 col-md-9"><br>
         <div class="form-group">
            <label>Bloquear ASN</label><br>
            <span>Um ASN por linha</span>
            <textarea name="asns" class="form-control" rows="5"><?= htmlspecialchars($cfg['asns'] ?? '') ?></textarea>
         </div>
      </div>
      <div class="col-12 col-md-9"><br>
         <div class="form-group">
            <label>Bloquear ISP</label><br>
            <span>Insira abaixo os ISPs que você quer que sejam bloqueados.<br>funciona apenas com a consulta de ips ativada!</span><br>
            <span>Um ISP por linha</span>
            <textarea name="isps" class="form-control" rows="5"><?= htmlspecialchars($cfg['isps'] ?? '') ?></textarea>
         </div>
      </div>
      <div class="col-12 col-md-9"><br>
         <div class="form-group">
            <label>Bloquear IP</label><br>
            <span>Um IP por linha</span>
            <textarea name="ips" class="form-control" rows="5"><?= htmlspecialchars($cfg['ips'] ?? '') ?></textarea>
         </div>
      </div>
      <div class="col-12 col-md-9"><br>
         <div class="form-group">
            <label>Bloquear User Agent (navegador)</label><br>
            <span>Um User Agent por linha</span>
            <textarea name="user_agents" class="form-control" rows="5"><?= htmlspecialchars($cfg['user_agents'] ?? '') ?></textarea>
         </div>
      </div>
      <div class="col-12 col-md-9"><br>
         <div class="form-group">
            <label>Paises permitidos</label><br>
            <span>Os países que estiverem marcados NÃO serão bloqueados.</span><br>
            <div class="box-paises">
               <ul class="lista-paises">
                  <?php
                     $nomes = [
                         'BR' => 'Brasil',
                         'AR' => 'Argentina',
                         'CL' => 'Chile',
                         'PT' => 'Portugal',
                         'ES' => 'Espanha',
                         'US' => 'Estados Unidos',
                         'IN' => 'Índia',
                         'RU' => 'Rússia',
                         'CN' => 'China',
                         'UA' => 'Ucrânia',
                         'VN' => 'Vietnã',
                         'ID' => 'Indonésia',
                         'TH' => 'Tailândia',
                         'BD' => 'Bangladesh',
                         'PK' => 'Paquistão',
                         'NG' => 'Nigéria',
                         'RO' => 'Romênia',
                         'PL' => 'Polônia',
                         'IR' => 'Irã',
                         'TR' => 'Turquia'
                     ];
                     foreach ($paises_lista as $p) {
                         $checked = in_array($p, $paises_salvos) ? 'checked' : '';
                     echo "<li><label><input type='checkbox' name='paises_permitidos[]' value='$p' $checked onchange='this.form.submit()'>{$nomes[$p]}</label></li>";
                     }
                     ?>
               </ul>
            </div>
         </div>
      </div>
      <div class="col-12 col-md-9">
         <div class="form-group">
            <label>Bloquear acesso Mobile</label><br>
            <select name="bloquear_mobile" class="form-control" onchange="this.form.submit()">
               <option value='0' <?= $cfg['bloquear_mobile']==0?'selected':'' ?>>Não</option>
               <option value='1' <?= $cfg['bloquear_mobile']==1?'selected':'' ?>>Sim</option>
            </select>
         </div>
      </div>
      <div class="col-12 col-md-9">
         <div class="form-group">
            <label>Bloquear acesso Desktop</label><br>
            <select name="bloquear_desktop" class="form-control" onchange="this.form.submit()">
               <option value='0' <?= $cfg['bloquear_desktop']==0?'selected':'' ?>>Não</option>
               <option value='1' <?= $cfg['bloquear_desktop']==1?'selected':'' ?>>Sim</option>
            </select>
         </div>
      </div>
      <div class="col-12 col-md-9" style='display:none;'>
         <div class="form-group">
            <label>Bloquear acesso Android</label><br>
            <select name="bloquear_android" class="form-control" onchange="this.form.submit()">
               <option value='0' <?= $cfg['bloquear_android']==0?'selected':'' ?>>Não</option>
               <option value='1' <?= $cfg['bloquear_android']==1?'selected':'' ?>>Sim</option>
            </select>
         </div>
      </div>
      <div class="col-12 col-md-9" style='display:none;' onchange="this.form.submit()">
         <div class="form-group">
            <label>Bloquear acesso IOS</label><br>
            <select name="bloquear_ios" class="form-control">
               <option value='0' <?= $cfg['bloquear_ios']==0?'selected':'' ?>>Não</option>
               <option value='1' <?= $cfg['bloquear_ios']==1?'selected':'' ?>>Sim</option>
            </select>
         </div>
      </div>
      <div class="col-12 col-md-9 mt-3"><button type="submit" class="btn btn-primary">Salvar Cloaker</button>
      </div>
   </form>