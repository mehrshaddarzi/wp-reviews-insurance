<?php
namespace WP_REVIEWS_INSURANCE;

/*
 * Define Post Type
 */
class Post_Type {

	public static $post_type;
	protected static $instance = null;

	/**
	 * Singleton class instance.
	 */
	public static function get() {
		if ( null === self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function __construct() {
		/*
		 * Set Post Type config
		 */
		self::$post_type = 'reviews';
		/*
		 * Create Book Post Type
		 */
		add_action( 'init', array( $this, 'create_post_type' ) );
		/*
		 * Change enter title Here
		 */
		add_filter( 'enter_title_here', array( $this, 'custom_enter_title' ) );
		/**
		 * Disable Gutenberg
		 */
		add_filter( 'use_block_editor_for_post_type', array( $this, 'disable_gutenberg' ), 10, 2 );
		add_filter( 'gutenberg_can_edit_post_type', array( $this, 'disable_gutenberg' ), 10, 2 ); //wp < 5.0
		/*
		 * Flush Rewrite in Not finding Post Type
		 */
		add_action( 'init', array( $this, 'flush_rewrite' ), 999 );
		/*
		 * Add column Post Type
		 */
		add_action( 'manage_' . Post_Type::$post_type . '_posts_custom_column', array( $this, 'column_post_table' ), 10, 2 );
		add_filter( 'manage_' . Post_Type::$post_type . '_posts_columns', array( $this, 'column_post_type' ) );
		add_filter( 'manage_edit-' . Post_Type::$post_type . '_sortable_columns', array( $this, 'sortable_column' ) );
		add_action( 'pre_get_posts', array( $this, 'action_type_orderby' ) );
	}

	/*
	 * Create Book Admin
	 */
	public function create_post_type() {
		$labels = array(
			'name'               => __( 'Insurance', 'wp-reviews-insurance' ),
			'singular_name'      => __( 'add Insurance', 'wp-reviews-insurance' ),
			'add_new'            => __( 'New Insurance', 'wp-reviews-insurance' ),
			'add_new_item'       => __( 'Add New Insurance', 'wp-reviews-insurance' ),
			'edit_item'          => __( 'Edit Insurance', 'wp-reviews-insurance' ),
			'new_item'           => __( 'New Insurance', 'wp-reviews-insurance' ),
			'all_items'          => __( 'All Insurance', 'wp-reviews-insurance' ),
			'view_item'          => __( 'Show Insurance', 'wp-reviews-insurance' ),
			'search_items'       => __( 'Search in Insurance', 'wp-reviews-insurance' ),
			'not_found'          => __( 'Not found Any Insurance', 'wp-reviews-insurance' ),
			'not_found_in_trash' => __( 'Not found any Insurance in Trash', 'wp-reviews-insurance' ),
			'parent_item_colon'  => __( 'Parent Insurance', 'wp-reviews-insurance' ),
			'menu_name'          => __( 'Insurance', 'wp-reviews-insurance' ),
		);
		$args   = array(
			'labels'                => $labels,
			'description'           => __( 'Insurance', 'wp-reviews-insurance' ),
			'public'                => true,
			'menu_position'         => 5,
			'has_archive'           => true,
			'show_in_admin_bar'     => false,
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'show_in_nav_menus'     => true,
			'menu_icon'             => 'dashicons-book',
			'can_export'            => true,
			'has_archive'           => true,
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'page',

			/* support */
			'supports'              => array( 'title', 'editor', 'thumbnail', 'author', 'comments', 'revisions' ),

			/* Rewrite */
			'rewrite'               => array( 'slug' => self::$post_type ),

			/* Rest Api */
			'show_in_rest'          => true,
			'rest_base'             => 'companies_api',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
		);
		register_post_type( self::$post_type, $args );

	}


	/*
	 * Flush Rewrite
	 */
	public function flush_rewrite() {

		if ( get_option( 'wp_reviews_post_type_flush' ) ) {
			/*
			 * Flush Rewrite
			 */
			flush_rewrite_rules();
			/*
			 * Remove Option
			 */
			delete_option( 'wp_reviews_post_type_flush' );
		}
	}

	/**
	 * Disable Gutenberg in custom post type
	 *
	 * @param $current_status
	 * @param $post_type
	 * @return bool
	 */
	public function disable_gutenberg( $current_status, $post_type ) {
		// Use your post type key instead of 'product'
		if ( $post_type === self::$post_type ) {
			return false;
		}
		return $current_status;
	}

	/*
	 * Change Title Enter Here
	 */
	public function custom_enter_title( $input ) {
		if ( self::$post_type === get_post_type() ) {
			return __( 'Please Enter the Name of the Company', 'wp-reviews-insurance' );
		}

		return $input;
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