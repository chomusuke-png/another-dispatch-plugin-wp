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
        $thumbnailUrl = '';
        if (isset($post->mock_link)) {
            $thumbnailUrl = 'https://placehold.co/150x150';
        } elseif (has_post_thumbnail($postId)) {
            $thumbnailUrl = get_the_post_thumbnail_url($postId, 'thumbnail'); 
        }
    ?>
        <div class="post-item">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <?php if (!empty($thumbnailUrl)) : ?>
                        <td width="120" valign="top" style="padding-right: 20px; width: 120px;">
                            <a href="<?php echo esc_url($postLink); ?>">
                                <img src="<?php echo esc_url($thumbnailUrl); ?>" alt="<?php echo esc_attr($postTitle); ?>" style="width: 100%; max-width: 120px; height: auto; border-radius: 6px; display: block;">
                            </a>
                        </td>
                    <?php endif; ?>
                    
                    <td valign="top">
                        <h2 class="post-title" style="margin-top: 0;">
                            <a href="<?php echo esc_url($postLink); ?>">
                                <?php echo esc_html($postTitle); ?>
                            </a>
                        </h2>
                        
                        <div class="post-excerpt">
                            <?php echo wp_kses_post($postExcerpt); ?>
                        </div>
                        
                        <a href="<?php echo esc_url($postLink); ?>" class="btn-link">Leer más &rarr;</a>
                    </td>
                </tr>
            </table>
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