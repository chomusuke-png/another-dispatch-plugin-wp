jQuery(document).ready(function($) {
    
    // 1. Colores Nativos & CSS Vars
    $('.adp-color-native').on('input change', function() {
        var color = $(this).val();
        var cssVar = $(this).data('css-var');
        $('#adp-email-simulator').css(cssVar, color);
        
        // Reforzar actualización del botón simulado si cambia el header
        if(cssVar === '--adp-email-header-bg') {
            $('.adp-sim-btn').css('background-color', color);
        }
        if(cssVar === '--adp-email-header-text') {
            $('.adp-sim-btn').css('color', color);
        }
    });

    // 2. Footer Texto Live
    $('#adp_footer_text').on('input', function() {
        $('#adp-sim-footer-content').html($(this).val());
    });

    // 3. Switcher de Vistas (Digest / Single)
    const templates = {
        single: `
            <div class="adp-sim-post-single">
                <div style="background-color: #eee; height: 150px; width: 100%; display: flex; align-items: center; justify-content: center; color: #999; margin-bottom: 20px;">
                    <span>Imagen Destacada</span>
                </div>
                <h3 style="margin-top: 0; color: #222;">Nueva Actualización Disponible</h3>
                <p>Hola <strong>Suscriptor</strong>,</p>
                <p>Hemos publicado un nuevo artículo que podría interesarte. Haz clic abajo para leerlo completo.</p>
                <div style="text-align: center; margin-top: 25px;">
                    <a href="#" class="adp-sim-btn" style="background-color: var(--adp-email-header-bg); color: var(--adp-email-header-text);">Leer en la web</a>
                </div>
            </div>
        `,
        digest: `
            <div class="adp-sim-digest-list">
                <p style="margin-bottom: 20px;">Resumen de la semana:</p>
                <div style="border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 15px;">
                    <h4 style="margin: 0 0 5px 0; color: #2271b1;">Tendencias 2026</h4>
                    <p style="font-size: 13px; color: #666; margin: 0;">Análisis detallado...</p>
                </div>
                <div style="padding-bottom: 15px;">
                    <h4 style="margin: 0 0 5px 0; color: #2271b1;">Tutorial WordPress</h4>
                    <p style="font-size: 13px; color: #666; margin: 0;">Cómo optimizar tu sitio...</p>
                </div>
            </div>
        `
    };

    function updatePreviewMode(mode) {
        var content = (mode === 'digest') ? templates.digest : templates.single;
        var title = (mode === 'digest') ? 'Resumen Semanal' : 'Nueva Publicación';
        
        $('#adp-sim-content-area').html(content);
        $('#adp-sim-main-title').text(title);
    }

    $('input[name="adp_preview_mode"]').on('change', function() {
        updatePreviewMode($(this).val());
    });
    
    // Iniciar en modo single
    updatePreviewMode('single');

    // 4. Logo Upload (Corregido IDs)
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
            // CORRECCIÓN: ID actualizado a adp_logo_url
            $('#adp_logo_url').val(attachment.url);
            $('#adp-logo-preview-wrapper').html('<img src="' + attachment.url + '" id="adp-logo-img">');
            $('#adp-remove-logo-btn').removeClass('hidden');
            
            // Actualizar simulador
            $('.adp-sim-logo-area').html('<img src="' + attachment.url + '" class="adp-sim-logo-img">');
        });
        mediaUploader.open();
    });

    $('#adp-remove-logo-btn').on('click', function(e) {
        e.preventDefault();
        $('#adp_logo_url').val('');
        $('#adp-logo-preview-wrapper').html('<span class="adp-no-logo-text">Sin logo</span>');
        $('.adp-sim-logo-area').empty();
        $(this).addClass('hidden');
    });

});