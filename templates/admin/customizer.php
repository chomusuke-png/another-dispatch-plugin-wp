<?php
/**
 * Archivo: templates/admin/customizer.php
 * Descripción: Interfaz de administración corregida.
 */

// Recuperamos las opciones usando los nombres CORRECTOS (los mismos de email-styles.php)
$logo_url      = get_option( 'adp_logo_url', '' );
$header_bg     = get_option( 'adp_color_header_bg', '#2271b1' );
$header_text   = get_option( 'adp_color_header_text', '#ffffff' );
$body_bg       = get_option( 'adp_body_bg', '#f0f0f1' );
$footer_text   = get_option( 'adp_footer_text', '© 2025 Tu Empresa. Todos los derechos reservados.' );
?>

<div class="wrap adp-wrap">
    <h1><?php echo esc_html__( 'Diseñador de Correos', 'another-dispatch-plugin' ); ?></h1>
    
    <form method="post" action="options.php" class="adp-customizer-form">
        <?php
            settings_fields( 'adp_customizer_settings' ); 
            do_settings_sections( 'adp_customizer_settings' );
        ?>

        <div class="adp-customizer-grid">
            
            <div class="adp-controls-pane">
                
                <div class="adp-control-section">
                    <h2 class="adp-section-title"><?php echo esc_html__( 'Identidad', 'another-dispatch-plugin' ); ?></h2>
                    
                    <div class="adp-form-group">
                        <label class="adp-label"><?php echo esc_html__( 'Logotipo', 'another-dispatch-plugin' ); ?></label>
                        <div class="adp-logo-uploader">
                            <div class="adp-logo-preview-wrapper" id="adp-logo-preview-wrapper">
                                <?php if ( ! empty( $logo_url ) ) : ?>
                                    <img src="<?php echo esc_url( $logo_url ); ?>" alt="Logo Preview" id="adp-logo-img">
                                <?php else : ?>
                                    <span class="adp-no-logo-text"><?php echo esc_html__( 'Sin logo seleccionado', 'another-dispatch-plugin' ); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <input type="hidden" name="adp_logo_url" id="adp_logo_url" value="<?php echo esc_attr( $logo_url ); ?>">
                            
                            <div class="adp-btn-group">
                                <button type="button" class="button adp-btn-upload" id="adp-upload-logo-btn">Subir Logo</button>
                                <button type="button" class="button adp-btn-remove <?php echo empty( $logo_url ) ? 'hidden' : ''; ?>" id="adp-remove-logo-btn">Quitar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="adp-control-section">
                    <h2 class="adp-section-title"><?php echo esc_html__( 'Colores', 'another-dispatch-plugin' ); ?></h2>

                    <div class="adp-color-grid">
                        <div class="adp-form-group-color">
                            <label class="adp-label-small" for="adp_color_header_bg">Header Fondo</label>
                            <div class="adp-color-input-wrapper">
                                <input type="color" name="adp_color_header_bg" id="adp_color_header_bg" value="<?php echo esc_attr( $header_bg ); ?>" class="adp-color-native" data-css-var="--adp-email-header-bg">
                            </div>
                        </div>

                        <div class="adp-form-group-color">
                            <label class="adp-label-small" for="adp_color_header_text">Header Texto</label>
                            <div class="adp-color-input-wrapper">
                                <input type="color" name="adp_color_header_text" id="adp_color_header_text" value="<?php echo esc_attr( $header_text ); ?>" class="adp-color-native" data-css-var="--adp-email-header-text">
                            </div>
                        </div>

                        <div class="adp-form-group-color">
                            <label class="adp-label-small" for="adp_body_bg">Fondo Email</label>
                            <div class="adp-color-input-wrapper">
                                <input type="color" name="adp_body_bg" id="adp_body_bg" value="<?php echo esc_attr( $body_bg ); ?>" class="adp-color-native" data-css-var="--adp-email-body-bg">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="adp-control-section">
                    <h2 class="adp-section-title"><?php echo esc_html__( 'Pie de Página', 'another-dispatch-plugin' ); ?></h2>
                    <div class="adp-form-group">
                        <label class="adp-label" for="adp_footer_text"><?php echo esc_html__( 'Texto del Footer', 'another-dispatch-plugin' ); ?></label>
                        <textarea name="adp_footer_text" id="adp_footer_text" rows="4" class="widefat"><?php echo esc_textarea( $footer_text ); ?></textarea>
                    </div>
                </div>

                <div class="adp-actions-bar">
                    <?php submit_button( __( 'Guardar Cambios', 'another-dispatch-plugin' ), 'primary', 'submit', false ); ?>
                </div>

            </div> 
            
            <div class="adp-preview-pane">
                <div class="adp-sticky-wrapper">
                    
                    <div class="adp-preview-header-bar">
                        <h3 class="adp-preview-title"><?php echo esc_html__( 'Vista Previa', 'another-dispatch-plugin' ); ?></h3>
                        
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
                    
                    <div class="adp-email-simulator" id="adp-email-simulator" style="
                        --adp-email-header-bg: <?php echo esc_attr($header_bg); ?>;
                        --adp-email-header-text: <?php echo esc_attr($header_text); ?>;
                        --adp-email-body-bg: <?php echo esc_attr($body_bg); ?>;
                    ">
                        <div class="adp-sim-header">
                            <div class="adp-sim-logo-area">
                                <?php if ( ! empty( $logo_url ) ) : ?>
                                    <img src="<?php echo esc_url( $logo_url ); ?>" alt="Logo" class="adp-sim-logo-img">
                                <?php endif; ?>
                            </div>
                            <h2 class="adp-sim-title" id="adp-sim-main-title">Confirmación</h2>
                        </div>
                        
                        <div class="adp-sim-body" id="adp-sim-content-area">
                            </div>

                        <div class="adp-sim-footer">
                            <span id="adp-sim-footer-content"><?php echo wp_kses_post( $footer_text ); ?></span>
                            <br>
                            <small><a href="#">Darse de baja</a></small>
                        </div>
                    </div>
                </div>
            </div> 
        </div>
    </form>
</div>