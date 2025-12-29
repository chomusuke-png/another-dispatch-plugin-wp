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
     * Programa el evento inicial si el post se está publicando.
     * Se pasa '0' como argumento de offset inicial.
     *
     * @param string $new_status
     * @param string $old_status
     * @param WP_Post $post
     */
    public function schedule_cron( $new_status, $old_status, $post ) {
        if ( 'publish' !== $new_status || 'publish' === $old_status || 'post' !== $post->post_type ) {
            return;
        }

        // Argumentos: PostID, Offset (0)
        $args = array( $post->ID, 0 );

        if ( ! wp_next_scheduled( 'adp_send_notification_cron', $args ) ) {
            wp_schedule_single_event( time(), 'adp_send_notification_cron', $args );
        }
    }
}