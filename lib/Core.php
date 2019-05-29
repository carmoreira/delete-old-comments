<?php

namespace Arte\WP\Plugin\DeleteComments;

use Arte\WP\Plugin\DeleteComments\Cron;

/**
 * The core class, where logic is defined.
 */
class Core {

	/**
	 * Unique identifier (slug)
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Current version.
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * Path to main file
	 *
	 * @var string
	 */
	protected $file_path;

	/**
	 * Setup the class variables
	 *
	 * @param string $name      Plugin name.
	 * @param string $version   Plugin version. Use semver.
	 * @param string $file_path Plugin file path
	 */
	public function __construct( $name, $version, $file_path ) {
		$this->name      = $name;
		$this->version   = $version;
		$this->file_path = $file_path;
	}

	/**
	 * Get the identifier, also used for i18n domain.
	 *
	 * @return string The unique identifier (slug)
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get the current version.
	 *
	 * @return string The current version.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Start the logic for this plugins.
	 *
	 * Runs on 'plugins_loaded'.
	 */
	public function init() {

		$this->set_locale();
		$this->setup_cron_jobs();

		// adds options in the comments/discussion page
		add_action( 'admin_init', array( $this, 'setup_options' ) );

	}

	/**
	 * Load translations
	 */
	private function set_locale() {
		$i18n = new Plugin\I18n();
		$i18n->set_domain( $this->name );
		$i18n->load_plugin_textdomain( dirname( $this->file_path ) );
	}

	/**
	 * Schedule the cron jobs.
	 */
	private function setup_cron_jobs() {

		$cron_jobs = array(
			Cron\Comments::ID => new Cron\Comments( $this->name ),
		);

		foreach ( $cron_jobs as $id => $cron_job ) {

			/* Runs during plugin deactivation. */
			register_deactivation_hook(
				$this->file_path,
				function () use ( &$id ) {
					// remove cron jon
					wp_clear_scheduled_hook( $id );

					// clear options
					delete_option( 'delete_comments_recurrence' );
					delete_option( 'delete_comments_age' );
					delete_option( 'delete_comments_operation' );
				}
			);

			add_action( $id, array( $cron_job, 'run' ) );
		}
	}

	/**
	 * Setup the custom options for plugin
	 */
	public function setup_options() {

		/* filter to check changes on reccurence option */
		add_filter( 'pre_update_option_delete_comments_recurrence', array( $this, 'check_recurrence_change' ), 10, 2 );

		/* Create settings section */
		add_settings_section(
			'delete-old-comments',    // Section ID
			'Delete old comments',     // Section title
			array( $this, 'settings_description' ), // Section callback function
			'discussion'               // Settings page slug
		);

		/* Register Settings */
		register_setting(
			'discussion',       // Options group
			'delete_comments_recurrence',  // Option name/database
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'never',
			)
		);

		register_setting(
			'discussion',       // Options group
			'delete_comments_age',  // Option name/database
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '3 years',
			)
		);

		register_setting(
			'discussion',       // Options group
			'delete_comments_operation',  // Option name/database
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'trash',
			)
		);

		/* Create settings field */
		add_settings_field(
			'delete_comments_recurrence',        // Field ID
			'Recurrence',                        // Field title
			array( $this, 'recurrence_field' ),  // Field callback function
			'discussion',                        // Settings page slug
			'delete-old-comments'               // Section ID
		);

		add_settings_field(
			'delete_comments_age',        // Field ID
			'Age',                        // Field title
			array( $this, 'age_field' ),  // Field callback function
			'discussion',                 // Settings page slug
			'delete-old-comments'        // Section ID
		);

		add_settings_field(
			'delete_comments_operation',        // Field ID
			'Operation type',                        // Field title
			array( $this, 'operation_field' ),  // Field callback function
			'discussion',                 // Settings page slug
			'delete-old-comments'        // Section ID
		);
	}

	/**
	 * Settings Section Description, provinding useful information
	 */
	public function settings_description() {

		// anchor link
		echo '<a name="delete-old-comments"></a>';

		// get existing cron jobs
		$cron_jobs = array(
			Cron\Comments::ID => new Cron\Comments( $this->name ),
		);

		// check if we need to run the event to delete comments now and set flag to use later
		$ran_manually = false;
		if ( is_admin() && isset( $_GET['delete_old_comments'] ) && isset( $_GET['delete_comments_nonce'] ) && wp_verify_nonce( $_GET['delete_comments_nonce'], 'delete_old_comments' ) ) {
			foreach ( $cron_jobs as $id => $cron_job ) {
				do_action( $id );
			}
			$ran_manually = true;
		}

		// description
		esc_html_e( 'Options to automatically delete comments based on their age.', 'delete-old-comments' );
		$age        = get_option( 'delete_comments_age', '3 years ago' );
		$dateformat = get_option( 'date_format' );
		$operation  = get_option( 'delete_comments_operation', 'trash' );

		// translate
		$operation  = $operation === 'trash' ? __( 'trash', 'delete-old-comments' ) : __( 'delete', 'delete-old-comments' );

		foreach ( $cron_jobs as $id => $cron_job ) {

			$timestamp = wp_next_scheduled( $id );

			if ( $timestamp ) {

				$future_date = strtotime( '-' . str_replace( ' ago', '', $age ), $timestamp ); // check time difference between next event and age selected

				$comments = get_comments(
					array(
						'date_query' => array(
							// format the timestamp to be compatible with strtotime
							'before' => date( 'd F Y H:i', $future_date ),
						),
					)
				);

				// format timestamp
				$next_date_time = date_i18n( $dateformat . ' G:i e', $timestamp );

				echo '<br>';
				printf(
					/* translators: %s is a date */
					esc_html__( 'Next clean up operation will run shortly after %s', 'delete-old-comments' ),
					$next_date_time
				);

				if ( $comments ) {
					echo '<br>';
					$count = count( $comments );
					printf(
						esc_attr(
							/* translators: %s is a number */
							_n(
								'And will %s %s comment',
								'And will %s %s comments',
								$count,
								'delete-old-comments'
							)
						),
						$operation,
						$count
					);
				} else {
					echo '<br>' . esc_html__( 'It will not delete any comment.', 'delete-old-comments' );
				}
			} else {
				echo '<br>' . esc_html__( 'Select the recurrence below to schedule the next clean up operation.', 'delete-old-comments' );
			}
		}

		if ( $ran_manually ) {

			echo '<br><strong>' . esc_html__( 'Operation to delete comments ran sucessfully.', 'delete-old-comments' ) . '</strong>';

		} else {

			// add query arg to run action
			$run_url = add_query_arg( 'delete_old_comments', '1', admin_url( 'options-discussion.php' ) );

			// add control nonce to url
			$run_url = wp_nonce_url( $run_url, 'delete_old_comments', 'delete_comments_nonce' );

			echo '<br> <a href="' . esc_url( $run_url ) . '#delete-old-comments">' . esc_html__( 'Run now', 'delete-old-comments' ) . '</a>';

		}

	}

	/**
	 * Display recurence field HTML
	 */
	public function recurrence_field() {

		$recurrence = wp_get_schedules();

		// add 'never' default option to recurrence array
		$recurrence['never'] = array(
			'interval' => false,
			'display'  => __( 'Never', 'delete-old-comments' ),
		);

		?>
		<label for="delete_comments_recurrence">
		<?php esc_html_e( 'Check for comments to delete', 'delete-old-comments' ); ?>
		<select id="delete_comments_recurrence" name="delete_comments_recurrence">
			<?php

			$current = get_option( 'delete_comments_recurrence', 'never' );

			foreach ( $recurrence as $key => $value ) {
				$selected = selected( $current, $key );
				printf(
					'<option value="%1$s"%2$s>%3$s</option>',
					esc_attr( $key ),
					esc_html( $selected ),
					esc_html( $value['display'] )
				);
			}
			?>
		</select>
		</label>
		<?php
	}

	/**
	 * Display age field HTML
	 */
	public function age_field() {
		?>
		<label for="delete_comments_age">
		<?php esc_html_e( 'Older than', 'delete-old-comments' ); ?>
		<select id="delete_comments_age" name="delete_comments_age">
			<?php

			$age = array(
				'5 years ago'  => esc_html__( '5 years', 'delete-old-comments' ),
				'4 years ago'  => esc_html__( '4 years', 'delete-old-comments' ),
				'3 years ago'  => esc_html__( '3 years', 'delete-old-comments' ),
				'2 years ago'  => esc_html__( '2 years', 'delete-old-comments' ),
				'1 year ago'   => esc_html__( '1 year', 'delete-old-comments' ),
				'6 months ago' => esc_html__( '6 months', 'delete-old-comments' ),
			);

			$current = get_option( 'delete_comments_age', '3 years ago' );

			foreach ( $age as $key => $display ) {
				$selected = selected( $current, $key );
				printf(
					'<option value="%1$s"%2$s>%3$s</option>',
					esc_attr( $key ),
					esc_html( $selected ),
					esc_html( $display )
				);
			}
			?>
		</select>
		</label>
		<?php
	}


	/**
	 * Display delete operation field
	 */
	public function operation_field() {
		?>
		<label for="delete_comments_operation">
		<select id="delete_comments_operation" name="delete_comments_operation">
			<?php

			$operation = array(
				'trash'  => esc_html__( 'Send to Trash', 'delete-old-comments' ),
				'delete'  => esc_html__( 'Delete Permanently (Not reversible)', 'delete-old-comments' ),
			);

			$current = get_option( 'delete_comments_operation', 'trash' );

			foreach ( $operation as $key => $display ) {
				$selected = selected( $current, $key );
				printf(
					'<option value="%1$s"%2$s>%3$s</option>',
					esc_attr( $key ),
					esc_html( $selected ),
					esc_html( $display )
				);
			}
			?>
		</select>
		</label>
		<?php
	}


	/**
	 * Check if recurrence option changed to setup new cron
	 *
	 * @param string $new_value New value
	 * @param string $old_value Value before edit
	 *
	 * @return string The new value.
	 */
	public function check_recurrence_change( $new_value, $old_value ) {

		if ( $old_value !== $new_value ) {

			$cron_jobs = array(
				Cron\Comments::ID => new Cron\Comments( $this->name ),
			);

			foreach ( $cron_jobs as $id => $cron_job ) {

				// clear previous schedule if it exists
				wp_clear_scheduled_hook( $id );

				// if the new value is not set to never and the event is not already set, then schedule it
				if ( 'never' !== $new_value && ! wp_next_scheduled( $id ) ) {
					// schedule the next event for the next day after midnight
					wp_schedule_event( strtotime( 'tomorrow +10min' ), $new_value, $id );
				}
			}
		}
		return $new_value;
	}

	/**
	 * Trigger events directly
	 */
	private function run_events() {

		$cron_jobs = array(
			Cron\Comments::ID => new Cron\Comments( $this->name ),
		);

		foreach ( $cron_jobs as $id => $cron_job ) {

			do_action( $id );

		}
	}
}
