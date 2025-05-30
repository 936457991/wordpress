<?php
/**
 * This file contains the class Daim_Ajax, used to include ajax actions.
 *
 * @package hreflang-manager
 */

/**
 * This class should be used to include ajax actions.
 */
class Dahm_Ajax {

	/**
	 * The instance of the Daim_Ajax class.
	 *
	 * @var Dahm_Ajax
	 */
	protected static $instance = null;

	/**
	 * The instance of the Daim_Shared class.
	 *
	 * @var Dahm_Shared
	 */
	private $shared = null;

	/**
	 * The constructor of the Dahm_Ajax class.
	 */
	private function __construct() {

		// Assign an instance of the plugin info.
		$this->shared = Dahm_Shared::get_instance();

		// AJAX requests for logged-in users.
		add_action( 'wp_ajax_dahm_bulk_import_generate_connections', array( $this, 'dahm_bulk_import_generate_connections' ) );
	}

	/**
	 * Return an istance of this class.
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Ajax handler used to generate the connections based on the data available in the table of the Bulk Import menu.
	 *
	 *  This method is called when the "Generate Connections" button available in the Bulk Import menu is clicked.
	 *
	 * @return void
	 */
	public function dahm_bulk_import_generate_connections() {

		// Check the nonce.
		if ( ! check_ajax_referer( 'dahm', 'security', false ) ) {
			echo 'Invalid AJAX Request';
			die();
		}

		// check the capability.
		if ( ! current_user_can( get_option( $this->shared->get( 'slug' ) . '_bulk_import_menu_capability' ) ) ) {
			echo 'Invalid Capability';
			die();
		}

		// Set the PHP "Max Execution Time" and "Memory Limit" based on the values defined in the options.
		$this->shared->set_met_and_ml();

		// Init vars.
		$query_result                   = false;
		$query_result_alternate         = false;
		$query_result_counter           = 0;
		$query_result_alternate_counter = 0;

		// Get the data of the table.

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Table data sanitized with a custom function.
		$table_data_a = isset( $_POST['table_data'] ) ? $this->shared->sanitize_table_data( wp_unslash( $_POST['table_data'] ) ) : null;

		// Save the data ----------------------------------------------------------------------------------------------.

		global $wpdb;
		$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_connect';

		foreach ( $table_data_a as $row_index => $row_data ) {

			$url_to_connect = sanitize_text_field( $row_data[0] );

			if ( ! empty( $url_to_connect ) ) {

				$url      = array();
				$language = array();
				$script   = array();
				$locale   = array();

				for ( $i = 1;$i <= 100;$i++ ) {

					$url[ $i ]      = esc_url_raw( $row_data[ 1 + ( 4 * ( $i - 1 ) ) ] );
					$language[ $i ] = sanitize_text_field( $row_data[ 2 + ( 4 * ( $i - 1 ) ) ] );
					$script[ $i ]   = sanitize_text_field( $row_data[ 3 + ( 4 * ( $i - 1 ) ) ] );
					$locale[ $i ]   = sanitize_text_field( $row_data[ 4 + ( 4 * ( $i - 1 ) ) ] );

					if ( ! $this->shared->is_valid_language( $language[ $i ] ) ) {
						$language[ $i ] = 'x-default';
					}

					if ( ! $this->shared->is_valid_script( $script[ $i ] ) ) {
						$script[ $i ] = '';
					}

					if ( ! $this->shared->is_valid_locale( $locale[ $i ] ) ) {
						$locale[ $i ] = '';
					}
				}

				// Create the serialized fields for the database.
				$url_json      = wp_json_encode( $url );
				$language_json = wp_json_encode( $language );
				$script_json   = wp_json_encode( $script );
				$locale_json   = wp_json_encode( $locale );

				// Add a new connection into the database.

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$query_result = $wpdb->query(
					$wpdb->prepare(
						"INSERT INTO {$wpdb->prefix}da_hm_connect SET url_to_connect = %s,
                 url = %s,
                 language = %s,
                 script = %s,
                 locale = %s",
						$url_to_connect,
						$url_json,
						$language_json,
						$script_json,
						$locale_json
					)
				);

				if ( false !== $query_result ) {
					++$query_result_counter;
				}

				$auto_alternate_pages = intval( get_option( 'da_hm_auto_alternate_pages' ), 10 );
				if ( 1 === $auto_alternate_pages ) {

					for ( $i = 1;$i <= 100;$i++ ) {

						if ( strlen( trim( $url[ $i ] ) ) > 0 && $url[ $i ] !== $url_to_connect ) {

							// phpcs:ignore WordPress.DB.DirectDatabaseQuery
							$query_result_alternate = $wpdb->query(
								$wpdb->prepare(
									"INSERT INTO {$wpdb->prefix}da_hm_connect SET url_to_connect = %s ,
                         url = %s,
                         language = %s,
                         script = %s,
                         locale = %s",
									$url[ $i ],
									$url_json,
									$language_json,
									$script_json,
									$locale_json
								)
							);

							if ( false !== $query_result_alternate ) {
								++$query_result_alternate_counter;
							}
						}
					}
				}
			}
		}

		if ( 0 === $query_result_counter ) {
			$this->shared->save_dismissible_notice(
				__( 'No rows have been added.', 'hreflang-manager' ),
				'error'
			);
		} else {
			$this->shared->save_dismissible_notice(
				$query_result_counter . ' ' . __( 'connections have been successfully added.', 'hreflang-manager' ),
				'updated'
			);

			if ( $query_result_alternate_counter > 0 ) {

				$this->shared->save_dismissible_notice(
					$query_result_alternate_counter . ' ' . __( 'connections of the alternate pages have been successfully added.', 'hreflang-manager' ),
					'updated'
				);

			}
		}

		// Send output.
		echo wp_json_encode(
			array(
				'connections_added'           => $query_result_counter,
				'connections_added_alternate' => $query_result_alternate_counter,
			)
		);
		die();
	}
}
