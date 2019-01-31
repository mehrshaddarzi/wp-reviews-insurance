<?php

namespace WP_REVIEWS_INSURANCE;

class Ajax {


	/**
	 * Ajax constructor.
	 */
	public function __construct() {

		$list_function = array();

		foreach ( $list_function as $method ) {
			add_action( 'wp_ajax_' . $method, array( $this, $method ) );
			add_action( 'wp_ajax_nopriv_' . $method, array( $this, $method ) );
		}

	}

	/**
	 * Show Json and Exit
	 *
	 * @since    1.0.0
	 * @param $array
	 */
	public function json_exit( $array ) {
		wp_send_json( $array );
		exit;
	}


	/**
	 * Check New Notification
	 */
	public function check_new_notification_online_pub() {
		global $wpdb;
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {


		}
		die();
	}

}