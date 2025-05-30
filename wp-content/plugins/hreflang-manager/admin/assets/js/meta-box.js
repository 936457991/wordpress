/**
 * This file is used loaded in the post editor when the Classic Editor is used, and it's used to initialize the select2.
 *
 * @package hreflang-manager
 */

(function ($) {

	'use strict';

	function initSelect2(){

		'use strict';

		let select2_elements = [];

		for (let i = 1;i <= 100;i++) {

			if ( $( '#language' + i ).length && $( '#locale' + i ).length ) {

				select2_elements.push( '#language' + i );
				select2_elements.push( '#script' + i );
				select2_elements.push( '#locale' + i );

			}

		}

		$( select2_elements.join( ',' ) ).select2();

	}

	$( document ).ready(
		function () {

			'use strict';

			initSelect2();

		}
	);

})( jQuery );