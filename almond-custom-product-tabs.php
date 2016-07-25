<?php
/*
Plugin Name: [DEV 0.0.3] Almond Custom Product Tabs
Plugin URI: http://almondwp.com/woocommerce
Description: A plugin that allows you to add customized product tabs to your products in your online store, for WooCommerce.
Version: 0.0.3
Author: Gabriel Maldonado
Author URI: http://almondwp.com
Text Domain: almond-custom-product-tabs
*/

// Terminate the script if accessed outside of WordPress
if(!defined('ABSPATH')){
	exit;
}

//add_action( 'admin_notices', 'sample_admin_notice__error' );

/**
*
* The main Class
*
* @since 1.0
*/
class AWP_Custom_Product_Tabs{
    
    /**
    * __construct
    * Class constructor that will set all the needed filter and action hooks
    * 
    * @see woocommerce_settings_tabs_array
    * @see woocommerce_settings_tabs_awp_custom_tabs
    * @see woocommerce_update_options_awp_custom_tabs
    * @see save_awp_gma_tab_field
    * 
    * @return Class instance
    */
    function __construct(){
        if (is_admin()){

            add_filter( 'admin_head', array($this, 'awp_debug'));
            
            add_filter( 'woocommerce_settings_tabs_array', array($this,'woocommerce_settings_tabs_array'), 50 );
            add_action( 'woocommerce_settings_tabs_awp_custom_tabs', array($this,'show_settings_tab' ));
            add_action( 'woocommerce_update_options_awp_custom_tabs', array($this,'update_settings_tab' ));
            add_action( 'woocommerce_update_option_awp_gma_tab',array($this,'save_awp_gma_tab_field' ),10);
            add_action('woocommerce_admin_field_awp_gma_tab',array($this,'show_awp_gma_tab_field' ),10);
            //add product tab link in admin
            add_action( 'woocommerce_product_write_panel_tabs', array($this,'woocommerce_product_write_panel_tabs' ));
            //add product tab content in admin
            add_action('woocommerce_product_write_panels', array($this,'woocommerce_product_write_panels'));
            //save product selected tabs
            add_action('woocommerce_process_product_meta', array($this,'woocommerce_process_product_meta'), 10, 2);
       
        }else{
            //add tabs to product page
            add_filter( 'woocommerce_product_tabs', array($this,'woocommerce_product_tabs') );
        }

        add_action('wp_ajax_woocommerce_json_custom_tabs', array($this,'woocommerce_json_custom_tabs'));
        add_action( 'init', array($this,'custom_product_tabs_post_type'), 0 );
    }
	
	/**
	 * awp_debug
	 * Used for debugging purposes, development tool
	 * 
	 * @return $parameter mix
	 */
    function awp_debug($parameter){

        echo '<div class="wrap" style="margin-left:100px;">';
        echo '<br><span class="dashicons dashicons-info" style="color:red;"></span><strong style="color:red;">AWP DEBUG: </strong><br>';// . $message; // . (string)$parameter_name;
        var_dump($parameter);
        echo '</div>';

    }
    /**
     * woocommerce_settings_tabs_array
     * Used to add a WooCommerce settings tab
     * 
     * @param  array $settings_tabs
     * 
     * @return array
     */
    function woocommerce_settings_tabs_array( $settings_tabs ) {
        
        $settings_tabs['awp_custom_tabs'] = __('Almond Custom Tabs','almond-custom-product-tabs');
        return $settings_tabs;
        
    }
 
    /**
     * show_settings_tab
     * Used to display the WooCommerce settings tab content
     *
     * @see get_settings() Stores an array of settings
     * @see woocommerce_admin_fields() Accepts an array of settings to display
     *
     * @return void
     */
    function show_settings_tab(){
        
        woocommerce_admin_fields($this->get_settings());
    
    }
 
    /**
     * update_settings_tab
     * Used to save the WooCommerce settings tab values
     *
     * @see get_settings() Stores an array of settings
     * @see woocommerce_admin_fields() Accepts an array of settings to display
     *
     * @return void
     */
    function update_settings_tab(){
        
        woocommerce_update_options($this->get_settings());

    }
 
    /**
     * get_settings
     * Used to define the WooCommerce settings tab fields
     * 
     * @see show_settings_tab() Used to display the WooCommerce settings tab content
     * @see update_settings_tab() Used to save the WooCommerce settings tab values
     * 
     * @return void
     */
    function get_settings(){
        $settings = array(
            'section_title' => array(
                'name'     => __('Almond Custom Tabs','almond-custom-product-tabs'),
                'type'     => 'title',
                'desc'     => '',
                'id'       => 'wc_awp_custom_tabs_section_title',
            ),
            'title' => array(
                'name'     => __( 'Global Custom Tabs', 'almond-custom-product-tabs' ),
                'type'     => 'awp_gma_tab',
                'desc'     => __( 'Start typing the Custom Tab name, Used for including custom tabs on all products.', 'almond-custom-product-tabs' ),
                'desc_tip' => true,
                'default'  => '',
                'id'       => 'wc_awp_custom_tabs_globals',
            ),
            'section_end' => array(
                'type' => 'sectionend',
                'id'   => 'wc_awp_custom_tabs_section_end',
            )
        );

        return apply_filters( 'wc_awp_custom_tabs_settings', $settings );
    }
 
    /**
     * show_awp_gma_tab_field
     * Used to print the settings field of the custom type awp_gma_tab
     * 
     * @param  array $field
     *     
     * @return void
     */
    function show_awp_gma_tab_field($field){

        global $woocommerce;
        ?>
        
        <form method="post">
        <span class="dashicons dashicons-welcome-add-page"></span>
        <tr valign="top">
            <th scope="row" class="titledesc">
            <span class="dashicons dashicons-welcome-add-page"></span>
                <label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['title'] ); ?></label>
                <?php echo '<img class="help_tip" data-tip="' . esc_attr( $field['desc'] ) . '" src="' . $woocommerce->plugin_url() . '/assets/images/help.png" height="16" width="16" />'; ?>
            </th>
            <td class="forminp forminp-<?php echo sanitize_title( $field['type'] ) ?>">
                <p class="form-field custom_product_tabs">
                <div>
                    <select id="custom_product_tabs" multiple="multiple" name="<?php echo $field['id'];?>[]" style="height:100%;width:50%;">
                    <?php
                        $tabds_ids = get_option($field['id']);
                        $_ids = ! empty( $tabs_ids ) ? array_map( 'absint',  $tabs_ids ) : array();

                        foreach ($this->get_custom_tabs_list() as $id => $label) {
                            
                            $selected = in_array($id, $_ids)?  'selected="selected"' : '';
                            echo '<option value="' . $id . '" ' . $selected . ' >' . $label . '</option>';
                        }
                    ?>
                    </select>
                </div>
                <div>
                    <button type="submit" value="Submit"></button>
                </div>
                </p>
            </td>
        </tr>
        </form>

        <?php

    }
 
    /**
     * save_awp_gma_tab_field
     * Updates the settings of the custom tab
     * 
     * @see update_option() WordPress function that updates a named value/pair to the options database table.
     * @see delete_option() WordPress function that deletes a named value/pair to the options database table.
     * 
     * @param  array $field
     * 
     * @return void 
     */

    function save_awp_gma_tab_field($field){

        if (isset($_POST[$field['id']])){
            $option_value =   $_POST[$field['id']];
            update_option($field['id'],$option_value);
        }else{
            delete_option($field['id']);
        }
    }
 
    /**
     * woocommerce_product_write_panel_tabs
     * Used to add a product custom tab to product edit screen
     * 
     * @return void
     */
    function woocommerce_product_write_panel_tabs(){
        ?>
        <li class="custom_tab">
           <span class="dashicons dashicons-welcome-add-page"></span>
            <a href="#custom_tab_data_ctabs">
                <?php _e('Custom Tabs', 'almond-custom-product-tabs'); ?>
            </a>
        </li>
        <?php
    }
 
    /**
     * woocommerce_product_write_panels
     * Used to display a product custom tab content (fields) to product edit screen
     * 
     * @global $post 
     * @global $woocommerce
     * 
     * @see get_post_meta() Retrieve post meta field for a post.
     * @see get_custom_tabs_list() 
     * 
     * @return void
     */
    function woocommerce_product_write_panels() {
        global $post,$woocommerce;
        $fields = array(
            array(
                'key'   => 'custom_tabs_ids',
                'label' => __( 'Select Custom Tabs', 'almond-custom-product-tabs' ),
                'desc'  => __( 'Start typing the Custom Tab name, Used for including custom tabs.', 'almond-custom-product-tabs' )
            ),
            array(
                'key'   => 'exclude_custom_tabs_ids',
                'label' => __( 'Select Global Tabs to exclude', 'almond-custom-product-tabs' ),
                'desc'  => __( 'Start typing the Custom Tab name. used for excluding global tabs.', 'almond-custom-product-tabs' )
            )
        );
        ?>
        <div id="custom_tab_data_ctabs" class="panel woocommerce_options_panel">
            <?php

            foreach ($fields as $field) {
                $tabs_ids = get_post_meta( $post->ID, $field['key'], true );
                $_ids = ! empty( $tabs_ids ) ? array_map( 'absint',  $tabs_ids ) : array();
                ?>
                    <div class="">
                        <p class="">
                            <?php echo $field['label']; ?>
                            <select multiple="multiple" name="<?php echo $field['key']; ?>[]">
                                <?php 

                                    foreach ($this->get_custom_tabs_list() as $id => $label) {
                                        $selected = in_array($id, $_ids)?  'selected="selected"' : '';
                                        echo '<option value="' . $id . '" ' . $selected . ' >' . $label . '</option>';

                                    }


                                ?>   
                            </select>
                            
                        </p>
                    </div>
                <?php
            }

            ?>
        </div>
        <?php
    }
 
    /**
     * woocommerce_process_product_meta
     * used to save product custom tabs meta
     * 
     * @param  int $post_id
     * 
     * @return void
     */
    function woocommerce_process_product_meta( $post_id ) {
        foreach (array('exclude_custom_tabs_ids','custom_tabs_ids') as $key) {
            if (isset($_POST[$key]))
                update_post_meta( $post_id, $key, $_POST[$key]);
            else
                delete_post_meta( $post_id, $key);
        }  
    }
     
    /**
     * woocommerce_json_custom_tabs
     * An AJAX handler to list tabs for tabs field
     * 
     * prints out json of {tab_id: tab_name}
     * 
     * @return void
     */
    function woocommerce_json_custom_tabs(){
        check_ajax_referer( 'search-products-tabs', 'security' );//we validate the request to prevent processing requests external of the site using check_ajax_referer
        header( 'Content-Type: application/json; charset=utf-8' );//define the response header
        $term = (string) urldecode(stripslashes(strip_tags($_GET['term'])));//sanitize the search term
        if (empty($term)) die();//check that its not empty 
        $post_types = array('awp_gma_tab'); //check if the search term is numeric then we threat it as a tab id and we search for custom product tabs with that id, if its not numeric we search for custom product tabs with that string
        if ( is_numeric( $term ) ) {
            //by tab id
            $args = array(
                'post_type'      => $post_types,
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'post__in'       => array(0, $term),
                'fields'         => 'ids'
            );
 
            $args2 = array(
                'post_type'      => $post_types,
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'post_parent'    => $term,
                'fields'         => 'ids'
            );
 
            $posts = array_unique(array_merge( get_posts( $args ), get_posts( $args2 )));
 
        } else {
            //by name
            $args = array(
                'post_type'      => $post_types,
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                's'              => $term,
                'fields'         => 'ids'
            );
            $posts = array_unique( get_posts( $args ) );
        }
        //format the found tabs in array of tab id => tab title printout a json encoded version of that array and die() to end the ajax request.
        $found_tabs = array();
 
        if ( $posts ) foreach ( $posts as $post_id ) {
 
            $found_tabs[ $post_id ] = get_the_title($post_id);
        }
         
        $found_tabs = apply_filters( 'woocommerce_json_search_found_tabs', $found_tabs );
        echo json_encode( $found_tabs );
 
        die();
    }
 
    /**
     * woocommerce_product_tabs
     * Used to add custom tabs to product view page
     * 
     * @global post
     * 
     * @param  array $tabs
     *
     * @see get_option()
     * @see get_post_meta()
     *
     * @see post_exists()
     * @see render_tab()
     *
     * @see get_the_title()
     * @see apply_filters()
     * @see get_post_field()
     * 
     * @return array
     */
    function woocommerce_product_tabs($tabs){
        global $post;
        //get global tabs
        $global_tabs = get_option('wc_awp_custom_tabs_globals');
        $global_tabs_ids = ! empty( $global_tabs ) ? array_map( 'absint',  $global_tabs ) : array();
        /* Print debug on the product description*/
        $this->awp_debug($global_tabs_ids);
 
        //get global tabs to exclude from this product
        $exclude_tabs = get_post_meta( $post->ID, 'exclude_custom_tabs_ids', true );
        $exclude_tabs_ids = ! empty($exclude_tabs  ) ? array_map( 'absint',  $exclude_tabs ) : array();
        $this->awp_debug($exclude_tabs_ids);

        //get global tabs to include with current product
        $product_tabs = get_post_meta( $post->ID, 'custom_tabs_ids', true );
        $_ids = ! empty($product_tabs  ) ? array_map( 'absint',  $product_tabs ) : null;
        
        $this->awp_debug($product_tabs);
        $this->awp_debug($_ids);
 
        //combine global and product specific tabs and remove excluded tabs
        $_ids = array_merge((array)$_ids,(array)array_diff((array)$global_tabs_ids, (array)$exclude_tabs_ids));

        //AWP_Custom_Product_Tabs::awp_debug($global_tabs);
        if ($_ids){
            //fix order
            $_ids = array_reverse($_ids);
            //loop over tabs and add them
            foreach ($_ids as $id) {
                // if a post exist for this id, then...
            	if ($this->post_exists($id)){
					$display_title = get_post_meta($id,'tab_display_title',true);
					$priority      = get_post_meta($id,'tab_priority',true);
	                $tabs['customtab_'.$id] = array(
	                    'title'    => ( !empty($display_title)? $display_title : get_the_title($id) ),
	                    'priority' => ( !empty($priority)? $priority : 50 ),
	                    'callback' => array($this,'render_tab'),
	                    'content'  => apply_filters('the_content',get_post_field( 'post_content', $id)) //this allows shortcodes in custom tabs
	                );
            	}
            }
        }

        return $tabs;
    }

 
    /**
     * render_tab
     * Used to render tabs on product view page
     *
     * @see woocommerce_product_tabs()
     *
     * @param  string $key
     * @param  array  $tab
     * 
     * @return void
     */
    function render_tab($key,$tab){
        global $post;
        // added dashicon to test tab tab
        echo '<span class="dashicons dashicons-welcome-add-page"></span>';
        // 
        echo '<h2>'.apply_filters('AWP_custom_tab_title',$tab['title'],$tab,$key).'</h2>';
        echo apply_filters('AWP_custom_tab_content',$tab['content'],$tab,$key);
    }
 
    /**
     * custom_product_tabs_post_type
     * Register custom tabs Post Type
     * 
     * @return void
     */
    function custom_product_tabs_post_type() {
        $labels = array(
            'name'                => _x( 'AWP Product Tabs', 'Post Type General Name', 'almond-custom-product-tabs' ),
            'singular_name'       => _x( 'AWP Product Tab', 'Post Type Singular Name', 'almond-custom-product-tabs' ),
            'menu_name'           => __( 'product Tabs', 'almond-custom-product-tabs' ),
            'parent_item_colon'   => __( '', 'almond-custom-product-tabs' ),
            'all_items'           => __( 'AWP Product Tabs', 'almond-custom-product-tabs' ),
            'view_item'           => __( '', 'almond-custom-product-tabs' ),
            'add_new_item'        => __( 'Add AWP Product Tab', 'almond-custom-product-tabs' ),
            'add_new'             => __( 'Add New AWP Product Tab', 'almond-custom-product-tabs' ),
            'edit_item'           => __( 'Edit AWP Product Tab', 'almond-custom-product-tabs' ),
            'update_item'         => __( 'Update AWP Product Tab', 'almond-custom-product-tabs' ),
            'search_items'        => __( 'Search AWP Product Tab', 'almond-custom-product-tabs' ),
            'not_found'           => __( 'AWP Product Tab not found', 'almond-custom-product-tabs' ),
            'not_found_in_trash'  => __( 'AWP Product Tab not found in Trash', 'almond-custom-product-tabs' ),
        );
        $args = array(
            'label'               => __( 'Product Tabs', 'almond-custom-product-tabs' ),
            'description'         => __( 'Custom Product Tabs', 'almond-custom-product-tabs' ),
            'labels'              => $labels,
            'supports'            => array( 'title', 'editor', 'custom-fields' ),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => 'edit.php?post_type=product',
            'show_in_nav_menus'   => false,
            'show_in_admin_bar'   => true,
            'menu_position'       => 5,
            'menu_icon'           => 'dashicons-feedback',
            'can_export'          => true,
            'has_archive'         => false,
            'exclude_from_search' => true,
            'publicly_queryable'  => false,
            'capability_type'     => 'post',
        );
        register_post_type( 'awp_gma_tab', $args );
    }

    /**
    * Check if the post exists
    *
    * 
    * @see get_post_status retrieve the post status based on the post id
    * @see is_string($var) boolean True if $var is string, false otherwise
    *
    * @return boolean True if post status is string, false otherwise.
    */
    function post_exists($post_id){
        /* post exist, with an id of $post_id*/
        $this->awp_debug($post_id);

    	return is_string(get_post_status( $post_id ) );
    }

    /**
     * get_custom_tabs_list
     * 
     * @return array
     */
    function get_custom_tabs_list(){
        $args = array(
            'post_type'      => array('awp_gma_tab'),
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids'
        );
        $found_tabs = array();
        $posts = get_posts($args);
        if ( $posts ) foreach ( $posts as $post_id ) {
 
            $found_tabs[ $post_id ] = get_the_title($post_id);
        }
        return $found_tabs;
    }
}//end AWP_Custom_Product_Tabs class.

// Create the instance of the main class if WooComerce is active, return a message to activate it otherwise.
if(in_array('woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' )) )){
 	new AWP_Custom_Product_Tabs();	
} else {

    //sample_admin_notice__error();
    add_action( 'admin_init', 'my_plugin_deactivate' );
    add_action( 'admin_notices', 'my_plugin_admin_notice' );

    function my_plugin_deactivate() {
              deactivate_plugins( plugin_basename( __FILE__ ) );
          }
    function  my_plugin_admin_notice(){
        $class = 'notice notice-error';
        $message = __( 'You need to activate WooCommerce before using Almond Custom Product Tabs.', 'almond-custom-product-tabs' );

        printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 

        // deactivates defaud plugin activated
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }       
    }

}

//function sample_admin_notice__error() {
    // $class = 'notice notice-error';
    // $message = __( 'You need to activate WooCommerce before using this plugin.', 'almond-custom-product-tabs' );

    // printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
    //deactivate_plugins( plugin_basename( __FILE__ ) );
    //wp_die( );
//}