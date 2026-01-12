<?php

/**
 * Class ADP_DB
 *
 * Abstracción para interactuar con la base de datos de suscriptores.
 */
class ADP_DB {

    /**
     * @var string
     */
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'adp_subscribers';
    }

    /**
     * Inserta un suscriptor en estado pendiente.
     * @param string $email
     * @param string $hash
     * @return int|false
     */
    public function add_pending_subscriber( $email, $hash ) {
        global $wpdb;
        return $wpdb->insert(
            $this->table_name,
            array(
                'email'           => sanitize_email( $email ),
                'status'          => 'pending',
                'activation_hash' => $hash,
                'created_at'      => current_time( 'mysql' )
            ),
            array( '%s', '%s', '%s', '%s' )
        );
    }

    /**
     * Activa un suscriptor.
     * @param string $email
     * @param string $hash
     * @return bool
     */
    public function activate_subscriber( $email, $hash ) {
        global $wpdb;
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM {$this->table_name} WHERE email = %s AND activation_hash = %s AND status = 'pending'", $email, $hash ) );

        if ( ! $row ) return false;

        return $wpdb->update(
            $this->table_name,
            array( 'status' => 'active', 'activation_hash' => '' ),
            array( 'id' => $row->id ),
            array( '%s', '%s' ),
            array( '%d' )
        );
    }

    /**
     * Obtiene suscriptores con filtro de estado.
     *
     * @param int $limit
     * @param int $offset
     * @param string $status 'active', 'pending', o 'all'.
     * @return array
     */
    public function get_subscribers( $limit = 0, $offset = 0, $status = 'active' ) {
        global $wpdb;
        
        $where_clause = "";
        if ( 'all' !== $status ) {
            // Validamos que el status sea seguro
            $safe_status = ( 'pending' === $status ) ? 'pending' : 'active';
            $where_clause = $wpdb->prepare( "WHERE status = %s", $safe_status );
        }

        $limit_clause = "";
        if ( $limit > 0 ) {
            $limit_clause = $wpdb->prepare( "LIMIT %d OFFSET %d", $limit, $offset );
        }

        return $wpdb->get_results( "SELECT * FROM {$this->table_name} $where_clause ORDER BY created_at DESC $limit_clause", ARRAY_A );
    }

    /**
     * Obtiene conteo total filtrado por estado.
     *
     * @param string $status 'active', 'pending', o 'all'.
     * @return string|null
     */
    public function get_subscriber_count( $status = 'active' ) {
        global $wpdb;
        
        if ( 'all' === $status ) {
            return $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_name}" );
        }

        $safe_status = ( 'pending' === $status ) ? 'pending' : 'active';
        return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$this->table_name} WHERE status = %s", $safe_status ) );
    }
    
    public function exists( $email ) {
        global $wpdb;
        return (bool) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$this->table_name} WHERE email = %s", $email ) );
    }

    public function delete_subscriber_by_id( $id ) {
        global $wpdb;
        return $wpdb->delete( $this->table_name, array( 'id' => intval( $id ) ), array( '%d' ) );
    }

    public function get_subscriber( $email ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE email = %s", $email ) );
    }

    public function update_subscriber_hash( $email, $hash ) {
        global $wpdb;
        return $wpdb->update(
            $this->table_name,
            array( 'activation_hash' => $hash, 'created_at' => current_time( 'mysql' ) ),
            array( 'email' => $email, 'status' => 'pending' ),
            array( '%s', '%s' ),
            array( '%s', '%s' )
        );
    }
     
    public function delete_subscriber( $email ) {
        global $wpdb;
        return $wpdb->delete( $this->table_name, array( 'email' => $email ), array( '%s' ) );
    }

    /**
     * Importación masiva de suscriptores.
     */
    public function bulk_insert( $emails ) {
        global $wpdb;
        
        if ( empty( $emails ) ) return 0;

        // Preparamos los valores para SQL: (email, status, created_at)
        $values = array();
        $now = current_time( 'mysql' );
        
        foreach ( $emails as $email ) {
            $safe_email = sanitize_email( $email );
            if ( is_email( $safe_email ) ) {
                // Escapamos manualmente para la query raw
                $safe_email = esc_sql( $safe_email ); 
                // Insertamos como 'active' directamente (asumimos consentimiento previo al importar)
                $values[] = "('$safe_email', 'active', '$now')"; 
            }
        }

        if ( empty( $values ) ) return 0;

        // Construimos la query masiva
        $values_str = implode( ', ', $values );
        $sql = "INSERT IGNORE INTO {$this->table_name} (email, status, created_at) VALUES $values_str";

        $result = $wpdb->query( $sql );
        return (int) $result;
    }
    
    /**
     * Obtener todos los emails para exportar (sin paginación).
     */
    public function get_all_subscribers_for_export() {
        global $wpdb;
        return $wpdb->get_results( "SELECT email, status, created_at FROM {$this->table_name}", ARRAY_A );
    }
}