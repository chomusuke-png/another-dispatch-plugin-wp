<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) { exit; }

global $wpdb;
$table_name = $wpdb->prefix . 'adp_subscribers';
$wpdb->query( "DROP TABLE IF EXISTS $table_name" );

wp_clear_scheduled_hook( 'adp_send_notification_cron' );
wp_clear_scheduled_hook( 'adp_monthly_digest_event' );
wp_clear_scheduled_hook( 'adp_daily_cleanup_event' );

if ( function_exists( 'as_unschedule_all_actions' ) ) {
    as_unschedule_all_actions( '', array(), 'adp_emails' );
}