<?php

namespace WP_REVIEWS_INSURANCE;

class Admin_Setting_Api {

	/**
	 * Plugin Option name
	 */
	public static $option_name = 'wp_reviews_insurance_opt';
	public $setting;

	/**
	 * The single instance of the class.
	 */
	protected static $_instance = null;

	/**
	 * Main Instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Admin_Setting_Api constructor.
	 */
	public function __construct() {
		/**
		 * Set Admin Setting
		 */
		add_action( 'admin_init', array( $this, 'wedevs_admin_init' ) );
	}

	/**
	 * Display the plugin settings options page
	 */
	public function wedevs_plugin_page() {

		echo '<div class="wrap">';
		settings_errors();

		$this->setting->show_navigation();
		$this->setting->show_forms();

		echo '</div>';
	}

	/**
	 * Registers settings section and fields
	 */
	public function wedevs_admin_init() {

		$sections = array(
			array(
				'id'    => self::$option_name,
				'title' => __( 'تنظیمات افزونه', 'wedevs' )
			),
		);

		$fields = array(
			self::$option_name             => array(
				array(
					'name'    => 'modir_mobile',
					'label'   => __( 'شماره همراه مدیر', 'wedevs' ),
					'desc'    => __( 'شماره همراه مدیر برای اطلاع رسانی', 'wedevs' ),
					'type'    => 'text',
					'default' => ''
				),
				array(
					'name'  => 'modir_email',
					'label' => __( 'ایمیل مدیر', 'wedevs' ),
					'desc'  => __( 'ایمیل مدیر برای اطلاع رسانی', 'wedevs' ),
					'type'  => 'text'
				),
				array(
					'name'  => 'acc_1',
					'label' => __( 'اطلاعات حساب یک', 'wedevs' ),
					'type'  => 'textarea'
				),
				array(
					'name'  => 'acc_2',
					'label' => __( 'اطلاعات حساب دو', 'wedevs' ),
					'type'  => 'textarea'
				),
				array(
					'name'    => 'zarinpal',
					'label'   => __( 'کد مرچنت زرین پال', 'wedevs' ),
					'type'    => 'text',
					'default' => ''
				),
				array(
					'name'    => 'user_panel',
					'label'   => __( 'برگه لیست فاکتور کاربران', 'wedevs' ),
					'type'    => 'pages',
					'default' => ''
				),
			)
		);

		$this->setting = new \WeDevs_Settings_API();

		//set sections and fields
		$this->setting->set_sections( $sections );
		$this->setting->set_fields( $fields );

		//initialize them
		$this->setting->admin_init();
	}


}