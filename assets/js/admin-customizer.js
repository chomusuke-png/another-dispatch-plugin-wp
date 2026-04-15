jQuery(document).ready(function($) {
    
    // --- 1. Live Preview de Colores (Variables CSS) ---
    
    // Función auxiliar para forzar los colores actuales sobre el HTML cargado
    function applyCurrentColors() {
        $('.adp-color-native').each(function() {
            var color = $(this).val();
            var cssVar = $(this).data('css-var');
            $('#adp-email-simulator').find('.adp-email-wrapper').css(cssVar, color);
        });
    }

    $('.adp-color-native').on('input change', function() {
        var color = $(this).val();
        var cssVar = $(this).data('css-var');
        $('#adp-email-simulator').find('.adp-email-wrapper').css(cssVar, color);
    });

    // --- 2. Live Preview Texto Footer ---
    $('#adp_footer_text').on('input', function() {
        var text = $(this).val();
        $('#adp-email-simulator').find('.footer p:first-child').html(text);
    });

    // --- 3. Lógica de Cambio de Vista (AJAX) ---
    $('input[name="adp_preview_mode"]').on('change', function() {
        var mode = $(this).val();
        var $container = $('#adp-sim-content-area'); 
        var $title = $('.adp-preview-title');

        var titlesMap = {
            'single': 'Vista Previa: Single Post',
            'digest': 'Vista Previa: Resumen Semanal',
            'welcome': 'Vista Previa: Bienvenida',
            'verification': 'Vista Previa: Verificación'
        };

        $container.css('opacity', 0.5); 
        $title.text(titlesMap[mode] || 'Vista Previa en Vivo');

        $.ajax({
            url: adp_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'adp_render_preview',
                nonce: adp_vars.nonce,
                mode: mode
            },
            success: function(response) {
                if(response.success) {
                    $container.html(response.data.html);
                    applyCurrentColors();
                }
            },
            error: function() {
                $container.html('<p style="color:red; text-align:center;">Error al cargar la vista previa.</p>');
            },
            complete: function() {
                $container.css('opacity', 1);
            }
        });
    });

    // --- 4. Gestión del Logo (Media Uploader) ---
    function updateLogoInterface(url) {
        if(url) {
            // Panel Izquierdo
            $('#adp-logo-preview-wrapper').html('<img src="' + url + '" id="adp-logo-img">');
            $('#adp-remove-logo-btn').removeClass('hidden');
            $('#adp_logo_url').val(url);

            // Simulador (Derecha)
            var imgHtml = '<img src="' + url + '" class="adp-sim-logo-img" style="max-height:50px; margin:0 auto;">';
            var $simHeaderLink = $('#adp-email-simulator .header h1 a');
            
            if ($simHeaderLink.length) {
                $simHeaderLink.html(imgHtml);
            } else {
                $('.adp-sim-logo-area').html(imgHtml);
            }

        } else {
            // Borrar
            $('#adp-logo-preview-wrapper').html('<span class="adp-no-logo-text">Sin logo seleccionado</span>');
            $('#adp-remove-logo-btn').addClass('hidden');
            $('#adp_logo_url').val('');

            var blogName = adp_vars.blogname || 'Vista Previa'; 
            $('#adp-email-simulator .header h1 a').text(blogName);
        }
    }

    var mediaUploader;
    $('#adp-upload-logo-btn').on('click', function(e) {
        e.preventDefault();
        if (mediaUploader) { mediaUploader.open(); return; }

        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Seleccionar Logo',
            button: { text: 'Usar este logo' },
            multiple: false
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            updateLogoInterface(attachment.url);
        });

        mediaUploader.open();
    });

    $('#adp-remove-logo-btn').on('click', function(e) {
        e.preventDefault();
        updateLogoInterface('');
    });
    
    // Inicialización: Asegurar que los colores se apliquen si la página ya cargó contenido
    applyCurrentColors();

});