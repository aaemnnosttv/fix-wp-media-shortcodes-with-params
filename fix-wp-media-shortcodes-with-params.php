<?php
/**
 * Plugin Name: Fix Media Shortcodes with Params
 * Description: Fixes audio & video shortcodes used for embeds that have parameters in the src. See <a href="https://core.trac.wordpress.org/ticket/30377">https://core.trac.wordpress.org/ticket/30377</a>
 * Author: Evan Mattson
 * Author URI: https://aaemnnost.tv
 * Version: 1.0
 */

namespace FixMediaShortcodes;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$normal_file = wp_check_filetype( 'file.mp4', array( 'mp4' => 'video/mp4' ) );
$querys_file = wp_check_filetype( 'file.mp4?param=1', array( 'mp4' => 'video/mp4' ) );

/**
 * If wp_check_filetype is ever updated to support a query string, self-deactivate.
 */
if ( $normal_file['ext'] === $querys_file['ext'] ) {
    require_once ABSPATH . '/wp-admin/includes/plugin.php';
    deactivate_plugins( plugin_basename( __FILE__ ) );
    return;
}

function fix_media_shortcode_atts( $out, $pairs, $atts, $shortcode ) {
    $get_media_extensions = "wp_get_{$shortcode}_extensions";

    if ( ! function_exists( $get_media_extensions ) ) {
        return $out;
    }

    $default_types = $get_media_extensions();
    array_unshift( $default_types, 'src' );

    $fixes = array();

    foreach ( $default_types as $type ) {
        if ( empty( $out[ $type ] ) ) {
            continue;
        }

        if ( filter_var( $out[ $type ], FILTER_VALIDATE_URL, FILTER_FLAG_QUERY_REQUIRED ) ) {
            $url = $out[ $type ];
            $ext = pathinfo( explode( '?', $url )[0], PATHINFO_EXTENSION ); 

            // Temporarily add the extension to the END so wp_check_file_type can match it.
            // This will be removed from the final output of the shortcode below.
            $out[ $type ] .= "&wp-check-file-type=.$ext";
            $fixes[] = $ext;
        }
    }

    if ( $fixes ) {
        add_filter( "wp_{$shortcode}_shortcode", function( $html ) use ( $fixes ) {
            foreach ( $fixes as $ext ) {
                $html = str_replace( "&#038;wp-check-file-type=.$ext", '', $html );
            }

            return $html;
        } );
    }

    return $out;
}
add_filter( 'shortcode_atts_audio', __NAMESPACE__ . '\\fix_media_shortcode_atts', 10, 4 );
add_filter( 'shortcode_atts_video', __NAMESPACE__ . '\\fix_media_shortcode_atts', 10, 4 );
