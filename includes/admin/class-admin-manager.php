<?php

/**
 * Class ADP_Admin
 *
 * Maneja la interfaz de administración y acciones del backend.
 */
class ADP_Admin {

    private $db;

    public function __construct( $db ) {
        $this->db = $db;
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'process_actions' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) ); // Renombrado para claridad
    }

    /**
     * Carga CSS y JS necesarios (incluyendo Color Picker).
     */
    public function enqueue_assets( $hook ) {
        // Solo cargar en las páginas de nuestro plugin
        if ( strpos( $hook, 'another-dispatch-plugin' ) === false && strpos( $hook, 'adp-customizer' ) === false ) {
            return;
        }

        // CSS General del Admin
        wp_enqueue_style( 'adp-admin-css', ADP_URL . 'assets/css/admin-style.css', array(), '2.2.0' );

        // Cargar Color Picker solo en la página del personalizador ('adp-customizer')
        if ( strpos( $hook, 'adp-customizer' ) !== false ) {
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_script( 'adp-admin-js', ADP_URL . 'assets/js/admin-script.js', array( 'wp-color-picker' ), '1.0.0', true );
        }
    }

    /**
     * Registra el menú.
     */
    public function add_admin_menu() {
        // 1. Menú Principal
        add_menu_page(
            'Dispatch',
            'Dispatch',
            'manage_options',
            'another-dispatch-plugin',
            array( $this, 'render_dashboard_page' ),
            'dashicons-email-alt',
            6
        );

        // 2. Submenú Dashboard (Home)
        add_submenu_page(
            'another-dispatch-plugin',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'another-dispatch-plugin',
            array( $this, 'render_dashboard_page' )
        );

        // 3. Submenú Personalizador (NUEVO)
        add_submenu_page(
            'another-dispatch-plugin',
            'Personalizar Diseño',
            'Mail Customizer',
            'manage_options',
            'adp-customizer',
            array( $this, 'render_customizer_page' )
        );
    }

    /**
     * Registra las opciones del plugin en WordPress.
     */
    public function register_settings() {
        // --- General ---
        register_setting( 'adp_plugin_settings', 'adp_delivery_frequency', 'sanitize_text_field' );
        register_setting( 'adp_plugin_settings', 'adp_sender_email', 'sanitize_email' );
        
        // --- SMTP ---
        register_setting( 'adp_plugin_settings', 'adp_smtp_host', 'sanitize_text_field' );
        register_setting( 'adp_plugin_settings', 'adp_smtp_port', 'absint' );
        register_setting( 'adp_plugin_settings', 'adp_smtp_secure', 'sanitize_text_field' );
        register_setting( 'adp_plugin_settings', 'adp_smtp_user', 'sanitize_text_field' );
        register_setting( 'adp_plugin_settings', 'adp_smtp_pass', 'sanitize_text_field' );

        // --- Customizer Colors (NUEVOS - Faltaban aquí) ---
        register_setting( 'adp_customizer_settings', 'adp_color_header_bg', 'sanitize_hex_color' );
        register_setting( 'adp_customizer_settings', 'adp_color_header_text', 'sanitize_hex_color' );
        register_setting( 'adp_customizer_settings', 'adp_color_btn_bg', 'sanitize_hex_color' );
        register_setting( 'adp_customizer_settings', 'adp_color_btn_text', 'sanitize_hex_color' );
        register_setting( 'adp_customizer_settings', 'adp_color_links', 'sanitize_hex_color' );
    }

    /**
     * Procesa acciones administrativas.
     */
    public function process_actions() {
        // Lógica de borrado
        if ( isset( $_GET['action'], $_GET['subscriber_id'], $_GET['_wpnonce'] ) && 'adp_delete_subscriber' === $_GET['action'] ) {
            if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'adp_delete_subscriber_action' ) ) wp_die( 'Error de seguridad.' );
            
            $this->db->delete_subscriber_by_id( intval( $_GET['subscriber_id'] ) );
            wp_safe_redirect( add_query_arg( array( 'page' => 'another-dispatch-plugin', 'adp_msg' => 'deleted' ), admin_url( 'admin.php' ) ) );
            exit;
        }

        // Lógica SMTP Test
        if ( isset( $_POST['adp_test_email_submit'] ) ) {
            check_admin_referer( 'adp_send_test_email', 'adp_test_email_nonce' );
            
            $to = wp_get_current_user()->user_email;
            $subject = 'Prueba SMTP ADP';
            $message = '<h1>¡Funciona!</h1><p>Configuración correcta.</p>';
            $from = get_option( 'adp_sender_email' ) ?: get_option( 'admin_email' );
            $headers = array( 'Content-Type: text/html; charset=UTF-8', 'From: ' . get_bloginfo('name') . ' <' . $from . '>' );

            $sent = wp_mail( $to, $subject, $message, $headers );
            wp_safe_redirect( add_query_arg( array( 'page' => 'another-dispatch-plugin', 'adp_msg' => $sent ? 'test_success' : 'test_error' ), admin_url( 'admin.php' ) ) );
            exit;
        }

        // Lógica Content Test
        if ( isset( $_POST['adp_test_content_submit'] ) ) {
            check_admin_referer( 'adp_test_content_action', 'adp_test_content_nonce' );
            
            $freq = get_option( 'adp_delivery_frequency', 'instant' );
            $msg_code = 'no_posts';

            if ( 'monthly' === $freq ) {
                wp_schedule_single_event( time(), 'adp_monthly_digest_event', array( 0 ) );
                $msg_code = 'digest_queued';
            } else {
                $latest_posts = get_posts( array( 'numberposts' => 1, 'post_status' => 'publish' ) );
                if ( ! empty( $latest_posts ) ) {
                    wp_schedule_single_event( time(), 'adp_send_notification_cron', array( $latest_posts[0]->ID, 0 ) );
                    $msg_code = 'instant_queued';
                }
            }
            wp_safe_redirect( add_query_arg( array( 'page' => 'another-dispatch-plugin', 'adp_msg' => $msg_code ), admin_url( 'admin.php' ) ) );
            exit;
        }
    }

    /**
     * Renderiza el Dashboard.
     */
    public function render_dashboard_page() {
        $subscribers = $this->db->get_subscribers();
        $count = $this->db->get_subscriber_count();

        $message = '';
        $msg_type = 'success';

        if ( isset( $_GET['adp_msg'] ) ) {
            switch ( $_GET['adp_msg'] ) {
                case 'deleted': $message = 'Suscriptor eliminado.'; break;
                case 'test_success': $message = 'Correo de prueba SMTP enviado.'; break;
                case 'test_error': $message = 'Error SMTP.'; $msg_type = 'error'; break;
                case 'digest_queued': $message = 'Resumen mensual en cola.'; break;
                case 'instant_queued': $message = 'Envío inmediato en cola.'; break;
                case 'no_posts': $message = 'No hay posts.'; $msg_type = 'warning'; break;
            }
        }
        
        if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) {
             $message = 'Configuración guardada correctamente.';
        }

        // CORREGIDO: Ruta actualizada a templates/admin/dashboard.php
        include ADP_PATH . 'templates/admin/dashboard.php';
    }

    /**
     * Renderiza el Personalizador.
     */
    public function render_customizer_page() {
        if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) {
             $message = 'Diseño actualizado correctamente.';
        }
        // CORREGIDO: Ruta actualizada a templates/admin/customizer.php
        include ADP_PATH . 'templates/admin/customizer.php';
    }
}