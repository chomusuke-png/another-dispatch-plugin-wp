<?php

/**
 * Class ADP_Subscribe_Handler
 */
class ADP_Subscribe_Handler {

    private $db;

    public function __construct( $db ) {
        $this->db = $db;
        add_action( 'init', array( $this, 'process_form' ) );
        add_action( 'init', array( $this, 'process_activation' ) );
        add_action( 'adp_send_verification_email_event', array( $this, 'execute_verification_email' ), 10, 2 );
    }

    public function process_form() {
        if ( ! isset( $_POST['adp_subscribe_submit'] ) ) return;
        if ( ! empty( $_POST['adp_honey_check'] ) ) return;
        if ( ! isset( $_POST['adp_nonce'] ) || ! wp_verify_nonce( $_POST['adp_nonce'], 'adp_save_sub' ) ) return;

        $email = sanitize_email( $_POST['adp_email'] );
        $redirect_url = remove_query_arg( array( 'adp_status', 'adp_action', 'token' ) );

        if ( ! is_email( $email ) ) return;

        $subscriber = $this->db->get_subscriber( $email );
        $activation_hash = md5( $email . time() . wp_salt() );

        // Lógica de guardado (sin cambios)
        $should_send = false;
        
        if ( $subscriber ) {
            if ( 'active' === $subscriber->status ) {
                wp_safe_redirect( add_query_arg( 'adp_status', 'exists', $redirect_url ) );
                exit;
            } 
            if ( 'pending' === $subscriber->status ) {
                $this->db->update_subscriber_hash( $email, $activation_hash );
                $should_send = true;
            }
        } else {
            $saved = $this->db->add_pending_subscriber( $email, $activation_hash );
            if ( $saved ) $should_send = true;
        }

        if ( $should_send ) {
            // --- CAMBIO: En vez de enviar directo, lo encolamos ---
            if ( function_exists( 'as_enqueue_async_action' ) ) {
                // 'async' intenta ejecutarse lo antes posible sin esperar al cron de lotes
                as_enqueue_async_action( 
                    'adp_send_verification_email_event', 
                    array( 'email' => $email, 'hash' => $activation_hash ),
                    'adp_emails' // Grupo para que salga en el Debug
                );
            } else {
                // Fallback por si AS no carga
                $this->execute_verification_email( $email, $activation_hash );
            }

            wp_safe_redirect( add_query_arg( 'adp_status', 'pending', $redirect_url ) );
            exit;
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
        $redirect_url = home_url( '/' );

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
     * Esta función ahora es pública para ser llamada por el Hook de Action Scheduler.
     * (Antes se llamaba send_confirmation_email, le cambié el nombre para ser explícito en el hook)
     */
    public function execute_verification_email( $email, $hash ) {
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
        
        $message  = "<h1>¡Casi listo!</h1>";
        $message .= "<p>Gracias por unirte a $blog_name. Para empezar a recibir correos, por favor confirma tu dirección haciendo clic abajo:</p>";
        $message .= "<p><a href='" . esc_url( $activation_link ) . "' style='padding:10px 20px; background:#2271b1; color:#fff; text-decoration:none; border-radius:4px; display:inline-block;'>Confirmar Suscripción</a></p>";
        $message .= "<p><small>Si no solicitaste esto, puedes ignorar este mensaje.</small></p>";

        $from = get_option( 'adp_sender_email' ) ?: get_option( 'admin_email' );
        $headers = array( 
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $blog_name . ' <' . $from . '>'
        );

        // Envío real
        wp_mail( $email, $subject, $message, $headers );
    }
}