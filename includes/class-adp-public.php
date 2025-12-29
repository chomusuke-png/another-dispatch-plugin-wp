<?php

/**
 * Class ADP_Public
 *
 * Maneja la interfaz pública: Shortcodes, procesamiento de formularios y feedback.
 */
class ADP_Public {

    /**
     * @var ADP_DB Instancia de la base de datos.
     */
    private $db;

    /**
     * Constructor.
     *
     * @param ADP_DB $db Inyección de dependencia de la base de datos.
     */
    public function __construct( $db ) {
        $this->db = $db;
        add_shortcode( 'adp_subscribe', array( $this, 'render_shortcode' ) );
        add_action( 'init', array( $this, 'process_subscription' ) );
        add_action( 'init', array( $this, 'process_unsubscribe' ) );
    }

    /**
     * Renderiza el formulario de suscripción mediante Shortcode.
     *
     * @return string HTML del formulario.
     */
    public function render_shortcode() {
        ob_start();
        
        // Detección simple de mensajes vía query string
        $message = '';
        if ( isset( $_GET['adp_status'] ) ) {
            $status = sanitize_text_field( $_GET['adp_status'] );
            if ( 'success' === $status ) {
                $message = '<div style="color: green; padding: 10px; background: #eaffea; border: 1px solid green; margin-bottom: 10px;">¡Gracias por suscribirte!</div>';
            } elseif ( 'exists' === $status ) {
                $message = '<div style="color: orange; padding: 10px; background: #fff8e1; border: 1px solid orange; margin-bottom: 10px;">Este correo ya está registrado.</div>';
            }
        }

        include ADP_PATH . 'templates/subscription-form.php';
        return ob_get_clean();
    }

    /**
     * Procesa el envío del formulario de suscripción.
     * Realiza redirecciones para evitar el reenvío del formulario (PRG pattern).
     */
    public function process_subscription() {
        if ( ! isset( $_POST['adp_subscribe_submit'] ) ) {
            return;
        }

        if ( ! isset( $_POST['adp_nonce'] ) || ! wp_verify_nonce( $_POST['adp_nonce'], 'adp_save_sub' ) ) {
            return;
        }

        $email = sanitize_email( $_POST['adp_email'] );
        $redirect_url = remove_query_arg( 'adp_status' );

        if ( ! is_email( $email ) ) {
            return; // O manejar error de email inválido
        }

        if ( $this->db->exists( $email ) ) {
            wp_safe_redirect( add_query_arg( 'adp_status', 'exists', $redirect_url ) );
            exit;
        }

        $saved = $this->db->add_subscriber( $email );

        if ( $saved ) {
            wp_safe_redirect( add_query_arg( 'adp_status', 'success', $redirect_url ) );
            exit;
        }
    }

    /**
     * Procesa la solicitud de desuscripción vía URL.
     */
    public function process_unsubscribe() {
        if ( isset( $_GET['adp_action'] ) && 'unsubscribe' === $_GET['adp_action'] ) {
            
            $email = isset( $_GET['email'] ) ? sanitize_email( $_GET['email'] ) : '';
            $hash  = isset( $_GET['hash'] ) ? sanitize_text_field( $_GET['hash'] ) : '';

            // Validación de seguridad
            if ( ! is_email( $email ) || empty( $hash ) ) {
                wp_die( 'Solicitud inválida.', 'Error' );
            }

            // Verificamos que el hash coincida con el email + salt del sitio
            $expected_hash = md5( $email . wp_salt() );

            if ( ! hash_equals( $expected_hash, $hash ) ) {
                wp_die( 'El enlace de desuscripción es inválido o ha caducado.', 'Error de seguridad' );
            }

            // Proceder a borrar
            $this->db->delete_subscriber( $email );

            // Mensaje final (podrías redirigir a una página de "Gracias" dedicada)
            wp_die( 
                '<h1>Te has desuscrito correctamente.</h1><p>Ya no recibirás más correos de notificaciones.</p><p><a href="' . home_url() . '">Volver al inicio</a></p>', 
                'Desuscrito', 
                array( 'response' => 200 ) 
            );
        }
    }
}