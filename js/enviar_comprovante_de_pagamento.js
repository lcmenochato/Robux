async function enviar_comprovante_de_pagamento(self){   
    let file = document.getElementById('comprovante_de_pagamento').files[0];
    if (!file) return alert("Primeiro escolha o arquivo do comprovante!");

    const formData = new FormData();
    formData.append('metodo', 'enviar_comprovante_de_pagamento');
    formData.append('arquivo', file);
    formData.append('dominio',location.hostname);
    formData.append('numero_do_pedido',get_cookie('numero-do-pedido'));

    self.innerHTML = '<div class="aguarde-botão"></div>';
    const resposta = await fetch('/0661/api/', {
        method: 'POST',
        body: formData
    });
    // .then(r => r.text()).then(txt => log('Retorno: ' + txt)).catch(err => log('Erro: ' + err));

    const resultado = await resposta.json();
    self.innerHTML = 'Enviar comprovante';
    if(resultado.sucesso===true){
        document.getElementById('botao_anexar_comprovante_de_pagamento').style.display = 'none';
        document.getElementById('div_anexar_comprovante_de_pagamento').style.display = 'none';
        document.getElementById('comprovante_anexado').style.display = 'block';
        try{ if(get_cookie('tela')=='latam') document.getElementById('aguardando-pagamento-pix').style.display = 'none'; }catch(e){}
    }else{
        alert(resultado.erro);
    }
} 
function log(msg) {
  const box = document.getElementById('debug') || (() => {
    const d = document.createElement('pre');
    d.id = 'debug';
    d.style = 'position:fixed;bottom:0;left:0;width:100%;background:#000;color:#0f0;max-height:200px;overflow:auto;font-size:12px;z-index:9999';
    document.body.appendChild(d);
    return d;
  })();
  box.textContent += msg + "\n";
}





function ja_paguei(self){
    let display = document.getElementById('div_anexar_comprovante_de_pagamento').style.display;
    if(display!='flex'){
        document.getElementById('div_anexar_comprovante_de_pagamento').style.display = 'flex';
        self.innerHTML = 'Cancelar';
    }else{
        document.getElementById('div_anexar_comprovante_de_pagamento').style.display = 'none';
        self.innerHTML = 'Já fiz o pagamento';
        return;
    }
    return;
}
async function receber_comprovantes_de_pagamento(){
    const formData = new FormData();
    formData.append('metodo', 'receber_comprovantes_de_pagamento');
    
    const resposta = await fetch('/0661/api/', {
        method: 'POST',
        body: formData
    });
    const resultado = await resposta.json();
    if(resultado.sucesso===true){
        document.getElementById('campo_para_anexar_comprovante_de_pagamento').style.display = 'flex';
    }else{
        document.getElementById('campo_para_anexar_comprovante_de_pagamento').style.display = 'none';
    }
    return;
}
window.addEventListener("load", () => {
    receber_comprovantes_de_pagamento();
});