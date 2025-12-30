<?php

/**
 * Class ADP_Autoloader
 * * Se encarga de cargar automáticamente las clases del plugin basándose en
 * la estructura de nombres de archivo y carpetas estándar de WordPress.
 *
 * @package ADP\Core
 */
class ADP_Autoloader {

    /**
     * Registra la función de autocompletado con la pila SPL.
     *
     * @return void
     */
    public static function register() {
        spl_autoload_register( array( __CLASS__, 'autoload' ) );
    }

    /**
     * Carga el archivo de clase para un nombre de clase dado.
     *
     * @param string $class_name El nombre completo de la clase.
     * @return void
     */
    public static function autoload( $class_name ) {
        // Verificar si la clase pertenece a nuestro plugin (prefijo ADP_)
        if ( 0 !== strpos( $class_name, 'ADP_' ) ) {
            return;
        }

        // Eliminar el prefijo para mapear a carpetas
        $relative_class = substr( $class_name, 4 );

        // Mapeo básico de nombres de clase a rutas
        // Ejemplo: ADP_Email_Sender -> includes/emails/class-adp-email-sender.php
        
        // Convertir CamelCase a kebab-case para el nombre del archivo
        $file_name = 'class-' . str_replace( '_', '-', strtolower( $relative_class ) ) . '.php';

        // Determinar subdirectorio basado en el nombre (Lógica simple basada en tu estructura actual)
        $directory = '';
        if ( false !== strpos( $class_name, 'Admin' ) ) {
            $directory = 'admin/';
        } elseif ( false !== strpos( $class_name, 'Email' ) || false !== strpos( $class_name, 'SMTP' ) || false !== strpos( $class_name, 'Watcher' ) ) {
            $directory = 'emails/';
        } elseif ( false !== strpos( $class_name, 'Shortcode' ) || false !== strpos( $class_name, 'Subscribe' ) || false !== strpos( $class_name, 'Widget' ) ) {
            $directory = 'public/';
        } else {
            $directory = 'core/';
        }

        $file_path = ADP_PATH . 'includes/' . $directory . $file_name;

        if ( file_exists( $file_path ) ) {
            require_once $file_path;
        }
    }
}