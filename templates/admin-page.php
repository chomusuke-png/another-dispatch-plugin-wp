<?php
/**
 * Template: Página de Administración de Suscriptores
 * Variables disponibles: $subscribers (array), $count (int), $message (string), $msg_type (string)
 */
?>
<div class="wrap">
    <h1>Lista de Suscriptores</h1>
    
    <?php if ( ! empty( $message ) ) : ?>
        <div class="notice notice-<?php echo !empty($msg_type) ? esc_attr($msg_type) : 'success'; ?> is-dismissible">
            <p><?php echo $message; // Permitimos HTML seguro generado en la clase ?></p>
        </div>
    <?php endif; ?>

    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
        <div class="card" style="max-width: 300px; margin-top: 20px; flex: 1;">
            <h2>Total Suscritos</h2>
            <p style="font-size: 2em; font-weight: bold; margin: 0; color: #2271b1;">
                <?php echo esc_html( $count ); ?>
            </p>
        </div>

        <div class="card" style="margin-top: 20px; flex: 2; min-width: 300px;">
            <h2>Configuración de Envío (SMTP)</h2>
            <form method="post" action="options.php">
                <?php settings_fields( 'adp_plugin_settings' ); ?>
                <?php do_settings_sections( 'adp_plugin_settings' ); ?>
                
                <h3 style="margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px;">Identidad</h3>
                <p>
                    <label for="adp_sender_email">Email del Remitente:</label><br>
                    <input type="email" id="adp_sender_email" name="adp_sender_email" 
                           value="<?php echo esc_attr( get_option( 'adp_sender_email' ) ); ?>" 
                           class="regular-text" placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" style="width: 100%;">
                </p>

                <h3 style="margin-top: 20px; margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px;">Servidor SMTP</h3>
                <p>
                    <label for="adp_smtp_host">Servidor (Host):</label><br>
                    <input type="text" id="adp_smtp_host" name="adp_smtp_host" 
                           value="<?php echo esc_attr( get_option( 'adp_smtp_host' ) ); ?>" 
                           class="regular-text" placeholder="ej. smtp.gmail.com" style="width: 100%;">
                </p>
                
                <div style="display: flex; gap: 10px;">
                    <p style="flex: 1;">
                        <label for="adp_smtp_port">Puerto:</label><br>
                        <input type="number" id="adp_smtp_port" name="adp_smtp_port" 
                        value="<?php echo esc_attr( get_option( 'adp_smtp_port', '587' ) ); ?>" 
                               class="small-text">
                    </p>
                    <p style="flex: 1;">
                        <label for="adp_smtp_secure">Encriptación:</label><br>
                        <select id="adp_smtp_secure" name="adp_smtp_secure">
                            <option value="tls" <?php selected( get_option( 'adp_smtp_secure' ), 'tls' ); ?>>TLS (Recomendado)</option>
                            <option value="ssl" <?php selected( get_option( 'adp_smtp_secure' ), 'ssl' ); ?>>SSL</option>
                            <option value="" <?php selected( get_option( 'adp_smtp_secure' ), '' ); ?>>Ninguna</option>
                        </select>
                    </p>
                </div>
                <p>465 (recomendado con SSL) o 587 (recomendado con TLS)</p>
                        
                <h3 style="margin-top: 20px; margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px;">Credenciales</h3>
                <p>
                    <label for="adp_smtp_user">Usuario SMTP:</label><br>
                    <input type="text" id="adp_smtp_user" name="adp_smtp_user" 
                           value="<?php echo esc_attr( get_option( 'adp_smtp_user' ) ); ?>" 
                           class="regular-text" style="width: 100%;">
                </p>
                <p>
                    <label for="adp_smtp_pass">Contraseña SMTP:</label><br>
                    <input type="password" id="adp_smtp_pass" name="adp_smtp_pass" 
                           value="<?php echo esc_attr( get_option( 'adp_smtp_pass' ) ); ?>" 
                           class="regular-text" style="width: 100%;">
                </p>
                
                <?php submit_button( 'Guardar Configuración SMTP' ); ?>
            </form>

            <hr style="margin: 20px 0; border: 0; border-top: 1px solid #ddd;">
            
            <h3>Prueba de conexión</h3>
            <p>Se enviará un correo de prueba a: <strong><?php echo esc_html( wp_get_current_user()->user_email ); ?></strong></p>
            
            <form method="post" action="">
                <?php wp_nonce_field( 'adp_send_test_email', 'adp_test_email_nonce' ); ?>
                <input type="submit" name="adp_test_email_submit" id="adp_test_email_submit" class="button button-secondary" value="Enviar Correo de Prueba">
            </form>
        </div>
    </div>

    <br>

    <table class="wp-list-table widefat fixed striped table-view-list">
        <thead>
            <tr>
                <th style="width: 50px;">ID</th>
                <th>Email</th>
                <th>Fecha de Suscripción</th>
                <th style="width: 100px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! empty( $subscribers ) ) : ?>
                <?php foreach ( $subscribers as $sub ) : ?>
                    <tr>
                        <td><?php echo esc_html( $sub['id'] ); ?></td>
                        <td>
                            <strong><?php echo esc_html( $sub['email'] ); ?></strong>
                        </td>
                        <td><?php echo esc_html( $sub['created_at'] ); ?></td>
                        <td>
                            <?php 
                            $delete_url = wp_nonce_url( 
                                admin_url( 'admin.php?page=another-dispatch-plugin&action=adp_delete_subscriber&subscriber_id=' . $sub['id'] ), 
                                'adp_delete_subscriber_action' 
                            ); 
                            ?>
                            <a href="<?php echo esc_url( $delete_url ); ?>" 
                               class="button button-small button-link-delete" 
                               onclick="return confirm('¿Estás seguro de que quieres eliminar a este suscriptor?');">
                                Borrar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="4">No hay suscriptores todavía.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>