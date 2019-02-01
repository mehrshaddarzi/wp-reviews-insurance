<?php

namespace WP_REVIEWS_INSURANCE;

use WP_REVIEWS_INSURANCE;

class Ajax {


	/**
	 * Ajax constructor.
	 */
	public function __construct() {

		$list_function = array( 'add_reviews_insurance' );

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


	public function add_reviews_insurance() {
		global $wpdb;
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			if ( isset( $_REQUEST['wp_reviews_score'] ) ) {

				//User Ip
				$ip = isset( $_SERVER['HTTP_CLIENT_IP'] ) ? $_SERVER['HTTP_CLIENT_IP'] : isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

				//Check Vote Duplicate
				if ( WP_REVIEWS_INSURANCE::$option['is_auth_ip'] == 1 ) {
					if ( Helper::search_in_comments() > 0 ) {
						$this->json_exit( array(
							'state_request' => 'error',
							'text'  => WP_REVIEWS_INSURANCE::$option['error_ip']
						) );
					}
				}

				//Add Comment
				$data = array(
					'comment_post_ID'      => $_POST['insurance_company'],
					'comment_author'       => $_POST['full_name'],
					'comment_author_email' => $_POST['your_email'],
					'comment_content'      => $_POST['wp_reviews_comment'],
					'user_id'              => ( is_user_logged_in() === true ? get_current_user_id() : 0 ),
					'comment_author_IP'    => $ip,
					'comment_date'         => current_time( 'mysql' ),
					'comment_approved'     => 0,
				);
				wp_insert_comment( $data );

				//Show Result
				$this->json_exit( array( 'state_request' => 'success' ) );
			}
		}
		die();
	}

}