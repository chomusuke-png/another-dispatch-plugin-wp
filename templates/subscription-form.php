<div class="adp-form-wrapper">
    
    <?php if ( ! empty( $message ) ) : ?>
        <?php echo $message; ?>
    <?php endif; ?>

    <form method="post" action="">
        <?php wp_nonce_field( 'adp_save_sub', 'adp_nonce' ); ?>
        
        <div class="adp-input-group">
            <input type="email" name="adp_email" class="adp-input-email" placeholder="Ingresa tu correo electrónico" required>
        </div>
        
        <div class="adp-input-group">
            <input type="submit" name="adp_subscribe_submit" class="adp-submit-btn" value="Suscribirme Ahora">
        </div>

        <small class="adp-footer-text">No enviamos spam. Date de baja cuando quieras.</small>
    </form>
</div>