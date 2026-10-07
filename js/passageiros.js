async function montar_passageiros(){
    let passageiros = [];
    for(let c=1;c<=parseInt(get_cookie('adultos'));c++){
        passageiros.push({titulo:`Adulto ${c}`,tipo: 'adulto', id: c});
    }
    for(let c=1;c<=parseInt(get_cookie('crianças'));c++){
        passageiros.push({titulo:`Criança ${c}`,tipo: 'criança', id: c});
    }
    for(let c=1;c<=parseInt(get_cookie('bebes'));c++){
        passageiros.push({titulo:`Bebê ${c}`,tipo: 'bebe', id: c});
    }
    document.getElementById('passageiros').innerHTML = '';
    for(let passageiro of passageiros){
        let article = document.createElement('article');
        article.classList.add('passageiro');

            let div_1 = document.createElement('div');
            div_1.classList.add('passageiro-div-1');            
                let img = document.createElement('img');
                img.src = 'https://i.imgur.com/C6el0Pj.png';
                let label = document.createElement('label');
                label.textContent = passageiro.titulo;
                label.id = `titulo_${passageiro.tipo}${passageiro.id}`;
                let i = document.createElement('i');
                i.id = `botão_detalhes_do_passageiro_${passageiro.tipo}${passageiro.id}`;
                i.classList.add('botão_detalhes_do_passageiro');
                i.classList.add('material-icons');
                i.setAttribute('onclick',`detalhes_do_passageiro(this)`);
                i.setAttribute('data-passageiro',`${passageiro.tipo}${passageiro.id}`);
                i.innerHTML = '&#xe5cf;';
            div_1.appendChild(img);
            div_1.appendChild(label);
            div_1.appendChild(i);

            let div_2 = document.createElement('div');
            div_2.classList.add('passageiro-div-2');
            div_2.setAttribute('data-id',`${passageiro.tipo}${passageiro.id}`);
            div_2.id = `detalhes_${passageiro.tipo}${passageiro.id}`;
                let div_input = document.createElement('div');
                div_input.classList.add('passageiro-div');
                    label = document.createElement('label');
                    label.textContent = 'Nome completo'; 
                    let input = document.createElement('input');
                    input.type = 'text';
                    input.placeholder = 'Maria Silva';
                    input.id = `nome_${passageiro.tipo}${passageiro.id}`;
                div_input.appendChild(label);
                div_input.appendChild(input);
                div_2.appendChild(div_input);
                    span = document.createElement('span');
                    span.innerHTML = '&nbsp;';
                    span.id = `erro_nome_${passageiro.tipo}${passageiro.id}`;
                div_2.appendChild(span);

                div_input = document.createElement('div');
                div_input.classList.add('passageiro-div');
                    label = document.createElement('label');
                    label.textContent = 'Data de nascimneto'; 
                    input = document.createElement('input');
                    input.type = 'text';
                    input.placeholder = 'dd-mm-aaaa';
                    input.id = `nascimento_${passageiro.tipo}${passageiro.id}`;
                    input.setAttribute('onkeyup',`mascarar_nascimento(this)`);
                div_input.appendChild(label);
                div_input.appendChild(input);
                div_2.appendChild(div_input);
                span = document.createElement('span');
                span.innerHTML = '&nbsp;';
                span.id = `erro_nascimento_${passageiro.tipo}${passageiro.id}`;
                div_2.appendChild(span);

                div_select = document.createElement('div');
                div_select.classList.add('passageiro-div');
                    label = document.createElement('label');
                    label.textContent = 'Sexo'; 
                    let select = document.createElement('select');
                    select.id = `sexo_${passageiro.tipo}${passageiro.id}`;
                        let option = document.createElement('option');
                        option.textContent = 'Selecione';
                        select.appendChild(option); 
                        let option1 = document.createElement('option');
                        option1.textContent = 'Masculino';
                        option1.value = 'masculino';
                        select.appendChild(option1);
                        let option2 = document.createElement('option');
                        option2.textContent = 'Feminino';
                        option2.value = 'feminino';
                        select.appendChild(option2);

                div_select.appendChild(label);
                div_select.appendChild(select);
                div_2.appendChild(div_select);
                span = document.createElement('span');
                span.innerHTML = '&nbsp;';
                span.id = `erro_sexo_${passageiro.tipo}${passageiro.id}`;
                div_2.appendChild(span);
                
                div_input = document.createElement('div');
                div_input.classList.add('passageiro-div');
                    label = document.createElement('label');
                    label.textContent = 'CPF'; 
                    input = document.createElement('input');
                    input.type = 'text';
                    input.placeholder = '123.456.789-10';
                    input.id = `documento_${passageiro.tipo}${passageiro.id}`;
                    input.setAttribute('onkeyup',`mascarar_cpf(this)`);
                    input.setAttribute('data-erro',`erro_documento_${passageiro.tipo}${passageiro.id}`);
                div_input.appendChild(label);
                div_input.appendChild(input);
                div_2.appendChild(div_input);
                span = document.createElement('span');
                span.innerHTML = '&nbsp;';
                span.id = `erro_documento_${passageiro.tipo}${passageiro.id}`;
                div_2.appendChild(span);

                if(passageiro.tipo=='adulto' && (parseInt(passageiro.id)===1 || parseInt(passageiro.id)===2)){
                    label = document.createElement('label');
                    label.textContent = 'Informação de contato';
                    div_2.appendChild(label);

                    div_input = document.createElement('div');
                    div_input.classList.add('passageiro-div');
                        label = document.createElement('label');
                        label.textContent = 'Email'; 
                        input = document.createElement('input');
                        input.type = 'text';
                        input.placeholder = 'email@email.com';
                        input.id = `email_${passageiro.tipo}${passageiro.id}`;
                        input.setAttribute('onblur',`mascarar_email(this)`);
                        input.setAttribute('data-erro',`erro_email_${passageiro.tipo}${passageiro.id}`);
                    div_input.appendChild(label);
                    div_input.appendChild(input);
                    div_2.appendChild(div_input);
                    span = document.createElement('span');
                    span.innerHTML = '&nbsp;';
                    span.id = `erro_email_${passageiro.tipo}${passageiro.id}`;
                    div_2.appendChild(span);

                    // div_select = document.createElement('div');
                    // div_select.classList.add('passageiro-div');
                        // label = document.createElement('label');
                        // label.textContent = 'Código do país'; 
                        // select = document.createElement('select');
                        // select.type = 'text';
                        // select.placeholder = '';
                        // select.id = `nome_${passageiro.tipo}${passageiro.id}`;
                    // div_select.appendChild(label);
                    // div_select.appendChild(select);
                    // div_2.appendChild(div_input);

                    div_input = document.createElement('div');
                    div_input.classList.add('passageiro-div');
                        label = document.createElement('label');
                        label.textContent = 'Telefone';                    
                        input = document.createElement('input');
                        input.type = 'text';
                        input.placeholder = '99 9 9999-9999';
                        input.id = `telefone_${passageiro.tipo}${passageiro.id}`;
                        input.setAttribute('onkeyup',`mascarar_telefone(this)`);
                        input.setAttribute('data-erro',`erro_telefone_${passageiro.tipo}${passageiro.id}`);
                        input.setAttribute('data-input-country',`BR`);
                    div_input.appendChild(label);
                    div_input.appendChild(input);
                    div_2.appendChild(div_input);
                    span = document.createElement('span');
                    span.innerHTML = '&nbsp;';
                    span.id = `erro_telefone_${passageiro.tipo}${passageiro.id}`;
                    div_2.appendChild(span);
                }

                let button = document.createElement('button');
                button.textContent = 'Confirmar dados';
                button.setAttribute('onclick',`confirmar_passageiro(this)`);
                button.setAttribute('data-passageiro',`${passageiro.tipo}`);
                button.setAttribute('data-passageiro-id',`${passageiro.id}`);
                div_2.appendChild(button);
        article.appendChild(div_1);
        article.appendChild(div_2);
        document.getElementById('passageiros').appendChild(article);
    }
    for(let passageiro of passageiros){
        if(get_cookie('passageiros')=='[]'){
            document.getElementById(`botão_detalhes_do_passageiro_${passageiro.tipo}${passageiro.id}`).click();    
        }else{
            document.getElementById('botão_detalhes_do_passageiro_adulto1').click();
        }
        break;
    }
    return;
}
function detalhes_do_passageiro(self){

    let itens = document.querySelectorAll('.botão_detalhes_do_passageiro');
    for(let item of itens){
        if(item.getAttribute('data-id')==self.getAttribute('data-passageiro')){
            continue;
        }
        item.style.transform = 'rotate(0deg)';
    }
    itens = document.querySelectorAll('.passageiro-div-2');
    for(let item of itens){
        if(item.getAttribute('data-id')==self.getAttribute('data-passageiro')){
            continue;
        }
        item.style.display = 'none';
    }

    let display = document.getElementById(`detalhes_${self.getAttribute('data-passageiro')}`).style.display;
    if(display=='flex'){
        document.getElementById(`detalhes_${self.getAttribute('data-passageiro')}`).style.display = 'none';
        self.style.transform = 'rotate(0deg)';
    }else{
        self.style.transform = 'rotate(180deg)';
        document.getElementById(`detalhes_${self.getAttribute('data-passageiro')}`).style.display = 'flex';
    }
    return;
}
function confirmar_passageiro(self){
    let tipo = self.getAttribute('data-passageiro');
    let id = parseInt(self.getAttribute('data-passageiro-id'));
    let passageiro = `${tipo}${id}`
    let nome = document.getElementById(`nome_${passageiro}`).value;
    let nascimento = document.getElementById(`nascimento_${passageiro}`).value;
    let sexo = document.getElementById(`sexo_${passageiro}`).value;
    let documento = document.getElementById(`documento_${passageiro}`).value;

    if(nome.length<3 || !nome.includes(' ')){
        document.getElementById(`nome_${passageiro}`).focus();
        document.getElementById(`erro_nascimento_${passageiro}`).innerHTML = 'Nome inválido';
        setTimeout(()=>{
            document.getElementById(`erro_nascimento_${passageiro}`).innerHTML = '&nbsp;';
        },2000);
        return;
    }
    if(nascimento.length!=10 || !nascimento.includes('-')){
        document.getElementById(`nascimento_${passageiro}`).focus();
        document.getElementById(`erro_nascimento_${passageiro}`).innerHTML = 'Data de nascimento incorreta';
        setTimeout(()=>{
            document.getElementById(`erro_nascimento_${passageiro}`).innerHTML = '&nbsp;';
        },2000);
        return;
    }
    if(sexo!='masculino' && sexo!='feminino'){
        document.getElementById(`sexo_${passageiro}`).focus();
        document.getElementById(`erro_sexo_${passageiro}`).innerHTML = 'Sexo inválido';
        setTimeout(()=>{
            document.getElementById(`erro_sexo_${passageiro}`).innerHTML = '&nbsp;';
        },2000);
        return;
    }
    if(documento.length!=14 || !documento.includes('.') || !documento.includes('-')){
        document.getElementById(`documento_${passageiro}`).focus();
        document.getElementById(`erro_documento_${passageiro}`).innerHTML = 'CPF inválido';
        setTimeout(()=>{
            document.getElementById(`erro_documento_${passageiro}`).innerHTML = '&nbsp;';
        },2000);
        return;
    }

    let email = '', telefone = '';
    if(tipo=='adulto' && (parseInt(id)===1 || parseInt(id)===2)){
        email = document.getElementById(`email_${passageiro}`).value;
        telefone = document.getElementById(`telefone_${passageiro}`).value;
        if(!email.includes('.') || !email.includes('@')){
            document.getElementById(`email_${passageiro}`).focus();
            document.getElementById(`erro_email_${passageiro}`).innerHTML = 'Email inválido';
            setTimeout(()=>{
                document.getElementById(`erro_email_${passageiro}`).innerHTML = '&nbsp;';
            },2000);
            return;
        }
        if(telefone.length<12 || telefone.length>16 || !telefone.includes(' ') || !documento.includes('-')){
            document.getElementById(`telefone_${passageiro}`).focus();
            document.getElementById(`erro_telefone_${passageiro}`).innerHTML = 'Telefone inválido';
            setTimeout(()=>{
                document.getElementById(`erro_telefone_${passageiro}`).innerHTML = '&nbsp;';
            },2000);
            return;
        }
    }

    self.innerHTML = '<section id="mini_loading"><div></div></section>';

    let passageiros = JSON.parse(get_cookie('passageiros'));
    let passageiro_atualizado = false;
    // ATUALIZA O PASSAGEIRO
    for(let p of passageiros){
        if(p.tipo==tipo && parseInt(p.id)==id){
            p.nome = nome;
            p.nascimento = nascimento;
            p.sexo = sexo;
            p.documento = documento;
            p.email = email;
            p.telefone = telefone;
            p.confirmado = true;
            passageiro_atualizado = true;
            break;
        }
    }
    if(passageiro_atualizado===false){
        if(email=='' || telefone==''){
            email = document.getElementById(`email_adulto1`).value;
            telefone = document.getElementById(`telefone_adulto1`).value;
        }
        passageiros.push({titulo: nome, tipo: tipo, id:id, nome: nome, nascimento: nascimento, sexo: sexo, documento: documento, email: email, telefone: telefone});   
    }
    set_cookie('passageiros',JSON.stringify(passageiros));

    setTimeout(()=>{
        document.getElementById(`titulo_${tipo}${id}`).innerText = nome;
        document.getElementById(`botão_detalhes_do_passageiro_${tipo}${id}`).click();

        let abrir = false;
        let rolar = false;

        let clicado = false;
        for(let c=1;c<=parseInt(get_cookie('adultos'));c++){
            let nome = document.getElementById(`nome_adulto${c}`).value;
            if(nome<3 || !nome.includes(' ')){
                document.getElementById(`botão_detalhes_do_passageiro_adulto${c}`).click();
                document.getElementById(`nome_adulto${c}`).focus();
                clicado = true;
            }   
        }
        for(let c=1;c<=parseInt(get_cookie('crianças'));c++){
            if(clicado===true){
                continue;
            }
            let nome = document.getElementById(`nome_criança${c}`).value;
            if(nome<3 || !nome.includes(' ')){
                document.getElementById(`botão_detalhes_do_passageiro_criança${c}`).click();
                document.getElementById(`nome_criança${c}`).focus();
                clicado = true;
            }   
        }
        for(let c=1;c<=parseInt(get_cookie('bebes'));c++){
            if(clicado===true){
                continue;
            }
            let nome = document.getElementById(`nome_bebe${c}`).value;
            if(nome<3 || !nome.includes(' ')){
                document.getElementById(`botão_detalhes_do_passageiro_bebe${c}`).click();
                document.getElementById(`nome_bebe${c}`).focus();
                clicado = true;
            }   
        }

        self.innerHTML = 'Confirmar dados';
        if(rolar===true){
            window.scrollTo({top: document.body.scrollHeight,behavior: 'smooth'});
        }
    },1500);
    return;
}
function adicionar_seguro_viagem(self){
    self.innerHTML = '<section id="mini_loading"><div></div></section>';
    let texto = '';
    let atributo = '';
    if(self.getAttribute('ação')=='adicionar'){
        setTimeout(()=>{
            texto = 'Remover';
            atributo = 'remover';
            self.innerHTML = texto;
            self.setAttribute('ação',atributo);
            let seguro_viagem = JSON.parse(get_cookie('link_seguro_viagem'));
            seguro_viagem.adicionado = true;
            set_cookie('link_seguro_viagem',JSON.stringify(seguro_viagem));
            calcular_resumo();
            adicionar_ao_carrinho();
        },500);
    }else
    if(self.getAttribute('ação')=='remover'){
        setTimeout(()=>{
            texto = 'Adicionar';
            atributo = 'adicionar';
            self.innerHTML = texto;
            self.setAttribute('ação',atributo);
            let seguro_viagem = JSON.parse(get_cookie('link_seguro_viagem'));
            seguro_viagem.adicionado = false;
            set_cookie('link_seguro_viagem',JSON.stringify(seguro_viagem));
            calcular_resumo();
            adicionar_ao_carrinho();
        },500);
    }
    return;
}
function calcular_resumo(){
    let preços = JSON.parse(calcular_preço_final());

    document.getElementById('preço_dos_voos').innerText = `BRL ${para_dinheiro(preços.voos,false)}`;
    document.getElementById('preço_dos_impostos').innerText = `BRL ${para_dinheiro(preços.impostos,false)}`;
    document.getElementById('preço_do_seguro_viagem-2').innerText = `BRL ${para_dinheiro(preços.seguro_viagem,false)}`;
    document.getElementById('preço_final').innerText = `BRL ${para_dinheiro(parseFloat(preços.voos)+parseFloat(preços.impostos)+parseFloat(preços.seguro_viagem),false)}`;
    if(parseFloat(preços.seguro_viagem)===0){
        document.getElementById('div_preço_seguro_viagem').style.display = 'none';
    }else{
        document.getElementById('div_preço_seguro_viagem').style.display = 'flex';
    }
    return;
}
function ir_para_pagamento(self){
    for(let passageiro of JSON.parse(get_cookie('passageiros'))){
        if(passageiro.confirmado!==true){
            window.scrollTo({top: 0,behavior: 'smooth'});
            if(document.getElementById(`detalhes_${passageiro.tipo}${passageiro.id}`).style.display!='flex'){
                document.getElementById(`botão_detalhes_do_passageiro_${passageiro.tipo}${passageiro.id}`).click();
            }
            document.getElementById(`nome_${passageiro.tipo}${passageiro.id}`).focus();
            return;
        }
    }
    adicionar_ao_carrinho();
    self.innerHTML = '<section id="mini_loading_2"><div></div></section>';
    setTimeout(()=>{
        self.innerHTML = 'Continuar';
        window.location.href = `/${get_cookie('caminho_atual')}/pagamento`;
    },1000);
}
window.onload = ()=>{
    if(get_cookie('passageiros')=='null' || get_cookie('passageiros')=='[]'){
        let passageiros = [];
        for(let c=1;c<=parseInt(get_cookie('adultos'));c++){
            passageiros.push({titulo: `Adulto ${c}`, tipo: 'adulto', id:c, nome: '', nascimento: '', sexo: '', documento: '', email: '', telefone: ''});
        }
        for(let c=1;c<=parseInt(get_cookie('crianças'));c++){
            passageiros.push({titulo: `Criança ${c}`, tipo: 'criança', id:c, nome: '', nascimento: '', sexo: '', documento: '', email: '', telefone: ''});
        }
        for(let c=1;c<=parseInt(get_cookie('bebes'));c++){
            passageiros.push({titulo: `Bebê ${c}`, tipo: 'bebe', id:c, nome: '', nascimento: '', sexo: '', documento: '', email: '', telefone: ''});
        }
        set_cookie('passageiros',JSON.stringify(passageiros));
    }
    montar_passageiros();
    for(let passageiro of JSON.parse(get_cookie('passageiros'))){
        try{
            document.getElementById(`nome_${passageiro.tipo}${passageiro.id}`).value = passageiro.nome;
            document.getElementById(`nascimento_${passageiro.tipo}${passageiro.id}`).value = passageiro.nascimento;
            document.getElementById(`sexo_${passageiro.tipo}${passageiro.id}`).value = passageiro.sexo;
            document.getElementById(`documento_${passageiro.tipo}${passageiro.id}`).value = passageiro.documento;
            if(passageiro.tipo=='adulto' && (parseInt(passageiro.id)===1 || parseInt(passageiro.id)===2)){
                document.getElementById(`email_${passageiro.tipo}${passageiro.id}`).value = passageiro.email;
                document.getElementById(`telefone_${passageiro.tipo}${passageiro.id}`).value = passageiro.telefone;
            }
            document.getElementById(`titulo_${passageiro.tipo}${passageiro.id}`).innerText = passageiro.nome!=''?passageiro.nome:`${passageiro.titulo}`;
        }catch(e){}
    }

    if(JSON.parse(get_cookie('link_seguro_viagem')).preço=='0' || JSON.parse(get_cookie('link_seguro_viagem')).preço=='0.0' || JSON.parse(get_cookie('link_seguro_viagem')).preço=='0.00' || get_cookie('tipo_do_voo')=='somente_ida'){
        document.getElementById('div_seguro_viagem').style.display = 'none';
    }else{
        let passageiros = parseInt(parseInt(get_cookie('adultos'))+parseInt(get_cookie('crianças'))+parseInt(get_cookie('bebes')));
        passageiros===1?passageiros = `1 passageiro`:`${passageiros} passageiros`;
        document.getElementById('passageiros_seguro_viagem').innerText = passageiros;
        document.getElementById('dias_seguro_viagem').innerText = obter_dias_de_viagem()===1?`1 dia`:`${obter_dias_de_viagem()} dias`;
    
        document.getElementById('preço_do_seguro_viagem').innerText = `BRL ${para_dinheiro(calcular_preço_do_seguro_viagem(),false)}`;
        if(JSON.parse(get_cookie('link_seguro_viagem')).adicionado===true){
            document.getElementById('adicionar_seguro_viagem').click();
        }
    }

    calcular_resumo();
    acionar_online();
}