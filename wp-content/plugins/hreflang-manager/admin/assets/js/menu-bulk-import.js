/**
 * This file is used to initialize Handsontable and handle the bulk import of connections in the Bulk Import menu.
 *
 * @package hreflang-manager
 */

(function ($) {

	'use strict';

	let hotInstance;

	$( document ).ready(
		function () {

			'use strict';

			// Set ajax in synchronous mode.
			$.ajaxSetup( {async: false} );

			initHot();

			bindEventListeners();

		}
	);

	function initHot() {

		'use strict';

		let hotData,
		dahmContainer;

		// Initialize an empty table.
		hotData = [
		[
		'URL to Connect'
		],
		];

		for (let i = 1;i <= 100;i++) {
			hotData[0].push( 'URL ' + i );
			hotData[0].push( 'Language ' + i );
			hotData[0].push( 'Script ' + i );
			hotData[0].push( 'Locale ' + i );
		}

		for (let i = 0; i < 10000; i++) {
			let content = []
			for (let i = 1;i <= 401;i++) {
				content.push( '' );
			}

			hotData.push( content );
		}

		// Instantiate the handsontable table.
		dahmContainer = document.getElementById( 'dahm-table' );
		hotInstance   = new window.Handsontable(
			dahmContainer,
			{

				// Set the table data.
				data: hotData,

				// Set the new maximum number of rows and columns.
				maxRows: 10001,
				maxCols: 401,

			}
		);

		hotInstance.updateSettings(
			{
				cells: function (row, col) {

					let cellProperties = {};

					if (row === 0) {
						cellProperties.readOnly               = true;
						cellProperties.disableVisualSelection = true;
					}

					return cellProperties;

				},
			}
		);

	}

	function bindEventListeners() {

		'use strict';

		$( '#generate-connections' ).on('click',
			function (event) {

				'use strict';

				// Do not continue if there is a pending ajax request.
				if (typeof window.dahmPendingAjaxRequest !== 'undefined' && window.dahmPendingAjaxRequest) {
					return;
				}

				event.preventDefault();

				generateConnections();

			}
		);

	}

	function generateConnections() {

		'use strict';

		let rawTableData,
		tableData = [];

		// Remove first row from the array (because it includes the labels of the hot table).
		rawTableData = hotInstance.getData().slice( 1 );

		// Put only the non-empty rows in tableData.
		for (let key1 in rawTableData) {
			let found = false;
			for (let i = 0;i <= 400;i++) {
				if (rawTableData[key1][i] !== '') {
					found = true;
					break;
				}
			}
			if (found) {
				tableData.push( rawTableData[key1] );
			}
		}

		// Do not proceed if there are zero valid connections.
		if (tableData.length === 0) {
			return;
		}

		// Convert the resulting JSON value to a JSON string.
		tableData = JSON.stringify( tableData );

		// Prepare ajax request.
		let data = {
			'action': 'dahm_bulk_import_generate_connections',
			'security': window.DAHM_PARAMETERS.nonce,
			'table_data': tableData,
		};

		// Send ajax request.
		$.post(
			window.DAHM_PARAMETERS.ajaxUrl,
			data,
			function (result) {

				'use strict';

				// Set the flag used to detect the pending ajax request.
				window.dahmPendingAjaxRequest = true;

				// Reload the dashboard menu.
				try {
					JSON.parse( result );
				} catch (e) {
					return;
				}

				result = JSON.parse( result );
				window.location.replace( window.DAHM_PARAMETERS.adminUrl + 'admin.php?page=da_hm_bulk_import&result=' + result.connections_added + '&result_alternate=' + result.connections_added_alternate );

			}
		);

	}

}(window.jQuery));