<?php
/**
 * Template: Plantilla HTML para el correo de notificación.
 * Variables disponibles: $blog_name, $post_title, $post_content, $post_link, $featured_image
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo esc_html( $post_title ); ?></title>
    <?php include ADP_PATH . 'templates/emails/email-styles.php'; ?>
</head>
<body>
    <div style="background-color: #f6f6f6; padding: 40px 0;">
        <div class="container">
            
            <div class="header">
                <h1>
                    <a href="<?php echo esc_url( home_url() ); ?>" style="text-decoration: none; color: #333333;">
                        <?php echo esc_html( $blog_name ); ?>
                    </a>
                </h1>
            </div>

            <div class="content">
                <?php if ( ! empty( $featured_image ) ) : ?>
                    <div style="text-align: center; margin-bottom: 20px;">
                        <img src="<?php echo esc_url( $featured_image ); ?>" alt="<?php echo esc_attr( $post_title ); ?>">
                    </div>
                <?php endif; ?>

                <h2 style="margin-top: 0;"><?php echo esc_html( $post_title ); ?></h2>

                <div>
                    <?php echo $post_content; ?>
                </div>

                <div style="text-align: center;">
                    <a href="<?php echo esc_url( $post_link ); ?>" class="button">Ir a ver</a>
                </div>
            </div>

            <div class="footer">
                <p>Recibiste este correo porque estás suscrito a <?php echo esc_html( $blog_name ); ?>.</p>
                
                <p style="margin-top: 10px; font-size: 11px; color: #aaaaaa;">
                    ¿Recibiste el correo por error o ya no quieres recibir noticias? 
                    <a href="<?php echo esc_url( $unsubscribe_link ); ?>" style="color: #999999; text-decoration: underline;">
                        Haz clic aquí para desuscribirte
                    </a>.
                </p>
            </div>
        </div>
    </div>
</body>
</html>