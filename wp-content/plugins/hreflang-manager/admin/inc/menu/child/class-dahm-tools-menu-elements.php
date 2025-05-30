<?php
/**
 * Class used to implement the back-end functionalities of the "Tools" menu.
 *
 * @package hreflang-manager
 */

/**
 * Class used to implement the back-end functionalities of the "Tools" menu.
 */
class Dahm_Tools_Menu_Elements extends Dahm_Menu_Elements {

	/**
	 * Constructor.
	 *
	 * @param object $shared The shared class.
	 * @param string $page_query_param The page query parameter.
	 * @param string $config The config parameter.
	 */
	public function __construct( $shared, $page_query_param, $config ) {

		parent::__construct( $shared, $page_query_param, $config );

		$this->menu_slug          = 'tool';
		$this->slug_plural        = 'tools';
		$this->label_singular     = __('Tool', 'hreflang-manager');
		$this->label_plural       = __('Tools', 'hreflang-manager');

	}

	/**
	 * Process the add/edit form submission of the menu. Specifically the following tasks are performed:
	 *
	 *  1. Sanitization
	 *  2. Validation
	 *  3. Database update
	 *
	 * @return false|void
	 */
	public function process_form() {

		// Process the xml file upload. (import) ----------------------------------------------------------------------.
		if ( isset( $_FILES['file_to_upload'] ) &&
			isset( $_FILES['file_to_upload']['name'] )
		) {

			// Nonce verification.
			check_admin_referer( 'dahm_tools_import', 'dahm_tools_import_nonce' );

			//phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.InputNotValidated -- The sanitization is performed with sanitize_uploaded_file().
			$file_data = $this->shared->sanitize_uploaded_file(
				array(
					'name'     => $_FILES['file_to_upload']['name'],
					'type'     => $_FILES['file_to_upload']['type'],
					'tmp_name' => $_FILES['file_to_upload']['tmp_name'],
					'error'    => $_FILES['file_to_upload']['error'],
					'size'     => $_FILES['file_to_upload']['size'],
				)
			);
			//phpcs:enable

			if ( 1 !== preg_match( '/^.+\.xml$/', $file_data['name'] ) ) {
				return;
			}

			if ( file_exists( $file_data['tmp_name'] ) ) {

				$counter = 0;

				// Read xml file.
				$xml = simplexml_load_file( $file_data['tmp_name'] );

				foreach ( $xml->connect as $single_connect ) {

					// Convert object to array.
					$single_connect_a = get_object_vars( $single_connect );

					// Remove the id key.
					unset( $single_connect_a['id'] );

					/**
					 * Generate the 'url_to_connect' value based on the 'Import Language', Import Script', and
					 * 'Import Locale' options if the "Import Mode" option is set to "Based on Import Options".
					 */
					if ( 'import_options' === get_option( 'da_hm_import_mode' ) ) {

						// Retrieve the 'Import Language', 'Import Script', and the "Import Locale" from the options.
						$import_language = get_option( $this->shared->get( 'slug' ) . '_import_language' );
						$import_script   = get_option( $this->shared->get( 'slug' ) . '_import_script' );
						$import_locale   = get_option( $this->shared->get( 'slug' ) . '_import_locale' );

						$single_connect_a['url_to_connect'] = $this->shared->generate_url_to_connect(
							$single_connect_a,
							$import_language,
							$import_script,
							$import_locale
						);

					}

					global $wpdb;
					$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_connect';

					// phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$wpdb->insert(
						$table_name,
						$single_connect_a
					);
					$inserted_table_id = $wpdb->insert_id;

					++$counter;

				}

				$this->shared->save_dismissible_notice(
					$counter . ' ' . __( 'connections have been added.', 'hreflang-manager' ),
					'updated'
				);

			}
		}

		// process the export button click. (export) ------------------------------------------------------------------.

		/**
		 * Intercept requests that come from the "Export" button of the "Tools -> Export" menu and generate the
		 * downloadable XML file
		 */
		if ( isset( $_POST['dahm_export'] ) ) {

			// Nonce verification.
			check_admin_referer( 'dahm_tools_export', 'dahm_tools_export' );

			// Verify capability.
			if ( ! current_user_can( get_option( $this->shared->get( 'slug' ) . '_tools_menu_capability' ) ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'hreflang-manager' ) );
			}

			// get the data from the 'connect' db.
			global $wpdb;

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$connect_a = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}da_hm_connect ORDER BY id ASC", ARRAY_A );

			// If there are data generate the csv header and the content.
			if ( count( $connect_a ) > 0 ) {

				// Generate the header of the XML file.
				header( 'Content-Encoding: UTF-8' );
				header( 'Content-type: text/xml; charset=UTF-8' );
				header( 'Content-Disposition: attachment; filename=hreflang-manager-' . time() . '.xml' );
				header( 'Pragma: no-cache' );
				header( 'Expires: 0' );

				// Generate initial part of the XML file.
				echo '<?xml version="1.0" encoding="UTF-8" ?>';
				echo '<root>';

				// Set column content.
				foreach ( $connect_a as $connect ) {

					echo '<connect>';

					// Get all the indexes of the $table array.
					$table_keys = array_keys( $connect );

					// Cycle through all the indexes of $connect and create all the tags related to this record.
					foreach ( $table_keys as $key ) {

						echo '<' . esc_attr( $key ) . '>' . esc_attr( $connect[ $key ] ) . '</' . esc_attr( $key ) . '>';

					}

					echo '</connect>';

				}

				// Generate the final part of the XML file.
				echo '</root>';

			} else {
				return false;
			}

			die();

		}
	}

	/**
	 * Display the form.
	 *
	 * @return void
	 */
	public function display_custom_content() {

		?>

		<div class="dahm-admin-body">

			<?php

			// Display the dismissible notices.
			$this->shared->display_dismissible_notices();

			// Display the license activation notice.
			$this->shared->display_license_activation_notice();

			?>

			<div class="dahm-tools-menu">

				<div class="dahm-main-form">

				<div class="dahm-main-form__wrapper-half">

					<div class="dahm-main-form__daext-form-section">

						<div class="dahm-main-form__section-header">
							<div class="dahm-main-form__section-header-title">
								<?php $this->shared->echo_icon_svg( 'log-in-04' ); ?>
								<div class="dahm-main-form__section-header-title-text"><?php esc_html_e( 'Import', 'hreflang-manager' ); ?></div>
							</div>
						</div>

						<div class="dahm-main-form__daext-form-section-body">

							<!-- Import form -->

							<form enctype="multipart/form-data" id="import-upload-form" method="post" class="wp-upload-form"
									action="admin.php?page=da_hm_<?php echo esc_attr( $this->slug_plural ); ?>">
								<?php
								wp_nonce_field( 'dahm_tools_import', 'dahm_tools_import_nonce' );
								?>

								<p>
								<div class="dahm-input-wrapper">
									<label for="upload" class="custom-file-upload"><?php esc_html_e( 'Choose file', 'hreflang-manager' ); ?></label>
									<div class="custom-file-upload-text" id="upload-text"><?php esc_html_e( 'No file chosen', 'hreflang-manager' ); ?></div>
									<input type="file" id="upload" name="file_to_upload"
											class="custom-file-upload-input">
								</div>
								</p>

								<p class="submit"><input type="submit" name="submit" id="submit" class="dahm-btn dahm-btn-primary"
														value="<?php esc_attr_e( 'Upload file and import', 'hreflang-manager' ); ?>"></p>
							</form>
							<p>
								<strong>
									<?php
									esc_html_e(
										'IMPORTANT: This functionality should only be used to import the XML files generated with the "Export" tool.',
										'hreflang-manager'
									);
									?>
								</strong></p>

						</div>

					</div>

					<div class="dahm-main-form__daext-form-section">

						<div class="dahm-main-form__section-header">
							<div class="dahm-main-form__section-header-title">
								<?php $this->shared->echo_icon_svg( 'log-out-04' ); ?>
								<div class="dahm-main-form__section-header-title-text"><?php esc_html_e( 'Export', 'hreflang-manager' ); ?></div>
							</div>
						</div>

						<div class="dahm-main-form__daext-form-section-body">

							<!-- Export form -->

							<p>
								<?php
								esc_html_e(
									'Click the Export button to generate an XML file that includes all the connections.',
									'hreflang-manager'
								);
								?>
							</p>

							<!-- the data sent through this form are handled by the export_xml_controller() method called with the WordPress init action -->
							<form method="POST" action="admin.php?page=<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>_<?php echo esc_attr( $this->slug_plural ); ?>">

								<div class="daext-widget-submit">
									<?php wp_nonce_field( 'dahm_tools_export', 'dahm_tools_export' ); ?>
									<input name="dahm_export" class="dahm-btn dahm-btn-primary" type="submit"
											value="<?php esc_attr_e( 'Export', 'hreflang-manager' ); ?>"
										<?php
										if ( ! $this->shared->exportable_data_exists() ) {
											echo 'disabled="disabled"';
										}
										?>
									>
								</div>

							</form>

						</div>

					</div>

				</div>

			</div>

			</div>

		</div>

		<?php
	}
}
