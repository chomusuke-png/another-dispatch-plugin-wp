/**
 * Archivo: assets/js/admin-customizer.js
 * Descripción: Lógica para el Customizer de Emails (Nativo & Switcher).
 */

jQuery(document).ready(function($) {
    
    // =========================================================
    // 1. MANEJO DE COLORES (Nativo HTML5)
    // =========================================================
    $('.adp-color-native').on('input change', function() {
        var color = $(this).val();
        var cssVar = $(this).data('css-var');
        
        // Actualizar Variable CSS del Simulador
        $('#adp-email-simulator').css(cssVar, color);
    });

    // =========================================================
    // 2. LIVE PREVIEW FOOTER
    // =========================================================
    $('#adp_footer_text').on('input', function() {
        var text = $(this).val();
        $('#adp-sim-footer-content').html(text);
    });

    // =========================================================
    // 3. SWITCHER DE VISTAS (Single vs Digest)
    // =========================================================
    
    // Plantillas simuladas (Strings HTML)
    const templates = {
        single: `
            <div class="adp-sim-post-single">
                <div class="adp-sim-thumb" style="background-color: #eee; height: 150px; width: 100%; display: flex; align-items: center; justify-content: center; color: #999; margin-bottom: 20px;">
                    <span>Imagen Destacada</span>
                </div>
                <h3 style="margin-top: 0; color: #222;">Actualización Importante: Nueva Versión 2.0</h3>
                <p>Hola <strong>Suscriptor</strong>,</p>
                <p>Acabamos de lanzar una gran actualización en nuestro proyecto. Hemos añadido nuevas mecánicas de juego y corregido varios bugs reportados por la comunidad.</p>
                <p>Haz clic en el botón de abajo para leer la nota completa del parche.</p>
                
                <div style="text-align: center; margin-top: 25px;">
                    <a href="#" class="adp-sim-btn">Leer en la web</a>
                </div>
            </div>
        `,
        digest: `
            <div class="adp-sim-digest-list">
                <p style="margin-bottom: 20px;">Aquí tienes el resumen de lo que pasó esta semana:</p>
                
                <div class="adp-sim-post-item" style="border-bottom: 1px solid #eee; padding-bottom: 20px; margin-bottom: 20px;">
                    <h4 style="margin: 0 0 10px 0;"><a href="#" style="color: #2271b1; text-decoration: none;">Análisis del Mercado 2026</a></h4>
                    <p style="font-size: 14px; color: #666; margin: 0 0 10px;">Un vistazo profundo a las tendencias económicas que se avecinan para el próximo año fiscal...</p>
                    <a href="#" style="font-size: 13px; font-weight: bold; color: #2271b1;">Leer más &rarr;</a>
                </div>

                <div class="adp-sim-post-item" style="border-bottom: 1px solid #eee; padding-bottom: 20px; margin-bottom: 20px;">
                    <h4 style="margin: 0 0 10px 0;"><a href="#" style="color: #2271b1; text-decoration: none;">Guía de Optimización WP</a></h4>
                    <p style="font-size: 14px; color: #666; margin: 0 0 10px;">Aprende a mejorar el rendimiento de tu sitio web con estos simples consejos de caché...</p>
                    <a href="#" style="font-size: 13px; font-weight: bold; color: #2271b1;">Leer más &rarr;</a>
                </div>

                <div class="adp-sim-post-item">
                    <h4 style="margin: 0 0 10px 0;"><a href="#" style="color: #2271b1; text-decoration: none;">Entrevista con el CEO</a></h4>
                    <p style="font-size: 14px; color: #666; margin: 0 0 10px;">Conversamos sobre el futuro de la inteligencia artificial y su impacto en el desarrollo...</p>
                    <a href="#" style="font-size: 13px; font-weight: bold; color: #2271b1;">Leer más &rarr;</a>
                </div>
            </div>
        `
    };

    function updatePreviewMode(mode) {
        var contentArea = $('#adp-sim-content-area');
        var titleArea = $('#adp-sim-main-title');
        
        // 1. Cambiar Contenido
        if (mode === 'digest') {
            contentArea.html(templates.digest);
            titleArea.text('Resumen Semanal');
        } else {
            contentArea.html(templates.single);
            titleArea.text('Nueva Publicación');
        }

        // 2. Re-aplicar colores dinámicos a elementos inyectados si es necesario
        // (En este CSS usamos variables CSS heredadas, así que es automático)
        
        // Pero el botón usa el color de header como fondo, necesitamos forzarlo si se inyecta de nuevo
        // Esto es un "hack" porque el botón en Single usa estilos inline simulados con la variable CSS
        $('.adp-sim-btn').css({
            'background-color': 'var(--adp-email-header-bg)',
            'color': 'var(--adp-email-header-text)'
        });
    }

    // Listener de Radio Buttons
    $('input[name="adp_preview_mode"]').on('change', function() {
        updatePreviewMode($(this).val());
    });

    // Inicializar
    updatePreviewMode('single');


    // =========================================================
    // 4. LOGO UPLOAD (WP Media)
    // =========================================================
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
            $('#adp_email_logo').val(attachment.url);
            
            // UI Admin
            $('#adp-logo-preview-wrapper').html('<img src="' + attachment.url + '" id="adp-logo-img">');
            $('#adp-remove-logo-btn').removeClass('hidden');

            // UI Simulator
            var simLogoArea = $('.adp-sim-logo-area');
            if (simLogoArea.find('img').length > 0) {
                simLogoArea.find('img').attr('src', attachment.url);
            } else {
                simLogoArea.html('<img src="' + attachment.url + '" class="adp-sim-logo-img">');
            }
        });
        mediaUploader.open();
    });

    $('#adp-remove-logo-btn').on('click', function(e) {
        e.preventDefault();
        $('#adp_email_logo').val('');
        $('#adp-logo-preview-wrapper').html('<span class="adp-no-logo-text">Sin logo seleccionado</span>');
        $('.adp-sim-logo-area').empty();
        $(this).addClass('hidden');
    });

});