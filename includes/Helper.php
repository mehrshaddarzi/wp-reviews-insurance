<?php

namespace WP_REVIEWS_INSURANCE;

use WP_REVIEWS_INSURANCE\Core\Debug;
use WP_REVIEWS_INSURANCE\Core\WP_Mail;


/**
 * Class Helper Used in custom Helper Method For This Plugin
 */
class Helper {

	/**
	 * Search IP and User id in comment
	 */
	public static function search_in_comments() {

		//User Ip
		$ip = isset( $_SERVER['HTTP_CLIENT_IP'] ) ? $_SERVER['HTTP_CLIENT_IP'] : isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

		// WP_Comment_Query arguments
		$args = array(
			'post_type' => Post_Type::$post_type,
			'orderby'   => 'comment_date',
			'count'     => true,
		);

		//Check Wth User id
		if ( is_user_logged_in() ) {
			$args['user_id'] = get_current_user_id();
		} else {
			//Search with IP
			$args['search'] = $ip;
		}

		$comment_query = new \WP_Comment_Query;
		$comments      = $comment_query->query( $args );
		return $comments;
	}

	/**
	 * Check Auth comment Code
	 *
	 * @param $key
	 * @return bool
	 */
	public static function check_auth_comment( $key ) {

		// WP_Comment_Query arguments
		$args = array(
			'post_type'  => Post_Type::$post_type,
			'meta_query' => array(
				array(
					'key'     => 'auth_key',
					'value'   => $key,
					'compare' => '='
				)
			)
		);

		$comment_query = new \WP_Comment_Query;
		$comments      = $comment_query->query( $args );
		if ( count( $comments ) > 0 ) {
			return $comments[0]->comment_ID;
		}

		return false;
	}

	/**
	 * Get Number Valid Reviews For insurance
	 *
	 * @param $post_id
	 * @return int
	 */
	public static function get_number_valid_reviews( $post_id ) {

		// WP_Comment_Query arguments
		$args = array(
			'post_type'  => Post_Type::$post_type,
			'orderby'    => 'comment_date',
			'count'      => true,
			'status'     => 'approve',
			'post_id'    => $post_id,
			'meta_query' => array(
				array(
					'key'     => 'comment_approve_user',
					'value'   => 'yes',
					'compare' => '='
				)
			)
		);

		$comment_query = new \WP_Comment_Query;
		$comments      = $comment_query->query( $args );
		return $comments;
	}

	/**
	 * Get Average Rate
	 *
	 * @param $post_id
	 * @param int $a
	 * @return string
	 */
	public static function get_average_rating( $post_id, $a = 1 ) {

		//Create Empty Average
		$ave = 0;

		// WP_Comment_Query arguments
		$args = array(
			'post_type'  => Post_Type::$post_type,
			'orderby'    => 'comment_date',
			'status'     => 'approve',
			'post_id'    => $post_id,
			'meta_query' => array(
				array(
					'key'     => 'comment_approve_user',
					'value'   => 'yes',
					'compare' => '='
				)
			)
		);

		$comment_query = new \WP_Comment_Query;
		$comments      = $comment_query->query( $args );
		foreach ( $comments as $comment ) {
			$score = get_comment_meta( $comment->comment_ID, 'score', true );
			if ( is_numeric( $score ) and $score > 0 ) {
				$ave = $ave + $score;
			}
		}

		if ( $ave > 0 and count( $comments ) > 0 ) {
			return number_format( ( $ave / count( $comments ) ), $a );
		}
		return 0;
	}

}