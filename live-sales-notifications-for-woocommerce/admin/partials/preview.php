<div class="animated pi-popup bounceIn" id="preview" style="display: none;" title="This is a preview of how the notification will look like">
    <div class="pi-popup-image">
        <a href="#"><img src="<?php echo esc_url($image); ?>"></a>
    </div>
    <div class="pi-popup-content">
        <?php echo wp_kses_post( $message ); ?>
        <?php echo $bottom_block; ?>
        <?php if(!empty($close_image)): ?>
        <a class="pi-popup-close" href="javascript:void(0)"><img src="<?php echo esc_url($close_image); ?>"></a>
        <?php endif; ?>
    </div>
</div>