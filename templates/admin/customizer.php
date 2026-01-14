<?php
/**
 * Template: Personalizador de Emails
 */

// Obtener valores guardados para pre-populacion
$header_bg   = get_option('adp_color_header_bg', '#333333');
$header_text = get_option('adp_color_header_text', '#ffffff');
$btn_bg      = get_option('adp_color_btn_bg', '#2271b1');
$btn_text    = get_option('adp_color_btn_text', '#ffffff');
$links       = get_option('adp_color_links', '#2271b1');
$logo_url    = get_option('adp_logo_url', '');
$footer_text = get_option('adp_footer_text', 'Enviado con amor desde ' . get_bloginfo('name'));
?>

<div class="wrap">
    <h1>🎨 Diseño del Correo</h1>
    <hr class="wp-header-end">

    <?php if ( isset( $message ) ) : ?>
        <div class="notice notice-success is-dismissible"><p><?php echo $message; ?></p></div>
    <?php endif; ?>

    <form method="post" action="options.php">
        <?php settings_fields( 'adp_customizer_settings' ); ?>
        
        <div class="adp-customizer-grid">
            
            <div class="adp-controls-pane">
                
                <div class="postbox">
                    <div class="postbox-header"><h2 class="hndle">Identidad</h2></div>
                    <div class="inside">
                        <p><strong>Logo del Header:</strong></p>
                        
                        <div class="adp-logo-uploader-area">
                            <?php 
                                $display = empty($logo_url) ? 'none' : 'block'; 
                                $preview_src = empty($logo_url) ? '' : $logo_url;
                            ?>
                            <img id="adp-logo-preview-img" src="<?php echo esc_url($preview_src); ?>" style="display: <?php echo $display; ?>; max-width: 100%; height: auto; margin-bottom: 10px; border: 1px solid #ddd; padding: 5px;">
                            
                            <input type="hidden" name="adp_logo_url" id="adp_logo_url" value="<?php echo esc_attr( $logo_url ); ?>">
                            
                            <button type="button" class="button" id="adp-upload-logo-btn">
                                <span class="dashicons dashicons-format-image" style="vertical-align: middle;"></span> Seleccionar Imagen
                            </button>
                            
                            <button type="button" class="button button-link-delete" id="adp-remove-logo-btn" style="display: <?php echo $display; ?>;">Quitar logo</button>
                        </div>

                        <hr>

                        <p><strong>Texto del Footer:</strong></p>
                        <textarea name="adp_footer_text" id="adp-footer-input" rows="3" class="large-text code"><?php echo esc_textarea( $footer_text ); ?></textarea>
                        <p class="description">Acepta HTML básico.</p>
                    </div>
                </div>

                <div class="postbox">
                    <div class="postbox-header"><h2 class="hndle">Colores</h2></div>
                    <div class="inside">
                        <table class="form-table" style="margin-top: 0;">
                            <tr>
                                <th scope="row">Fondo Cabecera</th>
                                <td><input type="text" name="adp_color_header_bg" value="<?php echo esc_attr( $header_bg ); ?>" class="adp-color-field" data-target="--header-bg"></td>
                            </tr>
                            <tr>
                                <th scope="row">Texto Cabecera</th>
                                <td><input type="text" name="adp_color_header_text" value="<?php echo esc_attr( $header_text ); ?>" class="adp-color-field" data-target="--header-text"></td>
                            </tr>
                            <tr>
                                <th scope="row">Fondo Botón</th>
                                <td><input type="text" name="adp_color_btn_bg" value="<?php echo esc_attr( $btn_bg ); ?>" class="adp-color-field" data-target="--btn-bg"></td>
                            </tr>
                            <tr>
                                <th scope="row">Texto Botón</th>
                                <td><input type="text" name="adp_color_btn_text" value="<?php echo esc_attr( $btn_text ); ?>" class="adp-color-field" data-target="--btn-text"></td>
                            </tr>
                            <tr>
                                <th scope="row">Enlaces</th>
                                <td><input type="text" name="adp_color_links" value="<?php echo esc_attr( $links ); ?>" class="adp-color-field" data-target="--link-color"></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <?php submit_button( 'Guardar Cambios' ); ?>
            </div>

            <div class="adp-preview-pane">
                <div class="adp-sticky-preview">
                    <h3>Vista Previa en Vivo</h3>
                    <div class="adp-fake-email-wrapper">
                        <style id="adp-preview-styles">
                            .adp-fake-email-header { background-color: <?php echo esc_attr($header_bg); ?>; color: <?php echo esc_attr($header_text); ?>; }
                            .adp-fake-btn { background-color: <?php echo esc_attr($btn_bg); ?>; color: <?php echo esc_attr($btn_text); ?> !important; }
                            .adp-fake-link { color: <?php echo esc_attr($links); ?>; }
                        </style>

                        <div class="adp-fake-email">
                            <div class="adp-fake-email-header" style="padding: 20px; text-align: center;">
                                <div id="adp-preview-logo-container" style="display: <?php echo empty($logo_url) ? 'none' : 'block'; ?>;">
                                    <img id="adp-preview-logo-img" src="<?php echo esc_url($logo_url); ?>" style="max-width: 150px; height: auto;">
                                </div>
                                <h1 id="adp-preview-blogname" style="margin: 10px 0 0; font-size: 24px; display: <?php echo empty($logo_url) ? 'block' : 'none'; ?>;">
                                    <?php echo get_bloginfo('name'); ?>
                                </h1>
                            </div>
                            
                            <div style="padding: 20px; background: #fff; color: #333; line-height: 1.6;">
                                <h2 style="margin-top: 0;">¡Hola! Aquí tienes un ejemplo</h2>
                                <p>Este es el cuerpo del mensaje. Así se verán tus correos cuando lleguen a tus suscriptores.</p>
                                <p>Puedes incluir <a href="#" class="adp-fake-link">enlaces como este</a> dentro del texto.</p>
                                <br>
                                <div style="text-align: center;">
                                    <a href="#" class="adp-fake-btn" style="display: inline-block; padding: 10px 20px; text-decoration: none; border-radius: 4px;">Leer Artículo Completo</a>
                                </div>
                            </div>

                            <div style="background: #f6f6f6; padding: 20px; text-align: center; font-size: 12px; color: #666;">
                                <div id="adp-preview-footer-content">
                                    <?php echo wp_kses_post( $footer_text ); ?>
                                </div>
                                <p style="margin-top: 10px;"><a href="#" style="color: #999;">Darse de baja</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>