<?php
/**
 * Template: Interfaz de administración visual
 */
if (!defined('ABSPATH')) {
    exit;
}

$logoUrl     = get_option('adp_logo_url', '');
$headerBg    = get_option('adp_color_header_bg', '#2271b1');
$headerText  = get_option('adp_color_header_text', '#ffffff');
$bodyBg      = get_option('adp_body_bg', '#f0f0f1');
$btnBg       = get_option('adp_color_btn_bg', '#2271b1');
$btnText     = get_option('adp_color_btn_text', '#ffffff');
$linkBg      = get_option('adp_color_links', '#2271b1');
$footerText  = get_option('adp_footer_text', '© 2026 Tu Empresa. Todos los derechos reservados.');
?>

<div class="wrap adp-wrap">
    <h1>Diseñador de Correos</h1>

    <?php if (!empty($message)) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html($message); ?></p>
        </div>
    <?php endif; ?>
    
    <form method="post" action="options.php" class="adp-customizer-form">
        <?php settings_fields('adp_customizer_settings'); ?>

        <div class="adp-customizer-grid">
            <div class="adp-controls-pane">
                <div class="postbox">
                    <div class="postbox-header"><h2 class="hndle">Identidad</h2></div>
                    <div class="inside">
                        <p class="description" style="margin-bottom:10px;">Define el logotipo que aparecerá en el encabezado.</p>
                        
                        <div class="adp-logo-uploader">
                            <div class="adp-logo-preview-wrapper" id="adp-logo-preview-wrapper">
                                <?php if (!empty($logoUrl)) : ?>
                                    <img src="<?php echo esc_url($logoUrl); ?>" alt="Logo Preview" id="adp-logo-img">
                                <?php else : ?>
                                    <span class="adp-no-logo-text">Sin logo seleccionado</span>
                                <?php endif; ?>
                            </div>
                            
                            <input type="hidden" name="adp_logo_url" id="adp_logo_url" value="<?php echo esc_attr($logoUrl); ?>">
                            
                            <div class="adp-btn-group">
                                <button type="button" class="button button-secondary" id="adp-upload-logo-btn">Subir Logo</button>
                                <button type="button" class="button link-delete <?php echo empty($logoUrl) ? 'hidden' : ''; ?>" id="adp-remove-logo-btn" style="color: #d63638;">Quitar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="postbox">
                    <div class="postbox-header"><h2 class="hndle">Paleta de Colores</h2></div>
                    <div class="inside">
                        <div class="adp-color-grid">
                            <div class="adp-form-group-color">
                                <label for="adp_color_header_bg">Fondo Header</label>
                                <input type="color" name="adp_color_header_bg" id="adp_color_header_bg" value="<?php echo esc_attr($headerBg); ?>" class="adp-color-native" data-css-var="--adp-email-header-bg">
                            </div>

                            <div class="adp-form-group-color">
                                <label for="adp_color_header_text">Texto Header</label>
                                <input type="color" name="adp_color_header_text" id="adp_color_header_text" value="<?php echo esc_attr($headerText); ?>" class="adp-color-native" data-css-var="--adp-email-header-text">
                            </div>

                            <div class="adp-form-group-color">
                                <label for="adp_body_bg">Fondo Email</label>
                                <input type="color" name="adp_body_bg" id="adp_body_bg" value="<?php echo esc_attr($bodyBg); ?>" class="adp-color-native" data-css-var="--adp-email-body-bg">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="postbox">
                    <div class="postbox-header"><h2 class="hndle">Pie de Página</h2></div>
                    <div class="inside">
                        <label class="screen-reader-text" for="adp_footer_text">Texto Footer</label>
                        <textarea name="adp_footer_text" id="adp_footer_text" rows="4" class="widefat" placeholder="Texto legal o dirección..."><?php echo esc_textarea($footerText); ?></textarea>
                    </div>
                </div>

                <div class="adp-actions-bar">
                    <?php submit_button('Guardar Cambios', 'primary large', 'submit', false, ['style' => 'width:100%']); ?>
                </div>
            </div> 
            
            <div class="adp-preview-pane">
                <div class="adp-sticky-wrapper">
                    <div class="adp-preview-header-bar">
                        <h3 class="adp-preview-title">Vista Previa en Vivo</h3>
                        <div class="adp-view-switch">
                            <label>
                                <input type="radio" name="adp_preview_mode" value="single" checked>
                                <span>Single</span>
                            </label>
                            <label>
                                <input type="radio" name="adp_preview_mode" value="digest">
                                <span>Digest</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="adp-email-simulator" id="adp-email-simulator">
                        <?php 
                        $isPreview = true; 
                        require ADP_PATH . 'templates/emails/email-styles.php'; 
                        ?>
                        <div id="adp-sim-content-area">
                            <?php
                                $postTitle = 'Bienvenido a nuestro Newsletter';
                                $postContent = '<p>Este es un ejemplo de cómo se verán tus correos.</p><p>Puedes personalizar los colores...</p>';
                                $postLink = '#';
                                $featuredImage = ''; 
                                $unsubscribeLink = '#';
                                $blogName = get_bloginfo('name');
                                
                                require ADP_PATH . 'templates/emails/single.php';
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>