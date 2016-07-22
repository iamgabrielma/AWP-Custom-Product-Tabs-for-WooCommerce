<?php
/*
Plugin Name: [DEV 0.0.3] Almond Custom Product Tabs
Plugin URI: http://almondwp.com
Description: A plugin to add Custom product tabs for WooCommerce
Version: 0.0.3
Author: Gabriel Maldonado
Author URI: http://almondwp.com
*/

/**
*
* The main Class
*
* @since 1.0
*/

// object(AWP_Custom_Product_Tabs)#243 (2) { ["post_type"]=> string(11) "awp_gma_tab" ["id"]=> string(15) "awp_custom_tabs" } 
class AWP_Custom_Product_Tabs{
    /**
     * $post_type
     * holds custo post type name
     * @var string
     */
    public $post_type = 'awp_gma_tab';
    /**
     * $id
     * holds settings tab id
     * @var string
     */
    public $id = 'awp_custom_tabs';
    

    /**
    * __construct
    * class constructor will set the needed filter and action hooks
    */

    // el constructor llama a todas las funciones que creamos mas abajo
    function __construct(){
        if (is_admin()){


            /* AWP DEBUG DEV */
            /**
            *
            * @see awp_debug($parameter)
            *
            * Passes the awp_debug() function through the output of admin_head WordPress function, just for debugging purposes and information about whats happening at execution points.
            */
            add_filter( 'admin_head', array($this, 'awp_debug'));
            

            //add settings tab
            add_filter( 'woocommerce_settings_tabs_array', array($this,'woocommerce_settings_tabs_array'), 50 );
            //show settings tab
            add_action( 'woocommerce_settings_tabs_awp_custom_tabs', array($this,'show_settings_tab' ));
            //save settings tab SAVE ID
            add_action( 'woocommerce_update_options_awp_custom_tabs', array($this,'update_settings_tab' ));
            /*
            woocommerce_update_option_{field_type} action hook to save its value. SAVE POST TYPE
            */
            add_action( 'woocommerce_update_option_awp_gma_tab',array($this,'save_awp_gma_tab_field' ),10);
 
            //add tabs select field
            /*
            WooCommerce comes ready with these field types:

            text
            color
            image_width
            select
            checkbox
            textarea
            single_select_page
            single_select_country
            multi_select_countries
            email
            pasword
            number
            multiselect
            radio
            Maybe a few others

            Other than that you define your own custom type, providing the implementation via the woocommerce_admin_field_{field_type} action hook to display it
            */
            add_action('woocommerce_admin_field_awp_gma_tab',array($this,'show_awp_gma_tab_field' ),10);
            //save tabs select field

 
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
        //ajax search handler

        add_action('wp_ajax_woocommerce_json_custom_tabs', array($this,'woocommerce_json_custom_tabs'));
        //register_post_type
        // this is what gets called and creates the custom post type with the args on function custom_product_tabs_post_type()
        add_action( 'init', array($this,'custom_product_tabs_post_type'), 0 );
    }


    function awp_debug($parameter){
        //$parameter_name = "$parameter";

        echo '<div class="wrap" style="margin-left:100px;">';
        echo '<br><span class="dashicons dashicons-info" style="color:red;"></span><strong style="color:red;">AWP DEBUG: </strong><br>';// . $message; // . (string)$parameter_name;
        var_dump($parameter);
        echo '</div>';

    }
    /**
     * woocommerce_settings_tabs_array
     * Used to add a WooCommerce settings tab
     * @param  array $settings_tabs
     * @return array
     */

    // we need a way to hook our custom tab to a specific product and a way to make it global so we dont have to call it each time, so first we add a settings tab to woocommerce that allows to select which tabs to add.
    // woocommerce_settings_tabs_array is a method provided by woocommerce and we hook our method to the constructor
    // https://docs.woothemes.com/document/adding-a-section-to-a-settings-tab/
    // https://docs.woothemes.com/document/settings-api/
    // a simple method to add our WooCommerce settings tab to the tabs array
    function woocommerce_settings_tabs_array( $settings_tabs ) {
        $settings_tabs['awp_custom_tabs'] = __('Almond Custom Tabs','AWP');

        /* Para llamar a awp_debug() no puedo desde el scope de esta funcion ya que es una funcion fuera, pero si puedo llamarla como un metodo del objeto que comprende las dos.*/
        //AWP_Custom_Product_Tabs::awp_debug($settings_tabs);

        return $settings_tabs;
    }
 
    /**
     * show_settings_tab
     * Used to display the WooCommerce settings tab content
     *
     * @see get_settings()
     * @see woocommerce_admin_fields()
     *
     * @return void
     */
    // woocommerce_admin_fields() accepts an array of settings fields to display
    function show_settings_tab(){
        woocommerce_admin_fields($this->get_settings());
        // lo unico que hace es coger woocommerce_update_options y pasa las opciones, que basicamente es otra funcion, get_settings, que tiene que coger como $this-> ya que es otra funcion fuera del scope the update_settings_tab pero dentro de la clase.
    }
 
    /**
     * update_settings_tab
     * Used to save the WooCommerce settings tab values
     *
     * @see get_settings()
     * @see woocommerce_admin_fields()
     *
     * @return void
     */
    // woocommerce_update_options which accepts that same array of settings fields to save or update on submit
    function update_settings_tab(){
        woocommerce_update_options($this->get_settings());
        // lo mismo, salva lo que este en settings, podriamos hacer dos funciones diferentes pero son los mismos campos que get_settings asi que se reaprovecha de esta manera, ambas pasan como parametro otra funcion diferente.

    }
 
    /**
     * get_settings
     * Used to define the WooCommerce settings tab fields
     * @see update_settings_tab()
     * @return void
     * admin > woocommerce > settings > creates new AWP Custom Tabs
     */
    // because both show_settings_tab() and update_settings_tab() accept that same array we create a method get_settings to return that array instead of writing it twice (once in each method)
    function get_settings(){
        $settings = array(
            'section_title' => array(
                'name'     => __('Almond Custom Tabs','AWP'),
                'type'     => 'title',
                'desc'     => '',
                //'id'       => 'wc_'.$this->id.'_section_title' // adding .$this->id. makes it easier to reuse
                'id'       => 'wc_awp_custom_tabs_section_title',
            ),
            'title' => array(
                'name'     => __( 'Global Custom Tabs', 'AWP' ),
                //'type'     => $this->post_type,
                'type'     => 'awp_gma_tab',
                'desc'     => __( 'Start typing the Custom Tab name, Used for including custom tabs on all products.', 'AWP' ),
                'desc_tip' => true, // the ? simbol, tooltip
                'default'  => '',
                //'id'       => 'wc_'.$this->id.'_globals',
                'id'       => 'wc_awp_custom_tabs_globals',
            ),
            'section_end' => array(
                'type' => 'sectionend',
                /*
                `sectionend` field (which tells WooCommerce its the end of our settings section and in the middle we create a custom type field with the same name of our custom tabs post type (using $this->post_type)
                */
                //'id'   => 'wc_'.$this->id.'_section_end',
                'id'   => 'wc_awp_custom_tabs_section_end',
            )
        );
        //AWP_Custom_Product_Tabs::awp_debug($settings);
        //AWP_Custom_Product_Tabs::awp_debug($this->id);
        //AWP_Custom_Product_Tabs::awp_debug($this::$id);
         //awp_custom_tabs, me introduce la variable global y así no tengo que reescribirlo todas las veces. siendo $this el objeto y $id, 
        //AWP_Custom_Product_Tabs::awp_debug($this::$id); no funciona porque $id no es static, es instance, tengo que usar ->

        //return apply_filters( 'wc_'.$this->id.'_settings', $settings );
        return apply_filters( 'wc_awp_custom_tabs_settings', $settings );
    }
 
    /**
     * show_awp_gma_tab_field
     * Used to print the settings field of the custom type awp_gma_tab
     * @param  array $field
     *
     * @see ajax_footer_js
     *     
     * @return void
     */
    /*
    we need to define a method named show_awp_gma_tab_field which we will hook using the woocommerce_admin_field_awp_gma_tab action hook to display our custom settings field and another method named save_awp_gma_tab_field to save the settings which we will hook using woocommerce_update_option_awp_gma_tab action hook:
    
    In show_awp_gma_tab_field we mimic the markup of the “Products Select Field Type” which is used by WooCommerce for selecting linked products, up sale products, cross sale products and probbly in other places as well. We do that to get the same functionality of these fields which is: you start typing the product name or id and an AJAX call is made to search the database for products with that string in the name or with that id (depends on what you are typing).
    */
    function show_awp_gma_tab_field($field){
        /*
        WooCommerce > Settings > Amond Custom Tabs
        */
        global $woocommerce;
        ?>
        <!-- ADDED on the top, below the <h2> -->
        <form method="post">
        <span class="dashicons dashicons-welcome-add-page"></span>
        
        <tr valign="top">
            <th scope="row" class="titledesc">
            <!-- ADDED TO Global Custom tabs text -->
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
                        //$_ids = ['1417']; // test for selected one post
                        $_ids = ! empty( $tabs_ids ) ? array_map( 'absint',  $tabs_ids ) : array(); //magia

                        foreach ($this->get_custom_tabs_list() as $id => $label) {
                            
                            $selected = in_array($id, $_ids)?  'selected="selected"' : '';
                            

                            //$label es basicamente las nuevas tablas que creamos globales, por eso hay que meter las <option> dentro del loop, para que lo cojan como valor de la opcion, lo mismo para id si no quiero usar $field['id']
                            //echo $label; //this is a new tab, this is a new tab 2, ...
                            echo '<option value="' . $id . '" ' . $selected . ' >' . $label . '</option>';
                        }
                    ?>
                        

                    </select>
                </div>
                <div>
                     
                        <button type="submit" value="Submit"></button>
                    
                </div>
                    <!--<select id="custom_product_tabs" style="width: 50%;" name="<?php/* echo $field['id'];?>[]" class="ajax_chosen_select_tabs" multiple="multiple" data-placeholder="<?php _e( 'Search for a custom AWP tab&hellip;', 'AWP' ); ?>"> 

                        <?php   
                        /*
                            $tabs_ids = get_option($field['id']);
                            $_ids = ! empty( $tabs_ids ) ? array_map( 'absint',  $tabs_ids ) : array();
                            foreach ( $this->get_custom_tabs_list() as $id => $label ) {
                                $selected = in_array($id, $_ids)?  'selected="selected"' : '';
                                echo '<option value="' . esc_attr( $id ) . '"'.$selected.'>' . esc_html( $label ) . '</option>';
                            }
                        */
                        ?>
                    <!--</select>-->
                </p>
            </td>
        </tr>
        </form>


        <?php

        //AWP_Custom_Product_Tabs::awp_debug($woocommerce);
        /* object(WooCommerce)#244 (10) { ["version"]=> string(5) "2.6.2" ["session"]=> NULL ["query"]=> object(WC_Query)#248 (1) { ["query_vars"]=> array(13) { ["order-pay"]=> string(9) "order-pay" ["order-received"]=> string(14) "order-received" ["orders"]=> string(6) "orders" ["view-order"]=> string(10) "view-order" ["downloads"]=> string(9) "downloads" ["edit-account"]=> string(12) "edit-account" ["edit-address"]=> string(12) "edit-address" ["payment-methods"]=> string(15) "payment-methods" ["lost-password"]=> string(13) "lost-password" ["customer-logout"]=> string(15) "customer-logout" ["add-payment-method"]=> string(18) "add-payment-method" ["delete-payment-method"]=> string(21) "delete-payment-method" ["set-default-payment-method"]=> string(26) "set-default-payment-method" } } ["product_factory"]=> object(WC_Product_Factory)#214 (0) { } ["countries"]=> object(WC_Countries)#219 (2) { ["locale"]=> NULL ["address_formats"]=> NULL } ["integrations"]=> object(WC_Integrations)#166 (1) { ["integrations"]=> array(0) { } } ["cart"]=> NULL ["customer"]=> NULL ["order_factory"]=> object(WC_Order_Factory)#168 (0) { } ["api"]=> object(WC_API)#249 (2) { ["server"]=> NULL ["authentication"]=> NULL } } 
        */
        //AWP_Custom_Product_Tabs::awp_debug($woocommerce->plugin_url());
        // string(68) "http://localhost/wordpress-core/build/wp-content/plugins/woocommerce" 
        //AWP_Custom_Product_Tabs::awp_debug($field['id']);
        // string(26) "wc_awp_custom_tabs_globals" 

        /*
        We change the select tag element class attribute to ajax_chosen_select_tabs because we don’t want to search for products, we want to search for tabs. So next we need to tell WooCommerce (well not as much WooCommerce as ajaxChosen library which is included by WooCommerce) to search for tabs when we type in a name or an id and we do so by hooking another method named ajax_footer_js on line 33, so let implement that:
        */
        //add_action('admin_footer',array($this,'ajax_footer_js'));
    }
 
    /**
     * save_awp_gma_tab_field
     * Used to save the settings field of the custom type awp_gma_tab
     * @param  array $field
     * @return void
     */
    /*
    In save_awp_gma_tab_field method we simple save the value if it is posted as an option or delete the option if nothing is posted.
    */
    function save_awp_gma_tab_field($field){

        if (isset($_POST[$field['id']])){
            $option_value =   $_POST[$field['id']];
            update_option($field['id'],$option_value);
        }else{
            delete_option($field['id']);
        }
        //AWP_Custom_Product_Tabs::awp_debug($field['id']);
    }
 
    /**
     * ajax_footer_js
     * Used to add needed javascript to product edit screen and custom settings tab
     *
     * @see show_awp_gma_tab_field ()
     * @see woocommerce_product_write_panels()
     *
     * @return void
     */
    /*We change the select tag element class attribute to ajax_chosen_select_tabs because we don’t want to search for products, we want to search for tabs. So next we need to tell WooCommerce (well not as much WooCommerce as ajaxChosen library which is included by WooCommerce) to search for tabs when we type in a name or an id and we do so by hooking another method named ajax_footer_js on line 33, so let implement that:

    A better practice solution would be to include this method as an external JavaScript file using wp_enqueue_script
    */
    /*
    function ajax_footer_js(){
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($){
            // Ajax Chosen Product Selectors
            jQuery("select.ajax_chosen_select_tabs").select2({});
            // class ajax_chosen_select_tabs in woocommerce_product_write_panels()
            // select2 -> jquery for selecting https://select2.github.io/examples.html
        });
        </script>
        <?php
    }
    */
    /**
     * woocommerce_product_write_panel_tabs
     * Used to add a product custom tab to product edit screen
     * @return void
     */
    //add our fields tab to the metabox using the woocommerce_product_write_panel_tabs action hook in the code>woocommerce_product_write_panel_tabs method:
    function woocommerce_product_write_panel_tabs(){
        ?>
        <li class="custom_tab">
            <!--<span class="dashicons dashicons-welcome-add-page"></span>-->
            <a href="#custom_tab_data_ctabs">
                <?php _e('Custom Tabs', 'AWP'); ?>
            </a>
        </li>
        <?php
    }
 
    /**
     * woocommerce_product_write_panels
     * Used to display a product custom tab content (fields) to product edit screen
     * @return void
     */
    // render the content of the metabox tab using the woocommerce_product_write_panels action hook in the code>woocommerce_product_write_panels method: products > product > custom tabs > select globals and globals to exclude
    function woocommerce_product_write_panels() {
        global $post,$woocommerce;
        //define an array of the two fields arguments
        $fields = array(
            array(
                'key'   => 'custom_tabs_ids',
                'label' => __( 'Select Custom Tabs', 'AWP' ),
                'desc'  => __( 'Start typing the Custom Tab name, Used for including custom tabs.', 'AWP' )
            ),
            array(
                'key'   => 'exclude_custom_tabs_ids',
                'label' => __( 'Select Global Tabs to exclude', 'AWP' ),
                'desc'  => __( 'Start typing the Custom Tab name. used for excluding global tabs.', 'AWP' )
            )
        );
        ?>
        <div id="custom_tab_data_ctabs" class="panel woocommerce_options_panel">
            <?php
            // output the fields markup one at a time
            
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

            /*
            foreach ($fields as $f) {
                $tabs_ids = get_post_meta( $post->ID, $f['key'], true );
                $_ids = ! empty( $tabs_ids ) ? array_map( 'absint',  $tabs_ids ) : array();
                ?>

                <div class="options_group">
                    <p class="form-field custom_product_tabs">
                    
                    <!--WORKS BUT NEEDS SOME LOVE <span class="dashicons dashicons-welcome-add-page"></span>-->

                        <label for="custom_product_tabs"><?php echo $f['label']; ?></label>
                        
                        <select style="width: 50%;" id="<?php echo $f['key']; ?>" name="<?php echo $f['key']; ?>[]" class="ajax_chosen_select_tabs" multiple="multiple" data-placeholder="<?php _e( 'Search for a custom tab&hellip;', 'AWP' ); ?>">
                            
                            <?php                           
                                foreach ( $this->get_custom_tabs_list() as $id => $label ) {
                                    $selected = in_array($id, $_ids)?  'selected="selected"' : '';
                                    echo '<option value="' . esc_attr( $id ) . '"'.$selected.'>' . esc_html( $label ) . '</option>';
                                }
                            ?>
                        </select> <img class="help_tip" data-tip="<?php echo esc_attr($f['desc']); ?>" src="<?php echo $woocommerce->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
                    </p>
                </div>
                <?php
            }
            */
            ?>
        </div>
        <?php
        //add_action('admin_footer',array($this,'ajax_footer_js')); //hook our ajax_footer_js method from before which we will use once
    }
 
    /**
     * woocommerce_process_product_meta
     * used to save product custom tabs meta
     * @param  int $post_id
     * @return void
     */
    //To finish up the meta box methods we need to implement the method which we will use to save these fields values as product meta. 
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
     * prints out json of {tab_id: tab_name}
     * @return void
     */
    /*
    We simply define a GET request method, json data type, admin ajax url and data parameters action named woocommerce_json_custom_tabs and security nonce. This tell `ajaxChosen` to make an ajax request to the server to look for tabs, so we need to implement that and we do it in woocommerce_json_custom_tabs method:
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
     * Used to add tabs to product view page
     * @param  array $tabs
     * @return array
     */
    //implement the woocommerce_product_tabs method which is hooked to the woocommerce_product_tabs filter hook. displaying the custom product tabs
    function woocommerce_product_tabs($tabs){
        global $post;
        //AWP_Custom_Product_Tabs::awp_debug($this->post);
        //get global tabs
        $global_tabs = get_option('wc_awp_custom_tabs_globals');
        $global_tabs_ids = ! empty( $global_tabs ) ? array_map( 'absint',  $global_tabs ) : array();
 
        //get global tabs to exclude from this product
        $exclude_tabs = get_post_meta( $post->ID, 'exclude_custom_tabs_ids', true );
        $exclude_tabs_ids = ! empty($exclude_tabs  ) ? array_map( 'absint',  $exclude_tabs ) : array();
 
        //get global tabs to include with current product
        $product_tabs = get_post_meta( $post->ID, 'custom_tabs_ids', true );
        $_ids = ! empty($product_tabs  ) ? array_map( 'absint',  $product_tabs ) : null;
        
        // fail porque esto deberia ser array y es una string
        var_dump($_ids);
        var_dump($product_tabs);
        var_dump($global_tabs);
 
        //combine global and product specific tabs and remove excluded tabs
        $_ids = array_merge((array)$_ids,(array)array_diff((array)$global_tabs_ids, (array)$exclude_tabs_ids));
        //AWP_Custom_Product_Tabs::awp_debug($global_tabs);
        if ($_ids){
            //fix order
            $_ids = array_reverse($_ids);
            //loop over tabs and add them
            foreach ($_ids as $id) {
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
     * @return void
     */
    function render_tab($key,$tab){
        global $post;
        //echo '<span class="dashicons dashicons-welcome-add-page"></span>';
        echo '<h2>'.apply_filters('AWP_custom_tab_title',$tab['title'],$tab,$key).'</h2>';
        echo apply_filters('AWP_custom_tab_content',$tab['content'],$tab,$key);
    }
 
    /**
     * custom_product_tabs_post_type
     * Register custom tabs Post Type
     * @return void
     */
    // easier point to start, the end, we create our "custom tab" custom post type -> https://generatewp.com/post-type/, creates admin > products > product tabs
    function custom_product_tabs_post_type() {
        $labels = array(
            'name'                => _x( 'AWP Product Tabs', 'Post Type General Name', 'AWP' ),
            'singular_name'       => _x( 'AWP Product Tab', 'Post Type Singular Name', 'AWP' ),
            'menu_name'           => __( 'product Tabs', 'AWP' ),
            'parent_item_colon'   => __( '', 'AWP' ),
            'all_items'           => __( 'AWP Product Tabs', 'AWP' ),
            'view_item'           => __( '', 'AWP' ),
            'add_new_item'        => __( 'Add AWP Product Tab', 'AWP' ),
            'add_new'             => __( 'Add New AWP Product Tab', 'AWP' ),
            'edit_item'           => __( 'Edit AWP Product Tab', 'AWP' ),
            'update_item'         => __( 'Update AWP Product Tab', 'AWP' ),
            'search_items'        => __( 'Search AWP Product Tab', 'AWP' ),
            'not_found'           => __( 'AWP Product Tab not found', 'AWP' ),
            'not_found_in_trash'  => __( 'AWP Product Tab not found in Trash', 'AWP' ),
            /*
            _x()
            Quite a few times, there will be collisions with similar translatable text found in more than two places, but with different translated context.
            By including the context in the pot file, translators can translate the two strings differently.
            
            __() Retrieve the translation of $text.

            */
        );
        $args = array(
            'label'               => __( 'Product Tabs', 'AWP' ),
            'description'         => __( 'Custom Product Tabs', 'AWP' ),
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
        // http://localhost/wordpress-core/build/wp-admin/post-new.php?post_type=awp_gma_tab
        // is the "add proudct tab" page, aunque parece que no construye el HTML asi que asumo que eso lo pone woocommerce a traves de su api.
    }

    function post_exists($post_id){

    	return is_string(get_post_status( $post_id ) );
    }

    /**
     * get_custom_tabs_list
     * @since 1.2
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
new AWP_Custom_Product_Tabs();