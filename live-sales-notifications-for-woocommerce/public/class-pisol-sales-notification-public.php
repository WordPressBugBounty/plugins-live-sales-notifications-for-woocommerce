<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       piwebsolution.com
 * @since      1.0.0
 *
 * @package    Pisol_Sales_Notification
 * @subpackage Pisol_Sales_Notification/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Pisol_Sales_Notification
 * @subpackage Pisol_Sales_Notification/public
 * @author     Rajesh Singh <rajeshsingh520@gmail.com>
 */
class Pisol_Sales_Notification_Public extends stdClass{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	public $settings = array();

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->settings = self::getSettings();

		remove_action('template_redirect', 'wc_track_product_view', 20);
		add_action( 'template_redirect', array($this,'wc_track_product_view'), 20 );

		add_action('wp_ajax_pisol_live_orders', array($this,'getOrders') );
		add_action('wp_ajax_nopriv_pisol_live_orders', array($this,'getOrders') );
		add_action('wc_ajax_pisol_live_orders', array($this,'getOrders') );
	}

	public static function getSettings(){
		$settings = array();

		/**
		 * Basic setting
		 */
		$settings['pi_sn_enabled'] = get_option('pi_sn_enabled', 1);
		$settings['pi_sn_enable_mobile'] = get_option('pi_sn_enabled_mobile', 1);

		/**
		 * Desing
		 */

		/* position */
		$settings['pi_sn_image_position'] = get_option('pi_sn_image_position','pi-image-left');
		$settings['pi_sn_popup_position'] = get_option('pi_sn_popup_position','pi-left-bottom');

		/* Background color */
		$settings['pi_sn_background_color'] = get_option('pi_sn_background_color','#ffffff');

		/* Layout */
		$settings['pi_sn_popup_width'] = get_option('pi_sn_popup_width', 40);
		$settings['pi_sn_image_width'] = get_option('pi_sn_image_width', 25);
		$settings['pi_sn_image_width_mobile'] = get_option('pi_sn_image_width_mobile', 25);
		$settings['pi_sn_border_radius'] = get_option('pi_sn_border_radius',5);
		$settings['pi_sn_border_radius_image'] = get_option('pi_sn_border_radius_image',0);
		$settings['pi_sn_image_padding'] = get_option('pi_sn_image_padding',10);
		$settings['pi_sn_link_image'] = get_option('pi_sn_link_image',1);
		$settings['pi_sn_link_in_tab'] = get_option('pi_sn_link_in_tab',0);

		/* Animation */
		$settings['pi_sn_open_animation'] = get_option('pi_sn_open_animation','fadeIn');
		$settings['pi_sn_open_animation'] = 'fadeIn';
		$settings['pi_sn_close_animation'] = get_option('pi_sn_close_animation','fadeOut');
		$settings['pi_sn_close_animation'] = 'fadeOut'; // In free version we are not giving option to change animation

		/* Close option */
		$settings['pi_sn_close_button'] = get_option('pi_sn_close_button',1);
		$settings['pi_sn_close_image'] = get_option('pi_sn_close_image',0);
		$settings['pi_sn_close_image'] = (($settings['pi_sn_close_image'] > 0) ? wp_get_attachment_url($settings['pi_sn_close_image']) : plugins_url('admin/img/close.png', dirname(__FILE__)) );

		/* Text colors */
		$settings['pi_sn_product_color'] = get_option('pi_sn_product_color', '#000000');
		$settings['pi_sn_product_link_color'] = get_option('pi_sn_product_link_color', '#000000');
		$settings['pi_sn_time_color'] = get_option('pi_sn_time_color', '#000000');
		$settings['pi_sn_date_color'] = get_option('pi_sn_date_color', '#000000');
		$settings['pi_sn_country_color'] = get_option('pi_sn_country_color', '#000000');
		$settings['pi_sn_state_color'] = get_option('pi_sn_state_color', '#000000');
		$settings['pi_sn_city_color'] = get_option('pi_sn_city_color', '#000000');
		$settings['pi_sn_first_name_color'] = get_option('pi_sn_first_name_color', '#000000');
		$settings['pi_sn_text_color'] = get_option('pi_sn_text_color', '#000000');

		/* Font size */
		$settings['pi_sn_product_font_size'] = get_option('pi_sn_product_font_size', 16);
		$settings['pi_sn_product_link_font_size'] = get_option('pi_sn_product_link_font_size', 16);
		$settings['pi_sn_time_font_size'] = get_option('pi_sn_time_font_size', 16);
		$settings['pi_sn_date_font_size'] = get_option('pi_sn_date_font_size', 16);
		$settings['pi_sn_country_font_size'] = get_option('pi_sn_country_font_size', 16);
		$settings['pi_sn_state_font_size'] = get_option('pi_sn_state_font_size', 16);
		$settings['pi_sn_city_font_size'] = get_option('pi_sn_city_font_size', 16);
		$settings['pi_sn_first_name_font_size'] = get_option('pi_sn_first_name_font_size', 16);
		$settings['pi_sn_text_font_size'] = get_option('pi_sn_text_font_size', 16);

		/* Font weight */
		$settings['pi_sn_product_font_weight'] = get_option('pi_sn_product_font_weight', 'normal');
		$settings['pi_sn_product_link_font_weight'] = get_option('pi_sn_product_link_font_weight', 'bold');
		$settings['pi_sn_time_font_weight'] = get_option('pi_sn_time_font_weight', 'bold');
		$settings['pi_sn_date_font_weight'] = get_option('pi_sn_date_font_weight', 'bold');
		$settings['pi_sn_country_font_weight'] = get_option('pi_sn_country_font_weight', 'bold');
		$settings['pi_sn_state_font_weight'] = get_option('pi_sn_state_font_weight', 'bold');
		$settings['pi_sn_city_font_weight'] = get_option('pi_sn_city_font_weight', 'bold');
		$settings['pi_sn_first_name_font_weight'] = get_option('pi_sn_first_name_font_weight', 'bold');
		$settings['pi_sn_text_font_weight'] = get_option('pi_sn_text_font_weight', 'bold');

		/**
		 * Timing
		 */
		$settings['pi_sn_popup_loop'] = get_option('pi_sn_popup_loop',1);
		$settings['pi_sn_first_popup'] = get_option('pi_sn_first_popup',6000);
		$settings['pi_sn_how_long_to_show'] = get_option('pi_sn_how_long_to_show',6000);
		$settings['pi_sn_interval_between_popup'] = get_option('pi_sn_interval_between_popup',6000);

		/**
		 * Product selection method 
		 */
		$settings['pi_sn_product_selection'] = get_option('pi_sn_product_selection','recently-viewed-products');

		/* Audio */
		$settings['pi_sn_enable_audio_alert'] = get_option('pi_sn_enable_audio_alert',0);

		return $settings;
	}

	public static function inlineStyle($settings = array()){
		if(empty($settings)){
			$settings = self::getSettings();
		}
		
		$return = '
		.pi-popup{
			background-color:'.$settings['pi_sn_background_color'].';
			'.self::popupPosition($settings).'
			width:'.$settings['pi_sn_popup_width'].'vw;
			border-radius:'.$settings['pi_sn_border_radius'].'px;
			background-image: none !important;
		}

		.pi-popup-image{
			max-width: '.$settings['pi_sn_image_width'].'%;
			order: '.($settings['pi_sn_image_position'] == 'pi-image-left' ? 1 : 2).';
			padding:'.$settings['pi_sn_image_padding'].'px;
		}

		.pi-popup-image img{
			border-radius:'.$settings['pi_sn_border_radius_image'].'px;
		}

		.pi-popup-content{
			order: '.($settings['pi_sn_image_position'] == 'pi-image-left' ? 2 : 1).';
			color:'.$settings['pi_sn_text_color'].';
			font-size:'.$settings['pi_sn_text_font_size'].'px;
			font-weight:'.$settings['pi_sn_text_font_weight'].';
		}
		
		.pi-product{
			color:'.$settings['pi_sn_product_color'].';
			font-size:'.$settings['pi_sn_product_font_size'].'px;
			font-weight:'.$settings['pi_sn_product_font_weight'].';
		}

		.pi-product_link{
			color:'.$settings['pi_sn_product_link_color'].';
			font-size:'.$settings['pi_sn_product_link_font_size'].'px;
			font-weight:'.$settings['pi_sn_product_link_font_weight'].';
		}

		.pi-time{
			color:'.$settings['pi_sn_time_color'].';
			font-size:'.$settings['pi_sn_time_font_size'].'px;
			font-weight:'.$settings['pi_sn_time_font_weight'].';
		}

		.pi-date{
			color:'.$settings['pi_sn_date_color'].';
			font-size:'.$settings['pi_sn_date_font_size'].'px;
			font-weight:'.$settings['pi_sn_date_font_weight'].';
		}

		.pi-country{
			color:'.$settings['pi_sn_country_color'].';
			font-size:'.$settings['pi_sn_country_font_size'].'px;
			font-weight:'.$settings['pi_sn_country_font_weight'].';
		}

		.pi-state{
			color:'.$settings['pi_sn_state_color'].';
			font-size:'.$settings['pi_sn_state_font_size'].'px;
			font-weight:'.$settings['pi_sn_state_font_weight'].';
		}

		.pi-city{
			color:'.$settings['pi_sn_city_color'].';
			font-size:'.$settings['pi_sn_city_font_size'].'px;
			font-weight:'.$settings['pi_sn_city_font_weight'].';
		}

		.pi-first_name{
			color:'.$settings['pi_sn_first_name_color'].';
			font-size:'.$settings['pi_sn_first_name_font_size'].'px;
			font-weight:'.$settings['pi_sn_first_name_font_weight'].';
		}

		@media (max-width:768px){
			.pi-popup{
				bottom:0px !important;
				left:0px !important;
				top:auto;
				width:100% !important;
				border-radius:0 !important;
			  }

			.pi-popup-image{
				width: '.$settings['pi_sn_image_width_mobile'].'% !important;
			}

			.pi-popup-close{
				right:10px;
			}
		}
		';
		return $return;
	}

	/**
	 * $x and $y are distance from x and y axis
	 */
	public static function popupPosition($settings, $x = 20, $y = 20){
		$return = "";
		switch($settings['pi_sn_popup_position']){
			case "pi-left-bottom":
			$return .= ' left:'.$x.'px; bottom:'.$y.'px; ';
			break;

			case "pi-right-bottom":
			$return .= ' right:'.$x.'px; bottom:'.$y.'px; ';
			break;

			case "pi-right-top":
			$return .= ' right:'.$x.'px; top:'.$y.'px; ';
			break;

			case "pi-left-top":
			$return .= ' left:'.$x.'px; top:'.$y.'px; ';
			break;
		}
		return $return;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/pisol-sales-notification-public.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name.'-animate', plugin_dir_url( __FILE__ ) . 'css/animate.css', array(), $this->version, 'all' );
		wp_add_inline_style($this->plugin_name.'-animate', self::inlineStyle( $this->settings ) );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		
		$display_control = new pisol_sn_control_display();

		if($this->settings['pi_sn_enabled'] && $display_control->toShowHide()){
			wp_enqueue_script( $this->plugin_name.'-popup', plugin_dir_url( __FILE__ ) . 'js/notification-popup.js', array( 'jquery' ), $this->version, true );
			wp_enqueue_script( $this->plugin_name.'-runner', plugin_dir_url( __FILE__ ) . 'js/notification-runner.js', array( 'jquery', $this->plugin_name.'-popup' ), $this->version, true );
			wp_localize_script($this->plugin_name.'-runner',"pi_notification_runner_setting", $this->popupRunnerGeneralSetting($this->settings));
			//wp_localize_script($this->plugin_name.'-runner',"pi_popups", $this->popupsContent());
		}
	}

	function getOrders(){
		$orders = $this->popupsContent($this->settings);
		echo json_encode($orders);
		die;
	}

	function popupRunnerGeneralSetting($settings = array()){
			if(empty($settings)){
				$settings = self::getSettings();
			}
			
			$setting = array(
				'wc_ajax_url' => WC_AJAX::get_endpoint( '%%endpoint%%' ),
				"first_popup"=> $settings['pi_sn_first_popup'],
				"interval_between_popup"=> $settings['pi_sn_interval_between_popup'],
				"how_long_to_show"=> $settings['pi_sn_how_long_to_show'],
				"animation"=> $settings['pi_sn_open_animation'],
				"closing_animation"=> $settings['pi_sn_close_animation'],
				"close"=> ($settings['pi_sn_close_button'] == 1 ? true : false),
				"close_image"=> $settings['pi_sn_close_image'],
				"dismiss"=> false,
				"loop"=>(empty($settings['pi_sn_popup_loop']) ? false : true),
				"mobile"=>($settings['pi_sn_enable_mobile'] == 1 ? true : false),
				"link_in_tab" => (empty($settings['pi_sn_link_in_tab']) ? false : true),
				"link_image" => (empty($settings['pi_sn_link_image']) ? false : true),
				'audio_alert_enabled' => (empty($settings['pi_sn_enable_audio_alert']) ? false : true),
				'audio_url'=> $this->audio_url(),
				'ajax_url'=>admin_url( 'admin-ajax.php' ),
				'max_notification_count'=> (int)get_option('pi_max_notification_count', 10)
			);
			return $setting;
	}

	function audio_url(){
		$audio_url = plugin_dir_url( __FILE__ ).'media/alert2.mp3';
		return $audio_url;
	}

	function popupsContent($settings = array()){
		if(empty($settings)){
			$settings = self::getSettings();
		}

		$products = array();
		
		switch($settings['pi_sn_product_selection']){
			case 'orders':
				$ordered_products = new pi_sn_ordered_products();
				$products = $ordered_products->getPopups();
			break;

			case 'recently-viewed-products':
				$viewed_products = new pi_sn_recently_viewed_product();
				$products = $viewed_products->getPopups();
			break;

			case 'selected-products':
				$viewed_products = new pi_sn_selected_product();
				$products = $viewed_products->getPopups();
			break;

			case 'selected-categories':
				$viewed_products = new pi_sn_selected_category();
				$products = $viewed_products->getPopups();
			break;
		}

		shuffle($products);
		
		return $products;
	}

	/**
	 * Track product views. Always.
	 */
	function wc_track_product_view() {
    if ( ! is_singular( 'product' ) /* xnagyg: remove this condition to run: || ! is_active_widget( false, false, 'woocommerce_recently_viewed_products', true )*/ ) {
        return;
    }

    global $post;

    if ( empty( $_COOKIE['woocommerce_recently_viewed'] ) ) { // @codingStandardsIgnoreLine.
        $viewed_products = array();
    } else {
        $viewed_products = wp_parse_id_list( (array) explode( '|', wp_unslash( $_COOKIE['woocommerce_recently_viewed'] ) ) ); // @codingStandardsIgnoreLine.
    }

    // Unset if already in viewed products list.
    $keys = array_flip( $viewed_products );

    if ( isset( $keys[ $post->ID ] ) ) {
        unset( $viewed_products[ $keys[ $post->ID ] ] );
    }

    $viewed_products[] = $post->ID;

    if ( count( $viewed_products ) > 15 ) {
        array_shift( $viewed_products );
    }

    // Store for session only.
    wc_setcookie( 'woocommerce_recently_viewed', implode( '|', $viewed_products ) );
	}


	
}
