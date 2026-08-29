<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

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

        add_action('updated_option', [$this, 'clearSettingsCache'], 10, 1);
    }

    function plugin_menu(){
        
        $this->menu = add_menu_page(
            __( 'Sales Notification','pisol-sales-notification' ),
            __( 'Sales Notification','pisol-sales-notification' ),
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
		wp_enqueue_style( $this->plugin_name."_promotion", plugin_dir_url( __FILE__ ) . 'css/promotion.css', array(), $this->version, 'all' );

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
                                    <a href="https://www.piwebsolution.com/" target="_blank"><img class="img-fluid ml-2" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) ); ?>img/pi-web-solution.svg"></a>
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
                                <a class="" href="https://www.piwebsolution.com/user-documentation-live-sales-notification-for-woocommerce/" target="_blank">
                                <span class="dashicons dashicons-media-document"></span> Documentation
                                </a>
                            </div>
                        </div>
                        <div class="col ">
                        <?php do_action('pisol_sales_notification_dependency_install'); ?>
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
        $this->support();
    }

    function promotion(){
        ?>
        <div class="col-xl-3 col-lg-4 col-md-4 col-12 border-left">
           <div class="pisol-sn-pro-banner">

                <div class="pisol-sn-proof">

                    <!-- Signature: a mockup of the live notification this plugin actually
                        produces on the storefront. Swap the name/city/product/time for
                        real (or better, rotating) example data before shipping — these
                        three values are placeholders, not copied from anywhere. -->
                    <div class="pisol-sn-toast">
                    <span class="pisol-sn-toast-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="20" r="1"/><circle cx="17" cy="20" r="1"/><path d="M2.5 3h2l2.3 11.4a2 2 0 002 1.6h7.7a2 2 0 002-1.6L20 7.5H6"/></svg>
                    </span>
                    <div class="pisol-sn-toast-copy">
                        <strong>Someone in New York</strong>
                        <span>just purchased a product</span>
                        <em><span class="pisol-sn-live-dot"></span>2 minutes ago</em>
                    </div>
                    </div>

                    <div class="pisol-sn-stars" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M12 2.5l2.9 6 6.6.9-4.8 4.6 1.1 6.6L12 17.5 6.2 20.6l1.1-6.6-4.8-4.6 6.6-.9L12 2.5z"/></svg>
                    <svg viewBox="0 0 24 24"><path d="M12 2.5l2.9 6 6.6.9-4.8 4.6 1.1 6.6L12 17.5 6.2 20.6l1.1-6.6-4.8-4.6 6.6-.9L12 2.5z"/></svg>
                    <svg viewBox="0 0 24 24"><path d="M12 2.5l2.9 6 6.6.9-4.8 4.6 1.1 6.6L12 17.5 6.2 20.6l1.1-6.6-4.8-4.6 6.6-.9L12 2.5z"/></svg>
                    <svg viewBox="0 0 24 24"><path d="M12 2.5l2.9 6 6.6.9-4.8 4.6 1.1 6.6L12 17.5 6.2 20.6l1.1-6.6-4.8-4.6 6.6-.9L12 2.5z"/></svg>
                    <svg viewBox="0 0 24 24"><path d="M12 2.5l2.9 6 6.6.9-4.8 4.6 1.1 6.6L12 17.5 6.2 20.6l1.1-6.6-4.8-4.6 6.6-.9L12 2.5z"/></svg>
                    </div>
                    <p class="pisol-sn-trust-copy">Trusted by <strong>30,000+</strong> WooCommerce Stores &ndash; Users love it</p>

                </div>

                <div class="pisol-sn-body">

                    <div class="pisol-sn-section">
                    <h4 class="pisol-sn-section-title">
                        <span class="pisol-sn-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l7 3v6c0 4.5-3 8-7 9-4-1-7-4.5-7-9V6l7-3z"/></svg></span>
                        Privacy &amp; Control
                    </h4>
                    <ul class="pisol-sn-list">
                        <li>Hide specific orders</li>
                        <li>Customer opt-out</li>
                        <li>Page targeting</li>
                        <li>Dismiss option</li>
                    </ul>
                    </div>

                    <div class="pisol-sn-section">
                    <h4 class="pisol-sn-section-title">
                        <span class="pisol-sn-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a9 9 0 100 18c1.1 0 2-.9 2-2 0-.5-.2-1-.5-1.4-.3-.4-.5-.9-.5-1.4 0-1.1.9-2 2-2h2.3A4.7 4.7 0 0021 9.7 9 9 0 0012 3z"/><circle cx="7.5" cy="10.5" r="1"/><circle cx="10.5" cy="7" r="1"/><circle cx="15" cy="7.5" r="1"/><circle cx="17" cy="11" r="1"/></svg></span>
                        Customization
                    </h4>
                    <ul class="pisol-sn-list">
                        <li>Custom animation</li>
                        <li>Background image</li>
                        <li>Placeholder image</li>
                        <li>Audio alert</li>
                    </ul>
                    </div>

                    <div class="pisol-sn-section">
                    <h4 class="pisol-sn-section-title">
                        <span class="pisol-sn-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 17 9 11 13 15 21 6"/><polyline points="14 6 21 6 21 13"/></svg></span>
                        Boost Sales
                    </h4>
                    <ul class="pisol-sn-list">
                        <li>Show visitor country</li>
                        <li>Stock remaining alert</li>
                        <li>Time since order placed</li>
                    </ul>
                    </div>

                    <div class="pisol-sn-section pisol-sn-section-last">
                    <h4 class="pisol-sn-section-title">
                        <span class="pisol-sn-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><rect x="3.5" y="5" width="17" height="16" rx="2.5"/><path d="M8 3v4M16 3v4M3.5 10h17"/></svg></span>
                        Flexible Order Feed
                    </h4>
                    <ul class="pisol-sn-list">
                        <li>Set order age</li>
                        <li>Exclude out-of-stock</li>
                    </ul>
                    </div>

                </div>

                <div class="pisol-sn-price-block">
                    <div class="pisol-sn-price">
                    <span class="pisol-sn-price-amount"><?php echo esc_html(PI_SALES_NOTIFICATION_PRICE); ?></span>
                    <span class="pisol-sn-price-suffix">only</span>
                    </div>
                    <a href="<?php echo esc_url(PI_SALES_NOTIFICATION_PRODUCT_PAGE_URL); ?>" target="_blank" class="pisol-sn-cta">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="10.5" width="14" height="9.5" rx="2"/><path d="M8 10.5V7.5a4 4 0 018 0v3"/></svg>
                    Unlock Pro Now &ndash; Limited Time Price!
                    </a>
                </div>

          </div>
        </div>
        <?php
    }

    function isWeekend() {
        return (wp_date('N', strtotime(wp_date('Y/m/d'))) >= 6);
    }

    function removeConflictCausingScripts(){
        if(isset($_GET['page']) && $_GET['page'] == 'pisol-sales-notification'){
            /* fixes css conflict with Nasa Core */
            wp_dequeue_style( 'nasa_back_end-css' );
        }
    }

    function support(){
        $website_url = home_url();
        $plugin_name = $this->plugin_name;
        ?>
        <form action="https://www.piwebsolution.com/quick-support/" method="post" target="_blank" style="display:inline; position:fixed; bottom:30px; right:35px; z-index:9999;" >
            <input type="hidden" name="website_url" value="<?php echo esc_attr( $website_url ); ?>">
            <input type="hidden" name="plugin_name" value="<?php echo esc_attr( $plugin_name ); ?>">
            <button type="submit" style="background:none;border:none;cursor:pointer;padding:0;">
                <img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) ); ?>img/chat.png" 
                    alt="Live Support" title="Quick Support" style="width:60px;height:60px;">
            </button>
        </form>
        <?php
    }

    function clearSettingsCache($option) {
        if (strpos($option, 'pi_sn_') === 0) {
            delete_transient('pi_sn_settings_cache');
        }
    }
}