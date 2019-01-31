<?php

namespace WP_REVIEWS_INSURANCE;
use WP_REVIEWS_INSURANCE;

class Front {

	/**
	 * Asset Script name
	 */
	public static $asset_name = 'user-order';

	/**
	 * constructor.
	 */
	public function __construct() {
		/*
		 * Add Script
		 */
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_style' ) );

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
		wp_enqueue_script( self::$asset_name, WP_REVIEWS_INSURANCE::$plugin_url . '/asset/script.js', array( 'jquery' ), WP_REVIEWS_INSURANCE::$plugin_version, false );
		wp_localize_script( self::$asset_name, 'wp_reviews_js', array(
			'ajax'          => home_url() . '/?WP_REVIEWS_INSURANCE_check_notification=yes&time=' . current_time( 'timestamp' ),
			'is_login_user' => ( is_user_logged_in() ? 1 : 0 )
		) );
	}
}