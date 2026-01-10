<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

while ( have_posts() ) {
    the_post();

    $video_id   = get_the_ID();
    $categories = get_the_terms( $video_id, 'interslide_video_category' );
    $topics     = get_the_terms( $video_id, 'interslide_video_topic' );
    $duration   = get_post_meta( $video_id, 'isv_video_duration', true );
    $series     = get_post_meta( $video_id, 'isv_video_series', true );
    $episode    = get_post_meta( $video_id, 'isv_video_episode', true );
    $credits    = get_post_meta( $video_id, 'isv_video_credits', true );
    $sources    = get_post_meta( $video_id, 'isv_video_sources', true );
    $method     = get_post_meta( $video_id, 'isv_video_methodology', true );
    $next_id    = (int) get_post_meta( $video_id, 'isv_video_next', true );
    $related    = (array) get_post_meta( $video_id, 'isv_video_related', true );
    $excerpt    = get_the_excerpt();

    $next_post = $next_id ? get_post( $next_id ) : null;
    if ( ! $next_post ) {
        $next_post = get_posts(
            array(
                'post_type'      => 'interslide_video',
                'posts_per_page' => 1,
                'post_status'    => 'publish',
                'orderby'        => 'date',
                'order'          => 'DESC',
                'post__not_in'   => array( $video_id ),
                'tax_query'      => $categories ? array(
                    array(
                        'taxonomy' => 'interslide_video_category',
                        'field'    => 'term_id',
                        'terms'    => wp_list_pluck( $categories, 'term_id' ),
                    ),
                ) : array(),
            )
        );
        $next_post = $next_post ? $next_post[0] : null;
    }

    $related_query = array();
    if ( ! empty( $related ) ) {
        $related_query = new WP_Query(
            array(
                'post_type'      => 'interslide_video',
                'post__in'       => array_map( 'absint', $related ),
                'orderby'        => 'post__in',
                'posts_per_page' => 8,
            )
        );
    } else {
        $related_query = new WP_Query(
            array(
                'post_type'      => 'interslide_video',
                'posts_per_page' => 6,
                'post__not_in'   => array( $video_id ),
                'tax_query'      => $categories ? array(
                    array(
                        'taxonomy' => 'interslide_video_category',
                        'field'    => 'term_id',
                        'terms'    => wp_list_pluck( $categories, 'term_id' ),
                    ),
                ) : array(),
            )
        );
    }
    ?>
    <main class="isv isv-single">
        <div class="isv-container">
            <div class="isv-meta">
                <span class="isv-meta__label"><?php echo esc_html__( 'Accueil', 'interslide-video-formats' ); ?></span>
                <span class="isv-meta__sep">/</span>
                <a href="<?php echo esc_url( get_post_type_archive_link( 'interslide_video' ) ); ?>" class="isv-meta__link">Vidéos</a>
                <?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
                    <span class="isv-meta__sep">/</span>
                    <a class="isv-meta__link" href="<?php echo esc_url( get_term_link( $categories[0] ) ); ?>">
                        <?php echo esc_html( $categories[0]->name ); ?>
                    </a>
                <?php endif; ?>
                <?php if ( ! empty( $topics ) && ! is_wp_error( $topics ) ) : ?>
                    <span class="isv-meta__sep">/</span>
                    <a class="isv-meta__link" href="<?php echo esc_url( get_term_link( $topics[0] ) ); ?>">
                        <?php echo esc_html( $topics[0]->name ); ?>
                    </a>
                <?php endif; ?>
            </div>

            <header class="isv-header">
                <h1 class="isv-title"><?php the_title(); ?></h1>
                <?php if ( $excerpt ) : ?>
                    <p class="isv-excerpt"><?php echo esc_html( $excerpt ); ?></p>
                <?php endif; ?>
                <div class="isv-submeta">
                    <span><?php echo esc_html( get_the_author() ); ?></span>
                    <span class="isv-submeta__sep">•</span>
                    <span><?php echo esc_html( get_the_date() ); ?></span>
                    <?php if ( $duration ) : ?>
                        <span class="isv-submeta__sep">•</span>
                        <span><?php echo esc_html( $duration ); ?></span>
                    <?php endif; ?>
                    <?php if ( $series ) : ?>
                        <span class="isv-submeta__sep">•</span>
                        <span><?php echo esc_html( $series ); ?><?php echo $episode ? ' — ' . esc_html( $episode ) : ''; ?></span>
                    <?php endif; ?>
                </div>
            </header>

            <div class="isv-player">
                <?php echo ISV_Plugin::get_video_embed( $video_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>

            <div class="isv-content">
                <?php the_content(); ?>
            </div>

            <div class="isv-actions">
                <button class="isv-copy" type="button" data-url="<?php echo esc_url( get_permalink() ); ?>"><?php esc_html_e( 'Copier le lien', 'interslide-video-formats' ); ?></button>
                <a href="https://twitter.com/intent/tweet?url=<?php echo esc_url( rawurlencode( get_permalink() ) ); ?>" target="_blank" rel="noopener" class="isv-share">X</a>
                <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo esc_url( rawurlencode( get_permalink() ) ); ?>" target="_blank" rel="noopener" class="isv-share">LinkedIn</a>
                <a href="https://wa.me/?text=<?php echo esc_url( rawurlencode( get_permalink() ) ); ?>" target="_blank" rel="noopener" class="isv-share">WhatsApp</a>
            </div>

            <?php if ( $next_post ) : ?>
                <section class="isv-section">
                    <h2 class="isv-section__title">À suivre</h2>
                    <?php echo ISV_Plugin::render_card( $next_post->ID ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </section>
            <?php endif; ?>

            <section class="isv-section">
                <h2 class="isv-section__title">Sur le même sujet</h2>
                <div class="isv-grid">
                    <?php
                    if ( $related_query && $related_query->have_posts() ) {
                        while ( $related_query->have_posts() ) {
                            $related_query->the_post();
                            echo ISV_Plugin::render_card( get_the_ID() );
                        }
                        wp_reset_postdata();
                    }
                    ?>
                </div>
            </section>

            <?php if ( $topics && ! is_wp_error( $topics ) ) : ?>
                <section class="isv-section">
                    <h2 class="isv-section__title">Pour aller plus loin</h2>
                    <div class="isv-tags">
                        <?php foreach ( $topics as $topic ) : ?>
                            <a href="<?php echo esc_url( get_term_link( $topic ) ); ?>" class="isv-tag"><?php echo esc_html( $topic->name ); ?></a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if ( $credits ) : ?>
                <section class="isv-section">
                    <h2 class="isv-section__title">Crédits</h2>
                    <p><?php echo esc_html( $credits ); ?></p>
                </section>
            <?php endif; ?>

            <?php if ( $sources ) : ?>
                <section class="isv-section">
                    <h2 class="isv-section__title">Sources</h2>
                    <ul class="isv-sources">
                        <?php foreach ( preg_split( '/\r\n|\r|\n/', $sources ) as $line ) : ?>
                            <?php
                            $parts = array_map( 'trim', explode( '|', $line ) );
                            $label = $parts[0] ?? '';
                            $url   = $parts[1] ?? '';
                            if ( ! $label ) {
                                continue;
                            }
                            ?>
                            <li>
                                <?php if ( $url ) : ?>
                                    <a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $label ); ?></a>
                                <?php else : ?>
                                    <?php echo esc_html( $label ); ?>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>

            <?php if ( $method ) : ?>
                <section class="isv-section">
                    <h2 class="isv-section__title">Outils / Méthodo</h2>
                    <p><?php echo esc_html( $method ); ?></p>
                </section>
            <?php endif; ?>
        </div>
    </main>
    <?php
}

get_footer();
