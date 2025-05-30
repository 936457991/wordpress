<?php
/**
 * Amphibious Theme Customizer
 *
 * @package Amphibious
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function amphibious_customize_register ( $wp_customize ) {

	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'background_color' )->transport = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

	/**
	 * Theme Options Panel
	 */
	$wp_customize->add_panel( 'amphibious_theme_options', array(
	    'title'     => esc_html__( 'Theme Options', 'amphibious' ),
	    'priority'  => 1,
	) );

	/**
	 * General Options Section
	 */
	$wp_customize->add_section( 'amphibious_general_options', array (
		'title'     => esc_html__( 'General Options', 'amphibious' ),
		'panel'     => 'amphibious_theme_options',
		'priority'  => 10,
		'description' => esc_html__( 'Personalize the settings of your theme.', 'amphibious' ),
	) );

	// Read More Label
	$wp_customize->add_setting ( 'amphibious_read_more_label', array(
		'default'           => amphibious_default( 'amphibious_read_more_label' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );

	$wp_customize->add_control ( 'amphibious_read_more_label', array(
		'label'    => esc_html__( 'Read More Label', 'amphibious' ),
		'section'  => 'amphibious_general_options',
		'priority' => 1,
		'type'     => 'text',
	) );

	// Excerpt Length
	$wp_customize->add_setting ( 'amphibious_excerpt_length', array(
		'default'           => amphibious_default( 'amphibious_excerpt_length' ),
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control ( 'amphibious_excerpt_length', array(
		'label'    => esc_html__( 'Excerpt Length', 'amphibious' ),
		'description' => esc_html__( 'Zero (0) length will not show the excerpt.', 'amphibious' ),
		'section'  => 'amphibious_general_options',
		'priority' => 2,
		'type'     => 'number',
	) );

	/**
	 * Layout Options Section
	 */
	$wp_customize->add_section( 'amphibious_layout_options', array (
		'title'     => esc_html__( 'Layout Options', 'amphibious' ),
		'panel'     => 'amphibious_theme_options',
		'priority'  => 20,
		'description' => esc_html__( 'Personalize the layout settings of your theme.', 'amphibious' ),
	) );

	// Theme Layout
	$wp_customize->add_setting ( 'amphibious_theme_layout', array(
		'default'           => amphibious_default( 'amphibious_theme_layout' ),
		'sanitize_callback' => 'amphibious_sanitize_select',
	) );

	$wp_customize->add_control ( 'amphibious_theme_layout', array(
		'label'    => esc_html__( 'Theme Layout', 'amphibious' ),
		'description' => esc_html__( 'Box layout will be visible at minimum 1200px display', 'amphibious' ),
		'section'  => 'amphibious_layout_options',
		'priority' => 1,
		'type'     => 'select',
		'choices'  => array(
			'wide' => esc_html__( 'Wide', 'amphibious' ),
			'box'  => esc_html__( 'Box',  'amphibious' ),
		),
	) );

	// Main Sidebar Position
	$wp_customize->add_setting ( 'amphibious_sidebar_position', array (
		'default'           => amphibious_default( 'amphibious_sidebar_position' ),
		'sanitize_callback' => 'amphibious_sanitize_select',
	) );

	$wp_customize->add_control ( 'amphibious_sidebar_position', array (
		'label'    => esc_html__( 'Main Sidebar Position', 'amphibious' ),
		'section'  => 'amphibious_layout_options',
		'priority' => 2,
		'type'     => 'select',
		'choices'  => array(
			'right' => esc_html__( 'Right', 'amphibious'),
			'left'  => esc_html__( 'Left',  'amphibious'),
		),
	) );

	/**
	 * Archive Content Options Section
	 */
	$wp_customize->add_section( 'amphibious_archive_content_options', array (
		'title'     => esc_html__( 'Archive Content Options', 'amphibious' ),
		'panel'     => 'amphibious_theme_options',
		'priority'  => 30,
		'description' => esc_html__( 'Content settings of blog, archives and search.', 'amphibious' ),
	) );

	// Post Details Title
	$wp_customize->add_setting ( 'amphibious_archive_co_post_details_title' );

	$wp_customize->add_control(
		new Amphibious_Heading_Control(
			$wp_customize,
			'amphibious_archive_co_post_details_title',
			array(
				'label'           => esc_html__( 'Post Details', 'amphibious' ),
				'section'         => 'amphibious_archive_content_options',
				'priority'        => 1,
				'type'            => 'amphibious-heading',
			)
		)
	);

	// Post Date Control
	$wp_customize->add_setting ( 'amphibious_archive_co_post_date', array (
		'default'           => amphibious_default( 'amphibious_archive_co_post_date' ),
		'transport'         => 'postMessage',
		'sanitize_callback' => 'amphibious_sanitize_checkbox',
	) );

	$wp_customize->add_control ( 'amphibious_archive_co_post_date', array (
		'label'           => esc_html__( 'Display Date', 'amphibious' ),
		'section'         => 'amphibious_archive_content_options',
		'priority'        => 2,
		'type'            => 'checkbox',
	) );

	// Post Categories Control
	$wp_customize->add_setting ( 'amphibious_archive_co_post_categories', array (
		'default'           => amphibious_default( 'amphibious_archive_co_post_categories' ),
		'transport'         => 'postMessage',
		'sanitize_callback' => 'amphibious_sanitize_checkbox',
	) );

	$wp_customize->add_control ( 'amphibious_archive_co_post_categories', array (
		'label'           => esc_html__( 'Display Categories', 'amphibious' ),
		'section'         => 'amphibious_archive_content_options',
		'priority'        => 3,
		'type'            => 'checkbox',
	) );

	// Post Author Control
	$wp_customize->add_setting ( 'amphibious_archive_co_post_author', array (
		'default'           => amphibious_default( 'amphibious_archive_co_post_author' ),
		'transport'         => 'postMessage',
		'sanitize_callback' => 'amphibious_sanitize_checkbox',
	) );

	$wp_customize->add_control ( 'amphibious_archive_co_post_author', array (
		'label'           => esc_html__( 'Display Author', 'amphibious' ),
		'section'         => 'amphibious_archive_content_options',
		'priority'        => 4,
		'type'            => 'checkbox',
	) );

	// Post Comments Control
	$wp_customize->add_setting ( 'amphibious_archive_co_post_comments', array (
		'default'           => amphibious_default( 'amphibious_archive_co_post_comments' ),
		'transport'         => 'postMessage',
		'sanitize_callback' => 'amphibious_sanitize_checkbox',
	) );

	$wp_customize->add_control ( 'amphibious_archive_co_post_comments', array (
		'label'           => esc_html__( 'Display Comments', 'amphibious' ),
		'section'         => 'amphibious_archive_content_options',
		'priority'        => 5,
		'type'            => 'checkbox',
	) );

	// Featured Images Title
	$wp_customize->add_setting ( 'amphibious_archive_co_featured_image_title' );

	$wp_customize->add_control(
		new Amphibious_Heading_Control(
			$wp_customize,
			'amphibious_archive_co_featured_image_title',
			array(
				'label'           => esc_html__( 'Featured Images', 'amphibious' ),
				'section'         => 'amphibious_archive_content_options',
				'priority'        => 6,
				'type'            => 'amphibious-heading',
			)
		)
	);

	// Featured Image Archive Control
	$wp_customize->add_setting ( 'amphibious_archive_co_featured_image', array (
		'default'           => amphibious_default( 'amphibious_archive_co_featured_image' ),
		'sanitize_callback' => 'amphibious_sanitize_checkbox',
	) );

	$wp_customize->add_control ( 'amphibious_archive_co_featured_image', array (
		'label'           => esc_html__( 'Display Featured Image', 'amphibious' ),
		'section'         => 'amphibious_archive_content_options',
		'priority'        => 7,
		'type'            => 'checkbox',
	) );

	// Archive Details Title
	$wp_customize->add_setting ( 'amphibious_archive_co_details_title' );

	$wp_customize->add_control(
		new Amphibious_Heading_Control(
			$wp_customize,
			'amphibious_archive_co_details_title',
			array(
				'label'           => esc_html__( 'Archive Details', 'amphibious' ),
				'section'         => 'amphibious_archive_content_options',
				'priority'        => 8,
				'type'            => 'amphibious-heading',
			)
		)
	);

	// Archive Title Label Control
	$wp_customize->add_setting ( 'amphibious_archive_co_title_label', array (
		'default'           => amphibious_default( 'amphibious_archive_co_title_label' ),
		'sanitize_callback' => 'amphibious_sanitize_checkbox',
	) );

	$wp_customize->add_control ( 'amphibious_archive_co_title_label', array (
		'label'           => esc_html__( 'Display Archive Title Label (Example: Category, Tag, Author etc)', 'amphibious' ),
		'section'         => 'amphibious_archive_content_options',
		'priority'        => 9,
		'type'            => 'checkbox',
	) );

	// Post First Category Control
	$wp_customize->add_setting ( 'amphibious_archive_co_post_first_category', array (
		'default'           => amphibious_default( 'amphibious_archive_co_post_first_category' ),
		'sanitize_callback' => 'amphibious_sanitize_checkbox',
	) );

	$wp_customize->add_control ( 'amphibious_archive_co_post_first_category', array (
		'label'           => esc_html__( 'Display Post First Category', 'amphibious' ),
		'section'         => 'amphibious_archive_content_options',
		'priority'        => 10,
		'type'            => 'checkbox',
	) );

	/**
	 * Single Content Options Section
	 */
	$wp_customize->add_section( 'amphibious_single_content_options', array (
		'title'     => esc_html__( 'Single Content Options', 'amphibious' ),
		'panel'     => 'amphibious_theme_options',
		'priority'  => 40,
		'description' => esc_html__( 'Content settings of single posts or pages.', 'amphibious' ),
	) );

	// Post Details Title
	$wp_customize->add_setting ( 'amphibious_single_co_post_details_title' );

	$wp_customize->add_control(
		new Amphibious_Heading_Control(
			$wp_customize,
			'amphibious_single_co_post_details_title',
			array(
				'label'           => esc_html__( 'Post Details', 'amphibious' ),
				'section'         => 'amphibious_single_content_options',
				'priority'        => 1,
				'type'            => 'amphibious-heading',
			)
		)
	);

	// Post Date Control
	$wp_customize->add_setting ( 'amphibious_single_co_post_date', array (
		'default'           => amphibious_default( 'amphibious_single_co_post_date' ),
		'transport'         => 'postMessage',
		'sanitize_callback' => 'amphibious_sanitize_checkbox',
	) );

	$wp_customize->add_control ( 'amphibious_single_co_post_date', array (
		'label'           => esc_html__( 'Display Date', 'amphibious' ),
		'section'         => 'amphibious_single_content_options',
		'priority'        => 2,
		'type'            => 'checkbox',
	) );

	// Post Categories Control
	$wp_customize->add_setting ( 'amphibious_single_co_post_categories', array (
		'default'           => amphibious_default( 'amphibious_single_co_post_categories' ),
		'transport'         => 'postMessage',
		'sanitize_callback' => 'amphibious_sanitize_checkbox',
	) );

	$wp_customize->add_control ( 'amphibious_single_co_post_categories', array (
		'label'           => esc_html__( 'Display Categories', 'amphibious' ),
		'section'         => 'amphibious_single_content_options',
		'priority'        => 3,
		'type'            => 'checkbox',
	) );

	// Post Tags Control
	$wp_customize->add_setting ( 'amphibious_single_co_post_tags', array (
		'default'           => amphibious_default( 'amphibious_single_co_post_tags' ),
		'transport'         => 'postMessage',
		'sanitize_callback' => 'amphibious_sanitize_checkbox',
	) );

	$wp_customize->add_control ( 'amphibious_single_co_post_tags', array (
		'label'           => esc_html__( 'Display Tags', 'amphibious' ),
		'section'         => 'amphibious_single_content_options',
		'priority'        => 4,
		'type'            => 'checkbox',
	) );

	// Post Author Control
	$wp_customize->add_setting ( 'amphibious_single_co_post_author', array (
		'default'           => amphibious_default( 'amphibious_single_co_post_author' ),
		'transport'         => 'postMessage',
		'sanitize_callback' => 'amphibious_sanitize_checkbox',
	) );

	$wp_customize->add_control ( 'amphibious_single_co_post_author', array (
		'label'           => esc_html__( 'Display Author', 'amphibious' ),
		'section'         => 'amphibious_single_content_options',
		'priority'        => 5,
		'type'            => 'checkbox',
	) );

	// Featured Images Title
	$wp_customize->add_setting ( 'amphibious_single_co_featured_images_title' );

	$wp_customize->add_control(
		new Amphibious_Heading_Control(
			$wp_customize,
			'amphibious_single_co_featured_images_title',
			array(
				'label'           => esc_html__( 'Featured Images', 'amphibious' ),
				'section'         => 'amphibious_single_content_options',
				'priority'        => 6,
				'type'            => 'amphibious-heading',
			)
		)
	);

	// Featured Image Post Control
	$wp_customize->add_setting ( 'amphibious_single_co_featured_image_post', array (
		'default'           => amphibious_default( 'amphibious_single_co_featured_image_post' ),
		'sanitize_callback' => 'amphibious_sanitize_checkbox',
	) );

	$wp_customize->add_control ( 'amphibious_single_co_featured_image_post', array (
		'label'           => esc_html__( 'Display on Posts', 'amphibious' ),
		'section'         => 'amphibious_single_content_options',
		'priority'        => 7,
		'type'            => 'checkbox',
	) );

	// Featured Image Page Control
	$wp_customize->add_setting ( 'amphibious_single_co_featured_image_page', array (
		'default'           => amphibious_default( 'amphibious_single_co_featured_image_page' ),
		'sanitize_callback' => 'amphibious_sanitize_checkbox',
	) );

	$wp_customize->add_control ( 'amphibious_single_co_featured_image_page', array (
		'label'           => esc_html__( 'Display on Pages', 'amphibious' ),
		'section'         => 'amphibious_single_content_options',
		'priority'        => 8,
		'type'            => 'checkbox',
	) );

	// Author Bio Title
	$wp_customize->add_setting ( 'amphibious_single_co_author_bio_title' );

	$wp_customize->add_control(
		new Amphibious_Heading_Control(
			$wp_customize,
			'amphibious_single_co_author_bio_title',
			array(
				'label'           => esc_html__( 'Author Bio', 'amphibious' ),
				'section'         => 'amphibious_single_content_options',
				'priority'        => 9,
				'type'            => 'amphibious-heading',
			)
		)
	);

	// Author Bio Control
	$wp_customize->add_setting ( 'amphibious_single_co_author_bio', array (
		'default'           => amphibious_default( 'amphibious_single_co_author_bio' ),
		'sanitize_callback' => 'amphibious_sanitize_checkbox',
	) );

	$wp_customize->add_control ( 'amphibious_single_co_author_bio', array (
		'label'           => esc_html__( 'Display on posts', 'amphibious' ),
		'section'         => 'amphibious_single_content_options',
		'priority'        => 10,
		'type'            => 'checkbox',
	) );

	/**
	 * Footer Section
	 */
	$wp_customize->add_section( 'amphibious_footer_options', array (
		'title'       => esc_html__( 'Footer Options', 'amphibious' ),
		'panel'       => 'amphibious_theme_options',
		'priority'    => 50,
		'description' => esc_html__( 'Personalize the footer settings of your theme.', 'amphibious' ),
	) );

	// Copyright Control
	$wp_customize->add_setting ( 'amphibious_copyright', array (
		'default'           => amphibious_default( 'amphibious_copyright' ),
		'transport'         => 'postMessage',
		'sanitize_callback' => 'wp_kses_post',
	) );

	$wp_customize->add_control ( 'amphibious_copyright', array (
		'label'    => esc_html__( 'Copyright', 'amphibious' ),
		'section'  => 'amphibious_footer_options',
		'priority' => 1,
		'type'     => 'textarea',
	) );

	// Credit Control
	$wp_customize->add_setting ( 'amphibious_credit', array (
		'default'           => amphibious_default( 'amphibious_credit' ),
		'transport'         => 'postMessage',
		'sanitize_callback' => 'amphibious_sanitize_checkbox',
	) );

	$wp_customize->add_control ( 'amphibious_credit', array (
		'label'    => esc_html__( 'Display Designer Credit', 'amphibious' ),
		'section'  => 'amphibious_footer_options',
		'priority' => 2,
		'type'     => 'checkbox',
	) );
}
add_action( 'customize_register', 'amphibious_customize_register' );

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function amphibious_customize_preview_js() {
	wp_enqueue_script( 'amphibious_customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), '20140120', true );
}
add_action( 'customize_preview_init', 'amphibious_customize_preview_js' );
