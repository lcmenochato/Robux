<?php
require_once __DIR__ . '/config/config.php';

try {
    $stmt = $conexao->prepare("SELECT * FROM pixel ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $pixel = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $pixel = null;
}

if (!$pixel || empty($pixel['pixelid'])) {
    return;
}

$pixelId = htmlspecialchars($pixel['pixelid'], ENT_QUOTES, 'UTF-8');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$rota = trim($uri, '/');
$partes = explode('/', $rota);
$pagina = end($partes);

$ehInicio = ($pagina === 'inicio' || count($partes) === 1);
$ehofertas = ($pagina === 'ofertas');
$ehpassageiros = ($pagina === 'passageiros');
$ehpagamento = ($pagina === 'pagamento');
?>

                <!-- Meta Pixel Code -->
                <script>
                    function get_param(name) { const results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href); return results ? decodeURI(results[1]) : null; }
                    !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};
                    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
                    n.queue=[];t=b.createElement(e);t.async=!0;
                    t.src=v;s=b.getElementsByTagName(e)[0];
                    s.parentNode.insertBefore(t,s)}(window, document,'script','https://connect.facebook.net/en_US/fbevents.js');
                    fbq('init', '<?= $pixelId ?>');
                    window.addEventListener('DOMContentLoaded', (event) => {
                        var q_4 = 0;
                        var interval_4 = setInterval(function(){
                            q_4++;
                            try{
                                if(localStorage.getItem('evento_purchase_da_meta')!==null){
                                    acionar_pixel_da_meta('PageView');
                                    clearInterval(interval_4);
                                }
                            }catch(e){}
                            if(q_4>=20) clearInterval(interval_4);
                        },500);
                        <?php if ($ehInicio): ?>

                var q_3 = 0;
                var interval_3 = setInterval(function(){
                    q_3++;
                    try{
                        if(localStorage.getItem('evento_purchase_da_meta')!==null){
                            acionar_pixel_da_meta('ViewContent');
                            clearInterval(interval_3);
                        }
                    }catch(e){}
                    if(q_3>=20) clearInterval(interval_3);
                },500);
<?php endif; ?>

                    });
                </script>
                <noscript><img height='1' width='1' style='display:none' src='https://www.facebook.com/tr?id=<?= $pixelId ?>&ev=PageView&noscript=1'/></noscript>
                <!-- End Meta Pixel Code -->        