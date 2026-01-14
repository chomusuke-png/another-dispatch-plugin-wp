<?php

/**
 * Class ADP_Admin
 */
class ADP_Admin {

    private $db;

    public function __construct( $db ) {
        $this->db = $db;
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'process_actions' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_ajax_adp_refresh_debug_stats', array( $this, 'ajax_refresh_debug_stats' ) );
    }

    public function enqueue_assets( $hook ) {
        // 1. Verificación rápida: Si no es nuestra página, salir.
        // Nota: Los hooks de submenu suelen tener el formato "toplevel_page_slug" o "dispatch_page_slug"
        if ( strpos( $hook, 'another-dispatch-plugin' ) === false && 
             strpos( $hook, 'adp-' ) === false ) {
            return;
        }

        $version = '2.4.0'; // Incrementa versión para romper caché

        // 2. Estilos Globales (Main) - Se carga en todas las pantallas del plugin
        wp_enqueue_style( 'adp-main-css', ADP_URL . 'assets/css/admin/main.css', array(), $version );

        // 3. Carga Condicional Modular (CSS Específico)
        if ( strpos( $hook, 'adp-customizer' ) !== false ) {
            
            // Módulo Customizer
            wp_enqueue_style( 'adp-customizer-css', ADP_URL . 'assets/css/admin/customizer.css', array( 'adp-main-css', 'wp-color-picker' ), $version );
            
            // Dependencias de JS específicas
            wp_enqueue_media();
            wp_enqueue_style( 'wp-color-picker' );
            
            wp_enqueue_script( 'adp-customizer-js', ADP_URL . 'assets/js/admin-customizer.js', array( 'jquery', 'wp-color-picker' ), $version, false );

        } elseif ( strpos( $hook, 'adp-debug' ) !== false ) {
            
            // Módulo Debug
            wp_enqueue_style( 'adp-debug-css', ADP_URL . 'assets/css/admin/debug.css', array( 'adp-main-css' ), $version );
            wp_enqueue_script( 'adp-admin-js', ADP_URL . 'assets/js/admin-script.js', array( 'jquery' ), $version, true );

        } else {
            
            // Módulo Dashboard (Por defecto en la home del plugin)
            wp_enqueue_style( 'adp-dashboard-css', ADP_URL . 'assets/css/admin/dashboard.css', array( 'adp-main-css' ), $version );
            wp_enqueue_script( 'adp-admin-js', ADP_URL . 'assets/js/admin-script.js', array( 'jquery' ), $version, true );
        }

        // 4. Variables JS Globales
        wp_localize_script( 'adp-admin-js', 'adp_vars', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'adp_debug_refresh_nonce' ),
            'is_debug_page' => ( strpos( $hook, 'adp-debug' ) !== false ) ? '1' : '0'
        ));
    }

    /**
     * Endpoint AJAX para refrescar datos de debug.
     */
    public function ajax_refresh_debug_stats() {
        check_ajax_referer( 'adp_debug_refresh_nonce', 'nonce' );

        if ( ! function_exists( 'as_get_scheduled_actions' ) ) {
            wp_send_json_error( 'Action Scheduler no activo' );
        }

        // 1. Obtener Contadores
        $statuses = array( 'pending', 'in-progress', 'complete', 'failed' );
        $stats = array();
        foreach ( $statuses as $status ) {
            $actions = as_get_scheduled_actions( array( 'group' => 'adp_emails', 'status' => $status, 'per_page' => -1 ), 'ids' );
            $stats[ $status ] = count( $actions );
        }

        // 2. Obtener HTML de la tabla
        $recent_actions = as_get_scheduled_actions( array( 'group' => 'adp_emails', 'per_page' => 20, 'orderby' => 'date', 'order' => 'DESC' ) );
        
        ob_start();
        if ( empty( $recent_actions ) ) {
            echo '<tr><td colspan="5">No hay actividad reciente.</td></tr>';
        } else {
            foreach ( $recent_actions as $action_id => $action ) {
                $hook = $action->get_hook();
                $args = $action->get_args();
                $schedule = $action->get_schedule();
                $next_run = $schedule->get_date();
                $store = ActionScheduler::store();
                $status_label = $store->get_status( $action_id );
                
                $status_color = '#72aee6';
                if ( 'complete' === $status_label ) $status_color = '#00a32a';
                if ( 'failed' === $status_label ) $status_color = '#d63638';
                if ( 'pending' === $status_label ) $status_color = '#dba617';
                
                echo '<tr>';
                echo '<td><strong>' . esc_html( $hook ) . '</strong><br><small>ID: ' . intval( $action_id ) . '</small></td>';
                echo '<td>';
                if ( ! empty( $args ) ) echo '<pre style="margin:0; font-size:10px;">' . print_r( $args, true ) . '</pre>';
                else echo '-';
                echo '</td>';
                echo '<td><span style="font-weight:bold; color:' . $status_color . '; text-transform:uppercase;">' . esc_html( $status_label ) . '</span></td>';
                echo '<td>' . ( $next_run ? $next_run->format( 'Y-m-d H:i:s' ) : '-' ) . '</td>';
                echo '<td>' . ( 'failed' === $status_label ? '<a href="' . admin_url( 'tools.php?page=action-scheduler&s=' . $action_id ) . '" target="_blank">Ver error</a>' : '-' ) . '</td>';
                echo '</tr>';
            }
        }
        $table_html = ob_get_clean();

        wp_send_json_success( array(
            'stats' => $stats,
            'table_html' => $table_html
        ));
    }

    public function add_admin_menu() {
        add_menu_page( 'Dispatch', 'Dispatch', 'manage_options', 'another-dispatch-plugin', array( $this, 'render_dashboard_page' ), 'dashicons-email-alt', 6 );
        add_submenu_page( 'another-dispatch-plugin', 'Dashboard', 'Dashboard', 'manage_options', 'another-dispatch-plugin', array( $this, 'render_dashboard_page' ) );
        add_submenu_page( 'another-dispatch-plugin', 'Configuración Global', 'Settings', 'manage_options', 'adp-settings', array( $this, 'render_settings_page' ) );
        add_submenu_page( 'another-dispatch-plugin', 'Personalizar Diseño', 'Mail Customizer', 'manage_options', 'adp-customizer', array( $this, 'render_customizer_page' ) );
        add_submenu_page( 'another-dispatch-plugin', 'Estado del Sistema', 'Debug / Logs', 'manage_options', 'adp-debug', array( $this, 'render_debug_page' ) );
    }

    public function register_settings() {
        // Ajustes Generales
        register_setting( 'adp_plugin_settings', 'adp_delivery_frequency', 'sanitize_text_field' );
        register_setting( 'adp_plugin_settings', 'adp_sender_email', 'sanitize_email' );
        register_setting( 'adp_plugin_settings', 'adp_batch_size', 'absint' );
        register_setting( 'adp_plugin_settings', 'adp_batch_delay', 'absint' );
        register_setting( 'adp_plugin_settings', 'adp_smtp_host', 'sanitize_text_field' );
        register_setting( 'adp_plugin_settings', 'adp_smtp_port', 'absint' );
        register_setting( 'adp_plugin_settings', 'adp_smtp_secure', 'sanitize_text_field' );
        register_setting( 'adp_plugin_settings', 'adp_smtp_user', 'sanitize_text_field' );
        register_setting( 'adp_plugin_settings', 'adp_smtp_pass', 'sanitize_text_field' );

        // Customizer: Colores
        register_setting( 'adp_customizer_settings', 'adp_color_header_bg', 'sanitize_hex_color' );
        register_setting( 'adp_customizer_settings', 'adp_color_header_text', 'sanitize_hex_color' );
        register_setting( 'adp_customizer_settings', 'adp_color_btn_bg', 'sanitize_hex_color' );
        register_setting( 'adp_customizer_settings', 'adp_color_btn_text', 'sanitize_hex_color' );
        register_setting( 'adp_customizer_settings', 'adp_color_links', 'sanitize_hex_color' );

        // Customizer: Branding
        register_setting( 'adp_customizer_settings', 'adp_logo_url', 'esc_url_raw' ); // <--- NUEVO
        register_setting( 'adp_customizer_settings', 'adp_footer_text', 'wp_kses_post' ); // <--- NUEVO (Permite HTML básico)
    }

    public function process_actions() {
        // ACCIÓN 1: EXPORTAR CSV
        if ( isset( $_POST['adp_action_export_csv'] ) ) {
            check_admin_referer( 'adp_export_csv', 'adp_export_nonce' );
            
            $filename = 'adp-subscribers-' . date( 'Y-m-d' ) . '.csv';
            $data = $this->db->get_all_subscribers_for_export();

            // Limpiamos buffer para que no salga HTML en el CSV
            if ( ob_get_length() ) ob_end_clean();

            header( 'Content-Type: text/csv; charset=utf-8' );
            header( 'Content-Disposition: attachment; filename=' . $filename );
            
            $output = fopen( 'php://output', 'w' );
            
            // Encabezados del CSV
            fputcsv( $output, array( 'Email', 'Estado', 'Fecha Suscripción' ) );
            
            foreach ( $data as $row ) {
                fputcsv( $output, $row );
            }
            
            fclose( $output );
            exit; // Detenemos ejecución de WP
        }

        // ACCIÓN 2: IMPORTAR CSV
        if ( isset( $_POST['adp_action_import_csv'] ) ) {
            check_admin_referer( 'adp_import_csv', 'adp_import_nonce' );

            if ( ! empty( $_FILES['adp_import_file']['tmp_name'] ) ) {
                $file = $_FILES['adp_import_file']['tmp_name'];
                $handle = fopen( $file, 'r' );
                
                if ( $handle !== false ) {
                    $emails_to_import = array();
                    
                    // Leemos línea por línea
                    while ( ( $data = fgetcsv( $handle, 1000, ',' ) ) !== false ) {
                        // Asumimos que el email está en la columna 0
                        // Validación básica para saltar encabezados si dicen "Email"
                        if ( isset( $data[0] ) && is_email( $data[0] ) ) {
                            $emails_to_import[] = $data[0];
                        }
                    }
                    fclose( $handle );

                    // Insertamos en lotes de 100 para no saturar memoria
                    $chunks = array_chunk( $emails_to_import, 100 );
                    $total_inserted = 0;
                    
                    foreach ( $chunks as $chunk ) {
                        $total_inserted += $this->db->bulk_insert( $chunk );
                    }

                    wp_safe_redirect( add_query_arg( array( 'page' => 'another-dispatch-plugin', 'adp_msg' => 'imported', 'count' => $total_inserted ), admin_url( 'admin.php' ) ) );
                    exit;
                }
            }
        }

        // ACCIÓN 3: Borrar suscriptor
        if ( isset( $_GET['action'], $_GET['subscriber_id'], $_GET['_wpnonce'] ) && 'adp_delete_subscriber' === $_GET['action'] ) {
            if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'adp_delete_subscriber_action' ) ) wp_die( 'Error de seguridad.' );
            $this->db->delete_subscriber_by_id( intval( $_GET['subscriber_id'] ) );
            wp_safe_redirect( add_query_arg( array( 'page' => 'another-dispatch-plugin', 'adp_msg' => 'deleted' ), admin_url( 'admin.php' ) ) );
            exit;
        }

        // ACCIÓN 4: Reenviar Verificación
        if ( isset( $_GET['action'], $_GET['email'], $_GET['_wpnonce'] ) && 'adp_resend_verification' === $_GET['action'] ) {
            if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'adp_resend_action' ) ) wp_die( 'Error de seguridad.' );
            $email = sanitize_email( $_GET['email'] );
            $sub = $this->db->get_subscriber( $email );
            if ( $sub && 'pending' === $sub->status ) {
                $new_hash = md5( $email . time() . wp_salt() );
                $this->db->update_subscriber_hash( $email, $new_hash );

                // USAR ACTION SCHEDULER (Async)
                if ( function_exists( 'as_enqueue_async_action' ) ) {
                    as_enqueue_async_action( 'adp_send_verification_email_event', array( 'email' => $email, 'hash' => $new_hash ), 'adp_emails' );
                }
                wp_safe_redirect( add_query_arg( array( 'page' => 'another-dispatch-plugin', 'adp_msg' => 'resent_success' ), admin_url( 'admin.php' ) ) );
                exit;
            }
        }

        // ACCIÓN 5: Tests
        if ( isset( $_POST['adp_test_email_submit'] ) ) {
            check_admin_referer( 'adp_send_test_email', 'adp_test_email_nonce' );
            $to = wp_get_current_user()->user_email;
            $subject = 'Prueba SMTP ADP';
            $message = '<h1>¡Funciona!</h1><p>Configuración correcta.</p>';
            $from = get_option( 'adp_sender_email' ) ?: get_option( 'admin_email' );
            $headers = array( 'Content-Type: text/html; charset=UTF-8', 'From: ' . get_bloginfo('name') . ' <' . $from . '>' );
            $sent = wp_mail( $to, $subject, $message, $headers );
            wp_safe_redirect( add_query_arg( array( 'page' => 'another-dispatch-plugin', 'adp_msg' => $sent ? 'test_success' : 'test_error' ), admin_url( 'admin.php' ) ) );
            exit;
        }

        // ACCIÓN 6: Fuerza bruta
        if ( isset( $_POST['adp_test_content_submit'] ) ) {
            check_admin_referer( 'adp_test_content_action', 'adp_test_content_nonce' );
            
            $freq = get_option( 'adp_delivery_frequency', 'instant' );
            $msg_code = 'no_posts';

            // Verificamos que Action Scheduler esté disponible
            if ( function_exists( 'as_schedule_single_action' ) ) {
                
                if ( 'monthly' === $freq ) {
                    // CASO 1: Mensual
                    as_schedule_single_action( time(), 'adp_monthly_digest_event', array( 0 ), 'adp_emails' );
                    $msg_code = 'digest_queued';

                } elseif ( 'weekly' === $freq ) {
                    // CASO 2: Semanal
                    as_schedule_single_action( time(), 'adp_weekly_digest_event', array( 0 ), 'adp_emails' );
                    $msg_code = 'digest_queued';

                } else {
                    // CASO 3: Inmediato (Último post)
                    $latest_posts = get_posts( array( 'numberposts' => 1, 'post_status' => 'publish' ) );
                    if ( ! empty( $latest_posts ) ) {
                        as_schedule_single_action( 
                            time(), 
                            'adp_process_batch_send', 
                            array( 'post_id' => $latest_posts[0]->ID, 'offset' => 0 ), 
                            'adp_emails' 
                        );
                        $msg_code = 'instant_queued';
                    }
                }
            } else {
                // Fallback de seguridad por si AS no está
                $msg_code = 'test_error';
            }

            wp_safe_redirect( add_query_arg( array( 'page' => 'another-dispatch-plugin', 'adp_msg' => $msg_code ), admin_url( 'admin.php' ) ) );
            exit;
        }
    }

    public function render_dashboard_page() {
        $current_filter = isset( $_GET['adp_filter'] ) ? sanitize_text_field( $_GET['adp_filter'] ) : 'all';
        $subscribers = $this->db->get_subscribers( 0, 0, $current_filter );
        $count_total   = $this->db->get_subscriber_count( 'all' );
        $count_active  = $this->db->get_subscriber_count( 'active' );
        $count_pending = $this->db->get_subscriber_count( 'pending' );

        $message = '';
        $msg_type = 'success';

        if ( isset( $_GET['adp_msg'] ) ) {
            switch ( $_GET['adp_msg'] ) {
                case 'deleted': $message = 'Suscriptor eliminado.'; break;
                case 'resent_success': $message = 'Correo de verificación reenviado correctamente.'; break;
                case 'test_success': $message = 'Correo de prueba SMTP enviado.'; break;
                case 'test_error': $message = 'Error SMTP.'; $msg_type = 'error'; break;
                case 'digest_queued': $message = 'Resumen mensual en cola.'; break;
                case 'instant_queued': $message = 'Envío inmediato en cola.'; break;
                case 'no_posts': $message = 'No hay posts.'; $msg_type = 'warning'; break;
                case 'imported': 
                    $count = isset( $_GET['count'] ) ? intval( $_GET['count'] ) : 0;
                    $message = "Importación completada. Se añadieron $count suscriptores nuevos."; 
                    break;
            }
        }
        
        include ADP_PATH . 'templates/admin/dashboard.php';
    }

    /**
     * Renderiza la nueva página de Ajustes.
     */
    public function render_settings_page() {
        if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) {
             $message = 'Configuración guardada correctamente.';
        }
        include ADP_PATH . 'templates/admin/settings.php';
    }

    public function render_customizer_page() {
        if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) {
             $message = 'Diseño actualizado correctamente.';
        }
        include ADP_PATH . 'templates/admin/customizer.php';
    }

    public function render_debug_page() {
        // Verificar estado de Action Scheduler
        $as_active = function_exists( 'as_get_scheduled_actions' );
        $actions_summary = []; $recent_actions = [];
        if ( $as_active ) {
            // Conteo por estado para nuestro grupo 'adp_emails'
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