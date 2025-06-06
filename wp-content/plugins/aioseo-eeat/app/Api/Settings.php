<?php
namespace AIOSEO\Plugin\Addon\Eeat\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles all setting related endpoints.
 *
 * @since 1.0.0
 */
class Settings {
	/**
	 * Save options from the front-end.
	 * @NOTE: This function is run via a special hook inside the main settings API class.
	 *
	 * @since 1.0.0
	 *
	 * @param  \WP_REST_Request $request The request
	 * @return \WP_REST_Request          The request.
	 */
	public static function saveChanges( $request ) {
		$body    = $request->get_json_params();
		$options = ! empty( $body['searchAppearanceOptions'] ) ? $body['searchAppearanceOptions'] : [];

		if ( empty( $options ) ) {
			return;
		}

		aioseoEeat()->options->sanitizeAndSave( $options );

		return $request;
	}

	/**
	 * Export settings.
	 *
	 * @since 1.0.0
	 *
	 * @param  \WP_REST_Request  $request  The REST Request.
	 * @param  \WP_REST_Response $response The parent response.
	 * @return \WP_REST_Response           The response.
	 */
	public static function exportSettings( $request, $response ) {
		if ( ! aioseo()->access->hasCapability( 'aioseo_search_appearance_settings' ) ) {
			return $response;
		}

		$body     = $request->get_json_params();
		$settings = ! empty( $body['settings'] ) ? $body['settings'] : [];

		if ( in_array( 'eeat', $settings, true ) ) {
			$response->data['settings']['settings']['eeat'] = aioseoEeat()->options->all();
		}

		return $response;
	}

	/**
	 * Imports settings.
	 *
	 * @since 1.0.0
	 *
	 * @param  \WP_REST_Request  $request  The REST Request.
	 * @param  \WP_REST_Response $response The REST Request.
	 * @return \WP_REST_Response           The response.
	 */
	public static function importSettings( $request, $response ) {
		if ( ! aioseo()->access->hasCapability( 'aioseo_search_appearance_settings' ) ) {
			return $response;
		}

		$file = $request->get_file_params()['file'];
		if (
			empty( $file['tmp_name'] ) ||
			empty( $file['type'] ) ||
			'application/json' !== $file['type']
		) {
			return $response;
		}

		$contents = aioseo()->core->fs->getContents( $file['tmp_name'] );

		// Since this could be any file, we need to pretend like every variable here is missing.
		$contents = json_decode( $contents, true );
		if ( empty( $contents ) ) {
			return $response;
		}

		if ( isset( $contents['settings']['eeat'] ) ) {
			aioseoEeat()->options->sanitizeAndSave( $contents['settings']['eeat'] );
		}

		return $response;
	}

	/**
	 * Reset settings.
	 *
	 * @since 1.0.0
	 *
	 * @param  \WP_REST_Request  $request  The REST Request.
	 * @param  \WP_REST_Response $response The REST Request.
	 * @return \WP_REST_Response           The response.
	 */
	public static function resetSettings( $request, $response ) {
		if ( ! aioseo()->access->hasCapability( 'aioseo_search_appearance_settings' ) ) {
			return $response;
		}

		$body     = $request->get_json_params();
		$settings = ! empty( $body['settings'] ) ? $body['settings'] : [];

		foreach ( $settings as $setting ) {
			if ( 'eeat' === $setting ) {
				aioseoEeat()->options->reset();
				break;
			}
		}

		return $response;
	}
}