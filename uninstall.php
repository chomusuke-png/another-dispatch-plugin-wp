<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Zumito\ADP
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit; // Stop execution if we are not uninstalling.
}

// 1. Eliminar la Tabla de Suscriptores
global $wpdb;
$tableName = $wpdb->prefix . 'adp_subscribers';
$wpdb->query("DROP TABLE IF EXISTS {$tableName}");

// 2. Limpiar todas las Opciones Registradas en la Activación/Ajustes
$optionsToDelete = [
    'adp_delivery_frequency',
    'adp_sender_email',
    'adp_batch_size',
    'adp_batch_delay',
    'adp_pending_expiration_days',
    'adp_color_header_bg',
    'adp_color_header_text',
    'adp_body_bg',
    'adp_color_btn_bg',
    'adp_color_btn_text',
    'adp_color_links',
    'adp_logo_url',
    'adp_footer_text',
    'adp_smtp_host',
    'adp_smtp_port',
    'adp_smtp_secure',
    'adp_smtp_user',
    'adp_smtp_pass'
];

foreach ($optionsToDelete as $optionName) {
    delete_option($optionName);
}

// 3. Limpiar todas las Tareas Programadas en la Cola de Action Scheduler
if (function_exists('as_unschedule_all_actions')) {
    as_unschedule_all_actions('', [], 'adp_emails');
}