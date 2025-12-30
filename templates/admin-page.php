<?php
/**
 * Template: Página de Administración de Suscriptores
 * Variables disponibles: $subscribers (array), $count (int), $message (string), $msg_type (string)
 */
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Suscriptores ADP</h1>
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
                </div>
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
                                        <td>
                                            <strong>
                                                <a href="mailto:<?php echo esc_attr( $sub['email'] ); ?>">
                                                    <?php echo esc_html( $sub['email'] ); ?>
                                                </a>
                                            </strong>
                                        </td>
                                        <td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $sub['created_at'] ) ) ); ?></td>
                                        <td style="text-align: center;">
                                            <?php 
                                            $delete_url = wp_nonce_url( 
                                                admin_url( 'admin.php?page=another-dispatch-plugin&action=adp_delete_subscriber&subscriber_id=' . $sub['id'] ), 
                                                'adp_delete_subscriber_action' 
                                            ); 
                                            ?>
                                            <a href="<?php echo esc_url( $delete_url ); ?>" 
                                               class="button button-small button-link-delete" 
                                               onclick="return confirm('¿Estás seguro de que quieres eliminar a este suscriptor?');">
                                                <span class="dashicons dashicons-trash"></span>
                                            </a>
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
                <div class="postbox-header">
                    <h2 class="hndle">Configuración General</h2>
                </div>
                <div class="inside">
                    <form method="post" action="options.php">
                        <?php settings_fields( 'adp_plugin_settings' ); ?>
                        <?php do_settings_sections( 'adp_plugin_settings' ); ?>

                        <h3 class="hndle">Modo de Envío</h3>
                        <?php $freq = get_option( 'adp_delivery_frequency', 'instant' ); ?>
                        
                        <div class="adp-radio-group">
                            <label class="adp-radio-option">
                                <input type="radio" name="adp_delivery_frequency" value="instant" <?php checked( $freq, 'instant' ); ?>> 
                                <span><strong>Inmediato:</strong> Enviar un correo cada vez que publico un post.</span>
                            </label>
                            
                            <label class="adp-radio-option">
                                <input type="radio" name="adp_delivery_frequency" value="monthly" <?php checked( $freq, 'monthly' ); ?>> 
                                <span><strong>Resumen Mensual:</strong> Enviar un solo correo el día 1 de cada mes.</span>
                            </label>
                        </div>

                        <h3 class="hndle">Identidad</h3>
                        <p>
                            <label class="adp-label-title" for="adp_sender_email">Email del Remitente (From):</label>
                            <input type="email" id="adp_sender_email" name="adp_sender_email" 
                                   value="<?php echo esc_attr( get_option( 'adp_sender_email' ) ); ?>" 
                                   class="widefat" placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>">
                        </p>

                        <h3 class="hndle">Servidor SMTP</h3>
                        <p class="description" style="margin-bottom: 10px;">Datos de conexión de PremiumHosting/DirectAdmin.</p>
                        
                        <p>
                            <label class="adp-label-title" for="adp_smtp_host">Host:</label>
                            <input type="text" id="adp_smtp_host" name="adp_smtp_host" 
                                   value="<?php echo esc_attr( get_option( 'adp_smtp_host' ) ); ?>" 
                                   class="widefat" placeholder="mail.midominio.com">
                        </p>

                        <div style="display: flex; gap: 10px;">
                            <div style="flex: 1;">
                                <label class="adp-label-title" for="adp_smtp_port">Puerto:</label>
                                <input type="number" id="adp_smtp_port" name="adp_smtp_port" 
                                       value="<?php echo esc_attr( get_option( 'adp_smtp_port', '587' ) ); ?>" 
                                       class="widefat">
                            </div>
                            <div style="flex: 1;">
                                <label class="adp-label-title" for="adp_smtp_secure">Encriptación:</label>
                                <select id="adp_smtp_secure" name="adp_smtp_secure" class="widefat">
                                    <option value="ssl" <?php selected( get_option( 'adp_smtp_secure' ), 'ssl' ); ?>>SSL (465)</option>
                                    <option value="tls" <?php selected( get_option( 'adp_smtp_secure' ), 'tls' ); ?>>TLS (587)</option>
                                    <option value="" <?php selected( get_option( 'adp_smtp_secure' ), '' ); ?>>Ninguna</option>
                                </select>
                            </div>
                        </div>

                        <h3 class="hndle">Autenticación</h3>
                        <p>
                            <label class="adp-label-title" for="adp_smtp_user">Usuario:</label>
                            <input type="text" id="adp_smtp_user" name="adp_smtp_user" 
                                   value="<?php echo esc_attr( get_option( 'adp_smtp_user' ) ); ?>" 
                                   class="widefat">
                        </p>
                        <p>
                            <label class="adp-label-title" for="adp_smtp_pass">Contraseña:</label>
                            <input type="password" id="adp_smtp_pass" name="adp_smtp_pass" 
                                   value="<?php echo esc_attr( get_option( 'adp_smtp_pass' ) ); ?>" 
                                   class="widefat">
                        </p>

                        <div style="margin-top: 20px;">
                            <?php submit_button( 'Guardar Cambios', 'primary', 'submit', false, array( 'style' => 'width:100%' ) ); ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="postbox">
                <div class="postbox-header">
                    <h2 class="hndle">Prueba de Conexión</h2>
                </div>
                <div class="inside">
                    <p>Envía un correo de prueba a <strong><?php echo esc_html( wp_get_current_user()->user_email ); ?></strong>.</p>
                    <form method="post" action="">
                        <?php wp_nonce_field( 'adp_send_test_email', 'adp_test_email_nonce' ); ?>
                        <input type="submit" name="adp_test_email_submit" class="button button-secondary" value="Enviar Test" style="width: 100%;">
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>