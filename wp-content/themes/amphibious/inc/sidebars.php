<?php
/**
 * Theme Sidebars
 *
 * @package Amphibious
 */

/**
 * Register the widget areas (sidebars) to use widgets in Appearance -> Widgets
 *
 * @link http://codex.wordpress.org/Function_Reference/register_sidebar
 */
function amphibious_register_sidebars() {
	// Widget Areas
	register_sidebar( array(
		'name'          => esc_html__( 'Main Sidebar', 'amphibious' ),
		'id'            => 'sidebar-1',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'amphibious_register_sidebars' );
