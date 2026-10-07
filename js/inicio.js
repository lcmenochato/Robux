function procurar_voos(self){
    //VERIFICAÇÕES
    if(get_cookie('tipo_do_voo')=='ida_e_volta' && (get_cookie('data_de_ida_do_voo')=='null' || get_cookie('data_de_volta_do_voo')=='null')){
        alert('Selecione a data de volta do seu voo');
        abrir_data_do_voo();
        return;
    }
    adicionar_ao_carrinho();
    window.location.href = `/${get_cookie('caminho_atual')}/ofertas`;
    return;
}
async function destinos_pre_definidos(){
    let destinos_pre_definidos = await request(null,true,{
        metodo: 'destinos_pre_definidos'
    });
    if(destinos_pre_definidos.sucesso===true){
        document.getElementById('destinos_pre_definidos').innerHTML = '';
        for(let destino of destinos_pre_definidos.resultado){
            let div = document.createElement('div');
            div.classList.add('destino-pre-definido');
                let article = document.createElement('article');
                    let img = document.createElement('img');
                    img.src = destino.imagem;
                article.appendChild(img);
            div.appendChild(article);
                let div2 = document.createElement('div');
                    let label = document.createElement('label');
                    label.textContent = destino.titulo;
                div2.appendChild(label);
                    let span = document.createElement('span');
                    span.textContent = 'Ida e Volta';
                div2.appendChild(span);
                    let div3 = document.createElement('div');
                        span = document.createElement('span');
                        span.textContent = 'Economy';
                    div3.appendChild(span);
                        span = document.createElement('span');
                        span.textContent = 'Acumule';
                        let b = document.createElement('b');
                        b.textContent = 'pontos';
                        span.appendChild(b);
                    div3.appendChild(span); 
                div2.appendChild(div3);
                    span = document.createElement('span');
                    span.textContent = 'Até 20% de desconto';
                div2.appendChild(span);
            div.appendChild(div2);
            document.getElementById('destinos_pre_definidos').appendChild(div);
        }
    }
    return;
}

window.onload = async ()=>{
    set_cookie('pagina_atual','inicio');

    await buscar_informações();

    let pathname = window.location.pathname; // ex: "/pasta/pagina.html"
	pathname = pathname.split('/');
	set_cookie('caminho_atual',pathname[1]);
        
    if(get_cookie('adultos')=='null' || get_cookie('adultos')=='undefined'){
        set_cookie('adultos','1');
        set_cookie('crianças','0');
        set_cookie('bebes','0');
    }

    if(get_cookie('classe_do_voo')!='economy' && get_cookie('classe_do_voo')!='premium' && get_cookie('classe_do_voo')!='business'){
        set_cookie('classe_do_voo','economy');
    }
    definir_classe_do_voo(get_cookie('classe_do_voo'));
    document.getElementById('aplicar_classe_do_voo').click();
    
    if(get_cookie('tipo_do_voo')!='ida_e_volta' && get_cookie('tipo_do_voo')!='somente_ida'){
        set_cookie('tipo_do_voo','ida_e_volta');
    }
    definir_tipo_do_voo(get_cookie('tipo_do_voo'));
    document.getElementById('aplicar_tipo_do_voo').click();

    if(get_cookie('origem_do_voo')!='null' && get_cookie('origem_do_voo')!='' && get_cookie('origem_do_voo')!='undefined'){
        let origem_do_voo = JSON.parse(get_cookie('origem_do_voo'));
        definir_local(origem_do_voo.code,origem_do_voo.city,origem_do_voo.lat,origem_do_voo.lng,origem_do_voo.label,origem_do_voo.span,origem_do_voo.country,'origem');
        document.getElementById('aplicar_origem_do_voo').click();
    }
    if(get_cookie('destino_do_voo')!='null' && get_cookie('destino_do_voo')!='' && get_cookie('destino_do_voo')!='undefined'){
        let destino_do_voo = JSON.parse(get_cookie('destino_do_voo'));
        definir_local(destino_do_voo.code,destino_do_voo.city,destino_do_voo.lat,destino_do_voo.lng,destino_do_voo.label,destino_do_voo.span,destino_do_voo.country,'destino');
        document.getElementById('aplicar_destino_do_voo').click();
    }

    montar_calendario(10);
    verificar_datas();
    montar_passageiros();

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

    setTimeout(()=>{
        if(document.getElementById('texto_data').innerText==''){
            document.getElementById('texto_data').innerText = 'Adicionar data';
        }
    },500);

    destinos_pre_definidos();

    acionar_online();
}