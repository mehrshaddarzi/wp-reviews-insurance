<?php
/**
 * Plugin Name: WP Reviews Insurance
 * Description: A Plugin For Reviews Insurance
 * Plugin URI:  https://veronaLabs.com
 * Version:     1.0.0
 * Author:      VeronaLabs
 * Author URI:  https://veronaLabs.com
 * License:     MIT
 * Text Domain: wp-reviews-insurance
 * Domain Path: /languages
 */

class WP_REVIEWS_INSURANCE {

	/**
	 * Plugin instance.
	 *
	 * @see get_instance()
	 * @type object
	 */
	protected static $instance = null;

	/**
	 * URL to this plugin's directory.
	 *
	 * @type string
	 */
	public static $plugin_url = '';

	/**
	 * Path to this plugin's directory.
	 *
	 * @type string
	 */
	public static $plugin_path;

	/**
	 * Path to this plugin's directory.
	 *
	 * @type string
	 */
	public static $plugin_version;

	/**
	 * Plugin Option Store
	 */
	public static $option;

	/**
	 * Access this plugin’s working instance
	 *
	 * @wp-hook plugins_loaded
	 * @since   2012.09.13
	 * @return  object of this class
	 */
	public static function get_instance() {
		null === self::$instance and self::$instance = new self;
		return self::$instance;
	}

	/**
	 * Used for regular plugin work.
	 *
	 * @wp-hook plugins_loaded
	 * @return  void
	 */
	public function plugin_setup() {

		//Get plugin Data information
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$plugin_data = get_plugin_data( __FILE__ );

		//Get Option
		self::$option = get_option( 'wp_reviews_insurance_opt' );

		//Get Plugin Version
		self::$plugin_version = $plugin_data['Version'];

		//Set Variable
		self::$plugin_url  = plugins_url( '', __FILE__ );
		self::$plugin_path = plugin_dir_path( __FILE__ );

		//Set Text Domain
		$this->load_language( 'wp-reviews-insurance' );

		//Load Composer
		include_once dirname( __FILE__ ) . '/vendor/autoload.php';

		//Load Class
		$autoload = array( 'Post_Type', 'Admin_Setting_Api', 'Admin_Page', 'Front', 'Comment', 'Ajax' );
		foreach ( $autoload as $class ) {
			$class_name = '\WP_REVIEWS_INSURANCE\\' . $class;
			new $class_name;
		}

		//Test Service
		if ( isset( $_GET['test'] ) ) {
			//self::send_mail('admin', 'عنوان ایمیل','matn email test');
			//exit;
		}
	}

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
		$email_template = wp_normalize_path( dirname( __FILE__ ) . '/template/email.php' );

		//Set To Admin
		if ( $to == "admin" ) {
			$opt = get_option( 'wp_reviews_insurance_opt' );
			//$to = 'opub.ir@gmail.com';
			$to = $opt['modir_email'];
		}

		//Email from
		$from_name  = 'نشرآنلاین';
		$from_email = get_bloginfo( 'admin_email' );

		//Template Arg
		$template_arg = array(
			'title'      => $subject,
			'logo'       => plugins_url( '', __FILE__ ) . '/template/email.jpg',
			'content'    => $content,
			'site_url'   => home_url(),
			'site_title' => 'نشر آنلاین',
		);

		//Send Email
		try {
			\WP_REVIEWS_INSURANCE\WP_Mail::init()->from( '' . $from_name . ' <' . $from_email . '>' )->to( $to )->subject( $subject )->template( $email_template, $template_arg )->send();
			return true;
		} catch ( Exception $e ) {
			return false;
		}

	}

	/**
	 * Loads translation file.
	 *
	 * Accessible to other classes to load different language files (admin and
	 * front-end for example).
	 *
	 * @wp-hook init
	 * @param   string $domain
	 * @return  void
	 */
	public function load_language( $domain ) {
		load_plugin_textdomain( $domain, false, basename( dirname( __FILE__ ) ) . '/languages' );
	}

	/*
	 * Activation Hook
	 */
	public static function activate() {
		/*
		 * Register Flush Rewrite Accept
		 */
		if ( ! get_option( 'wp_reviews_post_type_flush' ) ) {
			add_option( 'wp_reviews_post_type_flush', true );
		}
	}
}

//Load Plugin
add_action( 'plugins_loaded', array( WP_REVIEWS_INSURANCE::get_instance(), 'plugin_setup' ) );

//Use Activation
register_activation_hook( __FILE__, array( 'WP_REVIEWS_INSURANCE', 'activate' ) );

