<?php
/**
 * Template: Página de Administración de Suscriptores
 * Variables disponibles: $subscribers (array), $count (int), $message (string)
 */
?>
<div class="wrap">
    <h1>Lista de Suscriptores</h1>
    
    <?php if ( ! empty( $message ) ) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html( $message ); ?></p>
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
            <h2>Configuración de Envío</h2>
            <form method="post" action="options.php">
                <?php settings_fields( 'adp_plugin_settings' ); ?>
                <?php do_settings_sections( 'adp_plugin_settings' ); ?>
                
                <p>
                    <label for="adp_sender_email"><strong>Email del Remitente (From):</strong></label><br>
                    <input type="email" id="adp_sender_email" name="adp_sender_email" 
                           value="<?php echo esc_attr( get_option( 'adp_sender_email' ) ); ?>" 
                           class="regular-text" 
                           placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" 
                           style="width: 100%; max-width: 400px; margin-top: 5px;">
                </p>
                <p class="description">
                    Este es el correo que verán tus suscriptores. <br>
                    <em>Si lo dejas vacío, se usará: <?php echo esc_html( get_option( 'admin_email' ) ); ?></em>
                </p>
                
                <?php submit_button( 'Guardar Configuración' ); ?>
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