<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Class_Pi_Sales_Notification_Product{

    public $plugin_name;

    private $settings = array();

    private $active_tab;

    private $this_tab = 'default';

    private $tab_name = "Product selection";

    private $setting_key = 'pi_sn_product_setting';
    
    private $product_selection_method = array('orders'=>'Orders placed (Real sales)', 'recently-viewed-products'=>'Recently viewed products (Fake sales)','selected-products'=>'Show selected products (Fake sales)', 'selected-categories'=>'Show product from selected category (Fake sales)');

    public $pi_sn_product_selection;

    public $tab;    

    function __construct($plugin_name){
        $this->plugin_name = $plugin_name;

        $this->settings = array(
            array('field'=>'title', 'class'=> 'bg-dark2 text-light', 'class_title'=>'text-light font-weight-light h4', 'label'=>__("Which products to show in live sales popup",'pisol-sales-notification'), 'type'=>"setting_category"),
            array('field'=>'pi_sn_product_selection', 'label'=>__('Select product from','pisol-sales-notification'),'type'=>'select', 'default'=>"selected-categories", 'value'=>$this->product_selection_method,  'desc'=>__('Using this you can set which product will be shown in the notification popup','pisol-sales-notification')),
            array('field'=>'pi_sn_custom_first_name'),
            array('field'=>'pi_sn_custom_location'),
            array('field'=>'pi_sn_selected_product'),
            array('field'=>'pi_sn_selected_category'),
            array('field'=>'pi_sn_order_status'),
            array('field'=>'pi_sn_order_tags'),
            array('field'=>'pi_sn_time_unit'),
            array('field'=>'pi_sn_time_value'),
            array('field'=>'pi_sn_max_product_show'),
        );


        $this->tab = sanitize_text_field(filter_input( INPUT_GET, 'tab'));
        $this->active_tab = $this->tab != "" ? $this->tab : 'default';

        if($this->this_tab == $this->active_tab){
            add_action($this->plugin_name.'_tab_content', array($this,'tab_content'));
        }


        add_action($this->plugin_name.'_tab', array($this,'tab'),1);

        add_action( 'wp_ajax_pi_search_product', array( $this, 'search_product' ) );
        add_action( 'wp_ajax_pi_search_category', array( $this, 'search_category' ) );
        
        $this->register_settings();
        
        if(PI_SALES_NOTIFICATION_DELETE_SETTING){
            $this->delete_settings();
        }
    }

    
    function delete_settings(){
        foreach($this->settings as $setting){
            delete_option( $setting['field'] );
        }
    }

    


    function register_settings(){   

        foreach($this->settings as $setting){
            register_setting( $this->setting_key, $setting['field']);
        }
    
    }

    function tab(){
        ?>
        <a class=" <?php echo ($this->active_tab == $this->this_tab ? 'bg-primary' : ''); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page='.sanitize_text_field($_GET['page']).'&tab='.$this->this_tab ) ); ?>">
        <span class="dashicons dashicons-products"></span> <?php echo esc_html( $this->tab_name ); ?>
        </a>
        <?php
    }

    function tab_content(){
       $vname = get_option("pi_sn_custom_first_name","John&#10;Smith&#10;Adrianus&#10;Dirk&#10;Aldert");
       $vlocation = get_option("pi_sn_custom_location","New York City, New York, USA&#10;Bernau, Freistaat Bayern, Germany");
       $selected_products = get_option("pi_sn_selected_product",array());
       $selected_categories = get_option("pi_sn_selected_category",array());
       $selected_order_status = get_option("pi_sn_order_status",array('wc-completed'));
       $pi_sn_time_unit = get_option("pi_sn_time_unit",'day');
       $pi_sn_time_value = get_option("pi_sn_time_value",1);
       $pi_sn_max_product_show = get_option("pi_sn_max_product_show",10);

       $selected_tags = get_option('pi_sn_order_tags', array());
       if(empty($selected_tags) || !is_array($selected_tags)){
            $selected_tags = array();
       }
       ?>
        <form method="post" action="options.php"  class="pisol-setting-form">
        <?php settings_fields( $this->setting_key ); ?>
        <?php
            foreach($this->settings as $setting){
                new pisol_class_form_sn_v3_7($setting, $this->setting_key);
            }
        ?>
        <div id="orders">
            <div class="pisol-form-element-row field-type-setting_category">
                <div class="pisol-form-label-col">
                <h2 class="pisol-field-title">Order related options</h2>
                </div>
            </div>
            <div class="pisol-form-element-row field-type-select_multiple">
                <div class="pisol-form-label-col">
                    <label for="pi_sn_order_status" class="pisol-field-label">Based on the order status orders will be selected</label>
                </div>
                <div class="pisol-form-setting-col">
                    <select id="pi_sn_order_status" name="pi_sn_order_status[]" multiple="multiple" style="width:100%;">
                        <?php 
                            $order_status = wc_get_order_statuses(); 
                            foreach($order_status as $key=>$val){
                                echo '<option value="'.esc_attr( $key ).'" '.(in_array($key, $selected_order_status) ? ' selected="selected" ': '').'>'.esc_html( $val ).'</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>

            <div class="pisol-form-element-row field-type-setting_category">
                <div class="pisol-form-label-col">
                <h2 class="pisol-field-title">Order tags</h2>
                </div>
            </div>

            <div class="pisol-form-element-row field-type-select_multiple">
                <div class="pisol-form-label-col">
                    <label for="pi_sn_order_tags" class="pisol-field-label">Order tags to consider</label>
                    <div class="pisol-field-description"><small>Only order with these tags will be considered for the popup, leave blank if you want to consider all orders</small></div>
                </div>
                <div class="pisol-form-setting-col">
                    <?php do_action('pisol_sales_notification_dependency_install'); ?>
                    <?php 
                        $tags = self::get_tags();
                        if(empty($tags)){
                            echo '<p>'.__('No order tag found, create order tags in WooCommerce > Order Tags ', 'live-sales-notifications-for-woocommerce').'</p>';
                        }else{
                    ?>
                    <select id="pi_sn_order_tags" multiple name="pi_sn_order_tags[]" style="width:100%;" class="form-control">
                        <?php 
                            foreach($tags as $tag_id => $tag_name){
                                echo '<option value="'.esc_attr($tag_id).'" '.(in_array($tag_id, $selected_tags) ? 'selected="selected"' : '').'>'.esc_html($tag_name).'</option>';
                            }
                        ?>
                    </select>
                    <?php } ?>
                </div>
            </div>        
        </div>
        
        <div id="selected-products">
            <div class="pisol-form-element-row field-type-setting_category">
                <div class="pisol-form-label-col">
                <h2 class="pisol-field-title">Select product to show in popup</h2>
                </div>
            </div>
            <div class="pisol-form-element-row field-type-select_multiple">
                <div class="pisol-form-label-col">
                    <label for="pi_sn_selected_product" class="pisol-field-label">Select product</label>
                </div>
                <div class="pisol-form-setting-col">
                    <select type="text" id="pi_sn_selected_product" name="pi_sn_selected_product[]" class="pi_add_product" style="width:100%;" multiple="multiple">
                        <?php
                        if(is_array($selected_products)):
                            foreach($selected_products as $product){
                                echo '<option value="'.esc_attr( $product ).'" selected="selected">'.esc_html( pisol_sn_common::getTitle($product)." (ID: {$product})" ).'</option>';
                            }
                        endif;
                        ?>
                    </select>
                </div>
            </div>
        </div>
        <div id="selected-categories">
            <div class="pisol-form-element-row field-type-setting_category">
                <div class="pisol-form-label-col">
                <h2 class="pisol-field-title">Select category to show product</h2>
                </div>
            </div>
            <div class="pisol-form-element-row field-type-select_multiple">
                <div class="pisol-form-label-col">
                    <label for="pi_sn_selected_category" class="pisol-field-label">Select category</label>
                </div>
                <div class="pisol-form-setting-col">
                    <select id="pi_sn_selected_category" name="pi_sn_selected_category[]" class="pi_add_category" style="width:100%;" multiple="multiple">
                        <?php
                        if(is_array($selected_categories)):
                            foreach($selected_categories as $category_id){
                                $obj = get_term_by( 'id', $category_id, 'product_cat' );
                                if(!is_object($obj)) continue;
                                echo '<option value="'.esc_attr( $category_id ).'" selected="selected">'.esc_html( $obj->name ." (ID: {$category_id})" ).'</option>';
                            }
                        endif;
                        ?>
                    </select>
                </div>
            </div>
        </div>
        <div id="virtual-name-location">
            <div class="pisol-form-element-row field-type-setting_category">
                <div class="pisol-form-label-col">
                <h2 class="pisol-field-title">Virtual First name and Location</h2>
                </div>
            </div>
            <div class="pisol-form-element-row field-type-textarea">
                <div class="pisol-form-label-col">
                    <label for="pi_sn_custom_first_name" class="pisol-field-label">Virtual first name</label>
                    <div class="pisol-field-description"><small>This name will be used, when you decide to show virtual sales, Enter one name on one line</small></div>
                </div>
                <div class="pisol-form-setting-col">
                    <textarea name="pi_sn_custom_first_name" id="pi_sn_custom_first_name" class="form-control" style="height:200px !important;" placeholder="John&#10;Smith&#10;Adrianus&#10;Dirk&#10;Aldert"><?php echo esc_html($vname); ?></textarea>
                </div>
            </div>
            
            <div class="pisol-form-element-row field-type-switch free-version">
                <div class="pisol-form-label-col">
                    <label for="pi_sn_use_geolocation" class="pisol-field-label">Use visitor country</label>
                    <div class="pisol-field-description"><small>In the fake sales notification customer location will be shown as the visitors country<br>If you use this option then you can only use short code {country} and {state} in the message, {city} short code will not work</small></div>
                </div>
                <div class="pisol-form-setting-col">
                    <div class="custom-control custom-switch">
                    <input type="checkbox" value="1" class="custom-control-input" name="pi_sn_use_geolocation" id="pi_sn_use_geolocation" >
                    <label class="custom-control-label" for="pi_sn_use_geolocation"></label>
                    </div>  
                </div>
            </div>
            
            <div class="pisol-form-element-row field-type-textarea">
                <div class="pisol-form-label-col">
                    <label for="pi_sn_custom_location" class="pisol-field-label">Virtual location</label>
                    <div class="pisol-field-description"><small>One location on one line eg: city, state, country if you dont have state then this will be like this <br>e.g: city , , country, if you dont have city then e.g: , state, country</small></div>
                </div>
                <div class="pisol-form-setting-col">
                    <textarea name="pi_sn_custom_location" id="pi_sn_custom_location" class="form-control" style="height:200px !important;" placeholder="City, State, Country&#10;New York City, New York, USA&#10;Bernau, Freistaat Bayern, Germany"><?php echo esc_html($vlocation); ?></textarea> 
                </div>
            </div>
        </div>
        <div id="order-timing">
            <div class="pisol-form-element-row field-type-setting_category">
                <div class="pisol-form-label-col">
                <h2 class="pisol-field-title">How much old order should be shown</h2>
                </div>
            </div>

            <div class="pisol-form-element-row field-type-textarea">
                <div class="pisol-form-label-col">
                    <label for="pi_sn_time_value" class="pisol-field-label">Show orders placed in last X time</label>
                    <div class="pisol-field-description"><small>e.g: 1 day: will show order placed in last one day <br>e.g: 1 hour: will show order placed in last one hour</small></div>
                </div>
                <div class="pisol-form-setting-col">
                    <div style="display:flex; grid-gap:20px;">
                        <input type="number" min="1" step="1" name="pi_sn_time_value" value="<?php echo esc_attr( $pi_sn_time_value ); ?>"  class="form-control" id="pi_sn_time_value">
                        <select name="pi_sn_time_unit" value="<?php echo esc_attr( $pi_sn_time_unit ); ?>" class="form-control">
                            <option value="hour" <?php echo $pi_sn_time_unit == "hour" ? " selected='selected' ": ""; ?>>Hour</option>
                            <option value="day" <?php echo $pi_sn_time_unit == "day" ? " selected='selected' ": ""; ?>>Day</option>
                            <option value="week" <?php echo $pi_sn_time_unit == "week" ? " selected='selected' ": ""; ?>>Week</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div id="max-notification">
            <div class="pisol-form-element-row field-type-setting_category">
                <div class="pisol-form-label-col">
                <h2 class="pisol-field-title">How many notification to show on one page</h2>
                </div>
            </div>

            <div class="pisol-form-element-row field-type-textarea">
                <div class="pisol-form-label-col">
                    <label for="pi_sn_max_product_show" class="pisol-field-label">How many notification to show (make sure number is grater then 1)</label>
                    <div class="pisol-field-description"><small>For virtual orders this many notification will be created, but for original orders if it is less then this number no virtual order will be created</small></div>
                </div>
                <div class="pisol-form-setting-col">
                    <input type="number" min="1" step="1" id="pi_sn_max_product_show" name="pi_sn_max_product_show" value="<?php echo esc_attr( $pi_sn_max_product_show ); ?>"  class="form-control">
                </div>
            </div>
        </div>
        <input type="submit" class="my-3 btn btn-primary btn-md" value="Save Option" />
        </form>
       <?php
    }

    static function get_tags(){
        $tags_objs = get_posts( array(
        'post_type'      => 'pisol_aaot_tags',
        'numberposts'    => -1,
        ));
        $tags = [];
        foreach($tags_objs as $tag_obj){
            $tags[$tag_obj->ID] = $tag_obj->post_title;
        }
        return $tags;
    }


    public function search_product( $x = '', $post_types = array( 'product' ) ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

        ob_start();
        
        if(!isset($_GET['keyword'])) die;

		$keyword = isset($_GET['keyword']) ? sanitize_text_field($_GET['keyword']) : "";

		if ( empty( $keyword ) ) {
			die();
		}
		$arg            = array(
			'post_status'    => 'publish',
			'post_type'      => $post_types,
			'posts_per_page' => 50,
			's'              => $keyword

		);
		$the_query      = new WP_Query( $arg );
		$found_products = array();
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				$prd = wc_get_product( get_the_ID() );

				if ( $prd->has_child() && $prd->is_type( 'variable' ) ) {
					$product_children = $prd->get_children();
					if ( count( $product_children ) ) {
						foreach ( $product_children as $product_child ) {
							if ( woocommerce_version_check() ) {
								$product = array(
									'id'   => $product_child,
									'text' => str_replace("&#8211;",">",get_the_title( $product_child ))." (ID: {$product_child})"
								);

							} else {
								$child_wc  = wc_get_product( $product_child );
								$get_atts  = $child_wc->get_variation_attributes();
								$attr_name = array_values( $get_atts )[0];
								$product   = array(
									'id'   => $product_child,
									'text' => get_the_title() . ' - ' . $attr_name." (ID: {$product_child})"
								);

							}
							$found_products[] = $product;
						}

					}
				} else {
					$product_id    = get_the_ID();
					$product_title = get_the_title();
					$the_product   = new WC_Product( $product_id );
					if ( ! $the_product->is_in_stock() ) {
						$product_title .= ' (Out of stock)';
					}
					$product          = array( 'id' => $product_id, 'text' => $product_title." (ID: {$product_id})" );
					$found_products[] = $product;
				}
			}
        }
		wp_send_json( $found_products );
		die;
    }
    
    public function search_category() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		ob_start();

		$keyword = sanitize_text_field(filter_input( INPUT_GET, 'keyword'));

		if ( empty( $keyword ) ) {
			die();
		}
		$categories = get_terms(
			array(
				'taxonomy' => 'product_cat',
				'orderby'  => 'name',
				'order'    => 'ASC',
				'search'   => $keyword,
				'number'   => 100
			)
		);
		$items      = array();
		if ( count( $categories ) ) {
			foreach ( $categories as $category ) {
				$item    = array(
					'id'   => $category->term_id,
					'text' => $category->name." (ID:{$category->term_id})"
				);
				$items[] = $item;
			}
		}
		wp_send_json( $items );
		die;
    }
    
   
}


add_action('init', function(){
    new Class_Pi_Sales_Notification_Product($this->plugin_name);
});

/**
 *
 * @param string $version
 *
 * @return bool
 */
if ( ! function_exists( 'woocommerce_version_check' ) ) {
	function woocommerce_version_check( $version = '3.0' ) {
		global $woocommerce;

		if ( version_compare( $woocommerce->version, $version, ">=" ) ) {
			return true;
		}

		return false;
	}
}