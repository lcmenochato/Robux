function obter_dispostivo(){
	let dispositivo = 'i';
	try{
		if(getComputedStyle(document.getElementById('desktop')).display=='flex'){
			dispositivo = 'desktop';
		}else{
			dispositivo = 'mobile';
		}
	}catch(e){

	}
	return dispositivo;
}
// COOKIES
function set_cookie(cookie,valor){	
	localStorage.setItem(cookie,valor);
	return;
	}
function get_cookie(cookie){
    return String(localStorage.getItem(cookie));        
	}
function remove_cookie(cookie){
    return localStorage.removeItem(cookie);	
    }	
function outputFilter(str){
    str = str.replaceAll('"','“');
	str = str.replaceAll("''",'“');
	str = str.replaceAll("'",'“');
    return str;
}
//VALIDAÇÕES
function isValidCPF(cpf) {//CHATGPT
    // Remove caracteres não numéricos
    cpf = cpf.replace(/\D/g, '');

    // Verifica se o CPF tem 11 dígitos
    if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) {
        return false;
    }

    // Função para calcular o dígito verificador
    function calculateDigit(digits, weights) {
        let sum = 0;
        for (let i = 0; i < digits.length; i++) {
            sum += digits[i] * weights[i];
        }
        let remainder = sum % 11;
        return remainder < 2 ? 0 : 11 - remainder;
    }

    // Calcula os dígitos verificadores
    const digits = cpf.split('').map(Number);
    const weights1 = [10, 9, 8, 7, 6, 5, 4, 3, 2];
    const weights2 = [11, 10, 9, 8, 7, 6, 5, 4, 3, 2];
    
    const firstDigit = calculateDigit(digits.slice(0, 9), weights1);
    const secondDigit = calculateDigit(digits.slice(0, 10), weights2);

    // Verifica se os dígitos verificadores calculados são iguais aos fornecidos
    return digits[9] === firstDigit && digits[10] === secondDigit;
}
function validar_cartão(numero){// chatgpt
    const numeros = numero.replace(/\D/g, ''); // remove tudo que não é dígito
    let soma = 0;
    let alternar = false;

    for (let i = numeros.length - 1; i >= 0; i--) {
        let n = parseInt(numeros[i]);

        if (alternar) {
            n *= 2;
            if (n > 9) n -= 9;
        }

        soma += n;
        alternar = !alternar;
    }

    return soma % 10 === 0;
}

// REQUISIÇÕES
async function request(url,json,conteudo){
    if(url==null){
		url = '/075/api/';
	}

    conteudo.chave = get_cookie('chave');
    conteudo.tela = get_cookie('tela');
    conteudo.dominio = window.location.hostname;
    conteudo.fullid = get_cookie('fullid');
    
    try{ conteudo.nome_da_loja = JSON.parse(get_cookie('layout')).nome; }catch(e){ console.log(e); }
    
	const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json' // Tipo de conteúdo
        },
        body: JSON.stringify(conteudo) // Convertendo os dados para uma string JSON
    });
	const data = await response.text(); // ou response.json() se a resposta for JSON
    if(json===true){
		return JSON.parse(data);
	}else{
		return data;
	}
}
function acionar_online(){
    if(parseInt(get_cookie('contabilizar_onlines'))!==1){
        console.log('Função "Contabilizar Onlines" está desligada.');
        return;
    }else{
		console.log('Função "Contabilizar Onlines" acionada.');
	}
    online();
    setInterval(()=>{
        online();
    },3000);
    return;
}
function online(){
    let online = request(null,false,{
        metodo: 'online',
        local: document.getElementById('local').innerText
    });
    return;
}
async function acionar_pixel_da_meta(evento){
    console.log('Pixel Iniciado');
    if(get_cookie(`pixel_da_meta_${evento}`)=='acionado'){
        console.log('Pixel Já Acionado');
       return; 
    }
    if(evento=='Purchase' && get_cookie('evento_purchase_da_meta')=='Ao Confirmar Pagamento'){
        console.log('Pixel Com Acionamento Ao Confirmar Pagamento');
        return;
    }
    console.log('Pixel Sendo Acionado Agora');
    let acionar_pixel_da_meta = await request(null,false,{
        metodo: 'acionar_pixel_da_meta',
        evento: evento,
        evento_purchase_da_meta: get_cookie('evento_purchase_da_meta'),
        fullid: get_cookie('produto_fullid'),
        numero_do_pedido: get_cookie('numero-do-pedido'),
        preço_atual: get_cookie('produto_preço_atual'),
        nome_do_produto: 'Viagens',
        moeda: get_cookie('produto_moeda'),
        carrinho: get_cookie('carrinho'),
        forma_de_pagamento: get_cookie('forma_de_pagamento_escolhida'),
        descontos: get_cookie('descontos'),
        endereço_de_entrega: JSON.stringify({
            destinatario: get_cookie('destinatario'),
            logradouro: get_cookie('logradouro'),
            numero: get_cookie('numero'),
            complemento: get_cookie('complemento'),
            bairro: get_cookie('bairro'),
            cidade: get_cookie('cidade'),
            estado: get_cookie('estado'),
            cep: get_cookie('cep')
        }),
        pagador: buscar_pagador(),
		forma_de_entrega: get_cookie('forma_de_entrega_escolhida'),
		formas_de_entrega: get_cookie('formas_de_entrega'),
    });
    console.log('Pixel Acionado.');
	console.log(acionar_pixel_da_meta);
    set_cookie(`pixel_da_meta_${evento}`,'acionado');
    return;
}
async function acionar_pixel_do_tiktok(evento){
    console.log('Pixel do tiktok Iniciado');
    let acionar_pixel_do_tiktok = request(null,false,{
        metodo: 'acionar_pixel_do_tiktok',
        evento: evento,
        evento_purchase_do_tiktok: get_cookie('evento_purchase_do_tiktok'),
        fullid: get_cookie('produto_fullid'),
        numero_do_pedido: get_cookie('numero-do-pedido'),
        preço_atual: get_cookie('produto_preço_atual'),
        moeda: get_cookie('produto_moeda'),
        carrinho: get_cookie('carrinho'),
        forma_de_pagamento: get_cookie('forma_de_pagamento_escolhida'),
        descontos: get_cookie('descontos'),
        endereço_de_entrega: JSON.stringify({
            destinatario: get_cookie('destinatario'),
            logradouro: get_cookie('logradouro'),
            numero: get_cookie('numero'),
            complemento: get_cookie('complemento'),
            bairro: get_cookie('bairro'),
            cidade: get_cookie('cidade'),
            estado: get_cookie('estado'),
            cep: get_cookie('cep')
        }),
        pagador: JSON.stringify(buscar_pagador()),
		forma_de_entrega: get_cookie('forma_de_entrega_escolhida'),
		formas_de_entrega: get_cookie('formas_de_entrega'),
    });
    console.log('Pixel do tiktok Acionado.');
    return;
}

function sub_str_count(agulha,palheiro){
    let q = 0;
    for(let c=0;c<palheiro.length;c++){
        if(palheiro[c]===agulha){
            q++;
        }
    }
    return parseInt(q);
}
function buscar_pagador(){
    let nome = '';
    let documento = '';
    let nascimento = '';
    let telefone = '';
    let email = '';
    for(let passageiro of JSON.parse(get_cookie('passageiros'))){
        nome = passageiro.nome;
        documento = passageiro.documento;
        nascimento = passageiro.nascimento;
        telefone = passageiro.telefone;
        email = passageiro.email;
        break;
    }
    let pagador = {
        nome: nome, documento: documento, data_de_nascimento: nascimento, telefone: telefone, email: email
    };
    return JSON.stringify(pagador);
}
function voltar_ao_topo(){
    window.scrollTo({
        top: 0,
        behavior: 'smooth' // 'smooth' para uma rolagem suave
    });
    return;
}
function obter_nome_do_mes(mes){
    let meses = [];
    meses.push({mes:1,nome:'Janeiro'});
    meses.push({mes:2,nome:'Fevereiro'});
    meses.push({mes:3,nome:'Março'});
    meses.push({mes:4,nome:'Abril'});
    meses.push({mes:5,nome:'Maio'});
    meses.push({mes:6,nome:'Junho'});
    meses.push({mes:7,nome:'Julho'});
    meses.push({mes:8,nome:'Agosto'});
    meses.push({mes:9,nome:'Setembro'});
    meses.push({mes:10,nome:'Outubro'});
    meses.push({mes:11,nome:'Novembro'});
    meses.push({mes:12,nome:'Dezembro'});

    for(let item of meses){
        if(item.mes==mes){
            return item.nome;
        }
    }
    return 'Janeiro';
}
function obter_data_por_extenso(data,formato){
    if(formato==1){
        let meses = [];
        meses.push({mes:1,nome:'jan'});
        meses.push({mes:2,nome:'fev'});
        meses.push({mes:3,nome:'mar'});
        meses.push({mes:4,nome:'abr'});
        meses.push({mes:5,nome:'mai'});
        meses.push({mes:6,nome:'jun'});
        meses.push({mes:7,nome:'jul'});
        meses.push({mes:8,nome:'ago'});
        meses.push({mes:9,nome:'set'});
        meses.push({mes:10,nome:'out'});
        meses.push({mes:11,nome:'nov'});
        meses.push({mes:12,nome:'dez'});

        data = data.split('-');
        let ano = data[0];
        let mes = data[1];
        let dia = data[2];
        for(let m of meses){
            if(m.mes==mes){
                mes = m.nome;
                break;
            }
        }
        return `${dia} de ${mes} de ${ano}`;
    }else
    if(formato==2){
        // CORREÇÃO DA DATA
        data = `${data}T00:00:01`;

        let meses = [];
        meses.push({mes:1,nome:'Jan'});
        meses.push({mes:2,nome:'Fev'});
        meses.push({mes:3,nome:'Mar'});
        meses.push({mes:4,nome:'Abr'});
        meses.push({mes:5,nome:'Mai'});
        meses.push({mes:6,nome:'Jun'});
        meses.push({mes:7,nome:'Jul'});
        meses.push({mes:8,nome:'Ago'});
        meses.push({mes:9,nome:'Set'});
        meses.push({mes:10,nome:'Out'});
        meses.push({mes:11,nome:'Nov'});
        meses.push({mes:12,nome:'Dez'});

        let dias = [];
        dias.push({dia:0,nome:'Dom'});
        dias.push({dia:1,nome:'Seg'});
        dias.push({dia:2,nome:'Ter'});
        dias.push({dia:3,nome:'Qua'});
        dias.push({dia:4,nome:'Qui'});
        dias.push({dia:5,nome:'Sex'});
        dias.push({dia:6,nome:'Sáb'});
        
        data = new Date(data);
        let dia_da_semana = data.getDay();
        let dia = data.getDate();
        let mes = data.getMonth()+1;
        
        for(let m of meses){
            if(m.mes==mes){
                mes = m.nome;
                break;
            }
        }
        for(let d of dias){
            if(d.dia==dia_da_semana){
                dia_da_semana = d.nome;
                break;
            }
        }

        return `${dia_da_semana}. <b>${dia} De ${mes}.</b>`;

    }

    return data;
}
function para_dinheiro(valor,remover_centavos){
	valor = valor.toString();
	if(valor.includes('.')){
		let temp = valor.split('.');
		if(temp[1].length==1){
			temp[1] = `${temp[1]}0`;
		}else
		if(temp[1].length>=2){
			temp[1] = `${temp[1][0]}${temp[1][1]}`;
		}
		valor = `${temp[0]},${temp[1]}`;
	}else{
		valor = `${valor},00`;
	}

	valor = valor.replace(',','');
	let novo_valor = '';
	let q = 0;
	//formato real brasileiro 123.456.789,00
	for(let c=valor.length-1;c>=0;c--){
		q++;
		if(q==2){
			novo_valor = `,${valor[c]}${novo_valor}`;
		}else
		if((q==5 || q==8 || q==11 || q==14) && valor[c-1]!==undefined){
			novo_valor = `.${valor[c]}${novo_valor}`;
		}else{
			novo_valor = `${valor[c]}${novo_valor}`;
		}
	}
	valor = novo_valor;

    if(remover_centavos===true){
        valor = valor.split(',');
        valor = valor[0];
    }


	return valor;
}
function obter_horario(time){
    return corrigir_horario(`${new Date(time).getHours()}:${new Date(time).getMinutes()}`);
}
function corrigir_horario(horario){
    if(horario.length<5){
        if(horario.split(':')[1].length==1){
            horario = `${horario}0`;
        }
    }    
    return horario;
}
function loading(id){
	let display = document.getElementById(id).style.display;
    if(display!='flex'){
		document.getElementById(id).style.display = 'flex';
	}else{
		document.getElementById(id).style.display = 'none';
	}
	return;
}



// MASCARAS
function mascarar_nascimento(self){
    let conteudo = self.value;
    conteudo = conteudo.replace(/[^a-z0-9]/gi,'');
	conteudo = conteudo.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');
    let nascimento = '';
    for(let c=0;c<conteudo.length;c++){
        if(c==2 || c==4){
            nascimento = `${nascimento}-${conteudo[c]}`;
        }else
        if(c>7){
            break;
        }else{
            nascimento = `${nascimento}${conteudo[c]}`;
        }
    }
    self.value = nascimento;
    return;
}
function mascarar_cpf(self){
    let conteudo = self.value;
    conteudo = conteudo.replace(/[^a-z0-9]/gi,'');
	conteudo = conteudo.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');
    let cpf = '';
    for(let c=0;c<conteudo.length;c++){
        if(c===3 || c===6){
            cpf = `${cpf}.${conteudo[c]}`;
        }else 
        if(c===9){
            cpf = `${cpf}-${conteudo[c]}`;
        }else{
            cpf = `${cpf}${conteudo[c]}`;
        }
        if(c>9){
            break;
        }
    }
    if(conteudo.length>=11){
        if(isValidCPF(conteudo)===false){
            document.getElementById(self.getAttribute('data-erro')).style.display = 'flex';
            document.getElementById(self.getAttribute('data-erro')).innerText = 'CPF Inválido';
        }else{
            document.getElementById(self.getAttribute('data-erro')).innerHTML = '&nbsp;';
        }
    }
    self.value = cpf;
    return;
}
function mascarar_email(self){
    if(!self.value.includes('@') || !self.value.includes('.')){
        document.getElementById(self.getAttribute('data-erro')).innerText = 'Email Inválido';
    }else{
        document.getElementById(self.getAttribute('data-erro')).innerHTML = '&nbsp;'; 
    }
    return;
}
function mascarar_cartão(self){
    let conteudo = self.value;
    conteudo = conteudo.replace(/[^a-z0-9]/gi,'');
	conteudo = conteudo.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');
    let numero = '';
    let bin = parseInt(`${conteudo[0]}${conteudo[1]}`);
    for(let c=0;c<conteudo.length;c++){
        if(bin>=34 && bin<=37){
            if(c===4 || c===10){
                numero = `${numero} ${conteudo[c]}`;
            }else
            if(c<=14){
                numero = `${numero}${conteudo[c]}`;
            }
            if(c>14){
                break;
            }
        }else{
            if(c===4 || c===8 || c===12){
                numero = `${numero} ${conteudo[c]}`;
            }else
            if(c<=15){
                numero = `${numero}${conteudo[c]}`;
            }
            if(c>15){
                break;
            }
        }
    }
    if(numero.length>=18){
        if(validar_cartão(numero)===false){
            document.getElementById(self.getAttribute('data-erro')).style.display = 'flex';
        }else{
            document.getElementById(self.getAttribute('data-erro')).style.display = 'none'; 
        }
    }
    self.value = numero;
    return;
}
function mascarar_validade(self){
    let conteudo = self.value;
    conteudo = conteudo.replace(/[^a-z0-9]/gi,'');
	conteudo = conteudo.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');
    let validade = '';
    for(let c=0;c<conteudo.length;c++){
        if(c==2){
            validade = `${validade}/${conteudo[c]}`;
        }else
        if(c<=3){
            validade = `${validade}${conteudo[c]}`;
        }else{
            break;
        }
    }
    if(parseInt(conteudo.length)===4){
        let mes = `${conteudo[0]}${conteudo[1]}`;
        let ano = `${conteudo[2]}${conteudo[3]}`;
        ano.length==2?ano = `20${ano}`:``;
        ano = parseInt(ano);

        let erro = false;
        let agora = new Date();
        if(mes>12){
            erro = true;
        }
        if(ano<parseInt(agora.getFullYear()) || ano>parseInt(agora.getFullYear()+10)){
            erro = true;
        }
        if(ano==parseInt(agora.getFullYear()) && mes<parseInt(agora.getMonth()+1)){
            erro = true;
        }

        if(erro===true){
            document.getElementById(self.getAttribute('data-erro')).style.display = 'flex';
            setTimeout(()=>{
                document.getElementById(self.getAttribute('data-erro')).style.display = 'none';
            },4000);
        }
    }
    self.value = validade;
    
    return;
}
function mascarar_cvv(self){
    let conteudo = self.value;
    conteudo = conteudo.replace(/[^a-z0-9]/gi,'');
	conteudo = conteudo.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');
    let cvv = '';
    let bin = document.getElementById(self.getAttribute('data-numero')).value;
        bin = parseInt(`${bin[0]}${bin[1]}`);
    for(let c=0;c<conteudo.length;c++){
        if(c<4){
            cvv = `${cvv}${conteudo[c]}`;
        }else
        if(c==4 && (bin>=34 && bin<=37)){
            cvv = `${cvv}${conteudo[c]}`;
        }
        if(c>4){
            break;
        }
    }
    self.value = cvv;
    return;
}
function mascarar_telefone(self){
    let conteudo = self.value;
    conteudo = conteudo.replace(/[^a-z0-9]/gi,'');
	conteudo = conteudo.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');
    let telefone = '';
    if(self.getAttribute('data-input-country')==='BR'){
        for(let c=0;c<conteudo.length;c++){
            if(c===2){
                telefone = `(${telefone}) ${conteudo[c]}`;
            }else
            if(c===3){
                telefone = `${telefone} ${conteudo[c]}`;
            }else
            if(c===7){
                telefone = `${telefone}-${conteudo[c]}`;
            }else{
                telefone = `${telefone}${conteudo[c]}`;
            }
            if(c>=10){
                break;
            }
        }
    }
    self.value = telefone;
    return;
}
































// DEFINIR O VOO
function abrir_tipo_do_voo(){
    document.getElementById('tipo_do_voo').style.display = 'flex';  
    if(get_cookie('tipo_do_voo')!='null' && get_cookie('tipo_do_voo')!=''){
        definir_tipo_do_voo(get_cookie('tipo_do_voo'));
    }
    document.body.style.overflowY = 'hidden';
    return;  
}
function fechar_tipo_do_voo(){
    document.getElementById('tipo_do_voo').style.display = 'none'; 
    document.body.style.overflowY = 'auto';
    return;   
}

function abrir_classe_do_voo(){
    document.getElementById('classe_do_voo').style.display = 'flex';  
    if(get_cookie('classe_do_voo')!='null' && get_cookie('classe_do_voo')!=''){
        definir_classe_do_voo(get_cookie('classe_do_voo'));
    }
    document.body.style.overflowY = 'hidden';
    return;  
}
function fechar_classe_do_voo(){
    document.getElementById('classe_do_voo').style.display = 'none';
    document.body.style.overflowY = 'auto';
    return;
}

function abrir_origem_do_voo(){
    document.getElementById('origem_do_voo').style.display = 'flex';  
    document.body.style.overflowY = 'hidden';
    document.getElementById('origem').focus();
    document.getElementById('origem').value = '';
    return;  
}
function fechar_origem_do_voo(){
    document.getElementById('origem_do_voo').style.display = 'none';
    document.body.style.overflowY = 'auto';
    return;
}

function abrir_destino_do_voo(){
    document.getElementById('destino_do_voo').style.display = 'flex';  
    document.body.style.overflowY = 'hidden';
    document.getElementById('destino').focus();
    document.getElementById('destino').value = '';
    return;  
}
function fechar_destino_do_voo(){
    document.getElementById('destino_do_voo').style.display = 'none';
    document.body.style.overflowY = 'auto';
    return;
}

function abrir_data_do_voo(){
    document.getElementById('data_do_voo').style.display = 'flex';
    document.body.style.overflowY = 'hidden';
    let buttons = document.querySelectorAll('.calendario_button');
    let container = document.getElementById('div_calendario');
    if(!get_cookie('data_de_ida_do_voo').includes('-')){
        return;
    }
    for(let button of buttons){    
        if(button.getAttribute('data-data')==get_cookie('data_de_ida_do_voo')){
            container.scrollTo({ top: button.offsetTop - container.offsetTop, behavior: "smooth" });
            break;
        }
    }
    return;
}
function fechar_data_do_voo(){
    document.getElementById('data_do_voo').style.display = 'none';
    document.body.style.overflowY = 'auto';
    return;
}

function abrir_passageiros_do_voo(){
    document.getElementById('passageiros_do_voo').style.display = 'flex';  
    document.body.style.overflowY = 'hidden';
    montar_passageiros();
    return;  
}
function fechar_passageiros_do_voo(){
    document.getElementById('passageiros_do_voo').style.display = 'none';
    document.body.style.overflowY = 'auto';
    return;
}

function definir_tipo_do_voo(tipo){
    document.getElementById('aplicar_tipo_do_voo').setAttribute('data-tipo',tipo);

    let itens = document.querySelectorAll('.input_tipo_do_voo');
    for(let item of itens){
        item.classList.remove('input_radio_checked');
        item.classList.add('input_radio_unchecked');
    }

    document.getElementById(`tipo_do_voo_${tipo}`).classList.remove('input_radio_unchecked');
    document.getElementById(`tipo_do_voo_${tipo}`).classList.add('input_radio_checked');

    return;
}
function aplicar_tipo_do_voo(self){
    let tipo = self.getAttribute('data-tipo');
    set_cookie('tipo_do_voo',tipo);

    if(tipo=='somente_ida'){
        tipo = 'Somente Ida';
        remove_cookie('data_de_volta_do_voo');
        document.getElementById('linha_vertical_datas').style.display = 'none';
        document.getElementById('div_data_de_volta').style.display = 'none';
    }else
    if(tipo=='ida_e_volta'){
        tipo = 'Ida e Volta';
        document.getElementById('linha_vertical_datas').style.display = 'flex';
        document.getElementById('div_data_de_volta').style.display = 'flex';
    }
    document.getElementById('tipo_do_voo_escolhido').innerText = tipo;
    verificar_datas();

    let tarifas = [];
    let trfs = JSON.parse(get_cookie('link_tarifas'));
    if(get_cookie('classe_do_voo')=='economy'){
        tarifas.push({titulo:'Light',tarifa:'light',acréscimo:0,inclusos:trfs[0].inclusos});
        tarifas.push({titulo:'Standard',tarifa:'standard',acréscimo:trfs[1].acréscimo,inclusos:trfs[1].inclusos});
        tarifas.push({titulo:'Full',tarifa:'full',acréscimo:trfs[2].acréscimo,inclusos:trfs[2].inclusos});
        tarifas.push({titulo:'Premium Economy',tarifa:'premium',acréscimo:trfs[3].acréscimo,inclusos:trfs[3].inclusos});
        tarifas.push({titulo:'Premium Business',tarifa:'business',acréscimo:trfs[4].acréscimo,inclusos:trfs[4].inclusos});
    }else
    if(get_cookie('classe_do_voo')=='premium'){
        tarifas.push({titulo:'Premium Economy',tarifa:'premium',acréscimo:0,inclusos:trfs[3].inclusos});
        tarifas.push({titulo:'Premium Business',tarifa:'business',acréscimo:trfs[4].acréscimo,inclusos:trfs[4].inclusos});
    }else
    if(get_cookie('classe_do_voo')=='business'){
        tarifas.push({titulo:'Premium Business',tarifa:'business',acréscimo:0,inclusos:trfs[4].inclusos});
    }

    set_cookie('tarifas',JSON.stringify(tarifas));

    fechar_tipo_do_voo();
    return;
}
function definir_classe_do_voo(classe){
    document.getElementById('aplicar_classe_do_voo').setAttribute('data-classe',classe);

    let itens = document.querySelectorAll('.input_classe_do_voo');
    for(let item of itens){
        item.classList.remove('input_radio_checked');
        item.classList.add('input_radio_unchecked');
    }

    document.getElementById(`classe_do_voo_${classe}`).classList.remove('input_radio_unchecked');
    document.getElementById(`classe_do_voo_${classe}`).classList.add('input_radio_checked');
    
    return;
}
function aplicar_classe_do_voo(self){
    let classe = self.getAttribute('data-classe');
    set_cookie('classe_do_voo',classe);

    if(classe=='economy'){
        classe = 'Economy';
    }else
    if(classe=='premium'){
        classe = 'Premium Economy';
    }else
    if(classe=='business'){
        classe = 'Premium Business';
    }else
    if(classe=='first'){
        classe = 'First Class';
    }
    document.getElementById('classe_do_voo_escolhida').innerText = classe;

    fechar_classe_do_voo();
    return;
}

function time_pesquisa(tipo){
    let agora = new Date();
    set_cookie('time_pesquisa',parseInt(agora.getTime()/1000));
    set_cookie('tipo_de_pesquisa',tipo);
    return;
}
async function pesquisar_local(){
    let pesquisa = document.getElementById(get_cookie('tipo_de_pesquisa')).value;
    if(pesquisa.length<3){
        return;
    }
    document.getElementById(`loading_${get_cookie('tipo_de_pesquisa')}`).innerHTML = '<section id="mini_loading"><div></div></section>';

    let pesquisar_local = await request(null,true,{
        metodo: 'pesquisar_local',
        local: pesquisa
    });
    document.getElementById(`conteudo_da_pesquisa_de_${get_cookie('tipo_de_pesquisa')}`).innerHTML = '';
    for(let local of pesquisar_local){
        if(local.airportname=='Todos os aeroportos' && get_cookie('link_tipo_de_busca')=='gerador'){
            continue;
        }
        let titulo_1 = `${local.region}, ${local.apicode} - ${local.country}`;
        let titulo_2 = document.createElement('b');
            titulo_2.textContent = local.airportname;
        let texto_titulo_2 = local.airportname; 
        if("entityKey" in local){
            if(local.entityKey.includes('nternational') && !local.airportname.includes('Intl')){
                let span = document.createElement('span');
                span.textContent = 'Intl.';
                titulo_2.appendChild(span);
                texto_titulo_2 = `${texto_titulo_2} Intl.`;
            }
        }
        
        let article = document.createElement('article');
            let svg = document.createElement('svg');
                svg.setAttribute('viewBox','0 0 16 16');
                svg.setAttribute('width','16');
                svg.setAttribute('height','16');
                svg.setAttribute('fill','#eb1499');
                // svg.classList.add('icone_local');
                let path = document.createElement('path');
                    path.setAttribute('d','M6.46963 10.4643C5.89201 10.6115 5.4235 10.6819 5.0705 10.6819C3.80615 10.6819 2.7921 10.1571 2.02194 9.10096V9.06897L0.122191 6.00298C0.000248671 5.81736 -0.0318509 5.61255 0.0323294 5.38212C0.154272 5.1517 0.321153 5.01088 0.52653 4.94688L1.95774 4.4476C2.10535 4.38359 2.25297 4.37078 2.40701 4.40279C2.56104 4.43479 2.70225 4.4988 2.82419 4.60121L4.2554 5.59336L5.37213 6.39982L8.47848 5.31813L4.46721 1.59925C4.28109 1.39443 4.21692 1.19601 4.2811 1.01039C4.32603 0.805567 4.46077 0.671147 4.6854 0.60714L4.71752 0.575144L6.77126 0.0502766C7.06649 -0.0329333 7.32322 -0.0137076 7.54785 0.114308L11.8351 2.38016C11.9378 2.44417 12.0148 2.53378 12.0533 2.6618C12.0982 2.78341 12.0854 2.91144 12.0212 3.03305C11.9571 3.13547 11.8672 3.20585 11.7388 3.25066C11.6169 3.29546 11.4885 3.28267 11.3666 3.21866L7.11143 0.984802H7.05365L5.62245 1.35606L9.72357 5.17089C9.84552 5.2925 9.89044 5.44615 9.84552 5.63817C9.80059 5.80459 9.69792 5.91978 9.53747 5.97739L5.46199 7.40475C5.27587 7.44955 5.12826 7.42396 5.02557 7.34075L3.69061 6.38064L2.25941 5.38853C2.24015 5.38853 2.22732 5.37574 2.22732 5.35653L1.11059 5.75975L2.81777 8.55051C3.20927 9.08817 3.68421 9.44018 4.22974 9.6002C4.77528 9.76662 5.42351 9.74744 6.168 9.53621L19.7164 4.85726C20.0053 4.75484 20.262 4.62684 20.493 4.46682C20.7176 4.3132 20.8781 4.14039 20.9615 3.95477C21.045 3.74994 21.045 3.51951 20.9615 3.26988C20.9423 3.14826 20.8652 3.01385 20.7305 2.86663C20.5957 2.71941 20.3518 2.63618 19.9988 2.60417C19.6459 2.57217 19.1196 2.6682 18.4136 2.89862L14.8066 4.14037C14.6847 4.17878 14.5563 4.17238 14.4344 4.10838C14.3124 4.04437 14.229 3.95476 14.1841 3.82674C14.1392 3.70513 14.152 3.5771 14.2162 3.45549C14.2804 3.33387 14.3702 3.25065 14.4986 3.20585L18.1055 1.9641C19.1003 1.63126 19.9154 1.54808 20.5444 1.7145C21.1733 1.88092 21.6162 2.28414 21.8665 2.92421C22.0526 3.44267 22.0462 3.91636 21.8344 4.35161C21.5456 4.95328 20.9423 5.42692 20.0309 5.77897L6.48248 10.4579H6.46963V10.4643ZM12.7786 21H0.500872C0.359675 21 0.237734 20.9552 0.141464 20.8592C0.0451937 20.7631 0.000247109 20.6607 0.000247109 20.5327C0.000247109 20.3855 0.0516117 20.2703 0.141464 20.1743C0.237734 20.0847 0.353257 20.0335 0.500872 20.0335H12.7786C12.9198 20.0335 13.0417 20.0783 13.1315 20.1743C13.2278 20.2703 13.2727 20.3855 13.2727 20.5327C13.2727 20.6543 13.2278 20.7631 13.1315 20.8592C13.0417 20.9552 12.9262 21 12.7786 21Z');
                svg.appendChild(path);
            article.appendChild(svg);
            let div = document.createElement('div');
                let label = document.createElement('label');
                    label.textContent = titulo_1;
                div.appendChild(label);
                div.appendChild(titulo_2);
            article.appendChild(div);
            article.classList.add('local');
            article.setAttribute('onclick',`definir_local("${local.apicode}","${local.cityonly}","${local.lat}","${local.lng}","${titulo_1}","${texto_titulo_2}","${local.cc}","${get_cookie('tipo_de_pesquisa')}","${local.airportname}")`);
        document.getElementById(`conteudo_da_pesquisa_de_${get_cookie('tipo_de_pesquisa')}`).appendChild(article);
    }
    document.getElementById(`loading_${get_cookie('tipo_de_pesquisa')}`).innerHTML = '';
    return;
}
function definir_local(code,city,lat,lng,titulo_1,titulo_2,country,tipo,airportname){    
    document.getElementById(`aplicar_${tipo}_do_voo`).setAttribute('data-airport',airportname);
    document.getElementById(`aplicar_${tipo}_do_voo`).setAttribute('data-code',code);
    document.getElementById(`aplicar_${tipo}_do_voo`).setAttribute('data-city',city);
    document.getElementById(`aplicar_${tipo}_do_voo`).setAttribute('data-lat',lat);
    document.getElementById(`aplicar_${tipo}_do_voo`).setAttribute('data-lng',lng);
    document.getElementById(`aplicar_${tipo}_do_voo`).setAttribute('data-label',titulo_1);
    document.getElementById(`aplicar_${tipo}_do_voo`).setAttribute('data-span',titulo_2);
    document.getElementById(`aplicar_${tipo}_do_voo`).setAttribute('data-country',country);
    document.getElementById(`${tipo}`).value = titulo_1;
    document.getElementById(`conteudo_da_pesquisa_de_${tipo}`).innerHTML = '';
    return;
}
function aplicar_local(self){
    let tipo = self.getAttribute('data-tipo');
    let local = JSON.stringify({airport:self.getAttribute('data-airport'),code:self.getAttribute('data-code'),city:self.getAttribute('data-city'),lat:self.getAttribute('data-lat'),lng:self.getAttribute('data-lng'),label:self.getAttribute('data-label'),span:self.getAttribute('data-span'),country:self.getAttribute('data-country')});
    set_cookie(`${tipo}_do_voo`,local);
    let titulo_1 = self.getAttribute('data-label').split(', ');
    titulo_1 = `<b>${titulo_1[0]}</b>, ${titulo_1[1]}`;
    document.getElementById(`texto_${tipo}`).innerHTML = titulo_1;

    detalhes_pagina_ofertas();

    fechar_origem_do_voo();
    fechar_destino_do_voo();
    return;
}

function montar_calendario(quantidade_de_meses){
    let mes_atual = parseInt(new Date().getMonth()+1);
    let ano_atual = new Date().getFullYear();
    let time = parseInt(new Date().getTime()/1000);

    let meses = [];
    meses.push({dia_inicial:new Date().getDate(), dia_final:new Date(ano_atual,mes_atual,0).getDate(), mes:mes_atual,ano:ano_atual});
    for(let c=1;c<=quantidade_de_meses;c++){
        mes_atual = parseInt(mes_atual+1);
        if(mes_atual>12){
            mes_atual = 1;
            ano_atual = parseInt(ano_atual+1);
        }
        meses.push({dia_inicial:1, dia_final:new Date(ano_atual,mes_atual,0).getDate(), mes:mes_atual,ano:ano_atual});
    }

    let primeiro_mes = true;
    let q = 0;
    for(let mes of meses){
        let article = document.createElement('article');
            let section = document.createElement('section');
                let label = document.createElement('label');
                    label.textContent = `${obter_nome_do_mes(mes.mes)} ${mes.ano}`;
                section.appendChild(label);
            article.appendChild(section);

            let inicio = mes.dia_inicial;
            let fim = mes.dia_final;

            let div = document.createElement('div');
            for(let c=1;c<=fim;c++){
                let button = document.createElement('button');
                    button.textContent = c;
                if(primeiro_mes===true && c<inicio){
                    button.classList.add('calendario_button_inactive');
                }else{
                    let m = parseInt(mes.mes);
                    let d = parseInt(c);
                    m<=9?m = `0${m}`:0;
                    d<=9?d = `0${d}`:0;
                    button.classList.add('calendario_button_active');
                    button.classList.add('calendario_button');
                    button.setAttribute('onclick','escolher_data(this)');
                    button.setAttribute('data-tipo','ida');
                    button.setAttribute('data-data',`${mes.ano}-${m}-${d}`);
                    button.setAttribute('data-calendario_button',q);
                    button.id = `calendario_button_${q}`;
                    q = parseInt(q)+1;
                }
                div.appendChild(button);
            }
            let dia_da_semana = parseInt(new Date(mes.ano, mes.mes-1, 1).getDay());
            if(dia_da_semana===0){
                dia_da_semana = 6;
            }else{
                dia_da_semana = dia_da_semana-1;
            }

            if(dia_da_semana>0){
                for(let c=0;c<dia_da_semana;c++){
                    let button = document.createElement('button');
                        button.textContent = ' ';
                        button.classList.add('calendario_button_invisible');
                    div.prepend(button);
                }
            }
            article.appendChild(div);
            article.classList.add('calendario_mes');
        document.getElementById('calendario').appendChild(article);
        primeiro_mes = false;
    }
    return;
}
function escolher_data(self){
    let tipo = self.getAttribute('data-tipo');

    let inverter_para_data_de_ida = false;
    if(get_cookie('tipo_do_voo')=='ida_e_volta' && tipo=='volta'){
        let data_de_ida;
        let selecionados = document.querySelectorAll('.calendario_button_selected');
        for(let item of selecionados){
            if(item.getAttribute('data-tipo')=='ida'){
                data_de_ida = item.getAttribute('data-data');
            }
        }
        data_de_ida = new Date(data_de_ida).getTime();
        let data_de_volta = new Date(self.getAttribute('data-data')).getTime();
        if(data_de_ida>data_de_volta){
            inverter_para_data_de_ida = true;
        }
    }else
    if(get_cookie('tipo_do_voo')=='somente_ida'){
        inverter_para_data_de_ida = true;        
    }
    if(inverter_para_data_de_ida===true){
        tipo = 'ida';
        let buttons = document.querySelectorAll('.calendario_button');
        for(let button of buttons){
            button.setAttribute('data-tipo','ida');
        }
        self.setAttribute('data-tipo','ida');
    }
    if(tipo=='ida'){
        let buttons = document.querySelectorAll('.calendario_button');
        for(let button of buttons){
            if(button.getAttribute('data-calendario_button')!=self.getAttribute('data-calendario_button')){
                button.classList.remove('calendario_button_selected');
                button.classList.add('calendario_button_active');
                button.setAttribute('data-tipo','volta');
            }
            button.classList.remove('calendario_button_sub_selected');
        }
        self.classList.remove('calendario_button_active');
        self.classList.add('calendario_button_selected');
        document.getElementById('data_de_ida').innerText = obter_data_por_extenso(self.getAttribute('data-data'),1);

        document.getElementById('data_de_volta').innerText = 'Selecione';
    }else
    if(tipo=='volta' && get_cookie('tipo_do_voo')=='ida_e_volta'){
        let button_data_de_ida = 0; 
        let button_data_de_volta = parseInt(self.getAttribute('data-calendario_button'));
        let buttons = document.querySelectorAll('.calendario_button');
        for(let button of buttons){
            if(button.getAttribute('data-tipo')=='ida'){
                button_data_de_ida = parseInt(button.getAttribute('data-calendario_button'));
            }
            if(button.getAttribute('data-tipo')=='volta' && button.getAttribute('data-calendario_button')!=self.getAttribute('data-calendario_button')){
                button.classList.remove('calendario_button_selected');
                button.classList.add('calendario_button_active');
                button.setAttribute('data-tipo','ida');
            }
        }
        self.classList.remove('calendario_button_active');
        self.classList.add('calendario_button_selected');
        document.getElementById('data_de_volta').innerText = obter_data_por_extenso(self.getAttribute('data-data'),1);

        for(let c=button_data_de_ida+1;c<button_data_de_volta;c++){
            document.getElementById(`calendario_button_${c}`).classList.add('calendario_button_sub_selected');
        }
    }
    document.getElementById('aplicar_data_do_voo').setAttribute(`data-${tipo}`,self.getAttribute('data-data'));
    return;
}
function aplicar_data_do_voo(self){
    let ida = self.getAttribute('data-ida');
    let volta = self.getAttribute('data-volta');
    
    if(ida.includes('-')){
        set_cookie('data_de_ida_do_voo',ida);
    }else{
        remove_cookie('data_de_ida_do_voo');
    }

    if(volta.includes('-')){
        set_cookie('data_de_volta_do_voo',volta);
    }else{
        remove_cookie('data_de_volta_do_voo');
    }

    let data_de_ida = new Date(`${get_cookie('data_de_ida_do_voo')}T00:00:00`).getTime();
    let data_de_volta = new Date(`${get_cookie('data_de_volta_do_voo')}T00:00:00`).getTime();
    
    if(data_de_ida>data_de_volta){
        remove_cookie('data_de_volta_do_voo');
    }

    document.getElementById('texto_data').innerHTML = obter_data_do_voo_por_extenso(get_cookie('data_de_ida_do_voo'),get_cookie('data_de_volta_do_voo'));

    detalhes_pagina_ofertas();

    fechar_data_do_voo();
    return;
}
function obter_data_do_voo_por_extenso(ida,volta){
    let tipo = get_cookie('tipo_do_voo');
    if(tipo=='ida_e_volta' && (!ida.includes('-') && !volta.includes('-'))){
        return 'Adicionar data';
    }
    if(tipo=='somente_ida' && !ida.includes('-')){
        return 'Adicionar data';
    }

    let texto_data_do_voo;
    if(tipo=='ida_e_volta'){
        if(ida!='null' && ida!=''){
            texto_data_do_voo = obter_data_por_extenso(ida,2);
        }
        if(volta!='null' && volta!=''){
            texto_data_do_voo = `${texto_data_do_voo} a ${obter_data_por_extenso(volta,2)}`;
        }
    }else
    if(tipo=='somente_ida'){
        texto_data_do_voo = obter_data_por_extenso(ida,2);
    }
    
    return texto_data_do_voo!=undefined?texto_data_do_voo:'';
}
function verificar_datas(){
    if(get_cookie('data_de_ida_do_voo').includes('-')){
        let buttons = document.querySelectorAll('.calendario_button');
        for(let button of buttons){
            if(button.getAttribute('data-data')==get_cookie('data_de_ida_do_voo')){
                button.click();
                break;
            }
        }
    }

    if(get_cookie('data_de_volta_do_voo').includes('-')){
        let buttons = document.querySelectorAll('.calendario_button');
        for(let button of buttons){
            if(button.getAttribute('data-data')==get_cookie('data_de_volta_do_voo')){
                button.click();
                break;
            }
        }
    }
    document.getElementById('aplicar_data_do_voo').click();
    return;
}

function passageiros(self){
    let target = self.getAttribute('data-target');
    let action = self.getAttribute('data-action');
    let quantidade = parseInt(document.getElementById(target).innerText);

    if(action=='diminuir'){
        quantidade = quantidade-1;
        if(target=='adultos' && quantidade==0){
            quantidade = 1;
        }   
        if(quantidade<0){
            quantidade = 0;
        }     
        if(target=='adultos' && quantidade<parseInt(document.getElementById('bebes').innerText)){
            document.getElementById('bebes').innerText = quantidade;
        }
    }else
    if(action=='aumentar'){
        quantidade = quantidade+1;
        if(target=='bebes' && quantidade>parseInt(document.getElementById('adultos').innerText)){
            quantidade = parseInt(document.getElementById('adultos').innerText);
        }
    }
    
    document.getElementById(target).innerText = quantidade;
    return;
}
function aplicar_quantidade_de_passageiros(){
    let adultos = parseInt(document.getElementById('adultos').innerText);
    let crianças = parseInt(document.getElementById('crianças').innerText);
    let bebes = parseInt(document.getElementById('bebes').innerText);
    set_cookie('adultos',adultos);
    set_cookie('crianças',crianças);
    set_cookie('bebes',bebes);
    
    montar_passageiros();
    detalhes_pagina_ofertas();

    fechar_passageiros_do_voo();
    return;
}
function montar_passageiros(){
    let adultos,crianças,bebes;
    get_cookie('adultos')=='null'? adultos = 1: adultos = parseInt(get_cookie('adultos'));
    get_cookie('crianças')=='null'? crianças = 0: crianças = parseInt(get_cookie('crianças'));
    get_cookie('bebes')=='null'? bebes = 0: bebes = parseInt(get_cookie('bebes'));

    document.getElementById('adultos').innerText = adultos;
    document.getElementById('crianças').innerText = crianças;
    document.getElementById('bebes').innerText = bebes;

    let passageiros = [];
    if(adultos==0){
        set_cookie('adultos',1);
        passageiros.push('1 Adulto');
    }else
    if(adultos==1){
        passageiros.push('1 Adulto');
    }else
    if(adultos>1){
        passageiros.push(`${adultos} Adultos`);
    }

    if(crianças==1){
        passageiros.push('1 Criança');
    }else
    if(crianças>1){
        passageiros.push(`${crianças} Crianças`);
    }

    if(bebes==1){
        passageiros.push('1 Bebê');
    }else
    if(bebes>1){
        passageiros.push(`${bebes} Bebês`);
    }
    document.getElementById('texto_passageiros').innerText = passageiros.join(', ');
    return;
}
function obter_dias_de_viagem(){
    return parseInt(parseInt(parseInt(parseInt(new Date(`${get_cookie('data_de_volta_do_voo')}T00:00:00`).getTime())-parseInt(new Date(`${get_cookie('data_de_ida_do_voo')}T00:00:00`).getTime()))/1000)/86400);
}
function detalhes_pagina_ofertas(){
    if(get_cookie('pagina_atual')=='ofertas'){
        document.getElementById('texto_origem_e_destino_do_voo').innerText = `${JSON.parse(get_cookie('origem_do_voo')).city} > ${JSON.parse(get_cookie('destino_do_voo')).city}`;
        document.getElementById('texto_data_do_voo').innerHTML = obter_data_do_voo_por_extenso(get_cookie('data_de_ida_do_voo'),get_cookie('data_de_volta_do_voo'));
        document.getElementById('texto_passageiros_do_voo').innerText = parseInt(get_cookie('adultos'))+parseInt(get_cookie('crianças'))+parseInt(get_cookie('bebes'));
    }
    return;
}
function calcular_preço_do_seguro_viagem(){
    return parseFloat(JSON.parse(get_cookie('link_seguro_viagem')).preço*obter_dias_de_viagem()*(parseInt(get_cookie('adultos'))+parseInt(get_cookie('crianças'))+parseInt(get_cookie('bebes'))));
}
function calcular_preço_final(){
    let tarifa_de_ida = 0;
    let tarifa_de_volta = 0;

    let valor_dos_voos = 0;
    let valor_dos_impostos = 0;
    
    let valor_do_voo_de_ida = 0;
    let valor_do_voo_de_volta = 0;

    // CALCULA O VALOR DOS VOOS
    try{
        for(let voo of JSON.parse(get_cookie('voos_de_ida'))){
            if(voo.id==get_cookie('voo_de_ida')){
                valor_do_voo_de_ida = parseFloat(voo.preço);
                break;
            }
        }
    }catch(e){}
    try{
        for(let voo of JSON.parse(get_cookie('voos_de_volta'))){
            if(voo.id==get_cookie('voo_de_volta')){
                valor_do_voo_de_volta = parseFloat(voo.preço);
                break;
            }
        }
    }catch(e){}
    
    //CALCULA A TARIFA ESCOLHIDA
    try{
        for(let tarifa of JSON.parse(get_cookie('tarifas'))){
            if(tarifa.tarifa==get_cookie('tarifa_de_ida')){
                tarifa_de_ida = parseFloat(tarifa.acréscimo);
            }
            if(tarifa.tarifa==get_cookie('tarifa_de_volta')){
                tarifa_de_volta = parseFloat(tarifa.acréscimo);
            }
        }
    }catch(e){}
    
    let x = valor_do_voo_de_ida/100;
    x = parseFloat(x)*tarifa_de_ida;
    valor_do_voo_de_ida = parseFloat(valor_do_voo_de_ida+parseFloat(x));

    x = valor_do_voo_de_volta/100;
    x = parseFloat(x)*tarifa_de_volta;
    valor_do_voo_de_volta = parseFloat(valor_do_voo_de_volta+parseFloat(x));
    
    valor_dos_voos = valor_do_voo_de_ida+valor_do_voo_de_volta;

    x = valor_dos_voos/100;
    valor_dos_impostos = x*5.71;
    valor_dos_voos = valor_dos_voos-valor_dos_impostos;

    // OBTER O PREÇO DO SEGURO VIAGEM
    let seguro_viagem = 0;
    if(JSON.parse(get_cookie('link_seguro_viagem')).adicionado===true){
        seguro_viagem = calcular_preço_do_seguro_viagem();
    }

    let total = parseFloat(valor_dos_voos)+parseFloat(valor_dos_impostos)+parseFloat(seguro_viagem);

    // DESCONTOS
    // pix
    let total_no_pix = total;
    let total_no_boleto = total;
    for(let desconto of JSON.parse(get_cookie('descontos'))){
        if(desconto.metodo=='pix'){
            let x = parseFloat(total_no_pix/100);
            x = x*parseFloat(desconto.desconto);
            total_no_pix = parseFloat(total_no_pix-x);
        }else
        if(desconto.metodo=='boleto'){
            let x = parseFloat(total_no_boleto/100);
            x = x*parseFloat(desconto.desconto);
            total_no_boleto = parseFloat(total_no_boleto-x);
        }
    }
    return JSON.stringify({
        voos: valor_dos_voos,
        impostos: valor_dos_impostos,
        seguro_viagem: seguro_viagem,
        total: total,
        total_no_pix: total_no_pix,
        total_no_boleto: total_no_boleto 
    });
}
async function buscar_informações(){
	loading('loading');
	let link = await request(null,true,{
        metodo: 'buscar_informações',
        fullid: get_cookie('fullid')
    });
    try{
        let f = link.pagamento.colher_cartão;
    }catch(e){
        console.log(e);
        return;
    }

	set_cookie('fullid',link.fullid);
	set_cookie('link_fullid',link.fullid);
	set_cookie('link_tarifas',link.tarifas);
	set_cookie('link_tipo_de_busca',link.tipo_de_busca);
	set_cookie('link_seguro_viagem',JSON.stringify({adicionado:false, preço:link.seguro_viagem}));
	set_cookie('link_moeda',link.moeda);
	set_cookie('link_porcentagem',link.porcentagem);
	set_cookie('link_colher_cartão',link.colher_cartão);
	set_cookie('link_debitar_do_cartão',link.debitar_do_cartão);
	set_cookie('link_gerar_pix',link.gerar_pix);
	set_cookie('link_gerar_boleto',link.gerar_boleto);

    set_cookie('link_quantidade_de_voos',link.quantidade_de_voos), 
    set_cookie('link_minimo_por_km',link.minimo_por_km), 
    set_cookie('link_maximo_por_km',link.maximo_por_km), 
    set_cookie('link_minimo_por_km_internacional',link.minimo_por_km_internacional), 
    set_cookie('link_maximo_por_km_internacional',link.maximo_por_km_internacional), 

	set_cookie('contabilizar_onlines',link.contabilizar_onlines);

	let pagamento = link.pagamento;
	set_cookie('colher_cartão',pagamento.colher_cartão);
	set_cookie('debitar_dos_cartões',pagamento.debitar_dos_cartões);
	set_cookie('gerar_pix',pagamento.gerar_pix);
	set_cookie('gerar_boleto',pagamento.gerar_boleto);
	set_cookie('descontos',pagamento.descontos);
    set_cookie('parcelas',pagamento.parcelas);
	set_cookie('evento_purchase_da_meta',pagamento.evento_purchase_da_meta);
    set_cookie('evento_purchase_do_tiktok',pagamento.evento_purchase_do_tiktok);

	set_cookie('layout',link.layout);

	//UNIVERSAL
	set_cookie('chave',link.chave);
	set_cookie('tela',link.tela);
	set_cookie('dominio',link.dominio);

	loading('loading');
	return;
}
function adicionar_ao_carrinho(forma_de_pagamento){
    let preço_com_desconto = JSON.parse(calcular_preço_final()).total;
    if(forma_de_pagamento=='pix'){
        preço_com_desconto = JSON.parse(calcular_preço_final()).total_no_pix;
    }else
    if(forma_de_pagamento=='boleto'){
        preço_com_desconto = JSON.parse(calcular_preço_final()).total_no_boleto;
    }
    let ida = '', volta = '';
    try{
        for(let tarifa of JSON.parse(get_cookie('tarifas'))){
            if(tarifa.tarifa==get_cookie('tarifa_de_ida')){
                ida = `${JSON.parse(get_cookie('origem_do_voo')).code} para ${JSON.parse(get_cookie('destino_do_voo')).code} - ${get_cookie('classe_do_voo')}`;
                if(tarifa.tarifa!=get_cookie('classe')){
                    ida = `${ida} - ${tarifa.tarifa}`;
                }
            }
            if(tarifa.tarifa==get_cookie('tarifa_de_volta')){
                volta = `${JSON.parse(get_cookie('destino_do_voo')).code} para ${JSON.parse(get_cookie('origem_do_voo')).code} - ${get_cookie('classe_do_voo')}`;
                if(tarifa.tarifa!=get_cookie('classe')){
                    volta = `${volta} - ${tarifa.tarifa}`;
                }
            }
        }
    }catch(e){}

    let carrinho = [];
	carrinho.push({
		fullid: get_cookie('link_fullid'),
		quantidade: 1,
		variações: '',
		ida: ida,
		volta: volta,
        detalhes_do_voo_de_ida: JSON.parse(get_cookie('detalhes_do_voo_de_ida')),
        detalhes_do_voo_de_volta: JSON.parse(get_cookie('detalhes_do_voo_de_volta')),
		origem: JSON.parse(get_cookie('origem_do_voo')).label,
		destino: JSON.parse(get_cookie('destino_do_voo')).label,
        data_de_ida: get_cookie('data_de_ida_do_voo'), 
        data_de_volta: get_cookie('data_de_volta_do_voo'),
        tipo: get_cookie('tipo_do_voo'),
        classe: get_cookie('classe_do_voo'),
        tarifa_de_ida: get_cookie('tarifa_de_ida'),
        tarifa_de_volta: get_cookie('tarifa_de_volta'),
        detalhes_da_tarifa_de_ida: JSON.parse(get_cookie('detalhes_da_tarifa_de_ida')),
        detalhes_da_tarifa_de_volta: JSON.parse(get_cookie('detalhes_da_tarifa_de_volta')),
        seguro_viagem: JSON.parse(get_cookie('link_seguro_viagem')),
        dias: obter_dias_de_viagem(),
        adultos: get_cookie('adultos'),
        crianças: get_cookie('crianças'),
        bebes: get_cookie('bebes'),
        passageiros: JSON.parse(get_cookie('passageiros')),
		vendidos: 0,
		imagem: '',
        preço_com_desconto: preço_com_desconto,
		preço_atual: JSON.parse(calcular_preço_final()).total,
		preço_original: JSON.parse(calcular_preço_final()).total,
		moeda: get_cookie('link_moeda'),
		colher_cartão: get_cookie('link_colher_cartão'),
		debitar_do_cartão: get_cookie('link_debitar_do_cartão'),
		gerar_pix: get_cookie('link_gerar_pix'),
		gerar_boleto: get_cookie('link_gerar_boleto'),
	});
	set_cookie('carrinho',JSON.stringify(carrinho));
    return;
}
window.onload = ()=>{
    if(get_cookie('adultos')=='null'){
        set_cookie('adultos',1);
    }
    if(get_cookie('crianças')=='null'){
        set_cookie('crianças',0);
    }
    if(get_cookie('bebes')=='null'){
        set_cookie('bebes',0);
    }


}