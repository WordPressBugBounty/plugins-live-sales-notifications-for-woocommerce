<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Class_Pi_Sales_Notification_Option{

    public $plugin_name;

    private $settings = array();

    private $active_tab;

    private $this_tab = 'basic_setting';

    private $tab_name = "Popup setting";

    private $setting_key = 'pi_sn_basic_setting';
    
    public $pi_sn_translate_message;

    public $tab;

    function __construct($plugin_name){
        $this->plugin_name = $plugin_name;

        $this->settings = array(

            array('field'=>'title', 'class'=> 'bg-dark2 text-light', 'class_title'=>'text-light font-weight-light h4', 'label'=>__("Popup setting",'pisol-sales-notification'), 'type'=>"setting_category"),

            array('field'=>'pi_sn_enabled', 'label'=>__('Enable sales notification','pisol-sales-notification'),'type'=>'switch', 'default'=>1,   'desc'=>__('Enable sales notification or disable it','pisol-sales-notification')),

            array('field'=>'pi_sn_enabled_mobile', 'label'=>__('Enable sales notification on mobile','pisol-sales-notification'),'type'=>'switch', 'default'=>1,   'desc'=>__('Enable sales notification or disable it for mobile','pisol-sales-notification')),

            array('field'=>'pi_sn_exclusion_option', 'label'=>__('Give Option to customer to exclude there info from live feed','pisol-sales-notification'),'type'=>'switch', 'default'=>1,   'desc'=>__('This will enable an option in your checkout page, using that buyer can make sure there info is not shown in the live sales feed on your site','pisol-sales-notification')),

            array('field'=>'pi_sn_exclusion_option_message', 'label'=>__('Message shown to customer on checkout page','pisol-sales-notification'),'type'=>'text', 'default'=>"Don't show my information in live sales feed",   'desc'=>__('This message will be shown next to exclude info checkbox', 'pisol-sales-notification')),

            array('field'=>'pi_sn_mobile_breakpoint', 'label'=>__('Mobile breakpoint width','pisol-sales-notification'),'type'=>'number', 'default'=>767, 'min'=>1, 'step'=>1,   'desc'=>__('Define what width will be consider as mobile breakpoint','pisol-sales-notification'), 'pro'=>true),

            array('field'=>'pi_show_dismiss_option', 'label'=>__('Dismiss notification option','pisol-sales-notification'),'type'=>'switch', 'default'=>0,   'desc'=>__('Once user dismiss the notification, he will not see any live sales notification on your site for next X number of  days set by you','pisol-sales-notification'), 'pro'=>true),

            array('field'=>'pi_dismiss_for', 'label'=>__('Dismiss the popup for','pisol-sales-notification'),'type'=>'number', 'default'=>30, 'min'=>1, 'step'=>1,   'desc'=>__('Set the number of days for which the popup will not show to the visitor who has dismissed it','pisol-sales-notification'), 'pro'=>true),

            array('field'=>'pi_show_elapsed_time', 'label'=>__('Show elapsed time on the popup','pisol-sales-notification'),'type'=>'switch', 'default'=>1,   'desc'=>__('Once enable it will show like this order was placed: 1 min ago, 1 week ago, 2 minute ago','pisol-sales-notification'), 'pro'=>true),
            
            array('field'=>'pi_show_stock_left', 'label'=>__('Show stock left for the product to create urgency','pisol-sales-notification'),'type'=>'switch', 'default'=>1,   'desc'=>__('Show stock left for the product to create urgency to purchase','pisol-sales-notification'), 'pro'=>true),

            array('field'=>'pi_fake_stock_quantity', 'label'=>__('Fake stock quantity','pisol-sales-notification'),'type'=>'number', 'default'=>2, 'min'=>1, 'step'=>1,   'desc'=>__('If you don\'t use stock management then it will show this quantity for those products','pisol-sales-notification'), 'pro'=>true),
            
            
            array('field'=>'title', 'class'=> 'bg-dark2 text-light', 'class_title'=>'text-light font-weight-light h4', 'label'=>__("Timing of popup",'pisol-sales-notification'), 'type'=>"setting_category"),

            array('field'=>'pi_sn_popup_loop', 'label'=>__('Loop through ','pisol-sales-notification'),'type'=>'switch', 'default'=>1,   'desc'=>__('Repeat popup once all loaded popups are shown once','pisol-sales-notification')),

            array('field'=>'pi_sn_first_popup', 'label'=>__('When to start showing popup (milliseconds)','pisol-sales-notification'),'type'=>'number', 'default'=>6000, 'min'=>1000, 'step'=>50,   'desc'=>__('Once a person comes to page, when to start showing popup','pisol-sales-notification')),

            array('field'=>'pi_sn_how_long_to_show', 'label'=>__('How long to keep the popup opened (milliseconds)','pisol-sales-notification'),'type'=>'number', 'default'=>6000, 'min'=>1000, 'step'=>50,   'desc'=>__('How long to keep the popup open','pisol-sales-notification')),

            array('field'=>'pi_sn_interval_between_popup', 'label'=>__('Time gap between showing of 2 popups (milliseconds)','pisol-sales-notification'),'type'=>'number', 'default'=>6000, 'min'=>1000, 'step'=>50,   'desc'=>__('Once a popup closes then after how much time new popup should open','pisol-sales-notification')),

            array('field'=>'pi_max_notification_count', 'label'=>__('Number of times a popup will be shown to customer in single session','pisol-sales-notification'),'type'=>'number', 'default'=> '', 'min'=>0, 'step'=>1,   'desc'=>__('It keeps the count of popup shown to a customer in a single session, if you want to show popup only 3 times then set it to 3, if you want to show it unlimited time then leave it empty, session of the customer end when they close the browser tab','pisol-sales-notification')),

            
            array('field'=>'title', 'class'=> 'hide-pro bg-dark2 text-light', 'class_title'=>'text-light font-weight-light h4', 'label'=>__("Other settings",'pisol-sales-notification'), 'type'=>"setting_category"),

            array('field'=>'pi_sn_remove_out_of_stock', 'label'=>__('Don\'t show out of stock product in popup','pisol-sales-notification'),'type'=>'switch', 'default'=>1,   'desc'=>__('Using this you can remove out of stock product from coming in popup','pisol-sales-notification'), 'pro'=>true),

        );
        
        $this->tab = sanitize_text_field(filter_input( INPUT_GET, 'tab'));
        $this->active_tab = $this->tab != "" ? $this->tab : 'default';

        if($this->this_tab == $this->active_tab){
            add_action($this->plugin_name.'_tab_content', array($this,'tab_content'));
        }


        add_action($this->plugin_name.'_tab', array($this,'tab'),2);

       
        $this->register_settings();

        if(PI_SALES_NOTIFICATION_DELETE_SETTING){
            $this->delete_settings();
        }

        add_action( 'add_meta_boxes', array($this,'addOrderExclusionMetaBox') );
        add_action( 'woocommerce_update_order', array($this,'saveMeta'), 1, 2 );
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
        <a class="  <?php echo ($this->active_tab == $this->this_tab ? 'active' : ''); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page='.sanitize_text_field($_GET['page']).'&tab='.$this->this_tab ) ); ?>">
        <span class="dashicons dashicons-admin-home"></span> <?php echo esc_html( $this->tab_name ); ?>
        </a>
        <?php
    }

    function tab_content(){
        
       ?>
        <form method="post" action="options.php"  class="pisol-setting-form">
        <?php settings_fields( $this->setting_key ); ?>
        <?php
            foreach($this->settings as $setting){
                new pisol_class_form_sn_v3_7($setting, $this->setting_key);
            }
        ?>
        <input type="submit" class="my-3 btn btn-primary btn-md" value="Save Option" />
        </form>
       <?php
    }

    function addOrderExclusionMetaBox(){
        add_meta_box(
            'pisol_exclude_order',
            __('Live sales notification','pisol-sales-notification'),
            array($this,'metaBox'),
             ['shop_order', 'woocommerce_page_wc-orders'],
            'side',
            'default'
        );
    }

    function metaBox($post){
        if(is_a($post, 'WP_Post')){
            $order_id = $post->ID;
            $order = wc_get_order( $order_id );
        }elseif(is_a($post, 'WC_Order')){
            $order_id = $post->get_id();
            $order = $post;
        }else{
            return;
        }
        // Nonce field to validate form request came from current site
        wp_nonce_field( 'pisol_exclude_order', 'pisol_exclude_order_nonce' );
        // Get the location data if it's already been entered
        
        $location = $order->get_meta( 'pisol_exclude_order', true );
        
        // Output the field
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '<input type="checkbox" value="on" name="pisol_exclude_order" class="widefat" '.($location == 'on' || $location == 1 ? ' checked ': "").'> '.__('Don\'t show my information in live sales feed','pisol-sales-notification');
    }

    function saveMeta( $post_id, $order = null ){

        if ( empty( $_POST['pisol_exclude_order_nonce'] ) ) return;
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pisol_exclude_order_nonce'] ) ), 'pisol_exclude_order' ) ) return;
       
        if( empty( $order ) ) return;

        if(! isset( $_POST['pisol_exclude_order'] )){
            $order->delete_meta_data( 'pisol_exclude_order' );
            return;
        }

         $is_checked = isset( $_POST['pisol_exclude_order'] )
        ? ( 'on' === sanitize_text_field( wp_unslash( $_POST['pisol_exclude_order'] ) ) || '1' == $_POST['pisol_exclude_order'] )
        : false;

        if ( $is_checked ) {
            $order->update_meta_data( 'pisol_exclude_order', 'on' );
        } else {
            // Remove if unchecked/missing.
            $order->delete_meta_data( 'pisol_exclude_order' );
        }
    }
    
}

add_action('init', function(){
    new Class_Pi_Sales_Notification_Option($this->plugin_name);
});