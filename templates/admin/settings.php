<?php
/**
 * Template: Página de Configuración (Ajustes)
 */
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>Ajustes de Dispatch</h1>
    <hr class="wp-header-end">

    <?php if (!empty($message)) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html($message); ?></p>
        </div>
    <?php endif; ?>

    <form method="post" action="options.php">
        <?php settings_fields('adp_plugin_settings'); ?>
        
        <div class="adp-dashboard-wrapper" style="display: flex; gap: 20px; align-items: flex-start; margin-top: 20px;">
            <div class="adp-main-column" style="flex: 2; min-width: 400px;">
                <div class="postbox">
                    <div class="postbox-header"><h2 class="hndle">Configuración SMTP</h2></div>
                    <div class="inside">
                        <p>
                            <label class="adp-label-title" for="adp_sender_email">Remitente (From Email):</label>
                            <input type="email" id="adp_sender_email" name="adp_sender_email" value="<?php echo esc_attr(get_option('adp_sender_email')); ?>" class="widefat" placeholder="<?php echo esc_attr(get_option('admin_email')); ?>">
                            <span class="description">Debe coincidir con el usuario SMTP para evitar SPAM.</span>
                        </p>
                        <hr style="margin: 20px 0; border-top: 1px solid #f0f0f1;">
                        <p>
                            <label class="adp-label-title" for="adp_smtp_host">Host SMTP:</label>
                            <input type="text" id="adp_smtp_host" name="adp_smtp_host" value="<?php echo esc_attr(get_option('adp_smtp_host')); ?>" class="widefat">
                        </p>
                        <div style="display: flex; gap: 20px;">
                            <div style="flex: 1;">
                                <label class="adp-label-title">Puerto:</label>
                                <input type="number" name="adp_smtp_port" value="<?php echo esc_attr(get_option('adp_smtp_port', '587')); ?>" class="widefat">
                            </div>
                            <div style="flex: 1;">
                                <label class="adp-label-title">Seguridad:</label>
                                <select name="adp_smtp_secure" class="widefat">
                                    <option value="ssl" <?php selected(get_option('adp_smtp_secure'), 'ssl'); ?>>SSL</option>
                                    <option value="tls" <?php selected(get_option('adp_smtp_secure'), 'tls'); ?>>TLS</option>
                                    <option value="" <?php selected(get_option('adp_smtp_secure'), ''); ?>>Ninguna</option>
                                </select>
                            </div>
                        </div>
                        <p>
                            <label class="adp-label-title">Usuario SMTP:</label>
                            <input type="text" name="adp_smtp_user" value="<?php echo esc_attr(get_option('adp_smtp_user')); ?>" class="widefat">
                        </p>
                        <p>
                            <label class="adp-label-title">Contraseña SMTP:</label>
                            <input type="password" name="adp_smtp_pass" value="<?php echo esc_attr(get_option('adp_smtp_pass')); ?>" class="widefat">
                        </p>
                    </div>
                </div>

                <div class="postbox">
                    <div class="postbox-header"><h2 class="hndle">Gestión de Rebotes (IMAP)</h2></div>
                    <div class="inside">
                        <p class="description" style="margin-bottom: 15px; font-style: italic;">
                            Configura un buzón dedicado para recibir correos devueltos. El plugin revisará esta bandeja periódicamente para marcar los correos no existentes como rebotados (bounced).
                        </p>
                        <p>
                            <label class="adp-label-title" for="adp_bounce_email">Buzón de Rebotes (Return-Path Email):</label>
                            <input type="email" id="adp_bounce_email" name="adp_bounce_email" value="<?php echo esc_attr(get_option('adp_bounce_email')); ?>" class="widefat" placeholder="ej: rebotes@tudominio.com">
                            <span class="description">Los correos fallidos regresarán aquí. Si lo dejas vacío, los rebotes llegarán al Remitente (From Email).</span>
                        </p>
                        <hr style="margin: 20px 0; border-top: 1px solid #f0f0f1;">
                        <p>
                            <label class="adp-label-title" for="adp_imap_host">Host IMAP:</label>
                            <input type="text" id="adp_imap_host" name="adp_imap_host" value="<?php echo esc_attr(get_option('adp_imap_host')); ?>" class="widefat">
                        </p>
                        <p>
                            <label class="adp-label-title">Puerto IMAP:</label>
                            <input type="number" name="adp_imap_port" value="<?php echo esc_attr(get_option('adp_imap_port', '993')); ?>" class="widefat">
                        </p>
                        <p>
                            <label class="adp-label-title">Usuario IMAP:</label>
                            <input type="text" name="adp_imap_user" value="<?php echo esc_attr(get_option('adp_imap_user')); ?>" class="widefat" placeholder="ej: rebotes@tudominio.com">
                        </p>
                        <p>
                            <label class="adp-label-title">Contraseña IMAP:</label>
                            <input type="password" name="adp_imap_pass" value="<?php echo esc_attr(get_option('adp_imap_pass')); ?>" class="widefat">
                        </p>
                    </div>
                </div>

                <div style="margin-top: 20px;">
                    <?php submit_button('Guardar Configuración', 'primary', 'submit', false, ['style' => 'width: 100%; padding: 10px;']); ?>
                </div>
            </div>

            <div class="adp-sidebar-column" style="flex: 1; min-width: 300px;">
                <div class="postbox">
                    <div class="postbox-header"><h2 class="hndle">Modo de Envío</h2></div>
                    <div class="inside">
                        <?php $frequency = get_option('adp_delivery_frequency', 'instant'); ?>
                        
                        <div class="adp-radio-group">
                            <label class="adp-radio-option" style="margin-bottom: 10px; display: block;">
                                <input type="radio" name="adp_delivery_frequency" value="instant" <?php checked($frequency, 'instant'); ?>> 
                                <span><strong>Inmediato</strong><br><small style="color:#666; margin-left: 25px; display:block;">Enviar notificación al publicar.</small></span>
                            </label>
                            
                            <label class="adp-radio-option" style="margin-bottom: 10px; display: block;">
                                <input type="radio" name="adp_delivery_frequency" value="weekly" <?php checked($frequency, 'weekly'); ?>> 
                                <span><strong>Resumen Semanal</strong><br><small style="color:#666; margin-left: 25px; display:block;">Recompilación todos los lunes (posts de la semana anterior).</small></span>
                            </label>
                            
                            <label class="adp-radio-option" style="margin-bottom: 10px; display: block;">
                                <input type="radio" name="adp_delivery_frequency" value="monthly" <?php checked($frequency, 'monthly'); ?>> 
                                <span><strong>Resumen Mensual</strong><br><small style="color:#666; margin-left: 25px; display:block;">Recopilación de posts del mes.</small></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="postbox">
                    <div class="postbox-header"><h2 class="hndle">Rendimiento (Throttle)</h2></div>
                    <div class="inside">
                        <p class="description" style="margin-bottom: 15px; font-style: italic;">Ajusta esto según los límites de tu hosting.</p>
                        <p>
                            <label class="adp-label-title">Correos por Lote:</label>
                            <input type="number" name="adp_batch_size" value="<?php echo esc_attr(get_option('adp_batch_size', 50)); ?>" class="widefat" min="1" max="500">
                        </p>
                        <p>
                            <label class="adp-label-title">Pausa (Segundos):</label>
                            <input type="number" name="adp_batch_delay" value="<?php echo esc_attr(get_option('adp_batch_delay', 5)); ?>" class="widefat" min="0">
                        </p>
                    </div>
                </div>

                <div class="postbox">
                    <div class="postbox-header"><h2 class="hndle">Limpieza Automática</h2></div>
                    <div class="inside">
                        <p class="description" style="margin-bottom: 15px; font-style: italic;">Elimina suscripciones pendientes antiguas.</p>
                        <p>
                            <label class="adp-label-title">Días para expirar pendientes:</label>
                            <input type="number" name="adp_pending_expiration_days" value="<?php echo esc_attr(get_option('adp_pending_expiration_days', 30)); ?>" class="widefat" min="0">
                            <span class="description" style="display:block; margin-top:5px; font-size: 12px;">Ingresa 0 para desactivar la limpieza. Por ejemplo, con el valor 7 se eliminarán los correos no verificados tras una semana.</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>