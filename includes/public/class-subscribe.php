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
        
        // El formulario se procesa en 'init' porque redirige
        add_action( 'init', array( $this, 'process_form' ) );
        
        // CAMBIO IMPORTANTE: La activación se mueve a 'template_redirect'
        // Esto nos permite cargar el diseño del tema (Header/Footer)
        add_action( 'template_redirect', array( $this, 'process_activation' ) );
        
        // Hook para Action Scheduler
        add_action( 'adp_send_verification_email_event', array( $this, 'execute_verification_email' ), 10, 2 );
    }

    /**
     * Procesa el formulario POST.
     */
    public function process_form() {
        if ( ! isset( $_POST['adp_subscribe_submit'] ) ) return;
        if ( ! empty( $_POST['adp_honey_check'] ) ) return;
        if ( ! isset( $_POST['adp_nonce'] ) || ! wp_verify_nonce( $_POST['adp_nonce'], 'adp_save_sub' ) ) return;

        $email = sanitize_email( $_POST['adp_email'] );
        $redirect_url = remove_query_arg( array( 'adp_status', 'adp_action', 'token' ) );

        if ( ! is_email( $email ) ) return;

        $subscriber = $this->db->get_subscriber( $email );
        $activation_hash = md5( $email . time() . wp_salt() );
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
            if ( function_exists( 'as_enqueue_async_action' ) ) {
                as_enqueue_async_action( 'adp_send_verification_email_event', array( 'email' => $email, 'hash' => $activation_hash ), 'adp_emails' );
            } else {
                $this->execute_verification_email( $email, $activation_hash );
            }
            wp_safe_redirect( add_query_arg( 'adp_status', 'pending', $redirect_url ) );
            exit;
        }
    }

    /**
     * Procesa la solicitud GET del enlace de confirmación.
     * AHORA INTEGRADO CON EL TEMA.
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
        $home_url = home_url( '/' );

        // 1. Cargamos el encabezado del sitio (Menú, Logo, etc.)
        get_header(); 
        ?>

        <div class="adp-activation-wrapper" style="max-width: 800px; margin: 80px auto; padding: 40px 20px; text-align: center; font-family: sans-serif;">
            
            <?php if ( $activated ) : ?>
                <div style="margin-bottom: 20px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#00a32a" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                
                <h1 style="font-size: 32px; margin-bottom: 20px; color: #333;">¡Suscripción Confirmada!</h1>
                
                <p style="font-size: 18px; line-height: 1.6; color: #666; margin-bottom: 40px;">
                    Tu correo <strong><?php echo esc_html( $email ); ?></strong> ha sido verificado correctamente.<br>
                    Ya formas parte de nuestra comunidad.
                </p>

                <a href="<?php echo esc_url( $home_url ); ?>" style="display: inline-block; padding: 12px 30px; background-color: #2271b1; color: #fff; text-decoration: none; border-radius: 5px; font-weight: bold; transition: background 0.3s;">
                    Volver al Inicio
                </a>

            <?php else : ?>
                <div style="margin-bottom: 20px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#d63638" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                </div>

                <h1 style="font-size: 32px; margin-bottom: 20px; color: #333;">Enlace no válido</h1>
                
                <p style="font-size: 18px; line-height: 1.6; color: #666; margin-bottom: 40px;">
                    Este enlace de confirmación ha expirado o ya fue utilizado anteriormente.
                </p>

                <a href="<?php echo esc_url( $home_url ); ?>" style="display: inline-block; padding: 12px 30px; background-color: #555; color: #fff; text-decoration: none; border-radius: 5px;">
                    Ir al Inicio
                </a>
            <?php endif; ?>

        </div>

        <?php
        // 3. Cargamos el pie de página del sitio
        get_footer();
        
        // 4. Detenemos la ejecución para que no cargue nada más (como 404)
        exit;
    }

    /**
     * Envía el correo utilizando la plantilla visual unificada.
     */
    public function execute_verification_email( $email, $hash ) {
        $blog_name = get_bloginfo( 'name' );
        $activation_link = add_query_arg( array( 'adp_action' => 'activate', 'email' => urlencode( $email ), 'token' => $hash ), home_url( '/' ) );
        $subject = "Confirma tu suscripción a $blog_name";
        $from = get_option( 'adp_sender_email' ) ?: get_option( 'admin_email' );

        ob_start();
        include ADP_PATH . 'templates/emails/verification.php';
        $message = ob_get_clean();

        $headers = array( 'Content-Type: text/html; charset=UTF-8', 'From: ' . $blog_name . ' <' . $from . '>' );
        $result = wp_mail( $email, $subject, $message, $headers );

        if ( ! $result ) {
            throw new Exception( "Falló el envío de verificación a $email." );
        }
    }
}