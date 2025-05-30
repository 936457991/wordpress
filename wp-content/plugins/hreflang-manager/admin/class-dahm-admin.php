<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package hreflang-manager
 */

use Daextteam\PluginUpdateChecker\PluginUpdateChecker;

/**
 * This class should be used to work with the administrative side of WordPress.
 */
class Dahm_Admin {

	/**
	 * The instance of this class.
	 *
	 * @var null
	 */
	protected static $instance = null;

	/**
	 * The instance of the shared class.
	 *
	 * @var Dahm_Shared|null
	 */
	private $shared = null;

	/**
	 * The screen id of the "Connections" menu.
	 *
	 * @var null
	 */
	private $screen_id_connections = null;

	/**
	 * The screen id of the "Bulk Import" menu.
	 *
	 * @var null
	 */
	private $screen_id_bulk_import = null;

	/**
	 * The screen id of the "Tools" menu.
	 *
	 * @var null
	 */
	private $screen_id_tools = null;

	/**
	 * The screen id of the "Checker" menu.
	 *
	 * @var null
	 */
	private $screen_id_checker = null;

	/**
	 * The screen id of the "Maintenance" menu.
	 *
	 * @var null
	 */
	private $screen_id_maintenance = null;

	/**
	 * The screen id of the "Help" menu.
	 *
	 * @var null
	 */
	private $screen_id_help = null;

	/**
	 * The screen id of the "Options" menu.
	 *
	 * @var null
	 */
	private $screen_id_options = null;

	/**
	 * Instance of the class used to generate the back-end menus.
	 *
	 * @var null
	 */
	private $menu_elements = null;

	/**
	 * Constructor.
	 */
	private function __construct() {

		// Assign an instance of the plugin info.
		$this->shared = Dahm_Shared::get_instance();

		// Load admin stylesheets and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the admin menu.
		add_action( 'admin_menu', array( $this, 'me_add_admin_menu' ) );

		// Add the meta box.
		add_action( 'add_meta_boxes', array( $this, 'create_meta_box' ) );

		// Save the meta box.
		add_action( 'save_post', array( $this, 'save_meta_box' ) );

		// This hook is triggered during the creation of a new blog.
		add_action( 'wpmu_new_blog', array( $this, 'new_blog_create_options_and_tables' ), 10, 6 );

		// This hook is triggered during the deletion of a blog.
		add_action( 'delete_blog', array( $this, 'delete_blog_delete_options_and_tables' ), 10, 1 );

		// Perform a manual license verification when the user click the provided link to verify the license.
		add_action( 'admin_init', array( $this, 'manual_license_verification' ) );

		// Fires before a post is sent to the trash.
		add_action( 'wp_trash_post', array( $this, 'delete_post_connection' ) );

		// Require and instantiate the classes used to handle the menus.
		add_action( 'init', array( $this, 'handle_menus' ) );

	}

	/**
	 * Return an instance of this class.
	 *
	 * @return self|null
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * If we are in one of the plugin back-end menus require and instantiate the class used to handle the specific menu.
	 *
	 * @return void
	 */
	public function handle_menus() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce non-necessary for menu selection.
		$page_query_param = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : null;

		// Require and instantiate the class used to register the menu options.
		if ( null !== $page_query_param ) {

			$config = array(
				'admin_toolbar' => array(
					'items'      => array(
						array(
							'link_text' => __( 'Connections', 'hreflang-manager' ),
							'link_url'  => admin_url( 'admin.php?page=da_hm_connections' ),
							'icon'      => 'list',
							'menu_slug' => 'dahm-connection',
						),
						array(
							'link_text' => __( 'Bulk Import', 'hreflang-manager' ),
							'link_url'  => admin_url( 'admin.php?page=da_hm_bulk_import' ),
							'icon'      => 'database-02',
							'menu_slug' => 'dahm-bulk-import',
						),
						array(
							'link_text' => __( 'Tools', 'hreflang-manager' ),
							'link_url'  => admin_url( 'admin.php?page=da_hm_tools' ),
							'icon'      => 'tool-02',
							'menu_slug' => 'dahm-tool',
						),
					),
					'more_items' => array(
						array(
							'link_text' => __( 'Checker', 'hreflang-manager' ),
							'link_url'  => admin_url( 'admin.php?page=da_hm_checker' ),
							'pro_badge' => false,
						),
						array(
							'link_text' => __( 'Maintenance', 'hreflang-manager' ),
							'link_url'  => admin_url( 'admin.php?page=da_hm_maintenance' ),
							'pro_badge' => false,
						),
						array(
							'link_text' => __( 'Options', 'hreflang-manager' ),
							'link_url'  => admin_url( 'admin.php?page=da_hm_options' ),
							'pro_badge' => false,
						),
					),
				),
			);

			// The parent class.
			require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/class-dahm-menu-elements.php';

			// Use the correct child class based on the page query parameter.
			if ( 'da_hm_connections' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-dahm-connections-menu-elements.php';
				$this->menu_elements = new Dahm_Connections_Menu_Elements( $this->shared, $page_query_param, $config );
			}
			if ( 'da_hm_bulk_import' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-dahm-bulk-import-menu-elements.php';
				$this->menu_elements = new Dahm_Bulk_Import_Menu_Elements( $this->shared, $page_query_param, $config );
			}
			if ( 'da_hm_tools' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-dahm-tools-menu-elements.php';
				$this->menu_elements = new Dahm_Tools_Menu_Elements( $this->shared, $page_query_param, $config );
			}
			if ( 'da_hm_checker' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-dahm-checker-menu-elements.php';
				$this->menu_elements = new Dahm_Checker_Menu_Elements( $this->shared, $page_query_param, $config );
			}
			if ( 'da_hm_maintenance' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-dahm-maintenance-menu-elements.php';
				$this->menu_elements = new Dahm_Maintenance_Menu_Elements( $this->shared, $page_query_param, $config );
			}
			if ( 'da_hm_options' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-dahm-options-menu-elements.php';
				$this->menu_elements = new Dahm_Options_Menu_Elements( $this->shared, $page_query_param, $config );
			}
		}

	}

	/**
	 * Enqueue admin-specific styles.
	 *
	 * @return void
	 */
	public function enqueue_admin_styles() {

		$wp_localize_script_data = array();

		$screen = get_current_screen();

		// Menu connections.
		if ( $screen->id === $this->screen_id_connections ) {

			// Select2.
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/css/select2.min.css',
				array(),
				$this->shared->get( 'ver' )
			);

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array(), $this->shared->get( 'ver' ) );

			// jQuery UI Dialog.
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-jquery-ui-dialog',
				$this->shared->get( 'url' ) . 'admin/assets/css/jquery-ui-dialog.css',
				array(),
				$this->shared->get( 'ver' )
			);

		}

		// Menu Bulk Import.
		if ( $screen->id === $this->screen_id_bulk_import ) {

			// Store the JavaScript parameters in the window.DAHM_PARAMETERS object.
			$initialization_script  = 'window.DAHM_PARAMETERS = {';
			$initialization_script .= 'ajaxUrl: "' . admin_url( 'admin-ajax.php' ) . '",';
			$initialization_script .= 'nonce: "' . wp_create_nonce( 'dahm' ) . '",';
			$initialization_script .= 'adminUrl: "' . get_admin_url() . '",';
			$initialization_script .= '};';

			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/js/select2.min.js',
				array( 'jquery' ),
				$this->shared->get( 'ver' ),
				true
			);

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu-bulk-import', $this->shared->get( 'url' ) . 'admin/assets/js/menu-bulk-import.js', array( 'jquery', $this->shared->get( 'slug' ) . '-select2' ), $this->shared->get( 'ver' ), true );
			wp_localize_script( $this->shared->get( 'slug' ) . '-menu-bulk-import', 'objectL10n', $wp_localize_script_data );

			// Handsontable.
			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-handsontable-full',
				$this->shared->get( 'url' ) . 'admin/assets/inc/handsontable/handsontable.full.min.js',
				array( 'jquery' ),
				$this->shared->get( 'ver' ),
				true
			);

			wp_add_inline_script( $this->shared->get( 'slug' ) . '-menu-bulk-import', $initialization_script, 'before' );

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-menu/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

		}

		// Menu Tools.
		if ( $screen->id === $this->screen_id_tools ) {

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array(), $this->shared->get( 'ver' ) );

		}

		// Menu Checker.
		if ( $screen->id === $this->screen_id_checker ) {

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array(), $this->shared->get( 'ver' ) );

		}

		// Menu Maintenance.
		if ( $screen->id === $this->screen_id_maintenance ) {

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array(), $this->shared->get( 'ver' ) );

			// Select2.
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/css/select2.min.css',
				array(),
				$this->shared->get( 'ver' )
			);

			// jQuery UI Dialog.
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-jquery-ui-dialog',
				$this->shared->get( 'url' ) . 'admin/assets/css/jquery-ui-dialog.css',
				array(),
				$this->shared->get( 'ver' )
			);

		}

		// Menu Options.
		if ( $screen->id === $this->screen_id_options ) {

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array( 'wp-components' ), $this->shared->get( 'ver' ) );

		}

		$meta_box_post_types_a = maybe_unserialize( get_option( $this->shared->get( 'slug' ) . '_meta_box_post_types' ) );

		if ( in_array( $screen->id, $meta_box_post_types_a, true ) ) {
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-meta-box', $this->shared->get( 'url' ) . 'admin/assets/css/meta-box.css', array(), $this->shared->get( 'ver' ) );

			// Select2.
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/css/select2.min.css',
				array(),
				$this->shared->get( 'ver' )
			);

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-select2-custom', $this->shared->get( 'url' ) . 'admin/assets/css/select2-custom.css', array(), $this->shared->get( 'ver' ) );

		}
	}

	/**
	 * Enqueue admin-specific JavaScript.
	 *
	 * @return void
	 */
	public function enqueue_admin_scripts() {

		$wp_localize_script_data = array(
			'deleteText' => esc_attr__( 'Delete', 'hreflang-manager' ),
			'cancelText' => esc_attr__( 'Cancel', 'hreflang-manager' ),
		);

		$screen = get_current_screen();

		// General.
		wp_enqueue_script( $this->shared->get( 'slug' ) . '-general', $this->shared->get( 'url' ) . 'admin/assets/js/general.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

		// Menu connections.
		if ( $screen->id === $this->screen_id_connections ) {

			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/js/select2.min.js',
				array( 'jquery' ),
				$this->shared->get( 'ver' ),
				true
			);

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-menu/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu-connections', $this->shared->get( 'url' ) . 'admin/assets/js/menu-connections.js', array( 'jquery', $this->shared->get( 'slug' ) . '-select2', 'jquery-ui-dialog' ), $this->shared->get( 'ver' ), true );
			wp_localize_script( $this->shared->get( 'slug' ) . '-menu-connections', 'objectL10n', $wp_localize_script_data );

		}

		// Menu Bulk Import.
		if ( $screen->id === $this->screen_id_bulk_import ) {

			// Handsontable.
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-handsontable-full',
				$this->shared->get( 'url' ) . 'admin/assets/inc/handsontable/handsontable.full.min.css',
				array(),
				$this->shared->get( 'ver' )
			);

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array(), $this->shared->get( 'ver' ) );

			// Select2.
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/css/select2.min.css',
				array(),
				$this->shared->get( 'ver' )
			);

		}

		// Menu tools.
		if ( $screen->id === $this->screen_id_tools ) {

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-menu/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

		}

		// Menu checker.
		if ( $screen->id === $this->screen_id_checker ) {

			// Store the JavaScript parameters in the window.DAHM_PARAMETERS object.
			$initialization_script  = 'window.DAHM_PARAMETERS = {';
			$initialization_script .= 'ajax_url: "' . admin_url( 'admin-ajax.php' ) . '",';
			$initialization_script .= 'admin_url: "' . get_admin_url() . '",';
			$initialization_script .= 'site_url: "' . get_site_url() . '",';
			$initialization_script .= 'plugin_url: "' . $this->shared->get( 'url' ) . '",';
			$initialization_script .= 'checker_data_last_update: "' . get_option( $this->shared->get( 'slug' ) . '_checker_data_last_update' ) . '",';
			$initialization_script .= 'checker_data_update_frequency: "' . get_option( $this->shared->get( 'slug' ) . '_checker_data_update_frequency' ) . '",';
			$initialization_script .= 'current_time: "' . current_time( 'mysql' ) . '"';
			$initialization_script .= '};';

			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-checker-menu',
				$this->shared->get( 'url' ) . 'admin/react/checker-menu/build/index.js',
				array( 'wp-element', 'wp-api-fetch', 'wp-i18n' ),
				$this->shared->get( 'ver' ),
				true
			);

			wp_add_inline_script( $this->shared->get( 'slug' ) . '-checker-menu', $initialization_script, 'before' );

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-menu/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

		}

		// Menu Maintenance.
		if ( $screen->id === $this->screen_id_maintenance ) {

			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/js/select2.min.js',
				array( 'jquery' ),
				$this->shared->get( 'ver' ),
				true
			);

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-menu/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

			// Maintenance Menu.
			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-menu-maintenance',
				$this->shared->get( 'url' ) . 'admin/assets/js/menu-maintenance.js',
				array( 'jquery', 'jquery-ui-dialog', $this->shared->get( 'slug' ) . '-select2' ),
				$this->shared->get( 'ver' ),
				true
			);
			wp_localize_script(
				$this->shared->get( 'slug' ) . '-menu-maintenance',
				'objectL10n',
				$wp_localize_script_data
			);

		}

		// Menu Help.
		if ( $screen->id === $this->screen_id_help ) {

			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-menu-help',
				$this->shared->get( 'url' ) . 'admin/assets/css/menu-help.css',
				array(),
				$this->shared->get( 'ver' )
			);

		}

		// Menu Options.
		if ( $screen->id === $this->screen_id_options ) {

			// Store the JavaScript parameters in the window.DAEXTDAHM_PARAMETERS object.
			$initialization_script  = 'window.DAEXTDAHM_PARAMETERS = {';
			$initialization_script .= 'ajax_url: "' . admin_url( 'admin-ajax.php' ) . '",';
			$initialization_script .= 'nonce: "' . wp_create_nonce( 'daextdahm' ) . '",';
			$initialization_script .= 'admin_url: "' . get_admin_url() . '",';
			$initialization_script .= 'site_url: "' . get_site_url() . '",';
			$initialization_script .= 'plugin_url: "' . $this->shared->get( 'url' ) . '",';
			$initialization_script .= 'options_configuration_pages: ' . wp_json_encode( $this->shared->menu_options_configuration() );
			$initialization_script .= '};';

			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-menu-options',
				$this->shared->get( 'url' ) . 'admin/react/options-menu/build/index.js',
				array( 'wp-element', 'wp-api-fetch', 'wp-i18n', 'wp-components' ),
				$this->shared->get( 'ver' ),
				true
			);

			wp_add_inline_script( $this->shared->get( 'slug' ) . '-menu-options', $initialization_script, 'before' );

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-menu/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

		}

		$meta_box_post_types_a = maybe_unserialize( get_option( $this->shared->get( 'slug' ) . '_meta_box_post_types' ) );
		if ( in_array( $screen->id, $meta_box_post_types_a, true ) ) {

			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/js/select2.min.js',
				array( 'jquery' ),
				$this->shared->get( 'ver' ),
				true
			);

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-meta-box', $this->shared->get( 'url' ) . 'admin/assets/js/meta-box.js', array( 'jquery', $this->shared->get( 'slug' ) . '-select2' ), $this->shared->get( 'ver' ), true );
		}
	}

	/**
	 * Plugin activation.
	 *
	 * @param bool $networkwide True if the plugin is being activated network-wide.
	 *
	 * @return void
	 */
	public static function ac_activate( $networkwide ) {

		// Assign an instance of Dahm_Shared.
		$shared = Dahm_Shared::get_instance();

		// Delete options and tables for all the sites in the network.
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			// If this is a "Network Activation" create the options and tables for each blog.
			if ( $networkwide ) {

				// Get the current blog id.
				global $wpdb;
				$current_blog = $wpdb->blogid;

				// Create an array with all the blog ids.

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

				// Iterate through all the blogs.
				foreach ( $blogids as $blog_id ) {

					// Switch to the iterated blog.
					switch_to_blog( $blog_id );

					// Create options and tables for the iterated blog.
					self::ac_initialize_options();
					self::ac_create_database_tables();
					$shared->schedule_cron_event();

				}

				// Switch to the current blog.
				switch_to_blog( $current_blog );

			} else {

				// If this is not a "Network Activation" create options and tables only for the current blog.
				self::ac_initialize_options();
				self::ac_create_database_tables();
				$shared->schedule_cron_event();

			}
		} else {

			// If this is not a multisite installation create options and tables only for the current blog.
			self::ac_initialize_options();
			self::ac_create_database_tables();
			$shared->schedule_cron_event();

		}
	}

	/**
	 * Create the options and tables for the newly created blog.
	 *
	 * @param int $blog_id The id of the blog.
	 *
	 * @return void
	 */
	public function new_blog_create_options_and_tables( $blog_id ) {

		global $wpdb;

		// If the plugin is "Network Active" create the options and tables for this new blog.
		if ( is_plugin_active_for_network( 'hreflang-manager/init.php' ) ) {

			// Get the id of the current blog.
			$current_blog = $wpdb->blogid;

			// Switch to the blog that is being activated.
			switch_to_blog( $blog_id );

			// Create options and database tables for the new blog.
			$this->ac_initialize_options();
			$this->ac_create_database_tables();
			$this->shared->schedule_cron_event();

			// Switch to the current blog.
			switch_to_blog( $current_blog );

		}
	}

	/**
	 * Delete options and tables for the deleted blog.
	 *
	 * @param int $blog_id The id of the blog.
	 *
	 * @return void
	 */
	public function delete_blog_delete_options_and_tables( $blog_id ) {

		global $wpdb;

		// Get the id of the current blog.
		$current_blog = $wpdb->blogid;

		// Switch to the blog that is being activated.
		switch_to_blog( $blog_id );

		// Create options and database tables for the new blog.
		$this->un_delete_options();
		$this->un_delete_database_tables();

		// Switch to the current blog.
		switch_to_blog( $current_blog );
	}

	/**
	 * Initialize plugin options.
	 *
	 * @return void
	 */
	public static function ac_initialize_options() {

		if ( intval( get_option( 'da_hm_options_version' ), 10 ) < 2 ) {

			// Assign an instance of Dahm_Shared.
			$shared = Dahm_Shared::get_instance();

			foreach ( $shared->get( 'options' ) as $key => $value ) {
				add_option( $key, $value );
			}

			// Make the options compatible with the new plugin versions.
			$shared->convert_options_data();

			// Update options version.
			update_option( 'da_hm_options_version', '2' );

		}
	}

	/**
	 * Create the plugin database tables.
	 *
	 * @return void
	 */
	public static function ac_create_database_tables() {

		// Check database version and create the database.
		if ( intval( get_option( 'da_hm_database_version' ), 10 ) < 7 ) {

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			// Assign an instance of Dahm_Shared.
			$shared = Dahm_Shared::get_instance();

			global $wpdb;
			$table_name = $wpdb->prefix . 'da_hm_connect';
			$sql        = "CREATE TABLE $table_name (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                url_to_connect TEXT DEFAULT '' NOT NULL,
                url MEDIUMTEXT DEFAULT '' NOT NULL,
                language MEDIUMTEXT DEFAULT '' NOT NULL,
                script MEDIUMTEXT DEFAULT '' NOT NULL,
                locale MEDIUMTEXT DEFAULT '' NOT NULL,
                inherited TINYINT(1) DEFAULT 0 NOT NULL
            )
            COLLATE = utf8_general_ci
            ";

			dbDelta( $sql );

			global $wpdb;
			$table_name = $wpdb->prefix . 'da_hm_hreflang_checker_queue';
			$sql        = "CREATE TABLE $table_name (
                hreflang_checker_queue_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                alternate_url TEXT NOT NULL,
                url_to_connect TEXT NOT NULL,
                checked tinyint(1) NOT NULL DEFAULT 0
            )
            COLLATE = utf8_general_ci
            ";

			dbDelta( $sql );

			global $wpdb;
			$table_name = $wpdb->prefix . 'da_hm_hreflang_checker_issue';
			$sql        = "CREATE TABLE $table_name (
                hreflang_checker_issue_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                issue_type TEXT NOT NULL,
                severity ENUM('warning', 'error') DEFAULT 'warning' NOT NULL,
                alternate_url TEXT NOT NULL,
                details TEXT NOT NULL,
                date DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
                url_to_connect TEXT NOT NULL
            )
            COLLATE = utf8_general_ci
            ";

			dbDelta( $sql );

			if ( intval( get_option( 'da_hm_database_version' ), 10 ) < 6 ) {

				/**
				 * Add to the database, in the four fields created in the previous query, the serialized data of url,
				 * language, script, and locale.
				 *
				 * Specifically, the data are retrieved from url[1-100], language[1-100], script[1-100], and locale[1-100]
				 * and added as serialized strings to the url, language, script, and locale fields.
				 */

				// Iterate over all the records in the database.
				global $wpdb;

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$connect_a = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}da_hm_connect ORDER BY id ASC", ARRAY_A );

				foreach ( $connect_a as $connect ) {

					// Put the data in 4 arrays.
					for ( $i = 1; $i <= 100; $i ++ ) {

						$url[ $i ]      = $connect[ 'url' . $i ];
						$language[ $i ] = $connect[ 'language' . $i ];
						$script[ $i ]   = $connect[ 'script' . $i ];
						$locale[ $i ]   = $connect[ 'locale' . $i ];

					}

					// Serialize the 4 arrays.
					$url_json      = wp_json_encode( $url );
					$language_json = wp_json_encode( $language );
					$script_json   = wp_json_encode( $script );
					$locale_json   = wp_json_encode( $locale );

					// Save the 4 serialized fields in the record with the iterated id.

					// phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$wpdb->query(
						$wpdb->prepare(
							"UPDATE {$wpdb->prefix}da_hm_connect SET
	                         url = %s,
	                         language = %s,
	                         script = %s,
	                         locale = %s
	                        WHERE id = %d",
							$url_json,
							$language_json,
							$script_json,
							$locale_json,
							$connect['id']
						)
					);

				}

			}

			// Update database version.
			update_option( 'da_hm_database_version', '7' );

		}
	}

	/**
	 * Plugin delete.
	 *
	 * @return void
	 */
	public static function un_delete() {

		// Delete options and tables for all the sites in the network.
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			// Get the current blog id.
			global $wpdb;
			$current_blog = $wpdb->blogid;

			// Create an array with all the blog ids.

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

			// Iterate through all the blogs.
			foreach ( $blogids as $blog_id ) {

				// Switch to the iterated blog.
				switch_to_blog( $blog_id );

				// Create options and tables for the iterated blog.
				self::un_delete_options();
				self::un_delete_database_tables();

			}

			// Switch to the current blog.
			switch_to_blog( $current_blog );

		} else {

			// If this is not a multisite installation delete options and tables only for the current blog.
			self::un_delete_options();
			self::un_delete_database_tables();

		}
	}

	/**
	 * Delete plugin options.
	 *
	 * @return void
	 */
	public static function un_delete_options() {

		// Assign an instance of Dahm_Shared.
		$shared = Dahm_Shared::get_instance();

		foreach ( $shared->get( 'options' ) as $key => $value ) {
			delete_option( $key );
		}
	}

	/**
	 * Delete plugin database tables.
	 *
	 * @return void
	 */
	public static function un_delete_database_tables() {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( "DROP TABLE {$wpdb->prefix}da_hm_connect" );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( "DROP TABLE {$wpdb->prefix}da_hm_hreflang_checker_issue" );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( "DROP TABLE {$wpdb->prefix}da_hm_hreflang_checker_queue" );

	}

	// meta box -----------------------------------------------------------------.

	/**
	 * The add_meta_boxes hook callback.
	 *
	 * @return void
	 */
	public function create_meta_box() {

		// Verify the capability.
		if ( current_user_can( get_option( $this->shared->get( 'slug' ) . '_meta_box_capability' ) ) ) {

			$post_types_a = maybe_unserialize( get_option( $this->shared->get( 'slug' ) . '_meta_box_post_types' ) );

			foreach ( $post_types_a as $key => $post_type ) {
				$post_type = trim( $post_type );
				add_meta_box(
					'da-hm-meta',
					'Hreflang Manager',
					array( $this, 'meta_box_callback' ),
					$post_type,
					'normal',
					'high',
					// Ref: https://make.wordpress.org/core/2018/11/07/meta-box-compatibility-flags/ .
					array(

						/*
						 * It's not confirmed that this meta box works in the block editor.
						 */
						'__block_editor_compatible_meta_box' => false,

						/*
						 * This meta box should only be loaded in the classic editor interface, and the block editor
						 * should not display it.
						 */
						'__back_compat_meta_box' => true,

					)
				);
			}
		}
	}

	/**
	 * Display the Hreflang Manager meta box content.
	 *
	 * @return void
	 */
	public function meta_box_callback() {

		?>

		<table class="form-table table-hreflang-manager">

			<tbody>

			<?php

			/**
			 * Activate the 'disabled="disabled"' attribute when the post status is not:
			 *  - publish
			 *  - future
			 *  - pending
			 *  - private
			 */
			$post_status = get_post_status();
			if ( 'publish' !== $post_status && 'future' !== $post_status && 'pending' !== $post_status && 'private' !== $post_status ) {
				$input_disabled = 'disabled';
			} else {
				$input_disabled = '';
			}

			/**
			 * Look for a connection that has as a url_to_connect value the permalink value of this post
			 *
			 *  If there is already a connection:
			 *  - show the form with the field already filled with the value from the database
			 *  If there is no connection:
			 *  - show the form with empty fields
			 */

			// Get the number of connections that should be displayed in the menu.
			$connections_in_menu = intval( get_option( 'da_hm_connections_in_menu' ), 10 );

			$permalink = $this->shared->get_permalink( get_the_ID(), true );

			// Look for $permalink in the url_to_connect field of the da_hm_connect database table.
			global $wpdb;

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$permalink_connections = $wpdb->get_row(
				$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}da_hm_connect WHERE url_to_connect = %s", $permalink )
			);

			if ( null === $permalink_connections ) {

				// Default empty form.
				for ( $i = 1; $i <= $connections_in_menu; $i++ ) {

					?>

					<!-- url -->
					<tr valign="top">
						<th scope="row"><label for="url<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'URL', 'hreflang-manager' ); ?> <?php echo esc_html( $i ); ?></label></th>
						<td><input autocomplete="off" <?php echo esc_attr( $input_disabled ); ?> type="text" id="url<?php echo esc_attr( $i ); ?>" maxlength="2083" name="url<?php echo esc_attr( $i ); ?>" class="regular-text dahm-url"/></td>
					</tr>

					<!-- Language -->
					<tr valign="top">
						<th scope="row"><label for="language<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Language', 'hreflang-manager' ); ?> <?php echo esc_html( $i ); ?></label></th>
						<td>
							<select <?php echo esc_attr( $input_disabled ); ?> id="language<?php echo esc_attr( $i ); ?>" class="dahm-language" name="language<?php echo esc_attr( $i ); ?>">
								<?php

								$array_language = get_option( 'da_hm_language' );
								foreach ( $array_language as $key => $value ) {
									echo '<option value="' . esc_attr( $value ) . '" ' . selected( get_option( 'da_hm_default_language_' . $i ), $value, false ) . '>' . esc_html( $value ) . ' - ' . esc_html( $key ) . '</option>';
								}

								?>
							</select>
						</td>
					</tr>

					<!-- Script -->
					<tr valign="top">
						<th scope="row"><label for="script<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Script', 'hreflang-manager' ); ?> <?php echo esc_html( $i ); ?></label></th>
						<td>
							<select <?php echo esc_attr( $input_disabled ); ?> id="script<?php echo esc_attr( $i ); ?>" class="dahm-script" name="script<?php echo esc_attr( $i ); ?>">
								<option value=""><?php esc_html_e( 'Not Assigned', 'hreflang-manager' ); ?></option>
								<?php

								$array_language = get_option( 'da_hm_script' );
								foreach ( $array_language as $key => $value ) {
									echo '<option value="' . esc_attr( $value ) . '" ' . selected( get_option( 'da_hm_default_script_' . $i ), $value, false ) . '>' . esc_html( $value ) . ' - ' . esc_html( $key ) . '</option>';
								}

								?>
							</select>
						</td>
					</tr>
					
					<!-- Locale -->
					<tr valign="top">
						<th scope="row"><label for="locale<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Locale', 'hreflang-manager' ); ?> <?php echo esc_html( $i ); ?></label></th>
						<td>
							<select <?php echo esc_attr( $input_disabled ); ?> id="locale<?php echo esc_attr( $i ); ?>" class="dahm-locale" name="locale<?php echo esc_attr( $i ); ?>">
								<option value=""><?php esc_html_e( 'Not Assigned', 'hreflang-manager' ); ?></option>
								<?php

								$array_language = get_option( 'da_hm_locale' );
								foreach ( $array_language as $key => $value ) {
									echo '<option value="' . esc_attr( $value ) . '" ' . selected( get_option( 'da_hm_default_locale_' . $i ), $value, false ) . '>' . esc_html( $value ) . ' - ' . esc_html( $key ) . '</option>';
								}

								?>
							</select>
						</td>
					</tr>

					<?php

				}
			} else {

				// Decode the connection data.
				$permalink_connections->url      = json_decode( $permalink_connections->url );
				$permalink_connections->language = json_decode( $permalink_connections->language );
				$permalink_connections->script   = json_decode( $permalink_connections->script );
				$permalink_connections->locale   = json_decode( $permalink_connections->locale );

				// Form with data retrieved form the database.
				for ( $i = 1; $i <= $connections_in_menu; $i++ ) {

					?>

					<!-- url -->
					<tr valign="top">
						<th scope="row"><label for="url<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'URL', 'hreflang-manager' ); ?> <?php echo esc_html( $i ); ?></label></th>
						<td><input autocomplete="off" type="text" value="<?php echo esc_attr( stripslashes( $permalink_connections->url->{$i} ) ); ?>" id="url<?php echo esc_attr( $i ); ?>" maxlength="2083" name="url<?php echo esc_attr( $i ); ?>" class="regular-text dahm-url"/></td>

					</tr>

					<!-- Language <?php echo intval( $i, 10 ); ?> -->
					<tr valign="top">
						<th scope="row"><label for="language<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Language', 'hreflang-manager' ); ?> <?php echo esc_html( $i ); ?></label></th>
						<td>
							<select id="language<?php echo esc_attr( $i ); ?>" class="dahm-language" name="language<?php echo esc_attr( $i ); ?>">
								<?php

								$array_language = get_option( 'da_hm_language' );
								foreach ( $array_language as $key => $value ) {
									echo '<option value="' . esc_attr( $value ) . '" ' . selected( $permalink_connections->language->{$i}, $value, false ) . '>' . esc_html( $value ) . ' - ' . esc_html( $key ) . '</option>';
								}

								?>
							</select>
						</td>
					</tr>

					<!-- Script <?php echo intval( $i, 10 ); ?> -->
					<tr valign="top">
						<th scope="row"><label for="script<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Script', 'hreflang-manager' ); ?> <?php echo esc_html( $i ); ?></label></th>
						<td>
							<select id="script<?php echo esc_attr( $i ); ?>" class="dahm-script" name="script<?php echo esc_attr( $i ); ?>">
								<option value=""><?php esc_html_e( 'Not Assigned', 'hreflang-manager' ); ?></option>
								<?php

								$array_language = get_option( 'da_hm_script' );
								foreach ( $array_language as $key => $value ) {
									echo '<option value="' . esc_attr( $value ) . '" ' . selected( $permalink_connections->script->{$i}, $value, false ) . '>' . esc_html( $value ) . ' - ' . esc_html( $key ) . '</option>';
								}

								?>
							</select>
						</td>
					</tr>
					
					<!-- Locale <?php echo intval( $i, 10 ); ?> -->
					<tr valign="top">
						<th scope="row"><label for="locale<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Locale', 'hreflang-manager' ); ?> <?php echo esc_html( $i ); ?></label></th>
						<td>
							<select id="locale<?php echo esc_attr( $i ); ?>" class="dahm-locale" name="locale<?php echo esc_attr( $i ); ?>">
								<option value=""><?php esc_html_e( 'Not Assigned', 'hreflang-manager' ); ?></option>
								<?php

								$array_language = get_option( 'da_hm_locale' );
								foreach ( $array_language as $key => $value ) {
									echo '<option value="' . esc_attr( $value ) . '" ' . selected( $permalink_connections->locale->{$i}, $value, false ) . '>' . esc_html( $value ) . ' - ' . esc_html( $key ) . '</option>';
								}

								?>
							</select>
						</td>
					</tr>

					<?php

				}
			}

			?>

			</tbody>

		</table>

		<?php

		// Use nonce for verification.
		wp_nonce_field( plugin_basename( __FILE__ ), 'da_hm_nonce' );
	}

	/**
	 * Save the meta box data.
	 *
	 * @return void
	 */
	public function save_meta_box() {

		// Verify the capability.
		if ( ! current_user_can( get_option( $this->shared->get( 'slug' ) . '_meta_box_capability' ) ) ) {
			return;}

		// Security verification.

		// Verify if this is an auto save routine. If our form has not been submitted, we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		/**
		 * Verify this came from our screen and with proper authorization, because save_post can be triggered at other
		 * times.
		 */
		if ( isset( $_POST['da_hm_nonce'] ) ) {
			$nonce = sanitize_text_field( wp_unslash( $_POST['da_hm_nonce'] ) );
			if ( ! wp_verify_nonce( $nonce, plugin_basename( __FILE__ ) ) ) {
				return;
			}
		} else {
			return;
		}

		/* - end security verification - */

		/*
		 * Return ( do not save ) if the post status is not:
		 * - publish
		 * - future
		 * - pending
		 * - private
		 */
		$post_status = get_post_status();
		if ( 'publish' !== $post_status && 'future' !== $post_status && 'pending' !== $post_status && 'private' !== $post_status ) {
			return;}

		// Init vars.
		$url      = array();
		$language = array();
		$script   = array();
		$locale   = array();

		// Initialize the variables that include the URLs, the languages and the locale.
		for ( $i = 1;$i <= 100;$i++ ) {

			if ( isset( $_POST[ 'url' . $i ] ) && strlen( trim( esc_url_raw( wp_unslash( $_POST[ 'url' . $i ] ) ) ) ) > 0 ) {
				$url[ $i ]        = esc_url_raw( wp_unslash( $_POST[ 'url' . $i ] ) );
				$at_least_one_url = true;
			} else {
				$url[ $i ] = '';
			}

			if ( isset( $_POST[ 'language' . $i ] ) ) {
				$language[ $i ] = sanitize_text_field( wp_unslash( $_POST[ 'language' . $i ] ) );
			} else {
				$language[ $i ] = get_option( $this->shared->get( 'slug' ) . '_default_language_' . $i );
			}

			if ( isset( $_POST[ 'script' . $i ] ) ) {
				$script[ $i ] = sanitize_text_field( wp_unslash( $_POST[ 'script' . $i ] ) );
			} else {
				$script[ $i ] = get_option( $this->shared->get( 'slug' ) . '_default_script_' . $i );
			}

			if ( isset( $_POST[ 'locale' . $i ] ) ) {
				$locale[ $i ] = sanitize_text_field( wp_unslash( $_POST[ 'locale' . $i ] ) );
			} else {
				$locale[ $i ] = get_option( $this->shared->get( 'slug' ) . '_default_locale_' . $i );
			}
		}

		// JSON encode for the serialized field of the database.
		$url_json      = wp_json_encode( $url );
		$language_json = wp_json_encode( $language );
		$script_json   = wp_json_encode( $script );
		$locale_json   = wp_json_encode( $locale );

		/*
		 * save the fields in the da_hm_connect database table:
		 *
		 * - if a row with the da_hm_connect equal to the current permalink already exists update the row
		 *
		 * - if a row with the da_hm_connect equal to the current permalink doesn't exists create a new row
		 */
		$permalink = $this->shared->get_permalink( get_the_ID(), true );

		// Look for $permalink in the url_to_connect field of the da_hm_connect database table.
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$permalink_connections = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}da_hm_connect WHERE url_to_connect = %s", $permalink )
		);

		if ( null !== $permalink_connections ) {

			// Update an existing connection.

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->prefix}da_hm_connect SET
                 url = %s,
                 language = %s,
                 script = %s,
                 locale = %s
                WHERE url_to_connect = %s ",
					$url_json,
					$language_json,
					$script_json,
					$locale_json,
					$permalink
				)
			);

		} else {

			// Return ( do not create a new connection ) if there are not a single url defined.
			if ( ! isset( $at_least_one_url ) ) {
				return;}

			// Add a new connection into the database.

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->query(
				$wpdb->prepare(
					"INSERT INTO {$wpdb->prefix}da_hm_connect SET url_to_connect = %s ,
                  url = %s,
                 language = %s,
                 script = %s,
                 locale = %s",
					$permalink,
					$url_json,
					$language_json,
					$script_json,
					$locale_json
				)
			);

		}
	}

	/**
	 * Register the admin menu.
	 *
	 * @return void
	 */
	public function me_add_admin_menu() {

		$icon_svg = '
		<svg id="globe" xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 40 40">
		  <defs>
		    <style>
		      .cls-1 {
		        fill: #fff;
		        stroke-width: 0px;
		      }
		    </style>
		  </defs>
		  <path class="cls-1" d="M38,20c0-9.4-7.3-17.2-16.5-17.9-.5,0-1,0-1.5,0s-1,0-1.5,0C9.3,2.8,2,10.6,2,20s7.3,17.2,16.5,17.9c.5,0,1,0,1.5,0s1,0,1.5,0c9.2-.8,16.5-8.5,16.5-17.9ZM30,19c-.1-2.7-.7-5.2-1.6-7.6,1.3-.5,2.6-1.1,3.8-1.9,2.2,2.6,3.6,5.8,3.9,9.4h-6ZM21,4.4c1.8,1.7,3.4,3.6,4.6,5.8-1.5.4-3,.7-4.6.7v-6.6ZM19,11c-1.6,0-3.1-.3-4.6-.7,1.2-2.2,2.7-4.2,4.6-5.8v6.6ZM19,13v6h-7c.1-2.4.6-4.8,1.5-6.9,1.7.5,3.6.8,5.4.9ZM19,21v6c-1.9,0-3.7.4-5.4.9-.9-2.2-1.4-4.5-1.5-6.9h7ZM19,29v6.6c-1.8-1.7-3.4-3.6-4.6-5.8,1.5-.4,3-.7,4.6-.7ZM21,29c1.6,0,3.1.3,4.6.7-1.2,2.2-2.7,4.2-4.6,5.8v-6.6ZM21,27v-6h7c-.1,2.4-.6,4.8-1.5,6.9-1.7-.5-3.6-.8-5.4-.9ZM21,19v-6c1.9,0,3.7-.4,5.4-.9.9,2.2,1.4,4.5,1.5,6.9h-7ZM27.5,9.6c-.9-1.8-2.1-3.5-3.5-5.1,2.5.6,4.8,1.9,6.6,3.5-1,.6-2,1.1-3.1,1.5ZM12.5,9.6c-1.1-.4-2.1-.9-3.1-1.5,1.9-1.7,4.1-2.9,6.6-3.5-1.4,1.5-2.6,3.2-3.5,5.1ZM11.7,11.4c-.9,2.4-1.5,4.9-1.6,7.6h-6c.2-3.6,1.6-6.9,3.9-9.4,1.2.7,2.4,1.4,3.8,1.9ZM10,21c.1,2.7.7,5.2,1.6,7.6-1.3.5-2.6,1.1-3.8,1.9-2.2-2.6-3.6-5.8-3.9-9.4h6ZM12.5,30.4c.9,1.8,2.1,3.5,3.5,5.1-2.5-.6-4.8-1.9-6.6-3.5,1-.6,2-1.1,3.1-1.5ZM27.5,30.4c1.1.4,2.1.9,3.1,1.5-1.9,1.7-4.1,2.9-6.6,3.5,1.4-1.5,2.6-3.2,3.5-5.1ZM28.3,28.6c.9-2.4,1.5-4.9,1.6-7.6h6c-.2,3.6-1.6,6.9-3.9,9.4-1.2-.7-2.4-1.4-3.8-1.9Z"/>
		</svg>';

		$icon_svg = 'data:image/svg+xml;base64,' . base64_encode( $icon_svg );

		add_menu_page(
			'HM',
			'Hreflang',
			get_option( $this->shared->get( 'slug' ) . '_connections_menu_capability' ),
			$this->shared->get( 'slug' ) . '_connections',
			array( $this, 'me_display_menu_connections' ),
			$icon_svg
		);

		$this->screen_id_connections = add_submenu_page(
			$this->shared->get( 'slug' ) . '_connections',
			esc_html__( 'HM - Connections', 'hreflang-manager' ),
			esc_html__( 'Connections', 'hreflang-manager' ),
			get_option( $this->shared->get( 'slug' ) . '_connections_menu_capability' ),
			$this->shared->get( 'slug' ) . '_connections',
			array( $this, 'me_display_menu_connections' )
		);

		$this->screen_id_bulk_import = add_submenu_page(
			$this->shared->get( 'slug' ) . '_connections',
			esc_html__( 'HM - Bulk Import', 'hreflang-manager' ),
			esc_html__( 'Bulk Import', 'hreflang-manager' ),
			get_option( $this->shared->get( 'slug' ) . '_bulk_import_menu_capability' ),
			$this->shared->get( 'slug' ) . '_bulk_import',
			array( $this, 'me_display_menu_bulk_import' )
		);

		$this->screen_id_tools = add_submenu_page(
			$this->shared->get( 'slug' ) . '_connections',
			esc_html__( 'HM - Tools', 'hreflang-manager' ),
			esc_html__( 'Tools', 'hreflang-manager' ),
			get_option( $this->shared->get( 'slug' ) . '_tools_menu_capability' ),
			$this->shared->get( 'slug' ) . '_tools',
			array( $this, 'me_display_menu_tools' )
		);

		$this->screen_id_checker = add_submenu_page(
			$this->shared->get( 'slug' ) . '_connections',
			esc_html__( 'HM - Checker', 'hreflang-manager' ),
			esc_html__( 'Checker', 'hreflang-manager' ),
			get_option( $this->shared->get( 'slug' ) . '_checker_menu_capability' ),
			$this->shared->get( 'slug' ) . '_checker',
			array( $this, 'me_display_menu_checker' )
		);

		$this->screen_id_maintenance = add_submenu_page(
			$this->shared->get( 'slug' ) . '_connections',
			esc_html__( 'HM - Maintenance', 'hreflang-manager' ),
			esc_html__( 'Maintenance', 'hreflang-manager' ),
			get_option( $this->shared->get( 'slug' ) . '_maintenance_menu_capability' ),
			$this->shared->get( 'slug' ) . '_maintenance',
			array( $this, 'me_display_menu_maintenance' )
		);

		$this->screen_id_options = add_submenu_page(
			$this->shared->get( 'slug' ) . '_connections',
			esc_html__( 'HM - Options', 'hreflang-manager' ),
			esc_html__( 'Options', 'hreflang-manager' ),
			'manage_options',
			$this->shared->get( 'slug' ) . '_options',
			array( $this, 'me_display_menu_options' )
		);

		add_submenu_page(
			$this->shared->get( 'slug' ) . '_connections',
			esc_html__( 'Help & Support', 'hreflang-manager' ),
			esc_html__( 'Help & Support', 'hreflang-manager' ) . '<i class="dashicons dashicons-external" style="font-size:12px;vertical-align:-2px;height:10px;"></i>',
			'manage_options',
			'https://daext.com/doc/hreflang-manager-pro/',
		);
	}

	/**
	 * Includes the connections view.
	 *
	 * @return void
	 */
	public function me_display_menu_connections() {
		include_once 'view/connections.php';
	}

	/**
	 * Includes the Bulk Import view.
	 *
	 * @return void
	 */
	public function me_display_menu_bulk_import() {
		include_once 'view/bulk-import.php';
	}

	/**
	 * Includes the Tools view.
	 *
	 * @return void
	 */
	public function me_display_menu_tools() {
		include_once 'view/tools.php';
	}

	/**
	 * Includes the Checker view.
	 *
	 * @return void
	 */
	public function me_display_menu_checker() {
		include_once 'view/checker.php';
	}

	/**
	 * Includes the Maintenance view.
	 *
	 * @return void
	 */
	public function me_display_menu_maintenance() {
		include_once 'view/maintenance.php';
	}

	/**
	 * Includes the Options view.
	 *
	 * @return void
	 */
	public function me_display_menu_options() {
		include_once 'view/options.php';
	}

	/**
	 * Deletes a connection by using the permalink of the trashed post. Note that this operation is performed only if
	 *  the 'Auto Delete' option is enabled.
	 *
	 * @param int $post_id The id of the trashed post.
	 *
	 * @return void
	 */
	public function delete_post_connection( $post_id ) {

		if ( 1 === intval( get_option( $this->shared->get( 'slug' ) . '_auto_delete' ), 10 ) ) {

			$permalink = get_the_permalink( $post_id, false );

			global $wpdb;

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->query(
				$wpdb->prepare( "DELETE FROM {$wpdb->prefix}da_hm_connect WHERE url_to_connect = %s", $permalink )
			);

		}
	}

	/**
	 * Plugin deactivation.
	 *
	 * @return void
	 */
	public static function dc_deactivate() {
		wp_clear_scheduled_hook( 'da_hm_cron_hook' );
		wp_clear_scheduled_hook( 'da_hm_cron_hook_2' );
	}

	/**
	 * Perform a manual license verification when the user click the provided link to verify the license.
	 *
	 * @return void
	 */
	public function manual_license_verification() {

		if ( isset( $_GET['da_hm_verify_license'] ) ) {

			$verify_license_nonce = isset( $_GET['da_hm_verify_license_nonce'] ) ? sanitize_key( $_GET['da_hm_verify_license_nonce'] ) : null;

			if ( wp_verify_nonce( $verify_license_nonce, 'da_hm_verify_license' ) ) {

				require_once $this->shared->get( 'dir' ) . 'vendor/autoload.php';
				$plugin_update_checker = new PluginUpdateChecker(DAHM_PLUGIN_UPDATE_CHECKER_SETTINGS);

				// Delete the transient used to store the plugin info previously retrieved from the remote server.
				$plugin_update_checker->delete_transient();

				// Fetch the plugin information from the remote server and saved it in the transient.
				$plugin_update_checker->fetch_remote_plugin_info();

				if ( $plugin_update_checker->is_valid_license() ) {
					$this->shared->save_dismissible_notice(
						__( 'Your license is active, and all features are now enabled. Thank you!', 'hreflang-manager' ),
						'updated'
					);
				} else {
					$this->shared->save_dismissible_notice(
						__( 'The license key provided is either invalid or could not be verified at this time. Please check your key and try again, or contact support if the issue persists.', 'hreflang-manager' ),
						'error'
					);
				}

			}

		}

	}

}