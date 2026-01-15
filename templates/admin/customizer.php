<?php
/**
 * Archivo: templates/admin/customizer.php
 * Descripción: Interfaz de administración visual (Refactorizada a Postbox).
 */

// Recuperamos las opciones
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
            // do_settings_sections no es necesario si renderizamos manual los campos
        ?>

        <div class="adp-customizer-grid">
            
            <div class="adp-controls-pane">
                
                <div class="postbox">
                    <div class="postbox-header"><h2 class="hndle">Identidad</h2></div>
                    <div class="inside">
                        <p class="description" style="margin-bottom:10px;">Define el logotipo que aparecerá en el encabezado.</p>
                        
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
                                <button type="button" class="button button-secondary" id="adp-upload-logo-btn">Subir Logo</button>
                                <button type="button" class="button link-delete <?php echo empty( $logo_url ) ? 'hidden' : ''; ?>" id="adp-remove-logo-btn" style="color: #d63638;">Quitar</button>
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
                                <input type="color" name="adp_color_header_bg" id="adp_color_header_bg" value="<?php echo esc_attr( $header_bg ); ?>" class="adp-color-native" data-css-var="--adp-email-header-bg">
                            </div>

                            <div class="adp-form-group-color">
                                <label for="adp_color_header_text">Texto Header</label>
                                <input type="color" name="adp_color_header_text" id="adp_color_header_text" value="<?php echo esc_attr( $header_text ); ?>" class="adp-color-native" data-css-var="--adp-email-header-text">
                            </div>

                            <div class="adp-form-group-color">
                                <label for="adp_body_bg">Fondo Email</label>
                                <input type="color" name="adp_body_bg" id="adp_body_bg" value="<?php echo esc_attr( $body_bg ); ?>" class="adp-color-native" data-css-var="--adp-email-body-bg">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="postbox">
                    <div class="postbox-header"><h2 class="hndle">Pie de Página</h2></div>
                    <div class="inside">
                        <label class="screen-reader-text" for="adp_footer_text">Texto Footer</label>
                        <textarea name="adp_footer_text" id="adp_footer_text" rows="4" class="widefat" placeholder="Texto legal o dirección..."><?php echo esc_textarea( $footer_text ); ?></textarea>
                    </div>
                </div>

                <div class="adp-actions-bar">
                    <?php submit_button( __( 'Guardar Cambios', 'another-dispatch-plugin' ), 'primary large', 'submit', false, array( 'style' => 'width:100%' ) ); ?>
                </div>

            </div> 
            
            <div class="adp-preview-pane">
                <div class="adp-sticky-wrapper">
                    
                    <div class="adp-preview-header-bar">
                        <h3 class="adp-preview-title"><?php echo esc_html__( 'Vista Previa en Vivo', 'another-dispatch-plugin' ); ?></h3>
                        
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
                        $is_preview = true; 
                        include ADP_PATH . 'templates/emails/email-styles.php'; 
                        ?>

                        <div id="adp-sim-content-area">
                            <?php
                                // Mock data inicial para Single
                                $post_title = 'Bienvenido a nuestro Newsletter';
                                $post_content = '<p>Este es un ejemplo de cómo se verán tus correos.</p><p>Puedes personalizar los colores...</p>';
                                $post_link = '#';
                                $featured_image = ''; 
                                $unsubscribe_link = '#';
                                $blog_name = get_bloginfo('name');
                                
                                include ADP_PATH . 'templates/emails/single.php';
                            ?>
                        </div>

                    </div>
                    
                </div>
            </div>
        </div>
    </form>
</div>