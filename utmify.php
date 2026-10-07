<?php
require_once __DIR__ . '/config/config.php';

try {
    $stmt = $conexao->prepare("SELECT * FROM utmify WHERE usar = 1 ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $row = null;
}

if (!$row || empty($row['token'])) {
    return;
}

$token = htmlspecialchars($row['token'], ENT_QUOTES, 'UTF-8');

echo "\n\n        <!-- UTMIFY Code Start -->\n";

echo '        <script>
         window.addEventListener("load", () => {
                let script = document.createElement(\'script\');
                script.src = \'https://cdn.utmify.com.br/scripts/utms/latest.js\';
                script.setAttribute(\'data-utmify-prevent-subids\',\'\');
                script.setAttribute(\'data-utmify-prevent-xcod-sck\',\''.$token.'\');
                script.setAttribute(\'async\',\'\');
                script.setAttribute(\'defer\',\'\');
                document.head.appendChild(script);
            });
        </script>'."\n";

echo "        <!-- UTMIFY Code End -->        ";
