<?php
/**
 * Template: Personalizador de Diseño de Correos
 */
?>
<div class="wrap">
    <h1>Personalizar Diseño de Correo</h1>
    
    <?php if ( ! empty( $message ) ) : ?>
        <div class="notice notice-success is-dismissible"><p><?php echo $message; ?></p></div>
    <?php endif; ?>

    <div class="adp-dashboard-wrapper">
        
        <div class="adp-main-column">
            <div class="postbox">
                <div class="postbox-header"><h2 class="hndle">Paleta de Colores</h2></div>
                <div class="inside">
                    <form method="post" action="options.php">
                        <?php settings_fields( 'adp_customizer_settings' ); ?>
                        <?php do_settings_sections( 'adp_customizer_settings' ); ?>

                        <p class="description">Define los colores corporativos para tus newsletters.</p>
                        
                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row"><label>Fondo Cabecera (Header)</label></th>
                                <td>
                                    <input type="text" name="adp_color_header_bg" 
                                           value="<?php echo esc_attr( get_option( 'adp_color_header_bg', '#2271b1' ) ); ?>" 
                                           class="adp-color-field" data-default-color="#2271b1" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label>Texto Cabecera</label></th>
                                <td>
                                    <input type="text" name="adp_color_header_text" 
                                           value="<?php echo esc_attr( get_option( 'adp_color_header_text', '#ffffff' ) ); ?>" 
                                           class="adp-color-field" data-default-color="#ffffff" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label>Fondo Botones</label></th>
                                <td>
                                    <input type="text" name="adp_color_btn_bg" 
                                           value="<?php echo esc_attr( get_option( 'adp_color_btn_bg', '#2271b1' ) ); ?>" 
                                           class="adp-color-field" data-default-color="#2271b1" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label>Texto Botones</label></th>
                                <td>
                                    <input type="text" name="adp_color_btn_text" 
                                           value="<?php echo esc_attr( get_option( 'adp_color_btn_text', '#ffffff' ) ); ?>" 
                                           class="adp-color-field" data-default-color="#ffffff" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label>Enlaces</label></th>
                                <td>
                                    <input type="text" name="adp_color_links" 
                                           value="<?php echo esc_attr( get_option( 'adp_color_links', '#2271b1' ) ); ?>" 
                                           class="adp-color-field" data-default-color="#2271b1" />
                                </td>
                            </tr>
                        </table>

                        <?php submit_button( 'Guardar Diseño' ); ?>
                    </form>
                </div>
            </div>
        </div>

        <div class="adp-sidebar-column">
            <div class="postbox">
                <div class="postbox-header"><h2 class="hndle">Referencia Visual</h2></div>
                <div class="inside">
                    <p>Así se aplican los colores en la estructura del correo:</p>
                    
                    <div style="border: 1px solid #ccc; border-radius: 4px; overflow: hidden;">
                        <div style="background: <?php echo esc_attr( get_option( 'adp_color_header_bg', '#2271b1' ) ); ?>; padding: 15px; text-align: center;">
                            <span style="color: <?php echo esc_attr( get_option( 'adp_color_header_text', '#ffffff' ) ); ?>; font-weight: bold;">LOGO / TÍTULO</span>
                        </div>
                        
                        <div style="padding: 15px; background: #fff;">
                            <p style="margin-top: 0; color: #666;">Hola,</p>
                            <p style="color: #666;">Este es un texto de ejemplo con un <a href="#" style="color: <?php echo esc_attr( get_option( 'adp_color_links', '#2271b1' ) ); ?>">enlace activo</a>.</p>
                            
                            <div style="text-align: center; margin: 15px 0;">
                                <span style="display: inline-block; padding: 8px 16px; border-radius: 4px; background: <?php echo esc_attr( get_option( 'adp_color_btn_bg', '#2271b1' ) ); ?>; color: <?php echo esc_attr( get_option( 'adp_color_btn_text', '#ffffff' ) ); ?>; font-size: 12px; font-weight: bold;">LEER MÁS</span>
                            </div>
                        </div>
                    </div>
                    <p class="description" style="margin-top: 10px;">Nota: Guarda los cambios para actualizar esta vista previa.</p>
                </div>
            </div>
        </div>

    </div>
</div>