<?php
/**
 * Template: Layout Maestro de Correo.
 */

if ( ! isset( $is_preview ) ) $is_preview = false;
$blog_name = get_bloginfo( 'name' );
?>

<?php if ( ! $is_preview ) : ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo esc_html( $email_title ?? $blog_name ); ?></title>
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
                                echo '<img src="' . esc_url( $logo ) . '" alt="' . esc_attr( $blog_name ) . '" style="max-height:50px; margin:0 auto;">';
                            } else {
                                echo esc_html( $blog_name );
                            }
                            ?>
                        </a>
                    </h1>
                </div>

                <div class="email-meta">
                    <?php echo esc_html( $email_title ?? 'Nueva Publicación' ); ?>
                </div>

                <div class="content-body">
                    <?php echo $email_content; ?>
                </div>

                <div class="footer">
                    <p><?php echo wp_kses_post( get_option( 'adp_footer_text', '© ' . date('Y') . ' Todos los derechos reservados.' ) ); ?></p>
                    <p><a href="<?php echo esc_url( $unsubscribe_link ?? '#' ); ?>">Darse de baja de la lista</a></p>
                </div>
            </div>
        </div>
    </div>

<?php if ( ! $is_preview ) : ?>
</body>
</html>
<?php endif; ?>