<?php

/**
 * Class ADP_Unsubscribe_Handler
 *
 * Procesa la solicitud GET para darse de baja.
 */
class ADP_Unsubscribe_Handler {

    /**
     * @var ADP_DB
     */
    private $db;

    /**
     * @param ADP_DB $db
     */
    public function __construct( $db ) {
        $this->db = $db;
        add_action( 'init', array( $this, 'process_request' ) );
    }

    /**
     * Valida hash y elimina suscriptor.
     */
    public function process_request() {
        if ( ! isset( $_GET['adp_action'] ) || 'unsubscribe' !== $_GET['adp_action'] ) {
            return;
        }

        $email = isset( $_GET['email'] ) ? sanitize_email( $_GET['email'] ) : '';
        $hash  = isset( $_GET['hash'] ) ? sanitize_text_field( $_GET['hash'] ) : '';

        if ( ! is_email( $email ) || empty( $hash ) ) {
            wp_die( 'Solicitud incorrecta.' );
        }

        // Verificación de seguridad
        $expected = md5( $email . wp_salt() );
        if ( ! hash_equals( $expected, $hash ) ) {
            wp_die( 'Enlace inválido o expirado.' );
        }

        $this->db->delete_subscriber( $email );

        wp_die( 
            '<h1>Desuscripción exitosa</h1><p>Tu correo ha sido eliminado de nuestra lista.</p><a href="' . home_url() . '">Volver al inicio</a>',
            'Adiós',
            array( 'response' => 200 )
        );
    }
}