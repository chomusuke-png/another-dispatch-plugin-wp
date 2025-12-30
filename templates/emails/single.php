<?php
/**
 * Template: Plantilla para envío INMEDIATO (Single Post).
 * Variables: $blog_name, $post_title, $post_content, $post_link, $featured_image, $unsubscribe_link
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
    <div class="wrapper">
        <div style="height: 40px;"></div>
        
        <div class="container">
            <div class="header">
                <h1><a href="<?php echo esc_url( home_url() ); ?>"><?php echo esc_html( $blog_name ); ?></a></h1>
            </div>

            <div class="email-meta">
                Nueva publicación
            </div>

            <div class="content-body">
                <?php if ( ! empty( $featured_image ) ) : ?>
                    <a href="<?php echo esc_url( $post_link ); ?>">
                        <img src="<?php echo esc_url( $featured_image ); ?>" alt="<?php echo esc_attr( $post_title ); ?>" class="post-thumb">
                    </a>
                <?php endif; ?>

                <h2 class="post-title">
                    <a href="<?php echo esc_url( $post_link ); ?>"><?php echo esc_html( $post_title ); ?></a>
                </h2>

                <div class="post-content">
                    <?php echo $post_content; ?>
                </div>

                <div style="text-align: center; margin-top: 30px;">
                    <a href="<?php echo esc_url( $post_link ); ?>" class="btn-primary">Leer en la web</a>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>Estás recibiendo este correo porque te suscribiste a <strong><?php echo esc_html( $blog_name ); ?></strong>.</p>
            <p><a href="<?php echo esc_url( $unsubscribe_link ); ?>">Darse de baja de la lista</a></p>
        </div>
    </div>
</body>
</html>