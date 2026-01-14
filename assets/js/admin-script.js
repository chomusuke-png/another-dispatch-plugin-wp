jQuery(document).ready(function($) {

    // Inicializar Color Picker con callback 'change'
    $('.adp-color-field').wpColorPicker({
        change: function(event, ui) {
            var color = ui.color.toString();
            var target = $(this).data('target');
            
            // Actualizar preview en tiempo real
            if(target === '--header-bg') {
                $('.adp-fake-email-header').css('background-color', color);
            } else if(target === '--header-text') {
                $('.adp-fake-email-header').css('color', color);
            } else if(target === '--btn-bg') {
                $('.adp-fake-btn').css('background-color', color);
            } else if(target === '--btn-text') {
                $('.adp-fake-btn').css('color', color);
            } else if(target === '--link-color') {
                $('.adp-fake-link').css('color', color);
            }
        }
    });

    // Live Preview del Footer Text
    $('#adp-footer-input').on('input', function() {
        var text = $(this).val();
        // Permitir saltos de linea simples en preview
        text = text.replace(/\n/g, '<br>');
        $('#adp-preview-footer-content').html(text);
    });

    // WP Media Uploader para Logo
    var frame;
    $('#adp-upload-logo-btn').on('click', function(e) {
        e.preventDefault();

        // Si el frame ya existe, reabrilo
        if (frame) {
            frame.open();
            return;
        }

        // Crear frame nuevo
        frame = wp.media({
            title: 'Selecciona el Logo del Email',
            button: {
                text: 'Usar este logo'
            },
            multiple: false
        });

        // Al seleccionar imagen
        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            var imgUrl = attachment.url;

            // Guardar URL en input hidden
            $('#adp_logo_url').val(imgUrl);

            // Actualizar preview en formulario
            $('#adp-logo-preview-img').attr('src', imgUrl).show();
            $('#adp-remove-logo-btn').show();

            // Actualizar preview EN VIVO (Lado derecho)
            $('#adp-preview-logo-img').attr('src', imgUrl);
            $('#adp-preview-logo-container').show();
            $('#adp-preview-blogname').hide(); // Ocultar texto si hay logo
        });

        frame.open();
    });

    // Botón Remover Logo
    $('#adp-remove-logo-btn').on('click', function(e) {
        e.preventDefault();
        
        // Limpiar input
        $('#adp_logo_url').val('');
        
        // Limpiar UI formulario
        $('#adp-logo-preview-img').hide().attr('src', '');
        $(this).hide();

        // Limpiar Preview En Vivo
        $('#adp-preview-logo-container').hide();
        $('#adp-preview-blogname').show(); // Volver a mostrar texto
    });

    if ( typeof adp_vars !== 'undefined' && adp_vars.is_debug_page === '1' ) {
        
        function refreshDebugStats() {
            var $table = $('#adp-debug-table-body');
            var $indicator = $('#adp-live-indicator span.dashicons');

            // Efecto visual de carga (opcional)
            $table.css('opacity', '0.6'); 
            
            $.ajax({
                url: adp_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'adp_refresh_debug_stats',
                    nonce: adp_vars.nonce
                },
                success: function(response) {
                    if(response.success) {
                        // 1. Actualizar Tarjetas de Contadores
                        // Buscamos por ID específico que definiste en debug.php
                        if(response.data.stats) {
                            $('#adp-count-pending').text(response.data.stats['pending']);
                            $('#adp-count-complete').text(response.data.stats['complete']);
                            $('#adp-count-failed').text(response.data.stats['failed']);
                            $('#adp-count-in-progress').text(response.data.stats['in-progress']);
                        }

                        // 2. Actualizar Tabla
                        if(response.data.table_html) {
                            $table.html(response.data.table_html);
                        }
                    }
                },
                complete: function() {
                    $table.css('opacity', '1');
                }
            });
        }

        // Ejecutar inmediatamente al cargar
        // refreshDebugStats(); 

        // Ejecutar cada 5 segundos (Polling)
        setInterval(refreshDebugStats, 5000);
    }

});