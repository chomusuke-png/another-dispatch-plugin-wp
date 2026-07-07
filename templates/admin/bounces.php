<?php
/**
 * Template: Rebotes en Revisión Manual
 */
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Rebotes en Revisión</h1>
    <hr class="wp-header-end">

    <?php if (!empty($message)) : ?>
        <div class="notice notice-<?php echo esc_attr($messageType ?? 'success'); ?> is-dismissible">
            <p><?php echo esc_html($message); ?></p>
        </div>
    <?php endif; ?>

    <p style="max-width: 800px; color: #555;">
        Estos son los correos recibidos en el buzón de rebotes que el sistema <strong>no pudo interpretar automáticamente</strong>
        (formato de rebote desconocido, correo de otro tipo, etc.). Revísalos y decide manualmente qué hacer con el suscriptor
        asociado antes de que el mensaje se elimine del buzón.
    </p>

    <?php if (empty($pendingMessages)) : ?>
        <div class="notice notice-info" style="margin-top: 20px;">
            <p>No hay mensajes pendientes de revisión.</p>
        </div>
    <?php else : ?>
        <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 16px;">
            <?php foreach ($pendingMessages as $item) : ?>
                <div class="postbox" style="margin: 0;">
                    <div class="postbox-header">
                        <h2 class="hndle"><?php echo esc_html($item['subject']); ?></h2>
                    </div>
                    <div class="inside">
                        <p style="font-size: 12px; color: #666; margin: 0 0 10px;">
                            <strong>De:</strong> <?php echo esc_html($item['from'] ?: '(desconocido)'); ?>
                            &nbsp;|&nbsp;
                            <strong>Fecha:</strong> <?php echo esc_html($item['date'] ?: '(desconocida)'); ?>
                        </p>

                        <?php if ($item['snippet'] !== '') : ?>
                            <pre style="background:#f6f7f7; padding:10px; font-size:11px; white-space:pre-wrap; max-height:150px; overflow:auto; margin: 0 0 15px;"><?php echo esc_html($item['snippet']); ?></pre>
                        <?php endif; ?>

                        <form method="post" action="" style="display:flex; align-items:flex-end; gap:10px; flex-wrap:wrap;">
                            <?php wp_nonce_field('adp_bounce_review_action', 'adp_bounce_review_nonce'); ?>
                            <input type="hidden" name="adp_redirect_to" value="adp-bounces">
                            <input type="hidden" name="adp_bounce_uid" value="<?php echo esc_attr((string) $item['uid']); ?>">

                            <div>
                                <label style="display:block; font-weight:600; font-size:12px; margin-bottom:4px;">Correo del suscriptor afectado:</label>
                                <input type="email" name="adp_bounce_email" value="<?php echo esc_attr($item['guessedEmail']); ?>" placeholder="correo@dominio.com" style="width:280px;">
                            </div>

                            <button type="submit" name="adp_bounce_decision" value="bounced" class="button button-primary">
                                Marcar como rebotado
                            </button>
                            <button type="submit" name="adp_bounce_decision" value="active" class="button button-secondary">
                                Reactivar en lista activa
                            </button>
                            <button type="submit" name="adp_bounce_decision" value="discard" class="button" style="color:#d63638;" onclick="return confirm('¿Descartar este mensaje sin cambiar el estado de ningún suscriptor?');">
                                Descartar
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
