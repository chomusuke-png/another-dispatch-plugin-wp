<?php
/**
 * Template: Plantilla Single Post.
 * Soporta modo "Preview" para el Admin Customizer.
 */

if ( ! isset( $is_preview ) ) {
    $is_preview = false;
}
?>

<?php if ( ! $is_preview ) : ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo esc_html( $post_title ?? '' ); ?></title>
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
                            // Lógica de Logo vs Texto
                            $logo = get_option('adp_logo_url');
                            if ( ! empty( $logo ) ) {
                                echo '<img src="' . esc_url( $logo ) . '" alt="' . esc_attr( $blog_name ?? '' ) . '" style="max-height:50px; margin:0 auto;">';
                            } else {
                                echo esc_html( $blog_name ?? get_bloginfo('name') );
                            }
                            ?>
                        </a>
                    </h1>
                </div>

                <div class="email-meta">
                    Nueva publicación
                </div>

                <div class="content-body">
                    <?php if ( ! empty( $featured_image ) ) : ?>
                        <a href="<?php echo esc_url( $post_link ?? '#' ); ?>">
                            <img src="<?php echo esc_url( $featured_image ); ?>" alt="<?php echo esc_attr( $post_title ?? '' ); ?>" class="post-thumb">
                        </a>
                    <?php endif; ?>

                    <h2 class="post-title">
                        <a href="<?php echo esc_url( $post_link ?? '#' ); ?>"><?php echo esc_html( $post_title ?? 'Título del Post' ); ?></a>
                    </h2>

                    <div class="post-content">
                        <?php echo $post_content ?? '<p>Lorem ipsum dolor sit amet...</p>'; ?>
                    </div>

                    <div style="text-align: center; margin-top: 30px;">
                        <a href="<?php echo esc_url( $post_link ?? '#' ); ?>" class="btn-primary">Leer en la web</a>
                    </div>
                </div>
            </div>

            <div class="footer">
                <p><?php echo wp_kses_post( get_option( 'adp_footer_text', '© ' . date('Y') . ' Todos los derechos reservados.' ) ); ?></p>
                <p><a href="<?php echo esc_url( $unsubscribe_link ?? '#' ); ?>">Darse de baja de la lista</a></p>
            </div>
        </div>
    </div>

<?php if ( ! $is_preview ) : ?>
</body>
</html>
<?php endif; ?>