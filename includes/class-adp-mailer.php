<?php

/**
 * Class ADP_Mailer
 *
 * Maneja el envío de correos electrónicos HTML cuando se publica un post.
 */
class ADP_Mailer {

    private $db;

    public function __construct( $db ) {
        $this->db = $db;
        add_action( 'transition_post_status', array( $this, 'notify_subscribers' ), 10, 3 );
    }

    public function notify_subscribers( $new_status, $old_status, $post ) {
        // Solo enviar si el post se está publicando por primera vez y es tipo 'post'
        if ( 'publish' !== $new_status || 'publish' === $old_status || 'post' !== $post->post_type ) {
            return;
        }

        $subscribers = $this->db->get_subscribers();
        
        if ( empty( $subscribers ) ) {
            return;
        }

        // Preparar datos para la plantilla
        $blog_name      = get_bloginfo( 'name' );
        $post_title     = $post->post_title;
        $post_link      = get_permalink( $post->ID );
        $featured_image = get_the_post_thumbnail_url( $post->ID, 'full' );

        if ( empty( $featured_image ) && isset( $_POST['_thumbnail_id'] ) ) {
            $thumbnail_id = intval( $_POST['_thumbnail_id'] );
            if ( $thumbnail_id > 0 ) {
                $featured_image = wp_get_attachment_image_url( $thumbnail_id, 'full' );
            }
        }
        
        // Aplicar filtros para renderizar shortcodes, imágenes, párrafos, etc.
        $post_content   = apply_filters( 'the_content', $post->post_content );

        // Cargar el HTML del correo
        ob_start();
        include ADP_PATH . 'templates/email-template.php';
        $message = ob_get_clean();

        $subject = 'Nuevo post: ' . $post_title;
        
        // Headers para permitir HTML
        $headers = array( 
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $blog_name . ' <' . get_option( 'admin_email' ) . '>'
        );

        // Enviar en bucle
        foreach ( $subscribers as $subscriber ) {
            wp_mail( $subscriber['email'], $subject, $message, $headers );
        }
    }
}