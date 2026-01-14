<?php

/**
 * Class ADP_Admin_Settings
 *
 * Registra las configuraciones del plugin en la API de Settings de WP.
 */
class ADP_Admin_Settings {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_init', array( $this, 'register' ) );
    }

    /**
     * Registra los grupos de configuraciones.
     */
    public function register() {
        // --- Grupo 1: Ajustes Generales y SMTP ---
        $general_args = array( 'sanitize_callback' => 'sanitize_text_field' );
        
        register_setting( 'adp_plugin_settings', 'adp_delivery_frequency', $general_args );
        register_setting( 'adp_plugin_settings', 'adp_sender_email', array( 'sanitize_callback' => 'sanitize_email' ) );
        register_setting( 'adp_plugin_settings', 'adp_batch_size', array( 'sanitize_callback' => 'absint' ) );
        register_setting( 'adp_plugin_settings', 'adp_batch_delay', array( 'sanitize_callback' => 'absint' ) );
        
        // SMTP
        register_setting( 'adp_plugin_settings', 'adp_smtp_host', $general_args );
        register_setting( 'adp_plugin_settings', 'adp_smtp_port', array( 'sanitize_callback' => 'absint' ) );
        register_setting( 'adp_plugin_settings', 'adp_smtp_secure', $general_args );
        register_setting( 'adp_plugin_settings', 'adp_smtp_user', $general_args );
        register_setting( 'adp_plugin_settings', 'adp_smtp_pass', $general_args );


        // --- Grupo 2: Customizer (Visual) ---
        // IMPORTANTE: El nombre del grupo debe ser 'adp_customizer_settings' para coincidir con settings_fields()
        $color_args = array( 'sanitize_callback' => 'sanitize_hex_color' );
        
        // Colores (Coincidiendo con email-styles.php)
        register_setting( 'adp_customizer_settings', 'adp_color_header_bg', $color_args );
        register_setting( 'adp_customizer_settings', 'adp_color_header_text', $color_args );
        register_setting( 'adp_customizer_settings', 'adp_body_bg', $color_args );
        register_setting( 'adp_customizer_settings', 'adp_color_btn_bg', $color_args );
        register_setting( 'adp_customizer_settings', 'adp_color_btn_text', $color_args );
        register_setting( 'adp_customizer_settings', 'adp_color_links', $color_args );
        
        // Branding
        register_setting( 'adp_customizer_settings', 'adp_logo_url', array( 'sanitize_callback' => 'esc_url_raw' ) );
        register_setting( 'adp_customizer_settings', 'adp_footer_text', array( 'sanitize_callback' => 'wp_kses_post' ) );
    }
}