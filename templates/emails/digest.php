<?php
/**
 * Template: Fragmento Digest (Resumen).
 */

if ( ! isset( $posts_list ) && isset( $posts ) ) $posts_list = $posts;

// 1. Capturamos el contenido específico
ob_start(); 
?>

<?php if ( ! empty( $posts_list ) ) : ?>
    <?php foreach ( $posts_list as $post ) : 
        $p_id      = is_object($post) ? $post->ID : $post['ID'];
        $p_title   = is_object($post) ? $post->post_title : $post['post_title'];
        $p_excerpt = is_object($post) ? $post->post_excerpt : $post['post_excerpt'];
        $p_link    = isset($post->mock_link) ? $post->mock_link : get_permalink( $p_id );
    ?>
        <div class="post-item">
            <h2 class="post-title">
                <a href="<?php echo esc_url( $p_link ); ?>">
                    <?php echo esc_html( $p_title ); ?>
                </a>
            </h2>
            
            <div class="post-excerpt">
                <?php echo wp_kses_post( $p_excerpt ); ?>
            </div>
            
            <a href="<?php echo esc_url( $p_link ); ?>" class="btn-link">Leer más &rarr;</a>
        </div>
    <?php endforeach; ?>
<?php else : ?>
    <p>No hay publicaciones nuevas en este periodo.</p>
<?php endif; ?>

<?php
$email_content = ob_get_clean();

// 2. El título ya suele venir definido en $email_title desde el Sender, si no, fallback.
if ( empty( $email_title ) ) $email_title = 'Resumen Semanal';

// 3. Cargamos el layout
include ADP_PATH . 'templates/emails/layout.php';
?>