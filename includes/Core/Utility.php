<?php

namespace WP_REVIEWS_INSURANCE\Core;
class Utility {


	/*------------------------------------------------------------
	 - WP Email Api
	 ------------------------------------------------------------/

	/**
	 * Send Email
	 *
	 * @param $to
	 * @param $subject
	 * @param $content
	 * @return bool
	 */
	public static function send_mail( $to, $subject, $content ) {

		//Email Template
		$email_template = wp_normalize_path( \WP_REVIEWS_INSURANCE::$plugin_path . '/template/email.php' );
		if ( trim( \WP_REVIEWS_INSURANCE::$Template_Engine ) != "" ) {
			$template = wp_normalize_path( path_join( get_template_directory(), '/includes/my_file.php' ) );
			if ( file_exists( $template ) ) {
				$email_template = $template;
			}
		}

		//Get option Send Mail
		$opt = get_option( 'wp_reviews_email_opt' );

		//Set To Admin
		if ( $to == "admin" ) {
			$to = get_bloginfo( 'admin_email' );
		}

		//Email from
		$from_name  = $opt['from_name'];
		$from_email = $opt['from_email'];

		//Template Arg
		$template_arg = array(
			'title'       => $subject,
			'logo'        => $opt['email_logo'],
			'content'     => $content,
			'site_url'    => home_url(),
			'site_title'  => get_bloginfo( 'name' ),
			'footer_text' => $opt['email_footer'],
			'is_rtl'      => ( is_rtl() ? true : false )
		);

		//Send Email
		try {
			WP_Mail::init()->from( '' . $from_name . ' <' . $from_email . '>' )->to( $to )->subject( $subject )->template( $email_template, $template_arg )->send();
			return true;
		} catch ( Exception $e ) {
			return false;
		}
	}

	/*------------------------------------------------------------
	 - WP ACL Api
    ------------------------------------------------------------/

	/**
	 * Get User email
	 *
	 * @param bool $user_id
	 * @return string
	 */
	public static function get_user_email( $user_id = false ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		//setup user data
		$user_info = get_userdata( $user_id );
		return $user_info->user_email;
	}

	/**
	 * Get User Name
	 *
	 * @param bool $user_id
	 * @return string
	 */
	public static function get_user_full_name( $user_id = false ) {
		$user_info = get_userdata( $user_id );

		//check display name
		if ( $user_info->display_name != "" ) {
			return $user_info->display_name;
		}

		//Check First and Last name
		if ( $user_info->first_name != "" ) {
			return $user_info->first_name . " " . $user_info->last_name;
		}

		//return Username
		return $user_info->user_login;
	}

	/**
	 * Check User Exist By id
	 *
	 * @param $user
	 * @return bool
	 * We Don`t Use get_userdata or get_user_by function, because We need only count nor UserData object.
	 */
	public static function user_id_exists( $user ) {
		global $wpdb;
		$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->users WHERE ID = %d", $user ) );
		if ( $count == 1 ) {
			return true;
		} else {
			return false;
		}
	}


	/*------------------------------------------------------------
	 - WP POST Api
	------------------------------------------------------------/

	/**
	 * Get List Post From Post Type
	 *
	 * @param $post_type
	 * @return array
	 */
	public static function get_list_post( $post_type ) {
		$list = array();
		$args = array(
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => '-1',
			'order'          => 'ASC',
			'fields'         => 'ids'
		);

		$query = new \WP_Query( $args );
		foreach ( $query->posts as $ID ) {
			$list[ $ID ] = get_the_title( $ID );
		}

		return $list;
	}

	/**
	 * Check Post Exist By ID in wordpress
	 *
	 * @param $ID
	 * @param bool $post_type
	 * @return int
	 */
	public static function post_exist( $ID, $post_type = false ) {
		global $wpdb;

		$query = "SELECT count(*) FROM `$wpdb->posts` WHERE `ID` = $ID";
		if ( ! empty ( $post_type ) ) {
			$query .= " AND `post_type` = '$post_type'";
		}

		return ( (int) $wpdb->get_var( $query ) > 0 ? true : false );
	}

	/*------------------------------------------------------------
	 - WP Admin Ui
	------------------------------------------------------------/

	/**
	 * Show Admin Wordpress Ui Notice
	 *
	 * @param $text
	 * @param string $model
	 * @param bool $close_button
	 * @param bool $echo
	 * @param string $style_extra
	 * @return string
	 */
	public static function wp_admin_notice( $text, $model = "info", $close_button = true, $echo = true, $style_extra = 'padding:12px;' ) {
		$text = '
        <div class="notice notice-' . $model . '' . ( $close_button === true ? " is-dismissible" : "" ) . '">
           <div style="' . $style_extra . '">' . $text . '</div>
        </div>
        ';
		if ( $echo ) {
			echo $text;
		} else {
			return $text;
		}
	}


}