<?php

/**
 * Class ADP_Shortcode
 *
 * Se encarga de renderizar el formulario y cargar sus estilos.
 */
class ADP_Shortcode {

    public function __construct() {
        add_shortcode( 'adp_subscribe', array( $this, 'render' ) );
        // Hook para cargar CSS del frontend
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
    }

    /**
     * Carga la hoja de estilos pública.
     */
    public function enqueue_styles() {
        wp_enqueue_style( 
            'adp-public-css', 
            ADP_URL . 'assets/css/public-style.css', 
            array(), 
            '1.0.0', 
            'all' 
        );
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
                $message = '<div class="adp-alert success">¡Gracias! Te has suscrito correctamente.</div>';
            } elseif ( 'exists' === $status ) {
                $message = '<div class="adp-alert error">Este correo ya está registrado en nuestra lista.</div>';
            }
        }

        include ADP_PATH . 'templates/subscription-form.php';
        return ob_get_clean();
    }
}