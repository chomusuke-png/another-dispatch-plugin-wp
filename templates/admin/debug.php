<?php
/**
 * Template: Página de Debug y Logs
 */
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <div class="adp-header-flex">
        <h1 class="wp-heading-inline">Diagnóstico del Sistema</h1>
    </div>
    
    <hr class="wp-header-end">
    <p>Estado del motor de envíos (Action Scheduler).</p>

    <?php if (!$isActionSchedulerActive) : ?>
        <div class="notice notice-error inline">
            <p><strong>Error Crítico:</strong> Action Scheduler no parece estar activo. Los correos no se enviarán.</p>
        </div>
    <?php else : ?>

        <div class="adp-kpi-grid">
            <div class="adp-kpi-card pending">
                <span class="dashicons dashicons-clock adp-kpi-icon"></span>
                <h2 id="adp-count-pending" class="adp-kpi-number"><?php echo intval($actionsSummary['pending'] ?? 0); ?></h2>
                <p class="adp-kpi-label">En Cola</p>
            </div>
            <div class="adp-kpi-card complete">
                <span class="dashicons dashicons-yes-alt adp-kpi-icon"></span>
                <h2 id="adp-count-complete" class="adp-kpi-number"><?php echo intval($actionsSummary['complete'] ?? 0); ?></h2>
                <p class="adp-kpi-label">Completados</p>
            </div>
            <div class="adp-kpi-card failed">
                <span class="dashicons dashicons-warning adp-kpi-icon"></span>
                <h2 id="adp-count-failed" class="adp-kpi-number"><?php echo intval($actionsSummary['failed'] ?? 0); ?></h2>
                <p class="adp-kpi-label">Fallidos</p>
            </div>
            <div class="adp-kpi-card running">
                <span class="dashicons dashicons-controls-play adp-kpi-icon"></span>
                <h2 id="adp-count-in-progress" class="adp-kpi-number"><?php echo intval($actionsSummary['in-progress'] ?? 0); ?></h2>
                <p class="adp-kpi-label">En Ejecución</p>
            </div>
        </div>

        <div class="postbox" style="border: 1px solid #1e1e1e; background: #23282d;">
            <div class="postbox-header" style="background: #1e1e1e; border-bottom: 1px solid #000; display: flex; justify-content: space-between; align-items: center;">
                <h2 class="hndle" style="color: #fff; display: flex; align-items: center; gap: 8px;">
                    <span class="dashicons dashicons-editor-code" style="color: #44ccff;"></span> Consola en Vivo
                </h2>
                <button type="button" id="adp-clear-console-btn" class="button button-small" style="background: #333; color: #fff; border-color: #000;">Limpiar</button>
            </div>
            <div class="inside" style="padding: 0; margin: 0;">
                <div id="adp-live-console-output" style="padding: 15px; height: 250px; overflow-y: auto; font-family: 'Courier New', Courier, monospace; font-size: 12px; color: #ccc; line-height: 1.6;">
                    <span style="color:#888;">> Conectando al servidor...</span>
                </div>
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
                        if (empty($recentActions)) : ?>
                            <tr><td colspan="5">No hay actividad reciente.</td></tr>
                        <?php else : 
                            foreach ($recentActions as $actionId => $action) {
                                $hookName = $action->get_hook();
                                $arguments = $action->get_args();
                                $schedule = $action->get_schedule();
                                $nextRunDate = $schedule->get_date();
                                
                                $store = ActionScheduler::store();
                                $statusLabel = $store->get_status($actionId);
                                
                                echo '<tr>';
                                echo '<td><strong>' . esc_html($hookName) . '</strong><br><small style="color:#a7aaad;">ID: ' . esc_html((string)$actionId) . '</small></td>';
                                
                                echo '<td>';
                                if (!empty($arguments)) {
                                    echo '<pre style="margin:0; max-height:60px; overflow-y:auto; font-size:10px;">' . esc_html(print_r($arguments, true)) . '</pre>';
                                } else {
                                    echo '<span style="color:#ccc;">-</span>';
                                }
                                echo '</td>';
                                
                                echo '<td><span class="adp-status-badge ' . esc_attr($statusLabel) . '">' . esc_html($statusLabel) . '</span></td>';
                                
                                echo '<td>' . ($nextRunDate ? esc_html($nextRunDate->format('Y-m-d H:i:s')) : '-') . '</td>';
                                
                                echo '<td>';
                                if ('failed' === $statusLabel) {
                                    echo '<a href="' . esc_url(admin_url('tools.php?page=action-scheduler&s=' . $actionId)) . '" target="_blank" class="button button-small">Ver Log</a>';
                                } else {
                                    echo '-';
                                }
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