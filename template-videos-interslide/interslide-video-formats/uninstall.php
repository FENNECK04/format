<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

$delete_data = get_option( 'isv_delete_data_on_uninstall' );
if ( ! $delete_data ) {
    return;
}

$posts = get_posts(
    array(
        'post_type'      => 'interslide_video',
        'posts_per_page' => -1,
        'post_status'    => 'any',
        'fields'         => 'ids',
    )
);

foreach ( $posts as $post_id ) {
    wp_delete_post( $post_id, true );
}

$taxonomies = array( 'interslide_video_category', 'interslide_video_topic' );
foreach ( $taxonomies as $taxonomy ) {
    $terms = get_terms(
        array(
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'fields'     => 'ids',
        )
    );
    if ( ! is_wp_error( $terms ) ) {
        foreach ( $terms as $term_id ) {
            wp_delete_term( $term_id, $taxonomy );
        }
    }
}

delete_option( 'isv_delete_data_on_uninstall' );
