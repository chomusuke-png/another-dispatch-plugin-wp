<?php
/**
 * Template: Layout Maestro de Correo.
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!isset($isPreview)) {
    $isPreview = false;
}

$blogName = $blogName ?? get_bloginfo('name');
$emailTitle = $emailTitle ?? 'Notificación';
?>

<?php if (!$isPreview) : ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo esc_html($emailTitle); ?></title>
    <?php require ADP_PATH . 'templates/emails/email-styles.php'; ?>
</head>
<body>
<?php endif; ?>

    <div class="adp-email-wrapper">
        <div class="wrapper">
            <div style="height: 40px;"></div>
            
            <div class="container">
                <div class="header">
                    <h1>
                        <a href="<?php echo esc_url(home_url()); ?>">
                            <?php 
                            $logoUrl = get_option('adp_logo_url');
                            if (!empty($logoUrl)) {
                                echo '<img src="' . esc_url($logoUrl) . '" alt="' . esc_attr($blogName) . '" style="max-height:50px; margin:0 auto;">';
                            } else {
                                echo esc_html($blogName);
                            }
                            ?>
                        </a>
                    </h1>
                </div>

                <div class="email-meta adp-scrolling-container">
                    <span class="adp-scrolling-text"><?php echo esc_html($emailTitle); ?></span>
                </div>

                <div class="content-body">
                    <?php echo $emailContent ?? ''; ?>
                </div>

                <div class="footer">
                    <p><?php echo wp_kses_post(get_option('adp_footer_text', '© ' . gmdate('Y') . ' Todos los derechos reservados.')); ?></p>
                    <?php if (!empty($unsubscribeLink) && $unsubscribeLink !== '#') : ?>
                        <p><a href="<?php echo esc_url($unsubscribeLink); ?>">Darse de baja de la lista</a></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php if (!$isPreview) : ?>
</body>
</html>
<?php endif; ?>