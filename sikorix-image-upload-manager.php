<?php
/**
 * Plugin Name: Sikorix Image Upload Manager
 * Description: Set JPEG and WordPress image editor quality values and filter priorities when WordPress generates images.
 * Version: 3.1.2
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * Author: Sikorix
 * Author URI: https://sikorix.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: sikorix-image-upload-manager
 *
 * @package SikorixImageUploadManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Plugin version. */
define( 'SIKORIX_IUM_PLUGIN_VERSION', '3.1.2' );

/** Absolute path to the main plugin file. */
define( 'SIKORIX_IUM_PLUGIN_FILE', __FILE__ );

require_once __DIR__ . '/includes/options.php';
require_once __DIR__ . '/includes/filters.php';
require_once __DIR__ . '/includes/admin-page.php';

/**
 * Set default quality and priority options on first activation.
 */
function sikorix_ium_activate_plugin() {
	if ( false === get_option( SIKORIX_IUM_OPTION_JPEG_QUALITY, false ) ) {
		add_option( SIKORIX_IUM_OPTION_JPEG_QUALITY, SIKORIX_IUM_DEFAULT_QUALITY );
	}

	if ( false === get_option( SIKORIX_IUM_OPTION_EDITOR_QUALITY, false ) ) {
		add_option( SIKORIX_IUM_OPTION_EDITOR_QUALITY, SIKORIX_IUM_DEFAULT_QUALITY );
	}

	if ( false === get_option( SIKORIX_IUM_OPTION_JPEG_PRIORITY, false ) ) {
		add_option( SIKORIX_IUM_OPTION_JPEG_PRIORITY, SIKORIX_IUM_DEFAULT_FILTER_PRIORITY );
	}

	if ( false === get_option( SIKORIX_IUM_OPTION_EDITOR_PRIORITY, false ) ) {
		add_option( SIKORIX_IUM_OPTION_EDITOR_PRIORITY, SIKORIX_IUM_DEFAULT_FILTER_PRIORITY );
	}
}
register_activation_hook( SIKORIX_IUM_PLUGIN_FILE, 'sikorix_ium_activate_plugin' );
