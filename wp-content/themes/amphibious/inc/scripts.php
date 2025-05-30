<?php
/**
 * Theme Scripts
 *
 * @package Amphibious
 */

/**
 * Enqueue scripts and styles.
 */
function amphibious_scripts() {
	/**
	 * Enqueue JS files
	 */

	// Enquire
	wp_enqueue_script( 'enquire', get_template_directory_uri() . '/js/enquire.js', array( 'jquery' ), '2.1.6', true );

	// Fitvids
	wp_enqueue_script( 'fitvids', get_template_directory_uri() . '/js/fitvids.js', array( 'jquery' ), '1.1', true );

	// Superfish Menu
	wp_enqueue_script( 'hover-intent', get_template_directory_uri() . '/js/hover-intent.js', array( 'jquery' ), 'r7', true );
	wp_enqueue_script( 'superfish', get_template_directory_uri() . '/js/superfish.js', array( 'jquery' ), '1.7.10', true );

	// Comment Reply
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	// Keyboard image navigation support
	if ( is_singular() && wp_attachment_is_image() ) {
		wp_enqueue_script( 'amphibious-keyboard-image-navigation', get_template_directory_uri() . '/js/keyboard-image-navigation.js', array( 'jquery' ), '20140127', true );
	}

	// Custom Script
	wp_enqueue_script( 'amphibious-custom', get_template_directory_uri() . '/js/custom.js', array( 'jquery' ), '1.0', true );

	/**
	 * Enqueue CSS files
	 */

	// Bootstrap Grid
	wp_enqueue_style( 'amphibious-bootstrap-grid', get_template_directory_uri() . '/css/bootstrap-grid.css' );

	// Fontawesome 5
	wp_enqueue_style( 'font-awesome-5', get_template_directory_uri() . '/css/fontawesome-all.css' );

	// Fonts
	wp_enqueue_style( 'amphibious-fonts', amphibious_fonts_url(), array(), null );

	// Theme Stylesheet
	wp_enqueue_style( 'amphibious-style', get_stylesheet_uri() );
}
add_action( 'wp_enqueue_scripts', 'amphibious_scripts' );
