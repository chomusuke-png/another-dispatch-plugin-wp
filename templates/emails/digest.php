<?php
/**
 * Template: Fragmento Digest (Resumen).
 */
if (!defined('ABSPATH')) {
    exit;
}

ob_start(); 
?>

<?php if (!empty($postsList)) : ?>
    <?php foreach ($postsList as $post) : 
        $postId      = is_object($post) ? $post->ID : $post['ID'];
        $postTitle   = is_object($post) ? $post->post_title : $post['post_title'];
        $postExcerpt = is_object($post) ? $post->post_excerpt : $post['post_excerpt'];
        $postLink    = isset($post->mock_link) ? $post->mock_link : get_permalink($postId);
    ?>
        <div class="post-item">
            <h2 class="post-title">
                <a href="<?php echo esc_url($postLink); ?>">
                    <?php echo esc_html($postTitle); ?>
                </a>
            </h2>
            
            <div class="post-excerpt">
                <?php echo wp_kses_post($postExcerpt); ?>
            </div>
            
            <a href="<?php echo esc_url($postLink); ?>" class="btn-link">Leer más &rarr;</a>
        </div>
    <?php endforeach; ?>
<?php else : ?>
    <p>No hay publicaciones nuevas en este periodo.</p>
<?php endif; ?>

<?php
$emailContent = ob_get_clean();
$emailTitle = $emailTitle ?? 'Resumen Semanal';

require ADP_PATH . 'templates/emails/layout.php';
?>