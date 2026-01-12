<?php

/**
 * Class ADP_Subscribe_Handler
 *
 * Maneja el procesamiento del formulario (POST) y la activación de cuenta (GET).
 */
class ADP_Subscribe_Handler {

    /**
     * @var ADP_DB
     */
    private $db;

    /**
     * @param ADP_DB $db
     */
    public function __construct( $db ) {
        $this->db = $db;
        add_action( 'init', array( $this, 'process_form' ) );
        add_action( 'init', array( $this, 'process_activation' ) );
    }

    /**
     * Procesa el formulario POST, valida Honeypot e inicia Double Opt-in.
     */
    public function process_form() {
        if ( ! isset( $_POST['adp_subscribe_submit'] ) ) return;

        // 1. Honeypot
        if ( ! empty( $_POST['adp_honey_check'] ) ) return;

        if ( ! isset( $_POST['adp_nonce'] ) || ! wp_verify_nonce( $_POST['adp_nonce'], 'adp_save_sub' ) ) return;

        $email = sanitize_email( $_POST['adp_email'] );
        $redirect_url = remove_query_arg( array( 'adp_status', 'adp_action', 'token' ) );

        if ( ! is_email( $email ) ) return;

        // --- LÓGICA CORREGIDA ---
        
        // Buscamos al suscriptor
        $subscriber = $this->db->get_subscriber( $email );
        $activation_hash = md5( $email . time() . wp_salt() );

        if ( $subscriber ) {
            // CASO A: Ya existe y está activo -> Error "Ya existe"
            if ( 'active' === $subscriber->status ) {
                wp_safe_redirect( add_query_arg( 'adp_status', 'exists', $redirect_url ) );
                exit;
            } 
            
            // CASO B: Ya existe pero está pendiente -> Reenviar confirmación
            if ( 'pending' === $subscriber->status ) {
                // Actualizamos el hash para invalidar el anterior y refrescar la fecha
                $this->db->update_subscriber_hash( $email, $activation_hash );
                $this->send_confirmation_email( $email, $activation_hash );
                
                // Redirigimos al mismo mensaje de "Pendiente" (éxito)
                wp_safe_redirect( add_query_arg( 'adp_status', 'pending', $redirect_url ) );
                exit;
            }
        } else {
            // CASO C: No existe -> Crear nuevo
            $saved = $this->db->add_pending_subscriber( $email, $activation_hash );
            if ( $saved ) {
                $this->send_confirmation_email( $email, $activation_hash );
                wp_safe_redirect( add_query_arg( 'adp_status', 'pending', $redirect_url ) );
                exit;
            }
        }
    }

    /**
     * Procesa la solicitud GET del enlace de confirmación.
     */
    public function process_activation() {
        if ( ! isset( $_GET['adp_action'] ) || 'activate' !== $_GET['adp_action'] ) {
            return;
        }

        $email = isset( $_GET['email'] ) ? sanitize_email( $_GET['email'] ) : '';
        $hash  = isset( $_GET['token'] ) ? sanitize_text_field( $_GET['token'] ) : '';

        if ( empty( $email ) || empty( $hash ) ) {
            return;
        }

        $activated = $this->db->activate_subscriber( $email, $hash );
        $redirect_url = home_url( '/' ); // O una página específica de "Gracias"

        if ( $activated ) {
            wp_die( 
                '<h1>¡Suscripción Confirmada!</h1><p>Tu correo ha sido verificado y añadido a la lista.</p><a href="' . esc_url( $redirect_url ) . '">Volver al sitio</a>', 
                'Activado', 
                array( 'response' => 200 ) 
            );
        } else {
            wp_die( 'El enlace de activación es inválido o ya fue usado.' );
        }
    }

    /**
     * Envía el correo transaccional con el enlace de activación.
     *
     * @param string $email
     * @param string $hash
     */
    private function send_confirmation_email( $email, $hash ) {
        $blog_name = get_bloginfo( 'name' );
        $activation_link = add_query_arg(
            array(
                'adp_action' => 'activate',
                'email'      => urlencode( $email ),
                'token'      => $hash
            ),
            home_url( '/' )
        );

        $subject = "Confirma tu suscripción a $blog_name";
        
        // HTML simple para el correo de activación
        $message  = "<h1>¡Casi listo!</h1>";
        $message .= "<p>Gracias por unirte a $blog_name. Para empezar a recibir correos, por favor confirma tu dirección haciendo clic abajo:</p>";
        $message .= "<p><a href='" . esc_url( $activation_link ) . "' style='padding:10px 20px; background:#2271b1; color:#fff; text-decoration:none; border-radius:4px; display:inline-block;'>Confirmar Suscripción</a></p>";
        $message .= "<p><small>Si no solicitaste esto, puedes ignorar este mensaje.</small></p>";

        $headers = array( 'Content-Type: text/html; charset=UTF-8' );

        wp_mail( $email, $subject, $message, $headers );
    }
}