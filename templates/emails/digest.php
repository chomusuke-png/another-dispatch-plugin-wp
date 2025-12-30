<?php
/**
 * Template: Plantilla para envío MENSUAL (Digest).
 * Variables: $blog_name, $email_title, $posts_list (array), $unsubscribe_link
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
                <h1><a href="<?php echo esc_url( home_url() ); ?>"><?php echo esc_html( $blog_name ); ?></a></h1>
            </div>

            <div class="email-meta">
                <?php echo esc_html( $email_title ); ?>
            </div>

            <div class="content-body">
                <?php if ( ! empty( $posts_list ) ) : ?>
                    <?php foreach ( $posts_list as $post ) : ?>
                        <?php 
                            $link  = get_permalink( $post->ID );
                            $thumb = get_the_post_thumbnail_url( $post->ID, 'medium_large' ); 
                        ?>
                        <div class="post-item">
                            <?php if ( $thumb ) : ?>
                                <a href="<?php echo esc_url( $link ); ?>">
                                    <img src="<?php echo esc_url( $thumb ); ?>" class="post-thumb" alt="">
                                </a>
                            <?php endif; ?>
                            
                            <h2 class="post-title">
                                <a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $post->post_title ); ?></a>
                            </h2>
                            
                            <div class="post-excerpt">
                                <?php echo get_the_excerpt( $post ); ?>
                            </div>
                            
                            <a href="<?php echo esc_url( $link ); ?>" class="btn-link">Leer artículo completo &rarr;</a>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p style="text-align: center;">No hubo publicaciones este mes.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="footer">
            <p>Este es tu resumen mensual de <strong><?php echo esc_html( $blog_name ); ?></strong>.</p>
            <p><a href="<?php echo esc_url( $unsubscribe_link ); ?>">Darse de baja</a></p>
        </div>
    </div>
</body>
</html>