<?php

namespace WP_REVIEWS_INSURANCE\Core;

/**
 * Class Admin_Setting_Api
 * @package WP_REVIEWS_INSURANCE
 * @see https://github.com/tareq1988/wordpress-settings-api-class
 */
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
				'id'    => 'wp_reviews_email_opt',
				'desc'  => __( 'Basic email settings', 'wp-reviews-insurance' ),
				'title' => __( 'Email', 'wp-reviews-insurance' )
			),
			array(
				'id'    => 'wp_reviews_insurance_opt',
				'title' => __( 'General', 'wp-reviews-insurance' )
			)
		);

		$fields = array(
			'wp_reviews_email_opt'     => array(
				array(
					'name'    => 'from_email',
					'label'   => __( 'From Email', 'wp-reviews-insurance' ),
					'type'    => 'text',
					'default' => get_option( 'admin_email' )
				),
				array(
					'name'    => 'from_name',
					'label'   => __( 'From Name', 'wp-reviews-insurance' ),
					'type'    => 'text',
					'default' => get_option( 'blogname' )
				),
				array(
					'name'         => 'email_logo',
					'label'        => __( 'Email Logo', 'wp-reviews-insurance' ),
					'type'         => 'file',
					'button_label' => 'choose logo image'
				),
				array(
					'name'    => 'email_body',
					'label'   => __( 'Email Body', 'wp-reviews-insurance' ),
					'type'    => 'wysiwyg',
					'default' => '<p>Hi, [fullname] </p> For Accept Your Reviews Please Click Bottom Link : <p> [link]</p>',
					'desc'    => 'Use This Shortcode :<br /> [fullname] : User Name <br /> [link] : Accept email link'
				),
				array(
					'name'    => 'email_footer',
					'label'   => __( 'Email Footer Text', 'wp-reviews-insurance' ),
					'type'    => 'wysiwyg',
					'default' => 'All rights reserved',
				)
			),
			'wp_reviews_insurance_opt' => array(
				array(
					'name'    => 'is_auth_ip',
					'label'   => __( 'IP Validation', 'wp-reviews-insurance' ),
					'type'    => 'select',
					'desc'    => 'Each user can only have one vote',
					'options' => array(
						'0' => 'No',
						'1' => 'yes'
					)
				),
				array(
					'name'    => 'email_auth',
					'label'   => __( 'Confirmation email', 'wp-reviews-insurance' ),
					'type'    => 'select',
					'desc'    => 'The user must click confirmation email',
					'options' => array(
						'0' => 'No',
						'1' => 'yes'
					)
				),
				array(
					'name'    => 'email_subject',
					'label'   => __( 'Email subject for Confirm', 'wp-reviews-insurance' ),
					'type'    => 'text',
					'default' => 'confirm your reviews',
					'desc'    => 'Use This Shortcode :</br> [fullname] : User Name<br /> [sitename] : Site Name',
				),
				array(
					'name'    => 'email_thanks_text',
					'label'   => __( 'Thanks Confirm Text', 'wp-reviews-insurance' ),
					'type'    => 'text',
					'default' => 'Thank You For Your Reviews.',
				),
				array(
					'name'    => 'star_color',
					'label'   => __( 'Star Rate color', 'wp-reviews-insurance' ),
					'type'    => 'color',
					'default' => '#f2b01e'
				),
				array(
					'name'    => 'thanks_text',
					'label'   => __( 'Thanks you Text', 'wp-reviews-insurance' ),
					'type'    => 'wysiwyg',
					'default' => 'Thanks you for this vote.'
				),
				array(
					'name'    => 'error_ip',
					'label'   => __( 'Duplicate ip error', 'wp-reviews-insurance' ),
					'type'    => 'textarea',
					'default' => 'Each user can only have one vote'
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