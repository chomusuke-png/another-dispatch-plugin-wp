<?php

declare(strict_types=1);

namespace Zumito\ADP\PublicHooks;

use Exception;

class Shortcode
{
    public function __construct()
    {
        add_shortcode('adp_subscribe', [$this, 'renderShortcode']);
    }

    public function renderShortcode($attributes = []): string
    {
        $parsedAttributes = shortcode_atts([
            'title' => 'Suscríbete a nuestro boletín',
            'desc'  => 'Recibe las últimas noticias en tu correo electrónico.'
        ], is_array($attributes) ? $attributes : []);

        $messageHtml = $this->getFeedbackMessageHtml();

        ob_start();

        try {
            $formAction = esc_url(admin_url('admin-post.php'));
            $title = esc_html($parsedAttributes['title']);
            $description = esc_html($parsedAttributes['desc']);
            
            require ADP_PATH . 'templates/subscription-form.php';
            
            $content = ob_get_clean();
            return $content !== false ? $content : '';
        } catch (Exception $exception) {
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            return '<p>Error cargando el formulario de suscripción.</p>';
        }
    }

    private function getFeedbackMessageHtml(): string
    {
        if (empty($_GET['adp_msg'])) {
            return '';
        }

        $msgCode = sanitize_text_field(wp_unslash($_GET['adp_msg']));
        
        $messages = [
            'subscribed'       => ['text' => 'Revisa tu bandeja de entrada para confirmar tu correo.', 'class' => 'adp-success'],
            'already_active'   => ['text' => 'Ya estás suscrito a nuestro boletín.', 'class' => 'adp-info'],
            'already_pending'  => ['text' => 'Ya te has registrado. Por favor, confirma el enlace enviado a tu correo.', 'class' => 'adp-warning'],
            'invalid_email'    => ['text' => 'Por favor, introduce un correo electrónico válido.', 'class' => 'adp-error'],
            'empty_email'      => ['text' => 'El campo de correo no puede estar vacío.', 'class' => 'adp-error'],
            'db_error'         => ['text' => 'Hubo un error al procesar tu solicitud. Intenta más tarde.', 'class' => 'adp-error'],
        ];

        if (!isset($messages[$msgCode])) {
            return '';
        }

        $messageData = $messages[$msgCode];

        return sprintf(
            '<div class="adp-feedback-msg %s"><p>%s</p></div>',
            esc_attr($messageData['class']),
            esc_html($messageData['text'])
        );
    }
}