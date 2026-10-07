<?php
session_start();
require_once 'config.php';

// fazer ele ter 5 sessões para liberar o "admin"

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: ?COMON=/ADMIN");
    exit;
}

$error = '';
$sucesso = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['username'], $_POST['password'])) {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {

        $sql = "SELECT id, username, password FROM users WHERE username = ? LIMIT 1";
        $stmt = $conn->prepare($sql);

        if ($stmt) {

            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {

                $user = $result->fetch_assoc();

                if (password_verify($password, $user['password'])) {

                    session_regenerate_id(true);

                    $_SESSION['loggedin'] = true;
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];

                    $sucesso = "Login realizado com sucesso!";

                    echo "<script>
                            setTimeout(function(){
                            localStorage.setItem('theme', 'dark');
                                window.location.href='?COMON=/ADMIN';
                            },2500);
                          </script>";

                } else {
                    $error = "Senha inválida!";
                }

            } else {
                $error = "Usuário ou senha inválidos!";
            }

            $stmt->close();

        } else {
            $error = "Erro interno.";
        }

    } else {
        $error = "Preencha todos os campos.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
		<title>Acessar Painel</title>
		<!--JS-->
		<script type="text/javascript" src="js/js.js"></script>
		<script type="text/javascript" src="js/acesso.js"></script>
		<!--CSS-->
		<!--<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">-->
		<link id='tema_do_painel' rel="stylesheet" type='text/css' href='css/tema_escuro.css' />
		<link id='css_tema' rel="stylesheet" type='text/css' href="css/css.css" />
		<link rel="stylesheet" type='text/css' href="css/acesso.css" />
		<!--META-->
		<meta charset='utf-8'>
		<meta name="viewport" content="width=device-width,initial-scale=1.0, maximum-scale=1.0">
		<meta name="format-detection" content="telephone=no">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
	</head>
    <body>
        <div id='div_sucesso' style="display:<?php echo !empty($sucesso) ? 'flex' : 'none'; ?>;">
            <span id='texto_sucesso'><?php echo $sucesso; ?></span>
            <span onclick='ocultar_mensagem_de_sucesso();'>x</span>
        </div>

        <div id='div_erro' style="display:<?php echo !empty($error) ? 'flex' : 'none'; ?>;">
            <span id='texto_erro'><?php echo $error; ?></span>
            <span onclick='ocultar_mensagem_de_erro();'>x</span>
        </div>

        <main>
        <?php $query = "SELECT * FROM users LIMIT 1"; $result = $conn->query($query); if ($result && $result->num_rows > 0) { $dados = $result->fetch_assoc(); ?>
        <form action="" method="POST" id="campo_acessar">
            <h3>Acessar</h3>
            <label>Usuário</label>
            <input type="text" value="<?php echo htmlspecialchars($dados['username']); ?>" disabled>
            <input type="hidden" name="username" value="<?php echo htmlspecialchars($dados['username']); ?>">
            <br><br><label>Digite sua senha</label>
            <input name="password" type="password" required>
            <button type="submit">Acessar</button>
        </form>
            <?php } else { } ?>
        </main>
    </body>
</html>
