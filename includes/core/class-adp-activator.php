<?php

/**
 * Class ADP_Activator
 *
 * Maneja las tareas que deben ejecutarse durante la activación del plugin.
 */
class ADP_Activator {

    /**
     * Crea la tabla de base de datos para los suscriptores.
     * Utiliza dbDelta para asegurar compatibilidad y actualizaciones.
     */
    public static function activate() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'adp_subscribers';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            email varchar(100) NOT NULL,
            created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY email (email)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
}