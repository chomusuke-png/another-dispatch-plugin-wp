<?php
/**
 * Template: Página de Debug y Logs (Refactorizado)
 */
?>

<div class="wrap">
    <div class="adp-header-flex">
        <h1 class="wp-heading-inline">Diagnóstico del Sistema</h1>
    </div>
    
    <hr class="wp-header-end">
    <p>Estado del motor de envíos (Action Scheduler).</p>

    <?php if ( ! $as_active ) : ?>
        <div class="notice notice-error inline">
            <p><strong>Error Crítico:</strong> Action Scheduler no parece estar activo. Los correos no se enviarán.</p>
        </div>
    <?php else : ?>

        <div class="adp-kpi-grid">
            
            <div class="adp-kpi-card pending">
                <span class="dashicons dashicons-clock adp-kpi-icon"></span>
                <h2 id="adp-count-pending" class="adp-kpi-number"><?php echo intval( $actions_summary['pending'] ?? 0 ); ?></h2>
                <p class="adp-kpi-label">En Cola</p>
            </div>

            <div class="adp-kpi-card complete">
                <span class="dashicons dashicons-yes-alt adp-kpi-icon"></span>
                <h2 id="adp-count-complete" class="adp-kpi-number"><?php echo intval( $actions_summary['complete'] ?? 0 ); ?></h2>
                <p class="adp-kpi-label">Completados</p>
            </div>

            <div class="adp-kpi-card failed">
                <span class="dashicons dashicons-warning adp-kpi-icon"></span>
                <h2 id="adp-count-failed" class="adp-kpi-number"><?php echo intval( $actions_summary['failed'] ?? 0 ); ?></h2>
                <p class="adp-kpi-label">Fallidos</p>
            </div>

            <div class="adp-kpi-card running">
                <span class="dashicons dashicons-controls-play adp-kpi-icon"></span>
                <h2 id="adp-count-in-progress" class="adp-kpi-number"><?php echo intval( $actions_summary['in-progress'] ?? 0 ); ?></h2>
                <p class="adp-kpi-label">En Ejecución</p>
            </div>

        </div>

        <div class="postbox">
            <div class="postbox-header">
                <h2 class="hndle">Actividad Reciente</h2>
            </div>
            <div class="inside" style="padding:0; margin:0;">
                <table class="wp-list-table widefat fixed striped adp-log-table">
                    <thead>
                        <tr>
                            <th style="width: 250px;">Acción / Hook</th>
                            <th>Argumentos</th>
                            <th style="width: 120px;">Estado</th>
                            <th style="width: 180px;">Fecha Ejecución</th>
                            <th style="width: 100px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="adp-debug-table-body">
                        <?php 
                        if ( empty( $recent_actions ) ) : ?>
                            <tr><td colspan="5">No hay actividad reciente.</td></tr>
                        <?php else : 
                            foreach ( $recent_actions as $action_id => $action ) {
                                $hook = $action->get_hook();
                                $args = $action->get_args();
                                $schedule = $action->get_schedule();
                                $next_run = $schedule->get_date();
                                $store = ActionScheduler::store();
                                $status = $store->get_status( $action_id );
                                
                                echo '<tr>';
                                
                                // Columna Hook
                                echo '<td><strong>' . esc_html( $hook ) . '</strong><br><small style="color:#a7aaad;">ID: ' . intval( $action_id ) . '</small></td>';
                                
                                // Columna Args
                                echo '<td>';
                                if ( ! empty( $args ) ) echo '<code style="display:block; max-height:60px; overflow-y:auto; font-size:10px;">' . esc_html( print_r( $args, true ) ) . '</code>';
                                else echo '<span style="color:#ccc;">-</span>';
                                echo '</td>';
                                
                                // Columna Status
                                echo '<td><span class="adp-status-badge ' . esc_attr( $status ) . '">' . esc_html( $status ) . '</span></td>';
                                
                                // Columna Fecha
                                echo '<td>' . ( $next_run ? $next_run->format( 'Y-m-d H:i:s' ) : '-' ) . '</td>';
                                
                                // Columna Acciones
                                echo '<td>';
                                if ( 'failed' === $status ) echo '<a href="' . admin_url( 'tools.php?page=action-scheduler&s=' . $action_id ) . '" target="_blank" class="button button-small">Ver Log</a>';
                                else echo '-';
                                echo '</td>';
                                
                                echo '</tr>';
                            }
                        endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php endif; ?>
</div>