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

    public function send_digest_batch( $offset = 0 ) {
        // Solo ejecutar si la configuración es 'monthly'
        if ( 'monthly' !== get_option( 'adp_delivery_frequency' ) ) {
            return;
        }

        // 1. Obtener posts del mes ANTERIOR
        // Calculamos el primer y último día del mes pasado
        $first_day = date( 'Y-m-01', strtotime( 'last month' ) );
        $last_day  = date( 'Y-m-t', strtotime( 'last month' ) );

        $posts = get_posts( array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => -1, // Todos los posts
            'date_query'     => array(
                array(
                    'after'     => $first_day,
                    'before'    => $last_day,
                    'inclusive' => true,
                ),
            ),
        ) );

        if ( empty( $posts ) ) {
            error_log( 'ADP Digest: No hubo posts el mes pasado (' . date('F', strtotime('last month')) . '). No se envía nada.' );
            return;
        }

        // 2. Construir el HTML del Digest
        $blog_name = get_bloginfo( 'name' );
        $subject   = 'Resumen mensual de ' . $blog_name . ' - ' . date_i18n( 'F Y', strtotime( 'last month' ) );
        
        // Generamos el contenido HTML combinando los posts
        $digest_content = '<h2>Lo mejor del mes pasado</h2>';
        foreach ( $posts as $p ) {
            $thumb = get_the_post_thumbnail_url( $p->ID, 'medium' );
            $link  = get_permalink( $p->ID );
            
            $digest_content .= '<div style="margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 20px;">';
            if ( $thumb ) {
                $digest_content .= '<a href="' . $link . '"><img src="' . $thumb . '" style="max-width: 100%; border-radius: 4px; margin-bottom: 10px;"></a>';
            }
            $digest_content .= '<h3 style="margin: 0 0 10px 0;"><a href="' . $link . '" style="text-decoration: none; color: #333;">' . esc_html( $p->post_title ) . '</a></h3>';
            $digest_content .= '<p style="color: #666;">' . get_the_excerpt( $p ) . '</p>';
            $digest_content .= '<a href="' . $link . '" style="color: #2271b1;">Leer más &rarr;</a>';
            $digest_content .= '</div>';
        }

        // Usamos variables "falsas" para engañar a la plantilla email-template.php y reutilizarla
        $post_title     = $subject;
        $post_content   = $digest_content;
        $post_link      = home_url(); 
        $featured_image = ''; // Sin imagen principal gigante en el digest
        $unsubscribe_link = '##UNSUBSCRIBE_URL##';

        ob_start();
        include ADP_PATH . 'templates/email-template.php'; // Reutilizamos tu plantilla base
        $template_html = ob_get_clean();

        // 3. Preparar envío (Headers)
        $from_email = get_option( 'adp_sender_email' );
        if ( empty( $from_email ) || ! is_email( $from_email ) ) $from_email = get_option( 'admin_email' );
        
        $headers = array( 
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $blog_name . ' <' . $from_email . '>'
        );

        // 4. Obtener lote de suscriptores
        $subscribers = $this->db->get_subscribers( self::BATCH_SIZE, $offset );
        
        if ( empty( $subscribers ) ) return;

        // 5. Enviar
        foreach ( $subscribers as $sub ) {
            $email = $sub['email'];
            $hash = md5( $email . wp_salt() );
            $unsub_url = add_query_arg(
                array( 'adp_action' => 'unsubscribe', 'email' => urlencode( $email ), 'hash' => $hash ),
                home_url( '/' )
            );

            $body = str_replace( '##UNSUBSCRIBE_URL##', $unsub_url, $template_html );
            wp_mail( $email, $subject, $body, $headers );
        }

        // 6. Recursividad (Siguiente lote)
        if ( count( $subscribers ) === self::BATCH_SIZE ) {
            // Nota: Aquí programamos el evento MISMO digest para dentro de 1 minuto
            wp_schedule_single_event( time() + 60, 'adp_monthly_digest_event', array( $offset + self::BATCH_SIZE ) );
        }
    }
}