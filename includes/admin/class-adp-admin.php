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
     * Procesa acciones administrativas (como borrar).
     */
    public function process_actions() {
        // Verificar si hay una acción de borrado solicitada
        if ( isset( $_GET['action'], $_GET['subscriber_id'], $_GET['_wpnonce'] ) && 'adp_delete_subscriber' === $_GET['action'] ) {
            
            // 1. Verificar seguridad (Nonce y Permisos)
            if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'adp_delete_subscriber_action' ) ) {
                wp_die( 'Error de seguridad. Inténtalo de nuevo.' );
            }

            if ( ! current_user_can( 'manage_options' ) ) {
                wp_die( 'No tienes permisos para realizar esta acción.' );
            }

            // 2. Ejecutar borrado
            $id = intval( $_GET['subscriber_id'] );
            $this->db->delete_subscriber_by_id( $id );

            // 3. Redirigir para evitar reenvíos y mostrar mensaje
            wp_safe_redirect( add_query_arg( array( 'page' => 'another-dispatch-plugin', 'adp_msg' => 'deleted' ), admin_url( 'admin.php' ) ) );
            exit;
        }
    }

    /**
     * Renderiza la página de administración.
     */
    public function render_admin_page() {
        $subscribers = $this->db->get_subscribers(); // Podrías añadir paginación aquí en el futuro
        $count = $this->db->get_subscriber_count();

        // Verificar mensajes
        $message = '';
        if ( isset( $_GET['adp_msg'] ) && 'deleted' === $_GET['adp_msg'] ) {
            $message = 'Suscriptor eliminado correctamente.';
        }

        include ADP_PATH . 'templates/admin-page.php';
    }
}