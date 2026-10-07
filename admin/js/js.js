function mostrar_erro(mensagem){
	document.getElementById('erro').style.display = 'flex';
	document.getElementById('texto_erro').innerHTML = mensagem;
	setTimeout(function(){
		if(document.getElementById('texto_erro').innerText==mensagem){
			ocultar_mensagem_de_erro();
		}
	},5e3);
	return;
}
function ocultar_mensagem_de_erro(){
	document.getElementById('div_erro').style.display = 'none';
	document.getElementById('texto_erro').innerHTML = '';
	return;
}

