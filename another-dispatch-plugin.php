<?php
/**
 * Plugin Name: Another Dispatch Plugin
 * Description: Sistema modular de suscripción.
 * Version: 1.2.11
 * Author: Zumito
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ADP_PATH', plugin_dir_path( __FILE__ ) );
define( 'ADP_URL', plugin_dir_url( __FILE__ ) );

// Carga de dependencias
require_once ADP_PATH . 'includes/admin/class-admin-manager.php';
require_once ADP_PATH . 'includes/core/class-activator.php';
require_once ADP_PATH . 'includes/core/class-db.php';
require_once ADP_PATH . 'includes/emails/class-email-sender.php';
require_once ADP_PATH . 'includes/emails/class-post-watcher.php';
require_once ADP_PATH . 'includes/emails/class-smtp-config.php';
require_once ADP_PATH . 'includes/public/class-shortcode.php';
require_once ADP_PATH . 'includes/public/class-subscribe.php';
require_once ADP_PATH . 'includes/public/class-unsubscribe.php';
require_once ADP_PATH . 'includes/public/class-widget.php';

function adp_add_cron_intervals( $schedules ) {
    $schedules['monthly'] = array(
        'interval' => 2635200, // 30.5 días en segundos aprox
        'display'  => __( 'Una vez al mes' )
    );
    return $schedules;
}
add_filter( 'cron_schedules', 'adp_add_cron_intervals' );

/**
 * Activación y limpieza.
 */
function adp_activate_plugin() {
    ADP_Activator::activate();
    if ( ! wp_next_scheduled( 'adp_monthly_digest_event' ) ) {
        wp_schedule_event( time(), 'monthly', 'adp_monthly_digest_event' );
    }
}
register_activation_hook( __FILE__, 'adp_activate_plugin' );

function adp_deactivate_plugin() {
    wp_clear_scheduled_hook( 'adp_send_notification_cron' );
    wp_clear_scheduled_hook( 'adp_monthly_digest_event' );
}
register_deactivation_hook( __FILE__, 'adp_deactivate_plugin' );

/**
 * Inicialización del sistema.
 * Inyección de dependencias manual.
 */
function adp_run_plugin() {
    // 1. Instancia Base de Datos (Singleton o instancia única compartida)
    $db = new ADP_DB();

    // 2. Inicializar componentes Admin
    if ( is_admin() ) {
        new ADP_Admin( $db );
    }

    // 3. Inicializar componentes Públicos
    new ADP_Shortcode();
    new ADP_Subscribe_Handler( $db );
    new ADP_Unsubscribe_Handler( $db );

    // 4. Inicializar sistema de Emails
    new ADP_Post_Watcher();
    new ADP_Email_Sender( $db );
    new ADP_SMTP();

    // 5. Inicializar Widgets
    add_action( 'widgets_init', function() {
        register_widget( 'ADP_Widget' );
    });
}
add_action( 'plugins_loaded', 'adp_run_plugin' );