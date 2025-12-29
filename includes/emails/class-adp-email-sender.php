<?php

/**
 * Class ADP_Email_Sender
 *
 * Ejecuta el envío masivo de correos por lotes (Batching).
 */
class ADP_Email_Sender {

    /**
     * @var ADP_DB
     */
    private $db;

    /**
     * Tamaño del lote (Batch size).
     */
    const BATCH_SIZE = 50;

    /**
     * @param ADP_DB $db
     */
    public function __construct( $db ) {
        $this->db = $db;
        // Importante: indicamos que el hook acepta 2 argumentos ($post_id, $offset)
        add_action( 'adp_send_notification_cron', array( $this, 'send_batch' ), 10, 2 );
    }

    /**
     * Prepara y envía los correos del lote actual.
     * Si el lote está lleno, programa el siguiente.
     *
     * @param int $post_id
     * @param int $offset
     */
    public function send_batch( $post_id, $offset = 0 ) {
        // Obtener solo el lote actual
        $subscribers = $this->db->get_subscribers( self::BATCH_SIZE, $offset );

        if ( empty( $subscribers ) ) {
            return;
        }

        $post = get_post( $post_id );
        if ( ! $post ) return;

        // Datos para la plantilla
        $blog_name      = get_bloginfo( 'name' );
        $post_title     = $post->post_title;
        $post_link      = get_permalink( $post_id );
        $featured_image = get_the_post_thumbnail_url( $post_id, 'full' );
        $post_content   = apply_filters( 'the_content', $post->post_content );
        $unsubscribe_link = '##UNSUBSCRIBE_URL##';

        ob_start();
        include ADP_PATH . 'templates/email-template.php';
        $template_html = ob_get_clean();

        $subject = 'Nuevo post: ' . $post_title;
        $headers = array( 'Content-Type: text/html; charset=UTF-8' );

        // Enviar correos
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

        // Lógica de Recursividad:
        // Si obtuvimos la cantidad exacta del límite, asumimos que quedan más registros (o acabamos justo).
        // Programamos el siguiente evento inmediatamente.
        if ( count( $subscribers ) === self::BATCH_SIZE ) {
            wp_schedule_single_event( 
                time(), 
                'adp_send_notification_cron', 
                array( $post_id, $offset + self::BATCH_SIZE ) 
            );
        }
    }
}