<?php

/**
 * Class ADP_Public
 *
 * Maneja la interfaz pública: Shortcodes y procesamiento de formularios.
 */
class ADP_Public {

    /**
     * @var ADP_DB Instancia de la base de datos.
     */
    private $db;

    /**
     * Constructor.
     *
     * @param ADP_DB $db Inyección de dependencia de la base de datos.
     */
    public function __construct( $db ) {
        $this->db = $db;
        add_shortcode( 'adp_subscribe', array( $this, 'render_shortcode' ) );
        add_action( 'init', array( $this, 'process_subscription' ) );
    }

    /**
     * Renderiza el formulario de suscripción mediante Shortcode.
     *
     * @return string HTML del formulario.
     */
    public function render_shortcode() {
        ob_start();
        include ADP_PATH . 'templates/subscription-form.php';
        return ob_get_clean();
    }

    /**
     * Procesa el envío del formulario de suscripción.
     * Verifica nonce y guarda el correo.
     */
    public function process_subscription() {
        if ( ! isset( $_POST['adp_subscribe_submit'] ) ) {
            return;
        }

        if ( ! isset( $_POST['adp_nonce'] ) || ! wp_verify_nonce( $_POST['adp_nonce'], 'adp_save_sub' ) ) {
            return;
        }

        $email = sanitize_email( $_POST['adp_email'] );

        if ( is_email( $email ) && ! $this->db->exists( $email ) ) {
            $this->db->add_subscriber( $email );
            // Aquí se podría agregar una redirección o mensaje de éxito en sesión
        }
    }
}