<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package hreflang-manager-lite
 */

/**
 * This class should be used to work with the administrative side of WordPress.
 */
class Daexthrmal_Admin {

	/**
	 * The instance of this class.
	 *
	 * @var null
	 */
	protected static $instance = null;

	/**
	 * The instance of the shared class.
	 *
	 * @var Daexthrmal_Shared|null
	 */
	private $shared = null;

	/**
	 * The screen id of the "Connections" menu.
	 *
	 * @var null
	 */
	private $screen_id_connections = null;

	/**
	 * The screen id of the "Tools" menu.
	 *
	 * @var null
	 */
	private $screen_id_tools = null;

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
		$this->shared = Daexthrmal_Shared::get_instance();

		// Load admin stylesheets and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the admin menu.
		add_action( 'admin_menu', array( $this, 'me_add_admin_menu' ) );

		// This hook is triggered during the creation of a new blog.
		add_action( 'wpmu_new_blog', array( $this, 'new_blog_create_options_and_tables' ), 10, 6 );

		// This hook is triggered during the deletion of a blog.
		add_action( 'delete_blog', array( $this, 'delete_blog_delete_options_and_tables' ), 10, 1 );

		// Fires before a post is sent to the trash.
		add_action( 'wp_trash_post', array( $this, 'delete_post_connection' ) );

		// Require and instantiate the related classes used to handle the menus.
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
	 * If we are in one of the plugin back-end menus require and instantiate the related class used to handle the menu.
	 *
	 * @return void
	 */
	public function handle_menus() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce non-necessary for menu selection.
		$page_query_param = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : null;

		// Require and instantiate the class used to handle the current menu.
		if ( null !== $page_query_param ) {

			$config = array(
				'admin_toolbar' => array(
					'items'      => array(
						array(
							'link_text' => __( 'Connections', 'hreflang-manager-lite' ),
							'link_url'  => admin_url( 'admin.php?page=daexthrmal_connections' ),
							'icon'      => 'list',
							'menu_slug' => 'daexthrmal-connection',
						),
						array(
							'link_text' => __( 'Tools', 'hreflang-manager-lite' ),
							'link_url'  => admin_url( 'admin.php?page=daexthrmal_tools' ),
							'icon'      => 'tool-02',
							'menu_slug' => 'daexthrmal-tool',
						),
						array(
							'link_text' => __( 'Options', 'hreflang-manager-lite' ),
							'link_url'  => admin_url( 'admin.php?page=daexthrmal_options' ),
							'icon'      => 'settings-01',
							'menu_slug' => 'daexthrmal-options',
						),
					),
					'more_items' => array(
						array(
							'link_text' => __( 'Bulk Import', 'hreflang-manager-lite' ),
							'link_url'  => 'https://daext.com/hreflang-manager/',
							'pro_badge' => true,
						),
						array(
							'link_text' => __( 'Sync', 'hreflang-manager-lite' ),
							'link_url'  => 'https://daext.com/hreflang-manager/',
							'pro_badge' => true,
						),
						array(
							'link_text' => __( 'Editor Integration', 'hreflang-manager-lite' ),
							'link_url'  => 'https://daext.com/hreflang-manager/',
							'pro_badge' => true,
						),
						array(
							'link_text' => __( 'Import & Export', 'hreflang-manager-lite' ),
							'link_url'  => 'https://daext.com/hreflang-manager/',
							'pro_badge' => true,
						),
						array(
							'link_text' => __( 'Maintenance Tasks', 'hreflang-manager-lite' ),
							'link_url'  => 'https://daext.com/hreflang-manager/',
							'pro_badge' => true,
						),
						array(
							'link_text' => __( 'Configure Capabilities', 'hreflang-manager-lite' ),
							'link_url'  => 'https://daext.com/hreflang-manager/',
							'pro_badge' => true,
						),
					),
				),
			);

			// The parent class.
			require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/class-daexthrmal-menu-elements.php';

			// Use the correct child class based on the page query parameter.
			if ( 'daexthrmal_connections' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-daexthrmal-connections-menu-elements.php';
				$this->menu_elements = new Daexthrmal_Connections_Menu_Elements( $this->shared, $page_query_param, $config );
			}
			if ( 'daexthrmal_tools' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-daexthrmal-tools-menu-elements.php';
				$this->menu_elements = new Daexthrmal_Tools_Menu_Elements( $this->shared, $page_query_param, $config );
			}
			if ( 'daexthrmal_options' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-daexthrmal-options-menu-elements.php';
				$this->menu_elements = new Daexthrmal_Options_Menu_Elements( $this->shared, $page_query_param, $config );
			}
		}

	}

	/**
	 * Enqueue admin-specific styles.
	 *
	 * @return void
	 */
	public function enqueue_admin_styles() {

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

		}

		// Menu Tools.
		if ( $screen->id === $this->screen_id_tools ) {

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array(), $this->shared->get( 'ver' ) );

		}

		// Menu Options.
		if ( $screen->id === $this->screen_id_options ) {

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array( 'wp-components' ), $this->shared->get( 'ver' ) );

		}
	}

	/**
	 * Enqueue admin-specific JavaScript.
	 *
	 * @return void
	 */
	public function enqueue_admin_scripts() {

		$screen = get_current_screen();

		// General.
		wp_enqueue_script( $this->shared->get( 'slug' ) . '-general', $this->shared->get( 'url' ) . 'admin/assets/js/general.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

		// Menu connections.
		if ( $screen->id === $this->screen_id_connections ) {

			$wp_localize_script_data = array(
				'chooseAnOptionText' => esc_html__( 'Choose an Option ...', 'hreflang-manager-lite' ),
			);

			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/js/select2.min.js',
				array( 'jquery' ),
				$this->shared->get( 'ver' ),
				true
			);

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-menu/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu-connections', $this->shared->get( 'url' ) . 'admin/assets/js/menu-connections.js', array( 'jquery', $this->shared->get( 'slug' ) . '-select2' ), $this->shared->get( 'ver' ), true );
			wp_localize_script( $this->shared->get( 'slug' ) . '-menu-connections', 'objectL10n', $wp_localize_script_data );

		}

		// Menu tools.
		if ( $screen->id === $this->screen_id_tools ) {

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-menu/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

		}

		// Menu Options.
		if ( $screen->id === $this->screen_id_options ) {

			// Store the JavaScript parameters in the window.DAEXTDAEXTHRMAL_PARAMETERS object.
			$initialization_script  = 'window.DAEXTDAEXTHRMAL_PARAMETERS = {';
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
	}

	/**
	 * Plugin activation.
	 *
	 * @param bool $networkwide True if the plugin is being activated network-wide.
	 *
	 * @return void
	 */
	public static function ac_activate( $networkwide ) {

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

				}

				// Switch to the current blog.
				switch_to_blog( $current_blog );

			} else {

				// If this is not a "Network Activation" create options and tables only for the current blog.
				self::ac_initialize_options();
				self::ac_create_database_tables();

			}
		} else {

			// If this is not a multisite installation create options and tables only for the current blog.
			self::ac_initialize_options();
			self::ac_create_database_tables();

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

		if ( intval( get_option( 'daexthrmal_options_version' ), 10 ) < 1 ) {

			// Assign an instance of Daexthrmal_Shared.
			$shared = Daexthrmal_Shared::get_instance();

			foreach ( $shared->get( 'options' ) as $key => $value ) {
				add_option( $key, $value );
			}

			// Update options version.
			update_option( 'daexthrmal_options_version', '1' );

		}
	}

	/**
	 * Create the plugin database tables.
	 *
	 * @return void
	 */
	public static function ac_create_database_tables() {

		global $wpdb;

		// Get the database character collate that will be appended at the end of each query.
		$charset_collate = $wpdb->get_charset_collate();

		// check database version and create the database.
		if ( intval( get_option( 'daexthrmal_database_version' ), 10 ) < 1 ) {

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			// create *prefix*_statistic.
			$table_name = $wpdb->prefix . 'daexthrmal_connection';
			$sql        = "CREATE TABLE $table_name (
                connection_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                url_to_connect TEXT DEFAULT '' NOT NULL,
                url1 TEXT DEFAULT '' NOT NULL,
                language1 VARCHAR(9) DEFAULT '' NOT NULL,
                script1 VARCHAR(4) DEFAULT '' NOT NULL,
                locale1 VARCHAR(2) DEFAULT '' NOT NULL,
                url2 TEXT DEFAULT '' NOT NULL,
                language2 VARCHAR(9) DEFAULT '' NOT NULL,
                script2 VARCHAR(4) DEFAULT '' NOT NULL,
                locale2 VARCHAR(2) DEFAULT '' NOT NULL,
                url3 TEXT DEFAULT '' NOT NULL,
                language3 VARCHAR(9) DEFAULT '' NOT NULL,
                script3 VARCHAR(4) DEFAULT '' NOT NULL,
                locale3 VARCHAR(2) DEFAULT '' NOT NULL,
                url4 TEXT DEFAULT '' NOT NULL,
                language4 VARCHAR(9) DEFAULT '' NOT NULL,
                script4 VARCHAR(4) DEFAULT '' NOT NULL,
                locale4 VARCHAR(2) DEFAULT '' NOT NULL,
                url5 TEXT DEFAULT '' NOT NULL,
                language5 VARCHAR(9) DEFAULT '' NOT NULL,
                script5 VARCHAR(4) DEFAULT '' NOT NULL,
                locale5 VARCHAR(2) DEFAULT '' NOT NULL,
                url6 TEXT DEFAULT '' NOT NULL,
                language6 VARCHAR(9) DEFAULT '' NOT NULL,
                script6 VARCHAR(4) DEFAULT '' NOT NULL,
                locale6 VARCHAR(2) DEFAULT '' NOT NULL,
                url7 TEXT DEFAULT '' NOT NULL,
                language7 VARCHAR(9) DEFAULT '' NOT NULL,
                script7 VARCHAR(4) DEFAULT '' NOT NULL,
                locale7 VARCHAR(2) DEFAULT '' NOT NULL,
                url8 TEXT DEFAULT '' NOT NULL,
                language8 VARCHAR(9) DEFAULT '' NOT NULL,
                script8 VARCHAR(4) DEFAULT '' NOT NULL,
                locale8 VARCHAR(2) DEFAULT '' NOT NULL,
                url9 TEXT DEFAULT '' NOT NULL,
                language9 VARCHAR(9) DEFAULT '' NOT NULL,
                script9 VARCHAR(4) DEFAULT '' NOT NULL,
                locale9 VARCHAR(2) DEFAULT '' NOT NULL,
                url10 TEXT DEFAULT '' NOT NULL,
                language10 VARCHAR(9) DEFAULT '' NOT NULL,
                script10 VARCHAR(4) DEFAULT '' NOT NULL,
                locale10 VARCHAR(2) DEFAULT '' NOT NULL
            ) $charset_collate";
			dbDelta( $sql );

			// Update database version.
			update_option( 'daexthrmal_database_version', '1' );

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

		// Assign an instance of Daexthrmal_Shared.
		$shared = Daexthrmal_Shared::get_instance();

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
		$wpdb->query( "DROP TABLE {$wpdb->prefix}daexthrmal_connection" );
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
		        stroke-width: 0;
		      }
		    </style>
		  </defs>
		  <path class="cls-1" d="M38,20c0-9.4-7.3-17.2-16.5-17.9-.5,0-1,0-1.5,0s-1,0-1.5,0C9.3,2.8,2,10.6,2,20s7.3,17.2,16.5,17.9c.5,0,1,0,1.5,0s1,0,1.5,0c9.2-.8,16.5-8.5,16.5-17.9ZM30,19c-.1-2.7-.7-5.2-1.6-7.6,1.3-.5,2.6-1.1,3.8-1.9,2.2,2.6,3.6,5.8,3.9,9.4h-6ZM21,4.4c1.8,1.7,3.4,3.6,4.6,5.8-1.5.4-3,.7-4.6.7v-6.6ZM19,11c-1.6,0-3.1-.3-4.6-.7,1.2-2.2,2.7-4.2,4.6-5.8v6.6ZM19,13v6h-7c.1-2.4.6-4.8,1.5-6.9,1.7.5,3.6.8,5.4.9ZM19,21v6c-1.9,0-3.7.4-5.4.9-.9-2.2-1.4-4.5-1.5-6.9h7ZM19,29v6.6c-1.8-1.7-3.4-3.6-4.6-5.8,1.5-.4,3-.7,4.6-.7ZM21,29c1.6,0,3.1.3,4.6.7-1.2,2.2-2.7,4.2-4.6,5.8v-6.6ZM21,27v-6h7c-.1,2.4-.6,4.8-1.5,6.9-1.7-.5-3.6-.8-5.4-.9ZM21,19v-6c1.9,0,3.7-.4,5.4-.9.9,2.2,1.4,4.5,1.5,6.9h-7ZM27.5,9.6c-.9-1.8-2.1-3.5-3.5-5.1,2.5.6,4.8,1.9,6.6,3.5-1,.6-2,1.1-3.1,1.5ZM12.5,9.6c-1.1-.4-2.1-.9-3.1-1.5,1.9-1.7,4.1-2.9,6.6-3.5-1.4,1.5-2.6,3.2-3.5,5.1ZM11.7,11.4c-.9,2.4-1.5,4.9-1.6,7.6h-6c.2-3.6,1.6-6.9,3.9-9.4,1.2.7,2.4,1.4,3.8,1.9ZM10,21c.1,2.7.7,5.2,1.6,7.6-1.3.5-2.6,1.1-3.8,1.9-2.2-2.6-3.6-5.8-3.9-9.4h6ZM12.5,30.4c.9,1.8,2.1,3.5,3.5,5.1-2.5-.6-4.8-1.9-6.6-3.5,1-.6,2-1.1,3.1-1.5ZM27.5,30.4c1.1.4,2.1.9,3.1,1.5-1.9,1.7-4.1,2.9-6.6,3.5,1.4-1.5,2.6-3.2,3.5-5.1ZM28.3,28.6c.9-2.4,1.5-4.9,1.6-7.6h6c-.2,3.6-1.6,6.9-3.9,9.4-1.2-.7-2.4-1.4-3.8-1.9Z"/>
		</svg>';

		$icon_svg = 'data:image/svg+xml;base64,' . base64_encode( $icon_svg );

		add_menu_page(
			'HM',
			'Hreflang',
			'manage_options',
			$this->shared->get( 'slug' ) . '_connections',
			array( $this, 'me_display_menu_connections' ),
			$icon_svg
		);

		$this->screen_id_connections = add_submenu_page(
			$this->shared->get( 'slug' ) . '_connections',
			esc_html__( 'HM - Connections', 'hreflang-manager-lite' ),
			esc_html__( 'Connections', 'hreflang-manager-lite' ),
			'manage_options',
			$this->shared->get( 'slug' ) . '_connections',
			array( $this, 'me_display_menu_connections' )
		);

		$this->screen_id_tools = add_submenu_page(
			$this->shared->get( 'slug' ) . '_connections',
			esc_html__( 'HM - Tools', 'hreflang-manager-lite' ),
			esc_html__( 'Tools', 'hreflang-manager-lite' ),
			'manage_options',
			$this->shared->get( 'slug' ) . '_tools',
			array( $this, 'me_display_menu_tools' )
		);

		$this->screen_id_options = add_submenu_page(
			$this->shared->get( 'slug' ) . '_connections',
			esc_html__( 'HM - Options', 'hreflang-manager-lite' ),
			esc_html__( 'Options', 'hreflang-manager-lite' ),
			'manage_options',
			$this->shared->get( 'slug' ) . '_options',
			array( $this, 'me_display_menu_options' )
		);

		add_submenu_page(
			$this->shared->get( 'slug' ) . '_connections',
			esc_html__( 'Help & Support', 'hreflang-manager-lite' ),
			esc_html__( 'Help & Support', 'hreflang-manager-lite' ) . '<i class="dashicons dashicons-external" style="font-size:12px;vertical-align:-2px;height:10px;"></i>',
			'manage_options',
			'https://daext.com/doc/hreflang-manager/',
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
	 * Includes the Tools view.
	 *
	 * @return void
	 */
	public function me_display_menu_tools() {
		include_once 'view/tools.php';
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
				$wpdb->prepare( "DELETE FROM {$wpdb->prefix}daexthrmal_connection WHERE url_to_connect = %s", $permalink )
			);

		}
	}

	/**
	 * Plugin deactivation.
	 *
	 * @return void
	 */
	public static function dc_deactivate() {
		// Do nothing.
	}
}
