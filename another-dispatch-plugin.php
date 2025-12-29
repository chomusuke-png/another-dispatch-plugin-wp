<?php
/**
 * Plugin Name: Another Dispatch Plugin
 * Description: Sistema modular de suscripción.
 * Version: 2.2
 * Author: Zumito
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ADP_PATH', plugin_dir_path( __FILE__ ) );
define( 'ADP_URL', plugin_dir_url( __FILE__ ) );

// Carga de dependencias
require_once ADP_PATH . 'includes/core/class-adp-activator.php';
require_once ADP_PATH . 'includes/core/class-adp-db.php';
require_once ADP_PATH . 'includes/admin/class-adp-admin.php';
require_once ADP_PATH . 'includes/public/class-adp-shortcode.php';
require_once ADP_PATH . 'includes/public/class-adp-subscribe.php';
require_once ADP_PATH . 'includes/public/class-adp-unsubscribe.php';
require_once ADP_PATH . 'includes/public/class-adp-widget.php';
require_once ADP_PATH . 'includes/emails/class-adp-post-watcher.php';
require_once ADP_PATH . 'includes/emails/class-adp-email-sender.php';

/**
 * Activación y limpieza.
 */
function adp_activate_plugin() {
    ADP_Activator::activate();
}
register_activation_hook( __FILE__, 'adp_activate_plugin' );

function adp_deactivate_plugin() {
    wp_clear_scheduled_hook( 'adp_send_notification_cron' );
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
    new ADP_Post_Watcher();     // El "vigilante"
    new ADP_Email_Sender( $db ); // El "trabajador"

    // 5. Inicializar Widgets
    add_action( 'widgets_init', function() {
        register_widget( 'ADP_Widget' );
    });
}
add_action( 'plugins_loaded', 'adp_run_plugin' );