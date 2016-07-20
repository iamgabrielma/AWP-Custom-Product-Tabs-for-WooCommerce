<?php
/*
Plugin Name: [0.0.2] Custom Product Tabs for WooCommerce
Plugin URI: http://almondwp.com
Description: A WordPress plugin to add custom product tabs to the products for WooCommerce.
Version: 0.0.2
Author: Gabriel Maldonado
Author URI: http://almondwp.com
*/

class WC_Settings_Tab_Demo {

    /**
     * Bootstraps the class and hooks required actions & filters.
     *
     */
    function __construct() {

        add_filter( 'woocommerce_settings_tabs_array', __CLASS__ . '::add_settings_tab', 50 );
        add_action( 'woocommerce_settings_tabs_settings_tab_demo', __CLASS__ . '::settings_tab' );
        add_action( 'woocommerce_update_options_settings_tab_demo', __CLASS__ . '::update_settings' );
        
        add_action('init', array($this,'awp_gma_create_custom_product_tabs_post_type'), 0);

    }
    
    
    /**
     * Add a new settings tab to the WooCommerce settings tabs array.
     *
     * @param array $settings_tabs Array of WooCommerce setting tabs & their labels, excluding the Subscription tab.
     * @return array $settings_tabs Array of WooCommerce setting tabs & their labels, including the Subscription tab.
     */
    public static function add_settings_tab( $settings_tabs ) {
        $settings_tabs['settings_tab_demo'] = __( 'Settings Demo Tab', 'woocommerce-settings-tab-demo' );
        return $settings_tabs;
    }


    /**
     * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
     *
     * @uses woocommerce_admin_fields()
     * @uses self::get_settings()
     */
    public static function settings_tab() {
        woocommerce_admin_fields( self::get_settings() );
    }


    /**
     * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
     *
     * @uses woocommerce_update_options()
     * @uses self::get_settings()
     */
    public static function update_settings() {
        woocommerce_update_options( self::get_settings() );
    }


    /**
     * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
     *
     * @return array Array of settings for @see woocommerce_admin_fields() function.
     */
    public static function get_settings() {

        $settings = array(
            'section_title' => array(
                'name'     => __( 'Section Title', 'woocommerce-settings-tab-demo' ),
                'type'     => 'title',
                'desc'     => '',
                'id'       => 'wc_settings_tab_demo_section_title'
            ),
            'title' => array(
                'name' => __( 'Title', 'woocommerce-settings-tab-demo' ),
                'type' => 'text',
                'desc' => __( 'This is some helper text', 'woocommerce-settings-tab-demo' ),
                'id'   => 'wc_settings_tab_demo_title'
            ),
            'description' => array(
                'name' => __( 'Description', 'woocommerce-settings-tab-demo' ),
                'type' => 'textarea',
                'desc' => __( 'This is a paragraph describing the setting. Lorem ipsum yadda yadda yadda. Lorem ipsum yadda yadda yadda. Lorem ipsum yadda yadda yadda. Lorem ipsum yadda yadda yadda.', 'woocommerce-settings-tab-demo' ),
                'id'   => 'wc_settings_tab_demo_description'
            ),
            'section_end' => array(
                 'type' => 'sectionend',
                 'id' => 'wc_settings_tab_demo_section_end'
            )
        );

        return apply_filters( 'wc_settings_tab_demo_settings', $settings );
    }

    function awp_gma_create_custom_product_tabs_post_type(){
			
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
    }
    /**
    *
    *
    *
    */
    function show_awp_tab_field(){

    }
    /**
    *
    *
    *
    */
    function save_awp_tab_field(){

    }
    function woocommerce_product_write_panel_tabs(){

    	echo 'hello';
    }


}

new WC_Settings_Tab_Demo();