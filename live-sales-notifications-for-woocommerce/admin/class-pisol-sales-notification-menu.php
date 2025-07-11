<?php

class Pi_Sales_Menu{

    public $plugin_name;
    public $version;
    public $menu;
    
    function __construct($plugin_name , $version){
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        add_action( 'admin_menu', array($this,'plugin_menu') );
        add_action($this->plugin_name.'_promotion', array($this,'promotion'));

        add_action( 'admin_enqueue_scripts', array($this,'removeConflictCausingScripts'), 1000 );
    }

    function plugin_menu(){
        
        $this->menu = add_menu_page(
            __( 'Sales Notification'),
            __( 'Sales Notification'),
            'manage_options',
            'pisol-sales-notification',
            array($this, 'menu_option_page'),
            plugins_url( 'live-sales-notifications-for-woocommerce/admin/img/pi.svg' ),
            6
        );

        add_action("load-".$this->menu, array($this,"bootstrap_style"));
 
    }

    public function bootstrap_style() {
        
		wp_enqueue_style( $this->plugin_name."_bootstrap", plugin_dir_url( __FILE__ ) . 'css/bootstrap.css', array(), $this->version, 'all' );

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/pisol-sales-notification-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'select2', WC()->plugin_url() . '/assets/css/select2.css', [], $this->version);

        wp_enqueue_script( 'selectWoo', WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full.min.js', array( 'jquery' ), '1.0.4', true );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/pisol-sales-notification-admin.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( $this->plugin_name.'_jsrender', plugin_dir_url( __FILE__ ) . 'js/jsrender.min.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( $this->plugin_name.'_translate', plugin_dir_url( __FILE__ ) . 'js/pisol-translate.js', array( 'jquery', $this->plugin_name.'_jsrender' ), $this->version, true );
		
		wp_localize_script( $this->plugin_name, 'pi_ajax_object',array( 'ajax_url' => admin_url( 'admin-ajax.php' )));

        wp_enqueue_script( $this->plugin_name."_quick_save", plugin_dir_url( __FILE__ ) . 'js/pisol-quick-save.js', array('jquery'), $this->version, true );
		
	}

    function menu_option_page(){
        if(function_exists('settings_errors')){
            settings_errors();
        }
        ?>
        <div id="bootstrap-wrapper" class="pisol-setting-wrapper pisol-container-wrapper">
        <div class="pisol-container mt-2">
            <div class="pisol-row">
                    <div class="col-12">
                        <div class='bg-dark'>
                        <div class="pisol-row">
                            <div class="col-12 col-sm-2 py-2">
                                    <a href="https://www.piwebsolution.com/" target="_blank"><img class="img-fluid ml-2" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) ); ?>img/pi-web-solution.png"></a>
                            </div>
                            <div class="col-12 col-sm-10 text-right small d-flex align-items-center justify-content-end">
                                <a id="pi-special-button" href="<?php echo  esc_url( PI_SALES_NOTIFICATION_BUY_URL ); ?>" target="_blank">GET PRO VERSION</a>
                            </div>
                        </div>
                        </div>
                    </div>
            </div>
            <div class="pisol-row">
                <div class="col-12">
                <div class="bg-light border pl-3 pr-3 pt-0">
                    <div class="pisol-row">
                        <div class="col-12 col-md-4  col-lg-2 border-right">
                            <div id="pisol-side-menu" class="rounded">
                                <?php do_action($this->plugin_name.'_tab'); ?>
                                <a class="" href="https://www.piwebsolution.com/documentation-for-live-sales-notifications-for-woocommerce-plugin/" target="_blank">
                                <span class="dashicons dashicons-media-document"></span> Documentation
                                </a>
                            </div>
                            <div class="promotion mt-4">
                                <div class="bg-primary text-light text-center mb-3">
                                    <a class="" href="<?php echo esc_url( PI_SALES_NOTIFICATION_BUY_URL ); ?>" target="_blank">
                                    <?php new pisol_promotion('live_sales_notification_installation_date'); ?>
                                    </a>
                                </div>
                            </div>
                            
                        </div>
                        <div class="col ">
                        <?php do_action($this->plugin_name.'_tab_content'); ?>
                        </div>
                        <?php do_action($this->plugin_name.'_promotion'); ?>
                    </div>
                </div>
                </div>
            </div>
        </div>
        </div>
        <?php
    }

    function promotion(){
        ?>
        <div class="col-3 border-left">
           <div class="pi-shadow p-3 mt-3 rounded">
            <h2 id="pi-banner-tagline" class="mb-0" style="color:#ccc !important;">⭐️⭐️⭐️⭐️⭐️ <br><br> Trusted by <span style="color:#fff;">60,000+</span> WooCommerce Stores <br> – Users love it</h2>
                <div class="inside mt-2">
                    <ul class="text-left pisol-pro-feature-list mb-3 mt-3 pl-2">
                        <li class="h6 font-weight-bold"><b>✅ Privacy & Control</b></li>
                        <li class="h6">✓ Hide specific orders</li>
                        <li class="h6">✓ Customer opt-out</li>
                        <li class="h6">✓ Page targeting</li>
                        <li class="h6">✓ Dismiss option</li>
                    </ul>
                    <ul class="text-left pisol-pro-feature-list mb-3 mt-3 pl-2">
                        <li class="h6 font-weight-bold"><b>🎨 Customization</b></li>
                        <li class="h6">✓ Custom animation</li>
                        <li class="h6">✓ Background image</li>
                        <li class="h6">✓ Placeholder image</li>
                        <li class="h6">✓ Audio alert</li>
                    </ul>
                    <ul class="text-left pisol-pro-feature-list mb-3 mt-3 pl-2">
                        <li class="h6 font-weight-bold"><b>📈 Boost Sales</b></li>
                        <li class="h6">✓ Show visitor country</li>
                        <li class="h6">✓ Stock remaining alert</li>
                        <li class="h6">✓ Time since order placed</li>
                    </ul>
                    <ul class="text-left pisol-pro-feature-list mb-3 mt-3 pl-2">
                        <li class="h6 font-weight-bold"><b>📅 Flexible Order Feed</b></li>
                        <li class="h6">✓ Set order age</li>
                        <li class="h6">✓ Exclude out-of-stock</li>
                    </ul>
                    <h4 class="pi-bottom-banner">💰 Just <?php echo esc_html(PI_SALES_NOTIFICATION_PRICE); ?></h4>
                    <h4 class="pi-bottom-banner">🔥 Unlock all 25+ features and grow your sales!</h4>
                    <div class="mt-2 text-center">
                        <a class="btn btn-primary" id="prime-button" href="<?php echo esc_url( PI_SALES_NOTIFICATION_BUY_URL ); ?>" target="_blank">🔓 Unlock Pro Now – Limited Time Price!</a>
                    </div>
                </div>
            </div>
    </div>
        <?php
    }

    function isWeekend() {
        return (date('N', strtotime(date('Y/m/d'))) >= 6);
    }

    function removeConflictCausingScripts(){
        if(isset($_GET['page']) && $_GET['page'] == 'pisol-sales-notification'){
            /* fixes css conflict with Nasa Core */
            wp_dequeue_style( 'nasa_back_end-css' );
        }
    }
}