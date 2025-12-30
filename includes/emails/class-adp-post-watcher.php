<?php

/**
 * Class ADP_Post_Watcher
 *
 * Detecta transiciones de estado en los posts y programa el Cron.
 */
class ADP_Post_Watcher {

    public function __construct() {
        add_action( 'transition_post_status', array( $this, 'schedule_cron' ), 10, 3 );
    }

    /**
     * Programa el evento único si el post se está publicando.
     */
    public function schedule_cron( $new_status, $old_status, $post ) {
        // Log para depuración
        error_log( "ADP Debug: Cambio de estado detectado. Nuevo: $new_status, Viejo: $old_status, Tipo: {$post->post_type}, ID: {$post->ID}" );

        if ( 'publish' !== $new_status || 'publish' === $old_status || 'post' !== $post->post_type ) {
            return;
        }

        // Verificar la configuración de frecuencia
        $frequency = get_option( 'adp_delivery_frequency', 'instant' );
        
        if ( 'monthly' === $frequency ) {
            error_log( "ADP Debug: Post {$post->ID} publicado, pero el modo es 'Mensual'. Se omitió el envío inmediato." );
            return;
        }

        // Argumentos: PostID, Offset (0)
        $args = array( $post->ID, 0 );

        if ( ! wp_next_scheduled( 'adp_send_notification_cron', $args ) ) {
            $result = wp_schedule_single_event( time(), 'adp_send_notification_cron', $args );
            
            if ( $result ) {
                error_log( "ADP Debug: CRON programado con éxito para el post {$post->ID}" );
            } else {
                error_log( "ADP Error: Falló al programar el CRON para el post {$post->ID}" );
            }
        } else {
            error_log( "ADP Debug: El CRON ya estaba programado para este post." );
        }
    }
}