<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Pi_Sales_Notification_Preview extends stdClass{
    static $instance = null;

    static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    function __construct() {
        add_action('wp_loaded', array($this, 'preview'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    function preview() {
        if((isset($_GET['page']) && $_GET['page'] == 'pisol-sales-notification' && isset($_GET['tab']) && $_GET['tab'] == 'design')) {
            $this->render_preview();
        }
    }

    function enqueue_scripts() {
        if((isset($_GET['page']) && $_GET['page'] == 'pisol-sales-notification')){

            wp_enqueue_style( "live_sales_preview_default", PISOL_SALES_NOTIFICATION_WOOCOMMERCE_PLUGIN_URL . 'public/css/pisol-sales-notification-public.css', array(), PISOL_SALES_NOTIFICATION_VERSION, 'all' );

            $theme_file_initial = 'theme-1-10';
            wp_enqueue_style( "live_sales_preview_theme", PISOL_SALES_NOTIFICATION_WOOCOMMERCE_PLUGIN_URL . 'public/css/pisol-sales-notification-'.$theme_file_initial.'.css', array(), PISOL_SALES_NOTIFICATION_VERSION, 'all' );

            $css = Pisol_Sales_Notification_Public::inlineStyle([], 'default');
            
            wp_add_inline_style( "live_sales_preview_default", $css );

            $css_setting = $this->css_to_hide_non_theme_fields();
            wp_add_inline_style( "live_sales_preview_theme", $css_setting );

        }
    }

    function css_to_hide_non_theme_fields() {
        $fields = $this->field_to_hide_when_theme_used();
        $css = '';
        if(!empty($fields)){
            foreach($fields as $field){
                $css .= '.pisol-setting-form:not(:has(#pi_sn_theme option[value="default"]:checked)) #row_'.$field.'{display:none !important;}';
            }
        }

        $other_fields = $this->other_page_field_to_hide_when_theme_used();
        if(!empty($other_fields)){
            foreach($other_fields as $field){
                $css .= '#row_'.$field.'{display:none !important;}';
            }
        }
        return $css;
    }

    /**
     * this works on the theme selection setting page itself
     */
    function field_to_hide_when_theme_used() {

        return [
            'pi_sn_image_position',
            'pi_sn_background_color',
            'pi_sn_background_image',
            'pi_sn_popup_width',
            'pi_sn_image_width',
            'pi_sn_image_width_mobile',
            'pi_sn_border_radius',
            'pi_sn_border_radius_image',
            'pi_sn_image_padding',
            'pi_sn_setting_section_label_background_color'
        ];
    }

    function other_page_field_to_hide_when_theme_used() {
        $theme = get_option('pi_sn_theme', 'default');
        if($theme == 'default' || empty($theme)){
            return [];
        }

        return [
            'pi_sn_text_color',
            'pi_sn_product_color',
            'pi_sn_product_link_color',
            'pi_sn_time_color',
            'pi_sn_date_color',
            'pi_sn_country_color',
            'pi_sn_state_color',
            'pi_sn_city_color',
            'pi_sn_first_name_color',
            'pi_sn_text_font_size',
            'pi_sn_product_font_size',
            'pi_sn_product_link_font_size',
            'pi_sn_time_font_size',
            'pi_sn_date_font_size',
            'pi_sn_country_font_size',
            'pi_sn_state_font_size',
            'pi_sn_city_font_size',
            'pi_sn_first_name_font_size',
            'pi_sn_text_font_weight',
            'pi_sn_product_font_weight',
            'pi_sn_product_link_font_weight',
            'pi_sn_time_font_weight',
            'pi_sn_date_font_weight',
            'pi_sn_country_font_weight',
            'pi_sn_state_font_weight',
            'pi_sn_city_font_weight',
            'pi_sn_first_name_font_weight',
            'pi_sn_setting_section_label_title',
            'pi_sn_setting_section_label_font_size',
            'pi_sn_setting_section_label_font_weight'

        ];
    }


    function render_preview() {
        $message = $this->formattedMessage();
       
        $image = PISOL_SALES_NOTIFICATION_WOOCOMMERCE_PLUGIN_URL.'public/img/preview.jpg';
      
        $bottom_block = self::bottomBlock();

        $custom_close_id = get_option('pi_sn_close_image', '');

        $show_close_btn = get_option('pi_sn_close_button', 1);
        $close_image = '';
        if(!empty($show_close_btn)){
            $close_image = $custom_close_id ? wp_get_attachment_url($custom_close_id) : plugins_url('admin/img/close.png', dirname(__FILE__)) ;
        }

        include_once( plugin_dir_path( __FILE__ ) . 'partials/preview.php' );
    }

    function formattedMessage(){
        $message = get_option('pi_sn_sales_message','{product_link} was purchased by {first_name} from {state_country}');
        $product = pisol_sn_common::get_preview_product();
        $replaced_message = pisol_sn_common::searchReplace($product, $message);
        return $replaced_message;
    }

    static function bottomBlock() {
        $html = "";
        $html .= self::elapsedTime();
        $html .= self::stockLeft();
        $html .= self::dismissButton();
        if ($html != "") {
          $html = '<div class="pi-bottom-block">' . $html . '</div>';
        }
        return $html;
      }
  
      static function stockLeft() {
        $html = "";
        if (!empty(get_option('pi_show_stock_left', 1))) {
          $html = '<small class="stock-left">Only 2 left</small>';
        }
        return $html;
      }
  
      static function elapsedTime() {
        $html = "";
        if (!empty(get_option('pi_show_elapsed_time',1))) {
          $html = '<small class="time-elapsed">1 hour ago</small>';
        }
        return $html;
      }
  
      static function dismissButton() {
        $html = "";
        if (!empty(get_option('pi_show_dismiss_option',0))) {
          $html = '<small class="dismiss-option"><a href="javascript:void(0);" id="pisol-dismiss">'.__('Dismiss','pisol-sales-notification').'</a></small>';
        }
        return $html;
      }
  

}

Pi_Sales_Notification_Preview::get_instance();