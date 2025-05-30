<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @package hreflang-manager
 */

/**
 * This class should be used to work with the public side of WordPress.
 */
class Dahm_Public {

	/**
	 * The singleton instance of the class.
	 *
	 * @var Dahm_Shared
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
	 */
	private function __construct() {

		// Assign an instance of the plugin info.
		$this->shared = Dahm_Shared::get_instance();

		// Write in front-end head.
		add_action( 'wp_head', array( $this, 'set_hreflang' ) );

		// Prints the log HTML before the closing body tag on the front end.
		add_action( 'wp_footer', array( $this, 'generate_log' ) );

		// Enqueue styles.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
	}

	/**
	 * Create an instance of this class.
	 *
	 * @return self|null
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Write the connections in the 'head' section of the page.
	 *
	 * @return void
	 */
	public function set_hreflang() {

		// Echo the hreflang connections in the 'head' section of the page.
		$this->shared->echo_hreflang_output( 'page_html' );
	}

	/**
	 * Write the log with the connections.
	 *
	 * @return void
	 */
	public function generate_log() {

		// Don't show the log if the current user has no edit_posts capabilities or if the log in not enabled.
		if ( ! current_user_can( get_option( $this->shared->get( 'slug' ) . '_connections_menu_capability' ) ) || ( 1 !== intval( get_option( 'da_hm_show_log' ), 10 ) ) ) {
			return; }

		// Echo the log UI element that includes the connections.

		?>

		<div id="da-hm-log-container">
			<p id="da-hm-log-heading" ><?php esc_html_e( 'The following lines have been added in the HEAD section of this page', 'hreflang-manager' ); ?>:</p>
			<?php
			$this->shared->echo_hreflang_output( 'log_ui_element' );
			?>
		</div>

		<?php
	}

	/**
	 * Enqueue styles.
	 *
	 * @return void
	 */
	public function enqueue_styles() {

		// Enqueue the style used to show the log if the current user has the edit_posts capability and if the log is enabled.
		if ( current_user_can( get_option( $this->shared->get( 'slug' ) . '_connections_menu_capability' ) ) && 1 === ( intval( get_option( 'da_hm_show_log' ), 10 ) ) ) {
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-general', $this->shared->get( 'url' ) . 'public/assets/css/general.css', array(), $this->shared->get( 'ver' ) );
		}
	}
}