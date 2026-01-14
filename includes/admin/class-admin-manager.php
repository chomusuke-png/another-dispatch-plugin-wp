<?php

/**
 * Class ADP_Admin
 * * Controlador principal del área administrativa.
 * Instancia los submódulos de Assets, Settings, Actions y Ajax.
 */
class ADP_Admin {

    /** @var ADP_DB */
    private $db;

    /**
     * Constructor.
     * @param ADP_DB $db
     */
    public function __construct( $db ) {
        $this->db = $db;

        // Inicializar submódulos
        new ADP_Admin_Assets();
        new ADP_Admin_Settings();
        new ADP_Admin_Actions( $db );
        new ADP_Admin_Ajax();

        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
    }

    /**
     * Registra las páginas del menú.
     */
    public function add_admin_menu() {
        add_menu_page( 'Dispatch', 'Dispatch', 'manage_options', 'another-dispatch-plugin', array( $this, 'render_dashboard_page' ), 'dashicons-email-alt', 6 );
        add_submenu_page( 'another-dispatch-plugin', 'Dashboard', 'Dashboard', 'manage_options', 'another-dispatch-plugin', array( $this, 'render_dashboard_page' ) );
        add_submenu_page( 'another-dispatch-plugin', 'Configuración Global', 'Settings', 'manage_options', 'adp-settings', array( $this, 'render_settings_page' ) );
        add_submenu_page( 'another-dispatch-plugin', 'Personalizar Diseño', 'Mail Customizer', 'manage_options', 'adp-customizer', array( $this, 'render_customizer_page' ) );
        add_submenu_page( 'another-dispatch-plugin', 'Estado del Sistema', 'Debug / Logs', 'manage_options', 'adp-debug', array( $this, 'render_debug_page' ) );
    }

    /**
     * Vista: Dashboard.
     */
    public function render_dashboard_page() {
        $current_filter = isset( $_GET['adp_filter'] ) ? sanitize_text_field( $_GET['adp_filter'] ) : 'all';
        
        // Obtención de datos
        $subscribers   = $this->db->get_subscribers( 0, 0, $current_filter );
        $count_total   = $this->db->get_subscriber_count( 'all' );
        $count_active  = $this->db->get_subscriber_count( 'active' );
        $count_pending = $this->db->get_subscriber_count( 'pending' );

        // Mensajes de Feedback
        $message  = '';
        $msg_type = 'success';

        if ( isset( $_GET['adp_msg'] ) ) {
            $codes = array(
                'deleted'        => 'Suscriptor eliminado.',
                'resent_success' => 'Correo de verificación reenviado correctamente.',
                'test_success'   => 'Correo de prueba SMTP enviado.',
                'test_error'     => array( 'msg' => 'Error SMTP o Action Scheduler no activo.', 'type' => 'error' ),
                'digest_queued'  => 'Resumen mensual en cola.',
                'instant_queued' => 'Envío inmediato en cola.',
                'no_posts'       => array( 'msg' => 'No hay posts recientes.', 'type' => 'warning' ),
                'imported'       => "Importación completada (" . ( isset($_GET['count']) ? intval($_GET['count']) : 0 ) . " registros).",
            );
            
            $code_data = $codes[ $_GET['adp_msg'] ] ?? '';
            
            if ( is_array( $code_data ) ) {
                $message  = $code_data['msg'];
                $msg_type = $code_data['type'];
            } else {
                $message = $code_data;
            }
        }
        
        include ADP_PATH . 'templates/admin/dashboard.php';
    }

    /**
     * Vista: Settings.
     */
    public function render_settings_page() {
        $message = ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) ? 'Configuración guardada correctamente.' : '';
        include ADP_PATH . 'templates/admin/settings.php';
    }

    /**
     * Vista: Customizer.
     */
    public function render_customizer_page() {
        $message = ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) ? 'Diseño actualizado correctamente.' : '';
        include ADP_PATH . 'templates/admin/customizer.php';
    }

    /**
     * Vista: Debug.
     */
    public function render_debug_page() {
        $as_active = function_exists( 'as_get_scheduled_actions' );
        $actions_summary = array(); 
        $recent_actions  = array();

        if ( $as_active ) {
            $statuses = array( 'pending', 'in-progress', 'complete', 'failed', 'canceled' );
            foreach ( $statuses as $status ) {
                $actions = as_get_scheduled_actions( array( 'group' => 'adp_emails', 'status' => $status, 'per_page' => -1 ), 'ids' );
                $actions_summary[ $status ] = count( $actions );
            }
            $recent_actions = as_get_scheduled_actions( array( 'group' => 'adp_emails', 'per_page' => 20, 'orderby' => 'date', 'order' => 'DESC' ) );
        }
        include ADP_PATH . 'templates/admin/debug.php';
    }
}