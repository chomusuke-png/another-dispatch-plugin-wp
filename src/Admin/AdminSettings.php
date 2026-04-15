<?php

declare(strict_types=1);

namespace Zumito\ADP\Admin;

class AdminSettings
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'registerPluginSettings']);
    }

    public function registerPluginSettings(): void
    {
        $sanitizeTextFieldArgs = ['sanitize_callback' => 'sanitize_text_field'];
        $sanitizeEmailArgs = ['sanitize_callback' => 'sanitize_email'];
        $sanitizeIntegerArgs = ['sanitize_callback' => 'absint'];
        $sanitizeHexColorArgs = ['sanitize_callback' => 'sanitize_hex_color'];

        register_setting('adp_plugin_settings', 'adp_delivery_frequency', $sanitizeTextFieldArgs);
        register_setting('adp_plugin_settings', 'adp_sender_email', $sanitizeEmailArgs);
        register_setting('adp_plugin_settings', 'adp_batch_size', $sanitizeIntegerArgs);
        register_setting('adp_plugin_settings', 'adp_batch_delay', $sanitizeIntegerArgs);
        register_setting('adp_plugin_settings', 'adp_pending_expiration_days', array_merge($sanitizeIntegerArgs, ['default' => 0]));
        
        register_setting('adp_plugin_settings', 'adp_smtp_host', $sanitizeTextFieldArgs);
        register_setting('adp_plugin_settings', 'adp_smtp_port', $sanitizeIntegerArgs);
        register_setting('adp_plugin_settings', 'adp_smtp_secure', $sanitizeTextFieldArgs);
        register_setting('adp_plugin_settings', 'adp_smtp_user', $sanitizeTextFieldArgs);
        register_setting('adp_plugin_settings', 'adp_smtp_pass', $sanitizeTextFieldArgs);

        register_setting('adp_plugin_settings', 'adp_bounce_email', $sanitizeEmailArgs);
        register_setting('adp_plugin_settings', 'adp_imap_host', $sanitizeTextFieldArgs);
        register_setting('adp_plugin_settings', 'adp_imap_port', $sanitizeIntegerArgs);
        register_setting('adp_plugin_settings', 'adp_imap_user', $sanitizeTextFieldArgs);
        register_setting('adp_plugin_settings', 'adp_imap_pass', $sanitizeTextFieldArgs);

        register_setting('adp_customizer_settings', 'adp_color_header_bg', $sanitizeHexColorArgs);
        register_setting('adp_customizer_settings', 'adp_color_header_text', $sanitizeHexColorArgs);
        register_setting('adp_customizer_settings', 'adp_body_bg', $sanitizeHexColorArgs);
        register_setting('adp_customizer_settings', 'adp_color_btn_bg', $sanitizeHexColorArgs);
        register_setting('adp_customizer_settings', 'adp_color_btn_text', $sanitizeHexColorArgs);
        register_setting('adp_customizer_settings', 'adp_color_links', $sanitizeHexColorArgs);
        
        register_setting('adp_customizer_settings', 'adp_logo_url', ['sanitize_callback' => 'esc_url_raw']);
        register_setting('adp_customizer_settings', 'adp_footer_text', ['sanitize_callback' => 'wp_kses_post']);

        register_setting('adp_customizer_settings', 'adp_welcome_subject', $sanitizeTextFieldArgs);
        register_setting('adp_customizer_settings', 'adp_welcome_content', ['sanitize_callback' => 'wp_kses_post']);
    }
}