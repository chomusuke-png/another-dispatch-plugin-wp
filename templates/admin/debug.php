<?php
/**
 * Template: Página de Debug y Logs (Con Auto-Refresh AJAX)
 */
?>

<div class="wrap">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h1>Diagnóstico del Sistema</h1>
        
        <div id="adp-live-indicator" style="display:flex; align-items:center; gap:5px; font-size:12px; color:#666; background:#fff; padding:5px 10px; border-radius:15px; border:1px solid #ddd;">
            <span class="dashicons dashicons-update" style="font-size:14px; width:14px; height:14px; animation: spin 2s infinite linear;"></span>
            <span>Monitor en tiempo real activo</span>
        </div>
    </div>
    
    <p>Revisa aquí el estado del motor de envíos (Action Scheduler) para detectar problemas.</p>
    <hr class="wp-header-end">

    <?php if ( ! $as_active ) : ?>
        <div class="notice notice-error inline">
            <p><strong>Error Crítico:</strong> Action Scheduler no parece estar activo. Los correos no se enviarán.</p>
        </div>
    <?php else : ?>

        <div class="adp-dashboard-wrapper" style="margin-top: 20px;">
            <div class="adp-stat-card" style="width: 100%; display: flex; gap: 20px; box-sizing: border-box;">
                
                <div style="flex: 1; text-align: center; padding: 20px; background: #fff; border: 1px solid #ccd0d4; border-left: 4px solid #dba617;">
                    <span class="dashicons dashicons-clock" style="font-size: 30px; height: 30px; width: 30px; color: #dba617;"></span>
                    <h2 id="adp-count-pending" style="margin: 10px 0; font-size: 32px; transition: color 0.3s;"><?php echo intval( $actions_summary['pending'] ?? 0 ); ?></h2>
                    <p style="margin: 0; color: #666; font-weight: bold;">En Cola (Pendientes)</p>
                </div>

                <div style="flex: 1; text-align: center; padding: 20px; background: #fff; border: 1px solid #ccd0d4; border-left: 4px solid #00a32a;">
                    <span class="dashicons dashicons-yes-alt" style="font-size: 30px; height: 30px; width: 30px; color: #00a32a;"></span>
                    <h2 id="adp-count-complete" style="margin: 10px 0; font-size: 32px; transition: color 0.3s;"><?php echo intval( $actions_summary['complete'] ?? 0 ); ?></h2>
                    <p style="margin: 0; color: #666; font-weight: bold;">Completados</p>
                </div>

                <div style="flex: 1; text-align: center; padding: 20px; background: #fff; border: 1px solid #ccd0d4; border-left: 4px solid #d63638;">
                    <span class="dashicons dashicons-warning" style="font-size: 30px; height: 30px; width: 30px; color: #d63638;"></span>
                    <h2 id="adp-count-failed" style="margin: 10px 0; font-size: 32px; transition: color 0.3s;"><?php echo intval( $actions_summary['failed'] ?? 0 ); ?></h2>
                    <p style="margin: 0; color: #666; font-weight: bold;">Fallidos</p>
                </div>

                <div style="flex: 1; text-align: center; padding: 20px; background: #fff; border: 1px solid #ccd0d4;">
                    <span class="dashicons dashicons-controls-play" style="font-size: 30px; height: 30px; width: 30px; color: #2271b1;"></span>
                    <h2 id="adp-count-in-progress" style="margin: 10px 0; font-size: 32px; transition: color 0.3s;"><?php echo intval( $actions_summary['in-progress'] ?? 0 ); ?></h2>
                    <p style="margin: 0; color: #666; font-weight: bold;">En Ejecución</p>
                </div>
            </div>
        </div>

        <h2 style="margin-top: 30px;">Registro de Actividad Reciente</h2>
        <div class="card" style="max-width: 100%; margin-top: 10px; padding: 0;">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 200px;">Acción (Hook)</th>
                        <th>Argumentos (Datos)</th>
                        <th style="width: 150px;">Estado</th>
                        <th style="width: 200px;">Fecha</th>
                        <th>Log / Notas</th>
                    </tr>
                </thead>
                <tbody id="adp-debug-table-body">
                    <?php 
                    // Renderizamos la primera vez con PHP
                    if ( empty( $recent_actions ) ) : ?>
                        <tr><td colspan="5">No hay actividad reciente.</td></tr>
                    <?php else : 
                        foreach ( $recent_actions as $action_id => $action ) {
                            // Reutilizamos la lógica que ya teníamos, pero ahora también estará en el AJAX
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
                            echo '<td>';
                            if ( 'failed' === $status_label ) echo '<a href="' . admin_url( 'tools.php?page=action-scheduler&s=' . $action_id ) . '" target="_blank">Ver error</a>';
                            else echo '-';
                            echo '</td>';
                            echo '</tr>';
                        }
                    endif; ?>
                </tbody>
            </table>
        </div>

        <style>
            @keyframes spin { 100% { transform:rotate(360deg); } }
        </style>

    <?php endif; ?>
</div>