<?php
/*
Plugin Name: [0.0.1] Custom Product Tabs for WooCommerce
Plugin URI: http://almondwp.com
Description: A WordPress plugin to add custom product tabs to the products for WooCommerce.
Version: 0.0.1
Author: Gabriel Maldonado
Author URI: http://almondwp.com
*/

/**
*
* The main Class
*
* @since 
*/
class AWP_GMA_Custom_Product_Tabs{

	// Q: la clase esperaba una funcion __construct y dio error hasta que puse public delate de post-type, lo mismo para $id, me da Parse error: syntax error, unexpected '$id' (T_VARIABLE), expecting function (T_FUNCTION)
	//public $post_type = 'awp_tab';

	/**
	* Holds the custom post type id
	* @see woocommerce_settings_tabs_array()
	* @var string
	*/
	public $id = 'awp_custom_tabs';

	function __construct(){

		add_filter( 
			'woocommerce_settings_tabs_array', //tag
			/* call_user_func_array() expects parameter 1 to be a valid callback*/
			//'woocommerce_settings_tabs_array', //function
			array(
				$this, 
				'woocommerce_settings_tabs_array'
			),
			/* changed error to:
			Warning: call_user_func_array() expects parameter 1 to be a valid callback, class 'AWP_GMA_Custom_Product_Tabs' does not have a method 'woocommerce_settings_tabs_array'
			*/			
			50); //priority

		// Estaba en lo correcto al pensar que necesitaba iniciar de alguna manera el custom product tab post type
		add_action( 
			'init', 
			//'awp_gma_create_custom_product_tabs_post_type', 
			array(
				$this, 
				'awp_gma_create_custom_product_tabs_post_type'
			),
			0);
	}
	/*
	Warning: Invalid argument supplied for foreach() in /Applications/MAMP/htdocs/wordpress-core/build/wp-content/plugins/woocommerce/includes/admin/views/html-admin-settings.php on line 14

	woocommerce_settings_tabs_array is a method provided by woocommerce

	implementing the woocommerce_settings_tabs_array method of our class which we hook to woocommerce_settings_tabs_array filter hook provided by WooCommerce
	*/
    /**
     * woocommerce_settings_tabs_array
     * @param $test_param Array that contains all the parameters to display as navigation tabs under WooCommerce > Settings
     * @return 
     */	
	function woocommerce_settings_tabs_array($params){

		//print_r($test_param);
		$params[$this->id] = ('Custom Product Tabs');
		return $params;
		/*
		Undefined property
		AWP_GMA_Custom_Product_Tabs::$id
		*/
		

	}

		/**
		* Registers a new post type
		* @uses $wp_post_types Inserts new post type object into the list
		*
		* @param string  Post type key, must not exceed 20 characters
		* @param array|string  See optional args description above.
		* @return object|WP_Error the registered post type object, or an error object
		*/
		function awp_gma_create_custom_product_tabs_post_type() {
		
			$labels = array(
				'name'                => __( 'Plural Name', 'text-domain' ),
				'singular_name'       => __( 'Singular Name', 'text-domain' ),
				'add_new'             => _x( 'Add New Singular Name', 'text-domain', 'text-domain' ),
				'add_new_item'        => __( 'Add New Singular Name', 'text-domain' ),
				'edit_item'           => __( 'Edit Singular Name', 'text-domain' ),
				'new_item'            => __( 'New Singular Name', 'text-domain' ),
				'view_item'           => __( 'View Singular Name', 'text-domain' ),
				'search_items'        => __( 'Search Plural Name', 'text-domain' ),
				'not_found'           => __( 'No Plural Name found', 'text-domain' ),
				'not_found_in_trash'  => __( 'No Plural Name found in Trash', 'text-domain' ),
				'parent_item_colon'   => __( 'Parent Singular Name:', 'text-domain' ),
				'menu_name'           => __( 'Plural Name', 'text-domain' ),
			);
		
			$args = array(
				'labels'              => $labels,
				'hierarchical'        => false,
				'description'         => 'description',
				'taxonomies'          => array(),
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_menu'        => 'edit.php?post_type=product',
				//'show_in_admin_bar'   => true,
				'menu_position'       => null,
				'menu_icon'           => null,
				'show_in_nav_menus'   => true,
				'publicly_queryable'  => true,
				'exclude_from_search' => false,
				'has_archive'         => true,
				'query_var'           => true,
				'can_export'          => true,
				'rewrite'             => true,
				'capability_type'     => 'post',
				'supports'            => array(
					'title', 'editor', 'author', 'thumbnail',
					'excerpt','custom-fields', 'trackbacks', 'comments',
					'revisions', 'page-attributes', 'post-formats'
					)
			);
			register_post_type( 'awp_tab', $args );
			//http://localhost/wordpress-core/build/wp-admin/post-new.php?post_type=single-awp_tab
		}
		//add_action( 'init', 'awp_gma_create_custom_product_tabs_post_type' );
}
new AWP_GMA_Custom_Product_Tabs();