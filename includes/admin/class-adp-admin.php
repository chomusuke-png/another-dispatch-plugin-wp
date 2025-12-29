<?php

/**
 * Class ADP_Admin
 *
 * Maneja la interfaz de administración y acciones del backend.
 */
class ADP_Admin {

    /**
     * @var ADP_DB
     */
    private $db;

    /**
     * @param ADP_DB $db
     */
    public function __construct( $db ) {
        $this->db = $db;
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'process_actions' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    /**
     * Registra el menú.
     */
    public function add_admin_menu() {
        add_menu_page(
            'Suscriptores ADP',
            'Suscriptores ADP',
            'manage_options',
            'another-dispatch-plugin',
            array( $this, 'render_admin_page' ),
            'dashicons-email-alt',
            6
        );
    }

    /**
     * Registra las opciones del plugin en WordPress.
     */
    public function register_settings() {
        register_setting( 'adp_plugin_settings', 'adp_sender_email', 'sanitize_email' );
        register_setting( 'adp_plugin_settings', 'adp_smtp_host', 'sanitize_text_field' );
        register_setting( 'adp_plugin_settings', 'adp_smtp_port', 'absint' );
        register_setting( 'adp_plugin_settings', 'adp_smtp_secure', 'sanitize_text_field' );
        register_setting( 'adp_plugin_settings', 'adp_smtp_user', 'sanitize_text_field' );
        register_setting( 'adp_plugin_settings', 'adp_smtp_pass', 'sanitize_text_field' );
    }

    /**
     * Procesa acciones administrativas (borrar y enviar test).
     */
    public function process_actions() {
        // 1. Lógica de borrado (existente)
        if ( isset( $_GET['action'], $_GET['subscriber_id'], $_GET['_wpnonce'] ) && 'adp_delete_subscriber' === $_GET['action'] ) {
            if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'adp_delete_subscriber_action' ) ) wp_die( 'Error de seguridad.' );
            if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Permisos insuficientes.' );

            $id = intval( $_GET['subscriber_id'] );
            $this->db->delete_subscriber_by_id( $id );

            wp_safe_redirect( add_query_arg( array( 'page' => 'another-dispatch-plugin', 'adp_msg' => 'deleted' ), admin_url( 'admin.php' ) ) );
            exit;
        }

        // 2. Lógica de Email de Prueba (NUEVO)
        if ( isset( $_POST['adp_test_email_submit'] ) ) {
            if ( ! isset( $_POST['adp_test_email_nonce'] ) || ! wp_verify_nonce( $_POST['adp_test_email_nonce'], 'adp_send_test_email' ) ) {
                wp_die( 'Error de seguridad.' );
            }
            
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_die( 'Permisos insuficientes.' );
            }

            // Configurar el envío
            $to = wp_get_current_user()->user_email;
            $subject = 'Prueba de configuración ADP: ' . date('Y-m-d H:i:s');
            $message = '<h1>¡Funciona!</h1><p>Si estás leyendo esto, tu configuración SMTP en Another Dispatch Plugin es correcta.</p>';
            
            // Usar headers idénticos a los del envío real para validar "From"
            $from_email = get_option( 'adp_sender_email' );
            if ( empty( $from_email ) || ! is_email( $from_email ) ) {
                $from_email = get_option( 'admin_email' );
            }
            $blog_name = get_bloginfo( 'name' );
            
            $headers = array( 
                'Content-Type: text/html; charset=UTF-8',
                'From: ' . $blog_name . ' <' . $from_email . '>'
            );

            // Intentar enviar
            $sent = wp_mail( $to, $subject, $message, $headers );

            if ( $sent ) {
                wp_safe_redirect( add_query_arg( array( 'page' => 'another-dispatch-plugin', 'adp_msg' => 'test_success' ), admin_url( 'admin.php' ) ) );
            } else {
                wp_safe_redirect( add_query_arg( array( 'page' => 'another-dispatch-plugin', 'adp_msg' => 'test_error' ), admin_url( 'admin.php' ) ) );
            }
            exit;
        }
    }

    /**
     * Renderiza la página de administración.
     */
    public function render_admin_page() {
        $subscribers = $this->db->get_subscribers();
        $count = $this->db->get_subscriber_count();

        // Gestionar mensajes de feedback
        $message = '';
        $msg_type = 'success'; // por defecto verde

        if ( isset( $_GET['adp_msg'] ) ) {
            switch ( $_GET['adp_msg'] ) {
                case 'deleted':
                    $message = 'Suscriptor eliminado correctamente.';
                    break;
                case 'test_success':
                    $message = '<strong>¡Éxito!</strong> El correo de prueba se ha enviado correctamente a ' . esc_html( wp_get_current_user()->user_email );
                    break;
                case 'test_error':
                    $message = '<strong>Error:</strong> No se pudo enviar el correo. Revisa tus credenciales SMTP.';
                    $msg_type = 'error'; // rojo
                    break;
            }
        }
        
        if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) {
             $message = 'Configuración guardada correctamente.';
        }

        include ADP_PATH . 'templates/admin-page.php';
    }
}