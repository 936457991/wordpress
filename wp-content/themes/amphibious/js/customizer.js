/**
 * Theme Customizer enhancements for a better user experience.
 *
 * Contains handlers to make Theme Customizer preview reload changes asynchronously.
 */

( function( $ ) {
	// Site Title
	wp.customize( 'blogname', function( value ) {
		value.bind( function( to ) {
			$( '.site-title a' ).text( to );
		} );
	} );

	// Site Description
	wp.customize( 'blogdescription', function( value ) {
		value.bind( function( to ) {
			$( '.site-description' ).text( to );
		} );
	} );

	// Background Color
	wp.customize( 'background_color', function( value ) {
		value.bind( function( to ) {
			$( 'body' ).css( 'background-color', to );
		} );
	} );

	// Header text color.
	wp.customize( 'header_textcolor', function( value ) {
		value.bind( function( to ) {
			if ( 'blank' === to ) {
				$( '.site-title a, .site-description' ).css( {
					clip: 'rect(1px, 1px, 1px, 1px)',
					position: 'absolute',
				} );
			} else {
				$( '.site-title a, .site-description' ).css( {
					clip: 'auto',
					position: 'relative',
				} );
				$( '.site-title a, .site-description' ).css( {
					color: to,
				} );
				$( '.site-description' ).css( {
					opacity: 0.7,
				} );
			}
		} );
	} );

	// Date Control Archive
	wp.customize( 'amphibious_archive_co_post_date', function( value ) {
		value.bind( function( to ) {
			if ( true === to ) {
				$( '.blog .posted-on, .archive .posted-on, .search .posted-on' ).css( {
					clip: 'auto',
					position: 'relative',
				} );
			} else {
				$( '.blog .posted-on, .archive .posted-on, .search .posted-on' ).css( {
					clip: 'rect(1px, 1px, 1px, 1px)',
					position: 'absolute',
				} );
			}
		} );
	} );

	// Category Control Archive
	wp.customize( 'amphibious_archive_co_post_categories', function( value ) {
		value.bind( function( to ) {
			if ( true === to ) {
				$( '.blog .cat-links, .archive .cat-links, .search .cat-links' ).css( {
					clip: 'auto',
					position: 'relative',
				} );
			} else {
				$( '.blog .cat-links, .archive .cat-links, .search .cat-links' ).css( {
					clip: 'rect(1px, 1px, 1px, 1px)',
					position: 'absolute',
				} );
			}
		} );
	} );

	// Author Control Archive
	wp.customize( 'amphibious_archive_co_post_author', function( value ) {
		value.bind( function( to ) {
			if ( true === to ) {
				$( '.blog .byline, .archive .byline, .search .byline' ).css( {
					clip: 'auto',
					position: 'relative',
				} );
			} else {
				$( '.blog .byline, .archive .byline, .search .byline' ).css( {
					clip: 'rect(1px, 1px, 1px, 1px)',
					position: 'absolute',
				} );
			}
		} );
	} );

	// Comments Control Archive
	wp.customize( 'amphibious_archive_co_post_comments', function( value ) {
		value.bind( function( to ) {
			if ( true === to ) {
				$( '.blog .comments-link, .archive .comments-link, .search .comments-link' ).css( {
					clip: 'auto',
					position: 'relative',
				} );
			} else {
				$( '.blog .comments-link, .archive .comments-link, .search .comments-link' ).css( {
					clip: 'rect(1px, 1px, 1px, 1px)',
					position: 'absolute',
				} );
			}
		} );
	} );

	// Date Control Single
	wp.customize( 'amphibious_single_co_post_date', function( value ) {
		value.bind( function( to ) {
			if ( true === to ) {
				$( '.single .posted-on' ).css( {
					clip: 'auto',
					position: 'relative',
				} );
			} else {
				$( '.single .posted-on' ).css( {
					clip: 'rect(1px, 1px, 1px, 1px)',
					position: 'absolute',
				} );
			}
		} );
	} );

	// Category Control Single
	wp.customize( 'amphibious_single_co_post_categories', function( value ) {
		value.bind( function( to ) {
			if ( true === to ) {
				$( '.single .cat-links' ).css( {
					clip: 'auto',
					position: 'relative',
				} );
			} else {
				$( '.single .cat-links' ).css( {
					clip: 'rect(1px, 1px, 1px, 1px)',
					position: 'absolute',
				} );
			}
		} );
	} );

	// Tag Control Single
	wp.customize( 'amphibious_single_co_post_tags', function( value ) {
		value.bind( function( to ) {
			if ( true === to ) {
				$( '.single .tags-links' ).css( {
					clip: 'auto',
					position: 'relative',
				} );
			} else {
				$( '.single .tags-links' ).css( {
					clip: 'rect(1px, 1px, 1px, 1px)',
					position: 'absolute',
				} );
			}
		} );
	} );

	// Author Control Single
	wp.customize( 'amphibious_single_co_post_author', function( value ) {
		value.bind( function( to ) {
			if ( true === to ) {
				$( '.single .byline' ).css( {
					clip: 'auto',
					position: 'relative',
				} );
			} else {
				$( '.single .byline' ).css( {
					clip: 'rect(1px, 1px, 1px, 1px)',
					position: 'absolute',
				} );
			}
		} );
	} );

	// Copyright Control
	wp.customize( 'amphibious_copyright', function( value ) {
		value.bind( function( to ) {
			$( '.credits-blog' ).html( to );
		} );
	} );

	// Credit Control
	wp.customize( 'amphibious_credit', function( value ) {
		value.bind( function( to ) {
			if ( true === to ) {
				$( '.credits-designer' ).css( {
					clip: 'auto',
					position: 'relative',
				} );
			} else {
				$( '.credits-designer' ).css( {
					clip: 'rect(1px, 1px, 1px, 1px)',
					position: 'absolute',
				} );
			}
		} );
	} );
}( jQuery ) );
