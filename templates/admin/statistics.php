<?php
/**
 * Template: Página de Estadísticas
 */
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Rendimiento y Estadísticas</h1>
    <hr class="wp-header-end">

    <div style="display: flex; gap: 20px; margin-bottom: 20px; margin-top: 20px;">
        <div style="flex: 1; background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,.04); text-align: center;">
            <div style="font-size: 14px; color: #646970; text-transform: uppercase; font-weight: 600;">Total de Aperturas</div>
            <div style="font-size: 36px; font-weight: 300; color: #2271b1; margin-top: 10px;">
                <span class="dashicons dashicons-visibility" style="font-size: 30px; vertical-align: baseline; margin-right: 5px;"></span>
                <?php echo esc_html((string)$totalOpens); ?>
            </div>
        </div>
        <div style="flex: 1; background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,.04); text-align: center;">
            <div style="font-size: 14px; color: #646970; text-transform: uppercase; font-weight: 600;">Total de Clics</div>
            <div style="font-size: 36px; font-weight: 300; color: #00a32a; margin-top: 10px;">
                <span class="dashicons dashicons-external" style="font-size: 30px; vertical-align: baseline; margin-right: 5px;"></span>
                <?php echo esc_html((string)$totalClicks); ?>
            </div>
        </div>
    </div>

    <div style="display: flex; gap: 20px; align-items: flex-start;">
        
        <div style="flex: 1; min-width: 400px;">
            <div class="postbox">
                <div class="postbox-header">
                    <h2 class="hndle">Top 10 Enlaces Más Clickeados</h2>
                </div>
                <div class="inside" style="padding: 0; margin: 0;">
                    <?php if (!empty($topLinks)) : ?>
                        <table class="wp-list-table widefat fixed striped" style="border: none;">
                            <thead>
                                <tr>
                                    <th>URL del Enlace</th>
                                    <th style="width: 100px; text-align: center;">Clics</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topLinks as $link) : ?>
                                    <tr>
                                        <td style="word-break: break-all;">
                                            <a href="<?php echo esc_url($link->url); ?>" target="_blank">
                                                <?php echo esc_html($link->url); ?>
                                            </a>
                                        </td>
                                        <td style="text-align: center; font-weight: bold;">
                                            <?php echo esc_html((string)$link->click_count); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <div style="padding: 20px; text-align: center; color: #666;">
                            Aún no hay clics registrados.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div style="flex: 1; min-width: 400px;">
            <div class="postbox">
                <div class="postbox-header">
                    <h2 class="hndle">Actividad Reciente (Últimos 50)</h2>
                </div>
                <div class="inside" style="padding: 0; margin: 0; max-height: 500px; overflow-y: auto;">
                    <?php if (!empty($recentEvents)) : ?>
                        <table class="wp-list-table widefat fixed striped" style="border: none;">
                            <thead>
                                <tr>
                                    <th style="width: 120px;">Fecha</th>
                                    <th>Suscriptor</th>
                                    <th style="width: 80px;">Evento</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentEvents as $event) : ?>
                                    <tr>
                                        <td style="font-size: 12px; color: #666;">
                                            <?php echo esc_html(wp_date('d/m/Y H:i', strtotime($event->created_at))); ?>
                                        </td>
                                        <td>
                                            <a href="mailto:<?php echo esc_attr($event->email); ?>">
                                                <?php echo esc_html($event->email); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php if ($event->event_type === 'open') : ?>
                                                <span style="color: #2271b1; background: #e5f5fa; padding: 2px 6px; border-radius: 3px; font-size: 11px; font-weight: 600;">APERTURA</span>
                                            <?php elseif ($event->event_type === 'click') : ?>
                                                <span style="color: #008a20; background: #dff6dd; padding: 2px 6px; border-radius: 3px; font-size: 11px; font-weight: 600;" title="<?php echo esc_attr($event->url); ?>">CLIC</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <div style="padding: 20px; text-align: center; color: #666;">
                            No hay actividad reciente.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>