<?php

/**
 * Class ADP_Widget
 *
 * Widget para mostrar el formulario de suscripción en sidebars.
 */
class ADP_Widget extends WP_Widget {

    /**
     * Constructor del Widget.
     */
    public function __construct() {
        parent::__construct(
            'adp_widget',
            'Newsletter Form',
            array( 'description' => 'Muestra el formulario de suscripción de Another Dispatch Plugin' )
        );
    }

    /**
     * Renderiza el contenido del widget en el frontend.
     *
     * @param array $args Argumentos del widget (before_widget, etc).
     * @param array $instance Configuración guardada del widget.
     */
    public function widget( $args, $instance ) {
        echo $args['before_widget'];
        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
        }
        
        // Reutilizamos el template del formulario
        include ADP_PATH . 'templates/subscription-form.php';
        
        echo $args['after_widget'];
    }

    /**
     * Renderiza el formulario de configuración en el Admin.
     *
     * @param array $instance Configuración actual.
     */
    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : 'Suscríbete';
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">Título:</label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <?php
    }

    /**
     * Guarda la configuración del widget.
     *
     * @param array $new_instance Nuevos valores.
     * @param array $old_instance Viejos valores.
     * @return array Instancia sanitizada.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
        return $instance;
    }
}