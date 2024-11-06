<?php
/*
Plugin Name: QuicklyPHPMailer
Description: Plugin para enviar e-mails usando PHPMailer sem depender do PHPMailer do WordPress.
Version: 1.5
Author: Matheus Alves
Author URI: https://github.com/matthaiosalves/QuicklyPHPMailer
*/

if (!defined('ABSPATH')) {
    exit;
}

function enfileirarEmail($destinatario, $assunto, $mensagem)
{
    $existing_email = new WP_Query([
        'post_type' => 'email_queue',
        'meta_query' => [
            'relation' => 'AND',
            [
                'key' => 'email_destinatario',
                'value' => $destinatario,
                'compare' => '='
            ],
            [
                'key' => 'assunto',
                'value' => $assunto,
                'compare' => '='
            ]
        ]
    ]);

    if ($existing_email->have_posts()) {
        return;
    }

    wp_insert_post([
        'post_type' => 'email_queue',
        'post_title' => "E-mail para {$destinatario}",
        'post_content' => $mensagem,
        'post_status' => 'publish',
        'meta_input' => [
            'email_destinatario' => $destinatario,
            'assunto' => $assunto,
            'attempts' => 0,
        ],
    ]);
}

function processarFilaDeEmails()
{
    $query = new WP_Query([
        'post_type' => 'email_queue',
        'post_status' => 'publish',
        'posts_per_page' => -1,
    ]);

    $max_attempts = 3;

    while ($query->have_posts()) {
        $query->the_post();

        $post_id = get_the_ID();
        $attempts = (int) get_post_meta($post_id, 'attempts', true);
        $destinatario = get_post_meta($post_id, 'email_destinatario', true);
        $assunto = get_post_meta($post_id, 'assunto', true);
        $mensagem = get_the_content();

        if ($attempts >= $max_attempts) {
            error_log("E-mail para {$destinatario} falhou após {$max_attempts} tentativas.");
            wp_delete_post($post_id, true);
            continue;
        }

        if (sendEmail($destinatario, $assunto, $mensagem)) {
            wp_delete_post($post_id, true);
        } else {
            update_post_meta($post_id, 'attempts', $attempts + 1);
        }
    }

    wp_reset_postdata();
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
        error_log("Erro ao enviar o e-mail: {$mail->ErrorInfo}");
        return false;
    }
}

function pluginHandleRequest()
{
    if (isset($_POST["nome"])) {
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

        enfileirarEmail($destinatario, $assunto, $mensagem);
        echo esc_html('E-mail enfileirado com sucesso!');
        exit;
    }
}

add_action('shutdown', 'processarFilaDeEmails');
add_action('init', 'pluginHandleRequest');
