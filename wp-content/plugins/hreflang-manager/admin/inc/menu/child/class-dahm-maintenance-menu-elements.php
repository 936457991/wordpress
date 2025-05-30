<?php
/**
 * Class used to implement the back-end functionalities of the "Maintenance" menu.
 *
 * @package hreflang-manager
 */

/**
 * Class used to implement the back-end functionalities of the "Maintenance" menu.
 */
class Dahm_Maintenance_Menu_Elements extends Dahm_Menu_Elements {

	/**
	 * Constructor.
	 *
	 * @param object $shared The shared class.
	 * @param string $page_query_param The page query parameter.
	 * @param string $config The config parameter.
	 */
	public function __construct( $shared, $page_query_param, $config ) {

		parent::__construct( $shared, $page_query_param, $config );

		$this->menu_slug          = 'maintenance';
		$this->slug_plural        = 'maintenance';
		$this->label_singular     = __('Maintenance', 'hreflang-manager');
		$this->label_plural       = __('Maintenance', 'hreflang-manager');
	}

	/**
	 * Process the add/edit form submission of the menu. Specifically the following tasks are performed:
	 *
	 * 1. Sanitization
	 * 2. Validation
	 * 3. Database update
	 *
	 * @return void
	 */
	public function process_form() {

		// Preliminary operations ---------------------------------------------------------------------------------------------.
		global $wpdb;

		if ( isset( $_POST['form_submitted'] ) ) {

			// Nonce verification.
			check_admin_referer( 'dahm_execute_task', 'dahm_execute_task_nonce' );

			// Sanitization ---------------------------------------------------------------------------------------------------.
			$data['task'] = isset( $_POST['task'] ) ? intval( $_POST['task'], 10 ) : null;
			$data['from'] = isset( $_POST['from'] ) ? intval( $_POST['from'], 10 ) : null;
			$data['to']   = isset( $_POST['to'] ) ? intval( $_POST['to'], 10 ) : null;

			// Validation -----------------------------------------------------------------------------------------------------.

			$invalid_data_message = '';
			$invalid_data         = false;

			// validation.
			if ( $data['from'] >= $data['to'] ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid range.', 'hreflang-manager' ),
					'error'
				);
				$invalid_data = true;
			}

			if ( ( $data['to'] - $data['from'] ) > 10000 ) {

				$this->shared->save_dismissible_notice(
					__( "For performance reasons the range can't include more than 10000 items.", 'hreflang-manager' ),
					'error'
				);
				$invalid_data = true;
			}

			if ( false === $invalid_data ) {

				switch ( $data['task'] ) {

					// Delete Connections.
					case 0:
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery
						$query_result = $wpdb->query(
							$wpdb->prepare(
								"DELETE FROM {$wpdb->prefix}da_hm_connect WHERE id >= %d AND id <= %d",
								$data['from'],
								$data['to']
							)
						);

						if ( false !== $query_result ) {

							if ( $query_result > 0 ) {
								$this->shared->save_dismissible_notice(
									intval( $query_result, 10 ) . ' ' . __(
										'connections have been successfully deleted.',
										'hreflang-manager'
									),
									'updated'
								);
							} else {
								$this->shared->save_dismissible_notice(
									__( 'The are no connections in this range.', 'hreflang-manager' ),
									'error'
								);
							}
						}

						break;

				}
			}
		}
	}

	/**
	 * Display the form.
	 *
	 * @return void
	 */
	public function display_custom_content() {

		?>

		<div class="dahm-admin-body">

			<?php

			// Display the dismissible notices.
			$this->shared->display_dismissible_notices();

			// Display the license activation notice.
			$this->shared->display_license_activation_notice();

			?>

			<div class="dahm-main-form">

				<form id="form-maintenance" method="POST"
						action="admin.php?page=da_hm_maintenance"
						autocomplete="off">

				<div class="dahm-main-form__daext-form-section">

					<div class="dahm-main-form__daext-form-section-body">

							<input type="hidden" value="1" name="form_submitted">

							<?php wp_nonce_field( 'dahm_execute_task', 'dahm_execute_task_nonce' ); ?>

							<?php

							// Task.
							$this->select_field(
								'task',
								'Task',
								__( 'The task that should be performed.', 'hreflang-manager' ),
								array(
									'0' => 'Delete Connections',
								),
								null,
								'main'
							);

							// From.
							$this->input_field(
								'from',
								'From',
								'The initial ID of the range.',
								'The initial ID of the range.',
								'1',
								'main'
							);

							// To.
							$this->input_field(
								'to',
								'To',
								'The final ID of the range.',
								'The final ID of the range.',
								'1000',
								'main'
							);

							?>

							<!-- submit button -->
							<div class="daext-form-action">
								<input id="execute-task" class="dahm-btn dahm-btn-primary" type="submit"
										value="<?php esc_attr_e( 'Execute Task', 'hreflang-manager' ); ?>">
							</div>

						</div>

					</div>

				</form>

			</div>

		</div>

		<!-- Dialog Confirm -->
		<div id="dialog-confirm" title="<?php esc_attr_e( 'Execute the task?', 'hreflang-manager' ); ?>" class="daext-display-none">
			<p>
			<?php
			esc_html_e(
				'Multiple database items are going to be deleted. Do you really want to proceed?',
				'hreflang-manager'
			);
			?>
					</p>
		</div>

		<?php
	}
}
