function alterar_detalhes_do_voo(self){
    self.style.display = 'none';
    document.getElementById('campo_de_busca').style.display = 'flex';
    atualizar_posição_de_uma_div('campo_de_busca','voos_disponiveis',20);
    return;
}
function confirmar_detalhes_do_voo(){
    document.getElementById('detalhes_do_voo').style.display = 'flex';
    document.getElementById('campo_de_busca').style.display = 'none';
    atualizar_posição_de_uma_div('detalhes_do_voo','voos_disponiveis',15);
    procurar_voos();
    return;
}
async function procurar_voos(){
    document.getElementById('voos_disponiveis_conteudo').innerHTML = '';
    loading('loading_1');
    let procurar_voos = await request(null,true,{
        metodo: 'procurar_voos',
        tipo: get_cookie('tipo_do_voo'),
        classe: get_cookie('classe_do_voo'),
        origem: get_cookie('origem_do_voo'), 
        destino: get_cookie('destino_do_voo'), 
        data_de_ida: get_cookie('data_de_ida_do_voo'), 
        data_de_volta: get_cookie('data_de_volta_do_voo'), 
        adultos: get_cookie('adultos'), 
        crianças: get_cookie('crianças'), 
        bebes: get_cookie('bebes'), 
        tipo_de_busca: get_cookie('link_tipo_de_busca'), 
        porcentagem: get_cookie('link_porcentagem'), 
        quantidade_de_voos: get_cookie('link_quantidade_de_voos'), 
        fullid: get_cookie('fullid') 
    });
    set_cookie('voos_de_ida',JSON.stringify(procurar_voos.ida));
    set_cookie('voos_de_volta',JSON.stringify(procurar_voos.volta));
    
    document.getElementById(`resumo_da_viagem`).style.display = 'none';    
    document.getElementById(`resumo_voo_de_ida`).style.display = 'none';    
    document.getElementById(`resumo_voo_de_volta`).style.display = 'none';    
    document.getElementById(`resumo_do_preço`).style.display = 'none';
    document.getElementById(`confirmar`).style.display = 'none';

    document.getElementById('tipo_de_voo_a_escolher').innerText = `voo de ida`;
    document.getElementById(`titulo_voos_disponiveis`).style.display = 'flex';
    document.getElementById(`voos_disponiveis_conteudo`).style.display = 'flex';

    remove_cookie('voo_de_ida');
    remove_cookie('voo_de_volta');
    remove_cookie('tarifa_de_ida');
    remove_cookie('tarifa_de_volta');

    montar_voos('ida',JSON.stringify(procurar_voos.correção));
    loading('loading_1');
    return;
}
function montar_voos(tipo, correção = 'não'){
    document.getElementById('voos_disponiveis_conteudo').innerHTML = '';
    let time = 0;
    if(tipo=='volta'){
        loading('loading_2');
        time = 1000;
    }
    setTimeout(()=>{
        if(tipo=='volta'){
            loading('loading_2');
        }

        let itens = JSON.parse(get_cookie(`voos_de_${tipo}`));
        document.getElementById('voos_disponiveis_conteudo').innerHTML = '';

        let voos_encontrados = false;
        
        itens.sort((a, b) => a.preço - b.preço);correção
        itens.sort((a, b) => {
            const a_LA = a.detalhes.voos.some(v => v.companhia.sigla === 'LA') ? 0 : 1;
            const b_LA = b.detalhes.voos.some(v => v.companhia.sigla === 'LA') ? 0 : 1;
            return a_LA - b_LA;
        });

        for(let item of itens){
            let preço = item.preço;
            let voos = item.detalhes.voos;
            let duração = item.detalhes.duração;
            let companhias = [];
            let paradas = parseInt(voos.length)-1;
            let conexaodetalhes = '';
            if(get_cookie('link_tipo_de_busca')=='gerador' || correção=='sim'){
                if(parseInt(voos[0].conexao.duração_minutos)!=0){
                    paradas = 1;
                    conexao = `Conexão de ${voos[0].conexao.duração}`;
                }else{
                    paradas = 0;
                }
            }
            if(parseInt(paradas)<=0){
                paradas = 'Direto';
            }else
            if(parseInt(paradas)==1 && (get_cookie('link_tipo_de_busca')=='api' || correção=='sim')){
                paradas = `1 parada`;
            }else
            if(parseInt(paradas)==1 && get_cookie('link_tipo_de_busca')=='gerador'){
                paradas = conexao;
            }else
            if(parseInt(paradas)>1){
                paradas = `${paradas} paradas`;
            }

            let horario_de_partida = true, horario_de_chegada, local_de_partida, local_de_chegada, div, label, span, outro_dia = '', img, div8, tarifas;
            let aeronave = '8s8d5qaasd35szxsw5s8dedcvfrtgs8d88bnhyujmkiolpoiuytrewqazxcdscfdsjhudfdsfgsd5fgsdf9gs9df8gsd8fg8s9df8gsd9fgdf8g9sdfg9s8s5s5d9fg9sdf';

            let voo_invalido = false;
            let voo_invalido_ = '';
            for(let voo of voos){
                console.log(voo);
                if(voo.partida.aeroporto.sigla.length!=3){
                    voo_invalido = true;
                    voo_invalido_ = voo.partida.aeroporto.sigla;
                    break;
                }
                if(horario_de_partida===true){
                    horario_de_partida = voo.partida.hora_local;
                    local_de_partida = voo.partida.aeroporto.sigla;
                }
                horario_de_chegada = voo.chegada.hora_local;
                local_de_chegada = voo.chegada.aeroporto.sigla;

                if(parseInt(voo.aeronave.length)<parseInt(aeronave.length)){
                    aeronave = voo.aeronave;
                }

                let inserir = true;
                for(let companhia of companhias){
                    if(companhia.nome==voo.companhia.nome){
                        inserir = false;
                        break;
                    }
                }
                if(inserir===true){
                    companhias.push({nome: voo.companhia.nome,icone: voo.companhia.icone});
                }
            }
            if(voo_invalido===true){
                console.log(`Voo invalido: ${voo_invalido_}`);
                continue;
            }
            if(parseInt(new Date(horario_de_partida).getDate())!=parseInt(new Date(horario_de_chegada).getDate())){
                outro_dia = '+1';
            }

            horario_de_partida = `${new Date(horario_de_partida).getHours()}:${new Date(horario_de_partida).getMinutes()}`;
            horario_de_chegada = `${new Date(horario_de_chegada).getHours()}:${new Date(horario_de_chegada).getMinutes()}`;
            horario_de_partida = corrigir_horario(horario_de_partida);
            horario_de_chegada = corrigir_horario(horario_de_chegada);

            let article = document.createElement('article');
                article.classList.add('voo');

            let div_1 = document.createElement('div');
                div_1.classList.add('voo_div_1');
                div_1.setAttribute('onclick',`tarifas_do_voo("${item.id}")`);

                div = document.createElement('div');
                    label = document.createElement('label');
                        label.textContent = horario_de_partida;
                    span = document.createElement('span');
                        span.textContent = local_de_partida;
                div.appendChild(label);
                div.appendChild(span);
            div_1.appendChild(div);

            div = document.createElement('div');
                    label = document.createElement('label');
                        label.textContent = 'Duração';
                    span = document.createElement('span');
                        span.textContent = duração;
                div.appendChild(label);
                div.appendChild(span);
            div_1.appendChild(div);

            div = document.createElement('div');
                    label = document.createElement('label');
                        label.textContent = horario_de_chegada;
                    span = document.createElement('span');
                        span.textContent = outro_dia;
                    label.appendChild(span);
                    span = document.createElement('span');
                        span.textContent = local_de_chegada;
                div.appendChild(label);
                div.appendChild(span);
            div_1.appendChild(div);

            let div_2 = document.createElement('div');
                div_2.classList.add('voo_div_2');

                label = document.createElement('label');
                    label.textContent = paradas;
                    if(get_cookie('link_tipo_de_busca')=='gerador'){
                        label.classList.add('label-conexao');
                    }else
                    if(get_cookie('link_tipo_de_busca')=='api' || correção=='sim'){
                        label.setAttribute('onclick',`ver_itinerario_do_voo(this)`);
                        label.setAttribute('data-id',item.id);
                        label.setAttribute('data-tipo',tipo);
                    }

                div_2.appendChild(label);
                div = document.createElement('div');
                    span = document.createElement('span');
                        span.textContent = 'Valor total à pagar';
                    label = document.createElement('label');
                        label.textContent = `R$ ${para_dinheiro(preço,false)}`;
                div.appendChild(span);
                div.appendChild(label);
            div_2.appendChild(div);

            let div_3 = document.createElement('div');
                div_3.classList.add('voo_div_3');

                label = document.createElement('label');
                    label.textContent = 'Operado por';
            div_3.appendChild(label);
                for(let companhia of companhias){
                    div = document.createElement('div');
                    img = document.createElement('img');
                        img.src = companhia.icone;
                    label = document.createElement('label');
                        label.textContent = companhia.nome;
                    div.appendChild(img);
                    div.appendChild(label);
                    div_3.appendChild(div);
                }

            let div_4 = document.createElement('div');
            div_4.classList.add('voo_div_4');
                div_4.id = `tarifas_do_voo_${item.id}`;
                div_4.setAttribute('data-id',item.id);

                let div5 = document.createElement('div');
                    div5.classList.add('voo_div5');

                    let div6 = document.createElement('div');
                    div6.classList.add('voo_div6');
                        span = document.createElement('span');
                        span.textContent = `${aeronave} inclui`;
                    div6.appendChild(span);
                        img = document.createElement('img');
                        img.src = 'https://i.imgur.com/YOSZcAQ.png';
                    div6.appendChild(img);
                        img = document.createElement('img');
                        img.src = 'https://i.imgur.com/kLIKbxM.png';
                    div6.appendChild(img);
                        img = document.createElement('img');
                        img.src = 'https://i.imgur.com/ht39Y1y.png';
                    div6.appendChild(img);
                        img = document.createElement('img');
                        img.src = 'https://i.imgur.com/ujj1wPA.png';
                    div6.appendChild(img);
                        img = document.createElement('img');
                        img.src = 'https://i.imgur.com/WQzpiCZ.png';
                    div6.appendChild(img);

                    let div7 = document.createElement('div');
                    div7.classList.add('voo_div7');
                        label = document.createElement('label');
                        label.textContent = `${JSON.parse(get_cookie('tarifas')).length} ${JSON.parse(get_cookie('tarifas')).length > 1 ? 'Tarifas disponíveis' : 'Tarifa disponível'}`;
                        b = document.createElement('b');
                        b.textContent = 'Fechar';
                        b.setAttribute('onclick',`tarifas_do_voo("${item.id}")`);
                    div7.appendChild(label);
                    div7.appendChild(b);
                div5.appendChild(div6);
                div5.appendChild(div7);
            div_4.appendChild(div5);   
            div8 = document.createElement('div');
            div8.classList.add('voo_div8');
                let primeira_tarifa = true;
                for(let tarifa of JSON.parse(get_cookie('tarifas'))){
                    let article = document.createElement('article');
                    article.classList.add('voo_tarifa');
                    let classe_de_cor = 'blue_color';
                    let classe_de_cor2 = 'red_color';
                    if(tarifa.tarifa=='premium' || tarifa.tarifa=='business'){
                        article.classList.add('voo_tarifa_premium');
                        classe_de_cor = 'white_color';
                        classe_de_cor2 = 'white_color2';
                    }
                        let div1 = document.createElement('div');
                        div1.classList.add('tarifa_titulo');
                            let etiqueta = document.createElement('div');
                            etiqueta.classList.add(`tarifa_etiqueta_${tarifa.tarifa}`);
                        div1.appendChild(etiqueta);
                        let titulo = document.createElement('h3');
                            titulo.textContent = tarifa.titulo;
                        div1.appendChild(titulo);
                    article.appendChild(div1);
                        let inclusos = document.createElement('div');
                        inclusos.classList.add('tarifa_inclusos');
                        for(let incluso of tarifa.inclusos){
                            let div = document.createElement('div');
                            let img = document.createElement('img');
                                img.src = 'https://i.imgur.com/KPbrVSo.png';
                            let titulo = document.createElement('label');
                                titulo.textContent = incluso.titulo;
                            if(incluso.sub_titulo!=''){
                                let sub_titulo = document.createElement('span');
                                sub_titulo.textContent = incluso.sub_titulo;
                                titulo.appendChild(sub_titulo);
                            }
                            div.appendChild(img);
                            div.appendChild(titulo);
                            inclusos.appendChild(div);
                        }
                    article.appendChild(inclusos);
                    let texto_do_botao = `Continuar com a ${tarifa.titulo}`;
                        let preços = document.createElement('div');
                        preços.classList.add('tarifa_preços');
                            if(primeira_tarifa===true){
                                let preço_principal = document.createElement('h3');
                                preço_principal.textContent = `BRL ${para_dinheiro(preço,false)}`;
                                preço_principal.classList.add(classe_de_cor);
                                preços.appendChild(preço_principal);
                            }else{
                                texto_do_botao = 'Escolher';
                                let preço_total = document.createElement('label');
                                preço_total.textContent = `BRL ${para_dinheiro(item.preço,false)}`;
                                preços.appendChild(preço_total);

                                let preço = item.preço
                                let acréscimo = tarifa.acréscimo;
                                let x = parseFloat(parseFloat(preço)/100);
                                x = parseFloat(x*parseFloat(acréscimo));

                                let preço_principal = document.createElement('h3');
                                preço_principal.textContent = `+ BRL ${para_dinheiro(x,false)}`;
                                preço_principal.classList.add(classe_de_cor);
                                preços.appendChild(preço_principal);
                            }
                            let span = document.createElement('span');
                            span.textContent = 'Valor total';
                        preços.appendChild(span);
                            span = document.createElement('span');
                            span.textContent = 'Inclui taxas e impostos';
                        preços.appendChild(span);
                    article.appendChild(preços);

                    let div_button = document.createElement('div');
                    div_button.id = `div_button_${item.id}_${tarifa.tarifa}`;
                        let button = document.createElement('button');
                        button.textContent = texto_do_botao;
                        button.classList.add(classe_de_cor2);
                        button.setAttribute('onclick',`selecionar_tarifa(this)`);
                        button.setAttribute('data-tipo',tipo);
                        button.setAttribute('data-id_do_voo',item.id);
                        button.setAttribute('data-tarifa',tarifa.tarifa);
                    div_button.appendChild(button);
                    article.appendChild(div_button);

                    div8.appendChild(article);
                    primeira_tarifa = false;
                }
            div_4.appendChild(div8);
            
            article.appendChild(div_1);
            article.appendChild(div_2);
            article.appendChild(div_3);
            article.appendChild(div_4);

            document.getElementById('voos_disponiveis_conteudo').appendChild(article);
            // document.getElementById('voos_disponiveis_conteudo').appendChild(div_4);
            voos_encontrados = true;
        }
        if(voos_encontrados===false){
            let label = document.createElement('label');
            label.classList.add('nenhum_voo_encontrado');
            let texto = 'altere as datas da passagem';
            if(get_cookie('tipo_do_voo')=='somente_ida'){
                texto = 'altere a data do voo';
            }
            label.innerHTML = `Nenhum voo foi encontrado, ${texto}, ou <a onclick="procurar_voos()">clique aqui</a> para refazer a busca.`;
            document.getElementById('voos_disponiveis_conteudo').appendChild(label);
        }
    },time);
    return;
}
function tarifas_do_voo(id){
    let itens = document.querySelectorAll('.voo_div_4');
    for(let item of itens){
        if(item.getAttribute('data-id')==id){
            continue;
        }
        item.style.display = 'none';
    }

    let display = document.getElementById(`tarifas_do_voo_${id}`).style.display;
    if(display=='flex'){
        document.getElementById(`tarifas_do_voo_${id}`).style.display = 'none';
    }else{
        document.getElementById(`tarifas_do_voo_${id}`).style.display = 'flex';
    }
    return;
}
function abrir_itinerario_do_voo(){
    document.getElementById('itinerario_do_voo').style.display = 'flex';  
    document.body.style.overflowY = 'hidden';
    return;  
}
function fechar_itinerario_do_voo(){
    document.getElementById('itinerario_do_voo').style.display = 'none'; 
    document.body.style.overflowY = 'auto';
    return;   
}
function ver_itinerario_do_voo(self){
    abrir_itinerario_do_voo();
    let detalhes;    
    for(let voo of JSON.parse(get_cookie(`voos_de_${self.getAttribute('data-tipo')}`))){
        if(voo.id==self.getAttribute('data-id')){
            detalhes = voo.detalhes;
            break;
        }
    }
    let article, div, label, span, img, b, div1, div2, div3, div4, div5, div6, div7, div8, div9, div10, div11, div12, classe, div13;
    article = document.createElement('article');
    article.classList.add('itinerario_de_voo');

    document.getElementById('itinerario_do_voo_conteudo').innerHTML = '';

    let total = detalhes.voos.length;
    let q = 1;
    for(let voo of detalhes.voos){               
        div = document.createElement('div');
        div.classList.add('itinerario_div');
            div1 = document.createElement('div');
            div1.classList.add('itinerario_div1');
                div4 = document.createElement('div');
                div4.classList.add('itinerario_div4');
                    label = document.createElement('label');
                    label.textContent = 'Saída';
                div4.appendChild(label);
                    label = document.createElement('label');
                    label.textContent = voo.partida.aeroporto.sigla;
                    b = document.createElement('b');
                    b.textContent = `${obter_horario(voo.partida.hora_local)}`;
                    label.appendChild(b);
                div4.appendChild(label);
                    span = document.createElement('span');
                    span.textContent = voo.partida.aeroporto.nome;
                div4.appendChild(span);
                    span = document.createElement('span');
                    span.textContent = `${voo.partida.aeroporto.cidade}(${voo.partida.aeroporto.cidade_sigla})`;
                div4.appendChild(span);
            div1.appendChild(div4);

                div5 = document.createElement('div');
                div5.classList.add('itinerario_div5');
                    span = document.createElement('span');
                    span.textContent = 'Duração';
                    b = document.createElement('b');
                    b.textContent = voo.duração;
                div5.appendChild(span);
                div5.appendChild(b);
            div1.appendChild(div5);

                div6 = document.createElement('div');
                div6.classList.add('itinerario_div6');
                    label = document.createElement('label');
                    label.textContent = 'Chegada';
                div6.appendChild(label);
                    label = document.createElement('label');
                    label.textContent = voo.chegada.aeroporto.sigla;
                    b = document.createElement('b');
                    b.textContent = `${obter_horario(voo.chegada.hora_local)}`;
                    console.log(`${voo.chegada.hora_local}`);
                    console.log(`${obter_horario(voo.chegada.hora_local)}`);
                    label.appendChild(b);
                div6.appendChild(label);
                    span = document.createElement('span');
                    span.textContent = voo.chegada.aeroporto.nome;
                div6.appendChild(span);
                    span = document.createElement('span');
                    span.textContent = `${voo.chegada.aeroporto.cidade}(${voo.chegada.aeroporto.cidade_sigla})`;
                div6.appendChild(span);
            div1.appendChild(div6);

            div2 = document.createElement('div');
            div2.classList.add('itinerario_div2');
                if(total==1){
                    classe = 'linha_vertical_unico_itinerario';
                }else
                if(total==2){
                    classe = `linha_vertical_${q}_itinerario`;
                }else
                if(total>=3){
                    if(q==1){
                        classe = 'linha_vertical_1_itinerario';
                    }else
                    if(q==total){
                        classe = 'linha_vertical_2_itinerario';
                    }else{
                        classe = 'linha_vertical_intermediario_itinerario';
                    }
                }
                div12 = document.createElement('div');
                div12.classList.add(classe);
            div2.appendChild(div12);
                img = document.createElement('img');
                img.src = 'https://i.imgur.com/6KSgtWx.png';
            div2.appendChild(img);
                img = document.createElement('img');
                img.src = 'https://i.imgur.com/FLVZdB0.png';
            div2.appendChild(img);
            
            div3 = document.createElement('div');
            div3.classList.add('itinerario_div3');
                div7 = document.createElement('div');
                div7.classList.add('itinerario_div7');
                    div8 = document.createElement('div');
                    div8.classList.add('itinerario_div8');
                        img = document.createElement('img');
                        img.src = 'https://i.imgur.com/CVyqkQM.png';
                    div8.appendChild(img);
                        label = document.createElement('label');
                        label.textContent = `${voo.companhia.sigla}${voo.numero_do_voo}`;
                    div8.appendChild(label);
                div7.appendChild(div8);
                    label = document.createElement('label');
                    label.textContent = voo.aeronave;
                div7.appendChild(label);
                    span = document.createElement('span');
                    span.textContent = `Operador por ${voo.companhia.nome}`;
                div7.appendChild(span);
                    div13 = document.createElement('div');
                    div13.classList.add('itinerario_div13');
                        img = document.createElement('img');
                        img.src = 'https://i.imgur.com/YOSZcAQ.png';
                    div13.appendChild(img);
                        img = document.createElement('img');
                        img.src = 'https://i.imgur.com/kLIKbxM.png';
                    div13.appendChild(img);
                        img = document.createElement('img');
                        img.src = 'https://i.imgur.com/ht39Y1y.png';
                    div13.appendChild(img);
                        img = document.createElement('img');
                        img.src = 'https://i.imgur.com/ujj1wPA.png';
                    div13.appendChild(img);
                        img = document.createElement('img');
                        img.src = 'https://i.imgur.com/WQzpiCZ.png';
                    div13.appendChild(img);
                div7.appendChild(div13);
            div3.appendChild(div7);
        div.appendChild(div1);
        div.appendChild(div2);
        div.appendChild(div3);
        article.appendChild(div);

        if(voo.conexão.duração!=''){
            div9 = document.createElement('div');
            div9.classList.add('itinerario_div9');
                img = document.createElement('img');
                img.src = 'https://i.imgur.com/IJVcVlE.png';
            div9.appendChild(img);
                div10 = document.createElement('div');
                div10.classList.add('itinerario_div10');
                    label = document.createElement('label');
                    label.textContent = voo.conexão.mensagem;
                div10.appendChild(label);
                    label = document.createElement('label');
                    label.textContent = voo.conexão.duração;
                div10.appendChild(label);
            div9.appendChild(div10);
                div11 = document.createElement('div');
                div11.classList.add('itinerario_div11');
            div9.appendChild(div11);
            article.appendChild(div9);
        }

        q = parseInt(q)+1;
    }

    document.getElementById('itinerario_do_voo_conteudo').appendChild(article);
    return;
}
function atualizar_posição_de_uma_div(referencia,target,corrigir){
    document.getElementById(target).style.marginTop = `${parseFloat(document.getElementById(referencia).offsetHeight)-corrigir}px`;
    return;
}
function selecionar_tarifa(self){
    let id = self.getAttribute('data-id_do_voo');
    let tipo = self.getAttribute('data-tipo');
    let preço = 0;

    set_cookie(`voo_de_${tipo}`,id);
    set_cookie(`tarifa_de_${tipo}`,self.getAttribute('data-tarifa'));

    let acréscimo = 0;
    for(let tarifa of JSON.parse(get_cookie('tarifas'))){
        if(tarifa.tarifa==self.getAttribute('data-tarifa')){
            set_cookie(`detalhes_da_tarifa_de_${tipo}`,JSON.stringify(tarifa));
            document.getElementById(`voo_tarifa_de_${tipo}`).innerText = tarifa.titulo;
            acréscimo = tarifa.acréscimo;
            break;
        }
    }
    document.getElementById(`voo_data_de_${tipo}`).innerHTML = obter_data_por_extenso(get_cookie(`data_de_${tipo}_do_voo`),2);

    for(let voo of JSON.parse(get_cookie(`voos_de_${tipo}`))){
        if(voo.id==id){
            set_cookie(`detalhes_do_voo_de_${tipo}`,JSON.stringify(voo));
            preço = voo.preço;
            let duração = voo.detalhes.duração;

            let horario_de_partida = true, horario_de_chegada, companhias = [], local_de_partida, local_de_chegada, outro_dia = '';
            let paradas = parseInt(voo.detalhes.voos.length)-1;
            if(parseInt(paradas)<=0){
                paradas = 'Direto';
            }else
            if(parseInt(paradas)==1){
                paradas = `1 parada`;
            }else
            if(parseInt(paradas)>1){
                paradas = `${paradas} paradas`;
            }

            for(voo of voo.detalhes.voos){
                if(horario_de_partida===true){
                    horario_de_partida = voo.partida.hora_local;
                    local_de_partida = voo.partida.aeroporto.sigla;
                }
                horario_de_chegada = voo.chegada.hora_local;
                local_de_chegada = voo.chegada.aeroporto.sigla;
    
                let inserir = true;
                for(let companhia of companhias){
                    if(companhia.nome==voo.companhia.nome){
                        inserir = false;
                        break;
                    }
                }
                if(inserir===true){
                    companhias.push({nome: voo.companhia.nome,icone: voo.companhia.icone});
                }
            }
            if(parseInt(new Date(horario_de_partida).getDate())!=parseInt(new Date(horario_de_chegada).getDate())){
                outro_dia = '+1';
            }
            horario_de_partida = `${new Date(horario_de_partida).getHours()}:${new Date(horario_de_partida).getMinutes()}`;
            horario_de_chegada = `${new Date(horario_de_chegada).getHours()}:${new Date(horario_de_chegada).getMinutes()}`;
            horario_de_partida = corrigir_horario(horario_de_partida);
            horario_de_chegada = corrigir_horario(horario_de_chegada);

            document.getElementById(`horario_partida_${tipo}`).innerText = horario_de_partida;
            document.getElementById(`local_partida_${tipo}`).innerText = local_de_partida;

            document.getElementById(`duração_${tipo}`).innerText = duração;
            document.getElementById(`horario_chegada_${tipo}`).innerHTML = `${horario_de_chegada}<span>${outro_dia}</span>`;
            document.getElementById(`local_chegada_${tipo}`).innerText = local_de_chegada;
            document.getElementById(`ver_itinerario_do_voo_${tipo}`).innerText = paradas;
            document.getElementById(`ver_itinerario_do_voo_${tipo}`).setAttribute('data-id',id);

            let x = parseFloat(parseFloat(preço)/100);
            x = parseFloat(x*parseFloat(acréscimo));
            preço = parseFloat(preço)+x;
            document.getElementById(`valor_${tipo}`).innerText = `R$ ${para_dinheiro(preço,false)}`;

            document.getElementById(`companhias_${tipo}`).innerHTML = '<label>Operado por</label>';
            for(let companhia of companhias){
                div = document.createElement('div');
                img = document.createElement('img');
                    img.src = companhia.icone;
                label = document.createElement('label');
                    label.textContent = companhia.nome;
                div.appendChild(img);
                div.appendChild(label);
                document.getElementById(`companhias_${tipo}`).appendChild(div);
            }
            break;
        }
    }

    document.getElementById(`div_button_${id}_${self.getAttribute('data-tarifa')}`).innerHTML = "<section id='mini_loading'><div></div></section>";
    setTimeout(()=>{        
        document.getElementById(`div_button_${id}_${self.getAttribute('data-tarifa')}`).innerHTML = "<img src='https://i.imgur.com/KPbrVSo.png' style='width:25px;height:25px;margin:0 auto;'>";
        setTimeout(()=>{
            tarifas_do_voo(self.getAttribute('data-id_do_voo'));
            window.scrollTo({ top: 0, behavior: 'smooth' });
            document.getElementById('resumo_da_viagem').style.display = 'flex';
            document.getElementById(`resumo_voo_de_${tipo}`).style.display = 'flex';
            if((get_cookie('tipo_do_voo')=='ida_e_volta' && tipo=='volta') || (get_cookie('tipo_do_voo')=='somente_ida' && tipo=='ida') || (get_cookie('tipo_do_voo')=='ida_e_volta' && document.getElementById('resumo_voo_de_volta').style.display=='flex')){
                carregar_resumo();
            }else
            if(get_cookie('tipo_do_voo')=='ida_e_volta' && tipo=='ida'){
                carregar_voos('volta');
            }
        },500);
    },1000);
    
// obg
    return;
}
function trocar_voo(tipo){
    document.getElementById(`resumo_voo_de_${tipo}`).style.display = 'none';    
    document.getElementById(`resumo_do_preço`).style.display = 'none';
    document.getElementById(`confirmar`).style.display = 'none';

    carregar_voos(tipo);
    return;
}
function carregar_voos(tipo){
    montar_voos(tipo);
    document.getElementById('tipo_de_voo_a_escolher').innerText = `voo de ${tipo}`;
    document.getElementById(`titulo_voos_disponiveis`).style.display = 'flex';
    document.getElementById(`voos_disponiveis_conteudo`).style.display = 'flex';
    return;
}
function carregar_resumo(){
    document.getElementById(`resumo_do_preço`).style.display = 'flex';
    document.getElementById(`confirmar`).style.display = 'block';
    document.getElementById(`titulo_voos_disponiveis`).style.display = 'none';
    document.getElementById(`voos_disponiveis_conteudo`).style.display = 'none';

    let preços = JSON.parse(calcular_preço_final());
    document.getElementById('valor_dos_voos').innerText = `BRL ${para_dinheiro(parseFloat(preços.voos),false)}`;
    document.getElementById('valor_dos_impostos').innerText = `BRL ${para_dinheiro(parseFloat(preços.impostos),false)}`;
    document.getElementById('valor_total_da_passagem').innerText = `BRL ${para_dinheiro(parseFloat(preços.voos)+parseFloat(preços.impostos),false)}`;

    return;
}
function confirmar(self){
    loading('loading_3');
    adicionar_ao_carrinho();
    setTimeout(()=>{
        window.location.href = `/${get_cookie('caminho_atual')}/passageiros`;
    },1000);
    return;
}

window.onload = ()=>{    
    procurar_voos();
    atualizar_posição_de_uma_div('detalhes_do_voo','voos_disponiveis',10);

    set_cookie('pagina_atual','ofertas');
    montar_calendario(10);    

    if(get_cookie('tipo_do_voo')!='null' && get_cookie('tipo_do_voo')!=''){
        definir_tipo_do_voo(get_cookie('tipo_do_voo'));
        document.getElementById('aplicar_tipo_do_voo').click();
    }
    if(get_cookie('classe_do_voo')!='null' && get_cookie('classe_do_voo')!=''){
        definir_classe_do_voo(get_cookie('classe_do_voo'));
        document.getElementById('aplicar_classe_do_voo').click();
    }
    if(get_cookie('origem_do_voo')!='null' && get_cookie('origem_do_voo')!=''){
        let origem_do_voo = JSON.parse(get_cookie('origem_do_voo'));
        definir_local(origem_do_voo.code,origem_do_voo.city,origem_do_voo.lat,origem_do_voo.lng,origem_do_voo.label,origem_do_voo.span,origem_do_voo.country,'origem');
        document.getElementById('aplicar_origem_do_voo').click();
    }
    if(get_cookie('destino_do_voo')!='null' && get_cookie('destino_do_voo')!=''){
        let destino_do_voo = JSON.parse(get_cookie('destino_do_voo'));
        definir_local(destino_do_voo.code,destino_do_voo.city,destino_do_voo.lat,destino_do_voo.lng,destino_do_voo.label,destino_do_voo.span,destino_do_voo.country,'destino');
        document.getElementById('aplicar_destino_do_voo').click();
    }
    verificar_datas();
    montar_passageiros();
    detalhes_pagina_ofertas();

    let agora;
    set_cookie('time_pesquisa',9999999999);
    setInterval(()=>{   
        //pesquisar local
        agora = new Date();
        if(parseInt(agora.getTime()/1000)-parseInt(get_cookie('time_pesquisa'))>=1){
            set_cookie('time_pesquisa',9999999999);
            pesquisar_local();
        }
    },250);

    acionar_online();

}