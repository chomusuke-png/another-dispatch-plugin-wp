<?php

/**
 * Class ADP_Post_Watcher
 *
 * Detecta nuevos posts y programa el envío usando Action Scheduler.
 */
class ADP_Post_Watcher {

    public function __construct() {
        add_action( 'transition_post_status', array( $this, 'schedule_dispatch' ), 10, 3 );
    }

    /**
     * Programa el trabajo inicial cuando un post se publica.
     */
    public function schedule_dispatch( $new_status, $old_status, $post ) {
        // 1. Validar que sea un post publicándose
        if ( 'publish' !== $new_status || 'publish' === $old_status || 'post' !== $post->post_type ) {
            return;
        }

        // 2. Si es modo mensual, ignorar (lo maneja otro cron)
        if ( 'monthly' === get_option( 'adp_delivery_frequency', 'instant' ) ) {
            return;
        }

        // 3. Usar Action Scheduler para programar el envío
        // Verificamos si la función existe por seguridad (si la librería cargó bien)
        if ( function_exists( 'as_schedule_single_action' ) ) {
            
            $args = array( 'post_id' => $post->ID, 'offset' => 0 );
            $group = 'adp_emails';

            // Evitar duplicados: Solo programar si no hay una acción igual pendiente
            if ( ! as_has_scheduled_action( 'adp_process_batch_send', $args, $group ) ) {
                as_schedule_single_action( time(), 'adp_process_batch_send', $args, $group );
            }
        }
    }
}