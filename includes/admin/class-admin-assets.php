<?php

/**
 * Class ADP_Admin_Assets
 *
 * Maneja la carga de scripts y estilos en el área de administración.
 */
class ADP_Admin_Assets {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
    }

    /**
     * Encola los assets según la página actual del plugin.
     *
     * @param string $hook Sufijo de la página actual.
     */
    public function enqueue( $hook ) {
        // Solo cargar en páginas del plugin
        if ( strpos( $hook, 'another-dispatch-plugin' ) === false && strpos( $hook, 'adp-' ) === false ) {
            return;
        }

        $version = '2.6.0'; // Incrementamos versión
        $url     = ADP_URL;

        // Estilos Globales
        wp_enqueue_style( 'adp-main-css', $url . 'assets/css/admin/main.css', array(), $version );

        // Carga condicional por módulo
        if ( strpos( $hook, 'adp-customizer' ) !== false ) {
            $this->enqueue_customizer_assets( $url, $version );
        } elseif ( strpos( $hook, 'adp-debug' ) !== false ) {
            $this->enqueue_debug_assets( $url, $version );
        } else {
            $this->enqueue_dashboard_assets( $url, $version );
        }

        // Variables globales JS
        wp_localize_script( 'adp-admin-js', 'adp_vars', array(
            'ajax_url'      => admin_url( 'admin-ajax.php' ),
            'nonce'         => wp_create_nonce( 'adp_debug_refresh_nonce' ),
            'is_debug_page' => ( strpos( $hook, 'adp-debug' ) !== false ) ? '1' : '0'
        ));
    }

    /**
     * Assets para el personalizador.
     */
    private function enqueue_customizer_assets( $url, $version ) {
        // Eliminamos dependencia de wp-color-picker
        wp_enqueue_style( 'adp-customizer-css', $url . 'assets/css/admin/customizer.css', array( 'adp-main-css' ), $version );
        wp_enqueue_media();
        
        // Script del customizer
        wp_enqueue_script( 'adp-customizer-js', $url . 'assets/js/admin-customizer.js', array( 'jquery' ), $version, false );
        
        // Registramos el script genérico también para localizars vars si fuera necesario
        wp_register_script( 'adp-admin-js', '', array(), $version ); 
    }

    /**
     * Assets para debug.
     */
    private function enqueue_debug_assets( $url, $version ) {
        wp_enqueue_style( 'adp-debug-css', $url . 'assets/css/admin/debug.css', array( 'adp-main-css' ), $version );
        wp_enqueue_script( 'adp-admin-js', $url . 'assets/js/admin-script.js', array( 'jquery' ), $version, true );
    }

    /**
     * Assets para el dashboard principal.
     */
    private function enqueue_dashboard_assets( $url, $version ) {
        wp_enqueue_style( 'adp-dashboard-css', $url . 'assets/css/admin/dashboard.css', array( 'adp-main-css' ), $version );
        wp_enqueue_script( 'adp-admin-js', $url . 'assets/js/admin-script.js', array( 'jquery' ), $version, true );
    }
}