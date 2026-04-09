<?php

class ADP_Cleanup {

    private ADP_DB $db;

    public function __construct( ADP_DB $db ) {
        $this->db = $db;

        add_action( 'init', array( $this, 'schedule_daily_cleanup' ) );
        add_action( 'adp_daily_cleanup_event', array( $this, 'execute_pending_subscribers_cleanup' ) );
    }

    public function schedule_daily_cleanup(): void {
        if ( ! wp_next_scheduled( 'adp_daily_cleanup_event' ) ) {
            wp_schedule_event( time(), 'daily', 'adp_daily_cleanup_event' );
        }
    }

    public function execute_pending_subscribers_cleanup(): void {
        $expiration_days = (int) get_option( 'adp_pending_expiration_days', 0 );

        if ( $expiration_days > 0 ) {
            $this->db->delete_expired_pending_subscribers( $expiration_days );
        }
    }
}