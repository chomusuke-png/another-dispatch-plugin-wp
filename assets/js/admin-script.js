jQuery(document).ready(function($) {

    // 1. Inicializar Color Picker con callback 'change'
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

    // 2. Live Preview del Footer Text
    $('#adp-footer-input').on('input', function() {
        var text = $(this).val();
        // Permitir saltos de linea simples en preview
        text = text.replace(/\n/g, '<br>');
        $('#adp-preview-footer-content').html(text);
    });

    // 3. WP Media Uploader para Logo
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

    // Debug Stats (Existente)
    $('#adp-refresh-stats').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var $table = $('#adp-debug-table-body');
        
        $btn.addClass('updating').text('Refrescando...');
        $table.css('opacity', '0.5');

        $.ajax({
            url: adp_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'adp_refresh_debug_stats',
                nonce: adp_vars.nonce
            },
            success: function(response) {
                if(response.success) {
                    // Actualizar contadores
                    $('.adp-stat-card').each(function() {
                        var status = $(this).data('status');
                        if(response.data.stats[status] !== undefined) {
                            $(this).find('strong').text(response.data.stats[status]);
                        }
                    });
                    // Actualizar tabla
                    $table.html(response.data.table_html).css('opacity', '1');
                } else {
                    alert('Error: ' + response.data);
                }
            },
            complete: function() {
                $btn.removeClass('updating').text('Refrescar Datos');
            }
        });
    });

});