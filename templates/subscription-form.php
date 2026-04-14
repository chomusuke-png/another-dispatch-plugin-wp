<?php
/**
 * Template: Formulario Público de Suscripción (Shortcode & Widget)
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="adp-form-wrapper">
    
    <?php if (!empty($title)) : ?>
        <h3 class="adp-form-title"><?php echo esc_html($title); ?></h3>
    <?php endif; ?>

    <?php if (!empty($description)) : ?>
        <p class="adp-form-description"><?php echo esc_html($description); ?></p>
    <?php endif; ?>
    
    <?php if (!empty($messageHtml)) : ?>
        <?php echo wp_kses_post($messageHtml); ?>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url($formAction); ?>" class="adp-subscription-form">
        <input type="hidden" name="action" value="adp_subscribe_action">
        
        <?php wp_nonce_field('adp_subscribe_action', 'adp_subscribe_nonce'); ?>
        
        <div style="display:none; visibility:hidden; opacity:0; height:0;">
            <label for="adp_honey_check">Si eres humano, deja esto vacío:</label>
            <input type="text" name="adp_honey_check" id="adp_honey_check" value="" autocomplete="off" tabindex="-1">
        </div>

        <div class="adp-input-group">
            <input type="email" name="adp_email" class="adp-input-email" placeholder="Ingresa tu correo electrónico" required>
        </div>
        
        <div class="adp-input-group">
            <button type="submit" class="adp-submit-btn">Suscribirme Ahora</button>
        </div>

        <small class="adp-footer-text">No enviamos spam. Date de baja cuando quieras.</small>
    </form>
</div>