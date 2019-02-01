<?php

namespace WP_REVIEWS_INSURANCE;

use WP_REVIEWS_INSURANCE;
use WP_REVIEWS_INSURANCE\Core\Admin_Setting_Api;

class Admin_Page {

	/**
	 * Admin Page slug
	 */
	public static $admin_page_slug;

	/**
	 * Admin_Page constructor.
	 */
	public function __construct() {
		/*
		 * Set Page slug Admin
		 */
		self::$admin_page_slug = 'reviews';
		/*
		 * Setup Admin Menu
		 */
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		/*
		 * Register Script in Admin Area
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
		/*
		 * Add column To comment List Table
		 */
		add_filter( 'manage_edit-comments_columns', array( $this, 'add_comments_columns' ) );
		add_action( 'manage_comments_custom_column', array( $this, 'add_comment_columns_content' ), 10, 2 );
		add_action( 'admin_footer', array( $this, 'add_jquery_raty' ) );
		/*
		 * Add column Post Type
		 */
		add_action( 'manage_' . Post_Type::$post_type . '_posts_custom_column', array( $this, 'column_post_table' ), 10, 2 );
		add_filter( 'manage_' . Post_Type::$post_type . '_posts_columns', array( $this, 'column_post_type' ) );
		add_filter( 'manage_edit-' . Post_Type::$post_type . '_sortable_columns', array( $this, 'sortable_column' ) );
		add_action( 'pre_get_posts', array( $this, 'action_type_orderby' ) );
	}

	/**
	 * Admin Link
	 *
	 * @param $page
	 * @param array $args
	 * @return string
	 */
	public static function admin_link( $page, $args = array() ) {
		return add_query_arg( $args, admin_url( 'admin.php?page=' . $page ) );
	}

	/**
	 * If in Page in Admin
	 *
	 * @param $page_slug
	 * @return bool
	 */
	public static function in_page( $page_slug ) {
		global $pagenow;
		if ( $pagenow == "admin.php" and isset( $_GET['page'] ) and $_GET['page'] == $page_slug ) {
			return true;
		}

		return false;
	}

	/**
	 * Load assets file in admin
	 */
	public function admin_assets() {
		global $pagenow;

		//List Allow This Script
		if ( $pagenow == "edit-comments.php" || $pagenow == "edit.php" ) {

			//Jquery Raty
			//@see https://github.com/wbotelhos/raty
			wp_enqueue_style( 'jquery-raty', WP_REVIEWS_INSURANCE::$plugin_url . '/asset/jquery-raty/jquery.raty.css', array(), WP_REVIEWS_INSURANCE::$plugin_version, 'all' );
			wp_enqueue_script( 'jquery-raty', WP_REVIEWS_INSURANCE::$plugin_url . '/asset/jquery-raty/jquery.raty.js', array( 'jquery' ), WP_REVIEWS_INSURANCE::$plugin_version, false );

		}

	}

	/**
	 * Set Admin Menu
	 */
	public function admin_menu() {
		add_submenu_page( 'edit.php?post_type=' . Post_Type::$post_type, __( 'Settings', 'wp-reviews-insurance' ), __( 'Settings', 'wp-reviews-insurance' ), 'manage_options', 'wp_reviews_option', array( Admin_Setting_Api::instance(), 'wedevs_plugin_page' ) );
	}

	/**
	 * Comment column
	 *
	 * @param $my_cols
	 * @return array
	 */
	public function add_comments_columns( $my_cols ) {

		//Add new Column
		$columns = array(
			'wp_reviews_rating' => __( 'Reviews Rating', 'wp-reviews-insurance' ),
			'confirm_user'      => __( 'Confirm By User', 'wp-reviews-insurance' ),
		);
		$my_cols = array_slice( $my_cols, 0, 3, true ) + $columns + array_slice( $my_cols, 3, null, true );

		// if you want to remove a column, you can just use:
		// unset( $my_cols['response'] );

		// return the result
		return $my_cols;
	}

	/**
	 * Comment Column Content
	 *
	 * @param $column
	 * @param $comment_ID
	 */
	public function add_comment_columns_content( $column, $comment_ID ) {
		global $comment;
		switch ( $column ) :
			case 'wp_reviews_rating' :
				{
					if ( get_post_type( $comment->comment_post_ID ) == Post_Type::$post_type ) {
						$score = get_comment_meta( $comment->comment_ID, 'score', true );
						if ( is_numeric( $score ) and $score > 0 ) {
							echo '
						<div class="comment_score_' . $comment->comment_ID . '"></div>
						<script>
							jQuery(document).ready(function(){
							   jQuery(".comment_score_' . $comment->comment_ID . '").raty({starType: "i", readOnly: true, score: ' . $score . '});
							});
						</script>
						';
						} else {
							echo '_';
						}
					} else {
						echo '_';
					}
					break;
				}
			case 'confirm_user':
				{
					if ( get_post_type( $comment->comment_post_ID ) == Post_Type::$post_type ) {
						$user_auth = get_comment_meta( $comment->comment_ID, 'comment_approve_user', true );
						if ( ! empty( $user_auth ) ) {
							echo 'No';
						} else {
							echo 'Yes';
						}
					} else {
						echo '_';
					}
					break;
				}
		endswitch;
	}

	/**
	 * Add Jquery Raty Admin Footer
	 */
	public function add_jquery_raty() {
		global $pagenow;

		if ( $pagenow == "edit-comments.php" || $pagenow == "edit.php" ) {
			echo '
			<style>
			.cancel-on-png, .cancel-off-png, .star-on-png, .star-off-png, .star-half-png {color: ' . WP_REVIEWS_INSURANCE::$option["star_color"] . '}
			</style>
			';
		}
	}


	/**
	 * Add Column Post Type
	 *
	 * @param $column
	 * @param $post_id
	 */
	public function column_post_table( $column, $post_id ) {
		/*
		 * Number Reviews
		 */
		if ( $column == 'number_reviews' ) {
			echo number_format( Helper::get_number_valid_reviews( $post_id ) );
		}
		/*
		 * Star Rate
		 */
		if ( $column == 'rate' ) {
			echo '
		<div class="post_score_' . $post_id . '"></div>
		<script>
			jQuery(document).ready(function(){
			   jQuery(".post_score_' . $post_id . '").raty({starType: "i", readOnly: true, score: ' . Helper::get_average_rating( $post_id ) . ',half: false,halfShow: true});
			});
		</script>
		';
		}

	}

	/**
	 * Column Post Type Table Add
	 *
	 * @param $columns
	 * @return mixed
	 */
	public function column_post_type( $columns ) {
		/*
		* Add Comment Type column
		*/
		$columns['number_reviews'] = __( 'Number reviews', 'wp-reviews-insurance' );
		/*
		 * Rate
		 */
		$columns['rate'] = __( 'Average rating', 'wp-reviews-insurance' );
		/*
		 * Remove Comment
		 */
		unset( $columns['comments'] );
		return $columns;
	}

	/*
	* Add Sortable Column in Table
	*/
	public function sortable_column( $columns ) {
		$columns['number_reviews'] = 'comment_count';
		return $columns;
	}

	/*
	 * Redirect Type Order Process
	 */
	public function action_type_orderby( $query ) {
		if ( ! is_admin() ) {
			return;
		}
		$orderby = $query->get( 'orderby' );
		if ( 'comment_count' == $orderby ) {
			$query->set( 'orderby', 'comment_count' );
		}
	}

}