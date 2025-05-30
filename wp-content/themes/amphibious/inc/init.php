<?php
/**
 * Theme Bootstrap
 *
 * @package Amphibious
 */

/**
 * Sidebars
 */
require get_template_directory() . '/inc/sidebars.php'; // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude.FileIncludeFound

 /**
 * Scripts
 */
require get_template_directory() . '/inc/scripts.php'; // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude.FileIncludeFound

/**
 * Custom Functions
 */
require get_template_directory() . '/inc/extras.php'; // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude.FileIncludeFound

/**
 * Fonts Functions
 */
require get_template_directory() . '/inc/fonts.php'; // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude.FileIncludeFound

/**
 * Conditional Tags
 */
require get_template_directory() . '/inc/conditional-tags.php'; // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude.FileIncludeFound

/**
 * Template Tags
 */
require get_template_directory() . '/inc/template-tags.php'; // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude.FileIncludeFound

/**
 * Custom Header Feature
 */
require get_template_directory() . '/inc/custom-header.php'; // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude.FileIncludeFound

/**
 * Theme Customizer
 */
require get_template_directory() . '/customizer/controls.php'; // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude.FileIncludeFound
require get_template_directory() . '/customizer/customizer.php'; // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude.FileIncludeFound
