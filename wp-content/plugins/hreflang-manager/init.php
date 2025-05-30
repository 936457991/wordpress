<?php
/**
 * Plugin Name: Hreflang Manager
 * Description: Set language and regional URL for better SEO performance.
 * Version: 1.43
 * Author: DAEXT
 * Author URI: https://daext.com
 * Text Domain: hreflang-manager
 * License: GPLv3
 *
 * @package hreflang-manager
 */

use Daextteam\PluginUpdateChecker\PluginUpdateChecker;

// Prevent direct access to this file.
if ( ! defined( 'WPINC' ) ) {
	die();
}

/**
 * The plugin edition, either 'FREE' or 'PRO'. Note that this constant is only used to enable or disable the Pro
 * version banners in the admin area.
 */
define( 'DAHM_EDITION', 'PRO' );

const DAHM_PLUGIN_UPDATE_CHECKER_SETTINGS = array(
	'slug'                          => 'hreflang-manager',
	'prefix'                        => 'da_hm',
	'wp_plugin_update_info_api_url' => 'https://daext.com/wp-json/daext-commerce/v1/wp-plugin-update-info/',
);

// Class shared across public and admin.
require_once plugin_dir_path( __FILE__ ) . 'shared/class-dahm-shared.php';

// Rest API.
require_once plugin_dir_path( __FILE__ ) . 'inc/class-dahm-rest.php';
add_action( 'plugins_loaded', array( 'Dahm_Rest', 'get_instance' ) );

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Register the update checker callbacks on filters.
 *
 * @return void
 */
function da_hm_register_update_checker_callbacks_on_filters() {

	$plugin_update_checker = new PluginUpdateChecker( DAHM_PLUGIN_UPDATE_CHECKER_SETTINGS );
	$plugin_update_checker->register_callbacks_on_filters();

}

add_action( 'plugins_loaded', 'da_hm_register_update_checker_callbacks_on_filters' );

// Public.
require_once plugin_dir_path( __FILE__ ) . 'public/class-dahm-public.php';
add_action( 'plugins_loaded', array( 'Dahm_Public', 'get_instance' ) );

// Perform the Gutenberg related activities only if Gutenberg is present.
if ( function_exists( 'register_block_type' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'blocks/src/init.php';
}

// Admin.
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	// Admin.
	require_once plugin_dir_path( __FILE__ ) . 'admin/class-dahm-admin.php';
	add_action( 'plugins_loaded', array( 'Dahm_Admin', 'get_instance' ) );

	// Activate.
	register_activation_hook( __FILE__, array( Dahm_Admin::get_instance(), 'ac_activate' ) );

	// Deactivate.
	register_deactivation_hook( __FILE__, array( Dahm_Admin::get_instance(), 'dc_deactivate' ) );

}

// Admin.
if ( is_admin() ) {

	require_once plugin_dir_path( __FILE__ ) . 'admin/class-dahm-admin.php';

	// If this is not an AJAX request, create a new singleton instance of the admin class.
	if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
		add_action( 'plugins_loaded', array( 'Dahm_Admin', 'get_instance' ) );
	}

	// Activate the plugin using only the class static methods.
	register_activation_hook( __FILE__, array( 'Dahm_Admin', 'ac_activate' ) );

	// Deactivate the plugin only with static methods.
	register_deactivation_hook( __FILE__, array( 'Dahm_Admin', 'dc_deactivate' ) );

}

// Ajax.
if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

	// Admin.
	require_once plugin_dir_path( __FILE__ ) . 'class-dahm-ajax.php';
	add_action( 'plugins_loaded', array( 'Dahm_Ajax', 'get_instance' ) );

}

/**
 * If we are in the admin area, update the plugin db tables and options if they are not up-to-date.
 */
if ( is_admin() ) {

	require_once plugin_dir_path( __FILE__ ) . 'admin/class-dahm-admin.php';

	// If needed, create or update the database tables.
	Dahm_Admin::ac_create_database_tables();

	// If needed, create or update the plugin options.
	Dahm_Admin::ac_initialize_options();

}

/**
 * Load the plugin text domain for translation.
 *
 * @return void
 */
function da_hm_load_plugin_textdomain() {
	load_plugin_textdomain( 'hreflang-manager', false, 'hreflang-manager/lang/' );
}

add_action( 'init', 'da_hm_load_plugin_textdomain' );