<?php
/**
 * Template: Página de Herramientas y Disparadores Manuales
 */
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Herramientas y Pruebas</h1>
    <hr class="wp-header-end">

    <?php if (!empty($message)) : ?>
        <div class="notice notice-<?php echo esc_attr($messageType ?? 'success'); ?> is-dismissible">
            <p><?php echo esc_html($message); ?></p>
        </div>
    <?php endif; ?>

    <div class="adp-dashboard-wrapper" style="display: flex; gap: 20px; align-items: flex-start; margin-top: 20px;">
        
        <div style="flex: 1; min-width: 300px;">
            <div class="postbox">
                <div class="postbox-header">
                    <h2 class="hndle">Pruebas de Conexión</h2>
                </div>
                <div class="inside">
                    <div style="margin-bottom: 20px;">
                        <h3 style="margin: 0 0 10px; font-size: 13px;">Prueba SMTP (Envío)</h3>
                        <p style="font-size: 12px; color: #666; margin-bottom: 10px;">Verifica que las credenciales guardadas en Ajustes funcionen enviando un correo a tu cuenta de administrador.</p>
                        <form method="post" action="">
                            <?php wp_nonce_field('adp_send_test_email', 'adp_test_email_nonce'); ?>
                            <input type="hidden" name="adp_redirect_to" value="adp-tools">
                            <button type="submit" name="adp_test_email_submit" class="button button-secondary" style="width: 100%;">
                                <span class="dashicons dashicons-email" style="margin-top: 3px;"></span> Probar SMTP
                            </button>
                        </form>
                    </div>

                    <div style="border-top: 1px solid #eee; padding-top: 20px;">
                        <h3 style="margin: 0 0 10px; font-size: 13px;">Prueba IMAP (Rebotes)</h3>
                        <p style="font-size: 12px; color: #666; margin-bottom: 10px;">Verifica que el plugin puede autenticarse y leer la bandeja de correos rebotados.</p>
                        <form method="post" action="">
                            <?php wp_nonce_field('adp_test_imap_action', 'adp_test_imap_nonce'); ?>
                            <input type="hidden" name="adp_redirect_to" value="adp-tools">
                            <button type="submit" name="adp_test_imap_submit" class="button button-secondary" style="width: 100%;">
                                <span class="dashicons dashicons-update" style="margin-top: 3px;"></span> Probar IMAP
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div style="flex: 2; min-width: 400px;">
            <div class="postbox" style="border-color: #2271b1;">
                <div class="postbox-header" style="background: #f0f6fc; border-bottom-color: #c3e1ff;">
                    <h2 class="hndle" style="color: #005b9f;">Disparador Maestro</h2>
                </div>
                <div class="inside">
                    <p style="font-size: 13px; color: #555; margin-bottom: 15px;">
                        Fuerza el envío manual de un correo específico, ya sea para ver su aspecto final a nivel personal o para enviarlo a toda tu base de suscriptores activos.
                    </p>

                    <form method="post" action="" id="adp-unified-trigger-form">
                        <?php wp_nonce_field('adp_unified_trigger_action', 'adp_unified_trigger_nonce'); ?>
                        <input type="hidden" name="adp_redirect_to" value="adp-tools">

                        <div style="margin-bottom: 20px;">
                            <label style="font-weight: 600; display: block; margin-bottom: 5px;">1. ¿Qué correo quieres enviar?</label>
                            <select name="adp_trigger_type" class="widefat" style="max-width: 300px;">
                                <option value="welcome">Correo de Bienvenida</option>
                                <option value="post">Último Post (Inmediato)</option>
                                <option value="weekly">Resumen Semanal</option>
                                <option value="monthly">Resumen Mensual</option>
                            </select>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="font-weight: 600; display: block; margin-bottom: 10px;">2. ¿A quién se lo quieres enviar?</label>
                            
                            <label style="display: block; margin-bottom: 10px;">
                                <input type="radio" name="adp_trigger_target" value="single" checked onchange="toggleEmailInput(this.value)"> 
                                A un solo correo (Individual)
                            </label>
                            
                            <div id="adp_single_email_wrapper" style="margin: 5px 0 15px 25px;">
                                <input type="email" name="adp_single_email" placeholder="Ingresa un correo electrónico" style="width: 280px;" required>
                            </div>

                            <label style="display: block; margin-bottom: 10px;">
                                <input type="radio" name="adp_trigger_target" value="massive" onchange="toggleEmailInput(this.value)"> 
                                <strong style="color: #d63638;">A todos los suscriptores activos (Masivo)</strong>
                            </label>
                        </div>

                        <button type="submit" name="adp_unified_trigger_submit" class="button button-primary" style="padding: 0 30px;" onclick="return confirmTrigger();">
                            Ejecutar Envío
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    function toggleEmailInput(value) {
        const inputWrapper = document.getElementById('adp_single_email_wrapper');
        const emailInput = inputWrapper.querySelector('input');
        
        if (value === 'single') {
            inputWrapper.style.display = 'block';
            emailInput.setAttribute('required', 'required');
        } else {
            inputWrapper.style.display = 'none';
            emailInput.removeAttribute('required');
        }
    }

    function confirmTrigger() {
        const targetRadio = document.querySelector('input[name="adp_trigger_target"]:checked').value;
        const typeSelect = document.querySelector('select[name="adp_trigger_type"]').options[document.querySelector('select[name="adp_trigger_type"]').selectedIndex].text;
        
        if (targetRadio === 'massive') {
            return confirm(`¡ATENCIÓN! Estás a punto de enviar "${typeSelect}" a TODOS tus suscriptores de forma masiva.\n\n¿Estás completamente seguro de continuar?`);
        }
        
        return true;
    }
</script>