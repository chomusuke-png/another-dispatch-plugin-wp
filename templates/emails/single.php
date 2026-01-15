<?php
/**
 * Template: Fragmento Single Post.
 * Genera el contenido y carga el layout maestro.
 */

// 1. Capturamos el contenido específico
ob_start(); 
?>

<?php if ( ! empty( $featured_image ) ) : ?>
    <a href="<?php echo esc_url( $post_link ?? '#' ); ?>">
        <img src="<?php echo esc_url( $featured_image ); ?>" alt="<?php echo esc_attr( $post_title ?? '' ); ?>" class="post-thumb">
    </a>
<?php endif; ?>

<h2 class="post-title">
    <a href="<?php echo esc_url( $post_link ?? '#' ); ?>"><?php echo esc_html( $post_title ?? 'Título del Post' ); ?></a>
</h2>

<div class="post-content">
    <?php echo $post_content ?? '<p>Contenido no disponible...</p>'; ?>
</div>

<div style="text-align: center; margin-top: 30px;">
    <a href="<?php echo esc_url( $post_link ?? '#' ); ?>" class="btn-primary">Leer en la web</a>
</div>

<?php
$email_content = ob_get_clean();

// 2. Configuramos variables para el layout
$email_title = 'Nueva publicación'; // Opcional: podrías pasar $post_title

// 3. Cargamos el layout maestro
include ADP_PATH . 'templates/emails/layout.php';
?>