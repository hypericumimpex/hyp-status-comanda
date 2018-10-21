<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * WPML Compatibility Class
 *
 * @class   YITH_WCCOS_Wpml_Integration
 * @since   1.1.6
 *
 */
class YITH_WCCOS_Wpml_Integration {

    /** @var \YITH_WCCOS_Wpml_Integration */
    private static $_instance;

    /** @var SitePress */
    private $sitepress;

    public static function get_instance() {
        return !is_null( self::$_instance ) ? self::$_instance : self::$_instance = new self();
    }

    private function __construct() {
        global $sitepress;
        if ( $sitepress ) {
            $this->sitepress = $sitepress;

            // Translate status titles
            add_filter( 'yith_wccos_order_status_title', array( $this, 'translate_status_title' ), 10, 2 );
            add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
            add_action( 'save_post', array( $this, 'save_metabox' ) );
        }
    }

    public function get_current_language() {
        return $this->sitepress->get_current_language();
    }

    public function get_default_language() {
        return $this->sitepress->get_default_language();
    }

    public function translate_status_title( $title, $status_id ) {
        $current_language   = $this->get_current_language();
        $title_translations = get_post_meta( $status_id, '_yith_wccos_wpml_title_translations', true );
        if ( !!$title_translations && !empty( $title_translations[ $current_language ] ) ) {
            $title = $title_translations[ $current_language ];
        }

        return $title;
    }

    public function add_metabox() {
        add_meta_box( 'yith-wccos-wpml-translations',
                      __( 'WPML Traslations', 'yith-woocommerce-custom-order-status' ),
                      array( $this, 'show_title_translations_metabox' ),
                      'yith-wccos-ostatus',
                      'side',
                      'default' );
    }

    public function show_title_translations_metabox( $post ) {
        $languages        = $this->sitepress->get_active_languages();
        $default_language = $this->get_default_language();
        if ( isset( $languages[ $default_language ] ) ) {
            unset( $languages[ $default_language ] );
        }

        $title_translations = get_post_meta( $post->ID, '_yith_wccos_wpml_title_translations', true );

        foreach ( $languages as $language_code => $language ) {
            $language_name = isset( $language[ 'display_name' ] ) ? $language[ 'display_name' ] : $language_code;
            $name          = "_yith_wccos_wpml_title_translations[{$language_code}]";
            $value         = isset( $title_translations[ $language_code ] ) ? $title_translations[ $language_code ] : '';
            ?>
            <p>
                <label for="yith_wccos_wpml_title_translations_<?php echo $language_code; ?>"><?php echo sprintf( __( 'Title (%s)', 'yith-woocommerce-custom-order-status' ), $language_name ) ?></label>
                <input type="text" name="<?php echo $name; ?>"
                       id="yith_wccos_wpml_title_translations_<?php echo $language_code; ?>"
                       value="<?php echo $value ?>"/>
            </p>
            <?php
        }
    }

    public function save_metabox( $post_id ) {
        if ( isset( $_POST[ '_yith_wccos_wpml_title_translations' ] ) ) {
            update_post_meta( $post_id, '_yith_wccos_wpml_title_translations', $_POST[ '_yith_wccos_wpml_title_translations' ] );
        }
    }

}

return YITH_WCCOS_Wpml_Integration::get_instance();