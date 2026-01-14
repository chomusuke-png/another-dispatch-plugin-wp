/**
 * Archivo: assets/js/admin-customizer.js
 * Descripción: Lógica para el Customizer de Emails.
 * Funciones:
 * - Upload de imagen usando wp.media
 * - Live Preview de cambios de color y texto
 */

jQuery(document).ready(function($) {
    
    // 1. Inicializar Color Pickers con Callback de cambio
    $('.adp-color-field').wpColorPicker({
        change: function(event, ui) {
            var color = ui.color.toString();
            var cssVar = $(event.target).data('css-var');
            
            // Actualizar Variable CSS del Simulador en tiempo real
            $('#adp-email-simulator').css(cssVar, color);
            
            // Caso especial: El botón dentro del simulador usa estilos inline a veces
            if (cssVar === '--adp-email-header-bg') {
                $('.adp-sim-btn').css('background-color', color);
            }
            if (cssVar === '--adp-email-header-text') {
                $('.adp-sim-btn').css('color', color);
            }
        },
        clear: function() {
            // Resetear a default si se limpia (opcional)
        }
    });

    // 2. Live Preview del Texto del Footer
    $('#adp_footer_text').on('input', function() {
        var text = $(this).val();
        // Permitimos HTML básico en la preview
        $('#adp-sim-footer-content').html(text);
    });

    // 3. Manejo de Logo Upload (wp.media)
    var mediaUploader;

    $('#adp-upload-logo-btn').on('click', function(e) {
        e.preventDefault();

        // Si ya existe la instancia, la abrimos
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        // Crear nueva instancia de wp.media
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Seleccionar Logo del Email',
            button: {
                text: 'Usar este logo'
            },
            multiple: false
        });

        // Al seleccionar una imagen
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            
            // Guardar URL en el input hidden
            $('#adp_email_logo').val(attachment.url);
            
            // Actualizar Preview en Controles
            var imgHtml = '<img src="' + attachment.url + '" id="adp-logo-img" alt="Logo Preview">';
            $('#adp-logo-preview-wrapper').html(imgHtml);
            
            // Actualizar Preview en Simulador
            // Verificar si ya existe imagen en simulador, si no, crearla
            var simLogoArea = $('.adp-sim-logo-area');
            if (simLogoArea.find('img').length > 0) {
                simLogoArea.find('img').attr('src', attachment.url);
            } else {
                simLogoArea.html('<img src="' + attachment.url + '" class="adp-sim-logo-img">');
            }

            // Mostrar botón "Quitar"
            $('#adp-remove-logo-btn').removeClass('hidden');
        });

        mediaUploader.open();
    });

    // 4. Botón Quitar Logo
    $('#adp-remove-logo-btn').on('click', function(e) {
        e.preventDefault();
        
        // Limpiar input y previews
        $('#adp_email_logo').val('');
        $('#adp-logo-preview-wrapper').html('<span class="adp-no-logo-text">Sin logo seleccionado</span>');
        $('.adp-sim-logo-area').empty();
        
        // Ocultar botón
        $(this).addClass('hidden');
    });

});