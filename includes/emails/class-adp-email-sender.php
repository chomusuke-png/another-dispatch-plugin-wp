<?php

/**
 * Class ADP_Email_Sender
 *
 * Ejecuta el envío masivo de correos por lotes (Batching).
 */
class ADP_Email_Sender {

    private $db;
    const BATCH_SIZE = 50;

    public function __construct( $db ) {
        $this->db = $db;
        add_action( 'adp_send_notification_cron', array( $this, 'send_batch' ), 10, 2 );
        add_action( 'adp_monthly_digest_event', array( $this, 'send_digest_batch' ), 10, 1 );
    }

    /**
     * ENVÍO INDIVIDUAL (Inmediato)
     * Usa: templates/email-template.php
     */
    public function send_batch( $post_id, $offset = 0 ) {
        $subscribers = $this->db->get_subscribers( self::BATCH_SIZE, $offset );
        if ( empty( $subscribers ) ) return;

        $post = get_post( $post_id );
        if ( ! $post ) return;

        // --- Preparar datos para Single Post ---
        $blog_name      = get_bloginfo( 'name' );
        $post_title     = $post->post_title;
        $post_link      = get_permalink( $post_id );
        $featured_image = get_the_post_thumbnail_url( $post_id, 'full' );
        
        $post_content   = apply_filters( 'the_content', $post->post_content );
        $base_url       = home_url();
        $post_content   = preg_replace( '/(href|src)=("|\')\//i', '$1=$2' . $base_url . '/', $post_content );

        // Cargar Template A (Individual)
        ob_start();
        $unsubscribe_link = '##UNSUBSCRIBE_URL##';
        include ADP_PATH . 'templates/email-template.php';
        $template_html = ob_get_clean();

        $subject = 'Nuevo post: ' . $post_title;
        $headers = $this->get_headers( $blog_name );

        $this->process_queue( $subscribers, $subject, $template_html, $headers );

        if ( count( $subscribers ) === self::BATCH_SIZE ) {
            wp_schedule_single_event( time(), 'adp_send_notification_cron', array( $post_id, $offset + self::BATCH_SIZE ) );
        }
    }

    /**
     * ENVÍO MENSUAL (Digest)
     * Usa: templates/email-digest-template.php
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

        $subscribers = $this->db->get_subscribers( self::BATCH_SIZE, $offset );
        if ( empty( $subscribers ) ) return;

        // --- Preparar datos para Digest ---
        $blog_name   = get_bloginfo( 'name' );
        $email_title = 'Resumen de ' . date_i18n( 'F Y', strtotime( 'last month' ) );
        $posts_list  = $posts; // Pasamos el array limpio a la vista

        // Cargar Template B (Digest)
        ob_start();
        $unsubscribe_link = '##UNSUBSCRIBE_URL##';
        include ADP_PATH . 'templates/email-digest-template.php';
        $template_html = ob_get_clean();

        $subject = $email_title . ' - ' . $blog_name;
        $headers = $this->get_headers( $blog_name );

        $this->process_queue( $subscribers, $subject, $template_html, $headers );

        if ( count( $subscribers ) === self::BATCH_SIZE ) {
            wp_schedule_single_event( time() + 60, 'adp_monthly_digest_event', array( $offset + self::BATCH_SIZE ) );
        }
    }

    /**
     * Helper para obtener headers comunes
     */
    private function get_headers( $blog_name ) {
        $from_email = get_option( 'adp_sender_email' );
        if ( empty( $from_email ) || ! is_email( $from_email ) ) $from_email = get_option( 'admin_email' );
        
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