<?php

class Class_Pi_Sales_Order_Tag_Promotion{

    public $plugin_name;

    private $settings = array();

    private $active_tab;

    private $this_tab = 'order_tag_promotion';

    private $tab_name = "Get Telegram Notification of Order";

    private $setting_key = 'pi_sn_telegram_notification';
    
    public $pi_sn_translate_message;

    public $tab;

    public $plugin_slug = 'auto-assign-order-tags-for-woocommerce';

    public $plugin_file = 'auto-assign-order-tags-for-woocommerce/auto-assign-order-tags-for-woocommerce.php';

    function __construct($plugin_name){
        $this->plugin_name = $plugin_name;
        
        $this->tab = sanitize_text_field(filter_input( INPUT_GET, 'tab'));
        $this->active_tab = $this->tab != "" ? $this->tab : 'default';

        if($this->this_tab == $this->active_tab){
            add_action($this->plugin_name.'_tab_content', array($this,'tab_content'));
        }


        add_action($this->plugin_name.'_tab', array($this,'tab'),2);

    }


    function tab(){
        if($this->plugin_status() == 'active'){
            return;
        }
        ?>
        <a class="  pi-side-menu  <?php echo ($this->active_tab == $this->this_tab ? 'bg-primary' : 'bg-secondary'); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page='.sanitize_text_field($_GET['page']).'&tab='.$this->this_tab ) ); ?>">
        <img src="<?php echo esc_url( plugins_url( 'img/telegram-icon.svg', __FILE__ ) ); ?>" class="tab-icon">  <?php echo esc_html( $this->tab_name ); ?>
        </a>
        <?php
    }

    function tab_content(){
        $status = $this->plugin_status();
        if($status == 'active'){
            return;
        }
        $plugin_slug = $this->plugin_slug;
        $plugin_file = $this->plugin_file;

        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/promotion.php';
    }

    function plugin_status() {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';

        $plugin_file = $this->plugin_file; // e.g. 'auto-assign-order-tags-for-woocommerce/auto-assign-order-tags-for-woocommerce.php'

        // Check if plugin folder and main file exist
        if ( ! file_exists( WP_PLUGIN_DIR . '/' . $plugin_file ) ) {
            return 'not_installed';
        }

        // Check if plugin is active
        if ( is_plugin_active( $plugin_file ) ) {
            return 'active';
        }

        return 'inactive';
    }
}

add_action('init', function(){
    new Class_Pi_Sales_Order_Tag_Promotion($this->plugin_name);
});