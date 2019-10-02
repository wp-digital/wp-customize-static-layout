<?php
/**
 * Plugin Name: Customize Static Layout
 * Description: Manage static layout via the Customizer.
 * Plugin URI: https://github.com/redink-no/wp-customize-static-layout
 * Version: 1.2.0
 * Author: Innocode
 * Author URI: https://innocode.no/
 * Tested up to: 5.2.3
 * Text Domain: customize-static-layout
 *
 * @package CustomizeStaticLayout
 */

define( 'CUSTOMIZE_STATIC_LAYOUT', '1.2.0' );

require_once __DIR__ . '/includes/class-static-layout.php';

if ( version_compare( phpversion(), '5.5', '>=' ) ) {
    add_action( 'init', function () {
        global $customize_static_layout;

        $customize_static_layout = [];

        foreach ( apply_filters( CustomizeStaticLayout\StaticLayout::NAME . '_panels', [] ) as $panel => $settings ) {
            $customize_static_layout[ $panel ] = new CustomizeStaticLayout\StaticLayout( $panel, $settings );
            $customize_static_layout[ $panel ]->set_widgets( apply_filters( CustomizeStaticLayout\StaticLayout::NAME . "_{$panel}_widgets", [] ) );
        }
    }, 20 );
} else {
    if ( defined( 'WP_CLI' ) ) {
        WP_CLI::warning( _customize_static_layout_php_version_text() );
    } else {
        add_action( 'admin_notices', '_customize_static_layout_php_version_error' );
    }
}

/**
 * Admin notice for incompatible PHP version
 */
function _customize_static_layout_php_version_error() {
    printf( '<div class="error"><p>%s</p></div>', _customize_static_layout_php_version_text() );
}

/**
 * Describes the minimum PHP version
 *
 * @return string
 */
function _customize_static_layout_php_version_text() {
    return __( 'Customize Static Layout plugin error: Your version of PHP is too old to run this plugin. You must be running PHP 5.5 or higher.', 'customize-static-layout' );
}

/**
 * Displays static layout
 *
 * @param string $panel
 * @param array  $args
 */
function customize_static_layout( $panel, array $args = [] ) {
    do_action( CustomizeStaticLayout\StaticLayout::NAME . "_{$panel}_render", $args );
}
