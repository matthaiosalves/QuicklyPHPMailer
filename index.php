<?php
/*
Plugin Name: QuicklyPHPMailer
Description: Plugin para enviar e-mails usando PHPMailer sem depender do PHPMailer do WordPress.
Version: 1.0
Author: Matheus Alves
Author URI: https://github.com/matthaiosalves/QuicklyPHPMailer
*/

if (!defined('ABSPATH')) {
    exit;
}

function sendEmail($destinatario, $assunto, $mensagem)
{
    require_once plugin_dir_path(__FILE__) . 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once plugin_dir_path(__FILE__) . 'vendor/phpmailer/phpmailer/src/Exception.php';
    require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'HOST';
        $mail->Port       = 587;
        $mail->SMTPAuth   = true;
        $mail->Username   = 'USERNAME';
        $mail->Password   = 'PASSWORD';
        $mail->SMTPSecure = 'tls';

        $mail->setFrom('fake-email@email.com', 'Formulário - Fake Formulário');
        $mail->addAddress($destinatario);

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $assunto;
        $mail->Body    = $mensagem;

        return $mail->send();
    } catch (PHPMailer\PHPMailer\Exception $e) {
        return "Erro ao enviar o e-mail: {$mail->ErrorInfo}";
    }
}

function pluginHandleRequest()
{
    if (isset($_POST["_wpnonce"])) {
        if (!wp_verify_nonce($_POST["_wpnonce"], 'send_email_nonce')) {
            die('Erro de segurança. Por favor, recarregue a página e tente novamente.');
        }

        $nome = isset($_POST["nome"]) ? htmlspecialchars($_POST["nome"]) : '';
        $phone = isset($_POST["phone"]) ? filter_var($_POST["phone"], FILTER_SANITIZE_STRING) : '';
        $mensage = isset($_POST['mensage']) ? filter_var($_POST['mensage'], FILTER_SANITIZE_STRING) : '';

        if (empty($nome) || empty($phone) || empty($mensage)) {
            echo 'Todos os campos são obrigatórios.';
            exit;
        }

        $destinatario = 'fake-email@email.com';
        $assunto = 'Formulario';
        $mensagem = "
          <p><strong>De:</strong> Formulário: Fake Formulário</p>
          <p><strong>Nome:</strong> $nome</p>
          <p><strong>Telefone:</strong> $phone</p>
          <p><strong>Mensagem:</strong></p>
          <p>$mensage</p>
          <p>Mensagem enviada de: " . home_url() . "</p>
        ";

        $resultado = sendEmail($destinatario, $assunto, $mensagem);

        if ($resultado === true) {
            echo 'E-mail enviado com sucesso!';
        } else {
            echo $resultado;
        }
        exit;
    }
}


add_action('init', 'pluginHandleRequest');
