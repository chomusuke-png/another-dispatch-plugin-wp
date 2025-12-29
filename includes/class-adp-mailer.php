<?php

/**
 * Class ADP_Mailer
 *
 * Maneja el envío de correos electrónicos de forma asíncrona utilizando WP-Cron.
 */
class ADP_Mailer {

    private $db;

    /**
     * Constructor.
     */
    public function __construct( $db ) {
        $this->db = $db;
        // Hook cuando cambia el estado del post
        add_action( 'transition_post_status', array( $this, 'schedule_notification' ), 10, 3 );
        // Hook personalizado para el Cron
        add_action( 'adp_send_notification_cron', array( $this, 'process_notifications_batch' ), 10, 1 );
    }

    /**
     * Programa el evento de envío de correos cuando un post se publica.
     * Evita bloquear la interfaz de usuario durante la publicación.
     */
    public function schedule_notification( $new_status, $old_status, $post ) {
        if ( 'publish' !== $new_status || 'publish' === $old_status || 'post' !== $post->post_type ) {
            return;
        }

        // Programamos un evento único para que ocurra "inmediatamente" (en la próxima visita/cron)
        if ( ! wp_next_scheduled( 'adp_send_notification_cron', array( $post->ID ) ) ) {
            wp_schedule_single_event( time(), 'adp_send_notification_cron', array( $post->ID ) );
        }
    }

    /**
     * Procesa el envío masivo de correos.
     * Ejecutado por WP-Cron en segundo plano.
     */
    public function process_notifications_batch( $post_id ) {
        $subscribers = $this->db->get_subscribers();
        
        if ( empty( $subscribers ) ) {
            return;
        }

        $post = get_post( $post_id );
        if ( ! $post ) {
            return;
        }

        // Preparación de datos
        $blog_name      = get_bloginfo( 'name' );
        $post_title     = $post->post_title;
        $post_link      = get_permalink( $post_id );
        // Usamos solo la función nativa, más segura en contexto asíncrono
        $featured_image = get_the_post_thumbnail_url( $post_id, 'full' );
        
        $post_content   = apply_filters( 'the_content', $post->post_content );

        ob_start();
        include ADP_PATH . 'templates/email-template.php';
        $message = ob_get_clean();

        $subject = 'Nuevo post: ' . $post_title;
        
        $headers = array( 
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $blog_name . ' <' . get_option( 'admin_email' ) . '>'
        );

        // Envío en bucle
        foreach ( $subscribers as $subscriber ) {
            wp_mail( $subscriber['email'], $subject, $message, $headers );
        }

        // Cargar el HTML del correo con el marcador de posición
        ob_start();
        // Pasamos una variable dummy a la plantilla para que no rompa, 
        // pero la sustitución real la haremos abajo.
        $unsubscribe_link = '##UNSUBSCRIBE_URL##'; 
        include ADP_PATH . 'templates/email-template.php';
        $message_template = ob_get_clean();

        $subject = 'Nuevo post: ' . $post_title;
        
        $headers = array( 
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $blog_name . ' <' . get_option( 'admin_email' ) . '>'
        );

        foreach ( $subscribers as $subscriber ) {
            $email = $subscriber['email'];
            
            // Generar hash de seguridad único para este usuario
            $hash = md5( $email . wp_salt() );
            
            // Construir la URL completa
            $link = add_query_arg(
                array(
                    'adp_action' => 'unsubscribe',
                    'email'      => urlencode( $email ),
                    'hash'       => $hash
                ),
                home_url( '/' )
            );

            // Reemplazar el marcador en el mensaje final
            $final_message = str_replace( '##UNSUBSCRIBE_URL##', $link, $message_template );

            wp_mail( $email, $subject, $final_message, $headers );
        }
    }
}