/**
 * This file is used to handle the confirmation dialog for performing a task in the Maintenance menu.
 *
 * @package hreflang-manager
 */

(function ($) {

	'use strict';

	$( document ).ready(
		function () {

			'use strict';

			initSelect2();

			$( document.body ).on(
				'click',
				'#execute-task' ,
				function (event) {

					'use strict';

					event.preventDefault();
					$( '#dialog-confirm' ).dialog( 'open' );

				}
			);

		}
	);

	/**
	 * Original Version (not compatible with pre-ES5 browser)
	 */
	$(
		function () {

			'use strict';

			$( '#dialog-confirm' ).dialog(
				{
					autoOpen: false,
					resizable: false,
					height: 'auto',
					width: 340,
					modal: true,
					buttons: {
						[window.objectL10n.deleteText]: function () {

							'use strict';

							$( '#form-maintenance' ).submit();

						},
						[window.objectL10n.cancelText]: function () {

							'use strict';

							$( this ).dialog( 'close' );

						},
					},
				}
			);

		}
	);

	/**
	 * Initialize the select2 fields.
	 */
	function initSelect2() {

		'use strict';

		$( '#task' ).select2();

	}

}(window.jQuery));
