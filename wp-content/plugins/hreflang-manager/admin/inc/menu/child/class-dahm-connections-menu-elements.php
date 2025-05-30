<?php
/**
 * Class used to implement the back-end functionalities of the "Connections" menu.
 *
 * @package hreflang-manager
 */

/**
 * Class used to implement the back-end functionalities of the "Term Groups" menu.
 */
class Dahm_Connections_Menu_Elements extends Dahm_Menu_Elements {

	/**
	 * Constructor.
	 *
	 * @param object $shared The shared class.
	 * @param string $page_query_param The page query parameter.
	 * @param string $config The config parameter.
	 */
	public function __construct( $shared, $page_query_param, $config ) {

		parent::__construct( $shared, $page_query_param, $config );

		$this->menu_slug          = 'connection';
		$this->slug_plural        = 'connections';
		$this->label_singular     = 'Connection';
		$this->label_plural       = 'Connections';
		$this->primary_key        = 'id';
		$this->db_table           = 'connect';
		$this->list_table_columns = array(
			array(
				'db_field' => 'url_to_connect',
				'label'    => __( 'URL to Connect', 'hreflang-manager' ),
			),
			array(
				'db_field'               => 'id',
				'label'                  => 'Alternate Pages',
				'custom_output_function' => __( 'display_connection_codes', 'hreflang-manager' ),
			),
			array(
				'db_field'                => 'inherited',
				'label'                   => __( 'Inherited', 'hreflang-manager' ),
				'prepare_displayed_value' => array( $shared, 'prepare_displayed_value_inherited' ),
			),
		);
		$this->searchable_fields  = array(
			'id',
			'url_to_connect',
		);

		// Prepare the default values ---------------------------------------------------------------------------------.

		// Get default url, language, script, and locale from the options.
		$url = array();
		$language = array();
		$script = array();
		$locale = array();

		for ( $i = 1; $i <= 100; $i++ ) {
			$url[ $i ]      = get_option( $this->shared->get( 'slug' ) . '_default_url_' . $i );
			$language[ $i ] = get_option( $this->shared->get( 'slug' ) . '_default_language_' . $i );
			$script[ $i ]   = get_option( $this->shared->get( 'slug' ) . '_default_script_' . $i );
			$locale[ $i ]   = get_option( $this->shared->get( 'slug' ) . '_default_locale_' . $i );
		}

		$this->default_values     = array(
			'url_to_connect' => '',
			'url'                   => wp_json_encode( $url ),
			'language'               => wp_json_encode( $language ),
			'script'                 => wp_json_encode( $script ),
			'locale'                 => wp_json_encode( $locale ),
			'inherited'              => '0',
		);

	}

	/**
	 * Process the add/edit form submission of the menu. Specifically the following tasks are performed:
	 *
	 * 1. Sanitization
	 * 2. Validation
	 * 3. Database update
	 *
	 * @return void
	 */
	public function process_form() {

		if ( isset( $_POST['update_id'] ) ||
			isset( $_POST['form_submitted'] ) ) {

			// Nonce verification.
			check_admin_referer( 'dahm_create_update_' . $this->menu_slug, 'dahm_create_update_' . $this->menu_slug . '_nonce' );

		}

		?>

		<!-- process data -->

		<?php

		// Initialize variables ---------------------------------------------------------------------------------------.

		// Save the connection into the database.
		if ( isset( $_POST['form_submitted'] ) ) {

			// Sanitization -------------------------------------------------------------------------------------------.
			$update_id      = isset( $_POST['update_id'] ) ? intval( $_POST['update_id'], 10 ) : '';
			$url_to_connect = isset( $_POST['url_to_connect'] ) ? esc_url_raw( wp_unslash( $_POST['url_to_connect'] ) ) : null;
			$url            = array();
			$language       = array();
			$script         = array();
			$locale         = array();
			$inherited      = isset( $_POST['inherited'] ) && intval( $_POST['inherited'], 10 ) === 1 ? 1 : 0;

			for ( $i = 1; $i <= 100; $i++ ) {

				if ( isset( $_POST[ 'url' . $i ] ) ) {
					$sanitized_url = esc_url_raw( wp_unslash( $_POST[ 'url' . $i ] ) );
					if ( strlen( trim( $sanitized_url ) ) > 0 ) {
						$url[ $i ] = $sanitized_url;
					} else {
						$url[ $i ] = '';
					}
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

			// Validation ---------------------------------------------------------------------------------------------.

			// Verify if the "URL to Connect" is empty.
			if ( 0 === strlen( trim( $url_to_connect ) ) ) {
				$this->shared->save_dismissible_notice(
					__( 'The "URL to Connect" field is empty.', 'hreflang-manager' ),
					'error'
				);
				$invalid_data = true;
			}
		}

		// Update or add the record in the database.
		if ( isset( $_POST['form_submitted'] ) && ! isset( $invalid_data ) ) {

			$url_json      = wp_json_encode( $url );
			$language_json = wp_json_encode( $language );
			$script_json   = wp_json_encode( $script );
			$locale_json   = wp_json_encode( $locale );

			if ( isset( $update_id ) && ! empty( $update_id ) ) {

				// Update.

				global $wpdb;

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$query_result = $wpdb->query(
					$wpdb->prepare(
						"UPDATE {$wpdb->prefix}da_hm_connect SET
        url_to_connect = %s ,
                 url = %s,
                 language = %s,
                 script = %s,
                 locale = %s,
                 inherited = %d
            WHERE id = %d",
						$url_to_connect,
						$url_json,
						$language_json,
						$script_json,
						$locale_json,
						$inherited,
						$update_id
					)
				);

				if ( false !== $query_result ) {
					$this->shared->save_dismissible_notice(
						__( 'The connection has been successfully updated.', 'hreflang-manager' ),
						'updated'
					);
				}
			} else {

				// Add a new connection into the database.
				global $wpdb;

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$query_result = $wpdb->query(
					$wpdb->prepare(
						"INSERT INTO {$wpdb->prefix}da_hm_connect SET url_to_connect = %s ,
                 url = %s,
                 language = %s,
                 script = %s,
                 locale = %s,
                 inherited = %d",
						$url_to_connect,
						$url_json,
						$language_json,
						$script_json,
						$locale_json,
						$inherited
					)
				);

				if ( isset( $query_result ) && false !== $query_result ) {
					$this->shared->save_dismissible_notice(
						__( 'The connection has been successfully added.', 'hreflang-manager' ),
						'updated'
					);
				}

				$auto_alternate_pages = intval( get_option( 'da_hm_auto_alternate_pages' ), 10 );
				$query_result         = false;
				if ( 1 === $auto_alternate_pages ) {

					for ( $i = 1;$i <= 100;$i++ ) {

						if ( strlen( trim( $url[ $i ] ) ) > 0 && $url[ $i ] !== $url_to_connect ) {

							// phpcs:ignore WordPress.DB.DirectDatabaseQuery
							$query_result = $wpdb->query(
								$wpdb->prepare(
									"INSERT INTO {$wpdb->prefix}da_hm_connect SET url_to_connect = %s ,
                         url = %s,
                         language = %s,
                         script = %s,
                         locale = %s,
                         inherited = %d",
									$url[ $i ],
									$url_json,
									$language_json,
									$script_json,
									$locale_json,
									$inherited
								)
							);

						}
					}

					if ( isset( $query_result ) && false !== $query_result ) {

						$this->shared->save_dismissible_notice(
							__( 'The connections of the alternate pages have been successfully added.', 'hreflang-manager' ),
							'updated'
						);

					}
				}
			}
		}
	}

	/**
	 * Defines the form fields present in the add/edit form and call the method to print them.
	 *
	 * @param object $item_obj The object containing the data of the item.
	 * @return void
	 */
	public function print_form_fields( $item_obj = null ) {

		// Get the connection data.
		if ( isset( $item_obj ) ) {

			$item_obj['url']      = json_decode( $item_obj['url'] );
			$item_obj['language'] = json_decode( $item_obj['language'] );
			$item_obj['script']   = json_decode( $item_obj['script'] );
			$item_obj['locale']   = json_decode( $item_obj['locale'] );

		}

		// Get the languages.
		$languages = get_option( 'da_hm_language' );

		// Invert array indexes with array values.
		$languages = array_flip( $languages );

		// Add the language code at the start of the language name.
		foreach( $languages as $key => $value ) {
			$languages[ $key ] = $key . ' - ' . $value;
		}

		// Get the scripts.
		$scripts = get_option( 'da_hm_script' );

		// Invert array indexes with array values.
		$scripts = array_flip( $scripts );

		// Add the script code at the start of the script name.
		foreach( $scripts as $key => $value ) {
			$scripts[ $key ] = $key . ' - ' . $value;
		}

		// Add the "Not Assigned" option at the beginning of the array.
		$scripts = array( '' => __( 'Not Assigned', 'hreflang-manager' ) ) + $scripts;

		// Get the locale.
		$locales = get_option( 'da_hm_locale' );

		// Invert array indexes with array values.
		$locales = array_flip( $locales );

		// Add the locale code at the start of the locale name.
		foreach( $locales as $key => $value ) {
			$locales[ $key ] = $key . ' - ' . $value;
		}

		// Add the "Not Assigned" option at the beginning of the array.
		$locales = array( '' => __( 'Not Assigned', 'hreflang-manager' ) ) + $locales;

		// Add the form data in the $sections array.
		$sections = array(
			array(
				'label'          => 'Main',
				'section_id'     => 'main',
				'display_header' => false,
				'fields'         => array(
					array(
						'type'        => 'text',
						'name'        => 'url_to_connect',
						'label'       => __( 'URL to Connect', 'hreflang-manager' ),
						'description' => __( 'The URL where the hreflang link elements should be applied.', 'hreflang-manager' ),
						'value'       => isset( $item_obj ) ? $item_obj['url_to_connect'] : null,
						'maxlength'   => 2083,
						'required'    => true,
					),
				),
			),
			array(
				'label'          => 'Advanced',
				'section_id'     => 'advanced',
				'icon_id'        => 'settings-01',
				'display_header' => true,
				'fields'         => array(
					array(
						'type'        => 'select',
						'name'        => 'inherited',
						'label'       => __( 'Inherited', 'hreflang-manager' ),
						'description' => __( 'This option is used during the sync process to distinguish the connections inherited from the controller site from the connections manually created.', 'hreflang-manager' ),
						'options'     => array(
							'0' => __( 'No', 'hreflang-manager' ),
							'1' => __( 'Yes', 'hreflang-manager' ),
						),
						'value'       => isset( $item_obj ) ? $item_obj['inherited'] : null,
					),
				),
			),
		);

		// Get the number of connections that should be displayed in the menu.
		$connections_in_menu = intval( get_option( 'da_hm_connections_in_menu' ), 10 );

		for ( $i = 1;$i <= $connections_in_menu;$i++ ) {

			$sections[0]['fields'][] = array(
				'type'        => 'text',
				'name'        => 'url' . $i,
				'label'       => __( 'URL', 'hreflang-manager' ) . ' ' . $i,
				'description' => __( 'The URL of the alternate page', 'hreflang-manager' ) . ' ' . $i . '.',
				'value'       => isset( $item_obj ) ? $item_obj['url']->{$i} : null,
				'maxlength'   => 2083,
				'required'    => false,
			);

			$sections[0]['fields'][] = array(
				'type'        => 'select',
				'name'        => 'language' . $i,
				'label'       => __( 'Language', 'hreflang-manager' ) . ' ' . $i,
				'description' => __( 'The language of the alternate page', 'hreflang-manager' ) . ' ' . $i . '.',
				'options'     => $languages,
				'value'       => isset( $item_obj ) ? $item_obj['language']->{$i} : null,
				'required'    => false,
			);

			$sections[0]['fields'][] = array(
				'type'        => 'select',
				'name'        => 'script' . $i,
				'label'       => __( 'Script', 'hreflang-manager' ) . ' ' . $i,
				'description' => __( 'The script of the alternate page', 'hreflang-manager' ) . ' ' . $i . '.',
				'options'     => $scripts,
				'value'       => isset( $item_obj ) ? $item_obj['script']->{$i} : null,
				'required'    => false,
			);

			$sections[0]['fields'][] = array(
				'type'        => 'select',
				'name'        => 'locale' . $i,
				'label'       => __( 'Locale', 'hreflang-manager' ) . ' ' . $i,
				'description' => __( 'The locale of the alternate page', 'hreflang-manager' ) . ' ' . $i . '.',
				'options'     => $locales,
				'value'       => isset( $item_obj ) ? $item_obj['locale']->{$i} : null,
				'required'    => false,
			);

		}

		$this->print_form_fields_from_array( $sections );
	}

	/**
	 * Check if the item is deletable. If not, return the message to be displayed.
	 *
	 * @param int $item_id The ID of the item.
	 *
	 * @return array
	 */
	public function item_is_deletable( $item_id ) {

		$is_deletable               = true;
		$dismissible_notice_message = null;

		return array(
			'is_deletable'               => $is_deletable,
			'dismissible_notice_message' => $dismissible_notice_message,
		);
	}
}
