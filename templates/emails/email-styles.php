<?php
/**
 * Partial: Estilos CSS dinámicos.
 */

// Obtener colores configurados o usar defaults
$header_bg   = get_option( 'adp_color_header_bg', '#2271b1' );
$header_text = get_option( 'adp_color_header_text', '#ffffff' );
$btn_bg      = get_option( 'adp_color_btn_bg', '#2271b1' );
$btn_text    = get_option( 'adp_color_btn_text', '#ffffff' );
$link_color  = get_option( 'adp_color_links', '#2271b1' );
?>
<style>
    /* --- Reset & Base --- */
    body { margin: 0; padding: 0; background-color: #f4f4f4; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #555555; line-height: 1.6; }
    img { max-width: 100%; height: auto; display: block; }
    
    /* --- Colores Dinámicos --- */
    a { color: <?php echo esc_attr( $link_color ); ?>; text-decoration: none; }
    
    /* --- Estructura --- */
    .wrapper { width: 100%; table-layout: fixed; background-color: #f4f4f4; padding-bottom: 40px; }
    .container { max-width: 600px; background-color: #ffffff; margin: 0 auto; border-radius: 4px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    
    /* --- Header --- */
    .header { background-color: <?php echo esc_attr( $header_bg ); ?>; padding: 30px 20px; text-align: center; }
    .header h1 { margin: 0; color: <?php echo esc_attr( $header_text ); ?>; font-size: 24px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
    .header a { color: <?php echo esc_attr( $header_text ); ?>; text-decoration: none; }

    /* --- Subtítulos --- */
    .email-meta { background-color: #e5f6ff; color: #005b99; padding: 15px 20px; text-align: center; font-size: 15px; font-weight: 600; border-bottom: 1px solid #cceeff; }

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
        background-color: <?php echo esc_attr( $btn_bg ); ?>; 
        color: <?php echo esc_attr( $btn_text ); ?> !important; 
        border-radius: 4px; 
        font-weight: bold; 
        text-align: center; 
        margin-top: 10px; 
        text-decoration: none;
    }
    
    .btn-link { display: inline-block; font-size: 14px; font-weight: bold; color: <?php echo esc_attr( $link_color ); ?>; margin-top: 5px; }
    .btn-link:hover { text-decoration: underline; }

    /* --- Footer --- */
    .footer { background-color: #f4f4f4; text-align: center; padding: 20px; font-size: 12px; color: #999999; border-top: 1px solid #eaeaea; }
    .footer p { margin: 5px 0; }
    .footer a { color: #777777; text-decoration: underline; }
</style>