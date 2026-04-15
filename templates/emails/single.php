<?php
/**
 * Template: Fragmento Single Post.
 * Genera el contenido y carga el layout maestro.
 */
if (!defined('ABSPATH')) {
    exit;
}

if (empty($featuredImage) && !empty($isPreview)) {
    $featuredImage = 'https://placehold.co/600x400';
}

ob_start(); 
?>

<?php if (!empty($featuredImage)) : ?>
    <div style="text-align: center; margin-bottom: 25px;">
        <a href="<?php echo esc_url($postLink ?? '#'); ?>" style="display: block;">
            <img src="<?php echo esc_url($featuredImage); ?>" alt="<?php echo esc_attr($postTitle ?? ''); ?>" class="post-thumb" width="600" style="width: 100%; max-width: 600px; height: auto; border-radius: 8px; display: block; margin: 0 auto;">
        </a>
    </div>
<?php endif; ?>

<h2 class="post-title">
    <a href="<?php echo esc_url($postLink ?? '#'); ?>">
        <?php echo esc_html($postTitle ?? 'Título del Post'); ?>
    </a>
</h2>

<div class="post-content">
    <?php echo $postContent ?? '<p>Contenido no disponible...</p>'; ?>
</div>

<div style="text-align: center; margin-top: 30px;">
    <a href="<?php echo esc_url($postLink ?? '#'); ?>" class="btn-primary">Leer en la web</a>
</div>

<?php
$emailContent = ob_get_clean();
$emailTitle = $postTitle ?? 'Nueva publicación';

require ADP_PATH . 'templates/emails/layout.php';
?>