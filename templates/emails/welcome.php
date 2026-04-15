<?php
/**
 * Template: Correo de Bienvenida
 * Utiliza el layout maestro para heredar los estilos del customizer.
 */
if (!defined('ABSPATH')) {
    exit;
}

ob_start(); 
?>

<div class="post-content" style="text-align: center;">
    <?php echo $welcomeContent ?? '<p>¡Gracias por confirmar tu suscripción!</p>'; ?>
</div>

<div style="text-align: center; margin-top: 30px;">
    <a href="<?php echo esc_url(home_url()); ?>" class="btn-primary">Visitar el sitio web</a>
</div>

<?php
$emailContent = ob_get_clean();
$emailTitle = $emailTitle ?? '¡Bienvenido!';

require ADP_PATH . 'templates/emails/layout.php';
?>