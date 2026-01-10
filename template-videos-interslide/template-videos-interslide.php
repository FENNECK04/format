<?php
/**
 * Plugin Name: Template Videos Interslide
 * Description: Unified templates for brut_video posts and Interslide video content.
 * Version: 1.0.0
 * Author: OpenAI
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/brut-video-format.php';

/**
 * Create an Elementor Theme Builder template for brut_video.
 */
function tvii_create_elementor_template() {
    if ( ! post_type_exists( 'elementor_library' ) ) {
        return false;
    }

    $existing = get_posts(
        array(
            'post_type'      => 'elementor_library',
            'posts_per_page' => 1,
            'meta_key'       => '_tvii_template',
            'meta_value'     => '1',
            'fields'         => 'ids',
        )
    );

    if ( $existing ) {
        return (int) $existing[0];
    }

    $template_id = wp_insert_post(
        array(
            'post_type'   => 'elementor_library',
            'post_status' => 'publish',
            'post_title'  => __( 'Brut Video - Template Interslide', 'template-videos-interslide' ),
        )
    );

    if ( is_wp_error( $template_id ) ) {
        return false;
    }

    update_post_meta( $template_id, '_elementor_edit_mode', 'builder' );
    update_post_meta( $template_id, '_elementor_template_type', 'single' );
    update_post_meta( $template_id, '_elementor_version', '3.0.0' );
    update_post_meta( $template_id, '_tvii_template', '1' );

    if ( class_exists( '\\ElementorPro\\Modules\\ThemeBuilder\\Module' ) ) {
        $conditions = array(
            'include' => array(
                array(
                    'type' => 'singular',
                    'name' => 'brut_video',
                ),
            ),
        );
        \ElementorPro\Modules\ThemeBuilder\Module::instance()
            ->get_conditions_manager()
            ->save_conditions( $template_id, $conditions );
    }

    return $template_id;
}

/**
 * Ensure Elementor template exists after activation when Elementor is loaded.
 */
function tvii_maybe_create_elementor_template() {
    if ( get_option( 'tvii_pending_elementor_template' ) ) {
        if ( tvii_create_elementor_template() ) {
            delete_option( 'tvii_pending_elementor_template' );
        }
    }
}
add_action( 'admin_init', 'tvii_maybe_create_elementor_template' );

/**
 * Activation hook.
 */
function tvii_activate() {
    if ( ! tvii_create_elementor_template() ) {
        update_option( 'tvii_pending_elementor_template', 1 );
    }
}
register_activation_hook( __FILE__, 'tvii_activate' );
