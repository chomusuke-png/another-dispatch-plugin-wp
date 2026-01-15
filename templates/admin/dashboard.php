<?php
/**
 * Template: Página de Administración de Suscriptores
 */
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Dispatch Dashboard</h1>
    <hr class="wp-header-end">

    <?php if ( ! empty( $message ) ) : ?>
        <div class="notice notice-<?php echo !empty($msg_type) ? esc_attr($msg_type) : 'success'; ?> is-dismissible">
            <p><?php echo $message; ?></p>
        </div>
    <?php endif; ?>

    <div class="adp-dashboard-wrapper">

        <div class="adp-main-column">
            <div class="postbox">
                <div class="postbox-header">
                    <h2 class="hndle">Listado de Emails</h2>
                    
                    <form method="get" action="" style="display:inline-block;">
                        <input type="hidden" name="page" value="another-dispatch-plugin" />
                        <select name="adp_filter" onchange="this.form.submit()" style="font-size: 12px; height: 28px; vertical-align: middle;">
                            <option value="all" <?php selected( $current_filter, 'all' ); ?>>Todos</option>
                            <option value="active" <?php selected( $current_filter, 'active' ); ?>>Activos</option>
                            <option value="pending" <?php selected( $current_filter, 'pending' ); ?>>Pendientes</option>
                        </select>
                    </form>
                </div>

                <div class="inside">
                    <?php if ( ! empty( $subscribers ) ) : ?>
                        <table class="wp-list-table widefat fixed striped table-view-list">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">ID</th>
                                    <th>Email</th>
                                    <th style="width: 100px;">Estado</th>
                                    <th>Fecha</th>
                                    <th style="width: 100px; text-align: center;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $subscribers as $sub ) : ?>
                                    <tr>
                                        <td style="vertical-align: middle;">#<?php echo esc_html( $sub['id'] ); ?></td>
                                        <td style="vertical-align: middle;">
                                            <strong><a href="mailto:<?php echo esc_attr( $sub['email'] ); ?>"><?php echo esc_html( $sub['email'] ); ?></a></strong>
                                        </td>
                                        <td style="vertical-align: middle;">
                                            <?php if ( 'active' === $sub['status'] ) : ?>
                                                <span style="color: #008a20; font-weight: 600; font-size: 11px; background: #dff6dd; padding: 3px 8px; border-radius: 12px;">ACTIVO</span>
                                            <?php else : ?>
                                                <span style="color: #d63638; font-weight: 600; font-size: 11px; background: #fbeaea; padding: 3px 8px; border-radius: 12px;">PENDIENTE</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="vertical-align: middle;"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $sub['created_at'] ) ) ); ?></td>
                                        
                                        <td style="vertical-align: middle;">
                                            <div style="display: flex; gap: 6px; justify-content: center;">
                                                
                                                <?php if ( 'pending' === $sub['status'] ) : ?>
                                                    <?php $resend_url = wp_nonce_url( admin_url( 'admin.php?page=another-dispatch-plugin&action=adp_resend_verification&email=' . urlencode($sub['email']) ), 'adp_resend_action' ); ?>
                                                    
                                                    <a href="<?php echo esc_url( $resend_url ); ?>" class="button button-small" title="Reenviar verificación" style="display: flex; align-items: center; justify-content: center; width: 30px; height: 28px; padding: 0;">
                                                        <span class="dashicons dashicons-email-alt" style="color: #2271b1; font-size: 18px; margin: 0;"></span>
                                                    </a>
                                                <?php endif; ?>

                                                <?php 
                                                $del_url = wp_nonce_url( admin_url( 'admin.php?page=another-dispatch-plugin&action=adp_delete_subscriber&subscriber_id=' . $sub['id'] ), 'adp_delete_subscriber_action' ); 
                                                ?>
                                                
                                                <a href="<?php echo esc_url( $del_url ); ?>" class="button button-small" onclick="return confirm('¿Borrar suscriptor?');" title="Eliminar suscriptor" style="display: flex; align-items: center; justify-content: center; width: 30px; height: 28px; padding: 0; border-color: #d63638;">
                                                    <span class="dashicons dashicons-trash" style="color: #d63638; font-size: 18px; margin: 0;"></span>
                                                </a>

                                            </div>
                                        </td>

                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <div style="padding: 20px; text-align: center; color: #666;">
                            <span class="dashicons dashicons-groups" style="font-size: 40px; color: #ddd; margin-bottom: 10px; display: block; width: 100%;"></span>
                            <p>No se encontraron suscriptores con este filtro.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="adp-sidebar-column">

            <div class="adp-stat-card">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                    <div>
                        <div class="adp-stat-number" style="font-size: 32px;"><?php echo esc_html( $count_total ); ?></div>
                        <div class="adp-stat-label">Total Registros</div>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <div style="flex: 1; text-align: center; background: #f0f6fc; padding: 10px; border-radius: 4px;">
                        <strong style="display: block; font-size: 18px; color: #00a32a;"><?php echo esc_html( $count_active ); ?></strong>
                        <span style="font-size: 11px; text-transform: uppercase; color: #666;">Activos</span>
                    </div>
                    <div style="flex: 1; text-align: center; background: #fff8e5; padding: 10px; border-radius: 4px;">
                        <strong style="display: block; font-size: 18px; color: #dba617;"><?php echo esc_html( $count_pending ); ?></strong>
                        <span style="font-size: 11px; text-transform: uppercase; color: #666;">Pendientes</span>
                    </div>
                </div>
            </div>

            <div class="postbox">
                <div class="postbox-header"><h2 class="hndle">Gestión de Datos (CSV)</h2></div>
                <div class="inside">
                    
                    <p><strong>Exportar Lista:</strong></p>
                    <form method="post" action="">
                        <?php wp_nonce_field( 'adp_export_csv', 'adp_export_nonce' ); ?>
                        <input type="submit" name="adp_action_export_csv" class="button" value="Descargar CSV" style="width: 100%;">
                    </form>

                    <hr style="margin: 15px 0;">

                    <p><strong>Importar Suscriptores:</strong></p>
                    <form method="post" action="" enctype="multipart/form-data">
                        <?php wp_nonce_field( 'adp_import_csv', 'adp_import_nonce' ); ?>
                        <input type="file" name="adp_import_file" accept=".csv" required style="margin-bottom: 10px; width: 100%;">
                        <p class="description" style="margin-bottom: 10px;">
                            Sube un archivo CSV con una columna de emails. Los nuevos se añadirán como <strong>Activos</strong>. Los duplicados se ignorarán.
                        </p>
                        <input type="submit" name="adp_action_import_csv" class="button button-primary" value="Subir e Importar" style="width: 100%;">
                    </form>

                </div>
            </div>

            <div class="postbox" style="border-color: #ffb900;">
                <div class="postbox-header" style="background: #fff8e5; border-bottom-color: #f0c33c;">
                    <h2 class="hndle" style="color: #996800;">⚡ Operaciones Manuales</h2>
                </div>
                <div class="inside">
                    
                    <h3 style="margin: 0 0 10px; font-size: 13px;">Conexión SMTP</h3>
                    <form method="post" action="">
                        <?php wp_nonce_field( 'adp_send_test_email', 'adp_test_email_nonce' ); ?>
                        <input type="submit" name="adp_test_email_submit" class="button button-secondary" value="Probar Conexión" style="width: 100%;">
                    </form>

                    <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">

                    <h3 style="margin: 0 0 10px; font-size: 13px;">Disparador de Envío</h3>
                    <p style="font-size: 12px; color: #666; margin-bottom: 10px;">
                        Fuerza el envío inmediato del último post o resumen a todos los suscriptores. Úsalo con precaución.
                    </p>
                    
                    <form method="post" action="" onsubmit="return confirm('¿Seguro? Esto enviará correos REALES a tus suscriptores.');">
                        <?php wp_nonce_field( 'adp_test_content_action', 'adp_test_content_nonce' ); ?>
                        <input type="submit" name="adp_test_content_submit" class="button button-primary" value="Ejecutar Envío Masivo" style="width: 100%;">
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>