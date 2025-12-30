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
    <style>
        /* Estilos Reset & Base */
        body { margin: 0; padding: 0; background-color: #f4f4f4; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #555555; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f4f4f4; padding-bottom: 40px; }
        
        /* Contenedor Principal */
        .container { max-width: 600px; background-color: #ffffff; margin: 0 auto; border-radius: 4px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        
        /* Header */
        .header { background-color: #2271b1; padding: 30px 20px; text-align: center; }
        .header h1 { margin: 0; color: #ffffff; font-size: 24px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .header a { color: #ffffff; text-decoration: none; }
        
        /* Subtítulo del mes */
        .digest-title { background-color: #e5f6ff; color: #005b99; padding: 15px 20px; text-align: center; font-size: 16px; font-weight: 600; border-bottom: 1px solid #cceeff; }

        /* Lista de Posts */
        .post-list { padding: 20px; }
        .post-item { margin-bottom: 30px; border-bottom: 1px solid #eeeeee; padding-bottom: 25px; }
        .post-item:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        
        .post-img { width: 100%; height: auto; border-radius: 4px; margin-bottom: 15px; display: block; }
        .post-title { margin: 0 0 10px 0; font-size: 20px; line-height: 1.3; }
        .post-title a { color: #333333; text-decoration: none; }
        .post-excerpt { font-size: 14px; line-height: 1.6; color: #666666; margin-bottom: 15px; }
        .read-more { display: inline-block; font-size: 14px; color: #2271b1; text-decoration: none; font-weight: bold; }
        
        /* Footer */
        .footer { background-color: #f4f4f4; text-align: center; padding: 20px; font-size: 12px; color: #999999; }
        .footer a { color: #777777; text-decoration: underline; }
    </style>
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