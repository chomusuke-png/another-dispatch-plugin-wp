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
    }

    public function send_batch( $post_id, $offset = 0 ) {
        error_log( "ADP Debug: Iniciando proceso de envío (Lote offset: $offset) para Post ID: $post_id" );

        $subscribers = $this->db->get_subscribers( self::BATCH_SIZE, $offset );

        if ( empty( $subscribers ) ) {
            error_log( "ADP Debug: No se encontraron suscriptores (o fin de la lista). Offset: $offset" );
            return;
        }

        $post = get_post( $post_id );
        if ( ! $post ) {
            error_log( "ADP Error: No se pudo obtener el objeto Post ID: $post_id" );
            return;
        }

        $blog_name      = get_bloginfo( 'name' );
        $post_title     = $post->post_title;
        $post_link      = get_permalink( $post_id );
        $featured_image = get_the_post_thumbnail_url( $post_id, 'full' );
        
        $post_content   = apply_filters( 'the_content', $post->post_content );
        $base_url       = home_url();
        $post_content   = preg_replace( '/(href|src)=("|\')\//i', '$1=$2' . $base_url . '/', $post_content );

        ob_start();
        $unsubscribe_link = '##UNSUBSCRIBE_URL##';
        include ADP_PATH . 'templates/email-template.php';
        $template_html = ob_get_clean();

        $subject = 'Nuevo post: ' . $post_title;
        
        $from_email = get_option( 'adp_sender_email' );
        if ( empty( $from_email ) || ! is_email( $from_email ) ) {
            $from_email = get_option( 'admin_email' );
        }

        $headers = array( 
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $blog_name . ' <' . $from_email . '>'
        );

        $sent_count = 0;
        foreach ( $subscribers as $sub ) {
            $email = $sub['email'];
            $hash = md5( $email . wp_salt() );
            $unsub_url = add_query_arg(
                array( 'adp_action' => 'unsubscribe', 'email' => urlencode( $email ), 'hash' => $hash ),
                home_url( '/' )
            );

            $body = str_replace( '##UNSUBSCRIBE_URL##', $unsub_url, $template_html );
            
            $result = wp_mail( $email, $subject, $body, $headers );
            
            if ( $result ) {
                $sent_count++;
            } else {
                error_log( "ADP Error: Falló wp_mail para $email" );
            }
        }

        error_log( "ADP Debug: Lote finalizado. Enviados: $sent_count de " . count($subscribers) );

        if ( count( $subscribers ) === self::BATCH_SIZE ) {
            wp_schedule_single_event( time(), 'adp_send_notification_cron', array( $post_id, $offset + self::BATCH_SIZE ) );
            error_log( "ADP Debug: Programando siguiente lote..." );
        }
    }
}