<?php
/**
 * Archivo: templates/admin/customizer.php
 * Descripción: Interfaz de administración para personalizar el diseño de los emails.
 * Incluye controles de formulario y un área de previsualización en vivo.
 *
 * @var array $settings Valores actuales de las opciones guardadas en base de datos.
 */

// Recuperamos las opciones actuales (con fallbacks por defecto)
$logo_url      = get_option( 'adp_email_logo', '' );
$header_bg     = get_option( 'adp_header_bg', '#2271b1' );
$header_text   = get_option( 'adp_header_text_color', '#ffffff' );
$body_bg       = get_option( 'adp_body_bg', '#f0f0f1' );
$footer_text   = get_option( 'adp_footer_text', '© 2025 Tu Empresa. Todos los derechos reservados.' );
?>

<div class="wrap adp-wrap">
    <h1><?php echo esc_html__( 'Diseñador de Correos', 'another-dispatch-plugin' ); ?></h1>
    
    <form method="post" action="options.php" class="adp-customizer-form">
        <?php
            // Campos de seguridad y secciones de configuración de WP
            settings_fields( 'adp_email_settings_group' ); 
            do_settings_sections( 'adp_email_settings_group' );
        ?>

        <div class="adp-customizer-grid">
            
            <div class="adp-controls-pane">
                
                <div class="adp-control-section">
                    <h2 class="adp-section-title"><?php echo esc_html__( 'Identidad', 'another-dispatch-plugin' ); ?></h2>
                    
                    <div class="adp-form-group">
                        <label class="adp-label"><?php echo esc_html__( 'Logotipo del Email', 'another-dispatch-plugin' ); ?></label>
                        <div class="adp-logo-uploader">
                            <div class="adp-logo-preview-wrapper" id="adp-logo-preview-wrapper">
                                <?php if ( ! empty( $logo_url ) ) : ?>
                                    <img src="<?php echo esc_url( $logo_url ); ?>" alt="Logo Preview" id="adp-logo-img">
                                <?php else : ?>
                                    <span class="adp-no-logo-text"><?php echo esc_html__( 'Sin logo seleccionado', 'another-dispatch-plugin' ); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <input type="hidden" name="adp_email_logo" id="adp_email_logo" value="<?php echo esc_attr( $logo_url ); ?>">
                            
                            <div class="adp-btn-group">
                                <button type="button" class="button adp-btn-upload" id="adp-upload-logo-btn">
                                    <?php echo esc_html__( 'Subir Logo', 'another-dispatch-plugin' ); ?>
                                </button>
                                <button type="button" class="button adp-btn-remove <?php echo empty( $logo_url ) ? 'hidden' : ''; ?>" id="adp-remove-logo-btn">
                                    <?php echo esc_html__( 'Quitar', 'another-dispatch-plugin' ); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="adp-control-section">
                    <h2 class="adp-section-title"><?php echo esc_html__( 'Colores', 'another-dispatch-plugin' ); ?></h2>

                    <div class="adp-form-group">
                        <label class="adp-label" for="adp_header_bg"><?php echo esc_html__( 'Fondo del Encabezado', 'another-dispatch-plugin' ); ?></label>
                        <input type="text" name="adp_header_bg" id="adp_header_bg" value="<?php echo esc_attr( $header_bg ); ?>" class="adp-color-field" data-css-var="--adp-email-header-bg">
                    </div>

                    <div class="adp-form-group">
                        <label class="adp-label" for="adp_header_text_color"><?php echo esc_html__( 'Texto del Encabezado', 'another-dispatch-plugin' ); ?></label>
                        <input type="text" name="adp_header_text_color" id="adp_header_text_color" value="<?php echo esc_attr( $header_text ); ?>" class="adp-color-field" data-css-var="--adp-email-header-text">
                    </div>

                    <div class="adp-form-group">
                        <label class="adp-label" for="adp_body_bg"><?php echo esc_html__( 'Fondo General', 'another-dispatch-plugin' ); ?></label>
                        <input type="text" name="adp_body_bg" id="adp_body_bg" value="<?php echo esc_attr( $body_bg ); ?>" class="adp-color-field" data-css-var="--adp-email-body-bg">
                    </div>
                </div>

                <div class="adp-control-section">
                    <h2 class="adp-section-title"><?php echo esc_html__( 'Pie de Página', 'another-dispatch-plugin' ); ?></h2>
                    <div class="adp-form-group">
                        <label class="adp-label" for="adp_footer_text"><?php echo esc_html__( 'Texto del Footer', 'another-dispatch-plugin' ); ?></label>
                        <textarea name="adp_footer_text" id="adp_footer_text" rows="4" class="widefat"><?php echo esc_textarea( $footer_text ); ?></textarea>
                        <p class="description"><?php echo esc_html__( 'Puedes usar HTML básico.', 'another-dispatch-plugin' ); ?></p>
                    </div>
                </div>

                <div class="adp-actions-bar">
                    <?php submit_button( __( 'Guardar Cambios', 'another-dispatch-plugin' ), 'primary', 'submit', false ); ?>
                </div>

            </div> <div class="adp-preview-pane">
                <div class="adp-sticky-wrapper">
                    <h3 class="adp-preview-title"><?php echo esc_html__( 'Vista Previa en Vivo', 'another-dispatch-plugin' ); ?></h3>
                    
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
                            <h2 class="adp-sim-title">Confirmación de Suscripción</h2>
                        </div>

                        <div class="adp-sim-body">
                            <p>Hola <strong>Usuario</strong>,</p>
                            <p>Gracias por suscribirte a nuestras alertas. Este es un ejemplo de cómo se verán tus correos electrónicos con la configuración actual.</p>
                            <p>Por favor, haz clic en el botón de abajo para confirmar:</p>
                            
                            <a href="#" class="adp-sim-btn" style="background: var(--adp-email-header-bg); color: var(--adp-email-header-text);">Confirmar Email</a>
                        </div>

                        <div class="adp-sim-footer">
                            <span id="adp-sim-footer-content"><?php echo wp_kses_post( $footer_text ); ?></span>
                            <br>
                            <small><a href="#">Darse de baja</a></small>
                        </div>
                    </div>
                </div>
            </div> </div>
    </form>
</div>