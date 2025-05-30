<?php
/**
 * Here the REST API endpoint of the plugin are registered.
 *
 * @package hreflang-manager
 */

use Daextteam\PluginUpdateChecker\PluginUpdateChecker;

/**
 * This class should be used to work with the REST API endpoints of the plugin.
 */
class Dahm_Rest {

	/**
	 * The singleton instance of the class.
	 *
	 * @var null
	 */
	protected static $instance = null;

	/**
	 * An instance of the shared class.
	 *
	 * @var Daextrevop_Shared|null
	 */
	private $shared = null;

	/**
	 * Constructor.
	 */
	private function __construct() {

		// Assign an instance of the shared class.
		$this->shared = Dahm_Shared::get_instance();

		/**
		 * Add custom routes to the Rest API.
		 */
		add_action( 'rest_api_init', array( $this, 'rest_api_register_route' ) );
	}

	/**
	 * Create a singleton instance of the class.
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
	 * Add custom routes to the Rest API.
	 *
	 * @return void
	 */
	public function rest_api_register_route() {

		// Add the GET 'daext-hreflang-manager/v1/post' endpoint to the Rest API.
		register_rest_route(
			'daext-hreflang-manager/v1',
			'/post/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'rest_api_daext_hreflang_manager_read_connections_callback' ),
				'permission_callback' => array( $this, 'rest_api_daext_hreflang_manager_read_connections_callback_permission_check' ),
			)
		);

		// Add the POST 'daext-hreflang-manager/v1/post' endpoint to the Rest API.
		register_rest_route(
			'daext-hreflang-manager/v1',
			'/post/',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_api_daext_hreflang_manager_post_connection_callback' ),
				'permission_callback' => array( $this, 'rest_api_daext_hreflang_manager_post_connection_callback_permission_check' ),
			)
		);

		// Add the GET 'daext-hreflang-manager/v1/options' endpoint to the Rest API.
		register_rest_route(
			'daext-hreflang-manager/v1',
			'/read-options/',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_api_daext_hreflang_manager_read_options_callback' ),
				'permission_callback' => array( $this, 'rest_api_daext_hreflang_manager_read_options_callback_permission_check' ),
			)
		);

		// Add the POST 'daext-hreflang-manager/v1/options' endpoint to the Rest API.
		register_rest_route(
			'daext-hreflang-manager/v1',
			'/options',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_api_daext_hreflang_manager_update_options_callback' ),
				'permission_callback' => array( $this, 'rest_api_daext_hreflang_manager_update_options_callback_permission_check' ),

			)
		);

		// Create this endpoint only if Sync -> Status is equal to "Enabled" and Sync -> Role is equal to "Master".
		$sync_status = intval( get_option( $this->shared->get( 'slug' ) . '_sync_status' ), 10 );
		$sync_role   = intval( get_option( $this->shared->get( 'slug' ) . '_sync_role' ), 10 );
		if ( 1 === $sync_status && 0 === $sync_role ) {

			// Add the GET 'daext-hreflang-manager/v1/sync' endpoint to the Rest API.
			register_rest_route(
				'daext-hreflang-manager/v1',
				'/sync/',
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'rest_api_daext_hreflang_manager_read_all_connections_callback' ),
					'permission_callback' => '__return_true',
				)
			);

		}

		// Add the POST 'daext-hreflang-manager/v1/hreflang-checker-issues/' endpoint to the Rest API.
		register_rest_route(
			'daext-hreflang-manager/v1',
			'/hreflang-checker-issues/',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_api_daext_hreflang_manager_read_hreflang_checker_issues_callback' ),
				'permission_callback' => array( $this, 'rest_api_daext_hreflang_manager_read_hreflang_checker_issues_callback_permission_check' ),
			)
		);

	}

	/**
	 * Callback for the GET 'daext-hreflang-manager/v1/options' endpoint of the Rest API.
	 *
	 * @param array $data Data received from the request.
	 *
	 * @return false|WP_REST_Response
	 */
	public function rest_api_daext_hreflang_manager_read_connections_callback( $data ) {

		// Generate the response.

		$url_to_connect = $this->shared->get_permalink( $data['id'], true );

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}da_hm_connect WHERE url_to_connect = %s", $url_to_connect ),
			ARRAY_A
		);

		if ( $wpdb->num_rows > 0 ) {

			// Decode the connection data.
			$row['url']      = json_decode( $row['url'] );
			$row['language'] = json_decode( $row['language'] );
			$row['script']   = json_decode( $row['script'] );
			$row['locale']   = json_decode( $row['locale'] );

			// Convert the $row in the format used in the JavaScript part of the sidebar.
			for ( $i = 1;$i <= 100;$i++ ) {
				$row[ 'url' . $i ]      = $row['url']->{$i};
				$row[ 'language' . $i ] = $row['language']->{$i};
				$row[ 'script' . $i ]   = $row['script']->{$i};
				$row[ 'locale' . $i ]   = $row['locale']->{$i};
			}

			// Unset the properties not used in the JavaScript part of the sidebar.
			unset( $row->url );
			unset( $row->language );
			unset( $row->script );
			unset( $row->locale );

			// Prepare the response.
			$response = new WP_REST_Response( $row );

		} else {

			return false;
		}

		return $response;
	}

	/**
	 * Check the user capability.
	 *
	 * @return true|WP_Error
	 */
	public function rest_api_daext_hreflang_manager_read_connections_callback_permission_check() {

		// Check the capability.
		if ( ! current_user_can( get_option( $this->shared->get( 'slug' ) . '_editor_sidebar_capability' ) ) ) {
			return new WP_Error(
				'rest_read_error',
				'Sorry, you are not allowed to view the Hreflang Manager options.',
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Callback for the POST 'daext-hreflang-manager/v1/post/' endpoint of the Rest API.
	 *
	 *  This method is in the following contexts:
	 *  - To save the connection when the "Update" button of the Gutenberg editor is clicked.
	 *
	 * @param array $data Data received from the request.
	 *
	 * @return void|WP_REST_Response
	 */
	public function rest_api_daext_hreflang_manager_post_connection_callback( $data ) {

		$data    = json_decode( $data->get_body() );
		$post_id = $data->post_id;
		$data    = $data->connection_data;

		// Init vars.
		$url      = array();
		$language = array();
		$script   = array();
		$locale   = array();

		// Initialize the variables that include the URLs, the languages, the script and the locale.
		for ( $i = 1;$i <= 100;$i++ ) {

			if ( isset( $data->{'url' . $i} ) && strlen( trim( $data->{'url' . $i} ) ) > 0 ) {
				$url[ $i ]        = esc_url_raw( $data->{'url' . $i} );
				$at_least_one_url = true;
			} else {
				$url[ $i ] = '';
			}

			if ( isset( $data->{'language' . $i} ) ) {
				$language[ $i ] = sanitize_text_field( $data->{'language' . $i} );
			} else {
				$language[ $i ] = get_option( $this->shared->get( 'slug' ) . '_default_language_' . $i );
			}

			if ( isset( $data->{'script' . $i} ) ) {
				$script[ $i ] = sanitize_text_field( $data->{'script' . $i} );
			} else {
				$script[ $i ] = get_option( $this->shared->get( 'slug' ) . '_default_script_' . $i );
			}

			if ( isset( $data->{'locale' . $i} ) ) {
				$locale[ $i ] = sanitize_text_field( $data->{'locale' . $i} );
			} else {
				$locale[ $i ] = get_option( $this->shared->get( 'slug' ) . '_default_locale_' . $i );
			}
		}

		// json encode for the serialized field of the database.
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
		$permalink = $this->shared->get_permalink( $post_id, true );

		// Look for $permalink in the url_to_connect field of the da_hm_connect database table.
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$permalink_connections = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}da_hm_connect WHERE url_to_connect = %s", $permalink )
		);

		if ( null !== $permalink_connections ) {

			// Update an existing connection.

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$query_result = $wpdb->query(
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
			$query_result = $wpdb->query(
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

		// Generate the response.
		$response = new WP_REST_Response( $data );

		return $response;
	}

	/**
	 * Check the user capability.
	 *
	 * @return true|WP_Error
	 */
	public function rest_api_daext_hreflang_manager_post_connection_callback_permission_check() {

		// Check the capability.
		if ( ! current_user_can( get_option( $this->shared->get( 'slug' ) . '_editor_sidebar_capability' ) ) ) {
			return new WP_Error(
				'rest_read_error',
				'Sorry, you are not allowed to add a connection.',
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Callback for the GET 'daext-hreflang-manager/v1/options' endpoint of the Rest API.
	 *
	 * @return WP_REST_Response
	 */
	public function rest_api_daext_hreflang_manager_read_options_callback() {

		// Generate the response.
		$response = array();
		foreach ( $this->shared->get( 'options' ) as $key => $value ) {
			$response[ $key ] = get_option( $key );
		}

		// Prepare the response.
		$response = new WP_REST_Response( $response );

		return $response;
	}

	/**
	 * Check the user capability.
	 *
	 * @return true|WP_Error
	 */
	public function rest_api_daext_hreflang_manager_read_options_callback_permission_check() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_read_error',
				'Sorry, you are not allowed to read the Hreflang Manager options.',
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Callback for the GET 'daext-hreflang-manager/v1/sync' endpoint of the Rest API.
	 *
	 * @return false|WP_REST_Response
	 */
	public function rest_api_daext_hreflang_manager_read_all_connections_callback() {

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$connection_a = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}da_hm_connect",
			ARRAY_A
		);

		if ( count( $connection_a ) > 0 ) {

			// Prepare the response.
			$response = new WP_REST_Response( $connection_a );

		} else {

			$response = false;

		}

		return $response;
	}

	/**
	 * Callback for the POST 'daext-hreflang-manager/v1/options' endpoint of the Rest API.
	 *
	 * This method is in the following contexts:
	 *
	 *  - To update the plugin options in the "Options" menu.
	 *
	 * @param object $request The request data.
	 *
	 * @return WP_REST_Response
	 */
	public function rest_api_daext_hreflang_manager_update_options_callback( $request ) {

		// Get and sanitize data --------------------------------------------------------------------------------------.

		$options = array();

		// Get and sanitize data --------------------------------------------------------------------------------------.

		// Licensing --------------------------------------------------------------------------------------------------.
		$options['da_hm_license_provider']      = $request->get_param( 'da_hm_license_provider' ) !== null ? sanitize_key( $request->get_param( 'da_hm_license_provider' ) ) : null;
		$options['da_hm_license_key']      = $request->get_param( 'da_hm_license_key' ) !== null ? sanitize_key( $request->get_param( 'da_hm_license_key' ) ) : null;

		// General ----------------------------------------------------------------------------------------------------.
		$options['da_hm_detect_url_mode']          = $request->get_param( 'da_hm_detect_url_mode' ) !== null ? sanitize_key( $request->get_param( 'da_hm_detect_url_mode' ) ) : null;
		$options['da_hm_show_log_callback']        = $request->get_param( 'da_hm_show_log_callback' ) !== null ? intval( $request->get_param( 'da_hm_show_log_callback' ), 10 ) : null;
		$options['da_hm_https']                    = $request->get_param( 'da_hm_https' ) !== null ? intval( $request->get_param( 'da_hm_https' ), 10 ) : null;
		$options['da_hm_auto_trailing_slash']      = $request->get_param( 'da_hm_auto_trailing_slash' ) !== null ? intval( $request->get_param( 'da_hm_auto_trailing_slash' ), 10 ) : null;
		$options['da_hm_auto_delete']              = $request->get_param( 'da_hm_auto_delete' ) !== null ? intval( $request->get_param( 'da_hm_auto_delete' ), 10 ) : null;
		$options['da_hm_auto_alternate_pages']     = $request->get_param( 'da_hm_auto_alternate_pages' ) !== null ? intval( $request->get_param( 'da_hm_auto_alternate_pages' ), 10 ) : null;
		$options['da_hm_sample_future_permalink']  = $request->get_param( 'da_hm_sample_future_permalink' ) !== null ? intval( $request->get_param( 'da_hm_sample_future_permalink' ), 10 ) : null;
		$options['da_hm_show_log']                 = $request->get_param( 'da_hm_show_log' ) !== null ? intval( $request->get_param( 'da_hm_show_log' ), 10 ) : null;
		$options['da_hm_connections_in_menu']      = $request->get_param( 'da_hm_connections_in_menu' ) !== null ? intval( $request->get_param( 'da_hm_connections_in_menu' ), 10 ) : null;
		$options['da_hm_meta_box_post_types']      = $request->get_param( 'da_hm_meta_box_post_types' ) !== null && is_array( $request->get_param( 'da_hm_meta_box_post_types' ) ) ? array_map( 'sanitize_text_field', $request->get_param( 'da_hm_meta_box_post_types' ) ) : null;
		$options['da_hm_set_max_execution_time']   = $request->get_param( 'da_hm_set_max_execution_time' ) !== null ? intval( $request->get_param( 'da_hm_set_max_execution_time' ), 10 ) : null;
		$options['da_hm_max_execution_time_value'] = $request->get_param( 'da_hm_max_execution_time_value' ) !== null ? intval( $request->get_param( 'da_hm_max_execution_time_value' ), 10 ) : null;
		$options['da_hm_set_memory_limit']         = $request->get_param( 'da_hm_set_memory_limit' ) !== null ? intval( $request->get_param( 'da_hm_set_memory_limit' ), 10 ) : null;
		$options['da_hm_memory_limit_value']       = $request->get_param( 'da_hm_memory_limit_value' ) !== null ? intval( $request->get_param( 'da_hm_memory_limit_value' ), 10 ) : null;

		// Sync -------------------------------------------------------------------------------------------------------.
		$options['da_hm_sync_status']               = $request->get_param( 'da_hm_sync_status' ) !== null ? intval( $request->get_param( 'da_hm_sync_status' ), 10 ) : null;
		$options['da_hm_sync_role']                 = $request->get_param( 'da_hm_sync_role' ) !== null ? intval( $request->get_param( 'da_hm_sync_role' ), 10 ) : null;
		$options['da_hm_sync_frequency']            = $request->get_param( 'da_hm_sync_frequency' ) !== null ? intval( $request->get_param( 'da_hm_sync_frequency' ), 10 ) : null;
		$options['da_hm_sync_master_rest_endpoint'] = $request->get_param( 'da_hm_sync_master_rest_endpoint' ) !== null ? sanitize_text_field( $request->get_param( 'da_hm_sync_master_rest_endpoint' ) ) : null;
		$options['da_hm_sync_mode']                 = $request->get_param( 'da_hm_sync_mode' ) !== null ? intval( $request->get_param( 'da_hm_sync_mode' ), 10 ) : null;
		$options['da_hm_sync_language']             = $request->get_param( 'da_hm_sync_language' ) !== null ? sanitize_text_field( $request->get_param( 'da_hm_sync_language' ) ) : null;
		$options['da_hm_sync_script']               = $request->get_param( 'da_hm_sync_script' ) !== null ? sanitize_text_field( $request->get_param( 'da_hm_sync_script' ) ) : null;
		$options['da_hm_sync_locale']               = $request->get_param( 'da_hm_sync_locale' ) !== null ? sanitize_text_field( $request->get_param( 'da_hm_sync_locale' ) ) : null;
		$options['da_hm_sync_delete_target']        = $request->get_param( 'da_hm_sync_delete_target' ) !== null ? intval( $request->get_param( 'da_hm_sync_delete_target' ), 10 ) : null;

		// Import.
		$options['da_hm_import_mode']     = $request->get_param( 'da_hm_import_mode' ) !== null ? sanitize_text_field( $request->get_param( 'da_hm_import_mode' ) ) : null;
		$options['da_hm_import_language'] = $request->get_param( 'da_hm_import_language' ) !== null ? sanitize_text_field( $request->get_param( 'da_hm_import_language' ) ) : null;
		$options['da_hm_import_script']   = $request->get_param( 'da_hm_import_script' ) !== null ? sanitize_text_field( $request->get_param( 'da_hm_import_script' ) ) : null;
		$options['da_hm_import_locale']   = $request->get_param( 'da_hm_import_locale' ) !== null ? sanitize_text_field( $request->get_param( 'da_hm_import_locale' ) ) : null;

		// Capabilities.
		$options['da_hm_meta_box_capability']         = $request->get_param( 'da_hm_meta_box_capability' ) !== null ? sanitize_key( $request->get_param( 'da_hm_meta_box_capability' ) ) : null;
		$options['da_hm_editor_sidebar_capability']   = $request->get_param( 'da_hm_editor_sidebar_capability' ) !== null ? sanitize_key( $request->get_param( 'da_hm_editor_sidebar_capability' ) ) : null;
		$options['da_hm_connections_menu_capability'] = $request->get_param( 'da_hm_connections_menu_capability' ) !== null ? sanitize_key( $request->get_param( 'da_hm_connections_menu_capability' ) ) : null;
		$options['da_hm_bulk_import_menu_capability']      = $request->get_param( 'da_hm_bulk_import_menu_capability' ) !== null ? sanitize_key( $request->get_param( 'da_hm_bulk_import_menu_capability' ) ) : null;
		$options['da_hm_tools_menu_capability']       = $request->get_param( 'da_hm_tools_menu_capability' ) !== null ? sanitize_key( $request->get_param( 'da_hm_tools_menu_capability' ) ) : null;
		$options['da_hm_checker_menu_capability']       = $request->get_param( 'da_hm_checker_menu_capability' ) !== null ? sanitize_key( $request->get_param( 'da_hm_checker_menu_capability' ) ) : null;
		$options['da_hm_maintenance_menu_capability'] = $request->get_param( 'da_hm_maintenance_menu_capability' ) !== null ? sanitize_key( $request->get_param( 'da_hm_maintenance_menu_capability' ) ) : null;

		// Defaults ---------------------------------------------------------------------------------------------------.
		for ( $i = 1; $i <= 100; $i++ ) {
			$options[ 'da_hm_default_language_' . $i ] = $request->get_param( 'da_hm_default_language_' . $i ) !== null ? sanitize_key( $request->get_param( 'da_hm_default_language_' . $i ) ) : null;
			$options[ 'da_hm_default_script_' . $i ]   = $request->get_param( 'da_hm_default_script_' . $i ) !== null ? sanitize_text_field( $request->get_param( 'da_hm_default_script_' . $i ) ) : null;
			$options[ 'da_hm_default_locale_' . $i ]   = $request->get_param( 'da_hm_default_locale_' . $i ) !== null ? sanitize_key( $request->get_param( 'da_hm_default_locale_' . $i ) ) : null;
		}

		// Checker ----------------------------------------------------------------------------------------------------.
		$options['da_hm_checker_checks_per_iteration'] = $request->get_param( 'da_hm_checker_checks_per_iteration' ) !== null ? intval( $request->get_param( 'da_hm_checker_checks_per_iteration' ), 10 ) : null;
		$options['da_hm_checker_cron_schedule_interval'] = $request->get_param( 'da_hm_checker_cron_schedule_interval' ) !== null ? intval( $request->get_param( 'da_hm_checker_cron_schedule_interval' ), 10 ) : null;
		$options['da_hm_checker_request_timeout'] = $request->get_param( 'da_hm_checker_request_timeout' ) !== null ? intval( $request->get_param( 'da_hm_checker_request_timeout' ), 10 ) : null;

		// Hreflang Checks.
		$options[ 'da_hm_checker_invalid_http_response'] = $request->get_param(  'da_hm_checker_invalid_http_response' ) !== null ? intval( $request->get_param(  'da_hm_checker_invalid_http_response' ), 10 ) : null;
		$options[ 'da_hm_checker_duplicate_hreflang_entries'] = $request->get_param(  'da_hm_checker_duplicate_hreflang_entries' ) !== null ? intval( $request->get_param(  'da_hm_checker_duplicate_hreflang_entries' ), 10 ) : null;
		$options[ 'da_hm_checker_missing_self_referencing_hreflang'] = $request->get_param(  'da_hm_checker_missing_self_referencing_hreflang' ) !== null ? intval( $request->get_param(  'da_hm_checker_missing_self_referencing_hreflang' ), 10 ) : null;
		$options[ 'da_hm_checker_incorrect_language_script_region_codes'] = $request->get_param(  'da_hm_checker_incorrect_language_script_region_codes' ) !== null ? intval( $request->get_param(  'da_hm_checker_incorrect_language_script_region_codes' ), 10 ) : null;
		$options[ 'da_hm_checker_missing_hreflang_x_default'] = $request->get_param(  'da_hm_checker_missing_hreflang_x_default' ) !== null ? intval( $request->get_param(  'da_hm_checker_missing_hreflang_x_default' ), 10 ) : null;
		$options[ 'da_hm_checker_missing_return_link'] = $request->get_param(  'da_hm_checker_missing_return_link' ) !== null ? intval( $request->get_param(  'da_hm_checker_missing_return_link' ), 10 ) : null;
		$options[ 'da_hm_checker_canonical_and_hreflang_conflict'] = $request->get_param(  'da_hm_checker_canonical_and_hreflang_conflict' ) !== null ? intval( $request->get_param(  'da_hm_checker_canonical_and_hreflang_conflict' ), 10 ) : null;

		// Validation -------------------------------------------------------------------------------------------------.
		if ( null !== $options['da_hm_connections_in_menu'] ) {
			if ( intval( $options['da_hm_connections_in_menu'], 10 ) < 1 ) {
				$options['da_hm_connections_in_menu'] = get_option( 'da_hm_connections_in_menu' );
			}
		}

		if ( null !== $options['da_hm_max_execution_time_value'] ) {
			if ( ! preg_match( $this->shared->regex_number_ten_digits, $options['da_hm_max_execution_time_value'] ) || intval( $options['da_hm_max_execution_time_value'], 10 ) > 1000000 ) {
				$options['da_hm_max_execution_time_value'] = get_option( 'da_hm_max_execution_time_value' );
			}
		}

		if ( null !== $options['da_hm_memory_limit_value'] ) {
			if ( ! preg_match( $this->shared->regex_number_ten_digits, $options['da_hm_memory_limit_value'] ) || intval( $options['da_hm_memory_limit_value'], 10 ) > 1000000 ) {
				$options['da_hm_memory_limit_value'] = get_option( 'da_hm_set_memory_limit_value' );
			}
		}

		if ( null !== $options['da_hm_import_mode'] ) {
			if ( 'exact_copy' !== $options['da_hm_import_mode'] && 'import_options' !== $options['da_hm_import_mode'] ) {
				$options['da_hm_import_mode'] = get_option( 'da_hm_import_mode' );
			}
		}

		// Update the options -----------------------------------------------------------------------------------------.
		foreach ( $options as $key => $option ) {
			if ( null !== $option ) {
				update_option( $key, $option );
			}
		}

		/**
		 * If the Sync -> General Settings or the Sync -> Responder Role options sections are updated, schedule the cron
		 * event.
		 */
		if($request->get_param( 'da_hm_sync_status' ) !== null ||
		   $request->get_param( 'da_hm_sync_master_rest_endpoint' ) !== null
		){
			$this->shared->schedule_cron_event();
		}

		require_once $this->shared->get( 'dir' ) . 'vendor/autoload.php';
		$plugin_update_checker = new PluginUpdateChecker(DAHM_PLUGIN_UPDATE_CHECKER_SETTINGS);

		// Delete the transient used to store the plugin info previously retrieved from the remote server.
		$plugin_update_checker->delete_transient();

		// Fetch the plugin information from the remote server and saved it in the transient.
		$plugin_update_checker->fetch_remote_plugin_info();

		$response = new WP_REST_Response( 'Data successfully added.', '200' );

		return $response;
	}

	/**
	 * Check the user capability.
	 *
	 * @return true|WP_Error
	 */
	public function rest_api_daext_hreflang_manager_update_options_callback_permission_check() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_update_error',
				'Sorry, you are not allowed to update the Hreflang Manager options.',
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Check the user capability.
	 *
	 * @return true|WP_Error
	 */
	public function rest_api_daext_hreflang_manager_read_hreflang_checker_issues_callback_permission_check() {

		if ( ! current_user_can( get_option( $this->shared->get( 'slug' ) . '_checker_menu_capability' ) ) ) {
			return new WP_Error(
				'rest_update_error',
				'Sorry, you are not allowed to read the hreflang checker issues.',
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Callback for the POST 'daext-hreflang-manager/v1/hreflang-checker-issues' endpoint of the Rest API.
	 *
	 * This method is in the following contexts:
	 *
	 * - In the "Checker" menu to retrieve the hreflang checker issues.
	 *
	 * @param object $request The request data.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function rest_api_daext_hreflang_manager_read_hreflang_checker_issues_callback( $request ) {

		$data_update_required = intval( $request->get_param( 'data_update_required' ), 10 );

		if ( 0 === $data_update_required ) {

			// Use the provided form data.
			$search_string  = sanitize_text_field( $request->get_param( 'search_string' ) );
			$sorting_column = sanitize_text_field( $request->get_param( 'sorting_column' ) );
			$sorting_order  = sanitize_text_field( $request->get_param( 'sorting_order' ) );

		} else {

			// Set the default values of the form data.
			$search_string  = '';
			$sorting_column = 'date';
			$sorting_order  = 'desc';

			// Update the HTTP Status Archive.
			$this->shared->update_hreflang_checker_issues();

		}

		global $wpdb;
		$filter = '';

		// Create the WHERE part of the string based on the $search_string value.
		if ( '' !== $search_string ) {
			if ( strlen( $filter ) === 0 ) {
				$filter .= $wpdb->prepare( 'WHERE (alternate_url LIKE %s OR url_to_connect LIKE %s OR issue_type LIKE %s OR details LIKE %s)',
					'%' . $search_string . '%',
					'%' . $search_string . '%',
					'%' . $search_string . '%',
					'%' . $search_string . '%'
				);
			} else {
				$filter .= $wpdb->prepare( 'WHERE (alternate_url LIKE %s OR url_to_connect LIKE %s OR issue_type LIKE %s OR details LIKE %s)',
					'%' . $search_string . '%',
					'%' . $search_string . '%',
					'%' . $search_string . '%',
					'%' . $search_string . '%'
				);
			}
		}

		// Create the ORDER BY part of the query based on the $sorting_column and $sorting_order values.
		if ( '' !== $sorting_column ) {
			$filter .= $wpdb->prepare( ' ORDER BY %i', $sorting_column );
		} else {
			$filter .= ' ORDER BY date';
		}

		if ( 'desc' === $sorting_order ) {
			$filter .= ' DESC';
		} else {
			$filter .= ' ASC';
		}

		/**
		 * Check in the "_http_status" db table if all the links have been checked. (if there are zero links to
		 * check)
		 */
		global $wpdb;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}da_hm_hreflang_checker_issue" );

		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$count_total = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}da_hm_hreflang_checker_issue" );

		// Get the data from the "_archive" db table using $wpdb and put them in the $response array.
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $filter is prepared.
		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$checher_issues = $wpdb->get_results(
			"SELECT *
			FROM {$wpdb->prefix}da_hm_hreflang_checker_issue $filter"
		);
		// phpcs:enable

		/**
		 * Get the number of records in the "_hreflang_checker_queue".
		 */
		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$queue_url_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}da_hm_hreflang_checker_queue"
		);

		/**
		 * Get the number of records in the "_hreflang_checker_queue" that have the "checked" flag equal to "1".
		 */
		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$queue_url_count_checked = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}da_hm_hreflang_checker_queue WHERE checked = 1"
		);

		/**
		 * Get the number of records in the "_hreflang_checker_queue".
		 */
		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$queue_alternate_url_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}da_hm_hreflang_checker_queue WHERE alternate_url <> ''"
		);

		/**
		 * Get the number of records in the "_hreflang_checker_queue" that have the "checked" flag equal to "1".
		 */
		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$queue_alternate_url_count_checked = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}da_hm_hreflang_checker_queue WHERE alternate_url <> '' AND checked = 1"
		);

		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$queue_url_count_url_to_connect = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}da_hm_hreflang_checker_queue WHERE alternate_url = ''"
		);

		/**
		 * Get the total number of records in the "_connect" db table.
		 */
		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$total_url_counter = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}da_hm_connect" );

		/**
		 * Add the formatted date (based on the date format defined in the WordPress settings) to the $checher_issues
		 * array.
		 */
		foreach ( $checher_issues as $key => $request ) {
			$checher_issues[ $key ]->formatted_date = mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $request->date );
		}

		$response = array(
			'hreflang_checker_issues' => array(
				'all_issues'                        => count( $checher_issues ),
				'total_url_counter'                 => $total_url_counter,
				'queue_url_count'                   => $queue_url_count,
				'queue_url_count_checked'           => $queue_url_count_checked,
				'queue_url_count_url_to_connect'    => $queue_url_count_url_to_connect,
				'queue_alternate_url_count'         => $queue_alternate_url_count,
				'queue_alternate_url_count_checked' => $queue_alternate_url_count_checked,
			),
			'table'                   => $checher_issues,
		);

		// Prepare the response.
		$response = new WP_REST_Response( $response );

		return $response;
	}

}
