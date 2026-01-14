<?php

/**
 * Class ADP_Admin_Ajax
 *
 * Maneja las solicitudes AJAX del panel de administración.
 */
class ADP_Admin_Ajax {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'wp_ajax_adp_refresh_debug_stats', array( $this, 'refresh_debug_stats' ) );
    }

    /**
     * Refresca la tabla de debug y los contadores.
     */
    public function refresh_debug_stats() {
        check_ajax_referer( 'adp_debug_refresh_nonce', 'nonce' );

        if ( ! function_exists( 'as_get_scheduled_actions' ) ) {
            wp_send_json_error( 'Action Scheduler no activo' );
        }

        // Obtener Contadores
        $statuses = array( 'pending', 'in-progress', 'complete', 'failed' );
        $stats    = array();
        
        foreach ( $statuses as $status ) {
            $actions = as_get_scheduled_actions( array( 'group' => 'adp_emails', 'status' => $status, 'per_page' => -1 ), 'ids' );
            $stats[ $status ] = count( $actions );
        }

        // Generar HTML
        $recent_actions = as_get_scheduled_actions( array( 'group' => 'adp_emails', 'per_page' => 20, 'orderby' => 'date', 'order' => 'DESC' ) );
        
        ob_start();
        $this->render_table_rows( $recent_actions );
        $table_html = ob_get_clean();

        wp_send_json_success( array(
            'stats'      => $stats,
            'table_html' => $table_html
        ));
    }

    /**
     * Renderiza las filas de la tabla de acciones.
     * @param array $actions
     */
    private function render_table_rows( $actions ) {
        if ( empty( $actions ) ) {
            echo '<tr><td colspan="5">No hay actividad reciente.</td></tr>';
            return;
        }

        foreach ( $actions as $action_id => $action ) {
            $hook         = $action->get_hook();
            $args         = $action->get_args();
            $schedule     = $action->get_schedule();
            $next_run     = $schedule->get_date();
            $store        = ActionScheduler::store();
            $status_label = $store->get_status( $action_id );
            
            $status_color = '#72aee6';
            if ( 'complete' === $status_label ) $status_color = '#00a32a';
            if ( 'failed' === $status_label ) $status_color = '#d63638';
            if ( 'pending' === $status_label ) $status_color = '#dba617';
            
            echo '<tr>';
            echo '<td><strong>' . esc_html( $hook ) . '</strong><br><small>ID: ' . intval( $action_id ) . '</small></td>';
            echo '<td>' . ( ! empty( $args ) ? '<pre style="margin:0; font-size:10px;">' . print_r( $args, true ) . '</pre>' : '-' ) . '</td>';
            echo '<td><span style="font-weight:bold; color:' . $status_color . '; text-transform:uppercase;">' . esc_html( $status_label ) . '</span></td>';
            echo '<td>' . ( $next_run ? $next_run->format( 'Y-m-d H:i:s' ) : '-' ) . '</td>';
            echo '<td>' . ( 'failed' === $status_label ? '<a href="' . admin_url( 'tools.php?page=action-scheduler&s=' . $action_id ) . '" target="_blank">Ver error</a>' : '-' ) . '</td>';
            echo '</tr>';
        }
    }
}