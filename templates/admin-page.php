<?php
/**
 * Template: Página de Administración de Suscriptores
 * Variables disponibles: $subscribers (array), $count (int)
 */
?>
<div class="wrap">
    <h1>Lista de Suscriptores</h1>
    
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
                <th>ID</th>
                <th>Email</th>
                <th>Fecha de Suscripción</th>
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
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="3">No hay suscriptores todavía.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>