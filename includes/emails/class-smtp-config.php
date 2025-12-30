<?php

/**
 * Class ADP_SMTP
 *
 * Configura PHPMailer para usar un servidor SMTP personalizado.
 */
class ADP_SMTP {

    public function __construct() {
        add_action( 'phpmailer_init', array( $this, 'configure_smtp' ) );
    }

    /**
     * Intercepta la instancia de PHPMailer y aplica la configuración.
     *
     * @param PHPMailer $phpmailer Instancia pasada por referencia.
     */
    public function configure_smtp( $phpmailer ) {
        // Obtener configuración guardada
        $smtp_host = get_option( 'adp_smtp_host' );
        $smtp_user = get_option( 'adp_smtp_user' );
        $smtp_pass = get_option( 'adp_smtp_pass' );
        
        // Si no hay host configurado, no hacemos nada (usa mail() por defecto)
        if ( empty( $smtp_host ) ) {
            return;
        }

        // Configuración de Servidor
        $phpmailer->isSMTP();
        $phpmailer->Host       = $smtp_host;
        $phpmailer->Port       = (int) get_option( 'adp_smtp_port', 587 );
        $phpmailer->SMTPSecure = get_option( 'adp_smtp_secure', 'tls' ); // tls o ssl
        
        // Autenticación
        if ( ! empty( $smtp_user ) && ! empty( $smtp_pass ) ) {
            $phpmailer->SMTPAuth = true;
            $phpmailer->Username = $smtp_user;
            $phpmailer->Password = $smtp_pass;
        } else {
            // Caso raro: SMTP sin autenticación
            $phpmailer->SMTPAuth = false;
        }

        // Forzar codificación UTF-8
        $phpmailer->CharSet = 'UTF-8';
    }
}