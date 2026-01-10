<?php
/**
 * Brut video layout utilities.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const BRUT_VIDEO_FORMAT_VERSION = '1.0.0';
const BRUT_VIDEO_FORMAT_PATH    = __DIR__;

/**
 * Enqueue styles for brut_video single pages.
 */
function brut_video_format_enqueue_assets() {
    if ( ! is_singular( 'brut_video' ) ) {
        return;
    }

    $base_url = plugin_dir_url( __FILE__ );
    wp_enqueue_style( 'brut-video-format', $base_url . 'assets/css/brut-video-format.css', array(), BRUT_VIDEO_FORMAT_VERSION );
    wp_enqueue_script( 'brut-video-format', $base_url . 'assets/js/brut-video-format.js', array(), BRUT_VIDEO_FORMAT_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'brut_video_format_enqueue_assets' );

/**
 * Ensure brut_video supports template selection in the editor.
 */
function brut_video_format_add_post_type_support() {
    add_post_type_support( 'brut_video', 'page-attributes' );
}
add_action( 'init', 'brut_video_format_add_post_type_support' );

/**
 * Use plugin template for brut_video posts.
 */
function brut_video_format_template_include( $template ) {
    if ( ! is_singular( 'brut_video' ) ) {
        return $template;
    }

    return BRUT_VIDEO_FORMAT_PATH . '/templates/single-brut_video.php';
}
add_filter( 'template_include', 'brut_video_format_template_include' );

/**
 * Add body classes for brut_video layouts.
 */
function brut_video_format_body_classes( $classes ) {
    if ( ! is_singular( 'brut_video' ) ) {
        return $classes;
    }

    $classes[] = 'brut-video-format-page';

    return $classes;
}
add_filter( 'body_class', 'brut_video_format_body_classes' );

/**
 * Add meta box for YouTube URL and category display selection.
 */
function brut_video_format_add_meta_box() {
    add_meta_box(
        'brut-video-format-meta',
        __( 'Brut Video Format', 'brut-video-format' ),
        'brut_video_format_render_meta_box',
        'brut_video',
        'side',
        'default'
    );
}
add_action( 'add_meta_boxes', 'brut_video_format_add_meta_box' );

/**
 * Render meta box fields.
 */
function brut_video_format_render_meta_box( $post ) {
    wp_nonce_field( 'brut_video_format_save_meta', 'brut_video_format_nonce' );

    $youtube_url   = get_post_meta( $post->ID, '_brut_video_youtube_url', true );
    $display_terms = (array) get_post_meta( $post->ID, '_brut_video_display_categories', true );
    $categories    = get_the_terms( $post, 'category' );

    echo '<p><label for="brut-video-youtube-url"><strong>' . esc_html__( 'YouTube URL', 'brut-video-format' ) . '</strong></label></p>';
    echo '<input type="url" id="brut-video-youtube-url" name="brut_video_youtube_url" style="width:100%" value="' . esc_attr( $youtube_url ) . '" placeholder="https://www.youtube.com/watch?v=..." />';

    echo '<p><strong>' . esc_html__( 'Categories to display (max 2)', 'brut-video-format' ) . '</strong></p>';

    if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
        foreach ( $categories as $category ) {
            $checked = in_array( (string) $category->term_id, $display_terms, true ) ? 'checked' : '';
            echo '<label style="display:block;margin-bottom:4px;">';
            echo '<input type="checkbox" name="brut_video_display_categories[]" value="' . esc_attr( $category->term_id ) . '" ' . $checked . ' /> ';
            echo esc_html( $category->name );
            echo '</label>';
        }
    } else {
        echo '<em>' . esc_html__( 'Assign categories to this post to select which ones to display.', 'brut-video-format' ) . '</em>';
    }
}

/**
 * Save meta box fields.
 */
function brut_video_format_save_meta( $post_id ) {
    if ( ! isset( $_POST['brut_video_format_nonce'] ) || ! wp_verify_nonce( $_POST['brut_video_format_nonce'], 'brut_video_format_save_meta' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( isset( $_POST['brut_video_youtube_url'] ) ) {
        $youtube_url = esc_url_raw( wp_unslash( $_POST['brut_video_youtube_url'] ) );
        update_post_meta( $post_id, '_brut_video_youtube_url', $youtube_url );
    }

    $display_terms = array();
    if ( isset( $_POST['brut_video_display_categories'] ) && is_array( $_POST['brut_video_display_categories'] ) ) {
        $display_terms = array_slice(
            array_map( 'sanitize_text_field', wp_unslash( $_POST['brut_video_display_categories'] ) ),
            0,
            2
        );
    }

    update_post_meta( $post_id, '_brut_video_display_categories', $display_terms );
}
add_action( 'save_post_brut_video', 'brut_video_format_save_meta' );

/**
 * Helper: fetch display categories for a post.
 */
function brut_video_format_get_display_categories( $post_id ) {
    $limit         = (int) apply_filters( 'brut_video_display_category_limit', 2 );
    $selected_ids  = (array) get_post_meta( $post_id, '_brut_video_display_categories', true );
    $selected_ids  = array_filter( array_map( 'absint', $selected_ids ) );
    $categories    = get_the_terms( $post_id, 'category' );

    if ( empty( $categories ) || is_wp_error( $categories ) ) {
        return array();
    }

    if ( empty( $selected_ids ) ) {
        return array_slice( $categories, 0, $limit );
    }

    $selected = array();
    foreach ( $categories as $category ) {
        if ( in_array( (int) $category->term_id, $selected_ids, true ) ) {
            $selected[] = $category;
        }
    }

    return array_slice( $selected, 0, $limit );
}
