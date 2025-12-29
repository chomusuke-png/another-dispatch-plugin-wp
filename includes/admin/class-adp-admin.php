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
        // Nuevo: Registrar configuraciones
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
        // Configuración de remitente
        register_setting( 'adp_plugin_settings', 'adp_sender_email', 'sanitize_email' );

        // Configuración SMTP
        register_setting( 'adp_plugin_settings', 'adp_smtp_host', 'sanitize_text_field' );
        register_setting( 'adp_plugin_settings', 'adp_smtp_port', 'absint' );
        register_setting( 'adp_plugin_settings', 'adp_smtp_secure', 'sanitize_text_field' );
        register_setting( 'adp_plugin_settings', 'adp_smtp_user', 'sanitize_text_field' );
        // Nota: Las contraseñas deberían sanitizarse con cuidado para no romper caracteres especiales
        register_setting( 'adp_plugin_settings', 'adp_smtp_pass', 'sanitize_text_field' );
    }

    /**
     * Procesa acciones administrativas (como borrar).
     */
    public function process_actions() {
        if ( isset( $_GET['action'], $_GET['subscriber_id'], $_GET['_wpnonce'] ) && 'adp_delete_subscriber' === $_GET['action'] ) {
            
            if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'adp_delete_subscriber_action' ) ) {
                wp_die( 'Error de seguridad. Inténtalo de nuevo.' );
            }

            if ( ! current_user_can( 'manage_options' ) ) {
                wp_die( 'No tienes permisos para realizar esta acción.' );
            }

            $id = intval( $_GET['subscriber_id'] );
            $this->db->delete_subscriber_by_id( $id );

            wp_safe_redirect( add_query_arg( array( 'page' => 'another-dispatch-plugin', 'adp_msg' => 'deleted' ), admin_url( 'admin.php' ) ) );
            exit;
        }
    }

    /**
     * Renderiza la página de administración.
     */
    public function render_admin_page() {
        $subscribers = $this->db->get_subscribers();
        $count = $this->db->get_subscriber_count();

        $message = '';
        if ( isset( $_GET['adp_msg'] ) && 'deleted' === $_GET['adp_msg'] ) {
            $message = 'Suscriptor eliminado correctamente.';
        }
        // Capturar mensaje de settings guardados
        if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) {
             $message = 'Configuración guardada correctamente.';
        }

        include ADP_PATH . 'templates/admin-page.php';
    }
}