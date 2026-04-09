<?php

class ADP_Admin_Settings {

    public function __construct() {
        add_action( 'admin_init', array( $this, 'register' ) );
    }

    public function register() {
        $general_args = array( 'sanitize_callback' => 'sanitize_text_field' );
        
        register_setting( 'adp_plugin_settings', 'adp_delivery_frequency', $general_args );
        register_setting( 'adp_plugin_settings', 'adp_sender_email', array( 'sanitize_callback' => 'sanitize_email' ) );
        register_setting( 'adp_plugin_settings', 'adp_batch_size', array( 'sanitize_callback' => 'absint' ) );
        register_setting( 'adp_plugin_settings', 'adp_batch_delay', array( 'sanitize_callback' => 'absint' ) );
        register_setting( 'adp_plugin_settings', 'adp_pending_expiration_days', array( 'sanitize_callback' => 'absint', 'default' => 0 ) );
        
        register_setting( 'adp_plugin_settings', 'adp_smtp_host', $general_args );
        register_setting( 'adp_plugin_settings', 'adp_smtp_port', array( 'sanitize_callback' => 'absint' ) );
        register_setting( 'adp_plugin_settings', 'adp_smtp_secure', $general_args );
        register_setting( 'adp_plugin_settings', 'adp_smtp_user', $general_args );
        register_setting( 'adp_plugin_settings', 'adp_smtp_pass', $general_args );

        $color_args = array( 'sanitize_callback' => 'sanitize_hex_color' );
        
        register_setting( 'adp_customizer_settings', 'adp_color_header_bg', $color_args );
        register_setting( 'adp_customizer_settings', 'adp_color_header_text', $color_args );
        register_setting( 'adp_customizer_settings', 'adp_body_bg', $color_args );
        register_setting( 'adp_customizer_settings', 'adp_color_btn_bg', $color_args );
        register_setting( 'adp_customizer_settings', 'adp_color_btn_text', $color_args );
        register_setting( 'adp_customizer_settings', 'adp_color_links', $color_args );
        
        register_setting( 'adp_customizer_settings', 'adp_logo_url', array( 'sanitize_callback' => 'esc_url_raw' ) );
        register_setting( 'adp_customizer_settings', 'adp_footer_text', array( 'sanitize_callback' => 'wp_kses_post' ) );
    }
}