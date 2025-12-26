<?php

/**
 * Class ADP_Mailer
 *
 * Maneja el envío de correos electrónicos cuando se publica un post.
 */
class ADP_Mailer {

    /**
     * @var ADP_DB Instancia de la base de datos.
     */
    private $db;

    /**
     * Constructor.
     *
     * @param ADP_DB $db Instancia de base de datos.
     */
    public function __construct( $db ) {
        $this->db = $db;
        add_action( 'transition_post_status', array( $this, 'notify_subscribers' ), 10, 3 );
    }

    /**
     * Detecta la publicación de un post y envía correos.
     *
     * @param string $new_status Nuevo estado del post.
     * @param string $old_status Estado anterior del post.
     * @param WP_Post $post Objeto del post.
     */
    public function notify_subscribers( $new_status, $old_status, $post ) {
        // Solo enviar si el post se está publicando por primera vez y es tipo 'post'
        if ( 'publish' !== $new_status || 'publish' === $old_status || 'post' !== $post->post_type ) {
            return;
        }

        $subscribers = $this->db->get_subscribers();
        
        if ( empty( $subscribers ) ) {
            return;
        }

        $subject = 'Nuevo artículo publicado: ' . $post->post_title;
        $message = "Hola,\n\nHemos publicado un nuevo artículo: " . $post->post_title . "\n\nPuedes leerlo aquí: " . get_permalink( $post->ID );
        
        // Headers básicos
        $headers = array( 'Content-Type: text/plain; charset=UTF-8' );

        foreach ( $subscribers as $subscriber ) {
            wp_mail( $subscriber['email'], $subject, $message, $headers );
        }
    }
}