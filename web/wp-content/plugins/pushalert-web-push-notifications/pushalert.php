<?php
/*
 * Plugin Name: PushAlert - Web Push Notifications
 * Plugin URI: http://wordpress.org/plugins/pushalert-web-push-notifications/
 * Description: PushAlert is a user-engagement and retention platform to increase reach and sales on your WordPress Website and WooCommerce Store, allowing you to push real-time notifications to your website users on both mobile and desktop.
 * Author: PushAlert
 * Author URI: https://pushalert.co
 * Version: 2.0.3
 */

add_action('admin_init', 'pushalert_admin_init');
add_action('admin_notices', 'pushalert_warn_onactivate');
add_action('admin_menu', 'pushalert_admin_menu');
add_action('wp_head', 'pushalert_append_js');

add_action('admin_init', 'pushalert_push_notification_box_init');
add_action('draft_post', 'pushalert_save_notification');
add_action('future_post', 'pushalert_save_notification');
add_action('pending_post', 'pushalert_save_notification');
add_action('draft_to_publish', 'pushalert_send_notification');
add_action('pending_to_publish', 'pushalert_send_notification');
add_action('auto-draft_to_publish', 'pushalert_send_notification');
//add_action('future_to_publish', 'pushalert_send_notification_future');
add_action( 'publish_future_post', 'future_post_pushalert_send_notification' );
register_activation_hook( __FILE__, 'pushalert_init_options' );
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'plugin_settings_link');

if (isWooCommerceEnable()) {

    if(get_option('_pushalert_abandoned_cart', 0)){
        add_action('woocommerce_add_to_cart', 'pa_custom_updated_cart');
        add_action('woocommerce_cart_item_removed', 'pa_custom_updated_cart');
        add_action('woocommerce_after_cart_item_quantity_update', 'pa_custom_updated_cart');
        add_action('woocommerce_before_cart_item_quantity_zero', 'pa_custom_cart_quantity_zero');
        add_action('woocommerce_cart_is_empty', 'pa_custom_updated_cart');
        add_action('woocommerce_order_status_changed', 'pa_custom_order_completed', 10, 3);
    }

    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'plugin_woo_settings_link');

    add_action('init', 'pa_check_old_subscription_init');
    add_action('wp_head', 'pushalert_load_front_end_scripts');
    add_action('wp_footer', 'pa_check_old_subscription');
    
    add_action('wp_footer', 'pa_check_product_page');
    //add_action('woocommerce_before_main_content', 'pa_check_old_subscription');
    add_action( 'wp_ajax_associate_pushalert', 'ajax_associate_pushalert');
    
    
    add_action('woocommerce_account_dashboard', 'addPushAlertEnableSettings');
    
    add_filter( 'woocommerce_settings_tabs_array', 'add_settings_tab', 50 );
    add_action( 'woocommerce_settings_tabs_pushalert', 'pa_settings_tab' );
    add_action( 'woocommerce_update_options_pushalert', 'update_pa_settings_tab' );
    
    if(get_option('_pushalert_out_of_stock', 0) || get_option('_pushalert_price_drop', 0)){
        add_action( 'updated_post_meta', 'pa_woo_price_stock_update', 10, 4 );
    }
    if(get_option('_pushalert_shipment_alert', 0)){
        add_action( 'added_post_meta', 'pa_woo_track_shipment', 10, 4 );
    }

}
add_action('admin_menu', 'register_normal_send_notification_menu_page');

function isWooCommerceEnable(){
    $is_enable = in_array('woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option('active_plugins')));
    if($is_enable && !get_option('_pushalert_woocommerce_enable', 0)){
        pushalert_enable_ecommerce();
        pushalert_load_settings();
    }
    update_option('_pushalert_woocommerce_enable', $is_enable?1:0);
    
    return $is_enable;
}

function plugin_settings_link($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=pushalert') . '">' . __('Settings', 'pushalert') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}

function plugin_woo_settings_link($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=wc-settings&tab=pushalert') . '">' . __('WooCommerce Settings', 'pushalert') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}

function pushalert_warn_onactivate() {
    if (is_admin()) {
        $pushalert_api_key = get_option('pushalert_api_key');
        $pushalert_web_id = get_option('pushalert_web_id');

        if (!$pushalert_api_key || !$pushalert_web_id) {
            echo '<div class="updated"><p><strong>PushAlert:</strong> REST API Key and Website ID is required. Update <a href="' . admin_url('options-general.php?page=pushalert') . '">' . __('settings', 'pushalert') . '</a> now!</p></div>';
        }
    }
}

function pushalert_admin_init() {
    wp_register_style('pushalert_style_css', plugins_url('style.css', __FILE__), array(), '1.0');
    wp_register_script('pushalert_javascript_js', plugins_url('javascript.js', __FILE__), array(), '1.0');

    register_setting(
            'pushalert', 'pushalert_web_id'
    );

    register_setting(
            'pushalert', 'pushalert_api_key'
    );
    
    register_setting(
            'pushalert', 'pushalert_default_title'
    );

    register_setting(
            'pushalert', 'pushalert_utm_source'
    );

    register_setting(
            'pushalert', 'pushalert_utm_medium'
    );

    register_setting(
            'pushalert', 'pushalert_utm_campaign'
    );
}

function pushalert_admin_menu() {
    add_options_page('PushAlert Settings', 'PushAlert', 'manage_options', 'pushalert', 'pushalert_admin_settings_page');
}

function pushalert_admin_settings_page() {
        if ( isset( $_GET['settings-updated'] ) ) {
           pushalert_load_settings();
        }
    ?>
    <div class="wrap"><h2>PushAlert Settings</h2></div>
    <p>Configure options for PushAlert, you can get website ID and REST API key from your PushAlert Settings page. If you're not registered, signup for FREE at <a target="_blank" href="https://pushalert.co/">https://pushalert.co/</a>.</p>
    <form method="post" action="options.php">
    <?php settings_fields('pushalert'); ?>
        <table class="form-table">
            <tr><th scope="row"><h3>Website Settings</h3></th></tr>
            <tr>
                <th scope="row">Website ID</th>
                <td><input type="text" required name="pushalert_web_id" size="64" value="<?php echo esc_attr(get_option('pushalert_web_id')); ?>" placeholder="Website ID" /></td>
            </tr>
            <tr>
                <th scope="row">REST API Key</th>
                <td><input type="text" required name="pushalert_api_key" size="64" value="<?php echo esc_attr(get_option('pushalert_api_key')); ?>" placeholder="REST API Key" /></td>
            </tr>
            <tr>
                <th scope="row">Default Title</th>
                <td><input type="text" name="pushalert_default_title" size="64" maxlength="64" value="<?php echo esc_attr(get_option('pushalert_default_title')); ?>" placeholder="Title"/></td>
            </tr>
            <?php if (isWooCommerceEnable()) { ?>
            <tr>
                <th scope="row" colspan="2">
                    <h3>WooCommerce Settings</h3>
                    <p style="font-weight:400">Click <a href="<?php echo admin_url('admin.php?page=wc-settings&tab=pushalert')?>">here</a> to configure WooCommerce.</p>
                </th>
            </tr>
            <?php } ?>
            <tr><th scope="row"><h3>UTM Params</h3></th></tr>
            <tr>
                <th scope="row">Source</th>
                <td><input type="text" name="pushalert_utm_source" size="64" maxlength="32" value="<?php echo esc_attr(get_option('pushalert_utm_source')); ?>" placeholder="pushalert"/></td>
            </tr>
            <tr>
                <th scope="row">Medium</th>
                <td><input type="text" name="pushalert_utm_medium" size="64" maxlength="32" value="<?php echo esc_attr(get_option('pushalert_utm_medium')); ?>" placeholder="push_notification"/></td>
            </tr>
            <tr>
                <th scope="row">Name</th>
                <td><input type="text" name="pushalert_utm_campaign" size="64" maxlength="32" value="<?php echo esc_attr(get_option('pushalert_utm_campaign')); ?>" placeholder="pushalert_campaign"/></td>
            </tr>
        </table>
        <div class="submit"><input type="submit" class="button-primary" value="<?php echo __('Save Changes', 'pushalert') ?>" /></div>
    </form>
    <?php
   
    add_filter('admin_footer_text', 'replace_footer_admin');

}


function replace_footer_admin () {

    echo 'If you like <strong>PushAlert</strong> please leave us a <a href="https://wordpress.org/support/view/plugin-reviews/pushalert-web-push-notifications?filter=5#postform" target="_blank" class="wc-rating-link" data-rated="Thanks :)">★★★★★</a> rating. A huge thanks in advance!';

}

function pushalert_append_js() {
    $pushalert_web_id = get_option('pushalert_web_id');
    if ($pushalert_web_id) {
        ?>
        <!-- PushAlert WordPress -->
        <script type="text/javascript">
            (function (d, t) {
                var g = d.createElement(t),
                        s = d.getElementsByTagName(t)[0];
                g.src = "//cdn.pushalert.co/integrate_<?php echo $pushalert_web_id ?>.js";
                s.parentNode.insertBefore(g, s);
            }(document, "script"));
        </script>
        <!-- End PushAlert WordPress -->
        <?php
    }
}

function pushalert_push_notification_box_init() {
    $pushalert_api_key = get_option('pushalert_api_key');
    if ($pushalert_api_key) {
        wp_enqueue_style('pushalert_style_css');
        wp_enqueue_script('pushalert_javascript_js');

        add_meta_box(
                'pushalert_push_notification', __('PushAlert Notification', 'pushalert'), 'pushalert_push_notification_box', 'post', 'side', 'high'
        );
    }
}

function pushalert_push_notification_box($post) {

    $title = get_post_meta($post->ID, 'pushalert_notification_title', true);
    if ($title == "") {
        $title = get_option('pushalert_default_title');
    }
    $message = get_post_meta($post->ID, 'pushalert_notification_message', true);
    $enable = get_post_meta($post->ID, 'pushalert_notification_enable', true);
    $post_status = $post->post_status;

    wp_nonce_field(plugin_basename(__FILE__), 'pushalert_nonce_field');
    echo '<input type="text" maxlength="64" id="pushalert_notification_title" name="pushalert_notification_title" value="' . esc_attr($title) . '" placeholder="Title">';
    echo '<textarea id="pushalert_notification_message" maxlength="192" name="pushalert_notification_message" rows="4" placeholder="Your message here...">' . esc_textarea($message) . '</textarea>';
    echo '<label class="pushalert_enable_label"><input type="checkbox" name="pushalert_notification_enable" id="pushalert_notification_enable" value="1" ' . (($enable == 1) ? "checked" : "") . '> Push notification on publish</label>';
}

function pushalert_save_notification($ID) {
    if (!get_option('pushalert_api_key')) {
        return false;
    }

    if (get_post_type($ID) != 'post') {
        return false;
    }

    if (!empty($_POST)) {
        if (!isset($_POST['pushalert_nonce_field']) || (!wp_verify_nonce($_POST['pushalert_nonce_field'], plugin_basename(__FILE__)))) {
            return false;
        } else {
            $title = "";
            $message = "";
            $enable = 0;
            $utm_source = get_option('pushalert_utm_source');
            $utm_medium = get_option('pushalert_utm_medium');
            $utm_campaign = get_option('pushalert_utm_campaign');
            if (isset($_POST['pushalert_notification_title'])) {
                $title = pushalert_sanitize_text_field($_POST['pushalert_notification_title']);
            }
            if (isset($_POST['pushalert_notification_message'])) {
                $message = pushalert_sanitize_text_field($_POST['pushalert_notification_message']);
            }
            if (isset($_POST['pushalert_notification_enable']) && is_numeric($_POST['pushalert_notification_enable']) && $_POST['pushalert_notification_enable'] == 1) {
                $enable = 1;
            }

            update_post_meta($ID, 'pushalert_notification_title', $title);
            update_post_meta($ID, 'pushalert_notification_message', $message);
            update_post_meta($ID, 'pushalert_notification_enable', $enable);
            update_post_meta($ID, 'pushalert_utm_source', $utm_source);
            update_post_meta($ID, 'pushalert_utm_medium', $utm_medium);
            update_post_meta($ID, 'pushalert_utm_campaign', $utm_campaign);
            return true;
        }
    } else {
        return false;
    }
}

function pushalert_sanitize_text_field($str) {
    $filtered = wp_check_invalid_utf8($str); //html tags are fine
    $filtered = trim(preg_replace('/[\r\n\t ]+/', ' ', $filtered));

    $found = false;
    while (preg_match('/%[a-f0-9]{2}/i', $filtered, $match)) {
        $filtered = str_replace($match[0], '', $filtered);
        $found = true;
    }

    if ($found) {
        // Strip out the whitespace that may now exist after removing the octets.
        $filtered = trim(preg_replace('/ +/', ' ', $filtered));
    }

    return $filtered;
}

function pushalert_send_notification($post) {
    //Multiple post published
    if (array_key_exists('post_status', $_GET) && $_GET['post_status'] == 'all') {
        return false;
    }

    if (pushalert_save_notification($post->ID) || $skip_save) {
        $title = get_post_meta($post->ID, 'pushalert_notification_title', true);
        $message = get_post_meta($post->ID, 'pushalert_notification_message', true);
        $enable = get_post_meta($post->ID, 'pushalert_notification_enable', true);
        $utm_source = get_post_meta($post->ID, 'pushalert_utm_source', true);
        $utm_medium = get_post_meta($post->ID, 'pushalert_utm_medium', true);
        $utm_campaign = get_post_meta($post->ID, 'pushalert_utm_campaign', true);
        $post_status = $post->post_status;
        if ($post_status != 'publish') {
            return;
        } else {
            //add_action( 'admin_notices','pushalert_notification_pushed_notice');
            $url = get_permalink($post);
            $url = setGetParameter($url, "utm_source", $utm_source);
            $url = setGetParameter($url, "utm_medium", $utm_medium);
            $url = setGetParameter($url, "utm_campaign", $utm_campaign);
            if ($enable == 1) {
                pushalert_send_notification_curl($title, $message, $url);
            }
        }
    }
}

function future_post_pushalert_send_notification($post_id) {
    $enable = get_post_meta($post_id, 'pushalert_notification_enable', true);
    if ($enable == 1) {
        $title = get_post_meta($post_id, 'pushalert_notification_title', true);
        $message = get_post_meta($post_id, 'pushalert_notification_message', true);
        $utm_source = get_post_meta($post_id, 'pushalert_utm_source', true);
        $utm_medium = get_post_meta($post_id, 'pushalert_utm_medium', true);
        $utm_campaign = get_post_meta($post_id, 'pushalert_utm_campaign', true);

        //add_action( 'admin_notices','pushalert_notification_pushed_notice');
        $url = get_permalink($post_id);
        $url = setGetParameter($url, "utm_source", $utm_source);
        $url = setGetParameter($url, "utm_medium", $utm_medium);
        $url = setGetParameter($url, "utm_campaign", $utm_campaign);
    
        pushalert_send_notification_curl($title, $message, $url);
    }
}

function setGetParameter($url, $paramName, $paramValue) {
    if ($paramValue == "") {
        return $url;
    }
    if (strpos($url, $paramName . "=") !== FALSE) {
        $prefix = substr($url, 0, strpos($url, $paramName));
        $suffix = substr($url, strpos($url, $paramName));
        $suffix = substr($suffix, strpos($suffix, "=" + 1));
        $suffix = (strpos($suffix, "&") !== FALSE) ? substr($suffix, strpos($suffix, "&")) : "";
        $url = $prefix . $paramName . "=" . $paramValue . $suffix;
    } else {
        if (strpos($url, "?"))
            $url = $url . "&" . $paramName . "=" . $paramValue;
        else
            $url = $url . "?" . $paramName . "=" . $paramValue;
    }
    return $url;
}

function pushalert_send_notification_curl($title, $message, $url) {
    if ($title == "" || $message == "" || $url == "") {
        return false;
    }

    $apiKey = get_option('pushalert_api_key');
    if (!$apiKey) {
        return false;
    }

    $curlUrl = "https://api.pushalert.co/rest/v1/send";

    //POST variables
    $post_vars = array(
        "title" => $title,
        "message" => $message,
        "url" => $url,
    );
    
    if(!backgroundPost($curlUrl."?".http_build_query($post_vars))){

        $headers = Array();
        $headers[] = "Authorization: api_key=" . $apiKey;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $curlUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_vars));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
    }
}

function pushalert_send_to_custom($title, $message, $url, $attr_name, $attr_value, $checkout_button=false, $checkout_url=false) {
    if ($title == "" || $message == "" || $url == "") {
        return false;
    }

    $apiKey = get_option('pushalert_api_key');
    if (!$apiKey) {
        return false;
    }

    $curlUrl = "https://api.pushalert.co/rest/v1/send/custom";

    //POST variables
    $post_vars = array(
        "title" => $title,
        "message" => $message,
        "url" => $url,
        "attributes" => json_encode(array($attr_name=>$attr_value))
    );
    
    if($checkout_button && $checkout_url && $checkout_button!="" && $checkout_url!=""){
        $action1 = array(
            "title"=>"➤ ".$checkout_button,
            "url"=>$checkout_url,
        );
        
        $post_vars['action1'] = json_encode($action1);
    }
    
    if(!backgroundPost($curlUrl."?".http_build_query($post_vars))){

        $headers = Array();
        $headers[] = "Authorization: api_key=" . $apiKey;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $curlUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_vars));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
    }
}

function pushalert_load_settings() {
    $apiKey = get_option('pushalert_api_key');
    if (!$apiKey) {
        return false;
    }

    $curlUrl = "https://api.pushalert.co/rest/v1/settings";

    //POST variables
    $post_vars = array(
    );
    

    $headers = Array();
    $headers[] = "Authorization: api_key=" . $apiKey;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $curlUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_vars));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    $result = json_decode($result, true);

    if($result['success']){
        foreach($result['data'] as $key=>$value){
            update_option($key, $value);
        }
    }
    else{
        return false;
    }
}

function pushalert_enable_ecommerce() {
    $apiKey = get_option('pushalert_api_key');
    if (!$apiKey) {
        return false;
    }

    $curlUrl = "https://api.pushalert.co/rest/v1/enableEcommerce";

    //POST variables
    $post_vars = array(
        "type"=>"woocommerce"
    );
    

    $headers = Array();
    $headers[] = "Authorization: api_key=" . $apiKey;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $curlUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_vars));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
}

function pushalert_get_attributes($subscriber_id) {
    $apiKey = get_option('pushalert_api_key');
    if (!$apiKey) {
        return false;
    }

    $curlUrl = "https://api.pushalert.co/rest/v1/attribute/get";

    //POST variables
    $post_vars = array(
        "subscriber" => $subscriber_id
    );
    

    $headers = Array();
    $headers[] = "Authorization: api_key=" . $apiKey;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $curlUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_vars));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    $result = json_decode($result, true);

    if($result['success']){
        return $result['attributes'];
    }
    else{
        return false;
    }
}

function pushalert_put_attributes($subscriber_id, $attr_name, $attr_value) {
    $apiKey = get_option('pushalert_api_key');
    if (!$apiKey) {
        return false;
    }

    $curlUrl = "https://api.pushalert.co/rest/v1/attribute/put";

    //POST variables
    $post_vars = array(
        "subscriber" => $subscriber_id,
        "attributes" => json_encode(array($attr_name=>$attr_value))
    );
    
    if(!backgroundPost($curlUrl."?".http_build_query($post_vars))){

        $headers = Array();
        $headers[] = "Authorization: api_key=" . $apiKey;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $curlUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_vars));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
    }
}

function pushalert_track_order($order_id, $order_total, $check_pushalert_woo) {
    $apiKey = get_option('pushalert_api_key');
    if (!$apiKey) {
        return false;
    }

    $curlUrl = "https://api.pushalert.co/rest/v1/order";

    //POST variables
    $post_vars = array(
        "order_id" => $order_id,
        "order_total" => $order_total,
        "source" => $check_pushalert_woo
    );

    if(!backgroundPost($curlUrl."?".http_build_query($post_vars))){
        $headers = Array();
        $headers[] = "Authorization: api_key=" . $apiKey;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $curlUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_vars));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        //return $result['success'];
    }
}

function pushalert_product_update($product_info) {
    $apiKey = get_option('pushalert_api_key');
    if (!$apiKey) {
        return false;
    }

    $curlUrl = "https://api.pushalert.co/rest/v1/product/update";

    //POST variables
    $post_vars = array(
        "product_info" => json_encode($product_info)
    );

    if(!backgroundPost($curlUrl."?".http_build_query($post_vars))){
        $headers = Array();
        $headers[] = "Authorization: api_key=" . $apiKey;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $curlUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_vars));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
    }
}

function pushalert_track_order_shipment($order_info) {
    $apiKey = get_option('pushalert_api_key');
    if (!$apiKey) {
        return false;
    }

    $curlUrl = "https://api.pushalert.co/rest/v1/order/track";

    //POST variables
    $post_vars = array(
        "order_info" => json_encode($order_info)
    );

    if (!backgroundPost($curlUrl . "?" . http_build_query($post_vars))) {
        $headers = Array();
        $headers[] = "Authorization: api_key=" . $apiKey;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $curlUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_vars));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        //return $result['success'];
    }
}

function pushalert_add_abandoned_cart($subscriber_id, $user_info = array()) {
    $apiKey = get_option('pushalert_api_key');
    if (!$apiKey) {
        return false;
    }

    $curlUrl = "https://api.pushalert.co/rest/v1/abandonedCart";

    //POST variables
    $post_vars = array(
        "subscriber" => $subscriber_id,
        "extra_info" => json_encode($user_info)
    );

    if(!backgroundPost($curlUrl."?".http_build_query($post_vars))){
        $headers = Array();
        $headers[] = "Authorization: api_key=" . $apiKey;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $curlUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_vars));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        $result = json_decode($result, true);
        //return $result['success'];
    }
    
}

function pushalert_remove_abandoned_cart($subscriber_id) {
    $apiKey = get_option('pushalert_api_key');
    if (!$apiKey) {
        return false;
    }

    $curlUrl = "https://api.pushalert.co/rest/v1/abandonedCart/delete";

    //POST variables
    $post_vars = array(
        "subscriber" => $subscriber_id
    );
    
    if(!backgroundPost($curlUrl."?".http_build_query($post_vars))){

        $headers = Array();
        $headers[] = "Authorization: api_key=" . $apiKey;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $curlUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_vars));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        $result = json_decode($result, true);
        //return $result['success'];
    }
}

function backgroundPost($url){
    $apiKey = get_option('pushalert_api_key');
    if (!$apiKey) {
        return false;
    }
    
    $parts=parse_url($url);
    //print_r($parts);
    $fp = fsockopen("ssl://".$parts['host'],
            isset($parts['port'])?$parts['port']:443,
            $errno, $errstr, 30);

    
    if (!$fp) {
        return false;
    } else {
        $out = "POST ".$parts['path']." HTTP/1.1\r\n";
        $out.= "Host: ".$parts['host']."\r\n";
        $out.= "User-Agent: custom\r\n";
        $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out.= "Authorization: api_key=" . $apiKey."\r\n";
        $out.= "Content-Length: ".strlen($parts['query'])."\r\n";
        $out.= "Connection: Close\r\n\r\n";
        if (isset($parts['query'])) $out.= $parts['query'];
        fwrite($fp, $out);
        fclose($fp);
        return true;
    }
}

function pa_custom_updated_cart(){
    $total_items = sizeof( WC()->cart->get_cart() );
    if(current_filter()=="woocommerce_cart_item_removed" && $total_items==0){
        //handled by woocommerce_cart_is_empty
    }
    else{
        if($total_items==0){
            pa_clear_abandoned_cart();
        }
        else{
            //pa_clear_abandoned_cart();
            pa_init_abandoned_cart();
        }
    }
}

function pa_custom_cart_quantity_zero($cart_item_key){
   
    $total_items = sizeof( WC()->cart->get_cart() );
    if($total_items-1>0){
        //pa_clear_abandoned_cart();
        pa_init_abandoned_cart($total_items-1);
    }
    //else case is handled by woocommerce_cart_is_empty
}

function pa_custom_order_completed($order_id, $old_status, $new_status){
    
    $check_pushalert_woo = isset($_COOKIE['pushalert_woo'])?$_COOKIE['pushalert_woo']:false;
    if(!is_admin() && ($new_status == "processing" || $new_status == "completed") && $check_pushalert_woo){
        $order = new WC_Order($order_id); 
        pushalert_track_order($order_id, $order->get_total(), $check_pushalert_woo);
    }
    pa_clear_abandoned_cart();
}

function pa_subscription_check(){
    return (isset($_COOKIE['pushalert_subs_status']) && $_COOKIE['pushalert_subs_status']==='subscribed');
}

function pa_get_total_items($user_id){
    $saved_cart = get_user_meta( $user_id, '_woocommerce_persistent_cart', true );
    if($saved_cart && isset($saved_cart['cart'])){
        return count($saved_cart['cart']);
    }
    else{
        return 0;
    }
}

function pa_clear_abandoned_cart(){
    if(!is_admin()){
        if(!pa_subscription_check()){return;}

        $curr_user_id = get_current_user_id();
        $pushalert_subs_id = filter_input(INPUT_COOKIE, 'pushalert_subs_id');
        
        if($curr_user_id!=0){
            pushalert_remove_abandoned_cart($curr_user_id);
        }
        pushalert_remove_abandoned_cart($pushalert_subs_id);
   }
}

function pa_init_abandoned_cart($total_items=false){
    if(!pa_subscription_check()){return;}

    global $woocommerce;
    $curr_user = wp_get_current_user();
    $pushalert_subs_id = filter_input(INPUT_COOKIE, 'pushalert_subs_id');

    $user_info = array();
    $user_info['cart_url'] = $woocommerce->cart->get_cart_url();
    $user_info['checkout_url'] = $woocommerce->cart->get_checkout_url();
    $user_info['total_items'] = (!$total_items ? sizeof(WC()->cart->get_cart()) : $total_items);
    if($curr_user->ID!=0){
        $user_info['first_name'] = $curr_user->first_name;
        if($user_info['first_name']==""){
            $user_info['first_name'] = $curr_user->nickname;
        }
        pushalert_add_abandoned_cart($curr_user->ID, $user_info);
    }
    else{
        pushalert_add_abandoned_cart($pushalert_subs_id, $user_info);
    }
    
}

function pushalert_load_front_end_scripts(){
    wp_register_script( 'custom-script', plugins_url( '/js/pushalert.js', __FILE__ ), array( 'jquery' ) );
    wp_localize_script( 'custom-script', 'pa_ajax', array('ajax_url' => admin_url( 'admin-ajax.php' ) ));
        
    wp_enqueue_script( 'custom-script' );
}

function pushalert_init_options() {
    if(get_option('woocommerce_settings_pushalert_association_css')===false){
        add_option('woocommerce_settings_pushalert_confirm_message', 'Do you want to receive important notifications?');
        add_option('woocommerce_settings_pushalert_button_yes', 'Yes');
        add_option('woocommerce_settings_pushalert_button_no', 'No');
        add_option('woocommerce_settings_pushalert_dashboard_option', 'Receive Important Browser Notifications');
        add_option('woocommerce_settings_pushalert_association_css', '
.pa-receive-notification{
    position: fixed;
    top: 0;
    z-index: 999999;
    left: 0;
    right: 0;
    text-align: center;
    background: #fff;
    padding: 10px;
    box-shadow: 0 0 5px rgba(0,0,0,0.2);
}

.pa-receive-notification form{
    margin:0;
}
.pa-receive-notification button{
    padding: 5px 20px;
    margin: 0 5px;
    font-weight: 400;
}
.pa-receive-notification button.yes{
    background: black;
    color: white;
}

.pa-receive-notification button.no{
    background: white;
    color: black;
}');
        add_option('pushalert_encrypt_key', generateRandomString());
    }
}

function generateRandomString($length = 16) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function PACheckSubsID($user_id, $subs_id){
    $attributes = pushalert_get_attributes($subs_id);
    if(isset($attributes['user_id'])){
        return ($attributes['user_id']==$user_id);
    }
    else{
        return false;
    }
}

function PAAssociateSubsID($user_id, $subs_id){
    pushalert_put_attributes($subs_id, 'user_id', $user_id);
}

function PADeleteSubsID($user_id, $subs_id){
    pushalert_put_attributes($subs_id, 'user_id', ''); //unset user_id
}

function pa_check_old_subscription_init(){
    $check_pushalert_woo = filter_input(INPUT_GET, 'pushalert_source');
    if(isset($check_pushalert_woo)){
        setcookie('pushalert_woo', $check_pushalert_woo, time() + 86400, COOKIEPATH, COOKIE_DOMAIN);
        $_COOKIE['pushalert_woo'] = $check_pushalert_woo;
    }
    
    if(!pa_subscription_check()){return;}

    $curr_user_id = get_current_user_id();
    $pa_check_cookie = filter_input(INPUT_COOKIE, 'pushalert_'.pa_encrypt($curr_user_id, get_option('pushalert_encrypt_key')));
    if($curr_user_id!=0 && !isset($pa_check_cookie)){
        $pushalert_subs_id = filter_input(INPUT_COOKIE, 'pushalert_subs_id');
        if(isset($pushalert_subs_id) && $pushalert_subs_id!=""){
            if(PACheckSubsID($curr_user_id, $pushalert_subs_id)){
                setcookie('pushalert_'.pa_encrypt($curr_user_id, get_option('pushalert_encrypt_key')), '1', 2147483647, COOKIEPATH, COOKIE_DOMAIN);
                $_COOKIE['pushalert_'.pa_encrypt($curr_user_id, get_option('pushalert_encrypt_key'))] = '1';
                return;
            }
        }
    }
    if(!isset($pa_check_cookie) && $curr_user_id!=0){
        setcookie('pushalert_'.pa_encrypt($curr_user_id, get_option('pushalert_encrypt_key')), '-5', time() + 604800, COOKIEPATH, COOKIE_DOMAIN);
        $_COOKIE['pushalert_'.pa_encrypt($curr_user_id, get_option('pushalert_encrypt_key'))] = '-5';
    }
}

function pa_check_old_subscription(){
    wp_nonce_field(plugin_basename(__FILE__), 'pushalert_action_nonce_field');
    if(!pa_subscription_check()){return;}

    $curr_user_id = get_current_user_id();
    if($curr_user_id == 0){
        return;
    }
    else{
        $pa_check_cookie = $_COOKIE['pushalert_'.pa_encrypt($curr_user_id, get_option('pushalert_encrypt_key'))];
        if(isset($pa_check_cookie) && $pa_check_cookie!="-5"){
            return;
        }
    }

    $pushalert_subs_id = filter_input(INPUT_COOKIE, 'pushalert_subs_id');
    if($curr_user_id!=0 && isset($pushalert_subs_id) && $pushalert_subs_id!=""){
        if(!isset($pa_check_cookie) || (isset($pa_check_cookie) && $pa_check_cookie=="-5")){
            //pushalert_load_front_end_scripts();

            echo "
                <style>
                    ".get_option('woocommerce_settings_pushalert_association_css')."
                </style>
            ";

            echo "
                <div class='pa-receive-notification'>
                    <form type='POST'>
            ";
            echo "
                        ".get_option('woocommerce_settings_pushalert_confirm_message')."
                        <button name='pa-rec-notf-yes' type='button' class='yes'>".get_option('woocommerce_settings_pushalert_button_yes')."</button>
                        <button name='pa-rec-notf-no' type='button' class='no'>".get_option('woocommerce_settings_pushalert_button_no')."</button>
                    </form>
                </div>
            ";
        }
    }
}

function ajax_associate_pushalert() {

    $curr_user_id = get_current_user_id();
    if (!isset($_POST['pa_receive_notification_nonce_field']) || (!wp_verify_nonce($_POST['pa_receive_notification_nonce_field'], plugin_basename(__FILE__))) || $curr_user_id == 0) {
        echo "-0";
        wp_die();
    } else {
        $user_action = filter_input(INPUT_POST, 'user_action');
        if($user_action=="yes"){
            $pushalert_subs_id = filter_input(INPUT_COOKIE, 'pushalert_subs_id');
            if($curr_user_id!=0 && isset($pushalert_subs_id) && $pushalert_subs_id!=""){
                PAAssociateSubsID($curr_user_id, $pushalert_subs_id);

                global $woocommerce;
                $total_items = sizeof( WC()->cart->get_cart() );
                if($total_item>0){
                    pa_clear_abandoned_cart();
                    pa_init_abandoned_cart();
                }
                setcookie('pushalert_'.pa_encrypt($curr_user_id, get_option('pushalert_encrypt_key')), '1', 2147483647, COOKIEPATH, COOKIE_DOMAIN);
                echo "1";
                wp_die();
            }
        }
        else if($user_action=="delete"){
            $pushalert_subs_id = filter_input(INPUT_COOKIE, 'pushalert_subs_id');
            PADeleteSubsID($curr_user_id, $pushalert_subs_id);
            setcookie('pushalert_'.pa_encrypt($curr_user_id, get_option('pushalert_encrypt_key')), '-1', 2147483647, COOKIEPATH, COOKIE_DOMAIN);
            echo "-2";
            wp_die();
        }
        else{
            setcookie('pushalert_'.pa_encrypt($curr_user_id, get_option('pushalert_encrypt_key')), '-1', 2147483647, COOKIEPATH, COOKIE_DOMAIN);
            echo "-1";
            wp_die();
        }
    }
    echo "0";
    wp_die();
}

function pa_encrypt($encrypt, $key){
    $encoded = hash_hmac('sha256', $encrypt, $key);
    return $encoded;
}

function addPushAlertEnableSettings(){
    $checked = "";
    //pushalert_load_front_end_scripts();

    $curr_user_id = get_current_user_id();
    $pushalert_subs_id = filter_input(INPUT_COOKIE, 'pushalert_subs_id');
    $pa_check_cookie = $_COOKIE['pushalert_'.pa_encrypt($curr_user_id, get_option('pushalert_encrypt_key'))];
    if($curr_user_id!=0 && isset($pushalert_subs_id) && $pushalert_subs_id!="" && pa_subscription_check()){
        if(isset($pa_check_cookie) && $pa_check_cookie=="1"){
            $checked = "checked";
        }
    }

    echo "<label class='pushalert-dashboard-option'><input type='checkbox' name='pa-dashboard-enable-notification' $checked style='height:auto'/> ".get_option('woocommerce_settings_pushalert_dashboard_option')."</label>";
}


/**
 * Add a new settings tab to the WooCommerce settings tabs array.
 *
 * @param array $settings_tabs Array of WooCommerce setting tabs & their labels, excluding the Subscription tab.
 * @return array $settings_tabs Array of WooCommerce setting tabs & their labels, including the Subscription tab.
 */
function add_settings_tab( $settings_tabs ) {
    $settings_tabs['pushalert'] = __( 'PushAlert', 'woocommerce-pushalert-settings' );
    return $settings_tabs;
}

function pa_settings_tab(){
    woocommerce_admin_fields(pa_get_settings());
}

function update_pa_settings_tab(){
    woocommerce_update_options(pa_get_settings());
}

function pa_get_settings() {
    $settings = array(
        'subs_id_association' => array(
            'name'     => __( 'Subscription ID Association', 'woocommerce-settings-pushalert-association' ),
            'type'     => 'title',
            'desc'     => 'This message is shown to associate the logged in user to the PushAlert subscription ID. It is only shown to users, who subscribed to push notifications before logging into their account.',
            'id'       => 'woocommerce_settings_pushalert_association'
        ),
        'confirm_message' => array(
            'name' => __( 'Confirm Message', 'woocommerce-settings-pushalert-confirm-message' ),
            'type' => 'text',
            'id' => 'woocommerce_settings_pushalert_confirm_message',
            'class' => 'pushalert-woocommerce-text'
        ),
        'button_yes' => array(
            'name' => __( 'Button Yes', 'woocommerce-settings-pushalert-button-yes' ),
            'type' => 'text',
            'id' => 'woocommerce_settings_pushalert_button_yes',
            'class' => 'pushalert-woocommerce-text-small'
        ),
        'button_no' => array(
            'name' => __( 'Button No', 'woocommerce-settings-pushalert-button-no' ),
            'type' => 'text',
            'id' => 'woocommerce_settings_pushalert_button_no',
            'class' => 'pushalert-woocommerce-text-small'
        ),
        'subs_id_association_css' => array(
            'name' => __( 'CSS', 'woocommerce-settings-pushalert-association-css' ),
            'type' => 'textarea',
            'id' => 'woocommerce_settings_pushalert_association_css',
            'class' => 'pushalert-woocommerce-text-css'
        ),
        'dashboard_option' => array(
            'name' => __( 'Enable Notification Option Text', 'woocommerce-settings-pushalert-dashboard-option' ),
            'type' => 'text',
            'id' => 'woocommerce_settings_pushalert_dashboard_option',
            'class' => 'pushalert-woocommerce-text',
            'desc' => '<br/>Shown in My Account section of the WooCommerce account of your customer, where they can easily enable/disable notifications.'
        ),
        'subs_id_association_section_end' => array(
             'type' => 'sectionend'
        ),
        'ca_oos_price_drop' => array(
            'name'     => __( 'Cart Abandonment, Out of Stock, Price Drop and Shipment Notifications', 'woocommerce-settings-pushalert-ca-oos-price_drop' ),
            'type'     => 'title',
            'desc'     => 'Please visit <a href="https://pushalert.co/dashboard" target="_blank">PushAlert Dashboard</a> to configure Cart Abandonment, Out of Stock, Price Drop and Shipment Notifications. These are only available in basic and above plans, <a href="https://pushalert.co/dashboard/upgrade" target="_blank">upgrade now!</a>.',
            'id'       => 'woocommerce_settings_ca_oos_price_drop'
        ),
        'ca_oos_price_drop_end' => array(
             'type' => 'sectionend'
        )
    );
    return apply_filters( 'woocommerce_settings_pushalert_settings', $settings );
}

function register_normal_send_notification_menu_page() {
    add_menu_page('Send Notifications - PushAlert', 'Send Notification', 'manage_options', 'pushalert-send-notification', 'pushalert_send_notifications_callback', 'data:image/svg+xml;base64,PHN2ZyBpZD0iTGF5ZXJfMSIgZGF0YS1uYW1lPSJMYXllciAxIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA0MCA0MCI+PHRpdGxlPlB1c2hBbGVydC1Mb2dvPC90aXRsZT48ZyBpZD0iRm9ybWFfMSIgZGF0YS1uYW1lPSJGb3JtYSAxIj48ZyBpZD0iRm9ybWFfMS0yIiBkYXRhLW5hbWU9IkZvcm1hIDEtMiI+PHBhdGggZD0iTTIwLDM3LjQ5YzIuNzIsMCw0LjkzLTEuNjksNC45My0zLjA3SDE1QzE1LDM1LjgsMTcuMjUsMzcuNDksMjAsMzcuNDlabTEyLjcxLTcuMjNoMEE4LjQsOC40LDAsMCwxLDMwLDI0LjA3VjE3LjcxYTEwLDEwLDAsMCwwLTYuMzItOS4yOVY2LjE5YTMuNjgsMy42OCwwLDAsMC03LjM2LDBWOC40MkExMCwxMCwwLDAsMCwxMCwxNy43MXY2LjM1YTguNCw4LjQsMCwwLDEtMi43MSw2LjE5aDBhMS41MywxLjUzLDAsMCwwLDEsMi43M2gyMy41QTEuNTMsMS41MywwLDAsMCwzMi42OCwzMC4yNlpNMjAsNy41OGExLjY2LDEuNjYsMCwxLDEsMS42Ni0xLjY2QTEuNjYsMS42NiwwLDAsMSwyMCw3LjU4Wk0zMC43Nyw1TDI5LjgzLDYuNDNhMTIuMiwxMi4yLDAsMCwxLDUuMjksOGwxLjY5LS4zYTEzLjkyLDEzLjkyLDAsMCwwLTYtOS4xNGgwWk0xMC4wOSw2LjQzTDkuMTQsNWExMy45MiwxMy45MiwwLDAsMC02LDkuMTRsMS42OSwwLjNhMTIuMiwxMi4yLDAsMCwxLDUuMjgtOGgwWiIgZmlsbD0iI2ZmZiIvPjwvZz48L2c+PC9zdmc+', 30);
}

function pushalert_send_notifications_callback() {
    global $title;

    echo "<h2>$title</h2>";
    if(isset($_POST['pa-send-submit'])){
        if (!isset($_POST['pushalert-submenu-page-save-nonce']) || (!wp_verify_nonce($_POST['pushalert-submenu-page-save-nonce'], plugin_basename(__FILE__)))){
            echo '<div class="error"><p>Something went wrong!</p></div>';
        }
        else{
            $success = true;
            $notification_title = filter_input(INPUT_POST, 'woocommerce_pushalert_send_notification_title');
            $notification_message = filter_input(INPUT_POST, 'woocommerce_pushalert_send_notification_message');
            $notification_url = filter_input(INPUT_POST, 'woocommerce_pushalert_send_notification_url');
            
            if (isWooCommerceEnable() && get_option('_pushalert_send_to_custom', 0)) {
                $user_id = trim(filter_input(INPUT_POST, 'woocommerce_pushalert_send_notification_user_id'));
                if($user_id==="0"){
                    $notification_url = setGetParameter($notification_url, 'pushalert_source', 'dn');
                    pushalert_send_notification_curl($notification_title, $notification_message, $notification_url);
                }
                else{
                    if(!is_numeric($user_id)){
                        $user = get_user_by( 'email', sanitize_email($user_id));
                        if($user){
                            $user_id = $user->ID;
                        }
                        else{
                            $success = false;
                        }
                    }

                    if($success){
                        $notification_url = setGetParameter($notification_url, 'pushalert_source', 'dn');
                        pushalert_send_to_custom($notification_title, $notification_message, $notification_url, 'user_id', $user_id);
                    }
                }
            }
            else{
                pushalert_send_notification_curl($notification_title, $notification_message, $notification_url);
            }
            
            if($success){
                echo '<div class="updated"><p>Notification sent successfully!</p></div>';
            }
            else{
                echo '<div class="error"><p>Invald User ID/Email!</p></div>';
            }
        }
    }
    echo '<form method="POST" action="">
        <table class="form-table">';
    
    if (isWooCommerceEnable() && get_option('_pushalert_send_to_custom', 0)) {
        echo'   <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="woocommerce_pushalert_send_notification_user_id">User ID or Email</label>
                </th>
                <td class="forminp forminp-text">
                    <input name="woocommerce_pushalert_send_notification_user_id" id="woocommerce_pushalert_send_notification_user_id" placeholder="User ID or Email" required>
                    <span class="description">Use 0 to send to all or user id or email to send to a specific user.</span>
                </td>
            </tr>';
    }
    
    echo'   <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="woocommerce_pushalert_send_notification_title">Notification Title</label>
                </th>
                <td class="forminp forminp-text">
                    <input name="woocommerce_pushalert_send_notification_title" id="woocommerce_pushalert_send_notification_title" placeholder="Notification Title" class="pushalert-woocommerce-text" required>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="woocommerce_pushalert_send_notification_message">Notification Message</label>
                </th>
                <td class="forminp forminp-text">
                    <textarea name="woocommerce_pushalert_send_notification_message" id="woocommerce_pushalert_send_notification_message" placeholder="Notification Message" class="pushalert-woocommerce-text" required></textarea>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="woocommerce_pushalert_send_notification_url">Target URL</label>
                </th>
                <td class="forminp forminp-text">
                    <input name="woocommerce_pushalert_send_notification_url" id="woocommerce_pushalert_send_notification_url" placeholder="Target URL" class="pushalert-woocommerce-text" required>
                </td>
            </tr>
        </table>';
        submit_button( 'Send Notification', 'primary', 'pa-send-submit' );
        wp_nonce_field( plugin_basename(__FILE__), 'pushalert-submenu-page-save-nonce' );
        echo '</form>';
}

function pa_check_product_page(){
    if(is_product()){
        global $product;
        if(woocommerce_version_check()){
            $product_id = $product->get_id();
        }
        else{
            $product_id = $product->id;
        }
        ?>
        <script type="text/javascript">
            var pa_woo_product_info = <?php echo json_encode(
                    array(
                    'id'=>$product_id,
                    'variant_id'=> 0,
                    'title'=>$product->get_title(),
                    'price'=>$product->get_price(),
                    'price_formatted'=>strip_tags(wc_price($product->get_price())),
                    'type' =>$product->get_type(),
                    'image' => wp_get_attachment_url($product->get_image_id()),
                    'outofstock' => ($product->is_in_stock())?false:true
                    )); ?>
        </script>
        <?php
    }
}

function pa_woo_price_stock_update( $meta_id, $post_id, $meta_key, $meta_value ){
    $post_type = get_post_type($post_id);
    
    if($post_type=="product" || $post_type=="product_variation"){
        $what_changed=false;
        if($meta_key=="_price" && get_option('_pushalert_price_drop', 0)){ //price changed
            $what_changed = "price";
        }
        else if($meta_key=="_stock_status" && $meta_value=="instock" && get_option('_pushalert_out_of_stock', 0)){ //stock status changed
            $what_changed = "outofstock";
        }
        
        if($what_changed){
            $product = wc_get_product($post_id);
            if($post_type=="product_variation"){
                $product_id = wp_get_post_parent_id($post_id);
                $variant_id = $post_id;
            }
            else{
                $product_id = $post_id;
                $variant_id = 0;
            }
            
            $cart_url = apply_filters( 'woocommerce_get_cart_url', wc_get_page_permalink( 'cart' ) );
            $product_info = array(
                    'id'=>$product_id,
                    'variant_id'=> $variant_id,
                    'title'=>$product->get_title(),
                    'price'=>$product->get_price(),
                    'price_formatted'=>wc_price($product->get_price()),
                    'type' =>$product->get_type(),
                    'image' => wp_get_attachment_url($product->get_image_id()),
                    'outofstock' => ($product->is_in_stock())?0:1,
                    'url' => $product->get_permalink(),
                    'add_to_cart'=> pa_format_add_to_cart_link($product->add_to_cart_url(), $cart_url),
                    'changed' => $what_changed,
                    'currency' => array(get_woocommerce_currency(),get_woocommerce_currency_symbol())
                    );
            pushalert_product_update($product_info);
        }
    }
}

function pa_woo_track_shipment( $meta_id, $post_id, $meta_key, $meta_value ){
    if($meta_key=='_wc_shipment_tracking_items'){
        $st = WC_Shipment_Tracking_Actions::get_instance();
        //$items = json_decode($meta_value, true);
        $fromatted_links = $st->get_formatted_tracking_item( $order_id, $meta_value[0]);
        
        $order = new WC_Order($post_id); 
        $customer_info = $order->get_user();
        
        if($customer_info){
            $first_name = $customer_info->first_name;
            if($first_name==""){
                $first_name = $customer_info->nickname;
            }
            
            $order_status_update = array(
                "order_id" => $post_id,
                "order_status" => 'shipped',
                "customer_id" => $customer_info->id,
                "first_name" => $first_name,
                "last_name" => $customer_info->last_name,
                "order_status_url" => $order->get_view_order_url(),
                "tracking_url" => $fromatted_links['formatted_tracking_link']
            );
            
            pushalert_track_order_shipment($order_status_update);
        }
    }
}


function pa_format_add_to_cart_link($ajax_add_to_cart, $cart_url){
    //admin_url( 'admin-ajax.php', 'relative')
    if(strpos($ajax_add_to_cart, "?")!==false){
       $get_part = parse_url($ajax_add_to_cart, PHP_URL_QUERY);
       
       $query = parse_url($cart_url, PHP_URL_QUERY);
        if ($query) {
            $cart_url .= '&'.$get_part;
        } else {
            $cart_url .= '?'.$get_part;
        }
        
        return $cart_url;
    }
    else{
        return pa_get_root_domain().$ajax_add_to_cart;
    }
}

function pa_get_root_domain() {
    $url_parts = parse_url( get_site_url() );
    if ( $url_parts && isset( $url_parts['host'] ) ) {
            return $url_parts['host'];
    }
    return false;
}

function woocommerce_version_check( $version = '2.6' ) {
    global $woocommerce;
    if( version_compare( $woocommerce->version, $version, ">=" ) ) {
        return true;
    }
    else{
        return false;
    }
}

?>