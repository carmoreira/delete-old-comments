<?php

namespace Arte\WP\Plugin\DeleteComments\Cron;

/**
 * Comments cron.
 */
class Comments {

	/**
	 * The cron job unique identifier.
	 *
	 * @var string
	 */
	const ID = 'delete_old_comments';

	/**
	 * Run cron logic
	 */
	public function run() {

		$age       = get_option( 'delete_comments_age', '3 years ago' );
		$operation = get_option( 'delete_comments_operation', 'trash' );
		$delete    = $operation === 'trash' ? false : true;

		$args = array(
			'date_query' => array(
				'before' => $age,
			),
		);

		$comments = get_comments( $args );

		if ( $comments ) {
			$count = 0;
			foreach ( $comments as $comment ) {
				wp_delete_comment( $comment, $delete );
				$count++;
			}
		}
	}
}
