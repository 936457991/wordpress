<?php
/**
 * Customizer Controls
 *
 * @package Amphibious
 */

/**
 * Theme Mod Defaults
 *
 * @param string $theme_mod - Theme modification name.
 * @return mixed
 */
function amphibious_default( $theme_mod = '' ) {

	$default = array (
		'amphibious_read_more_label'                => esc_html_x( 'Read More', 'Read More Label', 'amphibious' ),
		'amphibious_excerpt_length'                 => 25,
		'amphibious_theme_layout'                   => 'wide',
		'amphibious_sidebar_position'               => 'right',
		'amphibious_archive_co_post_date'           => true,
		'amphibious_archive_co_post_categories'     => true,
		'amphibious_archive_co_post_author'         => true,
		'amphibious_archive_co_post_comments'       => true,
		'amphibious_archive_co_featured_image'      => true,
		'amphibious_archive_co_title_label'         => false,
		'amphibious_archive_co_post_first_category' => true,
		'amphibious_single_co_post_date'            => true,
		'amphibious_single_co_post_categories'      => true,
		'amphibious_single_co_post_tags'            => true,
		'amphibious_single_co_post_author'          => true,
		'amphibious_single_co_featured_image_post'  => true,
		'amphibious_single_co_featured_image_page'  => true,
		'amphibious_single_co_author_bio'           => true,
		'amphibious_copyright'                      => '',
		'amphibious_credit'                         => true,
	);

	return ( isset ( $default[$theme_mod] ) ? $default[$theme_mod] : '' );
}

/**
 * Theme Mod Wrapper
 *
 * @param string $theme_mod - Name of the theme mod to retrieve.
 * @return mixed
 */
function amphibious_mod( $theme_mod = '' ) {
	return get_theme_mod( $theme_mod, amphibious_default( $theme_mod ) );
}

/**
 * New Control Type: Heading
 * @see wp-includes/class-wp-customize-control.php
 */
if ( class_exists( 'WP_Customize_Control' ) ) {
	class Amphibious_Heading_Control extends WP_Customize_Control {
		/**
		 * @access public
		 * @var string
		 */
		public $type = 'amphibious-heading';

		/**
		 * Label for the control.
		 */
		public $label = '';

		/**
		 * Description for the control.
		 */
		public $description = '';

		/**
		 * Render the control's content.
		 */
		public function render_content() {
		?>
			<?php if ( ! empty( $this->label ) ) : ?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php endif; ?>
			<?php if ( ! empty( $this->description ) ) : ?>
				<span class="description customize-control-description"><?php echo wp_kses_post( $this->description ); ?></span>
			<?php endif; ?>
		<?php
		}
	}
}

/**
 * Sanitize Select.
 *
 * @param string $input Slug to sanitize.
 * @param WP_Customize_Setting $setting Setting instance.
 * @return string Sanitized slug if it is a valid choice; otherwise, the setting default.
 */
function amphibious_sanitize_select( $input, $setting ) {

	// Ensure input is a slug.
	$input = sanitize_key( $input );

	// Get list of choices from the control associated with the setting.
	$choices = $setting->manager->get_control( $setting->id )->choices;

	// If the input is a valid key, return it; otherwise, return the default.
	return ( array_key_exists( $input, $choices ) ? $input : $setting->default );
}

/**
 * Sanitize the checkbox.
 *
 * @param bool $checked Whether the checkbox is checked.
 * @return bool Whether the checkbox is checked.
 */
function amphibious_sanitize_checkbox( $checked ) {
	return ( ( isset( $checked ) && true === $checked ) ? true : false );
}

/**
 * Sanitize Unfiltered HTML.
 *
 * @param string $html HTML to sanitize.
 * @return string Sanitized HTML.
 */
function amphibious_sanitize_unfiltered_html( $html ) {
	return ( current_user_can( 'unfiltered_html' ) ? $html : wp_kses_post( $html ) );
}
