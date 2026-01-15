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
        add_action( 'wp_ajax_adp_render_preview', array( $this, 'render_preview' ) );
    }

    /**
     * Renderiza el HTML del template solicitado para el Customizer.
     */
    public function render_preview() {
        check_ajax_referer( 'adp_debug_refresh_nonce', 'nonce' );

        $mode = isset( $_POST['mode'] ) ? sanitize_text_field( $_POST['mode'] ) : 'single';
        
        $blog_name = get_bloginfo( 'name' );
        $is_preview = true;

        ob_start();

        if ( 'digest' === $mode ) {
            $email_title = 'Resumen Semanal';
            $posts_list = array(
                (object) array( 
                    'ID' => 0, 
                    'post_title' => 'Cómo optimizar WordPress en 2026', 
                    'post_excerpt' => 'Descubre las nuevas técnicas de caché...',
                    'mock_link' => '#'
                ),
                (object) array( 
                    'ID' => 0, 
                    'post_title' => 'Tendencias de Diseño Web', 
                    'post_excerpt' => 'El minimalismo extremo vuelve a estar de moda...',
                    'mock_link' => '#'
                ),
            );
            $unsubscribe_link = '#';
            
            include ADP_PATH . 'templates/emails/digest.php';

        } else {
            // Modo Single
            $post_title = 'Bienvenido a nuestro Newsletter';
            $post_content = '<p>Este es un ejemplo de cómo se verán tus correos.</p><p>Puedes personalizar los colores, el logo y el pie de página desde el panel de la izquierda. Los cambios se reflejan en tiempo real.</p>';
            $post_link = '#';
            $featured_image = '';
            $unsubscribe_link = '#';

            include ADP_PATH . 'templates/emails/single.php';
        }

        $html = ob_get_clean();
        wp_send_json_success( array( 'html' => $html ) );
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