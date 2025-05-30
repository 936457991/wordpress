<?php
/**
 * This class includes method used by the hreflang checker.
 *
 * @package hreflang-manager
 */

/**
 * This class includes method used by the hreflang checker.
 */
class Dahm_Hreflang_Checker {

	/**
	 * The singleton instance of the class.
	 *
	 * @var null
	 */
	protected static $instance = null;

	/**
	 * An instance of the shared class.
	 *
	 * @var Dahm_Shared|null
	 */
	private $shared = null;

	/**
	 * Constructor.
	 *
	 * @param Object $shared An instance of the shared class.
	 */
	public function __construct( $shared ) {

		$this->shared = $shared;
	}

	/**
	 * Create a singleton instance of the class.
	 *
	 * @param Object $shared An instance of the shared class.
	 *
	 * @return self|null
	 */
	public static function get_instance( $shared ) {

		if ( null === self::$instance ) {
			self::$instance = new self( $shared );
		}

		return self::$instance;
	}

	/**
	 * Generate a hreflang checker issue if the URL doesn't exist.
	 *
	 * @param String $status_code The HTTP status code of the URL.
	 * @param Array  $hreflang_checker_queue An array containing the queued data to be checked.
	 *
	 * @return void
	 */
	public function invalid_http_response( $status_code, $hreflang_checker_queue ) {

		global $wpdb;

		if ( 200 !== $status_code ) {

			$url_with_invalid_http_response = strlen( $hreflang_checker_queue['alternate_url'] ) > 0 ? $hreflang_checker_queue['alternate_url'] : $hreflang_checker_queue['url_to_connect'];

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->insert(
				$wpdb->prefix . 'da_hm_hreflang_checker_issue',
				array(
					'alternate_url'  => $hreflang_checker_queue['alternate_url'],
					'issue_type'     => __( 'Invalid HTTP Response', 'hreflang-manager' ),
					'severity'       => 'error',
					'details'        => $url_with_invalid_http_response . __( ' returned an HTTP response other than 200 OK, indicating potential access or content issues.', 'hreflang-manager' ),
					'date'           => current_time( 'mysql' ),
					'url_to_connect' => $hreflang_checker_queue['url_to_connect'],
				)
			);

		}
	}

	/**
	 * Duplicate Hreflang Entries
	 *
	 * Detects multiple entries for the same URL.
	 *
	 * @param String $status_code The HTTP status code of the URL.
	 * @param Array  $hreflang_checker_queue An array containing the queued data to be checked.
	 * @param Array  $hreflang_data An array with the hreflang data of the URL.
	 */
	public function duplicate_hreflang_entries( $status_code, $hreflang_checker_queue, $hreflang_data ) {

		global $wpdb;

		$href_a = array();

		/**
		 * Iterate over $hreflang_data for each iteration check that the href value is not already present in any other
		 * $hreflang_data['href']. If it is present, add data to the arrays later used to generate the issue.
		 */
		$hreflang_a_issues = array();
		foreach ( $hreflang_data as $hreflang ) {

			$hreflang_a_duplicates = array();

			/**
			 * Search if the URL $hreflang['href'] is present in more than one item of the $hreflang_data[] array in
			 * the ['href'] key.
			 *
			 * If present, add the URL and an array with the $hreflang['hreflang'] values to the $hreflang_a_issue
			 * array.
			 *
			 * Note. Do not add duplicates to the $hreflang_a_issue array.
			 */
			foreach ( $hreflang_data as $hreflang_single ) {
				if ( $hreflang['href'] === $hreflang_single['href'] && 'x-default' !== $hreflang_single['hreflang'] ) {
					$hreflang_a_duplicates[] = $hreflang_single;
				}
			}

			if ( count( $hreflang_a_duplicates ) > 1 ) {

				// Iterate over $hreflang_a_issues to check if the issue is already present.
				$issue_already_present = false;
				foreach ( $hreflang_a_issues as $hreflang_a_issues_single ) {
					foreach ( $hreflang_a_issues_single as $hreflang_a_issues_single_single ) {
						if ( $hreflang_a_issues_single_single['href'] === $hreflang_a_duplicates[0]['href'] ) {
							$issue_already_present = true;
							break;
						}
					}
				}

				if ( ! $issue_already_present ) {
					$hreflang_a_issues[] = $hreflang_a_duplicates;

				}
			}
		}

		if ( count( $hreflang_a_issues ) > 0 ) {

			foreach ( $hreflang_a_issues as $hreflang_a_issues_single ) {

				/**
				 * Create a message like this "/fr/ and /fr-fr/" from the array $hreflang_a_occurrences
				 */
				// implode all the items in $hreflang_a_issues_single array in the ['hreflang'] key.
				$hreflang_a_issues_single_str = implode( ' and ', array_column( $hreflang_a_issues_single, 'hreflang' ) );

				/**
				 * Create a "Details" message with dynamically generated values.
				 *
				 * E.g.: "Conflicting hreflang=fr for https://example.com/en/ → Points to both /fr/ and /fr-fr/"
				 */
				$details =
					__( 'Conflicting hreflang=', 'hreflang-manager' ) .
					$hreflang_a_issues_single[0]['hreflang'] . ' ' .
					__( 'for', 'hreflang-manager' ) . ' ' .
					$hreflang_a_issues_single[0]['href'] . ' ' .
					__( '→ Points to both', 'hreflang-manager' ) . ' ' . $hreflang_a_issues_single_str . '.';

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->insert(
					$wpdb->prefix . 'da_hm_hreflang_checker_issue',
					array(
						'alternate_url'  => $hreflang_checker_queue['alternate_url'],
						'issue_type'     => __( 'Duplicate Hreflang Entries', 'hreflang-manager' ),
						'severity'       => 'error',
						'details'        => $details,
						'date'           => current_time( 'mysql' ),
						'url_to_connect' => $hreflang_checker_queue['url_to_connect'],
					)
				);

			}
		}
	}

	/**
	 * Missing Self-Referencing Hreflang.
	 *
	 * Checks if each page has a hreflang tag for itself.
	 *
	 * 1. Check if $hreflang_checker_queue['url'] is not present in any of the $hreflang_data['href'] values.
	 *
	 * @param String $status_code The HTTP status code of the URL.
	 * @param Array  $hreflang_checker_queue An array containing the queued data to be checked.
	 * @param Array  $hreflang_data An array with the hreflang data of the URL.
	 */
	public function missing_self_referencing_hreflang( $status_code, $hreflang_checker_queue, $hreflang_data ) {

		global $wpdb;

		if ( 200 === $status_code ) {
			$self_referencing_tag_is_present = false;
			foreach ( $hreflang_data as $hreflang ) {
				if ( $hreflang['href'] === $hreflang_checker_queue['url_to_connect'] ) {
					$self_referencing_tag_is_present = true;
					break;
				}
			}
			if ( ! $self_referencing_tag_is_present ) {

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->insert(
					$wpdb->prefix . 'da_hm_hreflang_checker_issue',
					array(
						'alternate_url'  => $hreflang_checker_queue['alternate_url'],
						'issue_type'     => __( 'Missing Self-Referencing Hreflang', 'hreflang-manager' ),
						'severity'       => 'error',
						'details'        => __( 'Self-referencing hreflang missing for', 'hreflang-manager' ) . ' ' . $hreflang_checker_queue['url_to_connect'],
						'date'           => current_time( 'mysql' ),
						'url_to_connect' => $hreflang_checker_queue['url_to_connect'],
					)
				);

			}
		}
	}

	/**
	 * Incorrect Language, Script, or Region Codes.
	 *
	 * Verifies if language-region codes follow ISO 639-1 (language) + ISO 15924 (script) + ISO 3166-1 Alpha-2
	 * (region).
	 *
	 * @param String $status_code The HTTP status code of the URL.
	 * @param Array  $hreflang_checker_queue An array containing the queued data to be checked.
	 * @param Array  $hreflang_data An array with the hreflang data of the URL.
	 */
	public function incorrect_language_script_region_codes( $status_code, $hreflang_checker_queue, $hreflang_data ) {

		global $wpdb;

		foreach ( $hreflang_data as $hreflang ) {

			if ( 'x-default' !== $hreflang['hreflang'] ) {

				$hreflang_attribute_a = explode( '-', $hreflang['hreflang'] );

				if ( 1 === count( $hreflang_attribute_a ) ) {
					// When one single code is present, it is considered a language code.
					$language_code = $hreflang_attribute_a[0];
					$script_code   = null;
					$region_code   = null;
				} elseif ( 2 === count( $hreflang_attribute_a ) && 4 === strlen( $hreflang_attribute_a[1] ) ) {
					/**
					 * When two codes are present, and the second one is 4 characters long, the codes are considered as
					 * language and script codes.
					 */
					$language_code = $hreflang_attribute_a[0];
					$script_code   = $hreflang_attribute_a[1];
					$region_code   = null;
				} elseif ( 2 === count( $hreflang_attribute_a ) && 4 !== strlen( $hreflang_attribute_a[1] ) ) {
					/**
					 * When two codes are present, and the second one is not 4 characters long, the codes are considered
					 * as language and region codes.
					 */
					$language_code = $hreflang_attribute_a[0];
					$script_code   = null;
					$region_code   = $hreflang_attribute_a[1];
				} elseif ( 3 === count( $hreflang_attribute_a ) ) {
					/**
					 * When three codes are present, the codes are considered as language, script, and region codes.
					 */
					$language_code = $hreflang_attribute_a[0];
					$script_code   = $hreflang_attribute_a[1];
					$region_code   = $hreflang_attribute_a[2];
				} else {
					/**
					 * In all other cases, the codes are considered as invalid and no ISO code check is performed.
					 */
					continue;
				}

				/**
				 * Check if the language code is not valid by checking if it is not present in the ISO 639-1 list
				 * available in $shared->da_hm_language
				 */
				$array_language = get_option( 'da_hm_language' );
				if ( isset( $language_code ) && ! in_array( $language_code, $array_language, true ) ) {

					// phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$wpdb->insert(
						$wpdb->prefix . 'da_hm_hreflang_checker_issue',
						array(
							'alternate_url'  => $hreflang_checker_queue['alternate_url'],
							'issue_type'     => __( 'Incorrect Language Code', 'hreflang-manager' ),
							'severity'       => 'error',
							'details'        => 'hreflang=' . $hreflang['hreflang'] . ' ' . __( 'is invalid. Use a valid ISO_639-1 language code.', 'hreflang-manager' ),
							'date'           => current_time( 'mysql' ),
							'url_to_connect' => $hreflang_checker_queue['url_to_connect'],
						)
					);

				}

				/**
				 * Check if the script code is not valid by checking if it is not present in the ISO 15924 list
				 * available in $shared->da_hm_script
				 */
				$array_script = get_option( 'da_hm_script' );
				if ( isset( $script_code ) && ! in_array( $script_code, $array_script, true ) ) {

					// phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$wpdb->insert(
						$wpdb->prefix . 'da_hm_hreflang_checker_issue',
						array(
							'alternate_url'  => $hreflang_checker_queue['alternate_url'],
							'issue_type'     => __( 'Incorrect Script Code', 'hreflang-manager' ),
							'severity'       => 'error',
							'details'        => 'hreflang=' . $hreflang['hreflang'] . ' ' . __( 'is invalid. Use a valid ISO 15924 script code.', 'hreflang-manager' ),
							'date'           => current_time( 'mysql' ),
							'url_to_connect' => $hreflang_checker_queue['url_to_connect'],
						)
					);
				}

				/**
				 * Check if the region code is not valid by checking if it is not present in the ISO 3166-1 alpha-2 list
				 * available in $shared->da_hm_locale
				 */
				$array_locale = get_option( 'da_hm_locale' );
				if ( isset( $region_code ) && ! in_array( $region_code, $array_locale, true ) ) {

					// phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$wpdb->insert(
						$wpdb->prefix . 'da_hm_hreflang_checker_issue',
						array(
							'alternate_url'  => $hreflang_checker_queue['alternate_url'],
							'issue_type'     => __( 'Incorrect Region Code', 'hreflang-manager' ),
							'severity'       => 'error',
							'details'        => 'hreflang=' . $hreflang['hreflang'] . ' ' . __( 'is invalid. Use a valid ISO 3166-1 alpha-2 region code.', 'hreflang-manager' ),
							'date'           => current_time( 'mysql' ),
							'url_to_connect' => $hreflang_checker_queue['url_to_connect'],
						)
					);

				}
			}
		}
	}

	/**
	 * Missing Hreflang X-Default
	 *
	 * Ensures a hreflang x-default exists if the site targets multiple languages.
	 *
	 * @param String $status_code The HTTP status code of the URL.
	 * @param Array  $hreflang_checker_queue An array containing the queued data to be checked.
	 * @param Array  $hreflang_data An array with the hreflang data of the URL.
	 */
	public function missing_hreflang_x_default( $status_code, $hreflang_checker_queue, $hreflang_data ) {

		global $wpdb;

		$language_count                = 0;
		$x_default_hreflang_is_present = false;

		foreach ( $hreflang_data as $hreflang ) {

			$hreflang_attribute = $hreflang['hreflang'];

			// Check for x-default hreflang.
			if ( 'x-default' === $hreflang_attribute ) {
				$x_default_hreflang_is_present = true;
				break;
			}
		}

		foreach ( $hreflang_data as $hreflang ) {

			$hreflang_attribute_a = explode( '-', $hreflang['hreflang'] );

			// Count valid languages.
			if ( isset( $hreflang_attribute_a[0] ) ) {
				$array_language = get_option( 'da_hm_language' );
				if ( in_array( $hreflang_attribute_a[0], $array_language, true ) ) {
					++$language_count;
				}
			}
		}

		// Add issue if x-default hreflang is missing and there are multiple languages.
		if ( ! $x_default_hreflang_is_present && $language_count > 1 ) {

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->insert(
				$wpdb->prefix . 'da_hm_hreflang_checker_issue',
				array(
					'alternate_url'  => $hreflang_checker_queue['alternate_url'],
					'issue_type'     => __( 'Missing X-Default Hreflang', 'hreflang-manager' ),
					'severity'       => 'warning',
					'details'        => __( 'No x-default tag found for', 'hreflang-manager' ) . ' ' . $hreflang_checker_queue['url_to_connect'] . __( '.', 'hreflang-manager' ),
					'date'           => current_time( 'mysql' ),
					'url_to_connect' => $hreflang_checker_queue['url_to_connect'],
				)
			);

		}
	}

	/**
	 * Add the referenced URLs to the hreflang checker queue.
	 *
	 * @param String $status_code The HTTP status code of the URL.
	 * @param Array  $hreflang_checker_queue An array containing the queued data to be checked.
	 * @param Array  $hreflang_data An array with the hreflang data of the URL.
	 */
	public function add_referenced_urls_to_queue( $status_code, $hreflang_checker_queue, $hreflang_data ) {

		global $wpdb;

		foreach ( $hreflang_data as $hreflang ) {

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->insert(
				$wpdb->prefix . 'da_hm_hreflang_checker_queue',
				array(
					'alternate_url'  => $hreflang['href'],
					'url_to_connect' => $hreflang_checker_queue['url_to_connect'],
				)
			);

		}
	}

	/**
	 * Custom. Check link back compliance.
	 *
	 * Check link back compliance.
	 *
	 * // Note: Apply only to referenced URLs.
	 *
	 * todo: The URL of the primary page of the referenced URL should be stored in the db table (only for referenced
	 * URLs) '_hreflang_checked_queue'. This allow to properly compare the hreflang data of the primary URL with the
	 * hreflang data of the referenced URL.
	 *
	 * @param String $status_code The HTTP status code of the URL.
	 * @param Array  $hreflang_checker_queue An array containing the queued data to be checked.
	 * @param Array  $hreflang_data An array with the hreflang data of the URL.
	 */
	public function missing_return_link( $status_code, $hreflang_checker_queue, $hreflang_data ) {

		global $wpdb;

			$checker_request_timeout = intval( get_option( $this->shared->get( 'slug' ) . '_checker_request_timeout' ), 10 );

			// Configure the wp_remote_get() arguments.
			$wp_remote_get_args = array(
				'timeout'     => $checker_request_timeout,
				'redirection' => 0,
			);

			// Check the http response of the URL.
			$response    = wp_remote_get( $hreflang_checker_queue['url_to_connect'], $wp_remote_get_args );
			$status_code = wp_remote_retrieve_response_code( $response );

			// Extract the hreflang data from the hreflang tags found in the response body.
			$hreflang_data_url_to_connect = $this->extract_hreflang_data( $response );

			$has_link_back = false;

			foreach ( $hreflang_data as $hreflang_data_single ) {

				foreach ( $hreflang_data_url_to_connect as $hreflang_data_url_to_connect_single ) {

					if ( $hreflang_data_single['hreflang'] === $hreflang_data_url_to_connect_single['hreflang'] &&
						$hreflang_data_single['href'] === $hreflang_data_url_to_connect_single['href'] ) {

						$has_link_back = true;
						break;

					}
				}
			}

			if ( ! $has_link_back ) {

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->insert(
					$wpdb->prefix . 'da_hm_hreflang_checker_issue',
					array(
						'alternate_url'  => $hreflang_checker_queue['alternate_url'],
						'issue_type'     => __( 'Missing Return Link', 'hreflang-manager' ),
						'severity'       => 'error',
						'details'        => $hreflang_checker_queue['alternate_url'] . __( ' does not link back to ', 'hreflang-manager' ) . $hreflang_checker_queue['url_to_connect'] . __( ' with the same hreflang attribute.', 'hreflang-manager' ),
						'date'           => current_time( 'mysql' ),
						'url_to_connect' => $hreflang_checker_queue['url_to_connect'],
					)
				);

			}
	}

	/**
	 * Canonical and Hreflang Conflict.
	 *
	 * Detects cases where the canonical URL contradicts the hreflang tag.
	 *
	 * Applied to both primary and referenced URLs.
	 *
	 * @param String $status_code The HTTP status code of the URL.
	 * @param Array  $hreflang_checker_queue An array containing the queued data to be checked.
	 * @param Array  $hreflang_data An array with the hreflang data of the URL.
	 * @param Array  $response An array containing the response headers, body, and additional request information.
	 */
	public function canonical_and_hreflang_conflict( $status_code, $hreflang_checker_queue, $hreflang_data, $response ) {

		global $wpdb;

		// Extract the canonical tag URL found in the response body.
		$canonical_tag_url = $this->extract_canonical_tag_url( $response );

		// Do not proceed if the canonical tag URL is not found.
		if ( null === $canonical_tag_url ) {
			return;
		}

		// Check if canonical URL is present in the href attribute of the hreflang tags stored in $hreflang_data.
		$canonical_tag_is_present = false;
		foreach ( $hreflang_data as $hreflang ) {
			if ( $hreflang['href'] === $canonical_tag_url ) {
				$canonical_tag_is_present = true;
				break;
			}
		}

		if ( 200 === $status_code ) {
			if ( ! $canonical_tag_is_present ) {

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->insert(
					$wpdb->prefix . 'da_hm_hreflang_checker_issue',
					array(
						'alternate_url'  => $hreflang_checker_queue['alternate_url'],
						'issue_type'     => __( 'Canonical and Hreflang Conflict', 'hreflang-manager' ),
						'severity'       => 'error',
						'details'        => __( 'Canonical URL is', 'hreflang-manager' ) . ' ' . $canonical_tag_url . ' ' . __( 'but the matching hreflang tag is not present.', 'hreflang-manager' ),
						'date'           => current_time( 'mysql' ),
						'url_to_connect' => $hreflang_checker_queue['url_to_connect'],
					)
				);

			}
		}
	}

	/**
	 * Extract the canonical tag from the response body.
	 *
	 * @param Array $response An array containing the response headers, body, and additional request information.
	 *
	 * @return string|null The canonical URL or null if not found.
	 */
	public function extract_canonical_tag_url( $response ) {

		$canonical_url = null;

		// Check if response is different from 200.
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return $canonical_url;
		}

		// Check if the response body is not empty.
		if ( ! is_wp_error( $response ) &&
			200 === wp_remote_retrieve_response_code( $response ) &&
			isset( $response['body'] ) ) {
			$body = $response['body'];

			// Use DOMDocument to parse the HTML.
			$dom = new DOMDocument();
			$dom->loadHTML( $body );

			// Get all link elements.
			$links = $dom->getElementsByTagName( 'link' );

			// Iterate over each link element.
			foreach ( $links as $link ) {
				// Check if the link element has the rel attribute set to canonical.
				if ( $link->hasAttribute( 'rel' ) && $link->getAttribute( 'rel' ) === 'canonical' ) {
					$canonical_url = $link->getAttribute( 'href' );
					break;
				}
			}
		}

		return $canonical_url;
	}

	/**
	 * Extract the hreflang data from the hreflang tags found in the response body.
	 *
	 * @param Array $response An array containing the response headers, body, and additional request information.
	 *
	 * @return Array An array with the hreflang data of the URL.
	 */
	public function extract_hreflang_data( $response ) {

		$hreflang_data = array();

		// Check if response is different from 200.
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return $hreflang_data;
		}

		// Check if the response body is not empty.
		if ( ! is_wp_error( $response ) &&
			200 === wp_remote_retrieve_response_code( $response ) &&
			isset( $response['body'] ) ) {
			$body = $response['body'];

			// Use DOMDocument to parse the HTML.
			$dom = new DOMDocument();
			libxml_use_internal_errors( true ); // Suppress warnings.
			$dom->loadHTML( $body );
			libxml_clear_errors(); // Clear any warnings.

			// Get all link elements.
			$links = $dom->getElementsByTagName( 'link' );

			// Iterate over each link element.
			foreach ( $links as $link ) {
				// Check if the link element has the hreflang attribute.
				if ( $link->hasAttribute( 'hreflang' ) ) {
					$hreflang = $link->getAttribute( 'hreflang' );
					$href     = $link->getAttribute( 'href' );

					// Add the hreflang and href to the hreflang_data array.
					$hreflang_data[] = array(
						'hreflang' => $hreflang,
						'href'     => $href,
					);
				}
			}
		}

		return $hreflang_data;
	}

	/**
	 * Generate all the hreflang checker issues related to the URL.
	 *
	 * @param Array $hreflang_checker_queue An array containing the queued data to be checked.
	 * @param Array $response An array containing the response headers, body, and additional request information.
	 * @param Int   $status_code The HTTP status code of the URL.
	 *
	 * @return void
	 */
	public function generate_hreflang_checker_issues_of_url( $hreflang_checker_queue, $response, $status_code ) {

		// Extract the hreflang data from the hreflang tags found in the response body.
		$hreflang_data = $this->extract_hreflang_data( $response );

		if ( strlen( $hreflang_checker_queue['alternate_url'] ) > 0 ) {

			/**
			 * Checks performed on queued alternate URLs. --------------------------------------------------------------
			 */

			// Check: Invalid HTTP Response.
			if ( 1 === intval( get_option( 'da_hm_checker_invalid_http_response' ), 10 ) ) {
				$this->invalid_http_response( $status_code, $hreflang_checker_queue );
			}

			// Check: Check link back compliance.
			if ( 1 === intval( get_option( 'da_hm_checker_missing_return_link' ), 10 ) ) {
				$this->missing_return_link( $status_code, $hreflang_checker_queue, $hreflang_data );
			}
		} else {

			/**
			 * Checks performed on queued URLs to Connect. -------------------------------------------------------------
			 */

			// Check: Invalid HTTP Response.
			if ( 1 === intval( get_option( 'da_hm_checker_invalid_http_response' ), 10 ) ) {
				$this->invalid_http_response( $status_code, $hreflang_checker_queue, $hreflang_data );
			}

			// Check: Canonical and Hreflang Conflict.
			if ( 1 === intval( get_option( 'da_hm_checker_canonical_and_hreflang_conflict' ), 10 ) ) {
				$this->canonical_and_hreflang_conflict( $status_code, $hreflang_checker_queue, $hreflang_data, $response );
			}

			// Check: Incorrect Language, Script, or Region Codes.
			if ( 1 === intval( get_option( 'da_hm_checker_incorrect_language_script_region_codes' ), 10 ) ) {
				$this->incorrect_language_script_region_codes( $status_code, $hreflang_checker_queue, $hreflang_data );
			}

			// Check: Duplicate Hreflang Entries.
			if ( 1 === intval( get_option( 'da_hm_checker_duplicate_hreflang_entries' ), 10 ) ) {
				$this->duplicate_hreflang_entries( $status_code, $hreflang_checker_queue, $hreflang_data );
			}

			// Check: Missing Hreflang X-Default.
			if ( 1 === intval( get_option( 'da_hm_checker_missing_hreflang_x_default' ), 10 ) ) {
				$this->missing_hreflang_x_default( $status_code, $hreflang_checker_queue, $hreflang_data );
			}

			// Check: Missing Self-Referencing Hreflang.
			if ( 1 === intval( get_option( 'da_hm_checker_missing_self_referencing_hreflang' ), 10 ) ) {
				$this->missing_self_referencing_hreflang( $status_code, $hreflang_checker_queue, $hreflang_data );
			}

			// Add the referenced URLs to the hreflang checker queue.
			$this->add_referenced_urls_to_queue( $status_code, $hreflang_checker_queue, $hreflang_data );

		}
	}

	/**
	 * Get the HTTP response status code of a limited number of URLs saved in the "http_status" db table.
	 *
	 * Note that:
	 *
	 * - The number of URLs checked per run of this function is set in the "http_status_checks_per_iteration" option.
	 * - The timeout of the HTTP request is set in the "http_status_request_timeout" option.
	 * - This function runs in a WP-Cron job.
	 */
	public function generate_hreflang_checker_issues() {

		$checker_checks_per_iteration = intval( get_option( $this->shared->get( 'slug' ) . '_checker_checks_per_iteration' ), 10 );
		$checker_request_timeout      = intval( get_option( $this->shared->get( 'slug' ) . '_checker_request_timeout' ), 10 );

		// Configure the wp_remote_get() arguments.
		$wp_remote_get_args = array(
			'timeout'     => $checker_request_timeout,
			'redirection' => 0,
		);

		// Iterate through the url available in the http_status db table.
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$hreflang_checker_queue_a = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}da_hm_hreflang_checker_queue WHERE checked = 0 ORDER BY hreflang_checker_queue_id ASC LIMIT %d",
				$checker_checks_per_iteration
			),
			ARRAY_A
		);

		// Iterate through $http_status_a.
		foreach ( $hreflang_checker_queue_a as $key => $hreflang_checker_queue ) {

			// Check the http response of the URL.
			$url_to_check = strlen( $hreflang_checker_queue['alternate_url'] ) > 0 ? $hreflang_checker_queue['alternate_url'] : $hreflang_checker_queue['url_to_connect'];
			$response     = wp_remote_get( $url_to_check, $wp_remote_get_args );
			$status_code  = wp_remote_retrieve_response_code( $response );

			$this->generate_hreflang_checker_issues_of_url(
				$hreflang_checker_queue,
				$response,
				$status_code
			);

			// Update the checked field to 1 in the "_hreflang_checker_queue" db table for the iterated id.
			global $wpdb;

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->update(
				$wpdb->prefix . 'da_hm_hreflang_checker_queue',
				array(
					'checked' => 1,
				),
				array(
					'hreflang_checker_queue_id' => $hreflang_checker_queue['hreflang_checker_queue_id'],
				)
			);

		}
	}
}
