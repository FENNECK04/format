<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>
<main class="isv isv-archive">
    <div class="isv-container">
        <header class="isv-archive__header">
            <h1 class="isv-title">Vidéos</h1>
            <p class="isv-excerpt">Toutes les vidéos Interslide.</p>
        </header>
        <div class="isv-grid">
            <?php if ( have_posts() ) : ?>
                <?php while ( have_posts() ) : ?>
                    <?php the_post(); ?>
                    <?php echo ISV_Plugin::render_card( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                <?php endwhile; ?>
            <?php else : ?>
                <p><?php esc_html_e( 'Aucune vidéo trouvée.', 'interslide-video-formats' ); ?></p>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php
get_footer();
