<?php
/**
 * Uninstall plugin.
 *
 * @package hreflang-manager
 */

// Exit if this file is called outside WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die(); }

require_once plugin_dir_path( __FILE__ ) . 'shared/class-dahm-shared.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/class-dahm-admin.php';

// Delete options and tables.
Dahm_Admin::un_delete();
