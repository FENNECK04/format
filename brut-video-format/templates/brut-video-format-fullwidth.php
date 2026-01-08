<?php
/**
 * Template Name: Brut Video - Plein écran
 * Template Post Type: brut_video
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

while ( have_posts() ) {
    the_post();

    $youtube_url = get_post_meta( get_the_ID(), '_brut_video_youtube_url', true );
    $embed       = $youtube_url ? wp_oembed_get( $youtube_url ) : '';
    $categories  = brut_video_format_get_display_categories( get_the_ID() );
    $excerpt     = has_excerpt() ? get_the_excerpt() : '';
    $date        = get_the_date();
    ?>
    <main class="brut-video-format brut-video-format--fullwidth">
        <div class="brut-video-format__container">
            <div class="brut-video-format__media">
                <?php if ( $embed ) : ?>
                    <div class="brut-video-format__embed">
                        <?php echo $embed; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    </div>
                <?php elseif ( has_post_thumbnail() ) : ?>
                    <div class="brut-video-format__embed">
                        <?php the_post_thumbnail( 'large' ); ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="brut-video-format__content">
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

                <?php if ( $date ) : ?>
                    <div class="brut-video-format__date">
                        <?php echo esc_html( sprintf( __( 'Publié le %s', 'brut-video-format' ), $date ) ); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <?php
}

get_footer();
