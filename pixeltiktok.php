<?php
require_once __DIR__ . '/config/config.php';

try {
    $stmt = $conexao->prepare("SELECT * FROM tiktok ORDER BY id DESC LIMIT 1");
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

                <!-- TikTok Pixel Code Start -->
                <script>
                    function get_param(name) { const results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href); return results ? decodeURI(results[1]) : null; }
                    !function (w, d, t) {
                    w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=['page','track','identify','instances','debug','on','off','once','ready','alias','group','enableCookie','disableCookie','holdConsent','revokeConsent','grantConsent'],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(
                    var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var r='https://analytics.tiktok.com/i18n/pixel/events.js',o=n&&n.partner;ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=r,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};n=document.createElement('script'); n.type='text/javascript',n.async=!0,n.src=r+'?sdkid='+e+'&lib='+t;e=document.getElementsByTagName('script')[0];e.parentNode.insertBefore(n,e)};
                    ttq.load('<?= $pixelId ?>');
                    ttq.identify({
                        'external_id': '<?= hash('sha256',$pixelId) ?>'
                    });
                    }(window, document, 'ttq');
                    window.addEventListener('DOMContentLoaded', (event) => {
                        var q_2 = 0;
                        var interval_2 = setInterval(function(){
                            q_2++;
                            try{
                                if(localStorage.getItem('evento_purchase_do_tiktok')!==null){
                                    acionar_pixel_do_tiktok('Pageview');
                                    if(get_param('tt_test_id')!=null) ttq.track('pixel_instalado');
                                    clearInterval(interval_2);
                                }
                            }catch(e){}
                            if(q_2>=20) clearInterval(interval_2);
                        },500);
<?php if ($ehInicio): ?>

                var q_1 = 0;
                var interval_1 = setInterval(function(){
                    q_1++;
                    try{
                        if(localStorage.getItem('evento_purchase_do_tiktok')!==null){
                            acionar_pixel_do_tiktok('ViewContent');
                            clearInterval(interval_1);
                        }
                    }catch(e){}
                    if(q_1>=20) clearInterval(interval_1);
                },500);
<?php endif; ?>

                    });
                </script>
                <!-- TikTok Pixel Code End -->        