<?php

/**
 * Class ADP_Email_Sender
 *
 * Procesa el envío de correos utilizando Action Scheduler para evitar timeouts.
 */
class ADP_Email_Sender {

    /** @var ADP_DB */
    private $db;

    public function __construct( $db ) {
        $this->db = $db;

        add_action( 'adp_process_batch_send', array( $this, 'process_batch' ), 10, 2 );
        add_action( 'adp_monthly_digest_event', array( $this, 'send_digest_batch' ), 10, 1 );
    }

    /**
     * Helper para obtener configuración de rendimiento dinámica.
     */
    private function get_batch_size() {
        return (int) get_option( 'adp_batch_size', 50 ); // 50 por defecto
    }

    private function get_batch_delay() {
        return (int) get_option( 'adp_batch_delay', 120 ); // 120 seg por defecto
    }

    /**
     * Procesa un lote de correos.
     */
    public function process_batch( $post_id, $offset = 0 ) {
        $limit = $this->get_batch_size();
        
        $subscribers = $this->db->get_subscribers( $limit, $offset, 'active' );

        if ( empty( $subscribers ) ) {
            return;
        }

        $post = get_post( $post_id );
        if ( ! $post ) return;

        $subject = 'Nuevo post: ' . $post->post_title;
        $body    = $this->prepare_single_email_html( $post );
        $headers = $this->get_headers();

        foreach ( $subscribers as $sub ) {
            $this->send_individual_email( $sub['email'], $subject, $body, $headers );
        }

        // Programar siguiente lote
        if ( count( $subscribers ) === $limit ) {
            $new_offset = $offset + $limit;
            
            // Usamos el delay dinámico
            $delay = $this->get_batch_delay();

            if ( function_exists( 'as_schedule_single_action' ) ) {
                as_schedule_single_action( 
                    time() + $delay, 
                    'adp_process_batch_send', 
                    array( 'post_id' => $post_id, 'offset' => $new_offset ),
                    'adp_emails' 
                );
            }
        }
    }

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

        $limit = $this->get_batch_size(); // Dinámico
        $subscribers = $this->db->get_subscribers( $limit, $offset, 'active' );
        
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

        if ( count( $subscribers ) === $limit ) {
            $delay = $this->get_batch_delay();
            wp_schedule_single_event( time() + $delay, 'adp_monthly_digest_event', array( $offset + $limit ) );
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
        $unsub_url = add_query_arg( array( 'adp_action' => 'unsubscribe', 'email' => urlencode( $email ), 'hash' => $hash ), home_url( '/' ) );
        
        $user_headers = $headers;
        if ( ! is_array( $user_headers ) ) $user_headers = explode( "\n", str_replace( "\r\n", "\n", $user_headers ) );
        $user_headers[] = 'List-Unsubscribe: <' . $unsub_url . '>';
        $user_headers[] = 'List-Unsubscribe-Post: List-Unsubscribe=One-Click';

        $final_body = str_replace( '##UNSUBSCRIBE_URL##', $unsub_url, $body );
        wp_mail( $email, $subject, $final_body, $user_headers );
    }

    private function get_headers() {
        $from_email = get_option( 'adp_sender_email' ) ?: get_option( 'admin_email' );
        $blog_name  = get_bloginfo( 'name' );
        return array( 'Content-Type: text/html; charset=UTF-8', 'From: ' . $blog_name . ' <' . $from_email . '>' );
    }
}