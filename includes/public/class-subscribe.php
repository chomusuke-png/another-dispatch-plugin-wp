<?php

/**
 * Class ADP_Subscribe_Handler
 *
 * Procesa el envío del formulario POST.
 */
class ADP_Subscribe_Handler {

    /**
     * @var ADP_DB
     */
    private $db;

    /**
     * @param ADP_DB $db
     */
    public function __construct( $db ) {
        $this->db = $db;
        add_action( 'init', array( $this, 'process_form' ) );
    }

    /**
     * Verifica nonce y guarda datos.
     */
    public function process_form() {
        if ( ! isset( $_POST['adp_subscribe_submit'] ) ) {
            return;
        }

        if ( ! isset( $_POST['adp_nonce'] ) || ! wp_verify_nonce( $_POST['adp_nonce'], 'adp_save_sub' ) ) {
            return;
        }

        $email = sanitize_email( $_POST['adp_email'] );
        $redirect_url = remove_query_arg( 'adp_status' );

        if ( ! is_email( $email ) ) return;

        if ( $this->db->exists( $email ) ) {
            wp_safe_redirect( add_query_arg( 'adp_status', 'exists', $redirect_url ) );
            exit;
        }

        $saved = $this->db->add_subscriber( $email );
        if ( $saved ) {
            wp_safe_redirect( add_query_arg( 'adp_status', 'success', $redirect_url ) );
            exit;
        }
    }
}