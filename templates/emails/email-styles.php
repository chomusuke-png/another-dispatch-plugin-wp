<?php
/**
 * Partial: Estilos CSS dinámicos y estructura base.
 * Soporta CSS Variables para Live Preview y Hex para clientes de correo antiguos.
 */

// 1. Definimos los valores por defecto (PHP)
$bg_header = get_option( 'adp_color_header_bg', '#2271b1' );
$txt_header= get_option( 'adp_color_header_text', '#ffffff' );
$bg_body   = get_option( 'adp_body_bg', '#f0f0f1' );
$bg_btn    = get_option( 'adp_color_btn_bg', '#2271b1' );
$txt_btn   = get_option( 'adp_color_btn_text', '#ffffff' );
$col_link  = get_option( 'adp_color_links', '#2271b1' );

// 2. Definimos el selector raíz. En preview es el wrapper, en email real es body.
$root_selector = ( isset( $is_preview ) && $is_preview ) ? '.adp-email-wrapper' : 'body';
?>
<style>
    /* --- Variables CSS (Solo para navegadores modernos y Preview) --- */
    .adp-email-wrapper {
        --adp-header-bg: <?php echo esc_attr( $bg_header ); ?>;
        --adp-header-text: <?php echo esc_attr( $txt_header ); ?>;
        --adp-body-bg: <?php echo esc_attr( $bg_body ); ?>;
        --adp-btn-bg: <?php echo esc_attr( $bg_btn ); ?>;
        --adp-btn-text: <?php echo esc_attr( $txt_btn ); ?>;
        --adp-link-color: <?php echo esc_attr( $col_link ); ?>;
    }

    /* --- Reset & Base --- */
    <?php echo $root_selector; ?> {
        margin: 0; 
        padding: 0; 
        background-color: <?php echo esc_attr( $bg_body ); ?>; 
        background-color: var(--adp-body-bg, <?php echo esc_attr( $bg_body ); ?>);
        font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
        color: #555555; 
        line-height: 1.6; 
    }
    
    img { max-width: 100%; height: auto; display: block; }
    
    /* --- Enlaces --- */
    .adp-email-wrapper a { 
        color: <?php echo esc_attr( $col_link ); ?>; 
        color: var(--adp-link-color, <?php echo esc_attr( $col_link ); ?>);
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
        background-color: <?php echo esc_attr( $bg_header ); ?>; 
        background-color: var(--adp-header-bg, <?php echo esc_attr( $bg_header ); ?>);
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
        color: <?php echo esc_attr( $txt_header ); ?>; 
        color: var(--adp-header-text, <?php echo esc_attr( $txt_header ); ?>);
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
        
        background-color: <?php echo esc_attr( $bg_btn ); ?>; 
        background-color: var(--adp-btn-bg, <?php echo esc_attr( $bg_btn ); ?>);
        
        color: <?php echo esc_attr( $txt_btn ); ?> !important; 
        color: var(--adp-btn-text, <?php echo esc_attr( $txt_btn ); ?>) !important;
    }
    
    .btn-link { 
        display: inline-block; 
        font-size: 14px; 
        font-weight: bold; 
        margin-top: 5px;
        
        color: <?php echo esc_attr( $col_link ); ?>; 
        color: var(--adp-link-color, <?php echo esc_attr( $col_link ); ?>);
    }
    .btn-link:hover { text-decoration: underline; }

    /* --- Footer --- */
    .footer { 
        background-color: #f4f4f4; /* Fallback */
        background-color: var(--adp-body-bg, <?php echo esc_attr( $bg_body ); ?>); /* Se funde con el fondo */
        text-align: center; 
        padding: 20px; 
        font-size: 12px; 
        color: #999999; 
        border-top: 1px solid #eaeaea; 
    }
    .footer p { margin: 5px 0; }
    .footer a { color: #777777; text-decoration: underline; }
</style>