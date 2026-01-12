<?php

/**
 * Class ADP_Email_Sender
 *
 * Procesa el envío de correos utilizando Action Scheduler para evitar timeouts.
 */
class ADP_Email_Sender {

    /** @var ADP_DB */
    private $db;

    /**
     * Configuración de rendimiento:
     * BATCH_SIZE: Cuántos correos enviar por ejecución.
     * BATCH_DELAY: Segundos a esperar entre lotes (evita bloqueo SMTP del host).
     */
    const BATCH_SIZE  = 50;
    const BATCH_DELAY = 120; // 2 minutos de descanso entre lotes

    public function __construct( $db ) {
        $this->db = $db;

        // Hook registrado por Action Scheduler
        add_action( 'adp_process_batch_send', array( $this, 'process_batch' ), 10, 2 );
        
        // Mantener compatibilidad con Digest Mensual
        add_action( 'adp_monthly_digest_event', array( $this, 'send_digest_batch' ), 10, 1 );
    }

    /**
     * Procesa un lote de correos y se auto-programa para el siguiente si es necesario.
     */
    public function process_batch( $post_id, $offset = 0 ) {
        // 1. Obtener suscriptores (Solo ACTIVOS)
        $subscribers = $this->db->get_subscribers( self::BATCH_SIZE, $offset, 'active' );

        if ( empty( $subscribers ) ) {
            return; // Fin del proceso
        }

        $post = get_post( $post_id );
        if ( ! $post ) return;

        // 2. Preparar el contenido del correo
        $subject = 'Nuevo post: ' . $post->post_title;
        $body    = $this->prepare_single_email_html( $post );
        $headers = $this->get_headers();

        // 3. Enviar lote actual
        foreach ( $subscribers as $sub ) {
            $this->send_individual_email( $sub['email'], $subject, $body, $headers );
        }

        // 4. ¿Quedan más suscriptores? Programar siguiente lote con RETRASO
        // Si obtuvimos menos suscriptores que el límite, significa que ya acabamos.
        if ( count( $subscribers ) === self::BATCH_SIZE ) {
            $new_offset = $offset + self::BATCH_SIZE;
            
            // Programar para dentro de X segundos (BATCH_DELAY)
            if ( function_exists( 'as_schedule_single_action' ) ) {
                as_schedule_single_action( 
                    time() + self::BATCH_DELAY, 
                    'adp_process_batch_send', 
                    array( 'post_id' => $post_id, 'offset' => $new_offset ),
                    'adp_emails' 
                );
            }
        }
    }

    /**
     * Genera el HTML para un post individual.
     */
    private function prepare_single_email_html( $post ) {
        $blog_name      = get_bloginfo( 'name' );
        $post_title     = $post->post_title;
        $post_link      = get_permalink( $post->ID );
        $featured_image = get_the_post_thumbnail_url( $post->ID, 'full' );
        
        // Procesar contenido (rutas absolutas para imágenes)
        $post_content   = apply_filters( 'the_content', $post->post_content );
        $base_url       = home_url();
        $post_content   = preg_replace( '/(href|src)=("|\')\//i', '$1=$2' . $base_url . '/', $post_content );
        
        $unsubscribe_link = '##UNSUBSCRIBE_URL##'; // Placeholder

        ob_start();
        include ADP_PATH . 'templates/emails/single.php';
        return ob_get_clean();
    }

    /**
     * Envía el correo reemplazando el link de baja.
     */
    private function send_individual_email( $email, $subject, $body, $headers ) {
        $hash      = md5( $email . wp_salt() );
        $unsub_url = add_query_arg(
            array( 'adp_action' => 'unsubscribe', 'email' => urlencode( $email ), 'hash' => $hash ),
            home_url( '/' )
        );
        
        $final_body = str_replace( '##UNSUBSCRIBE_URL##', $unsub_url, $body );
        wp_mail( $email, $subject, $final_body, $headers );
    }

    private function get_headers() {
        $from_email = get_option( 'adp_sender_email' ) ?: get_option( 'admin_email' );
        $blog_name  = get_bloginfo( 'name' );
        return array( 
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $blog_name . ' <' . $from_email . '>'
        );
    }

    /**
     * Helper para procesar el loop de envío y reemplazar placeholders
     */
    private function process_queue( $subscribers, $subject, $template_html, $headers ) {
        foreach ( $subscribers as $sub ) {
            $email = $sub['email'];
            $hash  = md5( $email . wp_salt() );
            $unsub_url = add_query_arg(
                array( 'adp_action' => 'unsubscribe', 'email' => urlencode( $email ), 'hash' => $hash ),
                home_url( '/' )
            );

            $body = str_replace( '##UNSUBSCRIBE_URL##', $unsub_url, $template_html );
            wp_mail( $email, $subject, $body, $headers );
        }
    }
}