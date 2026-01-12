<?php
/**
 * Template: Página de Debug y Logs
 */
?>

<div class="wrap">
    <h1>Diagnóstico del Sistema</h1>
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
                    <h2 style="margin: 10px 0; font-size: 32px;"><?php echo intval( $actions_summary['pending'] ?? 0 ); ?></h2>
                    <p style="margin: 0; color: #666; font-weight: bold;">En Cola (Pendientes)</p>
                </div>

                <div style="flex: 1; text-align: center; padding: 20px; background: #fff; border: 1px solid #ccd0d4; border-left: 4px solid #00a32a;">
                    <span class="dashicons dashicons-yes-alt" style="font-size: 30px; height: 30px; width: 30px; color: #00a32a;"></span>
                    <h2 style="margin: 10px 0; font-size: 32px;"><?php echo intval( $actions_summary['complete'] ?? 0 ); ?></h2>
                    <p style="margin: 0; color: #666; font-weight: bold;">Completados</p>
                </div>

                <div style="flex: 1; text-align: center; padding: 20px; background: #fff; border: 1px solid #ccd0d4; border-left: 4px solid #d63638;">
                    <span class="dashicons dashicons-warning" style="font-size: 30px; height: 30px; width: 30px; color: #d63638;"></span>
                    <h2 style="margin: 10px 0; font-size: 32px;"><?php echo intval( $actions_summary['failed'] ?? 0 ); ?></h2>
                    <p style="margin: 0; color: #666; font-weight: bold;">Fallidos</p>
                </div>

                <div style="flex: 1; text-align: center; padding: 20px; background: #fff; border: 1px solid #ccd0d4;">
                    <span class="dashicons dashicons-controls-play" style="font-size: 30px; height: 30px; width: 30px; color: #2271b1;"></span>
                    <h2 style="margin: 10px 0; font-size: 32px;"><?php echo intval( $actions_summary['in-progress'] ?? 0 ); ?></h2>
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
                        <th style="width: 200px;">Fecha Programada/Ejecución</th>
                        <th>Log / Notas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $recent_actions ) ) : ?>
                        <tr>
                            <td colspan="5">No hay actividad reciente registrada en el grupo 'adp_emails'.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ( $recent_actions as $action_id => $action ) : ?>
                            <?php 
                                $status = $action->get_schedule()->is_recurring() ? 'recurrente' : 'unico'; // Simplificado, mejor usar get_status si está disponible o inferir
                                
                                // Action Scheduler devuelve objetos, la forma de obtener status varía según versión, 
                                // pero generalmente query devuelve objetos Action.
                                // Usamos el store para obtener el status real si es posible, o asumimos basado en la query.
                                // Truco: la query ya nos dio objetos, vamos a intentar sacar info básica.
                                $hook = $action->get_hook();
                                $args = $action->get_args();
                                $schedule = $action->get_schedule();
                                $next_run = $schedule->get_date();
                                
                                // Para obtener el estado real y logs necesitamos el Store
                                $store = ActionScheduler::store();
                                $status_label = $store->get_status( $action_id );
                                
                                $status_color = '#72aee6';
                                if ( 'complete' === $status_label ) $status_color = '#00a32a';
                                if ( 'failed' === $status_label ) $status_color = '#d63638';
                                if ( 'pending' === $status_label ) $status_color = '#dba617';
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html( $hook ); ?></strong><br>
                                    <small>ID: <?php echo intval( $action_id ); ?></small>
                                </td>
                                <td>
                                    <?php 
                                    if ( ! empty( $args ) ) {
                                        echo '<pre style="margin:0; font-size:10px;">' . print_r( $args, true ) . '</pre>';
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span style="font-weight:bold; color:<?php echo $status_color; ?>; text-transform:uppercase;">
                                        <?php echo esc_html( $status_label ); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    if ( $next_run ) {
                                        echo $next_run->format( 'Y-m-d H:i:s' );
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if ( 'failed' === $status_label ) : ?>
                                        <a href="<?php echo admin_url( 'tools.php?page=action-scheduler&s=' . $action_id ); ?>" target="_blank">Ver error completo</a>
                                    <?php else: ?>
                                        <span style="color:#ccc;">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <p class="description" style="margin-top: 20px;">
            ℹ️ <strong>Nota:</strong> Este plugin utiliza <em>Action Scheduler</em>. Si las tareas se quedan en "Pendiente" y no avanzan, asegúrate de que el Cron de WordPress esté funcionando o visita la 
            <a href="<?php echo admin_url( 'tools.php?page=action-scheduler' ); ?>">consola completa de Action Scheduler</a>.
        </p>

    <?php endif; ?>
</div>