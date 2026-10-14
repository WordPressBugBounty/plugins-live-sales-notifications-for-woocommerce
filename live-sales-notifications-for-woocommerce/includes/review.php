<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
/*
v2.0
*/
if(!class_exists('pisol_sales_notification_review')){
class pisol_sales_notification_review{

    public $title;
    public $slug;
    public $activation_date;
    public $saved_value;
    public $review_url;
    public $review_after;
    public $buy_url;
    public $price;
    public $allowed_tags = array();
    public $re_review;
        
    function __construct($title, $slug, $buy_url = '', $price = '' ){
        if(function_exists('is_admin') && is_admin()){
        $this->title = $title;
        $this->slug = $slug;
        $this->activation_date = "pi_review_activation_date_{$this->slug}";
        $this->saved_value = "pi_review_saved_value_{$this->slug}";
        $this->review_url = "https://wordpress.org/support/plugin/{$this->slug}/reviews/#new-post";
        $this->review_after = 14;
        $this->re_review = 60;
        $this->buy_url = $buy_url;
        $this->price = $price;

        $atts = array(
            'align', 'class', 'selected', 'multiple', 'checked', 'type', 'id', 'dir', 'lang',
            'style', 'xml:lang', 'src', 'alt', 'href', 'rel', 'rev', 'target', 'novalidate',
            'value', 'name', 'tabindex', 'action', 'method', 'for', 'width', 'height', 'data',
            'title', 'min', 'max', 'step', 'required', 'readonly',
        );

        $tags = array(
            'form', 'br', 'label', 'input', 'select', 'option', 'textarea', 'iframe', 'script',
            'style', 'strong', 'small', 'table', 'span', 'abbr', 'code', 'pre', 'div', 'img',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ol', 'ul', 'li', 'em', 'hr', 'tr', 'td',
            'p', 'a', 'b', 'i',
        );

        $allowed_atts = array_fill_keys($atts, array());

        foreach ($tags as $tag) {
            $this->allowed_tags[$tag] = $allowed_atts;
        }

        //update_option($this->saved_value, array('preference'=> 'later', 'update_at'=>'2021/06/10'));
        //delete_option($this->saved_value);
        
        add_action( 'admin_notices', array($this, 'display_admin_notice'),20 );
        add_action( "admin_post_pi_save_review_preference_{$this->slug}", array($this, 'savePreference'),20 );
        }
    }

    function display_admin_notice() {
    
        $options = get_option($this->saved_value);
        //$options = delete_option($this->saved_value);

        $activation_time = $this->getInstallationDate();

        $notice = '<div class="notice notice-error is-dismissible " >';
        $notice .= '<style>.pisol-review-btn {
            display: block;
            padding: 10px 15px;
            color: #FFF;
            text-decoration: none;
            border-radius: 2px;
        }
        
        .pi-active-btn {
            background-color: #00adb5;
            color:#fff !important;
        }
        
        .pi-passive-btn {
            background-color: #ccc;
        }

        .pi-buy-now-btn {
            background-color: #ee6443;
        }

        .pi-flex{
            display:flex;
            align-items:center;
        }
        </style>';
        $notice .= '<div class="pi-flex">';
        $notice .= '<img style="max-width:90px; height:auto;" src="'.plugin_dir_url( __FILE__ ).'review-icon.svg" alt="pi web solution">';
        $notice .= '<div style="margin-left:20px;">';
        /* translators: Plugin title */
        $notice .= '<p>'.sprintf(__("You've been using <strong>%s</strong> for a few weeks now <br>— hope it's been working well for you! Would you like to leave a review to help spread the word?",'pisol-sales-notification'), esc_html($this->title)).'</p>';
        $notice .= '<ul class="pi-flex" style="margin-top:15px;
        grid-template-columns: 1fr 1fr 1fr;
        grid-column-gap: 20px;
        text-align: center;">';
        $notice .= '<li><a  class="pi-active-btn pisol-review-btn" style="font-weight:bold; text-decoration:none;" val="given" href="'.add_query_arg(array('action' => "pi_save_review_preference_{$this->slug}", 'preference'=>'now','_wpnonce'=>wp_create_nonce( "pi_save_review_preference_{$this->slug}" )), admin_url('admin-post.php')).'" target="_blank">'.__("Sure, I'll review",'pisol-sales-notification').'</a></li>';
        
        $notice .= '<li><a  class="pi-active-btn pisol-review-btn" style="font-weight:bold; text-decoration:none;" val="given" href="'.add_query_arg(array('action' => "pi_save_review_preference_{$this->slug}", 'preference'=>'now','_wpnonce'=>wp_create_nonce( "pi_save_review_preference_{$this->slug}" )), admin_url('admin-post.php')).'" target="_blank">'.__("Want to suggest improvement",'pisol-sales-notification').'</a></li>';
        
        $notice .= '<li><a val="later" class="pi-active-btn pisol-review-btn" style="text-decoration:none;" href="'.add_query_arg(array('action' => "pi_save_review_preference_{$this->slug}", 'preference'=>'never',  '_wpnonce'=>wp_create_nonce( "pi_save_review_preference_{$this->slug}" )), admin_url('admin-post.php')).'">'.__("No thanks",'pisol-sales-notification').'</a></li>';
        
         
        if($this->buy_url && $this->price){   
            /* translators: Price */    
            $notice .= '<li><a target="_blank" class="pi-buy-now-btn pisol-review-btn" val="never" href="'.esc_url($this->buy_url).'&utm_ref=review_reminder">'.sprintf(__("BUY PRO FOR %s", 'pisol-sales-notification'), $this->price).'</a></li>';	
        }        
        $notice .= '</ul>';
        $notice .= '</div>';
        $notice .= '</div>';
        $notice .= '<a type="button" class="notice-dismiss" href="'.add_query_arg(array('action' => "pi_save_review_preference_{$this->slug}", 'preference'=>'later',  '_wpnonce'=>wp_create_nonce( "pi_save_review_preference_{$this->slug}" )), admin_url('admin-post.php')).'" style="text-decoration:none;"><span class="screen-reader-text">Dismiss this notice.</span></a>';
        $notice .= '</div>';
        
        if(!$options && current_time('timestamp') >= strtotime($activation_time." +{$this->review_after} days")){
            echo wp_kses($notice, $this->allowed_tags);
        } else if(is_array($options)) {
            if( array_key_exists('preference', $options) && array_key_exists('update_at', $options) && $options['preference'] =='later'){ 
                if($this->validateDate($options['update_at']) && current_time('timestamp') >= strtotime($options['update_at']." +{$this->re_review} days")){
                    echo wp_kses($notice, $this->allowed_tags);
                }
            }
        }
    }

    function savePreference(){
            $nonce = isset($_GET['_wpnonce']) ? $_GET['_wpnonce'] : '' ;
            $preference = isset($_GET['preference']) ? $_GET['preference'] : 'later';

            if(!isset($_GET['_wpnonce']) || !wp_verify_nonce($nonce,"pi_save_review_preference_{$this->slug}")){
                wp_die(esc_html('Link has expired'), '', array('response' => 403));
            }

            $back = wp_get_referer();
            if(!$back){
                $back = admin_url('index.php');
            }

            $values['update_at'] = current_time('Y/m/d');
            switch($preference){
                case 'later':
                    $values['preference'] = 'later';
                    $redirect = $back;
                    break;
                    
                case 'now':
                    $values['preference'] = 'now';
                    $redirect = $this->review_url;
                    break;
                        
                case 'never':
                    $values['preference'] = 'never';
                    $redirect = $back;
                    break;

                default:
                    $values['preference'] = 'later';
                    $redirect = $back;
                    break;
            }
            update_option($this->saved_value, $values);
            wp_redirect($redirect);
    }

    function getInstallationDate(){
        $get_install_date = get_option($this->activation_date);
        if(empty($get_install_date) || !$this->validateDate($get_install_date)){
            $now = current_time( "Y/m/d" );
            add_option( $this->activation_date, $now );
            return $now;
        }
        return $get_install_date;
    }

    function validateDate($date, $format = 'Y/m/d'){
        if ( empty($date) ) return false;
        
        $d = \DateTime::createFromFormat($format, $date);
        // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
        return $d && $d->format($format) === $date;
    }
}

new pisol_sales_notification_review('Live sales notification plugin', 'live-sales-notifications-for-woocommerce');
}