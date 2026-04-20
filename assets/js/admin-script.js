jQuery(document).ready(function($) {

    // 1. Inicializar Color Picker SOLO si la función existe en la página actual
    if ($.fn.wpColorPicker) {
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
    }

    // 2. Live Preview del Footer Text
    $('#adp-footer-input').on('input', function() {
        var text = $(this).val();
        text = text.replace(/\n/g, '<br>');
        $('#adp-preview-footer-content').html(text);
    });

    // 3. WP Media Uploader para Logo SOLO si wp.media existe
    if (typeof wp !== 'undefined' && wp.media) {
        var frame;
        $('#adp-upload-logo-btn').on('click', function(e) {
            e.preventDefault();
            if (frame) { frame.open(); return; }
            frame = wp.media({ title: 'Selecciona el Logo del Email', button: { text: 'Usar este logo' }, multiple: false });
            frame.on('select', function() {
                var imgUrl = frame.state().get('selection').first().toJSON().url;
                $('#adp_logo_url').val(imgUrl);
                $('#adp-logo-preview-img').attr('src', imgUrl).show();
                $('#adp-remove-logo-btn').show();
                $('#adp-preview-logo-img').attr('src', imgUrl);
                $('#adp-preview-logo-container').show();
                $('#adp-preview-blogname').hide();
            });
            frame.open();
        });

        $('#adp-remove-logo-btn').on('click', function(e) {
            e.preventDefault();
            $('#adp_logo_url').val('');
            $('#adp-logo-preview-img').hide().attr('src', '');
            $(this).hide();
            $('#adp-preview-logo-container').hide();
            $('#adp-preview-blogname').show(); 
        });
    }

    // 4. Lógica de la Consola en Vivo y Estadísticas (Solo en la página Debug)
    if ( typeof adp_vars !== 'undefined' && adp_vars.is_debug_page === '1' ) {
        
        function refreshDebugStats() {
            var $table = $('#adp-debug-table-body');
            $table.css('opacity', '0.6'); 
            $.ajax({
                url: adp_vars.ajax_url,
                type: 'POST',
                data: { action: 'adp_refresh_debug_stats', nonce: adp_vars.nonce },
                success: function(response) {
                    if(response.success) {
                        if(response.data.stats) {
                            $('#adp-count-pending').text(response.data.stats['pending']);
                            $('#adp-count-complete').text(response.data.stats['complete']);
                            $('#adp-count-failed').text(response.data.stats['failed']);
                            $('#adp-count-in-progress').text(response.data.stats['in-progress']);
                        }
                        if(response.data.table_html) {
                            $table.html(response.data.table_html);
                        }
                    }
                },
                complete: function() { $table.css('opacity', '1'); }
            });
        }

        // Lógica de Consola en Vivo
        function fetchLiveConsole(clear = false) {
            var $console = $('#adp-live-console-output');
            // Validar si el usuario está al final del scroll para mantenerlo anclado
            var isAtBottom = $console[0].scrollHeight - $console.scrollTop() <= $console.outerHeight() + 10;

            $.ajax({
                url: adp_vars.ajax_url,
                type: 'POST',
                data: { 
                    action: 'adp_get_live_console', 
                    nonce: adp_vars.nonce,
                    clear_log: clear ? 'true' : 'false'
                },
                success: function(response) {
                    if(response.success && response.data.html) {
                        $console.html(response.data.html);
                        if (isAtBottom || clear) {
                            $console.scrollTop($console[0].scrollHeight);
                        }
                    }
                }
            });
        }

        $('#adp-clear-console-btn').on('click', function() {
            fetchLiveConsole(true);
        });

        setInterval(refreshDebugStats, 5000);
        fetchLiveConsole();
        setInterval(fetchLiveConsole, 3000);
    }

});