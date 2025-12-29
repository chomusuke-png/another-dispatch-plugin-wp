<div class="adp-subscription-form">
    <?php if ( ! empty( $message ) ) echo $message; // Variable inyectada desde render_shortcode ?>
    <form method="post" action="">
        <?php wp_nonce_field( 'adp_save_sub', 'adp_nonce' ); ?>
        <p>
            <input type="email" name="adp_email" placeholder="Tu correo electrónico" required style="width: 100%; margin-bottom: 10px;">
        </p>
        <p>
            <input type="submit" name="adp_subscribe_submit" value="Suscribirse">
        </p>
    </form>
</div>