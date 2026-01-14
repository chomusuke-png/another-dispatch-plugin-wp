<?php
/**
 * Plugin Name: Another Dispatch Plugin
 * Description: Sistema modular de suscripción.
 * Version: 1.4.1
 * Author: Zumito
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ADP_PATH', plugin_dir_path( __FILE__ ) );
define( 'ADP_URL', plugin_dir_url( __FILE__ ) );

require_once ADP_PATH . 'vendor/autoload.php';

$as_file = ADP_PATH . 'vendor/woocommerce/action-scheduler/action-scheduler.php';
if ( file_exists( $as_file ) ) {
    require_once $as_file;
}

function adp_add_cron_intervals( $schedules ) {
    $schedules['monthly'] = array(
        'interval' => 2635200,
        'display'  => __( 'Una vez al mes' )
    );
    $schedules['weekly'] = array(
        'interval' => 604800,
        'display'  => __( 'Una vez a la semana' )
    );
    return $schedules;
}
add_filter( 'cron_schedules', 'adp_add_cron_intervals' );

/**
 * Activación y limpieza.
 */
function adp_activate_plugin() {
    ADP_Activator::activate();
    // Limpieza preventiva
    wp_clear_scheduled_hook( 'adp_send_notification_cron' );
}
register_activation_hook( __FILE__, 'adp_activate_plugin' );

function adp_deactivate_plugin() {
    wp_clear_scheduled_hook( 'adp_monthly_digest_event' );
}
register_deactivation_hook( __FILE__, 'adp_deactivate_plugin' );

/**
 * Inicialización del sistema.
 */
function adp_run_plugin() {
    $db = new ADP_DB();

    if ( is_admin() ) {
        new ADP_Admin( $db );
    }

    new ADP_Shortcode();
    new ADP_Subscribe_Handler( $db );
    new ADP_Unsubscribe_Handler( $db );

    new ADP_Post_Watcher();
    new ADP_Email_Sender( $db );
    new ADP_SMTP();

    add_action( 'widgets_init', function() {
        register_widget( 'ADP_Widget' );
    });
}
add_action( 'plugins_loaded', 'adp_run_plugin' );