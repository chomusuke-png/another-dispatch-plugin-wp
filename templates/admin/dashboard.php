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
                    <h2 class="hndle" style="display:inline-block; margin-right: 15px;">Listado de Emails</h2>
                    
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
                                    <th style="width: 120px; text-align: center;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $subscribers as $sub ) : ?>
                                    <tr>
                                        <td>#<?php echo esc_html( $sub['id'] ); ?></td>
                                        <td>
                                            <strong><a href="mailto:<?php echo esc_attr( $sub['email'] ); ?>"><?php echo esc_html( $sub['email'] ); ?></a></strong>
                                        </td>
                                        <td>
                                            <?php if ( 'active' === $sub['status'] ) : ?>
                                                <span style="color: green; font-weight: bold; font-size: 11px; background: #e7f7ed; padding: 2px 6px; border-radius: 3px;">ACTIVO</span>
                                            <?php else : ?>
                                                <span style="color: #d63638; font-weight: bold; font-size: 11px; background: #fce8e6; padding: 2px 6px; border-radius: 3px;">PENDIENTE</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $sub['created_at'] ) ) ); ?></td>
                                        
                                        <td style="text-align: center;">
                                            <?php 
                                            // URL para borrar
                                            $del_url = wp_nonce_url( admin_url( 'admin.php?page=another-dispatch-plugin&action=adp_delete_subscriber&subscriber_id=' . $sub['id'] ), 'adp_delete_subscriber_action' ); 
                                            ?>

                                            <?php if ( 'pending' === $sub['status'] ) : ?>
                                                <?php 
                                                $resend_url = wp_nonce_url( admin_url( 'admin.php?page=another-dispatch-plugin&action=adp_resend_verification&email=' . urlencode($sub['email']) ), 'adp_resend_action' ); 
                                                ?>
                                                <a href="<?php echo esc_url( $resend_url ); ?>" class="button button-small" title="Reenviar correo de verificación" style="margin-right: 5px;">
                                                    <span class="dashicons dashicons-email-alt" style="line-height: 26px; font-size: 16px; color: #2271b1;"></span>
                                                </a>
                                            <?php else : ?>
                                                <button class="button button-small" disabled title="Usuario ya verificado" style="margin-right: 5px; opacity: 0.5;">
                                                    <span class="dashicons dashicons-yes" style="line-height: 26px; font-size: 16px; color: green;"></span>
                                                </button>
                                            <?php endif; ?>

                                            <a href="<?php echo esc_url( $del_url ); ?>" class="button button-small button-link-delete" onclick="return confirm('¿Borrar suscriptor?');" title="Eliminar suscriptor">
                                                <span class="dashicons dashicons-trash" style="line-height: 26px; font-size: 16px;"></span>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <p class="description" style="padding: 10px;">No se encontraron suscriptores con este filtro.</p>
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
                <div class="postbox-header"><h2 class="hndle">Configuración General</h2></div>
                <div class="inside">
                    <form method="post" action="options.php">
                        <?php settings_fields( 'adp_plugin_settings' ); ?>
                        <?php do_settings_sections( 'adp_plugin_settings' ); ?>

                        <h3 class="hndle">Modo de Envío</h3>
                        <?php $freq = get_option( 'adp_delivery_frequency', 'instant' ); ?>
                        <div class="adp-radio-group">
                            <label class="adp-radio-option">
                                <input type="radio" name="adp_delivery_frequency" value="instant" <?php checked( $freq, 'instant' ); ?>> 
                                <span><strong>Inmediato:</strong> Enviar al publicar.</span>
                            </label>
                            <label class="adp-radio-option">
                                <input type="radio" name="adp_delivery_frequency" value="monthly" <?php checked( $freq, 'monthly' ); ?>> 
                                <span><strong>Resumen Mensual:</strong> Recopilación mensual.</span>
                            </label>
                        </div>

                        <h3 class="hndle">Rendimiento y Límites</h3>
                        <p class="description" style="margin-bottom: 15px;">Controla la velocidad de envío para evitar bloqueos de tu hosting.</p>
                        
                        <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                            <div style="flex: 1;">
                                <label class="adp-label-title">Correos por Lote (Batch Size):</label>
                                <input type="number" name="adp_batch_size" value="<?php echo esc_attr( get_option( 'adp_batch_size', 50 ) ); ?>" class="widefat" min="1" max="500">
                                <p class="description">Recomendado: 20 a 50.</p>
                            </div>
                            <div style="flex: 1;">
                                <label class="adp-label-title">Pausa entre Lotes (Segundos):</label>
                                <input type="number" name="adp_batch_delay" value="<?php echo esc_attr( get_option( 'adp_batch_delay', 120 ) ); ?>" class="widefat" min="0">
                                <p class="description">Tiempo de espera antes del siguiente envío.</p>
                            </div>
                        </div>

                        <h3 class="hndle">SMTP & Identidad</h3>
                        <p>
                            <label class="adp-label-title" for="adp_sender_email">Remitente:</label>
                            <input type="email" id="adp_sender_email" name="adp_sender_email" value="<?php echo esc_attr( get_option( 'adp_sender_email' ) ); ?>" class="widefat" placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>">
                        </p>
                        <p>
                            <label class="adp-label-title" for="adp_smtp_host">Host SMTP:</label>
                            <input type="text" id="adp_smtp_host" name="adp_smtp_host" value="<?php echo esc_attr( get_option( 'adp_smtp_host' ) ); ?>" class="widefat">
                        </p>
                        <div style="display: flex; gap: 10px;">
                            <div style="flex: 1;">
                                <label class="adp-label-title">Puerto:</label>
                                <input type="number" name="adp_smtp_port" value="<?php echo esc_attr( get_option( 'adp_smtp_port', '587' ) ); ?>" class="widefat">
                            </div>
                            <div style="flex: 1;">
                                <label class="adp-label-title">Seguridad:</label>
                                <select name="adp_smtp_secure" class="widefat">
                                    <option value="ssl" <?php selected( get_option( 'adp_smtp_secure' ), 'ssl' ); ?>>SSL</option>
                                    <option value="tls" <?php selected( get_option( 'adp_smtp_secure' ), 'tls' ); ?>>TLS</option>
                                    <option value="" <?php selected( get_option( 'adp_smtp_secure' ), '' ); ?>>Ninguna</option>
                                </select>
                            </div>
                        </div>
                        <p>
                            <label class="adp-label-title">Usuario:</label>
                            <input type="text" name="adp_smtp_user" value="<?php echo esc_attr( get_option( 'adp_smtp_user' ) ); ?>" class="widefat">
                        </p>
                        <p>
                            <label class="adp-label-title">Password:</label>
                            <input type="password" name="adp_smtp_pass" value="<?php echo esc_attr( get_option( 'adp_smtp_pass' ) ); ?>" class="widefat">
                        </p>

                        <div style="margin-top: 20px;">
                            <?php submit_button( 'Guardar Cambios', 'primary', 'submit', false, array( 'style' => 'width:100%' ) ); ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="postbox" style="border-color: #ffb900;">
                <div class="postbox-header" style="background: #fff8e5; border-bottom-color: #f0c33c;">
                    <h2 class="hndle" style="color: #996800;">⚡ Zona de Pruebas</h2>
                </div>
                <div class="inside">
                    
                    <h3 style="margin: 0 0 10px; font-size: 13px;">Conexión SMTP</h3>
                    <p style="font-size: 12px; margin-bottom: 10px; color: #666;">Envía un correo simple al admin para verificar credenciales.</p>
                    <form method="post" action="">
                        <?php wp_nonce_field( 'adp_send_test_email', 'adp_test_email_nonce' ); ?>
                        <input type="submit" name="adp_test_email_submit" class="button button-secondary" value="Probar Conexión" style="width: 100%;">
                    </form>

                    <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">

                    <h3 style="margin: 0 0 10px; font-size: 13px;">Simulación de Envío</h3>
                    <?php if ( 'monthly' === $freq ) : ?>
                        <p style="font-size: 12px; color: #666; margin-bottom: 10px;">
                            Estás en modo <strong>Mensual</strong>. Se generará y enviará inmediatamente el resumen del mes pasado.
                        </p>
                    <?php else : ?>
                        <p style="font-size: 12px; color: #666; margin-bottom: 10px;">
                            Estás en modo <strong>Inmediato</strong>. Se enviará el último post publicado a tu lista.
                        </p>
                    <?php endif; ?>
                    
                    <form method="post" action="" onsubmit="return confirm('¿Seguro? Esto enviará correos REALES a tus suscriptores.');">
                        <?php wp_nonce_field( 'adp_test_content_action', 'adp_test_content_nonce' ); ?>
                        <input type="submit" name="adp_test_content_submit" class="button button-primary" value="Forzar Envío a Suscriptores" style="width: 100%;">
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>