<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<?php
/**
 * @var string $message
 * @var string $image
 * @var string $close_image
 * @var string $bottom_block
 */
$theme = get_option('pi_sn_theme', 'default');
?>
<div class="animated pi-popup bounceIn pi-popup-theme-<?php echo esc_attr($theme); ?>" id="preview" style="display: none;" title="This is a preview of how the notification will look like">
    <div class="pi-popup-image">
        <a href="#"><img src="<?php echo esc_url($image); ?>"></a>
    </div>
    <div class="pi-popup-content">
        <?php echo wp_kses_post( $message ); ?>
        <?php echo wp_kses_post( $bottom_block ); ?>
    </div>
    <?php if(!empty($close_image)): ?>
        <a class="pi-popup-close" href="javascript:void(0)"><img src="<?php echo esc_url($close_image); ?>"></a>
    <?php endif; ?>
</div>