/**
 * This file is used to handle the confirmation dialog for deleting a category in the Categories menu.
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
				'#cancel' ,
				function (event) {

					// Reload the Categories menu.
					event.preventDefault();
					window.location.replace( window.daim_admin_url + 'admin.php?page=daim-categories' );

				}
			);

			// Dialog Confirm -----------------------------------------------------------------------------------------.
			window.DAIM = {};
			$( document.body ).on(
				'click',
				'.daim-crud-table__row-actions-single-action-delete button',
				function (event) {

					'use strict';

					event.preventDefault();
					window.DAIM.categoryToDelete = $( this ).val();
					$( '#dialog-confirm' ).dialog( 'open' );

				}
			);

		}
	);

	/**
	 * Initialize the select2 fields.
	 */
	function initSelect2() {

		'use strict';

		let options = {
			placeholder: window.objectL10n.chooseAnOptionText,
		};

		for (let i = 1; i <= 100; i++) {
			$( '#language' + i ).select2();
			$( '#script' + i ).select2();
			$( '#locale' + i ).select2();
		}

	}

	/**
	 * Dialog confirm initialization.
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
						[objectL10n.deleteText]: function () {

							'use strict';

							$( '#delete-item-' + window.DAIM.categoryToDelete ).submit();

						},
						[objectL10n.cancelText]: function () {

							'use strict';

							$( this ).dialog( 'close' );

						},
					},
				}
			);
		}
	);

}(window.jQuery));
