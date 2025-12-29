<?php

/**
 * Class ADP_Shortcode
 *
 * Se encarga únicamente de renderizar el formulario.
 */
class ADP_Shortcode {

    public function __construct() {
        add_shortcode( 'adp_subscribe', array( $this, 'render' ) );
    }

    /**
     * Renderiza el HTML del formulario.
     * @return string
     */
    public function render() {
        ob_start();
        
        $message = '';
        if ( isset( $_GET['adp_status'] ) ) {
            $status = sanitize_text_field( $_GET['adp_status'] );
            if ( 'success' === $status ) {
                $message = '<div class="adp-msg success" style="color: green;">¡Gracias por suscribirte!</div>';
            } elseif ( 'exists' === $status ) {
                $message = '<div class="adp-msg error" style="color: orange;">Este correo ya está registrado.</div>';
            }
        }

        include ADP_PATH . 'templates/subscription-form.php';
        return ob_get_clean();
    }
}