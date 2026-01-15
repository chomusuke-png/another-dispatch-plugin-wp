<?php

/**
 * Class ADP_DB
 * Abstracción optimizada para interactuar con la base de datos.
 */
class ADP_DB {

    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'adp_subscribers';
    }

    /**
     * Helper privado para generar cláusulas WHERE seguras.
     */
    private function build_status_where( $status ) {
        global $wpdb;
        if ( 'all' === $status ) {
            return '';
        }
        $safe_status = ( 'pending' === $status ) ? 'pending' : 'active';
        return $wpdb->prepare( "WHERE status = %s", $safe_status );
    }

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

    public function activate_subscriber( $email, $hash ) {
        global $wpdb;
        // Optimizamos seleccionando solo ID, es más rápido que *
        $id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$this->table_name} WHERE email = %s AND activation_hash = %s AND status = 'pending'", $email, $hash ) );

        if ( ! $id ) return false;

        return $wpdb->update(
            $this->table_name,
            array( 'status' => 'active', 'activation_hash' => '' ),
            array( 'id' => $id ),
            array( '%s', '%s' ),
            array( '%d' )
        );
    }

    public function get_subscribers( $limit = 0, $offset = 0, $status = 'active' ) {
        global $wpdb;
        
        $where = $this->build_status_where( $status );
        $limit_clause = ( $limit > 0 ) ? $wpdb->prepare( "LIMIT %d OFFSET %d", $limit, $offset ) : "";

        return $wpdb->get_results( "SELECT * FROM {$this->table_name} $where ORDER BY created_at DESC $limit_clause", ARRAY_A );
    }

    public function get_subscriber_count( $status = 'active' ) {
        global $wpdb;
        $where = $this->build_status_where( $status );
        return $wpdb->get_var( "SELECT COUNT(id) FROM {$this->table_name} $where" );
    }
    
    public function exists( $email ) {
        global $wpdb;
        return (bool) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$this->table_name} WHERE email = %s", $email ) );
    }

    public function delete_subscriber_by_id( $id ) {
        global $wpdb;
        return $wpdb->delete( $this->table_name, array( 'id' => intval( $id ) ), array( '%d' ) );
    }
    
    public function delete_subscriber( $email ) {
        global $wpdb;
        return $wpdb->delete( $this->table_name, array( 'email' => $email ), array( '%s' ) );
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

    public function bulk_insert( $emails ) {
        global $wpdb;
        if ( empty( $emails ) ) return 0;

        $values = array();
        $now = current_time( 'mysql' );
        
        foreach ( $emails as $email ) {
            $safe_email = sanitize_email( $email );
            if ( is_email( $safe_email ) ) {
                $safe_email = esc_sql( $safe_email ); 
                $values[] = "('$safe_email', 'active', '$now')"; 
            }
        }

        if ( empty( $values ) ) return 0;

        $values_str = implode( ', ', $values );
        // INSERT IGNORE es más eficiente que verificar uno por uno
        return (int) $wpdb->query( "INSERT IGNORE INTO {$this->table_name} (email, status, created_at) VALUES $values_str" );
    }
    
    public function get_all_subscribers_for_export() {
        global $wpdb;
        // Solo traemos columnas necesarias para el CSV
        return $wpdb->get_results( "SELECT email, status, created_at FROM {$this->table_name}", ARRAY_A );
    }
}