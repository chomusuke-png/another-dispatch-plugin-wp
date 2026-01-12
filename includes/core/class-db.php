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

    /**
     * Constructor.
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'adp_subscribers';
    }

    /**
     * Inserta un suscriptor en estado pendiente.
     *
     * @param string $email Email del usuario.
     * @param string $hash Token de activación.
     * @return int|false ID insertado o false en error.
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
     * Activa un suscriptor validando su email y hash.
     *
     * @param string $email Email a activar.
     * @param string $hash Token recibido.
     * @return bool True si se activó correctamente.
     */
    public function activate_subscriber( $email, $hash ) {
        global $wpdb;

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id FROM {$this->table_name} WHERE email = %s AND activation_hash = %s AND status = 'pending'",
                $email,
                $hash
            )
        );

        if ( ! $row ) {
            return false;
        }

        return $wpdb->update(
            $this->table_name,
            array( 'status' => 'active', 'activation_hash' => '' ), // Limpiamos hash tras activar
            array( 'id' => $row->id ),
            array( '%s', '%s' ),
            array( '%d' )
        );
    }

    /**
     * Verifica si el email existe (en cualquier estado).
     *
     * @param string $email
     * @return bool
     */
    public function exists( $email ) {
        global $wpdb;
        $query = $wpdb->prepare( "SELECT id FROM {$this->table_name} WHERE email = %s", $email );
        return (bool) $wpdb->get_var( $query );
    }

    /**
     * Obtiene solo suscriptores ACTIVOS para el envío de correos.
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function get_subscribers( $limit = 0, $offset = 0 ) {
        global $wpdb;
        
        // Filtramos por status = 'active'
        if ( $limit > 0 ) {
            return $wpdb->get_results( 
                $wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE status = 'active' LIMIT %d OFFSET %d", $limit, $offset ), 
                ARRAY_A 
            );
        }

        return $wpdb->get_results( "SELECT * FROM {$this->table_name} WHERE status = 'active'", ARRAY_A );
    }
    
    /**
     * Elimina un suscriptor.
     * * @param string $email
     * @return int|false
     */
    public function delete_subscriber( $email ) {
        global $wpdb;
        return $wpdb->delete( $this->table_name, array( 'email' => $email ), array( '%s' ) );
    }

    /**
     * Obtiene conteo total (opcional: filtrar por activos).
     * * @return string|null
     */
    public function get_subscriber_count() {
        global $wpdb;
        return $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_name} WHERE status = 'active'" );
    }
    
    /**
     * Elimina por ID.
     * * @param int $id
     * @return int|false
     */
    public function delete_subscriber_by_id( $id ) {
        global $wpdb;
        return $wpdb->delete( $this->table_name, array( 'id' => intval( $id ) ), array( '%d' ) );
    }
}