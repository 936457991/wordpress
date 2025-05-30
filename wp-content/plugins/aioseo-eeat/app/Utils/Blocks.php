<?php
namespace AIOSEO\Plugin\Addon\Eeat\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Utils\Blocks as CommonBlocks;

/**
 * Block related helper methods.
 *
 * @since 1.0.0
 */
class Blocks extends CommonBlocks {
	/**
	 * Class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * Initializes our blocks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'enqueue_block_editor_assets', [ $this, 'registerBlockEditorAssets' ] );
	}

	/**
	 * Registers a given block.
	 *
	 * @since 1.0.0
	 *
	 * @param  string               $slug Block name, including the namespace.
	 * @param  array                $args List of block arguments.
	 * @return \WP_Block_Type|false       The registered block on success, or false on failure.
	 */
	public function registerBlock( $slug = '', $args = [] ) {
		if ( ! strpos( $slug, '/' ) ) {
			$slug = 'aioseo/' . $slug;
		}

		if ( ! $this->isBlockEditorActive() ) {
			return false;
		}

		// Check if the block requires a minimum WP version.
		global $wp_version; // phpcs:ignore Squiz.NamingConventions.ValidVariableName
		if ( ! empty( $args['wp_min_version'] ) && version_compare( $wp_version, $args['wp_min_version'], '>' ) ) { // phpcs:ignore Squiz.NamingConventions.ValidVariableName
			return false;
		}

		// Checking whether block is registered to ensure it isn't registered twice.
		if ( $this->isRegistered( $slug ) ) {
			return false;
		}

		$defaults = [
			'render_callback' => null,
			'editor_script'   => aioseoEeat()->core->assets->jsHandle( 'src/vue/standalone/blocks/main.js' ),
			'editor_style'    => aioseoEeat()->core->assets->cssHandle( 'src/vue/standalone/blocks/editor.scss' ),
			'attributes'      => null,
			'supports'        => null
		];

		$args = wp_parse_args( $args, $defaults );

		return register_block_type( $slug, $args );
	}

	/**
	 * Registers Gutenberg editor assets.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function registerBlockEditorAssets() {
		aioseoEeat()->core->assets->loadCss( 'src/vue/standalone/blocks/main.js' );

		$dependencies = [
			'wp-annotations',
			'wp-block-editor',
			'wp-blocks',
			'wp-components',
			'wp-element',
			'wp-i18n',
			'wp-data',
			'wp-url',
			'wp-polyfill'
		];

		aioseoEeat()->core->assets->enqueueJs( 'src/vue/standalone/blocks/main.js', $dependencies );
		aioseoEeat()->core->assets->registerCss( 'src/vue/standalone/blocks/editor.scss' );
	}
}