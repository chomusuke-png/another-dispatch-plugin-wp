<?php

/**
 * Class ADP_Email_Sender
 *
 * Procesa el envío de correos utilizando Action Scheduler para evitar timeouts.
 * Maneja envíos individuales, semanales y mensuales.
 */
class ADP_Email_Sender {

    /** @var ADP_DB */
    private $db;

    /**
     * Constructor.
     *
     * @param ADP_DB $db Instancia de la base de datos del plugin.
     */
    public function __construct( $db ) {
        $this->db = $db;

        add_action( 'adp_process_batch_send', array( $this, 'process_batch' ), 10, 2 );
        add_action( 'adp_monthly_digest_event', array( $this, 'send_digest_batch' ), 10, 1 );
        add_action( 'adp_weekly_digest_event', array( $this, 'send_weekly_digest_batch' ), 10, 1 );
    }

    /**
     * Obtiene el tamaño del lote configurado.
     *
     * @return int Número de correos por lote.
     */
    private function get_batch_size() {
        return (int) get_option( 'adp_batch_size', 50 );
    }

    /**
     * Obtiene el retraso entre lotes configurado.
     *
     * @return int Segundos de espera.
     */
    private function get_batch_delay() {
        return (int) get_option( 'adp_batch_delay', 120 );
    }

    /**
     * Procesa un lote de correos para un post individual.
     *
     * @param int $post_id ID del post.
     * @param int $offset Desplazamiento para paginación de suscriptores.
     */
    public function process_batch( $post_id, $offset = 0 ) {
        $limit = $this->get_batch_size();
        
        $subscribers = $this->db->get_subscribers( $limit, $offset, 'active' );

        if ( empty( $subscribers ) ) {
            return;
        }

        $post = get_post( $post_id );
        if ( ! $post ) return;

        $subject = 'Nuevo post: ' . $post->post_title;
        $body    = $this->prepare_single_email_html( $post );
        $headers = $this->get_headers();

        foreach ( $subscribers as $sub ) {
            $this->send_individual_email( $sub['email'], $subject, $body, $headers );
        }

        if ( count( $subscribers ) === $limit ) {
            $new_offset = $offset + $limit;
            $delay      = $this->get_batch_delay();

            if ( function_exists( 'as_schedule_single_action' ) ) {
                as_schedule_single_action( 
                    time() + $delay, 
                    'adp_process_batch_send', 
                    array( 'post_id' => $post_id, 'offset' => $new_offset ),
                    'adp_emails' 
                );
            }
        }
    }

    /**
     * Callback para el evento de resumen mensual.
     *
     * @param int $offset Paginación de suscriptores.
     */
    public function send_digest_batch( $offset = 0 ) {
        // [DEBUG] Inicio traza mensual
        error_log( '[ADP DEBUG] Iniciando send_digest_batch. Frecuencia configurada: ' . get_option( 'adp_delivery_frequency' ) );

        if ( 'monthly' !== get_option( 'adp_delivery_frequency' ) ) {
            error_log( '[ADP DEBUG] ABORTADO: La frecuencia no es monthly.' );
            return;
        }

        $first_day = date( 'Y-m-01', strtotime( 'last month' ) );
        $last_day  = date( 'Y-m-t', strtotime( 'last month' ) );
        
        $title = 'Resumen de ' . date_i18n( 'F Y', strtotime( 'last month' ) );

        $this->process_digest_dispatch( $first_day, $last_day, $title, 'adp_monthly_digest_event', $offset );
    }

    /**
     * Callback para el evento de resumen semanal.
     *
     * @param int $offset Paginación de suscriptores.
     */
    public function send_weekly_digest_batch( $offset = 0 ) {
        // [DEBUG] Inicio traza semanal
        $freq = get_option( 'adp_delivery_frequency' );
        error_log( '[ADP DEBUG] Iniciando send_weekly_digest_batch. Frecuencia actual en DB: "' . $freq . '"' );

        if ( 'weekly' !== $freq ) {
            // [DEBUG] Razón de fallo común #1
            error_log( '[ADP DEBUG] ABORTADO: La frecuencia en ajustes no es "weekly". El botón forzó el evento, pero la función lo rechaza por seguridad.' );
            return;
        }

        // Usamos 'monday last week' y 'sunday last week' que son más seguros en PHP
        $start_date = date( 'Y-m-d', strtotime( 'monday last week' ) );
        $end_date   = date( 'Y-m-d', strtotime( 'sunday last week' ) );
        
        // [DEBUG] Verificación de fechas
        error_log( '[ADP DEBUG] Rango de fechas calculado: ' . $start_date . ' al ' . $end_date );

        $title = 'Resumen Semanal (' . date_i18n( 'd M', strtotime( $start_date ) ) . ' - ' . date_i18n( 'd M', strtotime( $end_date ) ) . ')';

        $this->process_digest_dispatch( $start_date, $end_date, $title, 'adp_weekly_digest_event', $offset );
    }

    /**
     * Lógica centralizada para envío de digest (mensual o semanal).
     *
     * @param string $start_date Fecha inicio Y-m-d.
     * @param string $end_date Fecha fin Y-m-d.
     * @param string $email_title Título para el correo y el template.
     * @param string $hook_name Nombre del hook para reprogramar el siguiente lote.
     * @param int    $offset Paginación actual.
     */
    private function process_digest_dispatch( $start_date, $end_date, $email_title, $hook_name, $offset ) {
        $args = array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'date_query'     => array(
                array( 'after' => $start_date, 'before' => $end_date, 'inclusive' => true ),
            ),
        );
        
        $posts = get_posts( $args );

        // [DEBUG] Verificación de posts
        if ( empty( $posts ) ) {
            error_log( '[ADP DEBUG] ABORTADO: No se encontraron posts en el rango solicitado.' );
            // [DEBUG] Dump de la query para ver qué falló
            error_log( '[ADP DEBUG] Query args: ' . print_r( $args, true ) );
            return;
        } else {
            error_log( '[ADP DEBUG] Posts encontrados: ' . count( $posts ) );
            foreach($posts as $p) {
                error_log( '[ADP DEBUG] - Post candidato: ' . $p->post_title . ' (' . $p->post_date . ')' );
            }
        }

        $limit       = $this->get_batch_size();
        $subscribers = $this->db->get_subscribers( $limit, $offset, 'active' );
        
        // [DEBUG] Verificación de suscriptores
        if ( empty( $subscribers ) ) {
            error_log( '[ADP DEBUG] ABORTADO: No hay suscriptores activos en este lote (Offset: ' . $offset . ').' );
            return;
        }
        error_log( '[ADP DEBUG] Enviando lote a ' . count($subscribers) . ' suscriptores.' );

        $blog_name  = get_bloginfo( 'name' );
        $posts_list = $posts; 

        // Preparar contenido del template
        ob_start();
        $unsubscribe_link = '##UNSUBSCRIBE_URL##';
        include ADP_PATH . 'templates/emails/digest.php';
        $template_html = ob_get_clean();

        $subject = $email_title . ' - ' . $blog_name;
        $headers = $this->get_headers();

        foreach ( $subscribers as $sub ) {
            $this->send_individual_email( $sub['email'], $subject, $template_html, $headers );
        }

        // Reprogramar siguiente lote si quedan suscriptores
        if ( count( $subscribers ) === $limit ) {
            error_log( '[ADP DEBUG] Reprogramando siguiente lote...' );
            $delay = $this->get_batch_delay();
            wp_schedule_single_event( time() + $delay, $hook_name, array( $offset + $limit ) );
        } else {
            error_log( '[ADP DEBUG] Envío finalizado (último lote).' );
        }
    }

    /**
     * Genera el HTML para un post individual.
     * * @param WP_Post $post Objeto del post.
     * @return string HTML del correo.
     */
    private function prepare_single_email_html( $post ) {
        $blog_name      = get_bloginfo( 'name' );
        $post_title     = $post->post_title;
        $post_link      = get_permalink( $post->ID );
        $featured_image = get_the_post_thumbnail_url( $post->ID, 'full' );
        $post_content   = apply_filters( 'the_content', $post->post_content );
        $base_url       = home_url();
        
        // Convertir rutas relativas a absolutas
        $post_content   = preg_replace( '/(href|src)=("|\')\//i', '$1=$2' . $base_url . '/', $post_content );
        
        $unsubscribe_link = '##UNSUBSCRIBE_URL##'; 
        ob_start();
        include ADP_PATH . 'templates/emails/single.php';
        return ob_get_clean();
    }

    /**
     * Envía el correo individual añadiendo headers de Entregabilidad (RFC 8058).
     *
     * @param string $email Destinatario.
     * @param string $subject Asunto.
     * @param string $body Cuerpo HTML.
     * @param array|string $headers Cabeceras.
     */
    private function send_individual_email( $email, $subject, $body, $headers ) {
        $hash      = md5( $email . wp_salt() );
        $unsub_url = add_query_arg( array( 'adp_action' => 'unsubscribe', 'email' => urlencode( $email ), 'hash' => $hash ), home_url( '/' ) );
        
        $user_headers = $headers;
        if ( ! is_array( $user_headers ) ) $user_headers = explode( "\n", str_replace( "\r\n", "\n", $user_headers ) );
        
        $user_headers[] = 'List-Unsubscribe: <' . $unsub_url . '>';
        $user_headers[] = 'List-Unsubscribe-Post: List-Unsubscribe=One-Click';

        $final_body = str_replace( '##UNSUBSCRIBE_URL##', $unsub_url, $body );
        
        // [DEBUG] Intento final de envío
        $result = wp_mail( $email, $subject, $final_body, $user_headers );
        if ( ! $result ) {
            error_log( '[ADP DEBUG] ERROR: wp_mail falló para ' . $email );
        }
    }

    /**
     * Genera las cabeceras estándar.
     *
     * @return array Cabeceras.
     */
    private function get_headers() {
        $from_email = get_option( 'adp_sender_email' ) ?: get_option( 'admin_email' );
        $blog_name  = get_bloginfo( 'name' );
        return array( 'Content-Type: text/html; charset=UTF-8', 'From: ' . $blog_name . ' <' . $from_email . '>' );
    }
}