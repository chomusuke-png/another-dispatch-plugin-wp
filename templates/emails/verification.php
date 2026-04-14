<?php
/**
 * Template: Correo de Verificación (Double Opt-in)
 * Utiliza el layout maestro para heredar los estilos del customizer.
 */
if (!defined('ABSPATH')) {
    exit;
}

ob_start(); 
?>

<h2 class="post-title" style="text-align: center;">¡Casi listo!</h2>
            
<div class="post-content">
    <p style="text-align: center;">Gracias por unirte a nuestra lista. Para empezar a recibir correos, necesitamos que confirmes que esta dirección es tuya.</p>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="<?php echo esc_url($verificationLink ?? '#'); ?>" class="btn-primary">Confirmar Suscripción</a>
    </div>
    
    <p style="font-size: 13px; color: #777; text-align: center;">Si no solicitaste esto, puedes ignorar este mensaje tranquilamente.</p>
</div>

<?php
$emailContent = ob_get_clean();
$emailTitle = 'Confirma tu suscripción';

// Forzamos a ocultar el enlace de "Darse de baja" en el correo de confirmación
$unsubscribeLink = ''; 

require ADP_PATH . 'templates/emails/layout.php';
?>