<?php
$https = false;

if (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
) {
    $https = true;
}

$dominio = ($https ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");
header("X-Robots-Tag: noindex, nofollow", true);
?>
<!DOCTYPE HTML>
    <html>
    <head>
    <title>A página está sendo carregada</title>
    	<meta http-equiv='content-language' content='pt-br' />
    	<meta http-equiv='content-type' content='text/html; charset=UTF-8' />
    	<meta name='keywords' content='latam ' >
    	<meta name='description' content='Página em carregamento, aguarde o carregamento total do site.' >
    	<meta name='robots' content='index,nofollow'>
    	<meta name='viewport' content='width=device-width,initial-scale=1.0, maximum-scale=1.0' />
    	<link rel='icon' type='image/x-icon' href='/favicon/favicon.ico'>

    <style>
		*{ box-sizing: border-box; }
		@media only screen and (min-width: 913px){
    		.c1{margin:0;width:100%;max-height:100vh;min-height:100vh;-webkit-text-size-adjustment:none;display:flex;flex-flow:column;background: radial-gradient(66.32% 66.32% at 54.13% 113.95%,rgba(9,172,172,.2) 0,rgba(243,30,195,0) 100%),linear-gradient(211.99deg,rgba(77,243,74,.2) -4.17%,rgba(194,84,217,0) 68.7%),radial-gradient(100% 100% at 28.65% 0,rgb(88,138,128) 0,rgba(196,53,156,0) 100%);}
    		.ccc{width:25px;height:25px;border-radius:50%;border:solid 1px #f1f1f1;background-color:#f1f1f1;transition: all 0.3s ease;}
    		.ccc1{width:90%;display:flex;justify-content:center;align-items:center;margin:0 auto;}
    		.ccc2{width:90%;height:max-content;margin:50px auto;display:flex;flex-flow:column;}
    		.ccc3{width:80%;display:flex;justify-content:center;align-items:center;text-align: center;margin: 0 auto;}
    	}

    	@media only screen and (max-width: 912px){
    		.c1{margin:0;width:100%;max-height:100vh;min-height:100vh;-webkit-text-size-adjustment:none;display:flex;flex-flow:column;background: radial-gradient(66.32% 66.32% at 54.13% 113.95%,rgba(9,172,172,.2) 0,rgba(243,30,195,0) 100%),linear-gradient(211.99deg,rgba(77,243,74,.2) -4.17%,rgba(194,84,217,0) 68.7%),radial-gradient(100% 100% at 28.65% 0,rgb(88,138,128) 0,rgba(196,53,156,0) 100%);}
    		.ccc{width:25px;height:25px;border-radius:50%;border:solid 1px #f1f1f1;background-color:#f1f1f1;transition: all 0.3s ease;}
    		.ccc1{width:90%;display:flex;justify-content:center;align-items:center;margin:0 auto;}
    		.ccc2{width:90%;height:max-content;margin:50px auto;display:flex;flex-flow:column;}
    		.ccc3{width:80%;display:flex;justify-content:center;align-items:center;text-align: center;margin: 0 auto;}
    	}

    </style>

    	<script>
    	i = 1;
    	j = 1;

    setInterval(function(){	
    	if(j==1){
    		document.getElementById('texto').innerHTML = 'A página está sendo carregada';
    		}else{
    			document.getElementById('texto').append('.');
    			}
    	j++;if(j==7){j=1;}
    },500);
    setInterval(function(){	
    	for(c=1;c<11;c++){
    		c1 = c+2;
    		if(c==i){
    			document.getElementById('a'+c).style.backgroundColor = 'rgb(133,181,70)';
    			document.getElementById('a'+c).style.border = 'solid 1px rgb(133,181,70)';
    			document.getElementById('a'+c).style.marginTop = '-25px';
    			}else{
    				document.getElementById('a'+c).style.backgroundColor = '#f1f1f1';
    				document.getElementById('a'+c).style.border = 'solid 1px #f1f1f1';
    				document.getElementById('a'+c).style.marginTop = '0';
    				}
    		}
    	i++;
    	if(i==11){
    		i = 1;
    		}
    	},150);

    </script>

    </head>
    <body class='c1'>
    	<div class='ccc2'>
    		<div class='ccc3'>
				<h1 id='texto' style='font-family:sans-serif;color:#333;'>A página está sendo carregada</h1>
			</div>
    		<p style='font-family:sans-serif;font-size:14px;color:#333;text-align:center;'>Página em carregamento</p>
    		<p style='font-family:sans-serif;font-size:14px;color:#333;text-align:center;'>Aguarde o carregamento total do site.</p>
    		<div style='height:25px;'></div>
    		<div class='ccc1'>
    			<div id='a1' class='ccc'></div>
    			<div style='width:2px;'></div>
    			<div id='a2' class='ccc'></div>
    			<div style='width:2px;'></div>
    			<div id='a3' class='ccc'></div>
    			<div style='width:2px;'></div>
    			<div id='a4' class='ccc'></div>
    			<div style='width:2px;'></div>
    			<div id='a5' class='ccc'></div>
    			<div style='width:2px;'></div>
    			<div id='a6' class='ccc'></div>
    			<div style='width:2px;'></div>
    			<div id='a7' class='ccc'></div>
    			<div style='width:2px;'></div>
    			<div id='a8' class='ccc'></div>
    			<div style='width:2px;'></div>
    			<div id='a9' class='ccc'></div>
    			<div style='width:2px;'></div>
    			<div id='a10' class='ccc'></div>
    		</div>
    		<a href='<?php echo $dominio; ?>' style='display:none;' ></a>
    	</div>
	</body>
</html>