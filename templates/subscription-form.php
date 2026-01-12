<div class="adp-form-wrapper">
    
    <?php if ( ! empty( $message ) ) : ?>
        <?php echo $message; ?>
    <?php endif; ?>

    <form method="post" action="">
        <?php wp_nonce_field( 'adp_save_sub', 'adp_nonce' ); ?>
        
        <div style="display:none; visibility:hidden; opacity:0; height:0;">
            <label for="adp_h_check">Si eres humano, deja esto vacío:</label>
            <input type="text" name="adp_honey_check" id="adp_h_check" value="" autocomplete="off" tabindex="-1">
        </div>

        <div class="adp-input-group">
            <input type="email" name="adp_email" class="adp-input-email" placeholder="Ingresa tu correo electrónico" required>
        </div>
        
        <div class="adp-input-group">
            <input type="submit" name="adp_subscribe_submit" class="adp-submit-btn" value="Suscribirme Ahora">
        </div>

        <small class="adp-footer-text">No enviamos spam. Date de baja cuando quieras.</small>
    </form>
</div>