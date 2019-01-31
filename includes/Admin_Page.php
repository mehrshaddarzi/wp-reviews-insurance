<?php

namespace WP_REVIEWS_INSURANCE;

use WP_REVIEWS_INSURANCE;
use WP_REVIEWS_INSURANCE\WP_List_Table\Reviews as wlt_order;

class Admin_Page {

	/**
	 * Admin Page slug
	 */
	public static $admin_page_slug;

	/**
	 * List OF Variable For WP_List_Table
	 */
	public $reviews_obj;

	/**
	 * List Pages Slug in This Plugin
	 */
	public static $pages;


	/**
	 * Admin_Page constructor.
	 */
	public function __construct() {

		//Set Variable
		self::$admin_page_slug = 'reviews';
		self::$pages           = array( "reviews" );

		//Add Admin Menu Wordpress
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		//Set Screen Option
		add_filter( 'set-screen-option', array( $this, 'set_screen' ), 10, 3 );

		//Add Script to Admin Wordpress
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );

		//Set Admin Notice and Custom Redirect and Custom Js/css for Per Page
		foreach ( self::$pages as $page_slug ) {
			add_action( 'admin_notices', array( $this, 'admin_notice_' . $page_slug ) );
			add_action( 'admin_init', array( $this, 'wlt_redirect_' . $page_slug ) );
			add_action( 'admin_head', array( $this, 'wlt_script_' . $page_slug ) );
			add_action( 'wlt_top_content', array( $this, 'wlt_top_' . $page_slug ) );
		}

		//Remove All Notice Another Plugin
		add_action( 'admin_print_scripts', array( $this, 'prevent_admin_notices_plugins' ) );

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
	 * Prevent and Disable all admin Notice
	 */
	public function prevent_admin_notices_plugins() {
		global $wp_filter, $pagenow;

		if ( $pagenow == "admin.php" and isset( $_GET['page'] ) and in_array( $_GET['page'], self::$pages ) and ! isset( $_GET['alert'] ) ) {
			if ( isset( $wp_filter['user_admin_notices'] ) ) {
				unset( $wp_filter['user_admin_notices'] );
			}
			if ( isset( $wp_filter['admin_notices'] ) ) {
				unset( $wp_filter['admin_notices'] );
			}
			if ( isset( $wp_filter['all_admin_notices'] ) ) {
				unset( $wp_filter['all_admin_notices'] );
			}
		}
	}

	/**
	 * Load assets file in admin
	 */
	public function admin_assets() {
		global $pagenow;

		//List Allow This Script
		if ( $pagenow == "admin.php" and isset( $_GET['page'] ) and in_array( $_GET['page'], self::$pages ) ) {

//			//Load Jquery Confirm
//			wp_enqueue_style( 'jQuery-confirm', WP_REVIEWS_INSURANCE::$plugin_url . '/asset/admin/css/jquery-confirm.min.css', true, '3.3.0' );
//			wp_enqueue_script( 'jQuery-confirm', WP_REVIEWS_INSURANCE::$plugin_url . '/asset/admin/js/jquery-confirm.min.js', array( 'jquery' ), '3.3.0', true );
//
//			//Load init Script
//			wp_enqueue_style( 'wp-online-style', WP_REVIEWS_INSURANCE::$plugin_url . '/asset/admin/css/style.css', true, WP_REVIEWS_INSURANCE::$plugin_version );
//			wp_enqueue_script( 'wp-online-js', WP_REVIEWS_INSURANCE::$plugin_url . '/asset/admin/js/script.js', array( 'jquery' ), WP_REVIEWS_INSURANCE::$plugin_version, true );
//			wp_localize_script( 'wp-online-js', 'wp_options_js', array(
//				'ajax'        => admin_url( "admin-ajax.php" ),
//				'is_rtl'      => ( is_rtl() ? 1 : 0 ),
//				'loading_img' => admin_url( "/images/spinner.gif" ),
//			) );
		}

	}

	/**
	 * Screen Option
	 *
	 * @param $status
	 * @param $option
	 * @param $value
	 * @return mixed
	 */
	public static function set_screen( $status, $option, $value ) {
		return $value;
	}

	/**
	 * Set Admin Menu
	 */
	public function admin_menu() {

		$post_type = add_submenu_page( 'edit.php?post_type=' . Post_Type::$post_type, __( 'Reviews', 'wp-reviews-insurance' ), __( 'Reviews', 'wp-reviews-insurance' ), 'manage_options', self::$admin_page_slug, array( $this, 'reviews' ) );
		add_submenu_page( 'edit.php?post_type=' . Post_Type::$post_type, __( 'Setting', 'wp-reviews-insurance' ), __( 'Setting', 'wp-reviews-insurance' ), 'manage_options', 'wp_reviews_option', array( Admin_Setting_Api::instance(), 'wedevs_plugin_page' ) );

		//Set Load Action For WP_List_Table
		add_action( "load-$post_type", array( $this, 'screen_option_order' ) );
	}



	/**=============================================================================== ORDER
	 * = Reviews WP_LIST_TABLE
	 * ================================================================================= */

	//Screen Option
	public function screen_option_order() {

		//Set Screen Option
		$option = 'per_page';
		$args   = array( 'label' => __( "تعداد نمایش در صفحه", '' ), 'default' => 10, 'option' => 'order_per_page' ); //options is user Meta
		add_screen_option( $option, $args );

		//Load WP_List_Table
		$this->order_obj = new wlt_order();
		$this->order_obj->prepare_items();
	}

	//Order Admin Page
	public function reviews() {
		if ( ! isset( $_GET['method'] ) ) {

			//Show Wp List Table
			Admin_Ui::wp_list_table( $this->order_obj, "cart", get_admin_page_title(), array(), true );
		} else {

		}
	}

	//Admin Notice
	public function admin_notice_reviews() {
		if ( self::in_page( 'order' ) and isset( $_GET['alert'] ) ) {
			switch ( $_GET['alert'] ) {

				//Delete Alert
				case "delete":
					Admin_Ui::wp_admin_notice( __( "آیتم های انتخابی با موفقیت حذف گردید", 'wp-statistics-actions' ), "success" );
					break;

				//Change status
				case "change-status":
					Admin_Ui::wp_admin_notice( __( "تغییر وضعیت سفارش با موفقیت انجام شد", 'wp-statistics-actions' ), "success" );
					break;

			}
		}
	}

	//Custom Script css/Js
	public function wlt_script_reviews() {
		if ( self::in_page( 'order' ) ) {
			echo '<style>table.widefat th.column-title {width: 260px;}</style>';
		}
	}

	//Top content Wp List Table
	public function wlt_top_reviews() {
		if ( self::in_page( 'order' ) and isset( $_GET['top'] ) ) {

			//Top Content for Status
			if ( $_GET['top'] == "change-status" ) {
				?>
                <div class="wlt-top-content"><h2>تغییر وضعیت سفارش</h2>
                <form action="" method="post">
                <table class="form-table">
                    <tbody>
                    <tr class="user-role-wrap">
                        <th><label for="role">تغییر وضعیت به</label></th>
                        <td>
                            <select name="new-status">
								<?php
								for ( $i = 1; $i <= 9; $i ++ ) {
									echo '<option value="' . $i . '"' . selected( $_GET['status'], $i, true ) . '>' . Helper::show_status( $i ) . '</option>';
								}
								?>
                            </select>
                        </td>
                    </tr>
                    <tr class="user-role-wrap">
                        <th><label for="role">اطلاع رسانی شود به کاربر ؟</label></th>
                        <td>
                            <select name="is-notification">
                                <option value="yes">آری</option>
                                <option value="no">خیر</option>
                            </select>
                        </td>
                    </tr>
                    <input type="hidden" name="order_id" value="<?php echo $_GET['order_id']; ?>">
                    </tbody>
                </table>
				<?php
				submit_button( "تغییر وضعیت" );
				echo '</form></div>';

			}

		}
	}

	//Redirect Process
	public function wlt_redirect_reviews() {
		//Current Page Slug
		$page_slug = 'order';
		if ( self::in_page( $page_slug ) and ! isset( $_GET['method'] ) ) {

			//Redirect For $_POST Form Performance
			foreach ( array( "s", "user" ) as $post ) {
				if ( isset( $_POST[ $post ] ) and ! empty( $_POST[ $post ] ) ) {
					$args = array( 'page' => $page_slug, $post => str_ireplace( " ", "+", $_POST[ $post ] ) );
					if ( isset( $_GET['filter'] ) ) {
						$args['filter'] = $_GET['filter'];
					}
					wp_redirect( add_query_arg( $args, admin_url( "admin.php" ) ) );
					exit;
				}
			}

			//Remove Admin Notice From Pagination
			if ( isset( $_GET['alert'] ) and isset( $_GET['paged'] ) ) {
				wp_redirect( remove_query_arg( array( 'alert' ) ) );
				exit;
			}

		}
	}

}