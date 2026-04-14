<?php
/**
 * Partial: Estilos CSS dinámicos y estructura base.
 * Soporta CSS Variables para Live Preview y Hex para clientes de correo antiguos.
 */
if (!defined('ABSPATH')) {
    exit;
}

$bgHeader  = get_option('adp_color_header_bg', '#2271b1');
$txtHeader = get_option('adp_color_header_text', '#ffffff');
$bgBody    = get_option('adp_body_bg', '#f0f0f1');
$bgBtn     = get_option('adp_color_btn_bg', '#2271b1');
$txtBtn    = get_option('adp_color_btn_text', '#ffffff');
$colLink   = get_option('adp_color_links', '#2271b1');

$rootSelector = (isset($isPreview) && $isPreview) ? '.adp-email-wrapper' : 'body';
?>
<style>
    /* --- Variables CSS (Solo para navegadores modernos y Preview) --- */
    .adp-email-wrapper {
        --adp-header-bg: <?php echo esc_attr($bgHeader); ?>;
        --adp-header-text: <?php echo esc_attr($txtHeader); ?>;
        --adp-body-bg: <?php echo esc_attr($bgBody); ?>;
        --adp-btn-bg: <?php echo esc_attr($bgBtn); ?>;
        --adp-btn-text: <?php echo esc_attr($txtBtn); ?>;
        --adp-link-color: <?php echo esc_attr($colLink); ?>;
    }

    /* --- Reset & Base --- */
    <?php echo $rootSelector; ?> {
        margin: 0; 
        padding: 0; 
        background-color: <?php echo esc_attr($bgBody); ?>; 
        background-color: var(--adp-body-bg, <?php echo esc_attr($bgBody); ?>);
        font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
        color: #555555; 
        line-height: 1.6; 
    }
    
    img { max-width: 100%; height: auto; display: block; }
    
    /* --- Enlaces --- */
    .adp-email-wrapper a { 
        color: <?php echo esc_attr($colLink); ?>; 
        color: var(--adp-link-color, <?php echo esc_attr($colLink); ?>);
        text-decoration: none; 
    }
    
    /* --- Estructura --- */
    .wrapper { width: 100%; table-layout: fixed; padding-bottom: 40px; }
    
    .container { 
        max-width: 600px; 
        background-color: #ffffff; 
        margin: 0 auto; 
        border-radius: 4px; 
        overflow: hidden; 
        box-shadow: 0 1px 3px rgba(0,0,0,0.1); 
    }
    
    /* --- Header --- */
    .header { 
        background-color: <?php echo esc_attr($bgHeader); ?>; 
        background-color: var(--adp-header-bg, <?php echo esc_attr($bgHeader); ?>);
        padding: 30px 20px; 
        text-align: center; 
    }
    
    .header h1 { 
        margin: 0; 
        font-size: 24px; 
        font-weight: bold; 
        text-transform: uppercase; 
        letter-spacing: 1px; 
    }
    
    .header h1, .header h1 a {
        color: <?php echo esc_attr($txtHeader); ?>; 
        color: var(--adp-header-text, <?php echo esc_attr($txtHeader); ?>);
        text-decoration: none;
    }

    /* --- Subtítulos --- */
    .email-meta { 
        background-color: #e5f6ff; 
        color: #005b99; 
        padding: 15px 20px; 
        text-align: center; 
        font-size: 15px; 
        font-weight: 600; 
        border-bottom: 1px solid #cceeff; 
    }

    /* --- Contenido --- */
    .content-body { padding: 30px; }
    
    .post-title { margin: 0 0 15px 0; font-size: 22px; line-height: 1.3; color: #333333; }
    .post-title a { color: #333333; text-decoration: none; }
    
    .post-item { margin-bottom: 30px; border-bottom: 1px solid #eeeeee; padding-bottom: 30px; }
    .post-item:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
    
    .post-thumb { width: 100%; border-radius: 4px; margin-bottom: 15px; object-fit: cover; }
    
    .post-content, .post-excerpt { font-size: 16px; color: #555555; margin-bottom: 20px; }
    
    /* --- Botones --- */
    .btn-primary { 
        display: inline-block; 
        padding: 12px 24px; 
        border-radius: 4px; 
        font-weight: bold; 
        text-align: center; 
        margin-top: 10px; 
        text-decoration: none;
        
        background-color: <?php echo esc_attr($bgBtn); ?>; 
        background-color: var(--adp-btn-bg, <?php echo esc_attr($bgBtn); ?>);
        
        color: <?php echo esc_attr($txtBtn); ?> !important; 
        color: var(--adp-btn-text, <?php echo esc_attr($txtBtn); ?>) !important;
    }
    
    .btn-link { 
        display: inline-block; 
        font-size: 14px; 
        font-weight: bold; 
        margin-top: 5px;
        
        color: <?php echo esc_attr($colLink); ?>; 
        color: var(--adp-link-color, <?php echo esc_attr($colLink); ?>);
    }
    .btn-link:hover { text-decoration: underline; }

    /* --- Footer --- */
    .footer { 
        background-color: #f4f4f4; /* Fallback */
        background-color: var(--adp-body-bg, <?php echo esc_attr($bgBody); ?>);
        text-align: center; 
        padding: 20px; 
        font-size: 12px; 
        color: #999999; 
        border-top: 1px solid #eaeaea; 
    }
    .footer p { margin: 5px 0; }
    .footer a { color: #777777; text-decoration: underline; }
</style>