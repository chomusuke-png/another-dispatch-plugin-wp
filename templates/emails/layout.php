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

$blogName   = $blogName ?? get_bloginfo('name');
$emailTitle = $emailTitle ?? 'Notificación';
$bgHeader   = get_option('adp_color_header_bg', '#2271b1');
$txtHeader  = get_option('adp_color_header_text', '#ffffff');
$bgBody     = get_option('adp_body_bg', '#f0f0f1');
?>

<?php if (!$isPreview) : ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($emailTitle); ?></title>
<?php endif; ?>

    <?php require ADP_PATH . 'templates/emails/email-styles.php'; ?>

<?php if (!$isPreview) : ?>
</head>
<body style="margin: 0; padding: 0; background-color: <?php echo esc_attr($bgBody); ?>;">
<?php endif; ?>

    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="adp-email-wrapper">
        <tr>
            <td align="center" style="background-color: <?php echo esc_attr($bgBody); ?>; padding: 20px 0;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="container" style="max-width: 600px; background-color: #ffffff; border-radius: 4px; overflow: hidden; font-family: sans-serif;">
                    
                    <tr>
                        <td align="center" bgcolor="<?php echo esc_attr($bgHeader); ?>" style="padding: 30px 20px;">
                            <a href="<?php echo esc_url(home_url()); ?>" target="_blank" style="text-decoration: none;">
                                <?php 
                                $logoUrl = get_option('adp_logo_url');
                                if (!empty($logoUrl)) : ?>
                                    <img src="<?php echo esc_url($logoUrl); ?>" alt="<?php echo esc_attr($blogName); ?>" border="0" style="display: block; max-height: 50px; margin: 0 auto;">
                                <?php else : ?>
                                    <span style="font-size: 24px; font-weight: bold; color: <?php echo esc_attr($txtHeader); ?>;">
                                        <?php echo esc_html($blogName); ?>
                                    </span>
                                    <?php endif; ?>
                                </a>
                            </td>
                        </tr>
                        
                        <tr>
                            <td align="center" style="padding: 15px 20px; background-color: #e5f6ff; border-bottom: 1px solid #cceeff; font-size: 15px; font-weight: bold; color: #005b99;">
                                <marquee behavior="" direction="right">
                                    <?php echo esc_html($emailTitle); ?>
                                </marquee>
                            </td>
                        </tr>

                    <tr>
                        <td style="padding: 40px 30px; font-size: 16px; line-height: 1.6; color: #555555;">
                            <?php echo $emailContent ?? ''; ?>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding: 30px; background-color: #f8f8f8; border-top: 1px solid #eeeeee; font-size: 12px; color: #999999;">
                            <p style="margin: 0 0 10px 0;">
                                <?php echo wp_kses_post(get_option('adp_footer_text', '© ' . gmdate('Y') . ' ' . $blogName)); ?>
                            </p>
                            <?php if (!empty($unsubscribeLink) && $unsubscribeLink !== '#') : ?>
                                <p style="margin: 0;">
                                    <a href="<?php echo esc_url($unsubscribeLink); ?>" style="color: #777777; text-decoration: underline;">Darse de baja</a>
                                </p>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                </td>
        </tr>
    </table>

<?php if (!$isPreview) : ?>
</body>
</html>
<?php endif; ?>