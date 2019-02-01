<?php

namespace WP_REVIEWS_INSURANCE\Core;

use WP_REVIEWS_INSURANCE\Helper;

class Debug {

	/**
	 * Global Request Key Name
	 * @var string
	 */
	public static $global_request = 'test';

	/**
	 * Debug constructor.
	 */
	public function __construct() {

		//Send Email debug
		add_action( 'phpmailer_init', array( $this, 'mailtrap' ) );

		//Admin Area Test
		add_action( 'admin_init', array( $this, 'plugin_admin_test' ) );

		//Front Area Test
		add_action( 'wp', array( $this, 'plugin_front_test' ) );

	}

	/**
	 * Pre Show Variable Debug
	 * @param $variable
	 * @param bool $exit
	 */
	public static function pre_debug( $variable, $exit = true ) {
		echo '<div style="width: 95%; padding:5px 30px 30px; background: #f6f6f6; border-radius: 15px;">';
		echo '<pre style="font: 15px Trebuchet MS; line-height: 30px;">';
		if ( is_array( $variable ) ) {
			print_r( $variable );
		} else {
			var_dump( $variable );
		}
		echo '</pre>';
		echo '</div>';
		if ( $exit ) {
			exit;
		}
	}

	/**
	 * PHP Mail Test
	 * @param $phpmailer
	 */
	public function mailtrap( $phpmailer ) {
		$phpmailer->isSMTP();
		$phpmailer->Host     = 'smtp.mailtrap.io';
		$phpmailer->SMTPAuth = true;
		$phpmailer->Port     = 2525;
		$phpmailer->Username = '0b7c8032bb38c2';
		$phpmailer->Password = '9bc4ec76c04858';
	}


	/**
	 * Admin Area Test Code
	 */
	public function plugin_admin_test() {
		if ( isset( $_REQUEST[ self::$global_request ] ) ) {

			self::pre_debug( Helper::search_in_comments() );

			exit;
		}
	}


	/**
	 * Front Area Test
	 */
	public function plugin_front_test() {
		if ( isset( $_REQUEST[ self::$global_request ] ) ) {

			exit;
		}
	}


}