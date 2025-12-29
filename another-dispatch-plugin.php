<?php
/**
 * Plugin Name: Another Dispatch Plugin
 * Description: Sistema modular de suscripción y notificación de nuevos posts.
 * Version: 1.0
 * Author: Tu Nombre
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ADP_VERSION', '1.0.0' );
define( 'ADP_PATH', plugin_dir_path( __FILE__ ) );
define( 'ADP_URL', plugin_dir_url( __FILE__ ) );

// Carga de clases
require_once ADP_PATH . 'includes/class-adp-activator.php';
require_once ADP_PATH . 'includes/class-adp-db.php';
require_once ADP_PATH . 'includes/class-adp-public.php';
require_once ADP_PATH . 'includes/class-adp-widget.php';
require_once ADP_PATH . 'includes/class-adp-mailer.php';
require_once ADP_PATH . 'includes/class-adp-admin.php';

/**
 * Función de ejecución al activar el plugin.
 * Crea la tabla de base de datos necesaria.
 */
function adp_activate_plugin() {
    ADP_Activator::activate();
}
register_activation_hook( __FILE__, 'adp_activate_plugin' );

/**
 * Función de limpieza al desactivar.
 */
function adp_deactivate_plugin() {
    wp_clear_scheduled_hook( 'adp_send_notification_cron' );
}
register_deactivation_hook( __FILE__, 'adp_deactivate_plugin' );

/**
 * Inicialización de las clases principales.
 */
function adp_run_plugin() {
    $db = new ADP_DB();

    new ADP_Public( $db );
    new ADP_Mailer( $db );
    new ADP_Admin( $db );

    add_action( 'widgets_init', function() {
        register_widget( 'ADP_Widget' );
    });
}
add_action( 'plugins_loaded', 'adp_run_plugin' );