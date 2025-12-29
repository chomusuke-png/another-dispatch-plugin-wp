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
     *
     * @param string $new_status
     * @param string $old_status
     * @param WP_Post $post
     */
    public function schedule_cron( $new_status, $old_status, $post ) {
        if ( 'publish' !== $new_status || 'publish' === $old_status || 'post' !== $post->post_type ) {
            return;
        }

        if ( ! wp_next_scheduled( 'adp_send_notification_cron', array( $post->ID ) ) ) {
            wp_schedule_single_event( time(), 'adp_send_notification_cron', array( $post->ID ) );
        }
    }
}