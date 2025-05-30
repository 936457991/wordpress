<?php
/**
 * amphibious functions and definitions
 *
 * @package Amphibious
 */

if ( ! function_exists( 'amphibious_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function amphibious_setup() {

	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on amphibious, use a find and replace
	 * to change 'amphibious' to the name of your theme in all the template files
	 */
	load_theme_textdomain( 'amphibious', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/*
	 * Enable support for custom logo.
	 *
	 * @link https://codex.wordpress.org/Theme_Logo
	 */
	add_theme_support( 'custom-logo', array(
		'height'      => 400,
		'width'       => 580,
		'flex-height' => true,
		'flex-width'  => true,
		'header-text' => array( 'site-title', 'site-description' ),
	) );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
	 */
	add_theme_support( 'post-thumbnails' );

	// Theme Image Sizes
	add_image_size( 'amphibious-featured', 1920, 1000, true );
	add_image_size( 'amphibious-featured-single', 1920, 0, true );

	// This theme uses wp_nav_menu() in one locations.
	register_nav_menus( array (
		'header-menu' => esc_html__( 'Header Menu', 'amphibious' ),
	) );

	// This theme styles the visual editor to resemble the theme style.
	add_editor_style( array ( 'css/editor-style.css', amphibious_fonts_url() ) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array (
		'comment-form', 'comment-list', 'gallery', 'caption'
	) );

	// Setup the WordPress core custom background feature.
	add_theme_support( 'custom-background', apply_filters( 'amphibious_custom_background_args', array (
		'default-color' => 'f2f2f2',
		'default-image' => '',
	) ) );

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );
}
endif; // amphibious_setup
add_action( 'after_setup_theme', 'amphibious_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function amphibious_content_width() {
	// phpcs:ignore WPThemeReview.CoreFunctionality.PrefixAllGlobals.NonPrefixedVariableFound
	$GLOBALS['content_width'] = apply_filters( 'amphibious_content_width', 774 );
}
add_action( 'after_setup_theme', 'amphibious_content_width', 0 );

/**
 * Theme Bootstrap
 */
require get_template_directory() . '/inc/init.php';
