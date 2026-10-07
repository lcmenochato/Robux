<?php
   session_start();
   ini_set('display_errors', 1);
   ini_set('display_startup_errors', 1);
   error_reporting(E_ALL);
   require_once 'config.php';
   
   if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
       header("Location: ./");
       exit;
   }
   ?>
<!doctype html>
<html lang="pt-br" data-theme="dark">
   <?php include('head.php'); ?>
   <body data-theme="light">
      <div id="body" class="theme-cyan">
      <?php include('theme.php'); ?>
      <div class="overlay"></div>
      <div id="wrapper">
      <?php include('submenu.php'); ?>
      <?php include('menu.php'); ?>
      <div id="main-content">
      <div class="container-fluid">
      <div class="block-header">
         <div class="row clearfix">
            <div class="col-lg-4 col-md-12 col-sm-12">
               <h1>Olá  admin, você está em  / Pedidos</h1>
               <span>TXT_JPGI1 - @TXT_JPGI1 <== no telegram!  </span>
            </div>
         </div>
      </div>

<!--  
      
<section id='pedidos' class='campo'>
				<h3>Pedidos</h3>
				<div class='tipo_de_pedido'>
					<label>Tipo</label>
					<select id='filtro_pedidos' onchange='buscar_pedidos("")'>
						<option value='todos'>Todos</option>
						<option value='pix'>Pix</option>
						<option value='cartão'>Cartão</option>
						<option value='boleto'>Boleto</option>
					</select>
				</div>
				<div class='botões'>
					<button onclick='checkbox(this)' data-action='selecionar' data-target='checkbox-pedido'>Marcar todos</button>
					<span>&nbsp;&nbsp;</span>
					<button onclick='remover_selecionados(this)' data-target='checkbox-pedido' data-tabela='pedidos' data-function='buscar_pedidos' data-text='Os pedidos selecionados serão removidos.'>Remover marcados</button>
					<button onclick='lembrete_de_pagamento("checkbox-pedido",this)' data-alvo='todos'>Lembrete de pagamento</button>
				</div>
				<div class='div_contadores'>
					<h1>Total<span id='pedidos_total'>0</span></h1>
					<h1>Únicos<span id='pedidos_unicos'>0</span></h1>
					<h1>Repetidos<span id='pedidos_repetidos'>0</span></h1>
					<h1>Valor total<span id='pedidos_valor_total'>R$ 0,00</span></h1>
					<h1>Valor pendente<span id='pedidos_valor_pendente'>R$ 0,00</span></h1>
					<h1>Valor pago<span id='pedidos_valor_pago'>R$ 0,00</span></h1>
				</div>
				<div id='pedidos_conteudo'></div>
			</section>
-->