<?php
/**
 * Template: Correo de Verificación (Double Opt-in)
 * Variables: $blog_name, $activation_link
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirma tu suscripción</title>
    <?php include ADP_PATH . 'templates/emails/email-styles.php'; ?>
</head>
<body>
    <div class="adp-email-container">
        
        <div class="adp-email-header">
            <h1><?php echo esc_html( $blog_name ); ?></h1>
        </div>

        <div class="adp-email-body">
            <h2 class="adp-post-title">¡Casi listo!</h2>
            
            <div class="adp-post-content">
                <p>Gracias por unirte a nuestra lista. Para empezar a recibir correos, necesitamos que confirmes que esta dirección es tuya.</p>
                
                <p style="text-align: center; margin: 30px 0;">
                    <a href="<?php echo esc_url( $activation_link ); ?>" class="adp-btn">Confirmar Suscripción</a>
                </p>
                
                <p style="font-size: 13px; color: #777;">Si no solicitaste esto, puedes ignorar este mensaje tranquilamente.</p>
            </div>
        </div>

        <div class="adp-email-footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo esc_html( $blog_name ); ?>. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>