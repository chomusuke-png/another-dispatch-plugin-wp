<?php

/**
 * Class ADP_DB
 *
 * Abstracción para interactuar con la base de datos.
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
     * Inserta suscriptor.
     * @param string $email
     * @return int|false
     */
    public function add_subscriber( $email ) {
        global $wpdb;
        return $wpdb->insert(
            $this->table_name,
            array( 'email' => sanitize_email( $email ), 'created_at' => current_time( 'mysql' ) ),
            array( '%s', '%s' )
        );
    }

    /**
     * Elimina suscriptor.
     * @param string $email
     * @return int|false
     */
    public function delete_subscriber( $email ) {
        global $wpdb;
        return $wpdb->delete( $this->table_name, array( 'email' => $email ), array( '%s' ) );
    }

    /**
     * @return array
     */
    public function get_subscribers() {
        global $wpdb;
        return $wpdb->get_results( "SELECT * FROM {$this->table_name}", ARRAY_A );
    }

    /**
     * @param string $email
     * @return bool
     */
    public function exists( $email ) {
        global $wpdb;
        $query = $wpdb->prepare( "SELECT id FROM {$this->table_name} WHERE email = %s", $email );
        return (bool) $wpdb->get_var( $query );
    }

    /**
     * @return string|null
     */
    public function get_subscriber_count() {
        global $wpdb;
        return $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_name}" );
    }
}