jQuery(document).ready(function($) {
    
    // --- Lógica del Color Picker ---
    if ( typeof $.fn.wpColorPicker === 'function' ) {
        $('.adp-color-field').wpColorPicker();
    }

    // --- Debug Live Monitor ---
    if ( typeof adp_vars !== 'undefined' && adp_vars.is_debug_page === '1' ) {
        
        function refreshDebugStats() {
            $.ajax({
                url: adp_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'adp_refresh_debug_stats',
                    nonce: adp_vars.nonce
                },
                success: function( response ) {
                    if ( response.success ) {
                        var stats = response.data.stats;
                        
                        // Actualizar contadores con efecto visual si cambian
                        updateCounter( '#adp-count-pending', stats['pending'] );
                        updateCounter( '#adp-count-complete', stats['complete'] );
                        updateCounter( '#adp-count-failed', stats['failed'] );
                        updateCounter( '#adp-count-in-progress', stats['in-progress'] );

                        // Actualizar tabla
                        $('#adp-debug-table-body').html( response.data.table_html );
                    }
                }
            });
        }

        function updateCounter( selector, newValue ) {
            var $el = $(selector);
            var currentVal = parseInt( $el.text() );
            
            // Si el valor cambia, hacemos un parpadeo de color
            if ( currentVal !== newValue ) {
                $el.text( newValue );
                $el.css('color', '#2271b1'); // Azul flash
                setTimeout(function(){
                    $el.css('color', ''); // Volver al original
                }, 500);
            }
        }

        // Ejecutar cada 3 segundos (3000ms)
        setInterval( refreshDebugStats, 3000 );
    }

});