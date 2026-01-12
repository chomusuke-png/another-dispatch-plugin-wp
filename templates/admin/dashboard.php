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
                <div class="postbox-header"><h2 class="hndle">Listado de Emails</h2></div>
                <div class="inside">
                    <?php if ( ! empty( $subscribers ) ) : ?>
                        <table class="wp-list-table widefat fixed striped table-view-list">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">ID</th>
                                    <th>Email</th>
                                    <th>Fecha</th>
                                    <th style="width: 100px; text-align: center;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $subscribers as $sub ) : ?>
                                    <tr>
                                        <td>#<?php echo esc_html( $sub['id'] ); ?></td>
                                        <td><strong><a href="mailto:<?php echo esc_attr( $sub['email'] ); ?>"><?php echo esc_html( $sub['email'] ); ?></a></strong></td>
                                        <td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $sub['created_at'] ) ) ); ?></td>
                                        <td style="text-align: center;">
                                            <?php 
                                            $del_url = wp_nonce_url( admin_url( 'admin.php?page=another-dispatch-plugin&action=adp_delete_subscriber&subscriber_id=' . $sub['id'] ), 'adp_delete_subscriber_action' ); 
                                            ?>
                                            <a href="<?php echo esc_url( $del_url ); ?>" class="button button-small button-link-delete" onclick="return confirm('¿Borrar suscriptor?');"><span class="dashicons dashicons-trash"></span></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <p class="description" style="padding: 10px;">No hay suscriptores registrados todavía.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="adp-sidebar-column">

            <div class="adp-stat-card">
                <div class="adp-stat-number"><?php echo esc_html( $count ); ?></div>
                <div class="adp-stat-label">Suscriptores Activos</div>
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