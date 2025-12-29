<?php

/**
 * Disparado cuando el plugin se desinstala.
 *
 * @package    Another_Dispatch_Plugin
 */

// Si no se llama desde WordPress, salir.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

// 1. Borrar la tabla de la base de datos
$table_name = $wpdb->prefix . 'adp_subscribers';
$wpdb->query( "DROP TABLE IF EXISTS $table_name" );

// 2. Limpiar opciones (si guardaras alguna en wp_options)
// delete_option( 'adp_settings' );

// 3. Limpiar Cron Jobs programados
wp_clear_scheduled_hook( 'adp_send_notification_cron' );