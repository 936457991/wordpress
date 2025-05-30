<?php
/**
 * Class used to implement the back-end functionalities of the "Checker" menu.
 *
 * @package hreflang-manager
 */

/**
 * Class used to implement the back-end functionalities of the "CHECKER" menu.
 */
class Dahm_Checker_Menu_Elements extends Dahm_Menu_Elements {

	/**
	 * Constructor.
	 *
	 * @param object $shared The shared class.
	 * @param string $page_query_param The page query parameter.
	 * @param string $config The config parameter.
	 */
	public function __construct( $shared, $page_query_param, $config ) {

		parent::__construct( $shared, $page_query_param, $config );

		$this->menu_slug          = 'checker';
		$this->slug_plural        = 'checker';
		$this->label_singular     = __('Checker', 'hreflang-manager');
		$this->label_plural       = __('Checker', 'hreflang-manager');

	}

	/**
	 * Display the content of the body.
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

			<div id="react-root"></div>

		</div>

		<?php

	}

}
