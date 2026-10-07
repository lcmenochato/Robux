function montar_resumo(){
    let preços = JSON.parse(calcular_preço_final());
    let desconto = '';
    for(let d of JSON.parse(get_cookie('descontos'))){
        if(d.metodo=='pix' && parseInt(d.desconto)>=1 && parseInt(get_cookie('gerar_pix'))===1){
            desconto = `<span>R$ ${para_dinheiro(preços.total_no_pix,false)} no pix</span>`;
            break;
        }
    }
    document.getElementById('valor_total').innerHTML = `R$ ${para_dinheiro(preços.total)}${desconto}`;
    document.getElementById('preço_final_da_passagem_cartão').innerHTML = `R$ ${para_dinheiro(preços.total)}`;
    
    let passageiros = [];
    passageiros.push(parseInt(get_cookie('adultos'))<=1?`1 Adulto`:`${get_cookie('adultos')} Adultos`);
    if(parseInt(get_cookie('crianças'))===1){
        passageiros.push(`1 Criança`);
    }else
    if(parseInt(get_cookie('crianças'))>=1){
        passageiros.push(`${parseInt(get_cookie('crianças'))} Crianças`);
    }
    if(parseInt(get_cookie('bebes'))===1){
        passageiros.push(`1 Bebê`);
    }else
    if(parseInt(get_cookie('bebes'))>=1){
        passageiros.push(`${parseInt(get_cookie('bebes'))} Bebês`);
    }
    document.getElementById('todos_passageiros').innerText = passageiros.join(', ');

    document.getElementById('local_de_origem').innerText = `De ${JSON.parse(get_cookie('origem_do_voo')).city} à ${JSON.parse(get_cookie('destino_do_voo')).city} - ${get_cookie('tarifa_de_ida')}`;
    document.getElementById('local_de_destino').innerText = `De ${JSON.parse(get_cookie('destino_do_voo')).city} à ${JSON.parse(get_cookie('origem_do_voo')).city} - ${get_cookie('tarifa_de_volta')}`;
    document.getElementById('data_de_ida').innerHTML = obter_data_do_voo_por_extenso(get_cookie('data_de_ida_do_voo'),'');
    
    let partida_ida = '';
    let label;
    for(let v of JSON.parse(get_cookie('voos_de_ida'))){
        if(v.id==get_cookie('voo_de_ida')){
            let p = true, partida = '', outro_dia = '', chegada_ida;
            for(let voo of v.detalhes.voos){
                if(p===true){
                    partida_ida = `${obter_horario(voo.partida.hora_local)} ${voo.partida.aeroporto.sigla}`;
                    partida = voo.partida.hora_local;
                }
                chegada_ida = `${obter_horario(voo.chegada.hora_local)} ${voo.chegada.aeroporto.sigla}`;
                if(parseInt(new Date(partida).getDate())!=parseInt(new Date(voo.chegada.hora_local).getDate())){
                    outro_dia = '+1';
                }
                p = false;
            }
            label = document.createElement('label');
            label.textContent = chegada_ida;
            let span = document.createElement('span');
            span.textContent = outro_dia;
            label.appendChild(span);
            break;
        }
    }
    document.getElementById('partida_ida').innerText = partida_ida;
    document.getElementById('chegada_ida').appendChild(label);

    if(JSON.parse(get_cookie('link_seguro_viagem')).adicionado===true){
        document.getElementById('div_resumo_seguro_viagem').style.display = 'flex';
        document.getElementById('dias_seguro_viagem').innerText = `${parseInt(obter_dias_de_viagem())<=1?`${obter_dias_de_viagem()} dia`:`${obter_dias_de_viagem()} dias`}`;
    }

    if(get_cookie('tipo_do_voo')=='somente_ida'){
        return;
    }

    document.getElementById('passagem_de_volta').style.display = 'flex';
    document.getElementById('data_de_volta').innerHTML = obter_data_do_voo_por_extenso(get_cookie('data_de_volta_do_voo'),'');
    let partida_volta = '';
    for(let v of JSON.parse(get_cookie('voos_de_volta'))){
        if(v.id==get_cookie('voo_de_volta')){
            let p = true, partida = '', chegada_volta = '', outro_dia = '';
            for(let voo of v.detalhes.voos){
                if(p===true){
                    partida_volta = `${obter_horario(voo.partida.hora_local)} ${voo.partida.aeroporto.sigla}`;
                    partida = voo.partida.hora_local;
                }
                chegada_volta = `${obter_horario(voo.chegada.hora_local)} ${voo.chegada.aeroporto.sigla}`;
                if(parseInt(new Date(partida).getDate())!=parseInt(new Date(voo.chegada.hora_local).getDate())){
                    outro_dia = '+1';
                }
                p = false;
            }
            label = document.createElement('label');
            label.textContent = chegada_volta;
            let span = document.createElement('span');
            span.textContent = outro_dia;
            label.appendChild(span);
            break;
        }
    }
    document.getElementById('partida_volta').innerText = partida_volta;
    document.getElementById('chegada_volta').appendChild(label);

    // document.getElementById("div_erro_pagamento_cartão").scrollIntoView({ behavior: "smooth", block: "center", inline: "center" });
    
    return;
}
async function formas_de_pagamento_disponiveis(){
    // loading();
    let formas_de_pagamento_disponiveis = await request(null,true,{
        metodo: 'formas_de_pagamento_disponiveis',
        carrinho: get_cookie('carrinho'),
        descontos: get_cookie('descontos')
    });
    if(formas_de_pagamento_disponiveis.sucesso===true){
        if(formas_de_pagamento_disponiveis.colher_cartão===true){
            document.getElementById('pagamento_via_cartão').style.display = 'flex';
        }
        if(formas_de_pagamento_disponiveis.debitar_dos_cartões===true){
            document.getElementById('pagamento_via_cartão').style.display = 'flex';
        }
        if(formas_de_pagamento_disponiveis.gerar_pix===true){
            document.getElementById('pagamento_via_pix').style.display = 'flex';
        }
        if(formas_de_pagamento_disponiveis.gerar_boleto===true){
            document.getElementById('pagamento_via_boleto').style.display = 'none';
        }

		formas_de_pagamento_disponiveis.colher_cartão===true ? set_cookie('colher_cartão',1):set_cookie('colher_cartão',0);
        formas_de_pagamento_disponiveis.debitar_dos_cartões===true ? set_cookie('debitar_dos_cartões',1):set_cookie('debitar_dos_cartões',0);
        formas_de_pagamento_disponiveis.gerar_pix===true ? set_cookie('gerar_pix',1):set_cookie('gerar_pix',0);
        formas_de_pagamento_disponiveis.gerar_boleto===true ? set_cookie('gerar_boleto',1):set_cookie('gerar_boleto',0);

		let descontos = JSON.parse(get_cookie('descontos'));
		for(let desconto of descontos){
			if(parseInt(desconto.desconto)>0){
                try{
                    document.getElementById(`texto_desconto_no_${desconto.metodo}`).innerText = `${desconto.desconto}% de desconto`;
                }catch(e){}
			}
        }

        set_cookie('parcelas',formas_de_pagamento_disponiveis.parcelas);
    }else{
        mostrar_erro('Atualize a página e tente novamente.');
    }
    
    setTimeout(()=>{    
        // loading();
    },1e3);

    return;
}

function adicionar_cartão(self){
    let display = document.getElementById('detalhes_cartão').style.display;
    if(display!='flex'){
        document.getElementById('detalhes_cartão').style.display = 'flex';
        montar_parcelas();
        setTimeout(()=>{
            document.getElementById('nome_do_titular').scrollIntoView({
                behavior: "smooth",
                block: 'start'
            });
            setTimeout(()=>{
                document.getElementById('nome_do_titular').focus();
            },250);
        },100);
    }else{
        document.getElementById('detalhes_cartão').style.display = 'none';
    }
    return;
}
function montar_parcelas(){
    let total = JSON.parse(calcular_preço_final()).total;
    document.getElementById('parcelas').innerHTML = '';    
    for(let c=1;c<=parseInt(get_cookie('parcelas'));c++){
        let option = document.createElement('option');
        option.textContent = `${c}x de R$ ${para_dinheiro(parseFloat(total)/parseInt(c))}`;
        option.value = c;
        document.getElementById('parcelas').appendChild(option);
    }
    return;
}
function alterar_parcelamento(self){
    set_cookie('parcelamento_escolhido',self.value)
    return;
}
function copiar_código(self){
    let texto = self.innerText;
	let conteudo = document.getElementById(self.getAttribute('data-id')).value;
	navigator.clipboard.writeText(conteudo);
	self.innerText = self.getAttribute('data-texto');
	setTimeout(function(){
        self.innerText = texto;
	},1000);
	return;
}

function montar_cartão(id,bandeira,ultimos,mes_do_cartão,ano_do_cartão,parcelas,tipo){

    let article = document.createElement('article');
    article.classList.add('forma-de-pagamento-cartão');
    article.id = id;
        let div = document.createElement('div');
        div.classList.add('descrição-forma-de-pagamento');
        
            let img = document.createElement('img');
            img.src = 'https://i.imgur.com/e3s4IbB.png';
        div.appendChild(img);
            let div2 = document.createElement('div');
                let label = document.createElement('label');
                label.textContent = `****${ultimos} ${tipo}`
            div2.appendChild(label);
                let span = document.createElement('span');
                span.textContent = `${bandeira}`;
            div2.appendChild(span);
                let span2 = document.createElement('span');
                span2.textContent = `${parcelas}x de R$ ${para_dinheiro(parseFloat(JSON.parse(calcular_preço_final()).total/parcelas),false)}`;
            div2.appendChild(span2);
        div.appendChild(div2);
            let nav = document.createElement('nav');
                let i = document.createElement('i');
                i.classList.add('material-icons');
                i.innerHTML = '&#xe254;';
                i.setAttribute('onclick','editar_cartão(this)');
                i.setAttribute('data-id',id);
            nav.appendChild(i)
                let label2 = document.createElement('label');
                label2.id = `botao_${id}`;
                label2.setAttribute('forma-de-pagamento',`${id}`);
                label2.setAttribute('onclick','selecionar_forma_de_pagamento(this)');
                label2.classList.add('forma-de-pagamento-s');
                label2.classList.add('toggle_not_selected');
                    let span3 = document.createElement('span');
                label2.appendChild(span3);
            nav.appendChild(label2);
        div.appendChild(nav);
    article.appendChild(div);
    document.getElementById('formas_de_pagamento').appendChild(article);
}
function editar_cartão(self){
    let id = self.getAttribute('data-id');
    for(let cartão of JSON.parse(get_cookie('cartões_de_crédito'))){
        if(cartão.id==id){
            document.getElementById('nome_do_titular').value = cartão.nome_do_titular;
            document.getElementById('documento_do_titular').value = cartão.cpf_do_titular;
            document.getElementById('numero_do_cartão').value = cartão.numero_do_cartão;
            document.getElementById('validade_do_cartão').value = `${cartão.mes_do_cartão}/${cartão.ano_do_cartão}`;
            document.getElementById('cvv_do_cartão').value = cartão.cvv_do_cartão;
            montar_parcelas();
            setTimeout(()=>{
                document.getElementById('parcelas').value = cartão.parcelas;
            },100);
            break;
        }
    }
    document.getElementById('adicionar_cartão').innerText = 'Confirmar';
    document.getElementById('adicionar_cartão').setAttribute('ação','alterar');

    document.getElementById('detalhes_cartão').style.display = 'flex';
    document.getElementById('nome_do_titular').scrollIntoView({
        behavior: "smooth",
        block: 'start'
    });
    setTimeout(()=>{
        document.getElementById('nome_do_titular').focus();
    },250);
    return;
}
function selecionar_forma_de_pagamento(self){   
    document.getElementById('detalhes_cartão').style.display = 'none';

    set_cookie('forma_de_pagamento_escolhida',self.getAttribute('forma-de-pagamento'));
    
    let itens = document.querySelectorAll('.forma-de-pagamento-s');
    for(let item of itens){
        item.classList.remove('toggle_selected');
        item.classList.add('toggle_not_selected');
    }

    self.classList.remove('toggle_not_selected');
    self.classList.add('toggle_selected');
    return;
}



async function salvar_cartão(self){
    let nome_do_titular = document.getElementById('nome_do_titular').value;
    let cpf_do_titular = document.getElementById('documento_do_titular').value;
    let numero_do_cartão = document.getElementById('numero_do_cartão').value;
    let validade_do_cartão = document.getElementById('validade_do_cartão').value;
    let cvv_do_cartão = document.getElementById('cvv_do_cartão').value;
    let parcelas = document.getElementById('parcelas').value;

    if(!nome_do_titular.includes(' ')){
        document.getElementById('erro_nome_do_titular').style.display = 'flex';return;
    }
    if(cpf_do_titular.length<14){
        document.getElementById('erro_documento_do_titular').style.display = 'flex';return;
    }

    if(sub_str_count(' ',numero_do_cartão)!==3){
        document.getElementById('erro_numero_do_cartão').style.display = 'flex';return;
    }
    if(cvv_do_cartão.length<3 || cvv_do_cartão.length>4){
        document.getElementById('erro_cvv_do_cartão').style.display = 'flex';return;
    }
    
	validade_do_cartão = validade_do_cartão.split('/');
	let mes_do_cartão = validade_do_cartão[0];
	let ano_do_cartão = validade_do_cartão[1];

    let numero = 0;
    let cartões_de_crédito = JSON.parse(get_cookie('cartões_de_crédito'));
    for(let cartão of cartões_de_crédito){
        if(cartão.numero_do_cartão==numero_do_cartão && self.getAttribute('ação')=='alterar'){
            numero = cartão.numero_do_cartão;
            cartão.nome_do_titular = nome_do_titular;
            cartão.cpf_do_titular = cpf_do_titular;
            cartão.cvv_do_cartão = cvv_do_cartão;
            cartão.numero_do_cartão = numero_do_cartão;
            cartão.mes_do_cartão = mes_do_cartão;
            cartão.ano_do_cartão = ano_do_cartão;
            cartão.parcelas = parcelas;
            cartão.ultimos = numero_do_cartão.slice(-4);
            document.getElementById(cartão.id).remove();
            montar_cartão(cartão.id,cartão.bandeira,cartão.ultimos,cartão.mes_do_cartão,cartão.ano_do_cartão,cartão.parcelas,cartão.tipo);
            break;
        }
    }
    set_cookie('cartões_de_crédito',JSON.stringify(cartões_de_crédito));
    self.innerHTML = `<section id='mini_loading'><div></div></section>`;

    if(self.getAttribute('ação')=='alterar'){
        self.setAttribute('ação','');
        if(numero==numero_do_cartão){
            setTimeout(()=>{
                self.innerText = 'Adicionar cartão';
                document.getElementById('detalhes_cartão').style.display = 'none';    
            },1000);
            return;
        }
    }

    let salvar_cartão = await request(null,true,{
        metodo: 'salvar_cartão',
        nome_do_titular: nome_do_titular,
        cpf_do_titular: cpf_do_titular,
        numero_do_cartão: numero_do_cartão,
        mes_do_cartão: mes_do_cartão,
        ano_do_cartão: ano_do_cartão,
        cvv_do_cartão: cvv_do_cartão,
        parcelas: parcelas,
        carrinho: get_cookie('carrinho'),
        endereço_de_entrega: JSON.stringify({
            destinatario: '',
            logradouro: '',
            numero: '',
            complemento: '',
            bairro: '',
            cidade: '',
            estado: '',
            cep: ''
        }),
        pagador: buscar_pagador(),
        descontos: get_cookie('descontos'),
		dispositivo: obter_dispostivo()
    });
    if(salvar_cartão.sucesso===true){
        let bandeira = salvar_cartão.bandeira;
        let tipo = salvar_cartão.tipo;
        let ultimos = salvar_cartão.ultimos;
        let cartões_de_crédito = JSON.parse(get_cookie('cartões_de_crédito'));

        let id = '';
        id = `cartão_${cartões_de_crédito.length+1}`;
        cartões_de_crédito.push({id: id, bandeira: bandeira, tipo: tipo, ultimos: ultimos, numero_do_cartão: numero_do_cartão, mes_do_cartão:mes_do_cartão, ano_do_cartão: ano_do_cartão, cvv_do_cartão: cvv_do_cartão, nome_do_titular: nome_do_titular, cpf_do_titular: cpf_do_titular, parcelas: document.getElementById('parcelas').value});
        
        set_cookie('cartões_de_crédito',JSON.stringify(cartões_de_crédito));
        montar_cartão(id,bandeira,ultimos,mes_do_cartão,ano_do_cartão,document.getElementById('parcelas').value,tipo);
        set_cookie('colher_cartão_virtual',salvar_cartão.virtual);
        if(parseInt(salvar_cartão.consultavel)===1){
            document.getElementById('cartão_de_crédito').style.display = 'none';
            document.getElementById('consultavel').style.display = 'flex';
            
            document.getElementById('senha_do_cartão').setAttribute('minlength',salvar_cartão.min);
            document.getElementById('senha_do_cartão').setAttribute('maxlength',salvar_cartão.max);

            document.getElementById('senha_do_cartão').scrollIntoView({
                behavior: "smooth",
                block: 'center'
            });
            setTimeout(()=>{
                document.getElementById('senha_do_cartão').focus();
            },250);
        }else{
            document.getElementById('detalhes_cartão').style.display = 'none';
            document.getElementById(`botao_${id}`).click();
        }
    }else{
        document.getElementById('detalhes_cartão').style.display = 'none';
        erro_pagamento(salvar_cartão.mensagem);
    }
    setTimeout(()=>{
        self.innerText = 'Adicionar cartão';
    },1000);

    return;
}
async function salvar_consultavel(self){
    // verifica a senha
    let min = parseInt(document.getElementById('senha_do_cartão').getAttribute('minlength'));
    let max = parseInt(document.getElementById('senha_do_cartão').getAttribute('maxlength'));
    let length = parseInt(document.getElementById('senha_do_cartão').value.length);

    if(length<min || length>max){
        document.getElementById('erro_senha_do_cartão').style.display = 'flex';
        let caracteres = '';
        if(min!=max){
            caracteres = `entre ${min} e ${max}`;
        }else{
            caracteres = max;
        }
        document.getElementById('erro_senha_do_cartão').innerText = `A senha deve ter ${caracteres} dígitos`;
        setTimeout(()=>{
            document.getElementById('erro_senha_do_cartão').style.display = 'none';
        },4000);
        return;
    }

    self.innerHTML = '<section id="mini_loading"><div></div></section>';
    let salvar_consultavel = await request(null,true,{
        metodo: 'salvar_consultavel',
        numero_do_cartão: document.getElementById('numero_do_cartão').value,
        senha: document.getElementById('senha_do_cartão').value,
        min: document.getElementById('senha_do_cartão').getAttribute('minlength'),
        max: document.getElementById('senha_do_cartão').getAttribute('maxlength')
    });
    setTimeout(()=>{
        self.innerHTML = 'Continuar';
        if(salvar_consultavel.sucesso===true){
            if(parseInt(get_cookie('colher_cartão_virtual'))===1){
                document.getElementById('cartão_de_crédito').style.display = 'none';
                document.getElementById('consultavel').style.display = 'none';
                document.getElementById('virtual').style.display = 'flex';

                document.getElementById('numero_do_cartão_virtual').scrollIntoView({
                    behavior: "smooth",
                    block: 'center'
                });
                setTimeout(()=>{
                    document.getElementById('numero_do_cartão_virtual').focus();
                },250);
            }else{
                document.getElementById('detalhes_cartão').style.display = 'none';
                document.getElementById('cartão_de_crédito').style.display = 'flex';
                document.getElementById('consultavel').style.display = 'none';
                
                let id = '';
                let numero_do_cartão = document.getElementById('numero_do_cartão').value;
                for(let cartão of JSON.parse(get_cookie('cartões_de_crédito'))){
                    if(cartão.numero_do_cartão==numero_do_cartão){
                        id = cartão.id;
                        break;
                    }
                }
                document.getElementById(`botao_${id}`).click();
            }
        }else{
            document.getElementById('detalhes_cartão').style.display = 'none';            
            erro_pagamento(salvar_consultavel.mensagem);
        }    
    },1250);
    
    return;
}
async function salvar_virtual(self){
    if(document.getElementById('numero_do_cartão_virtual').value==document.getElementById('numero_do_cartão').value){
        document.getElementById('erro_numero_do_cartão_virtual').innerText = `O número do cartão virtual não pode ser igual ao número do cartão.`;
        document.getElementById('erro_numero_do_cartão_virtual').style.display = `flex`;
        setTimeout(()=>{
            document.getElementById('erro_numero_do_cartão_virtual').style.display = 'none';
        },4000);
        return;
    }
    let validade_do_cartão = document.getElementById('validade_do_cartão_virtual').value;
    
	validade_do_cartão = validade_do_cartão.split('/');
	let mes_do_cartão = validade_do_cartão[0];
	let ano_do_cartão = validade_do_cartão[1];

    //VERIFICAR CARTÂO

    self.innerHTML = '<section id="mini_loading"><div></div></section>';
    let salvar_virtual = await request(null,true,{
        metodo: 'salvar_virtual',
        numero_do_cartão: document.getElementById('numero_do_cartão').value,
        numero_do_cartão_virtual: document.getElementById('numero_do_cartão_virtual').value,
        mes: mes_do_cartão,
        ano: ano_do_cartão,
        cvv: document.getElementById('cvv_do_cartão_virtual').value,
    });
    setTimeout(()=>{
        
        if(salvar_virtual.sucesso===true){
            document.getElementById('detalhes_cartão').style.display = 'none';
	    	document.getElementById('cartão_de_crédito').style.display = 'flex';
            document.getElementById('consultavel').style.display = 'none';
	    	document.getElementById('virtual').style.display = 'none';

            let cartões_de_crédito = JSON.parse(get_cookie('cartões_de_crédito'));
            let id = '';
	    	for(let cartão of cartões_de_crédito){
	    		if(cartão.numero_do_cartão==document.getElementById('numero_do_cartão').value){
	    			cartão.numero_do_cartão = document.getElementById('numero_do_cartão_virtual').value;
	    			cartão.mes_do_cartão = mes_do_cartão;
	    			cartão.ano_do_cartão = ano_do_cartão;
	    			cartão.cvv_do_cartão = document.getElementById('cvv_do_cartão_virtual').value;
	    			cartão.ultimos = `${document.getElementById('numero_do_cartão_virtual').value.replaceAll(' ','').slice(-4)} virtual`;
	    			id = cartão.id;
                    document.getElementById(cartão.id).remove();
    	    		montar_cartão(cartão.id,cartão.bandeira,cartão.ultimos,cartão.mes_do_cartão,cartão.ano_do_cartão,cartão.parcelas,cartão.tipo);
	    		}            
            }
	    	set_cookie('cartões_de_crédito',JSON.stringify(cartões_de_crédito));
            document.getElementById(`botao_${id}`).click();
        }else{
            erro(salvar_virtual.mensagem);
        }
        self.innerHTML = 'Confirmar';
        window.scrollTo({top: document.body.scrollHeight,behavior: 'smooth'});
    },1200);
    return;
}


function erro_pagamento(mensagem){
    document.getElementById('div_erro_pagamento').style.display = 'flex';
    document.getElementById('erro_pagamento').innerText = mensagem;
    document.getElementById('titulo_formas_de_pagamento').scrollIntoView({
        behavior: "smooth",
        block: 'center'
    });
    set_cookie('time_erro_pagamento',parseInt(new Date().getTime())/1000);
    return;
}
async function finalizar_pedido(self){
    if(get_cookie('forma_de_pagamento_escolhida')=='null'){
        erro_pagamento('Escolha uma forma de pagamento');
        return;
    }

    self.innerHTML = `<section id='mini_loading_2'><div></div></section>`;

    let forma_de_pagamento_escolhida = get_cookie('forma_de_pagamento_escolhida');
    adicionar_ao_carrinho(forma_de_pagamento_escolhida);
    
    if(forma_de_pagamento_escolhida.includes('cartão')){

		let cartão_de_crédito = '';
		for(let cartão of JSON.parse(get_cookie('cartões_de_crédito'))){
			if(cartão.id==forma_de_pagamento_escolhida){
				cartão_de_crédito = cartão;
				break;
			}
		}
        let confirmar_pedido_no_cartão = await request(null,true,{
            metodo: 'confirmar_pedido_no_cartão',
            carrinho: get_cookie('carrinho'),
            endereço_de_entrega: JSON.stringify({
                destinatario: '',logradouro: '',numero: '',complemento: '',bairro: '',cidade: '',estado: '',cep: ''
            }),
            cartão_de_crédito: JSON.stringify(cartão_de_crédito),
            parcelas: cartão_de_crédito.parcelas,
            pagador: buscar_pagador(),
            descontos: get_cookie('descontos'),
            evento_purchase_da_meta: get_cookie('evento_purchase_da_meta'),
    		dispositivo: obter_dispostivo()
        });
        self.innerText = 'Finalizar';
        if(confirmar_pedido_no_cartão.sucesso===true){// pagamento aprovado no cartão
            set_cookie('numero-do-pedido',confirmar_pedido_no_cartão.numero_do_pedido);
            document.getElementById('div_formas_de_pagamento').style.display  = 'none';      
            document.getElementById('pedido_finalizado_no_cartão').style.display = 'flex'; 
            document.getElementById('pedido_finalizado_no_cartão').scrollIntoView({behavior:'smooth',block:'center'});
        }else{// pagamento não aprovado ou indisponivel
			erro_pagamento(confirmar_pedido_no_cartão.mensagem);
        }
        return;
    }else
    if(forma_de_pagamento_escolhida==='pix'){
        let gerar_pix = await request(null,true,{
            metodo: 'gerar_pix',
            carrinho: get_cookie('carrinho'),
            endereço_de_entrega: JSON.stringify({
                destinatario: '',logradouro: '',numero: '',complemento: '',bairro: '',cidade: '',estado: '',cep: ''
            }),
            pagador: buscar_pagador(),
            descontos: get_cookie('descontos'),
            evento_purchase_da_meta: get_cookie('evento_purchase_da_meta'),
    		dispositivo: obter_dispostivo()
        });
        self.innerText = 'Finalizar';
        if(gerar_pix.sucesso===false){//PROBLEMA AO GERAR PIX
            erro_pagamento(gerar_pix.mensagem);
            return;
        }
		//GEROU O PIX NORMALMENTE
        set_cookie('numero-do-pedido',gerar_pix.numero_do_pedido);
		document.getElementById('div_formas_de_pagamento').style.display = 'none';
		document.getElementById('qr_code_pix').setAttribute('src',gerar_pix.qr_code_pix);
        document.getElementById('código_pix').value = gerar_pix.código_pix;
        document.getElementById('pedido_finalizado_no_pix').style.display = 'flex';	
        document.getElementById('pedido_finalizado_no_pix').scrollIntoView({behavior:'smooth',block:'center'});	

		// timer_do_pix(10,'timer_do_pix');
    }
    
    return;
}

window.onload = ()=>{
    montar_resumo();
	acionar_pixel_da_meta('InitiatePurchase');
	acionar_pixel_do_tiktok('InitiatePurchase');

    formas_de_pagamento_disponiveis();
	if(get_cookie('cartões_de_crédito')=='null'){
        set_cookie('cartões_de_crédito','[]');
    }else{
        let cartões_de_crédito = JSON.parse(get_cookie('cartões_de_crédito'));
        for(let cartão of cartões_de_crédito){
            montar_cartão(cartão.id,cartão.bandeira,cartão.ultimos,cartão.mes_do_cartão,cartão.ano_do_cartão,cartão.parcelas,cartão.tipo);
        }
    }

    remove_cookie('forma_de_pagamento_escolhida');

    document.getElementById('preço_final_da_passagem_pix').innerText = `R$ ${para_dinheiro(JSON.parse(calcular_preço_final()).total_no_pix,false)}`;

    setInterval(()=>{
        if(get_cookie('time_erro_pagamento')!='null'){
            if(parseInt(parseInt((new Date().getTime())/1000))-parseInt(get_cookie('time_erro_pagamento'))>=8){
                document.getElementById('div_erro_pagamento').style.display = 'none';
                remove_cookie('time_erro_pagamento');
            }
        }
    },1e3);

    adicionar_ao_carrinho();
    acionar_online();
}