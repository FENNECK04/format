<?php
/**
 * Plugin Name: Interslide Video Formats
 * Description: Custom video content type and templates for Interslide-style video pages.
 * Version: 1.0.0
 * Author: OpenAI
 * Text Domain: interslide-video-formats
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class ISV_Plugin {
    const VERSION = '1.0.0';
    const OPTION_DELETE_DATA = 'isv_delete_data_on_uninstall';

    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_post_type' ) );
        add_action( 'init', array( __CLASS__, 'register_taxonomies' ) );
        add_action( 'init', array( __CLASS__, 'register_meta' ) );
        add_action( 'add_meta_boxes', array( __CLASS__, 'register_meta_boxes' ) );
        add_action( 'save_post_interslide_video', array( __CLASS__, 'save_meta' ) );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
        add_filter( 'template_include', array( __CLASS__, 'template_include' ) );
        add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
        add_action( 'wp_head', array( __CLASS__, 'output_meta_tags' ) );
        add_action( 'wp_head', array( __CLASS__, 'output_schema' ) );
        add_filter( 'manage_interslide_video_posts_columns', array( __CLASS__, 'admin_columns' ) );
        add_action( 'manage_interslide_video_posts_custom_column', array( __CLASS__, 'admin_column_content' ), 10, 2 );
        add_shortcode( 'interslide_video_grid', array( __CLASS__, 'shortcode_grid' ) );
        add_action( 'admin_menu', array( __CLASS__, 'register_settings_page' ) );
        add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
    }

    public static function register_post_type() {
        $labels = array(
            'name'               => __( 'Interslide Vidéos', 'interslide-video-formats' ),
            'singular_name'      => __( 'Interslide Vidéo', 'interslide-video-formats' ),
            'add_new'            => __( 'Ajouter', 'interslide-video-formats' ),
            'add_new_item'       => __( 'Ajouter une vidéo', 'interslide-video-formats' ),
            'edit_item'          => __( 'Modifier la vidéo', 'interslide-video-formats' ),
            'new_item'           => __( 'Nouvelle vidéo', 'interslide-video-formats' ),
            'view_item'          => __( 'Voir la vidéo', 'interslide-video-formats' ),
            'search_items'       => __( 'Rechercher des vidéos', 'interslide-video-formats' ),
            'not_found'          => __( 'Aucune vidéo trouvée', 'interslide-video-formats' ),
            'not_found_in_trash' => __( 'Aucune vidéo dans la corbeille', 'interslide-video-formats' ),
            'menu_name'          => __( 'Interslide Vidéos', 'interslide-video-formats' ),
        );

        register_post_type(
            'interslide_video',
            array(
                'labels'             => $labels,
                'public'             => true,
                'has_archive'        => true,
                'rewrite'            => array( 'slug' => 'videos' ),
                'menu_icon'          => 'dashicons-video-alt3',
                'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'comments', 'revisions' ),
                'show_in_rest'       => true,
                'show_in_nav_menus'  => true,
                'menu_position'      => 20,
            )
        );
    }

    public static function register_taxonomies() {
        register_taxonomy(
            'interslide_video_category',
            'interslide_video',
            array(
                'label'        => __( 'Catégories vidéo', 'interslide-video-formats' ),
                'hierarchical' => true,
                'show_in_rest' => true,
                'rewrite'      => array( 'slug' => 'video-categorie' ),
            )
        );

        register_taxonomy(
            'interslide_video_topic',
            'interslide_video',
            array(
                'label'        => __( 'Topics vidéo', 'interslide-video-formats' ),
                'hierarchical' => false,
                'show_in_rest' => true,
                'rewrite'      => array( 'slug' => 'video-topic' ),
            )
        );
    }

    public static function register_meta() {
        $meta_keys = array(
            'isv_video_source_type' => 'string',
            'isv_video_mp4'          => 'string',
            'isv_video_youtube'      => 'string',
            'isv_video_vimeo'        => 'string',
            'isv_video_embed'        => 'string',
            'isv_video_duration'     => 'string',
            'isv_video_series'       => 'string',
            'isv_video_episode'      => 'string',
            'isv_video_next'         => 'integer',
            'isv_video_related'      => 'array',
            'isv_video_sources'      => 'string',
            'isv_video_credits'      => 'string',
            'isv_video_methodology'  => 'string',
        );

        foreach ( $meta_keys as $key => $type ) {
            register_post_meta(
                'interslide_video',
                $key,
                array(
                    'show_in_rest' => true,
                    'single'       => true,
                    'type'         => $type,
                    'auth_callback' => function() {
                        return current_user_can( 'edit_posts' );
                    },
                )
            );
        }
    }

    public static function register_meta_boxes() {
        add_meta_box(
            'isv_video_details',
            __( 'Détails vidéo', 'interslide-video-formats' ),
            array( __CLASS__, 'render_meta_box' ),
            'interslide_video',
            'normal',
            'high'
        );
    }

    public static function render_meta_box( $post ) {
        wp_nonce_field( 'isv_video_save_meta', 'isv_video_nonce' );

        $source_type = get_post_meta( $post->ID, 'isv_video_source_type', true );
        $mp4         = get_post_meta( $post->ID, 'isv_video_mp4', true );
        $youtube     = get_post_meta( $post->ID, 'isv_video_youtube', true );
        $vimeo       = get_post_meta( $post->ID, 'isv_video_vimeo', true );
        $embed       = get_post_meta( $post->ID, 'isv_video_embed', true );
        $duration    = get_post_meta( $post->ID, 'isv_video_duration', true );
        $series      = get_post_meta( $post->ID, 'isv_video_series', true );
        $episode     = get_post_meta( $post->ID, 'isv_video_episode', true );
        $next_video  = get_post_meta( $post->ID, 'isv_video_next', true );
        $related     = (array) get_post_meta( $post->ID, 'isv_video_related', true );
        $sources     = get_post_meta( $post->ID, 'isv_video_sources', true );
        $credits     = get_post_meta( $post->ID, 'isv_video_credits', true );
        $methodology = get_post_meta( $post->ID, 'isv_video_methodology', true );

        $videos = get_posts(
            array(
                'post_type'      => 'interslide_video',
                'posts_per_page' => 50,
                'post_status'    => array( 'publish', 'draft' ),
                'orderby'        => 'date',
                'order'          => 'DESC',
                'exclude'        => array( $post->ID ),
            )
        );
        ?>
        <div class="isv-admin">
            <p><strong><?php esc_html_e( 'Source vidéo', 'interslide-video-formats' ); ?></strong></p>
            <p>
                <label><input type="radio" name="isv_video_source_type" value="mp4" <?php checked( $source_type, 'mp4' ); ?>> <?php esc_html_e( 'MP4', 'interslide-video-formats' ); ?></label>
                <label style="margin-left:12px;"><input type="radio" name="isv_video_source_type" value="youtube" <?php checked( $source_type, 'youtube' ); ?>> YouTube</label>
                <label style="margin-left:12px;"><input type="radio" name="isv_video_source_type" value="vimeo" <?php checked( $source_type, 'vimeo' ); ?>> Vimeo</label>
                <label style="margin-left:12px;"><input type="radio" name="isv_video_source_type" value="embed" <?php checked( $source_type, 'embed' ); ?>> <?php esc_html_e( 'Embed', 'interslide-video-formats' ); ?></label>
            </p>
            <p>
                <label><?php esc_html_e( 'MP4 (URL média)', 'interslide-video-formats' ); ?></label>
                <input type="url" name="isv_video_mp4" class="widefat" value="<?php echo esc_attr( $mp4 ); ?>">
            </p>
            <p>
                <label><?php esc_html_e( 'URL YouTube', 'interslide-video-formats' ); ?></label>
                <input type="url" name="isv_video_youtube" class="widefat" value="<?php echo esc_attr( $youtube ); ?>">
            </p>
            <p>
                <label><?php esc_html_e( 'URL Vimeo', 'interslide-video-formats' ); ?></label>
                <input type="url" name="isv_video_vimeo" class="widefat" value="<?php echo esc_attr( $vimeo ); ?>">
            </p>
            <p>
                <label><?php esc_html_e( 'Embed HTML (iframe autorisé)', 'interslide-video-formats' ); ?></label>
                <textarea name="isv_video_embed" class="widefat" rows="3"><?php echo esc_textarea( $embed ); ?></textarea>
            </p>
            <hr>
            <p>
                <label><?php esc_html_e( 'Durée (mm:ss)', 'interslide-video-formats' ); ?></label>
                <input type="text" name="isv_video_duration" class="widefat" value="<?php echo esc_attr( $duration ); ?>">
            </p>
            <p>
                <label><?php esc_html_e( 'Série', 'interslide-video-formats' ); ?></label>
                <input type="text" name="isv_video_series" class="widefat" value="<?php echo esc_attr( $series ); ?>">
            </p>
            <p>
                <label><?php esc_html_e( 'Épisode', 'interslide-video-formats' ); ?></label>
                <input type="text" name="isv_video_episode" class="widefat" value="<?php echo esc_attr( $episode ); ?>">
            </p>
            <p>
                <label><?php esc_html_e( 'Vidéo à suivre', 'interslide-video-formats' ); ?></label>
                <select name="isv_video_next" class="widefat">
                    <option value=""><?php esc_html_e( 'Sélectionner', 'interslide-video-formats' ); ?></option>
                    <?php foreach ( $videos as $video ) : ?>
                        <option value="<?php echo esc_attr( $video->ID ); ?>" <?php selected( (int) $next_video, (int) $video->ID ); ?>>
                            <?php echo esc_html( $video->post_title ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p>
                <label><?php esc_html_e( 'Sur le même sujet (IDs séparés par des virgules)', 'interslide-video-formats' ); ?></label>
                <input type="text" name="isv_video_related" class="widefat" value="<?php echo esc_attr( implode( ',', array_map( 'absint', $related ) ) ); ?>">
            </p>
            <p>
                <label><?php esc_html_e( 'Sources (format: Label | URL, une ligne par source)', 'interslide-video-formats' ); ?></label>
                <textarea name="isv_video_sources" class="widefat" rows="3"><?php echo esc_textarea( $sources ); ?></textarea>
            </p>
            <p>
                <label><?php esc_html_e( 'Crédits', 'interslide-video-formats' ); ?></label>
                <input type="text" name="isv_video_credits" class="widefat" value="<?php echo esc_attr( $credits ); ?>">
            </p>
            <p>
                <label><?php esc_html_e( 'Outils / méthodo', 'interslide-video-formats' ); ?></label>
                <textarea name="isv_video_methodology" class="widefat" rows="3"><?php echo esc_textarea( $methodology ); ?></textarea>
            </p>
        </div>
        <?php
    }

    public static function save_meta( $post_id ) {
        if ( ! isset( $_POST['isv_video_nonce'] ) || ! wp_verify_nonce( $_POST['isv_video_nonce'], 'isv_video_save_meta' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $source_type = isset( $_POST['isv_video_source_type'] ) ? sanitize_text_field( wp_unslash( $_POST['isv_video_source_type'] ) ) : '';
        $mp4         = isset( $_POST['isv_video_mp4'] ) ? esc_url_raw( wp_unslash( $_POST['isv_video_mp4'] ) ) : '';
        $youtube     = isset( $_POST['isv_video_youtube'] ) ? esc_url_raw( wp_unslash( $_POST['isv_video_youtube'] ) ) : '';
        $vimeo       = isset( $_POST['isv_video_vimeo'] ) ? esc_url_raw( wp_unslash( $_POST['isv_video_vimeo'] ) ) : '';
        $embed       = isset( $_POST['isv_video_embed'] ) ? wp_kses( wp_unslash( $_POST['isv_video_embed'] ), self::allowed_embed_tags() ) : '';
        $duration    = isset( $_POST['isv_video_duration'] ) ? sanitize_text_field( wp_unslash( $_POST['isv_video_duration'] ) ) : '';
        $series      = isset( $_POST['isv_video_series'] ) ? sanitize_text_field( wp_unslash( $_POST['isv_video_series'] ) ) : '';
        $episode     = isset( $_POST['isv_video_episode'] ) ? sanitize_text_field( wp_unslash( $_POST['isv_video_episode'] ) ) : '';
        $next_video  = isset( $_POST['isv_video_next'] ) ? absint( $_POST['isv_video_next'] ) : 0;
        $related_raw = isset( $_POST['isv_video_related'] ) ? sanitize_text_field( wp_unslash( $_POST['isv_video_related'] ) ) : '';
        $sources     = isset( $_POST['isv_video_sources'] ) ? sanitize_textarea_field( wp_unslash( $_POST['isv_video_sources'] ) ) : '';
        $credits     = isset( $_POST['isv_video_credits'] ) ? sanitize_text_field( wp_unslash( $_POST['isv_video_credits'] ) ) : '';
        $methodology = isset( $_POST['isv_video_methodology'] ) ? sanitize_textarea_field( wp_unslash( $_POST['isv_video_methodology'] ) ) : '';

        $related = array();
        if ( $related_raw ) {
            $parts = array_filter( array_map( 'absint', explode( ',', $related_raw ) ) );
            $related = array_slice( $parts, 0, 8 );
        }

        update_post_meta( $post_id, 'isv_video_source_type', $source_type );
        update_post_meta( $post_id, 'isv_video_mp4', $mp4 );
        update_post_meta( $post_id, 'isv_video_youtube', $youtube );
        update_post_meta( $post_id, 'isv_video_vimeo', $vimeo );
        update_post_meta( $post_id, 'isv_video_embed', $embed );
        update_post_meta( $post_id, 'isv_video_duration', $duration );
        update_post_meta( $post_id, 'isv_video_series', $series );
        update_post_meta( $post_id, 'isv_video_episode', $episode );
        update_post_meta( $post_id, 'isv_video_next', $next_video );
        update_post_meta( $post_id, 'isv_video_related', $related );
        update_post_meta( $post_id, 'isv_video_sources', $sources );
        update_post_meta( $post_id, 'isv_video_credits', $credits );
        update_post_meta( $post_id, 'isv_video_methodology', $methodology );
    }

    public static function enqueue_assets() {
        if ( self::is_video_context() ) {
            wp_enqueue_style( 'isv-front', plugin_dir_url( __FILE__ ) . 'assets/css/isv.css', array(), self::VERSION );
            wp_enqueue_script( 'isv-front', plugin_dir_url( __FILE__ ) . 'assets/js/isv.js', array(), self::VERSION, true );
        }
    }

    public static function template_include( $template ) {
        if ( is_singular( 'interslide_video' ) ) {
            $theme_template = locate_template( array( 'single-interslide_video.php' ) );
            return $theme_template ? $theme_template : plugin_dir_path( __FILE__ ) . 'templates/single-interslide_video.php';
        }

        if ( is_post_type_archive( 'interslide_video' ) ) {
            $theme_template = locate_template( array( 'archive-interslide_video.php' ) );
            return $theme_template ? $theme_template : plugin_dir_path( __FILE__ ) . 'templates/archive-interslide_video.php';
        }

        return $template;
    }

    public static function body_class( $classes ) {
        if ( self::is_video_context() ) {
            $classes[] = 'isv-page';
        }

        return $classes;
    }

    public static function output_meta_tags() {
        if ( ! is_singular( 'interslide_video' ) ) {
            return;
        }

        global $post;
        $title       = get_the_title( $post );
        $description = $post->post_excerpt ? $post->post_excerpt : wp_trim_words( wp_strip_all_tags( $post->post_content ), 30 );
        $image       = get_the_post_thumbnail_url( $post, 'large' );

        echo '<meta property="og:type" content="video.other">';
        echo '<meta property="og:title" content="' . esc_attr( $title ) . '">';
        echo '<meta property="og:description" content="' . esc_attr( $description ) . '">';
        if ( $image ) {
            echo '<meta property="og:image" content="' . esc_url( $image ) . '">';
        }
        echo '<meta name="twitter:card" content="summary_large_image">';
        echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">';
        echo '<meta name="twitter:description" content="' . esc_attr( $description ) . '">';
        if ( $image ) {
            echo '<meta name="twitter:image" content="' . esc_url( $image ) . '">';
        }
    }

    public static function output_schema() {
        if ( ! is_singular( 'interslide_video' ) ) {
            return;
        }

        $post        = get_post();
        $title       = get_the_title( $post );
        $description = $post->post_excerpt ? $post->post_excerpt : wp_trim_words( wp_strip_all_tags( $post->post_content ), 30 );
        $thumbnail   = get_the_post_thumbnail_url( $post, 'large' );
        $duration    = get_post_meta( $post->ID, 'isv_video_duration', true );
        $upload_date = get_the_date( 'c', $post );

        $schema = array(
            '@context'     => 'https://schema.org',
            '@type'        => 'VideoObject',
            'name'         => $title,
            'description'  => $description,
            'thumbnailUrl' => $thumbnail ? array( $thumbnail ) : array(),
            'uploadDate'   => $upload_date,
        );

        $embed_url = self::get_video_embed_url( $post->ID );
        if ( $embed_url ) {
            $schema['embedUrl'] = $embed_url;
        }

        if ( $duration ) {
            $schema['duration'] = self::format_duration_iso8601( $duration );
        }

        echo '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>';
    }

    public static function admin_columns( $columns ) {
        $columns['isv_duration'] = __( 'Durée', 'interslide-video-formats' );
        $columns['isv_source']   = __( 'Source', 'interslide-video-formats' );
        $columns['isv_category'] = __( 'Catégorie', 'interslide-video-formats' );
        $columns['isv_topic']    = __( 'Topics', 'interslide-video-formats' );
        $columns['isv_series']   = __( 'Série', 'interslide-video-formats' );

        return $columns;
    }

    public static function admin_column_content( $column, $post_id ) {
        if ( 'isv_duration' === $column ) {
            echo esc_html( get_post_meta( $post_id, 'isv_video_duration', true ) );
        }

        if ( 'isv_source' === $column ) {
            echo esc_html( get_post_meta( $post_id, 'isv_video_source_type', true ) );
        }

        if ( 'isv_category' === $column ) {
            $terms = get_the_terms( $post_id, 'interslide_video_category' );
            if ( $terms && ! is_wp_error( $terms ) ) {
                echo esc_html( wp_list_pluck( $terms, 'name' ) ? implode( ', ', wp_list_pluck( $terms, 'name' ) ) : '' );
            }
        }

        if ( 'isv_topic' === $column ) {
            $terms = get_the_terms( $post_id, 'interslide_video_topic' );
            if ( $terms && ! is_wp_error( $terms ) ) {
                echo esc_html( wp_list_pluck( $terms, 'name' ) ? implode( ', ', wp_list_pluck( $terms, 'name' ) ) : '' );
            }
        }

        if ( 'isv_series' === $column ) {
            echo esc_html( get_post_meta( $post_id, 'isv_video_series', true ) );
        }
    }

    public static function shortcode_grid( $atts ) {
        $atts = shortcode_atts(
            array(
                'category' => '',
                'topic'    => '',
                'limit'    => 6,
                'order'    => 'desc',
                'featured' => 'false',
            ),
            $atts,
            'interslide_video_grid'
        );

        $tax_query = array();
        if ( $atts['category'] ) {
            $tax_query[] = array(
                'taxonomy' => 'interslide_video_category',
                'field'    => 'slug',
                'terms'    => array_map( 'sanitize_title', explode( ',', $atts['category'] ) ),
            );
        }
        if ( $atts['topic'] ) {
            $tax_query[] = array(
                'taxonomy' => 'interslide_video_topic',
                'field'    => 'slug',
                'terms'    => array_map( 'sanitize_title', explode( ',', $atts['topic'] ) ),
            );
        }

        $query = new WP_Query(
            array(
                'post_type'      => 'interslide_video',
                'posts_per_page' => absint( $atts['limit'] ),
                'order'          => strtoupper( $atts['order'] ) === 'ASC' ? 'ASC' : 'DESC',
                'tax_query'      => $tax_query,
            )
        );

        ob_start();
        echo '<div class="isv isv-grid">';
        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                echo self::render_card( get_the_ID() );
            }
        }
        echo '</div>';
        wp_reset_postdata();

        return ob_get_clean();
    }

    public static function register_settings_page() {
        add_options_page(
            __( 'Interslide Video Formats', 'interslide-video-formats' ),
            __( 'Interslide Video Formats', 'interslide-video-formats' ),
            'manage_options',
            'isv-settings',
            array( __CLASS__, 'render_settings_page' )
        );
    }

    public static function register_settings() {
        register_setting( 'isv_settings', self::OPTION_DELETE_DATA );

        add_settings_section(
            'isv_cleanup',
            __( 'Nettoyage', 'interslide-video-formats' ),
            '__return_false',
            'isv-settings'
        );

        add_settings_field(
            'isv_delete_data',
            __( 'Supprimer les données à la désinstallation', 'interslide-video-formats' ),
            array( __CLASS__, 'render_delete_field' ),
            'isv-settings',
            'isv_cleanup'
        );
    }

    public static function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Interslide Video Formats', 'interslide-video-formats' ); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'isv_settings' ); ?>
                <?php do_settings_sections( 'isv-settings' ); ?>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public static function render_delete_field() {
        $value = (bool) get_option( self::OPTION_DELETE_DATA );
        ?>
        <label>
            <input type="checkbox" name="<?php echo esc_attr( self::OPTION_DELETE_DATA ); ?>" value="1" <?php checked( $value ); ?>>
            <?php esc_html_e( 'Supprimer les vidéos, taxonomies et métadonnées à la désinstallation.', 'interslide-video-formats' ); ?>
        </label>
        <?php
    }

    public static function get_video_embed_url( $post_id ) {
        $source_type = get_post_meta( $post_id, 'isv_video_source_type', true );
        $youtube     = get_post_meta( $post_id, 'isv_video_youtube', true );
        $vimeo       = get_post_meta( $post_id, 'isv_video_vimeo', true );
        $mp4         = get_post_meta( $post_id, 'isv_video_mp4', true );

        if ( 'youtube' === $source_type && $youtube ) {
            return $youtube;
        }
        if ( 'vimeo' === $source_type && $vimeo ) {
            return $vimeo;
        }
        if ( 'mp4' === $source_type && $mp4 ) {
            return $mp4;
        }

        return '';
    }

    public static function get_video_embed( $post_id ) {
        $source_type = get_post_meta( $post_id, 'isv_video_source_type', true );
        $mp4         = get_post_meta( $post_id, 'isv_video_mp4', true );
        $youtube     = get_post_meta( $post_id, 'isv_video_youtube', true );
        $vimeo       = get_post_meta( $post_id, 'isv_video_vimeo', true );
        $embed       = get_post_meta( $post_id, 'isv_video_embed', true );

        if ( 'mp4' === $source_type && $mp4 ) {
            return sprintf(
                '<video class="isv-video" controls preload="metadata" src="%s"></video>',
                esc_url( $mp4 )
            );
        }

        if ( 'youtube' === $source_type && $youtube ) {
            return self::render_lazy_embed( 'youtube', $youtube );
        }

        if ( 'vimeo' === $source_type && $vimeo ) {
            return self::render_lazy_embed( 'vimeo', $vimeo );
        }

        if ( 'embed' === $source_type && $embed ) {
            return sprintf( '<div class="isv-embed">%s</div>', $embed );
        }

        return '';
    }

    public static function render_lazy_embed( $provider, $url ) {
        $thumbnail = '';
        if ( 'youtube' === $provider ) {
            $video_id = self::extract_youtube_id( $url );
            if ( $video_id ) {
                $thumbnail = sprintf( 'https://img.youtube.com/vi/%s/maxresdefault.jpg', $video_id );
            }
        }

        $poster = $thumbnail ? sprintf( '<img src="%s" alt="" loading="lazy">', esc_url( $thumbnail ) ) : '';

        return sprintf(
            '<div class="isv-embed isv-embed--lazy" data-provider="%s" data-url="%s">%s<button class="isv-embed__play" type="button">▶</button></div>',
            esc_attr( $provider ),
            esc_url( $url ),
            $poster
        );
    }

    public static function extract_youtube_id( $url ) {
        $parts = wp_parse_url( $url );
        if ( empty( $parts['host'] ) ) {
            return '';
        }

        if ( false !== strpos( $parts['host'], 'youtu.be' ) && ! empty( $parts['path'] ) ) {
            return ltrim( $parts['path'], '/' );
        }

        if ( ! empty( $parts['query'] ) ) {
            parse_str( $parts['query'], $query_vars );
            if ( ! empty( $query_vars['v'] ) ) {
                return $query_vars['v'];
            }
        }

        return '';
    }

    public static function allowed_embed_tags() {
        return array(
            'iframe' => array(
                'src'             => true,
                'width'           => true,
                'height'          => true,
                'frameborder'     => true,
                'allow'           => true,
                'allowfullscreen' => true,
                'title'           => true,
            ),
        );
    }

    public static function render_card( $post_id ) {
        $title    = get_the_title( $post_id );
        $permalink = get_permalink( $post_id );
        $duration = get_post_meta( $post_id, 'isv_video_duration', true );
        $category = self::get_primary_term( $post_id, 'interslide_video_category' );
        $thumb    = get_the_post_thumbnail_url( $post_id, 'medium_large' );

        ob_start();
        ?>
        <article class="isv-card">
            <a href="<?php echo esc_url( $permalink ); ?>" class="isv-card__media">
                <?php if ( $thumb ) : ?>
                    <img src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy">
                <?php endif; ?>
                <?php if ( $duration ) : ?>
                    <span class="isv-card__duration"><?php echo esc_html( $duration ); ?></span>
                <?php endif; ?>
            </a>
            <div class="isv-card__content">
                <?php if ( $category ) : ?>
                    <span class="isv-card__category"><?php echo esc_html( $category->name ); ?></span>
                <?php endif; ?>
                <h3 class="isv-card__title"><a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a></h3>
            </div>
        </article>
        <?php
        return ob_get_clean();
    }

    public static function get_primary_term( $post_id, $taxonomy ) {
        $terms = get_the_terms( $post_id, $taxonomy );
        if ( $terms && ! is_wp_error( $terms ) ) {
            return $terms[0];
        }
        return null;
    }

    public static function format_duration_iso8601( $duration ) {
        if ( preg_match( '/^(\d+):(\d{2})$/', $duration, $matches ) ) {
            $minutes = (int) $matches[1];
            $seconds = (int) $matches[2];
            return sprintf( 'PT%dM%dS', $minutes, $seconds );
        }
        return $duration;
    }

    public static function is_video_context() {
        if ( is_singular( 'interslide_video' ) || is_post_type_archive( 'interslide_video' ) ) {
            return true;
        }

        if ( is_singular() ) {
            $post = get_post();
            if ( $post && has_shortcode( $post->post_content, 'interslide_video_grid' ) ) {
                return true;
            }
        }

        return false;
    }
}

ISV_Plugin::init();
