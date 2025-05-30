<?php
/**
 * Class used to implement the back-end functionalities of the "Bulk Import" menu.
 *
 * @package hreflang-manager
 */

/**
 * Class used to implement the back-end functionalities of the "Bulk Import" menu.
 */
class Dahm_Bulk_Import_Menu_Elements extends Dahm_Menu_Elements {

	/**
	 * Constructor.
	 *
	 * @param object $shared The shared class.
	 * @param string $page_query_param The page query parameter.
	 * @param string $config The config parameter.
	 */
	public function __construct( $shared, $page_query_param, $config ) {

		parent::__construct( $shared, $page_query_param, $config );

		$this->menu_slug          = 'bulk-import';
		$this->slug_plural        = 'bulk-import';
		$this->label_singular     = __('Bulk Import', 'hreflang-manager');
		$this->label_plural       = __('Bulk Import', 'hreflang-manager');

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

			<div class="dahm-main-form">

				<div class="dahm-main-form__daext-form-section">

					<div class="dahm-main-form__daext-form-section-body">

						<!-- Data -->
						<div class="dahm-handsontable-wrapper">
							<label for="data"><?php esc_html_e( 'Data', 'hreflang-manager' ); ?></label>
							<div class="dahm-handsontable-container">
								<div id="dahm-table"></div>
							</div>
						</div>

						<!-- Submit -->
						<div class="daext-form-action">
							<input id="generate-connections" class="dahm-btn dahm-btn-primary" type="submit" value="<?php esc_attr_e( 'Generate Connections', 'hreflang-manager' ); ?>">
						</div>

					</div>

				</div>

			</div>

		</div>

		<?php
	}
}
