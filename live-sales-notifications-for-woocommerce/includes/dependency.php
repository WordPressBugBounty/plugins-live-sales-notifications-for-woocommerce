<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class pisol_sales_notification_free_dependency{
    
    static $instance = null;

    public $dependency = [
        'auto-assign-order-tags-for-woocommerce/auto-assign-order-tags-for-woocommerce.php',
        'auto-assign-order-tags-pro-for-woocommerce/auto-assign-order-tags-pro-for-woocommerce.php',
    ];

    public $plugin = 'auto-assign-order-tags-for-woocommerce';
    public $plugin_file = 'auto-assign-order-tags-for-woocommerce/auto-assign-order-tags-for-woocommerce.php';

    public $plugin_page = 'pisol-sales-notification';

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action( 'pisol_sales_notification_dependency_install', array( $this, 'notice' ) );
        add_action( 'wp_ajax_install_dependency_plugin_' . $this->plugin_page, array( $this, 'install_plugin' ) );
    }

    public function notice() {
        if( $this->dependency_check() ){
            return ;
        }

        if( $this->isProInstalledButInactive() ){
            $plugin_file = $this->dependency[1];
            $install_url   = wp_nonce_url(
                admin_url( 'plugins.php?action=activate&plugin=' . urlencode( $plugin_file ) ),
                'activate-plugin_' . $plugin_file
            );
            $class = 'activate-dependency-plugin-'.$this->plugin_page;
        }else{
            $install_url = wp_nonce_url(
                admin_url(
                    'update.php?action=install-plugin&plugin=' . $this->plugin
                ),
                'install-plugin_' . $this->plugin
            );
            $class = 'install-dependency-plugin-'.$this->plugin_page;
        }
        ?>
        <div class="notice notice-error d-flex align-items-center my-3 justify-content-between">
            <p class="my-0"><?php esc_html_e( 'Order tag module is missing, Activate/Install the missing order tag modules to ensure all functionality is available.', 'live-sales-notifications-for-woocommerce' ); ?></p>
            <p class="my-0"><a href="<?php echo esc_url( $install_url ); ?>" id="<?php echo $class; ?>" class="button btn-primary"><?php esc_html_e( 'Activate/Install', 'live-sales-notifications-for-woocommerce' ); ?></a></p>
        </div>
        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('install-dependency-plugin-<?php echo $this->plugin_page; ?>').addEventListener('click', function(e) {
                    e.preventDefault();
                    var button = this;
                    button.disabled = true;
                    button.innerText = 'Installing...';

                    jQuery.post(ajaxurl, {
                        action: 'install_dependency_plugin_<?php echo $this->plugin_page; ?>',
                        nonce: '<?php echo esc_js( wp_create_nonce( 'install_dependency_nonce_' . $this->plugin_page ) ); ?>',
                    }, function(response) {
                        location.reload();
                    });
                });
            });
        </script>
        <?php
    }

    function isProInstalledButInactive() {
        if ( ! function_exists( 'is_plugin_active' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        if ( file_exists( WP_PLUGIN_DIR . '/' . $this->dependency[1] ) && ! is_plugin_active( $this->dependency[1] ) ) {
            return true;
        }
        return false;
    }

    function dependency_check() {
        if ( ! function_exists( 'is_plugin_active' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        foreach ( $this->dependency as $plugin ) {
            if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin ) && is_plugin_active( $plugin )) {
                return true;
            }
        }
        return false;
    }

    function install_plugin() {
        if ( ! current_user_can( 'install_plugins' ) ) {
            wp_send_json_error( 'Unauthorized', 403 );
        }

        $nonce = filter_input( INPUT_POST, 'nonce' );
        if ( ! wp_verify_nonce( $nonce, 'install_dependency_nonce_' . $this->plugin_page ) ) {
            wp_send_json_error( 'Invalid nonce', 400 );
        }

        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

        if (file_exists(WP_PLUGIN_DIR . '/' . $this->plugin_file)){
            if (!is_plugin_active($this->plugin_file)) {
                activate_plugin($this->plugin_file);
            }
            wp_send_json_success(['message' => 'Module already installed.']);
        }

        $api = plugins_api('plugin_information', ['slug' => $this->plugin]);
        if (is_wp_error($api)) {
            wp_send_json_error(['message' => 'Failed to fetch plugin information.']);
        }

        $upgrader = new \Plugin_Upgrader(new \Automatic_Upgrader_Skin());
        $result = $upgrader->install($api->download_link);

        if (is_wp_error($result) || !$result) {
            wp_send_json_error(['message' => 'Plugin installation failed.']);
        }

        activate_plugin($this->plugin_file);

        delete_option('pi_aaot_do_activation_redirect');

        wp_send_json_success(['message' => 'Module installed and activated successfully.']);
    }
}

pisol_sales_notification_free_dependency::get_instance();