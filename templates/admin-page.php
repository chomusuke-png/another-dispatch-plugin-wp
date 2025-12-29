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

    <div class="card" style="max-width: 300px; margin-top: 20px;">
        <h2>Total Suscritos</h2>
        <p style="font-size: 2em; font-weight: bold; margin: 0; color: #2271b1;">
            <?php echo esc_html( $count ); ?>
        </p>
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
                            // Generar URL segura de borrado con Nonce
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