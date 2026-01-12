<?php

/**
 * Class ADP_Activator
 *
 * Maneja las tareas de activación y estructura de base de datos.
 */
class ADP_Activator {

    /**
     * Crea o actualiza la tabla de suscriptores con nuevos campos de seguridad.
     */
    public static function activate() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'adp_subscribers';
        $charset_collate = $wpdb->get_charset_collate();

        // Añadimos 'status' y 'activation_hash'
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            email varchar(100) NOT NULL,
            status varchar(20) DEFAULT 'pending' NOT NULL,
            activation_hash varchar(64) DEFAULT '' NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY email (email)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
}