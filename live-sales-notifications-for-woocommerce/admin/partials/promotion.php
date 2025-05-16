<div style="padding: 20px; background: #fff; border: 1px solid #ddd; border-radius: 8px;" class="my-3">
  <h2>Get Real-Time Order Alerts on Telegram 📲</h2>
  <p>
    Want to stay instantly updated when specific types of orders come in — like high-value orders, COD orders, or orders with certain products?
  </p>
  <p>
    With our <strong>Auto Assign Order Tags for WooCommerce</strong> plugin, you can now <strong>receive Telegram notifications</strong> based on custom order conditions:
  </p>
  <ul>
    <li>✅ Get notified only for large orders</li>
    <li>✅ Receive alerts for Cash on Delivery orders</li>
    <li>✅ Send messages when a specific product is purchased</li>
    <li>✅ And much more — fully customizable!</li>
  </ul>
  <p><strong>All you need to do is:</strong></p>
  <ol>
    <li>Install the <a href="https://wordpress.org/plugins/auto-assign-order-tags-for-woocommerce/" target="_blank">Auto Assign Order Tags for WooCommerce</a> plugin</li>
    <li>Configure order conditions and attach a <strong>Telegram message</strong> to each tag</li>
    <li>Get real-time alerts — no extra setup or API needed!</li>
  </ol>
  <p style="font-weight: bold; color: #008000;">⚡ Perfect for store owners who want to monitor critical orders instantly!</p>
  <p>
    <?php
        if ( $status === 'not_installed' ) {
            $install_url = wp_nonce_url(
                self_admin_url( 'update.php?action=install-plugin&plugin=' . $plugin_slug ),
                'install-plugin_' . $plugin_slug
            );
            echo '<p><a href="' . esc_url( $install_url ) . '" style="display: inline-block; background-color: #0073aa; color: #fff; padding: 10px 20px; border-radius: 5px; text-decoration: none;">Install Now</a></p>';
        }elseif ( $status === 'inactive') {
            
                $activate_url = wp_nonce_url(
                    self_admin_url( 'plugins.php?action=activate&plugin=' . $plugin_file ),
                    'activate-plugin_' . $plugin_file
                );
                echo '<p><a href="' . esc_url( $activate_url ) . '" style="display: inline-block; background-color: #0073aa; color: #fff; padding: 10px 20px; border-radius: 5px; text-decoration: none;">Activate</a></p>';
             
        }else{
                echo '<p><em>The plugin is already active.</em></p>';
        }
    ?>
  </p>
</div>
