setTimeout(function() {
    document.getElementById('sucesso').style.display = 'none';
}, 5000); 

function ocultar_mensagem_de_erro(){
	document.getElementById('div_erro').style.display = 'none';
	document.getElementById('texto_erro').innerHTML = '';
	return;
}

function ocultar_mensagem_de_sucesso(){
	document.getElementById('div_sucesso').style.display = 'none';
	document.getElementById('texto_sucesso').innerHTML = '';
	return;
}