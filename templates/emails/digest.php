<?php
/**
 * Template: Digest (Resumen Semanal)
 * Actualizado para soportar el Customizer y variables del AJAX.
 */

if ( ! isset( $is_preview ) ) {
    $is_preview = false;
}

if ( ! isset( $posts_list ) && isset( $posts ) ) {
    $posts_list = $posts;
}
?>

<?php if ( ! $is_preview ) : ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo esc_html( $email_title ?? 'Resumen Semanal' ); ?></title>
    <?php include ADP_PATH . 'templates/emails/email-styles.php'; ?>
</head>
<body>
<?php endif; ?>

    <div class="adp-email-wrapper">
        <div class="wrapper">
            <div style="height: 40px;"></div>
            
            <div class="container">
                
                <div class="header">
                    <h1>
                        <a href="<?php echo esc_url( home_url() ); ?>">
                            <?php 
                            $logo = get_option('adp_logo_url');
                            if ( ! empty( $logo ) ) {
                                echo '<img src="' . esc_url( $logo ) . '" alt="' . esc_attr( get_bloginfo('name') ) . '" style="max-height:50px; margin:0 auto;">';
                            } else {
                                echo esc_html( get_bloginfo( 'name' ) );
                            }
                            ?>
                        </a>
                    </h1>
                </div>

                <div class="email-meta">
                    <?php echo esc_html( $email_title ?? 'Resumen Semanal' ); ?>
                </div>

                <div class="content-body">
                    <?php if ( ! empty( $posts_list ) ) : ?>
                        <?php foreach ( $posts_list as $post ) : 
                            $p_id = is_object($post) ? $post->ID : $post['ID'];
                            $p_title = is_object($post) ? $post->post_title : $post['post_title'];
                            $p_excerpt = is_object($post) ? $post->post_excerpt : $post['post_excerpt'];
                            $p_link = get_permalink( $p_id );
                        ?>
                            <div class="post-item">
                                <h2 class="post-title">
                                    <a href="<?php echo esc_url( $p_link ); ?>">
                                        <?php echo esc_html( $p_title ); ?>
                                    </a>
                                </h2>
                                
                                <div class="post-excerpt">
                                    <?php echo wp_kses_post( $p_excerpt ); ?>
                                </div>
                                
                                <a href="<?php echo esc_url( $p_link ); ?>" class="btn-link">Leer más &rarr;</a>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p>No hay publicaciones nuevas en este periodo.</p>
                    <?php endif; ?>
                </div>

                <div class="footer">
                    <p><?php echo wp_kses_post( get_option( 'adp_footer_text', '© ' . date('Y') . ' Todos los derechos reservados.' ) ); ?></p>
                    <p><a href="<?php echo esc_url( $unsubscribe_link ?? '#' ); ?>">Darse de baja</a></p>
                </div>

            </div>
        </div>
    </div>
<?php if ( ! $is_preview ) : ?>
</body>
</html>
<?php endif; ?>