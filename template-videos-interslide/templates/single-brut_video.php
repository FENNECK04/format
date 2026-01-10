<?php
/**
 * Single template for brut_video posts.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

while ( have_posts() ) {
    the_post();

    $post_id     = get_the_ID();
    $youtube_url = get_post_meta( $post_id, '_brut_video_youtube_url', true );
    $embed       = $youtube_url ? wp_oembed_get( $youtube_url ) : '';
    $categories  = brut_video_format_get_display_categories( $post_id );
    $excerpt     = has_excerpt() ? get_the_excerpt() : '';
    $date        = get_the_date();
    $author      = get_the_author();
    $clean_body  = brut_video_format_render_clean_content( $post_id );

    $related_query = new WP_Query(
        array(
            'post_type'      => 'brut_video',
            'posts_per_page' => 6,
            'post__not_in'   => array( $post_id ),
            'tax_query'      => ! empty( $categories ) ? array(
                array(
                    'taxonomy' => 'category',
                    'field'    => 'term_id',
                    'terms'    => wp_list_pluck( $categories, 'term_id' ),
                ),
            ) : array(),
        )
    );
    $next_query = new WP_Query(
        array(
            'post_type'      => 'brut_video',
            'posts_per_page' => 1,
            'post__not_in'   => array( $post_id ),
            'orderby'        => 'date',
            'order'          => 'DESC',
        )
    );
    ?>
    <main class="brut-video-format">
        <div class="brut-video-format__container">
            <div class="brut-video-format__meta">
                <a class="brut-video-format__meta-link" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <?php esc_html_e( 'Accueil', 'brut-video-format' ); ?>
                </a>
                <span class="brut-video-format__meta-sep">/</span>
                <span class="brut-video-format__meta-link"><?php esc_html_e( 'Vidéos', 'brut-video-format' ); ?></span>
                <?php if ( ! empty( $categories ) ) : ?>
                    <span class="brut-video-format__meta-sep">/</span>
                    <a class="brut-video-format__meta-link" href="<?php echo esc_url( get_term_link( $categories[0] ) ); ?>">
                        <?php echo esc_html( $categories[0]->name ); ?>
                    </a>
                <?php endif; ?>
            </div>

            <div class="brut-video-format__layout">
                <div class="brut-video-format__media">
                    <?php if ( $embed ) : ?>
                        <div class="brut-video-format__embed">
                            <?php echo $embed; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="brut-video-format__editorial">
                    <header class="brut-video-format__header">
                <?php if ( ! empty( $categories ) ) : ?>
                    <div class="brut-video-format__categories">
                        <?php foreach ( $categories as $category ) : ?>
                            <span class="brut-video-format__category">
                                <?php echo esc_html( $category->name ); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <h1 class="brut-video-format__title"><?php the_title(); ?></h1>

                <?php if ( $excerpt ) : ?>
                    <div class="brut-video-format__excerpt">
                        <?php echo wp_kses_post( wpautop( $excerpt ) ); ?>
                    </div>
                <?php endif; ?>

                <div class="brut-video-format__submeta">
                    <?php if ( $author ) : ?>
                        <span><?php echo esc_html( $author ); ?></span>
                        <span class="brut-video-format__submeta-sep">•</span>
                    <?php endif; ?>
                    <?php if ( $date ) : ?>
                        <span><?php echo esc_html( sprintf( __( 'Publié le %s', 'brut-video-format' ), $date ) ); ?></span>
                    <?php endif; ?>
                </div>
            </header>

                    <div class="brut-video-format__content">
                        <?php echo $clean_body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    </div>

                    <div class="brut-video-format__actions">
                        <button class="brut-video-format__copy" type="button" data-url="<?php echo esc_url( get_permalink() ); ?>">
                            <?php esc_html_e( 'Copier le lien', 'brut-video-format' ); ?>
                        </button>
                    </div>

                    <?php if ( $next_query->have_posts() ) : ?>
                        <?php $next_query->the_post(); ?>
                        <section class="brut-video-format__next">
                            <span class="brut-video-format__section-label"><?php esc_html_e( 'À suivre', 'brut-video-format' ); ?></span>
                            <a href="<?php the_permalink(); ?>" class="brut-video-format__next-card">
                                <div class="brut-video-format__next-media">
                                    <?php if ( has_post_thumbnail() ) : ?>
                                        <?php the_post_thumbnail( 'medium_large' ); ?>
                                    <?php endif; ?>
                                </div>
                                <div class="brut-video-format__next-content">
                                    <h3><?php the_title(); ?></h3>
                                </div>
                            </a>
                        </section>
                        <?php wp_reset_postdata(); ?>
                    <?php endif; ?>
                </div>

                <aside class="brut-video-format__sharebar">
                    <a class="brut-video-format__share" href="https://wa.me/?text=<?php echo esc_url( rawurlencode( get_permalink() ) ); ?>" target="_blank" rel="noopener" aria-label="WhatsApp">WA</a>
                    <a class="brut-video-format__share" href="https://t.me/share/url?url=<?php echo esc_url( rawurlencode( get_permalink() ) ); ?>" target="_blank" rel="noopener" aria-label="Telegram">TG</a>
                    <a class="brut-video-format__share" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_url( rawurlencode( get_permalink() ) ); ?>" target="_blank" rel="noopener" aria-label="Facebook">FB</a>
                    <a class="brut-video-format__share" href="https://twitter.com/intent/tweet?url=<?php echo esc_url( rawurlencode( get_permalink() ) ); ?>" target="_blank" rel="noopener" aria-label="X">X</a>
                    <a class="brut-video-format__share" href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo esc_url( rawurlencode( get_permalink() ) ); ?>" target="_blank" rel="noopener" aria-label="LinkedIn">in</a>
                </aside>
            </div>

            <?php if ( $related_query->have_posts() ) : ?>
                <section class="brut-video-format__section">
                    <h2 class="brut-video-format__section-title">Sur le même sujet</h2>
                    <div class="brut-video-format__grid">
                        <?php while ( $related_query->have_posts() ) : ?>
                            <?php $related_query->the_post(); ?>
                            <article class="brut-video-format__card">
                                <a href="<?php the_permalink(); ?>" class="brut-video-format__card-media">
                                    <?php if ( has_post_thumbnail() ) : ?>
                                        <?php the_post_thumbnail( 'medium_large' ); ?>
                                    <?php endif; ?>
                                </a>
                                <h3 class="brut-video-format__card-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>
                            </article>
                        <?php endwhile; ?>
                        <?php wp_reset_postdata(); ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </main>
    <?php
}

get_footer();
