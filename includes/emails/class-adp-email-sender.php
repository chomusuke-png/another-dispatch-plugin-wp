<?php

/**
 * Class ADP_Email_Sender
 *
 * Ejecuta el envío masivo de correos cuando el Cron lo indica.
 */
class ADP_Email_Sender {

    /**
     * @var ADP_DB
     */
    private $db;

    /**
     * @param ADP_DB $db
     */
    public function __construct( $db ) {
        $this->db = $db;
        add_action( 'adp_send_notification_cron', array( $this, 'send_batch' ), 10, 1 );
    }

    /**
     * Prepara y envía los correos.
     *
     * @param int $post_id
     */
    public function send_batch( $post_id ) {
        $subscribers = $this->db->get_subscribers();
        if ( empty( $subscribers ) ) return;

        $post = get_post( $post_id );
        if ( ! $post ) return;

        // Datos para la plantilla
        $blog_name      = get_bloginfo( 'name' );
        $post_title     = $post->post_title;
        $post_link      = get_permalink( $post_id );
        $featured_image = get_the_post_thumbnail_url( $post_id, 'full' );
        $post_content   = apply_filters( 'the_content', $post->post_content );
        $unsubscribe_link = '##UNSUBSCRIBE_URL##'; // Placeholder

        // Generar HTML base
        ob_start();
        include ADP_PATH . 'templates/email-template.php';
        $template_html = ob_get_clean();

        $subject = 'Nuevo post: ' . $post_title;
        $headers = array( 'Content-Type: text/html; charset=UTF-8' );

        foreach ( $subscribers as $sub ) {
            $email = $sub['email'];
            
            // Generar link de desuscripción único
            $hash = md5( $email . wp_salt() );
            $unsub_url = add_query_arg(
                array( 'adp_action' => 'unsubscribe', 'email' => urlencode( $email ), 'hash' => $hash ),
                home_url( '/' )
            );

            // Reemplazar placeholder
            $body = str_replace( '##UNSUBSCRIBE_URL##', $unsub_url, $template_html );

            wp_mail( $email, $subject, $body, $headers );
        }
    }
}