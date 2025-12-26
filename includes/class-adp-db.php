<?php

/**
 * Class ADP_DB
 *
 * Abstracción para interactuar con la tabla de suscriptores.
 */
class ADP_DB {

    /**
     * @var string Nombre de la tabla con prefijo.
     */
    private $table_name;

    /**
     * Constructor. Define el nombre de la tabla.
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'adp_subscribers';
    }

    /**
     * Inserta un nuevo suscriptor en la base de datos.
     *
     * @param string $email El correo electrónico a suscribir.
     * @return int|false ID del insertado o false si falla.
     */
    public function add_subscriber( $email ) {
        global $wpdb;
        
        return $wpdb->insert(
            $this->table_name,
            array(
                'email' => sanitize_email( $email ),
                'created_at' => current_time( 'mysql' )
            ),
            array( '%s', '%s' )
        );
    }

    /**
     * Obtiene todos los correos de los suscriptores.
     *
     * @return array Lista de objetos con la propiedad email.
     */
    public function get_subscribers() {
        global $wpdb;
        return $wpdb->get_results( "SELECT * FROM {$this->table_name}", ARRAY_A );
    }
    
    /**
     * Verifica si un email ya existe.
     * * @param string $email
     * @return bool
     */
    public function exists( $email ) {
        global $wpdb;
        $query = $wpdb->prepare( "SELECT id FROM {$this->table_name} WHERE email = %s", $email );
        return (bool) $wpdb->get_var( $query );
    }

    /**
     * Obtiene la cantidad total de suscriptores.
     *
     * @return string|null Cantidad de filas en la tabla.
     */
    public function get_subscriber_count() {
        global $wpdb;
        return $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_name}" );
    }
}