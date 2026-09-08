<?php
/**
* version 3.10 (custom)
* work with bootstrap there are some extra customization
*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if(!class_exists('pisol_class_form_sn_v3_7')):
class pisol_class_form_sn_v3_7{

    private $setting;
    private $saved_value; 
    private $pro;
    public $allowed_tags;
    public $allowed_atts;

    function __construct($setting){

        $this->setting = $setting;

        if(isset( $this->setting['default'] )){
            $this->saved_value = get_option($this->setting['field'], $this->setting['default']);
        }else{
            $this->saved_value = get_option($this->setting['field']);
        }

        if(isset( $this->setting['pro'] )){
            if($this->setting['pro']){
                $this->pro = ' free-version ';
                //$this->setting['desc'] = '<span style="color:#f00; font-weight:bold;">Workes in Pro version only / Without PRO version this setting will have no effect</span>';
            }else{
                $this->pro = ' paid-version ';
            }
        }else{
            $this->pro = "";
        }

        $allowed_atts = array_fill_keys( array(
            'align', 'class', 'selected', 'multiple', 'checked', 'type', 'id', 'dir', 'lang',
            'style', 'xml:lang', 'src', 'alt', 'href', 'rel', 'rev', 'target', 'novalidate',
            'value', 'name', 'tabindex', 'action', 'method', 'for', 'width', 'height', 'data',
            'title', 'min', 'max', 'step', 'required', 'readonly'
        ), array() );

        $tags = ['form','br','label','input','select','option','textarea','iframe','script','style',
         'strong','small','table','span','abbr','code','pre','div','img','h1','h2','h3','h4','h5','h6','ol','ul','li','em','hr','tr','td','p','a','b','i'];

        $this->allowed_tags = array_fill_keys( $tags, $allowed_atts );
        
        
        $this->check_field_type();
    }

    function check_field_type(){
        if ( ! isset( $this->setting['type'] ) ) return;

        $method_map = array(
            'select'          => 'select_box',
            'radio'           => 'radio_group',
            'number'          => 'number_box',
            'text'            => 'text_box',
            'text_html'       => 'text_box',
            'textarea'        => 'textarea_box',
            'multiselect'     => 'multiselect_box',
            'color'           => 'color_box',
            'hidden'          => 'hidden_box',
            'switch'          => 'switch_display',
            'switch_category' => 'switch_category_display',
            'setting_category'=> 'setting_category',
            'image'           => 'image',
            'group'           => 'field_group',
        );

        $method = isset( $method_map[ $this->setting['type'] ] ) ? $method_map[ $this->setting['type'] ] : null;

        if ( $method ) {
            $this->$method();
        } else {
            $this->custom_field();
        }
    }   

    /**
     * 
     * array(
            'field' => 'shipping_group_1',   // still required, even though it's not a real option — used for id/hooks
            'type'  => 'group',
            'label' => 'Shipping Options',
            'desc'  => 'Configure how rates are calculated.',
            'fields' => array(
                array('field' => 'shipping_flat_rate', 'type' => 'number', 'label' => 'Flat rate', ...),
                array('field' => 'shipping_free_over', 'type' => 'number', 'label' => 'Free over', ...),
            ),
        ),
     */
    function field_group(){
        $prefix = $this->get_hook_prefix();

        $collapsible = !empty($this->setting['collapsible']);
        $is_open     = !isset($this->setting['open']) || $this->setting['open']; // defaults to open

        $group_desc = isset($this->setting['desc'])
            ? '<div class="pisol-field-description"><small>'.wp_kses($this->setting['desc'], $this->allowed_tags).'</small></div>'
            : '';

        $group_links = $this->get_link_html();
        // Prevent link clicks from toggling <details> when inside <summary>
        if(!empty($group_links) && !empty($collapsible)){
            $group_links = str_replace('<a ', '<a onclick="event.stopPropagation();" ', $group_links);
        }
        $group_links_html = !empty($group_links) ? $group_links : '';

        if($collapsible){
            $group_label = isset($this->setting['label'])
                ? '<h3 class="pisol-field-title h5 mb-3 '.esc_attr($this->setting['class_title'] ?? '').'">'.wp_kses_post($this->setting['label']).'</h3>'
                : '';

            $group_open = '<details id="row_'.esc_attr($this->setting['field']).'" class="pisol-form-element-row field-type-group border rounded p-3 mb-4 '.esc_attr($this->setting['class'] ?? '').'"'.($is_open ? ' open' : '').'>'
                . '<summary class="h6 mb-0 pisol-field-group-summary pisol-form-label-col" style="cursor:pointer;">'.$group_label
                .$group_desc.$group_links_html.'</summary>'
                . '<div class="pt-3 pisol-field-group-content">';

            $group_close = '</div></details>';
        }else{
            $group_label = isset($this->setting['label'])
                ? '<h3 class="pisol-field-title h5 mb-3 '.esc_attr($this->setting['class_title'] ?? '').'">'.wp_kses_post($this->setting['label']).'</h3>'
                : '';

            $group_open = '<div id="row_'.esc_attr($this->setting['field']).'" class="pisol-form-element-row field-type-group border rounded p-3 mb-4 '.esc_attr($this->setting['class'] ?? '').'">'
                .'<div class="pisol-form-label-col">'
                . $group_label
                . $group_desc
                . $group_links_html
                . '</div>';

            $group_close = '</div>';
        }

        $group_open = apply_filters("{$prefix}_formmaker_group_html_open", $group_open, $this->setting, $this);
        $group_open = apply_filters("{$prefix}_formmaker_group_html_open_field_{$this->setting['field']}", $group_open, $this->setting, $this);

        $group_close = apply_filters("{$prefix}_formmaker_group_html_close", $group_close, $this->setting, $this);
        $group_close = apply_filters("{$prefix}_formmaker_group_html_close_field_{$this->setting['field']}", $group_close, $this->setting, $this);

        $fields_html = '';
        if(!empty($this->setting['fields']) && is_array($this->setting['fields'])){
            ob_start();
            foreach($this->setting['fields'] as $sub_setting){
                new self($sub_setting);
            }
            $fields_html = ob_get_clean();
        }

        echo $group_open . $fields_html . $group_close;
    }

    function get_label_html(){
        if($this->setting['type'] == 'setting_category') return '<h2 class="pisol-field-title mt-0 mb-0 '.esc_attr($this->setting['class_title'] ?? '').'">'.wp_kses_post($this->setting['label']).'</h2>';

        return '<label class="pisol-field-label h6 mb-0" for="'.esc_attr($this->setting['field']).'">'.wp_kses_post($this->setting['label']).'</label>';
    }

    function get_desc_html(){
        return (isset($this->setting['desc'])) && !empty($this->setting['desc']) ? '<div class="pisol-field-description"><small>'.wp_kses($this->setting['desc'], $this->allowed_tags).'</small></div>' : "";
    }

    function get_link_html(){
        /*
        'links'=>array(array('name'=>"Video", 'url'=>"https://www.youtube.com/watch?v=KNC5lkoE2Fs", 'type'=>'iframe'))
        'links'=>array(array('name'=>"Video", 'url'=>"image url", 'type'=>'image'))
        */

        if(!isset($this->setting['links']) || !is_array($this->setting['links']) || empty($this->setting['links'])) return;

        $html = '';
        $links = $this->setting['links'];
        foreach($links as $link){
            $class = 'pi-'.($link['type'] ?? 'link');
            $html .= '<a href="'.esc_url($link['url']).'" class="'.esc_attr($class).' pi-info-links" target="_blank">'.esc_html($link['name']).'</a> ';
        }
        return !empty($html) ? '<div class="pisol-info-links">'.$html.'</div>' : '';
    }

    function get_title_col_width(){
        $prefix = $this->get_hook_prefix();

        $width_array = apply_filters("{$prefix}_formmaker_title_col_width_array", array('switch_category' => 9, 'setting_category' => 12));

        $width = isset($width_array[$this->setting['type']]) ? $width_array[$this->setting['type']] : 6; 

        // portfolio-wide override, applies to all types unless something more specific overrides it
        $width = apply_filters("{$prefix}_formmaker_title_col_width", $width, $this->setting['type']);

        // type-specific override, e.g. 'pisol_form_maker_title_col_width_switch_category'
        $width = apply_filters("{$prefix}_formmaker_title_col_width_{$this->setting['type']}", $width, $this->setting['type']);

        // field-level override — set directly in the setting array, wins over any filter
        if (isset($this->setting['col_width'])) {
            $width = $this->setting['col_width'];
        }

        return (int) $width;
    }

    function bootstrap($field_html){
        $label_html = $this->get_label_html();
        $desc_html = $this->get_desc_html();
        $links_html = $this->get_link_html();
        $title_col = $this->get_title_col_width();
        $setting_col = 12 - $title_col;

        if($setting_col < 1) {
            $setting_col = 12; // Ensure at least 1 column for the setting
        }

        $prefix = $this->get_hook_prefix();

        // hook 1: let something modify the field's own HTML before it's placed in the row
        $field_html = apply_filters("{$prefix}_formmaker_field_html", $field_html, $this->setting['type'], $this->setting, $this);
        $field_html = apply_filters("{$prefix}_formmaker_field_html_{$this->setting['type']}", $field_html, $this->setting, $this);
        $field_html = apply_filters("{$prefix}_formmaker_field_html_field_{$this->setting['field']}", $field_html, $this->setting, $this);

        ob_start();
        if($this->setting['type'] != 'hidden'){
        ?>
        <div id="row_<?php echo esc_attr($this->setting['field']); ?>"  class="pisol-form-element-row row py-4 border-bottom align-items-center <?php echo esc_attr($this->pro); ?> <?php echo !empty($this->setting['class']) ? esc_attr($this->setting['class']) : ''; ?> field-type-<?php echo esc_attr($this->setting['type']); ?>">
            <div class="pisol-form-label-col col-md-<?php echo esc_attr($title_col); ?>">
                <?php echo wp_kses($label_html, $this->allowed_tags); ?>
                <?php echo wp_kses($desc_html != "" ? $desc_html : "", $this->allowed_tags); ?>
                <?php echo wp_kses($links_html != "" ? $links_html : "", $this->allowed_tags); ?>
                <?php do_action("{$prefix}_after_label_of_{$this->setting['field']}", $this->setting['field'],$this->setting); ?>
            </div>
            <?php if($this->setting['type'] != 'setting_category'): ?>
            <div class="pisol-form-setting-col col-12 col-md-<?php echo esc_attr($setting_col); ?>">
                <?php echo wp_kses($field_html, $this->allowed_tags, ['https', 'http']); ?>
            </div>
            <?php endif; ?>
        </div>
        <?php
        }else{
            ?>
            <div id="row_<?php echo esc_attr($this->setting['field']); ?>" class="pisol-form-element-row row align-items-center <?php echo esc_attr($this->pro); ?> field-type-<?php echo esc_attr($this->setting['type']); ?>">
                <div class="pisol-form-setting-col col-12 col-md-12">
                    <?php echo wp_kses($field_html, $this->allowed_tags, ['https', 'http']); ?>
                </div>
            </div>
            <?php
        }
        $row_html = ob_get_clean();
        // hook 2: let something replace the entire row, wrapper and all
        $row_html = apply_filters("{$prefix}_formmaker_row_html", $row_html, $this->setting['type'], $this->setting, $this);
        $row_html = apply_filters("{$prefix}_formmaker_row_html_{$this->setting['type']}", $row_html, $this->setting, $this);
        $row_html = apply_filters("{$prefix}_formmaker_row_html_field_{$this->setting['field']}", $row_html, $this->setting, $this);

        echo $row_html;
    }

    function get_hook_prefix(){
        // Relies on each plugin's copy having a renamed class/namespace,
        // per the copy-and-rename convention used for this shared file.
        return strtolower( str_replace('\\', '_', get_class($this)) );
    }

    /*
        Field type: select box
    */
    function select_box(){

        $field = '<select class="pisol-field-'.esc_attr($this->setting['type']).' form-control '.esc_attr($this->pro).'" name="'.esc_attr($this->setting['field']).'" id="'.esc_attr($this->setting['field']).'"'
         .(isset($this->setting['multiple']) ? ' multiple="'.esc_attr($this->setting['multiple']).'"': '')
        .'>';
            foreach($this->setting['value'] as $key => $val){
               $field .= '<option value="'.esc_attr($key).'" '.( ( $this->saved_value == $key) ? " selected=\"selected\" " : "" ).'>'.esc_html($val).'</option>';
            }
        $field .= '</select>';

        $this->bootstrap($field);
    }

    function radio_group(){
        $field = '<div class="pisol-field-radio-group-container '.esc_attr($this->setting['radio_class'] ?? '').'">';
        foreach($this->setting['value'] as $key => $val){
            $is_last = ($key === array_key_last($this->setting['value']));
            $mb_class = $is_last ? '' : ' mb-3';
            $field .= '<div class="pisol-field-radio-container form-check ' . $mb_class . '">
                <input class="pisol-field-radio form-check-input" type="radio" name="'.esc_attr($this->setting['field']).'" id="'.esc_attr($this->setting['field'].'_'.$key).'" value="'.esc_attr($key).'" '.checked($this->saved_value, $key, false).'>
                <label class="form-check-label font-italic" for="'.esc_attr($this->setting['field'].'_'.$key).'">'.esc_html($val).'</label>
            </div>';
        }
        $field .= '</div>';

        $this->bootstrap($field);
    }
    

    /*
        Field type: select box
    */
    function multiselect_box(){
        $field = '<select style="min-height:100px;" class="pisol-field-multiselect form-control multiselect '.esc_attr($this->pro).'" name="'.esc_attr($this->setting['field']).'[]" id="'.esc_attr($this->setting['field']).'" multiple'. '>';
            foreach($this->setting['value'] as $key => $val){
                if(isset($this->saved_value) && $this->saved_value != false){
                    $field .='<option value="'.esc_attr($key).'" '.( ( in_array($key, $this->saved_value) ) ? " selected=\"selected\" " : "" ).'>'.esc_html($val).'</option>';
                }else{
                    $field .= '<option value="'.esc_attr($key).'">'.esc_html($val).'</option>';
                }
            }
        $field .= '</select>';

        $this->bootstrap($field);
    }

    /*
        Field type: Number box
    */
    function number_box(){
        $field = '<input type="number" class="pisol-field-number form-control '.esc_attr($this->pro).'" name="'.esc_attr($this->setting['field']).'" id="'.esc_attr($this->setting['field']).'" value="'.esc_attr($this->saved_value).'"'
        .(isset($this->setting['min']) ? ' min="'.esc_attr($this->setting['min']).'"': '')
        .(isset($this->setting['max']) ? ' max="'.esc_attr($this->setting['max']).'"': '')
        .(isset($this->setting['step']) ? ' step="'.esc_attr($this->setting['step']).'"': '')
        .(isset($this->setting['required']) ? ' required="'.esc_attr($this->setting['required']).'"': '')
        .(isset($this->setting['readonly']) ? ' readonly="'.esc_attr($this->setting['readonly']).'"': '')
        .'>';

        $this->bootstrap($field);
    }

    /*
        Field type: Number box
    */
    function text_box(){
        $field = '<input type="text" class="pisol-field-text form-control '.esc_attr($this->pro).'" name="'.esc_attr($this->setting['field']).'" id="'.esc_attr($this->setting['field']).'" value="'.esc_attr($this->saved_value).'"'
        .(isset($this->setting['required']) ? ' required="'.esc_attr($this->setting['required']).'"': '')
        .(isset($this->setting['readonly']) ? ' readonly="'.esc_attr($this->setting['readonly']).'"': '')
        .'>';

        $this->bootstrap($field);
    }
    
    /*
    Textarea field
    */
    function textarea_box(){
        $field = '<textarea style="height:auto !important; min-height:200px;" type="text" class="pisol-field-textarea form-control '.esc_attr($this->pro).'" name="'.esc_attr($this->setting['field']).'" id="'.esc_attr($this->setting['field']).'"'
        .(isset($this->setting['required']) ? ' required="'.esc_attr($this->setting['required']).'"': '')
        .(isset($this->setting['readonly']) ? ' readonly="'.esc_attr($this->setting['readonly']).'"': '')
        .'>';
        $field .= esc_textarea($this->saved_value); 
        $field .= '</textarea>';

        $this->bootstrap($field);
    }

     /*
        Field type: color
    */
    function color_box(){
        $field = '<input type="color" class="color-picker pisol-field-color '.esc_attr($this->pro).'" name="'.esc_attr($this->setting['field']).'" id="'.esc_attr($this->setting['field']).'" value="'.esc_attr($this->saved_value).'"'
        .(isset($this->setting['required']) ? ' required="'.esc_attr($this->setting['required']).'"': '')
        .(isset($this->setting['readonly']) ? ' readonly="'.esc_attr($this->setting['readonly']).'"': '')
        .'>';

        $this->bootstrap($field);
    }

    function hidden_box(){
        $field ='<input type="hidden" class="pisol-field-hidden form-control '.esc_attr($this->pro).'" name="'.esc_attr($this->setting['field']).'" id="'.esc_attr($this->setting['field']).'" value="'.esc_attr($this->saved_value).'"'
        .(isset($this->setting['required']) ? ' required="'.esc_attr($this->setting['required']).'"': '')
        .(isset($this->setting['readonly']) ? ' readonly="'.esc_attr($this->setting['readonly']).'"': '')
        .'>';

        $this->bootstrap($field);
    }

    /*
        Field type: switch
    */
    function switch_display(){
        $field = '<div class="custom-control custom-switch">
        <input type="checkbox" value="1" class="pisol-field-checkbox custom-control-input" name="'.esc_attr($this->setting['field']).'" id="'.esc_attr($this->setting['field']).'" '.(($this->saved_value == true) ? "checked='checked'": "").' >
        <label class="custom-control-label" for="'.esc_attr($this->setting['field']).'"></label>
        </div>';

        $this->bootstrap($field);
    }

    function switch_category_display(){
        $field = '<div class="custom-control custom-switch">
        <input type="checkbox" value="1" class="pisol-field-checkbox custom-control-input" name="'.esc_attr($this->setting['field']).'" id="'.esc_attr($this->setting['field']).'" '.(!empty($this->saved_value) ? "checked='checked'": "").' >
        <label class="custom-control-label" for="'.esc_attr($this->setting['field']).'"></label>
        </div>';

        $this->bootstrap($field);
    }

    /**
     * Category: is to devide setting in different part 
     */
    function setting_category(){
        $this->bootstrap('');
    }

    function image(){
        wp_enqueue_media();
        add_action( 'admin_footer', array($this,'media_selector_scripts') );
        $field = '
        <div class="row align-items-center pisol-image-field-container">
        <div class="col-6 pisol-image-field-button-container">
        <input id="'.esc_attr($this->setting['field']).'_button" type="button" class="button" value="Upload image" />
        <input type="hidden" name="'.esc_attr($this->setting['field']).'" id="'.esc_attr($this->setting['field']).'" value="'.esc_attr($this->saved_value).'">
        </div>
        <div class="col-6 pisol-image-field-preview-container">
        <div class="image-preview-wrapper">
        <img id="'.esc_attr($this->setting['field']).'_preview" '.($this->saved_value > 0 ? 'src="'.wp_get_attachment_url( get_option( $this->setting['field'] ) ).'"': '').' width="100" height="100" style="max-height: 100px; width: 100px;">
        <a href="#" class="clear-image-'.esc_attr($this->setting['field']).'">Clear</a>
        </div>
        </div>
        </div>
        ';

        $this->bootstrap($field);
    }

    function media_selector_scripts(){
        $my_saved_attachment_post_id = get_option($this->setting['field'], 0 );
	    ?><script type='text/javascript'>
		jQuery( document ).ready( function( $ ) {
			// Uploading files
			var file_frame;
			var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id
			var set_to_post_id = <?php echo esc_attr($my_saved_attachment_post_id == 0 || $my_saved_attachment_post_id =="" ? "0" : $my_saved_attachment_post_id) ; ?>; // Set this
			jQuery('#<?php echo esc_attr($this->setting['field']); ?>_button').on('click', function( event ){
				event.preventDefault();
				// If the media frame already exists, reopen it.
				if ( file_frame ) {
					// Set the post ID to what we want
					file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
					// Open frame
					file_frame.open();
					return;
				} else {
					// Set the wp.media post id so the uploader grabs the ID we want when initialised
					wp.media.model.settings.post.id = set_to_post_id;
				}
				// Create the media frame.
				file_frame = wp.media.frames.file_frame = wp.media({
					title: 'Select a image to upload',
					button: {
						text: 'Use this image',
					},
					multiple: false	// Set to true to allow multiple files to be selected
				});
				// When an image is selected, run a callback.
				file_frame.on( 'select', function() {
					// We set multiple to false so only get one image from the uploader
					attachment = file_frame.state().get('selection').first().toJSON();
					// Do something with attachment.id and/or attachment.url here
					$( '#<?php echo esc_attr($this->setting['field']); ?>_preview' ).attr( 'src', attachment.url ).css( 'width', 'auto' );
					$( '#<?php echo esc_attr($this->setting['field']); ?>' ).val( attachment.id );
					// Restore the main post ID
					wp.media.model.settings.post.id = wp_media_post_id;
				});
					// Finally, open the modal
					file_frame.open();
			});
			// Restore the main ID when the add media button is pressed
			jQuery( 'a.add_media' ).on( 'click', function() {
				wp.media.model.settings.post.id = wp_media_post_id;
			});
            jQuery( 'a.clear-image-<?php echo esc_attr($this->setting['field']); ?>' ).on( 'click', function(e) {
                e.preventDefault();
                $( '#<?php echo esc_attr($this->setting['field']); ?>_preview' ).attr("src","");
                $( '#<?php echo esc_attr($this->setting['field']); ?>' ).val("");
            });
		});
	</script>
    <?php
    }

    function custom_field(){
        do_action('pisol_custom_field_'.$this->setting['type'], $this->setting, $this->saved_value);
    }



    /**
     *  if a field don't want to do any sanitization then they will set 
     * 'validation' => false
     * and if they want to add there custom sanitization function then they will do 
     * 'sanitize_callback' => 'function_name' OR
     * 'sanitize_callback' => array('class_name', 'function_name')
     * if they want to use different sanitization function that is other then the one defined for them then they will use 
     * 'sanitize_callback' => 'sanitize_text_field' => directly add the sanitization function name
     */
    static function register_setting($group, $setting){

        if(isset($setting['type']) && $setting['type'] === 'group'){
            if(!empty($setting['fields']) && is_array($setting['fields'])){
                foreach($setting['fields'] as $sub_setting){
                    self::register_setting($group, $sub_setting);
                }
            }
            return;
        }
        
        $validation_function = self::getValidationFunction($setting);
       

        if($validation_function !== false){
            if(!is_array($validation_function) && method_exists(__CLASS__, $validation_function)){
                register_setting($group, $setting['field'], [
                    'sanitize_callback' => [__CLASS__, $validation_function]
                ]);
                return;
            }else{
                if(is_array($validation_function) && count($validation_function) == 2 && method_exists($validation_function[0], $validation_function[1])){
                    register_setting($group, $setting['field'], [
                        'sanitize_callback' => $validation_function
                    ]);
                    return;
                }elseif(!is_array($validation_function) && function_exists($validation_function)){  
                    register_setting($group, $setting['field'], [
                        'sanitize_callback' => $validation_function
                    ]);
                    return;
                }
            }
        }
        
        register_setting($group, $setting['field']);
        
    }

    static function getValidationFunction($setting){
        if(isset($setting['validation']) && $setting['validation'] === false) return false;

        if(isset($setting['sanitize_callback'])){
            return $setting['sanitize_callback'];
        }

        $sanitize_text_allow_basic_html_field_types = ['text_html'];
        
        $sanitize_text_field_types = ['select', 'text', 'multiselect', 'color', 'hidden', 'switch', 'switch_category', 'radio'];

        $sanitize_textarea_field_types = ['textarea'];

        $sanitize_number_field_types = ['number'];

        if(isset($setting['type']) && in_array($setting['type'], $sanitize_text_field_types)){
            return 'sanitize_text_field';
        }

        if(isset($setting['type']) && in_array($setting['type'], $sanitize_textarea_field_types)){
            return 'sanitize_textarea_field';
        }

        if(isset($setting['type']) && in_array($setting['type'], $sanitize_number_field_types)){
            return 'sanitize_numeric_values';
        }

        if(isset($setting['type']) && in_array($setting['type'], $sanitize_text_allow_basic_html_field_types)){
            return 'sanitize_text_allow_basic_html';
        }

        return false;
            
    }

    static function sanitize_text_field($input) {
       
        $sanitized_input = is_array($input) ? array_map([__CLASS__,'sanitize_text_field'], $input) : sanitize_text_field($input);
        
        return $sanitized_input;
    }

    static function sanitize_textarea_field($input) {
       $sanitized_input = sanitize_textarea_field($input);

       return $sanitized_input;
       
    }

    static function sanitize_text_allow_basic_html($input) {
        $allowed_tags = array(
            'span' => array(),
            'strong' => array(),
            'b' => array(),
            'i' => array(),
            'br' => array(),
        );

        $sanitized_input = wp_kses($input, $allowed_tags);

        return $sanitized_input;
    }

    // Sanitize numeric input (supports both integer and float)
    static function sanitize_numeric_values($input) {
        if (is_numeric($input)) {
            if (ctype_digit($input)) {
                $sanitized_input = intval($input);
            } else {
                $sanitized_input = floatval($input);
            }
        } else {
            $sanitized_input = 0; // You can change this to any default value
        }

        return $sanitized_input;
    }

}
endif;