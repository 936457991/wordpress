<?php
/**
 * Custom functions to handle the theme fonts
 *
 * @package Amphibious
 */

/**
 * Register fonts for theme.
 *
 * @return string Fonts URL for the theme.
 */
function amphibious_fonts_url() {
    // Fonts and Other Variables
    $fonts_url = '';
	$fonts     = array();
	$subsets   = 'latin,latin-ext';

	/* Translators: If there are characters in your language that are not
    * supported by Poppins, translate this to 'off'. Do not translate
    * into your own language.
    */
    if ( 'off' !== esc_html_x( 'on', 'Poppins font: on or off', 'amphibious' ) ) {
		$fonts[] = 'Poppins:400,400i,700,700i';
	}

	/* Translators: If there are characters in your language that are not
    * supported by Rubik, translate this to 'off'. Do not translate
    * into your own language.
    */
    if ( 'off' !== esc_html_x( 'on', 'Rubik font: on or off', 'amphibious' ) ) {
		$fonts[] = 'Rubik:400,400i,700,700i';
	}

	/* Translators: To add an additional character subset specific to your language,
	* translate this to 'greek', 'cyrillic', 'devanagari' or 'vietnamese'.
	* Do not translate into your own language.
	*/
	$subset = esc_html_x( 'no-subset', 'Add new subset (cyrillic, greek, devanagari, vietnamese)', 'amphibious' );

	if ( 'cyrillic' === $subset ) {
		$subsets .= ',cyrillic,cyrillic-ext';
	} elseif ( 'greek' === $subset ) {
		$subsets .= ',greek,greek-ext';
	} elseif ( 'devanagari' === $subset ) {
		$subsets .= ',devanagari';
	} elseif ( 'vietnamese' === $subset ) {
		$subsets .= ',vietnamese';
	}

	if ( $fonts ) {
		$fonts_url = add_query_arg( array(
			'family' => urlencode( implode( '|', $fonts ) ),
			'subset' => urlencode( $subsets ),
		), 'https://fonts.googleapis.com/css' );
	}

	/**
	 * Filters the Google Fonts URL.
	 *
	 * @param string $fonts_url Google Fonts URL.
	 */
	return apply_filters( 'amphibious_fonts_url', $fonts_url );
}
