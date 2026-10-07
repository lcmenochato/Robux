<?php
class AntiBot
{
    //private const LINK_REDIRECIONAR = 'https://www.latamairlines.com/';

    private array $bots_confiaveis = [
    'Googlebot', 'AdsBot-Google', 'Google-InspectionTool', 'Mediapartners-Google', 'Google-Read-Aloud',
    'FeedFetcher-Google', 'facebookexternalhit', 'facebot', 'MetaInspector', 'instagram',
    'whatsapp', 'WhatsApp', 'Bingbot', 'Yahoo! Slurp', 'DuckDuckBot', 'Twitterbot',
    'LinkedInBot', 'Pinterestbot', 'TikTokBot', 'ByteDance', 'AdsBot','facebookcatalog'
    ];


    private array $palavras_bloqueadas = [
        'AhrefsBot', 'SemrushBot', 'MJ12bot', 'DotBot', 'PetalBot', 'Bytespider',
        'Scrapy', 'HttpClient', 'Python-urllib', 'Java', 'curl', 'Wget', 'PhantomJS',
        'Selenium', 'HeadlessChrome', 'sqlmap', 'Nmap', 'WPScan', 'masscan', 'zgrab',
        'nikto', 'w3af', 'Xenu', 'UptimeRobot', 'monitor', 'spider', 'crawler', 'scanner',
        'Go-http-client', 'GuzzleHttp', 'libwww-perl', 'Ruby', 'Node.js', 'axios',
        'PostmanRuntime', 'Cyberfox', 'BlackWidow', 'Custo', 'DISCo', 'Download Demon',
        'eCatch', 'EirGrabber', 'EmailCollector', 'EmailSiphon', 'EmailWolf', 'Express WebPictures',
        'ExtractorPro', 'EyeNetIE', 'FlashGet', 'GetRight', 'GetWeb!', 'Go!Zilla', 'Go-Ahead-Got-It',
        'GrabNet', 'Grafula', 'HMView', 'HTTrack', 'Image Stripper', 'Image Sucker', 'Indy Library',
        'InterGET', 'Internet Ninja', 'JetCar', 'JOC Web Spider', 'larbin', 'LeechFTP', 'Mass Downloader',
        'Navroad', 'NearSite', 'NetAnts', 'NetSpider', 'Net Vampire', 'NetZIP', 'Octopus', 'Offline Explorer',
        'PageGrabber', 'Papa Foto', 'pcBrowser', 'RealDownload', 'ReGet', 'SiteSnagger', 'SmartDownload',
        'SuperBot', 'SuperHTTP', 'Surfbot', 'tAkeOut', 'Teleport Pro', 'VoidEYE', 'Web Image Collector',
        'Web Sucker', 'WebAuto', 'WebCopier', 'WebFetch', 'WebGo IS', 'WebLeacher', 'WebReaper', 'WebSauger',
        'Website eXtractor', 'Website Quester', 'WebStripper', 'WebWhacker', 'WebZIP', 'Wget', 'Widow', 'WWWOFFLE',
        'Acunetix', 'Netsparker', 'OpenVAS', 'Retina', 'Nessus', 'Qualys', 'Nexpose', 'Core Impact', 'BurpSuite',
        'ZAP', 'DirBuster', 'Gobuster', 'Fuzz', 'Wfuzz', 'Ffuf', 'Hydra', 'Medusa', 'Crawlera', 'ScrapingBee',
        'ScraperAPI', 'Oxylabs', 'SmartProxy', 'Luminati', 'BrightData', 'Apify', 'Octoparse', 'ParseHub',
        'Headless', 'Playwright', 'Puppeteer', 'curl/', 'python-requests', 'axios/', 'node-fetch',
        'HttpClient/', 'undici', 'java/', 'okhttp', 'discordbot', 'telegrambot', 'Slackbot', 'skypeuripreview',
        'DataForSeo', 'SerpApi', 'Barkrowler', 'SeznamBot', 'YandexBot', 'YandexImages', 'Zoominfo', 'ZoomBot', 'AhrefsSiteAudit'
    ];

    private array $padroes_uri_suspeitos = [
        '/\.env/', '/wp-config/', '/\.git/', '/\.sql/', '/phpinfo/', '/xmlrpc\.php/',
        '/admin/', '/setup\.php/', '/config\.php/', '/\.htaccess/', '/\.bash_history/',
        '/composer\.json/', '/package\.json/', '/\.vscode/', '/backup/', '/dump/',
        '/\.remote/', '/\.local/', '/\.production/', '/\.staging/', '/\.dev/'
    ];

    private array $headers_obrigatorios = [
        'HTTP_USER_AGENT',
        'HTTP_ACCEPT',
        'HTTP_ACCEPT_LANGUAGE'
    ];

    private $conexao;
    private string $conexao_tipo;

    public function __construct($conexao, string $conexao_tipo)
    {
        $this->conexao = $conexao;
        $this->conexao_tipo = $conexao_tipo;
    }

    public function executar(): void
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ip = $this->getRealIp();

        if (empty(trim($user_agent))) {
            $this->bloquearAcesso($ip);
        }

        if (!$this->verificarHeaders()) {
            $this->bloquearAcesso($ip);
        }

        if ($this->isBotConfiavel($user_agent)) {
            return;
        }

        if ($this->isUserAgentSuspeito($user_agent)) {
            $this->bloquearAcesso($ip);
        }

        if ($this->isUriSuspeita()) {
            $this->bloquearAcesso($ip);
        }
        
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) || strlen($_SERVER['HTTP_ACCEPT_LANGUAGE']) < 2) {
             $this->bloquearAcesso($ip);
        }
    }

    private function getRealIp(): string
    {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    private function verificarHeaders(): bool
    {
        foreach ($this->headers_obrigatorios as $header) {
            if (!isset($_SERVER[$header]) || empty($_SERVER[$header])) {
                return false;
            }
        }
        return true;
    }

    private function isBotConfiavel(string $user_agent): bool
    {
        $pattern = '/(' . implode('|', array_map('preg_quote', $this->bots_confiaveis)) . ')/i';
        return (bool) preg_match($pattern, $user_agent);
    }

    private function isUserAgentSuspeito(string $user_agent): bool
    {
        //$pattern_bloqueio = '/(' . implode('|', array_map('preg_quote', $this->palavras_bloqueadas)) . ')/i';
        $pattern_bloqueio = '/(' . implode('|', array_map( fn($palavra) => preg_quote($palavra, '/'), $this->palavras_bloqueadas )) . ')/i';
        return (bool) preg_match($pattern_bloqueio, $user_agent);
    }



    private function isUriSuspeita(): bool
    {
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        foreach ($this->padroes_uri_suspeitos as $padrao) {
            if (preg_match($padrao, $request_uri)) {
                return true;
            }
        }
        return false;
    }

    private function bloquearAcesso(string $ip): void
    {
        try {
            if ($this->conexao) {
                if ($this->conexao_tipo === 'pdo' && $this->conexao instanceof PDO) {
                    $this->registrarIpComPdo($ip);
                } elseif ($this->conexao_tipo === 'mysqli' && $this->conexao instanceof mysqli) {
                    $this->registrarIpComMysqli($ip);
                }
            }
        } catch (Exception $e) {
        } finally {
            http_response_code(404);
            include $_SERVER['DOCUMENT_ROOT'] . '/erro.php';
            exit;
        }
    }

    private function registrarIpComPdo(string $ip): void
    {
        $sql = "INSERT INTO ipsblock (ip, bloqueados, ultima_ocorrencia) 
                VALUES (:ip, 1, NOW()) 
                ON DUPLICATE KEY UPDATE bloqueados = bloqueados + 1, ultima_ocorrencia = NOW()";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute([':ip' => $ip]);
    }

    private function registrarIpComMysqli(string $ip): void
    {
        $sql = "INSERT INTO ipsblock (ip, bloqueados, ultima_ocorrencia) 
                VALUES (?, 1, NOW()) 
                ON DUPLICATE KEY UPDATE bloqueados = bloqueados + 1, ultima_ocorrencia = NOW()";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bind_param("s", $ip);
        $stmt->execute();
    }
}

require_once(__DIR__ . '/../config/config.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($conexao)) {
    $antiBot = new AntiBot($conexao, $conexao_tipo);
    $antiBot->executar();
}
?>
