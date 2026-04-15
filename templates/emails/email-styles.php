<?php
/**
 * Partial: Estilos CSS optimizados para compatibilidad.
 */
if (!defined('ABSPATH')) exit;

$bgHeader  = get_option('adp_color_header_bg', '#2271b1');
$txtHeader = get_option('adp_color_header_text', '#ffffff');
$bgBody    = get_option('adp_body_bg', '#f0f0f1');
$bgBtn     = get_option('adp_color_btn_bg', '#2271b1');
$txtBtn    = get_option('adp_color_btn_text', '#ffffff');
$colLink   = get_option('adp_color_links', '#2271b1');
?>
<style type="text/css">
    /* Reset general para clientes de correo */
    body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
    table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
    img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
    table { border-collapse: collapse !important; }
    body { height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; background-color: <?php echo esc_attr($bgBody); ?>; }

    /* Estilos de ayuda */
    .apple-link-footer a { color: #999999 !important; text-decoration: none !important; }
    
    /* Responsive */
    @media screen and (max-width: 600px) {
        .container { width: 100% !important; max-width: 100% !important; }
        .img-max { width: 100% !important; max-width: 100% !important; height: auto !important; }
        .content-body { padding: 20px !important; }
    }
</style>