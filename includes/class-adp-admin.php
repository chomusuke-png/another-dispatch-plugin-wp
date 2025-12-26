<?php

/**
 * Class ADP_Admin
 *
 * Maneja la interfaz de administración del plugin en el backend de WordPress.
 */
class ADP_Admin {

    /**
     * @var ADP_DB Instancia de la base de datos.
     */
    private $db;

    /**
     * Constructor.
     *
     * @param ADP_DB $db Instancia de base de datos.
     */
    public function __construct( $db ) {
        $this->db = $db;
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
    }

    /**
     * Registra el menú en el panel de administración.
     */
    public function add_admin_menu() {
        add_menu_page(
            'Suscriptores ADP',         // Título de la página
            'Suscriptores ADP',         // Título del menú
            'manage_options',           // Capacidad requerida
            'another-dispatch-plugin',  // Slug del menú
            array( $this, 'render_admin_page' ), // Callback
            'dashicons-email-alt',      // Icono
            6                           // Posición
        );
    }

    /**
     * Obtiene los datos y renderiza la plantilla de la página de administración.
     */
    public function render_admin_page() {
        // Obtener datos de la DB
        $subscribers = $this->db->get_subscribers();
        $count = $this->db->get_subscriber_count();

        // Cargar vista
        include ADP_PATH . 'templates/admin-page.php';
    }
}