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
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
    }

    public function enqueue_styles( $hook ) {
        if ( 'toplevel_page_another-dispatch-plugin' !== $hook ) {
            return;
        }
        wp_enqueue_style( 'adp-admin-css', ADP_URL . 'assets/css/admin-style.css', array(), '1.0.0', 'all' );
    }

    public function add_admin_menu() {
        add_menu_page( 'Dispatch', 'Dispatch', 'manage_options', 'another-dispatch-plugin', array( $this, 'render_admin_page' ), 'dashicons-email-alt', 6 );
    }

    public function register_settings() {
        register_setting( 'adp_plugin_settings', 'adp_delivery_frequency', 'sanitize_text_field' );
        register_setting( 'adp_plugin_settings', 'adp_sender_email', 'sanitize_email' );
        register_setting( 'adp_plugin_settings', 'adp_smtp_host', 'sanitize_text_field' );
        register_setting( 'adp_plugin_settings', 'adp_smtp_port', 'absint' );
        register_setting( 'adp_plugin_settings', 'adp_smtp_secure', 'sanitize_text_field' );
        register_setting( 'adp_plugin_settings', 'adp_smtp_user', 'sanitize_text_field' );
        register_setting( 'adp_plugin_settings', 'adp_smtp_pass', 'sanitize_text_field' );
    }

    /**
     * Procesa todas las acciones del admin (Borrar, Test SMTP, Test Contenido).
     */
    public function process_actions() {
        // 1. Borrar Suscriptor
        if ( isset( $_GET['action'], $_GET['subscriber_id'], $_GET['_wpnonce'] ) && 'adp_delete_subscriber' === $_GET['action'] ) {
            if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'adp_delete_subscriber_action' ) ) wp_die( 'Seguridad fallida.' );
            if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Sin permisos.' );

            $this->db->delete_subscriber_by_id( intval( $_GET['subscriber_id'] ) );
            wp_safe_redirect( add_query_arg( array( 'page' => 'another-dispatch-plugin', 'adp_msg' => 'deleted' ), admin_url( 'admin.php' ) ) );
            exit;
        }

        // 2. Test SMTP (Conexión)
        if ( isset( $_POST['adp_test_email_submit'] ) ) {
            check_admin_referer( 'adp_send_test_email', 'adp_test_email_nonce' ); // Manera corta de verificar nonce y permisos

            $to = wp_get_current_user()->user_email;
            $subject = 'Prueba SMTP ADP: ' . date('H:i:s');
            $message = '<h1>Conexión Exitosa</h1><p>Tu configuración SMTP funciona correctamente.</p>';
            
            $from = get_option( 'adp_sender_email' ) ?: get_option( 'admin_email' );
            $headers = array( 'Content-Type: text/html; charset=UTF-8', 'From: ' . get_bloginfo('name') . ' <' . $from . '>' );

            $sent = wp_mail( $to, $subject, $message, $headers );
            
            $redirect_status = $sent ? 'test_success' : 'test_error';
            wp_safe_redirect( add_query_arg( array( 'page' => 'another-dispatch-plugin', 'adp_msg' => $redirect_status ), admin_url( 'admin.php' ) ) );
            exit;
        }

        // 3. Test de Contenido (NUEVO: Disparar envío real)
        if ( isset( $_POST['adp_test_content_submit'] ) ) {
            check_admin_referer( 'adp_test_content_action', 'adp_test_content_nonce' );

            $freq = get_option( 'adp_delivery_frequency', 'instant' );

            if ( 'monthly' === $freq ) {
                // Opción A: Es mensual -> Disparamos el evento Digest (offset 0)
                wp_schedule_single_event( time(), 'adp_monthly_digest_event', array( 0 ) );
                $msg_code = 'digest_queued';
            } else {
                // Opción B: Es inmediato -> Buscamos el último post publicado
                $latest_posts = get_posts( array( 'numberposts' => 1, 'post_status' => 'publish', 'post_type' => 'post' ) );
                
                if ( ! empty( $latest_posts ) ) {
                    $post_id = $latest_posts[0]->ID;
                    // Disparamos el evento Inmediato para ese post
                    wp_schedule_single_event( time(), 'adp_send_notification_cron', array( $post_id, 0 ) );
                    $msg_code = 'instant_queued';
                } else {
                    $msg_code = 'no_posts';
                }
            }

            wp_safe_redirect( add_query_arg( array( 'page' => 'another-dispatch-plugin', 'adp_msg' => $msg_code ), admin_url( 'admin.php' ) ) );
            exit;
        }
    }

    public function render_admin_page() {
        $subscribers = $this->db->get_subscribers();
        $count = $this->db->get_subscriber_count();

        $message = '';
        $msg_type = 'success';

        if ( isset( $_GET['adp_msg'] ) ) {
            switch ( $_GET['adp_msg'] ) {
                case 'deleted': $message = 'Suscriptor eliminado.'; break;
                case 'test_success': $message = 'Correo de prueba SMTP enviado con éxito.'; break;
                case 'test_error': $message = 'Error al enviar prueba SMTP. Revisa credenciales.'; $msg_type = 'error'; break;
                // Nuevos mensajes
                case 'digest_queued': $message = '<strong>¡Proceso Iniciado!</strong> El Resumen Mensual se está enviando en segundo plano.'; break;
                case 'instant_queued': $message = '<strong>¡Proceso Iniciado!</strong> El último post se está enviando a la lista en segundo plano.'; break;
                case 'no_posts': $message = 'No se encontraron posts publicados para probar el envío.'; $msg_type = 'warning'; break;
            }
        }
        
        if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) {
             $message = 'Configuración guardada correctamente.';
        }

        include ADP_PATH . 'templates/admin-page.php';
    }
}