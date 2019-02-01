<?php

namespace WP_REVIEWS_INSURANCE;

use WP_REVIEWS_INSURANCE;
use WP_REVIEWS_INSURANCE\Core\Template;
use WP_REVIEWS_INSURANCE\Core\Utility;

class Front {

	/**
	 * Asset Script name
	 */
	public static $asset_name = 'wp-reviews-insurance';

	/**
	 * constructor.
	 */
	public function __construct() {
		/*
		 * Add Script
		 */
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_style' ) );
		/*
		 * short Code Show Review form
		 */
		add_shortcode( 'reviews-form', array( $this, 'reviews_form' ) );
		add_shortcode( 'reviews-insurance', array( $this, 'reviews_insurance' ) );
		add_shortcode( 'reviews-list', array( $this, 'reviews_list' ) );
		/*
		 * Add Custom Meta Fo Comment
		 */
		add_action( 'wp_insert_comment', array( $this, 'add_comment' ), 10, 2 );
		/*
		 * Confirm Review User
		 */
		add_action( 'wp_footer', array( $this, 'check_auth_reviews' ) );
	}

	/**
	 * Register Asset
	 */
	public function wp_enqueue_style() {

		//Jquery Raty
		//@see https://github.com/wbotelhos/raty
		wp_enqueue_style( 'jquery-raty', WP_REVIEWS_INSURANCE::$plugin_url . '/asset/jquery-raty/jquery.raty.css', array(), WP_REVIEWS_INSURANCE::$plugin_version, 'all' );
		wp_enqueue_script( 'jquery-raty', WP_REVIEWS_INSURANCE::$plugin_url . '/asset/jquery-raty/jquery.raty.js', array( 'jquery' ), WP_REVIEWS_INSURANCE::$plugin_version, false );

		//Native Plugin
		wp_enqueue_style( self::$asset_name, WP_REVIEWS_INSURANCE::$plugin_url . '/asset/style.css', array(), WP_REVIEWS_INSURANCE::$plugin_version, 'all' );
		$custom_css = ".wp-insurance-form .cancel-on-png, .wp-insurance-form .cancel-off-png, .wp-insurance-form .star-on-png, .wp-insurance-form .star-off-png, .wp-insurance-form .star-half-png {color: " . WP_REVIEWS_INSURANCE::$option['star_color'] . ";}";
		wp_add_inline_style( self::$asset_name, $custom_css );

		//		wp_enqueue_script( self::$asset_name, WP_REVIEWS_INSURANCE::$plugin_url . '/asset/script.js', array( 'jquery' ), WP_REVIEWS_INSURANCE::$plugin_version, false );
//		wp_localize_script( self::$asset_name, 'wp_reviews_js', array(
//			'ajax'          => home_url() . '/?WP_REVIEWS_INSURANCE_check_notification=yes&time=' . current_time( 'timestamp' ),
//			'is_login_user' => ( is_user_logged_in() ? 1 : 0 )
//		) );
	}

	/**
	 * Reviews Form
	 */
	public function reviews_form() {

		//Get insurance List
		$insurance = Utility::get_list_post( Post_Type::$post_type );

		//Get User Full name and Email
		$full_name  = '';
		$user_email = '';
		if ( is_user_logged_in() ) {
			$full_name  = Utility::get_user_full_name( get_current_user_id() );
			$user_email = Utility::get_user_email( get_current_user_id() );
		}

		//Get Thank You Text
		$thank_you = WP_REVIEWS_INSURANCE::$option['thanks_text'];

		return Template::get()->shortlink_get_template( 'reviews-form.php', compact( 'insurance', 'full_name', 'user_email', 'thank_you' ) );
	}

	/**
	 * Show Review Company List
	 */
	public function reviews_insurance( $atts ) {

		//Prepare ShortCode
		$atts = shortcode_atts( array(
			'order' => 'DESC'
		), $atts, 'reviews-insurance' );

		//Get List Post
		$list  = array();
		$args  = array(
			'post_type'      => Post_Type::$post_type,
			'post_status'    => 'publish',
			'posts_per_page' => '-1',
			'order'          => $atts['order'],
			'fields'         => 'ids'
		);
		$query = new \WP_Query( $args );
		foreach ( $query->posts as $ID ) {
			$list[] = array(
				'title' => get_the_title( $ID ),
				'rate'  => Helper::get_average_rating( $ID )
			);
		}

		return Template::get()->shortlink_get_template( 'reviews-insurance.php', compact( 'list' ) );
	}

	/**
	 * Show Reviews List
	 */
	public function reviews_list( $atts ) {
		global $post;

		//Prepare ShortCode
		$atts = shortcode_atts( array(
			'order'        => 'DESC',
			'insurance_id' => $post->ID,
			'number'       => false,
		), $atts, 'reviews-insurance' );

		//Get List Post
		$list = array();

		// WP_Comment_Query arguments
		$args          = array(
			'post_type'  => Post_Type::$post_type,
			'orderby'    => 'comment_date',
			'status'     => 'approve',
			'order'      => $atts['order'],
			'post_id'    => $atts['insurance_id'],
			'number'     => false,
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
			$score                        = get_comment_meta( $comment->comment_ID, 'score', true );
			$list[ $comment->comment_ID ] = array_merge( array( 'score' => $score ), get_object_vars( $comment ) );
		}


		return Template::get()->shortlink_get_template( 'reviews-list.php', compact( 'list' ) );
	}

	/*
	 * Add Comment Meta
	 */
	public function add_comment( $id, $comment ) {

		if ( isset( $_POST['wp_reviews_score'] ) and ! empty( $_POST['wp_reviews_score'] ) ) {

			//Save Rating
			update_comment_meta( $id, 'score', sanitize_text_field( $_POST['wp_reviews_score'] ) );

			//Send Email
			if ( WP_REVIEWS_INSURANCE::$option['email_auth'] == 1 ) {

				//Generate User Link
				$auth_key = wp_generate_password( 50, false );
				$link     = add_query_arg( array( 'wp_confirm_reviews_insurance' => $auth_key ), home_url() );
				update_comment_meta( $id, 'auth_key', sanitize_text_field( $auth_key ) );

				//Email text
				$email_opt     = get_option( 'wp_reviews_email_opt' );
				$email_text    = str_replace( array( '[fullname]', '[link]' ), array( $comment->comment_author, '<a href="' . $link . '" target="_blank">Confirm Your Review</a>' ), $email_opt['email_body'] );
				$email_subject = str_replace( array( '[fullname]', '[sitename]' ), array( $comment->comment_author, get_bloginfo( 'name' ) ), WP_REVIEWS_INSURANCE::$option['email_subject'] );
				Utility::send_mail( $comment->comment_author_email, $email_subject, $email_text );

			} else {
				update_comment_meta( $id, 'comment_approve_user', 'yes' );
			}
		}
	}

	/*
	 * Check confirm Review
	 */
	public function check_auth_reviews() {
		if ( isset( $_GET['wp_confirm_reviews_insurance'] ) ) {

			//check auth Code
			$comment_id = Helper::check_auth_comment( $_GET['wp_confirm_reviews_insurance'] );
			if ( $comment_id != false ) {

				//remove meta Key
				delete_comment_meta( $comment_id, 'auth_key' );

				//Add Validate User Reviews
				update_comment_meta( $comment_id, 'comment_approve_user', 'yes' );

				//Show Alert
				echo '<div class="confirm-review-alert">' . WP_REVIEWS_INSURANCE::$option['email_thanks_text'] . '</div>';
				echo '
				<script>
				jQuery(document).ready(function(){
				   jQuery(".confirm-review-alert").delay(1500).fadeOut("normal"); 
				});
				</script>
			';
			}
		}
	}


}