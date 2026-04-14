<?php

declare(strict_types=1);

namespace Zumito\ADP\PublicHooks;

use WP_Widget;

class Widget extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'adp_subscription_widget',
            'ADP Newsletter',
            ['description' => 'Muestra un formulario de suscripción para Another Dispatch Plugin.']
        );

        add_action('widgets_init', function () {
            register_widget(self::class);
        });
    }

    /**
     * @param array<string, mixed> $args
     * @param array<string, mixed> $instance
     */
    public function widget($args, $instance): void
    {
        echo $args['before_widget'] ?? '';

        $title = !empty($instance['title']) ? apply_filters('widget_title', $instance['title']) : '';
        if ($title !== '') {
            echo ($args['before_title'] ?? '') . esc_html($title) . ($args['after_title'] ?? '');
        }

        echo do_shortcode(sprintf(
            '[adp_subscribe title="" desc="%s"]',
            esc_attr($instance['description'] ?? '')
        ));

        echo $args['after_widget'] ?? '';
    }

    /**
     * @param array<string, mixed> $instance
     */
    public function form($instance): void
    {
        $title = $instance['title'] ?? 'Suscríbete';
        $description = $instance['description'] ?? 'Recibe nuestras noticias.';
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">Título:</label>
            <input class="widefat" 
                   id="<?php echo esc_attr($this->get_field_id('title')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>" 
                   type="text" 
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('description')); ?>">Descripción:</label>
            <textarea class="widefat" 
                      id="<?php echo esc_attr($this->get_field_id('description')); ?>" 
                      name="<?php echo esc_attr($this->get_field_name('description')); ?>"><?php echo esc_textarea($description); ?></textarea>
        </p>
        <?php
    }

    /**
     * @param array<string, mixed> $newInstance
     * @param array<string, mixed> $oldInstance
     * @return array<string, mixed>
     */
    public function update($newInstance, $oldInstance): array
    {
        $instance = [];
        $instance['title'] = !empty($newInstance['title']) ? sanitize_text_field($newInstance['title']) : '';
        $instance['description'] = !empty($newInstance['description']) ? sanitize_textarea_field($newInstance['description']) : '';

        return $instance;
    }
}