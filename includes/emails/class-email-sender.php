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
     * --- FUNCIÓN RECUPERADA (Era necesaria para el Digest Mensual) ---
     */
    public function send_digest_batch( $offset = 0 ) {
        if ( 'monthly' !== get_option( 'adp_delivery_frequency' ) ) return;

        // Calcular mes pasado
        $first_day = date( 'Y-m-01', strtotime( 'last month' ) );
        $last_day  = date( 'Y-m-t', strtotime( 'last month' ) );

        $posts = get_posts( array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'date_query'     => array(
                array( 'after' => $first_day, 'before' => $last_day, 'inclusive' => true ),
            ),
        ) );

        if ( empty( $posts ) ) return;

        // Nota: Aquí usamos la lógica simple vieja para el digest, 
        // idealmente en el futuro también podrías migrar esto a Action Scheduler.
        $subscribers = $this->db->get_subscribers( self::BATCH_SIZE, $offset, 'active' );
        if ( empty( $subscribers ) ) return;

        $blog_name   = get_bloginfo( 'name' );
        $email_title = 'Resumen de ' . date_i18n( 'F Y', strtotime( 'last month' ) );
        $posts_list  = $posts; 

        ob_start();
        $unsubscribe_link = '##UNSUBSCRIBE_URL##';
        include ADP_PATH . 'templates/emails/digest.php';
        $template_html = ob_get_clean();

        $subject = $email_title . ' - ' . $blog_name;
        $headers = $this->get_headers();

        foreach ( $subscribers as $sub ) {
            $this->send_individual_email( $sub['email'], $subject, $template_html, $headers );
        }

        if ( count( $subscribers ) === self::BATCH_SIZE ) {
            wp_schedule_single_event( time() + 60, 'adp_monthly_digest_event', array( $offset + self::BATCH_SIZE ) );
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
        
        $post_content   = apply_filters( 'the_content', $post->post_content );
        $base_url       = home_url();
        $post_content   = preg_replace( '/(href|src)=("|\')\//i', '$1=$2' . $base_url . '/', $post_content );
        
        $unsubscribe_link = '##UNSUBSCRIBE_URL##'; 

        ob_start();
        include ADP_PATH . 'templates/emails/single.php';
        return ob_get_clean();
    }

    /**
     * Envía el correo individual añadiendo headers de Entregabilidad (RFC 8058).
     */
    private function send_individual_email( $email, $subject, $body, $headers ) {
        $hash      = md5( $email . wp_salt() );
        $unsub_url = add_query_arg(
            array( 'adp_action' => 'unsubscribe', 'email' => urlencode( $email ), 'hash' => $hash ),
            home_url( '/' )
        );
        
        $user_headers = $headers;

        if ( ! is_array( $user_headers ) ) {
            $user_headers = explode( "\n", str_replace( "\r\n", "\n", $user_headers ) );
        }

        $user_headers[] = 'List-Unsubscribe: <' . $unsub_url . '>';
        $user_headers[] = 'List-Unsubscribe-Post: List-Unsubscribe=One-Click';
        $final_body = str_replace( '##UNSUBSCRIBE_URL##', $unsub_url, $body );
        
        wp_mail( $email, $subject, $final_body, $user_headers );
    }

    private function get_headers() {
        $from_email = get_option( 'adp_sender_email' ) ?: get_option( 'admin_email' );
        $blog_name  = get_bloginfo( 'name' );
        return array( 
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $blog_name . ' <' . $from_email . '>'
        );
    }
}