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
    <style>
        /* Estilos básicos de reseteo para clientes de correo */
        body { margin: 0; padding: 0; background-color: #f6f6f6; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
        .container { width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 5px; }
        .header { text-align: center; padding-bottom: 20px; border-bottom: 1px solid #eeeeee; }
        .header h1 { margin: 0; color: #333333; font-size: 24px; }
        .content { padding: 20px 0; line-height: 1.6; color: #555555; }
        .content h2 { color: #333333; }
        .content img { max-width: 100%; height: auto; border-radius: 4px; }
        .button { display: inline-block; padding: 10px 20px; background-color: #0073aa; color: #ffffff; text-decoration: none; border-radius: 3px; margin-top: 20px; }
        .footer { text-align: center; font-size: 12px; color: #999999; margin-top: 20px; border-top: 1px solid #eeeeee; padding-top: 10px; }
    </style>
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