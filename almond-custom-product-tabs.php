<?php
/*
Plugin Name: More Product Tabs
Plugin URI: http://almondwp.com/woocommerce
Description: A plugin that allows you to add more customized product tabs to your products, for WooCommerce. Another neat plugin from AlmondWP.
Version: 1.0
Author: Gabriel Maldonado
Author URI: http://almondwp.com
Text Domain: awp-gma-more-product-tabs
*/

if(!defined('ABSPATH')){
	exit;
}

function show_awp_gma_more_product_tabs_link_on_activation($plugin_links) {
        
    $plugin_links[] = '<a href="' . get_site_url() . '/wp-admin/admin.php?page=wc-settings&tab=awp_custom_tabs' . '">Settings</a>';
    $plugin_links[] = '<a href="http://almondwp.com" target="_blank">More plugins by AlmondWP</a>';
    return $plugin_links;
}
add_filter("plugin_action_links_".plugin_basename(__FILE__), 'show_awp_gma_more_product_tabs_link_on_activation', 10, 5);

/**
*
* The main Class
*
* @since 1.0
*/
class AWP_GMA_More_Product_Tabs{
    
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

            
            add_action('admin_menu', array($this, 'awp_mpt_script'));
            
            add_filter( 'woocommerce_settings_tabs_array', array($this,'woocommerce_settings_tabs_array'), 50 );
            add_action( 'woocommerce_settings_tabs_awp_custom_tabs', array($this,'show_settings_tab' ));
            add_action( 'woocommerce_update_options_awp_custom_tabs', array($this,'update_settings_tab' ));
            add_action( 'woocommerce_update_option_awp_gma_tab',array($this,'save_awp_gma_tab_field' ),10);
            add_action('woocommerce_admin_field_awp_gma_tab',array($this,'show_awp_gma_tab_field' ),10);
            
            add_action( 'woocommerce_product_write_panel_tabs', array($this,'woocommerce_product_write_panel_tabs' ));
            
            add_action('woocommerce_product_write_panels', array($this,'woocommerce_product_write_panels'));
            
            add_action('woocommerce_process_product_meta', array($this,'woocommerce_process_product_meta'), 10, 2);
       
        }else{
            
            add_filter( 'woocommerce_product_tabs', array($this,'woocommerce_product_tabs') );
        }

        
        add_action( 'init', array($this,'custom_product_tabs_post_type'), 0 );
    }
    
    function awp_mpt_script() {

        wp_enqueue_script( 'awp-mpt-script', plugin_dir_url( __FILE__ ) . '/js/awp-mpt-script.js', array('jquery'), '1.0.0', true );

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
        
        $settings_tabs['awp_custom_tabs'] = __('More Product Tabs','awp-gma-more-product-tabs');
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
                'name'     => __('More Product Tabs','awp-gma-more-product-tabs'),
                'type'     => 'title',
                'desc'     => '',
                'id'       => 'wc_awp_custom_tabs_section_title',
            ),
            'title' => array(
                'name'     => __( 'Global Custom Tabs', 'awp-gma-more-product-tabs' ),
                'type'     => 'awp_gma_tab',
                'desc'     => __( 'Used for including custom tabs on all products.', 'awp-gma-more-product-tabs' ),
                'desc_tip' => true,
                'default'  => '',
                'id'       => 'wc_awp_custom_tabs_globals',
            ),
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
        <tr valign="top">
            <th scope="row" class="titledesc">
            
                <label for="<?php echo esc_attr( $field['id'] ); ?>">
                    <?php echo esc_html( $field['title'] ); ?>
                        
                </label>
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
           <!--<span class="dashicons dashicons-welcome-add-page"></span>-->
            <a href="#custom_tab_data_ctabs">
                <?php _e('More Product Tabs', 'awp-gma-more-product-tabs'); ?>
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
                'label' => __( 'Global Custom Tabs to display', 'awp-gma-more-product-tabs' ),
                'desc'  => __( 'Used for including custom tabs. Multiple selection allowed.', 'awp-gma-more-product-tabs' )
            ),
            array(
                'key'   => 'exclude_custom_tabs_ids',
                'label' => __( 'Global Custom Tabs to exclude', 'awp-gma-more-product-tabs' ),
                'desc'  => __( 'Used for excluding global tabs.Multiple selection allowed.', 'awp-gma-more-product-tabs' )
            )
        );
        ?>
        <div id="custom_tab_data_ctabs" class="panel woocommerce_options_panel">
        <div class="options_group">
        <p><strong>More Custom Tabs</strong> allows you to add new custom product tabs to the default ones. Need support? <a target="_blank" href="http://almondwp.com/woocommerce">Please click here</a>.</p>
        </div>
            <?php

            foreach ($fields as $field) {
                $tabs_ids = get_post_meta( $post->ID, $field['key'], true );
                $_ids = ! empty( $tabs_ids ) ? array_map( 'absint',  $tabs_ids ) : array();
                ?>
                    <div class="options_group">
                        <p class="form-field">
                            <label><?php echo $field['label']; ?></label>
                            <select class="<?php echo $field['key']?>" multiple="multiple" name="<?php echo $field['key']; ?>[]">
                                <?php 

                                    foreach ($this->get_custom_tabs_list() as $id => $label) {
                                        $selected = in_array($id, $_ids)?  'selected="selected"' : '';
                                        echo '<option class="'. $field['key'] .'" value="' . $id . '" ' . $selected . ' >' . $label . '</option>';

                                    }


                                ?>   
                            </select>
                            <!-- same class but unique identifier -->
                            <div class="test-div" id="<?php echo $field['key']; ?>"></div>
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
     * woocommerce_product_tabs
     * Used to add custom tabs to product view page
     * 
     * @global post
     * 
     * @param  array $tabs
     *
     *
     * @see post_exists()
     * @see render_tab()
     * @see get_settings() Settings are defined on get_settings, we can grab them with get_option
     * 
     * @return array
     */
    function woocommerce_product_tabs($tabs){
        global $post;

        
        $new_section = get_option('wc_awp_custom_test');
        

        
        $global_tabs = get_option('wc_awp_custom_tabs_globals');
        $global_tabs_ids = ! empty( $global_tabs ) ? array_map( 'absint',  $global_tabs ) : array();
        
        
 
        
        $exclude_tabs = get_post_meta( $post->ID, 'exclude_custom_tabs_ids', true );
        $exclude_tabs_ids = ! empty($exclude_tabs  ) ? array_map( 'absint',  $exclude_tabs ) : array();
        

        
        $product_tabs = get_post_meta( $post->ID, 'custom_tabs_ids', true );
        $_ids = ! empty($product_tabs  ) ? array_map( 'absint',  $product_tabs ) : null;
        
        
    
 
        //combine global and product specific tabs and remove excluded tabs
        $_ids = array_merge((array)$_ids,(array)array_diff((array)$global_tabs_ids, (array)$exclude_tabs_ids));

        
        if ($_ids){
        
            $_ids = array_reverse($_ids);
        
            foreach ($_ids as $id) {
        
            	if ($this->post_exists($id)){
					$display_title = get_post_meta($id,'tab_display_title',true);
					$priority      = get_post_meta($id,'tab_priority',true);
	                $tabs['customtab_'.$id] = array(
	                    'title'    => ( !empty($display_title)? $display_title : get_the_title($id) ),
	                    'priority' => ( !empty($priority)? $priority : 50 ),
	                    'callback' => array($this,'render_tab'),
	                    'content'  => apply_filters('the_content',get_post_field( 'post_content', $id))
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
     * @return html
     */
    function render_tab($key,$tab){
        global $post;
     
        echo '<h2>'.apply_filters('awp_gma_mpt_tab_title',$tab['title'],$tab,$key).'</h2>';
        echo apply_filters('awp_gma_mpt_tab_content',$tab['content'],$tab,$key);


    }
 
    /**
     * custom_product_tabs_post_type
     * Register custom tabs Post Type
     * 
     * @return void
     */
    function custom_product_tabs_post_type() {
        $labels = array(
            'name'                => _x( 'More Product Tabs', 'Post Type General Name', 'awp-gma-more-product-tabs' ),
            'singular_name'       => _x( 'More Product Tab', 'Post Type Singular Name', 'awp-gma-more-product-tabs' ),
            'menu_name'           => __( 'More Product Tabs', 'awp-gma-more-product-tabs' ),
            'parent_item_colon'   => __( '', 'awp-gma-more-product-tabs' ),
            'all_items'           => __( 'More Product Tabs', 'awp-gma-more-product-tabs' ), //dashboard products submenu
            'view_item'           => __( '', 'awp-gma-more-product-tabs' ),
            'add_new_item'        => __( 'Add Product Tab', 'awp-gma-more-product-tabs' ),
            'add_new'             => __( 'Add New Product Tab', 'awp-gma-more-product-tabs' ),
            'edit_item'           => __( 'Edit Product Tab', 'awp-gma-more-product-tabs' ),
            'update_item'         => __( 'Update Product Tab', 'awp-gma-more-product-tabs' ),
            'search_items'        => __( 'Search Product Tabs', 'awp-gma-more-product-tabs' ),
            'not_found'           => __( 'Product Tab not found', 'awp-gma-more-product-tabs' ),
            'not_found_in_trash'  => __( 'Product Tab not found in Trash', 'awp-gma-more-product-tabs' ),
        );
        $args = array(
            'label'               => __( 'More Product Tabs', 'awp-gma-more-product-tabs' ),
            'description'         => __( 'More Product Tabs', 'awp-gma-more-product-tabs' ),
            'labels'              => $labels,
            'supports'            => array( 'title', 'editor', 'custom-fields' ),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => 'edit.php?post_type=product',
            'show_in_nav_menus'   => false,
            'show_in_admin_bar'   => true,
            'menu_position'       => 5,
            'menu_icon'           => 'dashicons-welcome-add-page',
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
        //$this->awp_debug($post_id);

    	return is_string(get_post_status( $post_id ) );
    }

    /**
     *
     * Returns the custom tabs created by the user
     * get_custom_tabs_list
     * 
     * @return $found_tabs array Custom tabs created by the user and associated to its post ID
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
}


if(in_array('woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' )) )){
 	new AWP_GMA_More_Product_Tabs();	
} else {


    add_action( 'admin_init', 'awp_gma_more_product_tabs_deactivate' );
    add_action( 'admin_notices', 'awp_gma_more_product_tabs_admin_notice' );

    function awp_gma_more_product_tabs_deactivate() {
              deactivate_plugins( plugin_basename( __FILE__ ) );
          }
    function  awp_gma_more_product_tabs_admin_notice(){
        $class = 'notice notice-error';
        $message = __( 'You need to activate <a href="#">WooCommerce</a> before using <strong>More Product Tabs.</strong>', 'awp-gma-more-product-tabs' );

        printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 

  
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }       
    }

}