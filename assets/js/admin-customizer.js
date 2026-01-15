jQuery(document).ready(function($) {
    
    // --- 1. Live Preview de Colores (Variables CSS) ---
    $('.adp-color-native').on('input change', function() {
        var color = $(this).val();
        var cssVar = $(this).data('css-var'); // Ej: --adp-header-bg
        
        // Aplicamos la variable al contenedor padre (.adp-email-simulator)
        // Así, cualquier HTML que carguemos por AJAX heredará estos colores automáticamente.
        $('#adp-email-simulator').css(cssVar, color);
    });

    // --- 2. Live Preview Texto Footer ---
    $('#adp_footer_text').on('input', function() {
        var text = $(this).val();
        // Buscamos dentro del simulador
        $('#adp-email-simulator').find('.footer p:first-child').html(text);
    });

    // --- 3. Lógica de Cambio de Vista (AJAX) ---
    $('input[name="adp_preview_mode"]').on('change', function() {
        var mode = $(this).val();
        var $container = $('#adp-sim-content-area'); // El área blanca del contenido
        var $title = $('#adp-sim-main-title'); // El título principal (Confirmación, etc)

        $container.css('opacity', 0.5); // Feedback visual de carga
        
        // Cambiamos el título visualmente rápido mientras carga el contenido
        if(mode === 'digest') {
            $title.text('Resumen Semanal');
        } else {
            $title.text('Nueva Publicación');
        }

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
                    // Inyectamos el HTML que viene de PHP (single.php o digest.php)
                    $container.html(response.data.html);
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
    
    // Función auxiliar para actualizar la vista previa en el panel y en el simulador
    function updateLogoInterface(url) {
        if(url) {
            // Panel de Control (Izquierda)
            $('#adp-logo-preview-wrapper').html('<img src="' + url + '" id="adp-logo-img">');
            $('#adp-remove-logo-btn').removeClass('hidden');
            $('#adp_logo_url').val(url);

            // Simulador (Derecha)
            // Buscamos la imagen dentro del simulador. Si no existe el tag img, lo creamos.
            var imgHtml = '<img src="' + url + '" class="adp-sim-logo-img" style="max-height:50px; margin:0 auto;">';
            var $simHeaderLink = $('#adp-email-simulator .header h1 a');
            
            // Si el header tiene un link, metemos la imagen dentro.
            if ($simHeaderLink.length) {
                $simHeaderLink.html(imgHtml);
            } else {
                // Fallback por si la estructura cambia
                $('.adp-sim-logo-area').html(imgHtml);
            }

        } else {
            // Borrar Logo (Panel)
            $('#adp-logo-preview-wrapper').html('<span class="adp-no-logo-text">Sin logo seleccionado</span>');
            $('#adp-remove-logo-btn').addClass('hidden');
            $('#adp_logo_url').val('');

            // Borrar Logo (Simulador - volver al texto por defecto)
            var blogName = adp_vars.blogname || 'Vista Previa'; 
            $('#adp-email-simulator .header h1 a').text(blogName);
        }
    }

    // Botón Subir
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

    // Botón Quitar
    $('#adp-remove-logo-btn').on('click', function(e) {
        e.preventDefault();
        updateLogoInterface(''); // Pasamos vacío para resetear
    });

});