<?php

/**
 * Class ADP_Admin_Actions
 *
 * Procesa acciones de formulario (POST/GET) como exportación, importación y pruebas.
 */
class ADP_Admin_Actions {

    /** @var ADP_DB */
    private $db;

    /**
     * @param ADP_DB $db
     */
    public function __construct( $db ) {
        $this->db = $db;
        add_action( 'admin_init', array( $this, 'handle_requests' ) );
    }

    /**
     * Router de acciones.
     */
    public function handle_requests() {
        $this->handle_export_csv();
        $this->handle_import_csv();
        $this->handle_delete_subscriber();
        $this->handle_resend_verification();
        $this->handle_smtp_test();
        $this->handle_manual_trigger();
    }

    /**
     * Exportar CSV.
     */
    private function handle_export_csv() {
        if ( ! isset( $_POST['adp_action_export_csv'] ) ) return;
        check_admin_referer( 'adp_export_csv', 'adp_export_nonce' );

        $filename = 'adp-subscribers-' . date( 'Y-m-d' ) . '.csv';
        $data     = $this->db->get_all_subscribers_for_export();

        if ( ob_get_length() ) ob_end_clean();

        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=' . $filename );

        $output = fopen( 'php://output', 'w' );
        fputcsv( $output, array( 'Email', 'Estado', 'Fecha Suscripción' ) );

        foreach ( $data as $row ) {
            fputcsv( $output, $row );
        }

        fclose( $output );
        exit;
    }

    /**
     * Importar CSV.
     */
    private function handle_import_csv() {
        if ( ! isset( $_POST['adp_action_import_csv'] ) ) return;
        check_admin_referer( 'adp_import_csv', 'adp_import_nonce' );

        if ( ! empty( $_FILES['adp_import_file']['tmp_name'] ) ) {
            $handle = fopen( $_FILES['adp_import_file']['tmp_name'], 'r' );
            if ( $handle !== false ) {
                $emails = array();
                while ( ( $data = fgetcsv( $handle, 1000, ',' ) ) !== false ) {
                    if ( isset( $data[0] ) && is_email( $data[0] ) ) {
                        $emails[] = $data[0];
                    }
                }
                fclose( $handle );

                $total = 0;
                foreach ( array_chunk( $emails, 100 ) as $chunk ) {
                    $total += $this->db->bulk_insert( $chunk );
                }

                $this->redirect_with_msg( 'imported', array( 'count' => $total ) );
            }
        }
    }

    /**
     * Eliminar suscriptor.
     */
    private function handle_delete_subscriber() {
        if ( isset( $_GET['action'], $_GET['subscriber_id'] ) && 'adp_delete_subscriber' === $_GET['action'] ) {
            check_admin_referer( 'adp_delete_subscriber_action' );
            
            $this->db->delete_subscriber_by_id( intval( $_GET['subscriber_id'] ) );
            $this->redirect_with_msg( 'deleted' );
        }
    }

    /**
     * Reenviar verificación.
     */
    private function handle_resend_verification() {
        if ( isset( $_GET['action'], $_GET['email'] ) && 'adp_resend_verification' === $_GET['action'] ) {
            check_admin_referer( 'adp_resend_action' );
            
            $email = sanitize_email( $_GET['email'] );
            $sub   = $this->db->get_subscriber( $email );

            if ( $sub && 'pending' === $sub->status ) {
                $new_hash = md5( $email . time() . wp_salt() );
                $this->db->update_subscriber_hash( $email, $new_hash );

                if ( function_exists( 'as_enqueue_async_action' ) ) {
                    as_enqueue_async_action( 'adp_send_verification_email_event', array( 'email' => $email, 'hash' => $new_hash ), 'adp_emails' );
                }
                $this->redirect_with_msg( 'resent_success' );
            }
        }
    }

    /**
     * Test SMTP.
     */
    private function handle_smtp_test() {
        if ( ! isset( $_POST['adp_test_email_submit'] ) ) return;
        check_admin_referer( 'adp_send_test_email', 'adp_test_email_nonce' );

        $to      = wp_get_current_user()->user_email;
        $subject = 'Prueba SMTP ADP';
        $message = '<h1>¡Funciona!</h1><p>Configuración correcta.</p>';
        $from    = get_option( 'adp_sender_email' ) ?: get_option( 'admin_email' );
        $headers = array( 'Content-Type: text/html; charset=UTF-8', 'From: ' . get_bloginfo( 'name' ) . ' <' . $from . '>' );
        
        $sent = wp_mail( $to, $subject, $message, $headers );
        $this->redirect_with_msg( $sent ? 'test_success' : 'test_error' );
    }

    /**
     * Trigger manual de envío.
     */
    private function handle_manual_trigger() {
        if ( ! isset( $_POST['adp_test_content_submit'] ) ) return;
        check_admin_referer( 'adp_test_content_action', 'adp_test_content_nonce' );

        if ( ! function_exists( 'as_schedule_single_action' ) ) {
            $this->redirect_with_msg( 'test_error' );
            return;
        }

        $freq = get_option( 'adp_delivery_frequency', 'instant' );
        $msg  = 'no_posts';

        if ( 'monthly' === $freq ) {
            as_schedule_single_action( time(), 'adp_monthly_digest_event', array( 0 ), 'adp_emails' );
            $msg = 'digest_queued';
        } elseif ( 'weekly' === $freq ) {
            as_schedule_single_action( time(), 'adp_weekly_digest_event', array( 0 ), 'adp_emails' );
            $msg = 'digest_queued';
        } else {
            $latest = get_posts( array( 'numberposts' => 1, 'post_status' => 'publish' ) );
            if ( ! empty( $latest ) ) {
                as_schedule_single_action( time(), 'adp_process_batch_send', array( 'post_id' => $latest[0]->ID, 'offset' => 0 ), 'adp_emails' );
                $msg = 'instant_queued';
            }
        }

        $this->redirect_with_msg( $msg );
    }

    /**
     * Helper de redirección.
     */
    private function redirect_with_msg( $msg_code, $args = array() ) {
        $url_args = array_merge( array( 'page' => 'another-dispatch-plugin', 'adp_msg' => $msg_code ), $args );
        wp_safe_redirect( add_query_arg( $url_args, admin_url( 'admin.php' ) ) );
        exit;
    }
}