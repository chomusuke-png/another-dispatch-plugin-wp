<?php
/**
 * Template: Plantilla HTML para el Resumen Mensual (Digest).
 * * Variables disponibles: 
 * - $blog_name (string)
 * - $email_title (string)
 * - $posts_list (array de WP_Post)
 * - $unsubscribe_link (string)
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo esc_html( $email_title ); ?></title>
    <?php include ADP_PATH . 'templates/emails/email-styles.php'; ?>
</head>
<body>
    <div class="wrapper">
        <div style="height: 40px;"></div>
        
        <div class="container">
            <div class="header">
                <h1>
                    <a href="<?php echo esc_url( home_url() ); ?>">
                        <?php echo esc_html( $blog_name ); ?>
                    </a>
                </h1>
            </div>

            <div class="digest-title">
                <?php echo esc_html( $email_title ); ?>
            </div>

            <div class="post-list">
                <?php if ( ! empty( $posts_list ) ) : ?>
                    <?php foreach ( $posts_list as $post ) : ?>
                        <?php 
                            $link  = get_permalink( $post->ID );
                            $thumb = get_the_post_thumbnail_url( $post->ID, 'medium_large' ); 
                        ?>
                        <div class="post-item">
                            <?php if ( $thumb ) : ?>
                                <a href="<?php echo esc_url( $link ); ?>">
                                    <img src="<?php echo esc_url( $thumb ); ?>" alt="" class="post-img">
                                </a>
                            <?php endif; ?>
                            
                            <h2 class="post-title">
                                <a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $post->post_title ); ?></a>
                            </h2>
                            
                            <div class="post-excerpt">
                                <?php echo get_the_excerpt( $post ); ?>
                            </div>
                            
                            <a href="<?php echo esc_url( $link ); ?>" class="read-more">Leer artículo completo &rarr;</a>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p style="text-align: center;">No hubo publicaciones este mes.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="footer">
            <p>Recibiste este resumen porque estás suscrito a las novedades de <?php echo esc_html( $blog_name ); ?>.</p>
            <p>
                <a href="<?php echo esc_url( $unsubscribe_link ); ?>">Darse de baja</a>
            </p>
        </div>
    </div>
</body>
</html>