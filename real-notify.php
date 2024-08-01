<?php
/*
Plugin Name: Real Notify
Description: A plugin to notify users of new posts in real-time.
Version: 1.1
Author: ronak3899
Author URI: https://profiles.wordpress.org/ronak3899/
Text Domain: real-notify
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin paths
define('REAL_NOTIFY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('REAL_NOTIFY_PLUGIN_URL', plugin_dir_url(__FILE__));

// Enqueue styles and scripts
function real_notify_enqueue_assets()
{
    wp_enqueue_style('real-notify-styles', REAL_NOTIFY_PLUGIN_URL . 'css/styles.css');
    wp_enqueue_script('real-notify-scripts', REAL_NOTIFY_PLUGIN_URL . 'js/script.js', array('jquery'), null, true);
    $current_user = wp_get_current_user();
    $user_roles = get_option('real_notify_user_roles', array());
    wp_localize_script('real-notify-scripts', 'real_notify_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'interval' => get_option('real_notify_interval', 15) * 1000,
        'user_type' => get_option('real_notify_user_type', 'all'),
        'user_roles' => $current_user->roles,
        'allowed_roles' => $user_roles,
        'position' => get_option('real_notify_position', 'bottom-right'),
        'enable_sound' => get_option('real_notify_enable_sound', 1),
        'sound_url' => REAL_NOTIFY_PLUGIN_URL . 'sounds/notification.mp3',
        'bg_color' => get_option('real_notify_bg_color', '#ffffff'),
    ));
}
add_action('wp_enqueue_scripts', 'real_notify_enqueue_assets');

// Admin Enqueue styles and scripts
function real_notify_admin_enqueue_assets($hook)
{
    if ($hook !== 'toplevel_page_real_notify_settings') {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_style('real-notify-admin-styles', REAL_NOTIFY_PLUGIN_URL . 'css/admin-style.css');
    wp_enqueue_script('real-notify-admin-scripts', REAL_NOTIFY_PLUGIN_URL . 'js/admin-script.js', array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'real_notify_admin_enqueue_assets');

// Create settings page
function real_notify_create_menu()
{
    add_menu_page(
        __('Real Notify Settings', 'real-notify'),
        __('Real Notify', 'real-notify'),
        'manage_options',
        'real_notify_settings',
        'real_notify_settings_page',
        'dashicons-bell',
        66
    );
}
add_action('admin_menu', 'real_notify_create_menu');

function real_notify_settings_page()
{
    if ($_POST['real_notify_settings']) {
        update_option('real_notify_interval', sanitize_text_field($_POST['real_notify_interval']));
        update_option('real_notify_duration_value', intval($_POST['real_notify_duration_value']));
        update_option('real_notify_duration_unit', sanitize_text_field($_POST['real_notify_duration_unit']));
        update_option('real_notify_post_types', isset($_POST['real_notify_post_types']) ? $_POST['real_notify_post_types'] : array());
        update_option('real_notify_user_type', sanitize_text_field($_POST['real_notify_user_type']));
        update_option('real_notify_user_roles', isset($_POST['real_notify_user_roles']) ? $_POST['real_notify_user_roles'] : array());
        update_option('real_notify_message_template', wp_kses_post($_POST['real_notify_message_template']));
        update_option('real_notify_position', sanitize_text_field($_POST['real_notify_position']));
        update_option('real_notify_enable_sound', isset($_POST['real_notify_enable_sound']) ? 1 : 0);
        update_option('real_notify_image_source', sanitize_text_field($_POST['real_notify_image_source']));
        if ($_POST['real_notify_image_source'] == "library") {
            update_option('real_notify_image_url', sanitize_url($_POST['real_notify_image_url']));
        } else {
            update_option('real_notify_image_url', '');
        }
        update_option('real_notify_bg_color', sanitize_hex_color($_POST['real_notify_bg_color']));
        echo '<div class="updated"><p>' . __('Settings saved.', 'real-notify') . '</p></div>';
    }

    $interval = get_option('real_notify_interval', 15);
    $post_type_objects = array_filter(get_post_types(['public' => true], 'objects'), function ($post_type_object) {
        return !in_array($post_type_object->name, ['page', 'attachment','product', 'feedback', 'nav_menu_item', 'wp_block', 'wp_template', 'wp_template_part', 'wp_navigation', 'wp_font_family', 'wp_font_face']);
    });
    $selected_post_types = get_option('real_notify_post_types', array());
    $user_type = get_option('real_notify_user_type', 'all');
    $user_roles = get_option('real_notify_user_roles', array());
    $selected_position = get_option('real_notify_position', 'bottom-right');
    $image_source = get_option('real_notify_image_source', 'featured');
    $image_url = get_option('real_notify_image_url', '');
    global $wp_roles;
    $all_roles = $wp_roles->roles;
?>
    <div class="wrap">
        <h1><?php _e('Real Notify Settings', 'real-notify'); ?></h1>
        <form method="post" action="">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Notification Message Template', 'real-notify'); ?></th>
                    <td>
                        <?php
                        $editor_id = 'real_notify_message_template';
                        $content = get_option($editor_id, 'New post published: {title} by {author}. See {link}');
                        wp_editor($content, $editor_id, array('textarea_name' => $editor_id, 'media_buttons' => false));
                        ?>
                        <ul>
                            <li><b><?php _e('Available placeholders:', 'real-notify'); ?></b></li>
                            <li><?php _e('{title} - Post title', 'real-notify'); ?></li>
                            <li><?php _e('{author} - Post Author Name', 'real-notify'); ?></li>
                            <li><?php _e('{link} - Post Link', 'real-notify'); ?></li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Notification Popup Position', 'real-notify'); ?></th>
                    <td>
                        <select name="real_notify_position">
                            <option value="top-left" <?php selected($selected_position, 'top-left'); ?>><?php _e('Top Left', 'real-notify'); ?></option>
                            <option value="top-right" <?php selected($selected_position, 'top-right'); ?>><?php _e('Top Right', 'real-notify'); ?></option>
                            <option value="bottom-left" <?php selected($selected_position, 'bottom-left'); ?>><?php _e('Bottom Left', 'real-notify'); ?></option>
                            <option value="bottom-right" <?php selected($selected_position, 'bottom-right'); ?>><?php _e('Bottom Right', 'real-notify'); ?></option>
                            <option value="center-center" <?php selected($selected_position, 'center-center'); ?>><?php _e('Center Center', 'real-notify'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Show Notification from past __ time. When user visits your site.:', 'real-notify'); ?></th>
                    <td>
                        <input type="number" name="real_notify_duration_value" value="<?php echo esc_attr(get_option('real_notify_duration_value', 1)); ?>" />
                        <select name="real_notify_duration_unit">
                            <option value="hours" <?php selected(get_option('real_notify_duration_unit', 'hours'), 'hours'); ?>><?php esc_html_e('Hours', 'real-notify'); ?></option>
                            <option value="days" <?php selected(get_option('real_notify_duration_unit', 'hours'), 'days'); ?>><?php esc_html_e('Days', 'real-notify'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Check for New Notifications (seconds)', 'real-notify'); ?></th>
                    <td><input type="number" min="15" name="real_notify_interval" value="<?php echo esc_attr($interval); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Post Types for Notification', 'real-notify'); ?></th>
                    <td>
                        <?php foreach ($post_type_objects as $post_type) : ?>
                            <label>
                                <input type="checkbox" name="real_notify_post_types[]" value="<?php echo esc_attr($post_type->name); ?>" <?php echo in_array($post_type->name, $selected_post_types) ? 'checked' : ''; ?> />
                                <?php echo esc_html($post_type->labels->singular_name); ?>
                            </label><br />
                        <?php endforeach; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Notify Users', 'real-notify'); ?></th>
                    <td>
                        <label>
                            <input type="radio" name="real_notify_user_type" value="all" <?php checked($user_type, 'all'); ?> />
                            <?php _e('All Users', 'real-notify'); ?>
                        </label><br />
                        <label>
                            <input type="radio" name="real_notify_user_type" value="logged_in" <?php checked($user_type, 'logged_in'); ?> />
                            <?php _e('Logged In Users', 'real-notify'); ?>
                        </label><br />
                        <label>
                            <input type="radio" name="real_notify_user_type" value="logged_out" <?php checked($user_type, 'logged_out'); ?> />
                            <?php _e('Logged Out Users', 'real-notify'); ?>
                        </label>
                    </td>
                </tr>
                <tr id="real_notify_roles_row" style="display: <?php echo $user_type === 'logged_in' ? 'table-row' : 'none'; ?>">
                    <th scope="row"><?php _e('Select User Roles', 'real-notify'); ?></th>
                    <td>
                        <?php foreach ($all_roles as $role_slug => $role_details) : ?>
                            <label>
                                <input type="checkbox" name="real_notify_user_roles[]" value="<?php echo esc_attr($role_slug); ?>" <?php echo in_array($role_slug, (array) $user_roles) ? 'checked' : ''; ?> />
                                <?php echo esc_html($role_details['name']); ?>
                            </label><br />
                        <?php endforeach; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Enable Notification Sound', 'real-notify'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="real_notify_enable_sound" value="1" <?php checked(get_option('real_notify_enable_sound', 1), 1); ?> />
                            <?php _e('Play sound for notifications', 'real-notify'); ?>
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Notification Image', 'real-notify'); ?></th>
                    <td>
                        <label>
                            <input type="radio" name="real_notify_image_source" value="featured" <?php checked($image_source, 'featured'); ?> />
                            <?php _e('Featured Image', 'real-notify'); ?>
                        </label><br />
                        <label>
                            <input type="radio" name="real_notify_image_source" value="library" <?php checked($image_source, 'library'); ?> />
                            <?php _e('Select from Library', 'real-notify'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th></th>
                    <td>
                        <div id="image_selection_container" <?php echo ($image_source === 'library') ? "" : "style='display:none;'"; ?>">
                            <button id="upload_image_button" class="button-primary" <?php echo ($image_url) ? "style='display:none;'" : ""; ?>><?php _e('Select Image', 'real-notify'); ?></button>
                            <input type="hidden" id="real_notify_image_url" name="real_notify_image_url" value="<?php echo ($image_url) ? esc_attr($image_url) : REAL_NOTIFY_PLUGIN_URL . 'img/placeholder.webp'; ?>" />
                            <button id="remove_image_button" class="button" <?php echo ($image_url) ? "" : "style='display:none;'"; ?>><?php _e('Remove Image', 'real-notify'); ?></button>
                            <div id="image_preview" style="margin-top: 20px;">
                                <img src="<?php echo ($image_url) ? esc_attr($image_url) : REAL_NOTIFY_PLUGIN_URL . 'img/placeholder.webp'; ?>" style="max-width: 100px;" />
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Notification Background Color', 'real-notify'); ?></th>
                    <td>
                        <input type="color" name="real_notify_bg_color" id="real_notify_bg_color" value="<?php echo esc_attr(get_option('real_notify_bg_color', '#ffffff')); ?>" opacity/>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="real_notify_settings" class="button-primary" value="<?php _e('Save Changes', 'real-notify'); ?>" />
            </p>
        </form>
        <div class="real-notify-preview">
            <div id="real_notify_preview" class="real_notify_popup_notification bottom-right"></div>
        </div>
    </div>
<?php
}

// Hook into publish actions
$post_types = get_option('real_notify_post_types', array());
foreach ($post_types as $post_type) {
    add_action('publish_' . $post_type, 'real_notify_published_post');
}

function real_notify_published_post($post_id)
{
    $post = get_post($post_id);
    $template = get_option('real_notify_message_template', 'New post published: {title} by {author}. See {link}');
    $image_source = get_option('real_notify_image_source', 'featured');
    if ($image_source == "featured") {
        $featured_image_url = get_the_post_thumbnail_url($post_id, 'full');
    } else {
        $featured_image_url = get_option('real_notify_image_url', '');
    }

    if (!$featured_image_url) {
        $featured_image_url = REAL_NOTIFY_PLUGIN_URL . 'img/placeholder.webp';
    }

    $message = str_replace(
        ['{title}', '{author}', '{link}'],
        [
            esc_html($post->post_title),
            esc_html(get_the_author_meta('display_name', $post->post_author)),
            '<a href="' . esc_url(get_permalink($post_id)) . '">' . esc_html($post->post_title) . '</a>'
        ],
        $template
    );
    $message = wpautop($message);

    $args = array(
        'content' => $message,
        'image' => $featured_image_url,
        'timestamp' => time()
    );

    $duration_value = get_option('real_notify_duration_value', 1);
    $duration_unit = get_option('real_notify_duration_unit', 'hours');

    switch ($duration_unit) {
        case 'hours':
            $duration = $duration_value * 3600;
            break;
        case 'days':
            $duration = $duration_value * 86400;
            break;
        default:
            $duration = 3600;
            break;
    }

    set_transient('real_notify_' . mt_rand(100000, 999999), $args, $duration);
}

function real_notify_check_notifications()
{

    $response = array();
    $user_type = get_option('real_notify_user_type', 'all');
    $current_time = time();

    if (($user_type == 'logged_in' && !is_user_logged_in()) || ($user_type == 'logged_out' && is_user_logged_in())) {
        echo json_encode($response);
        wp_die();
    }

    if (is_user_logged_in()) {

        $current_user = wp_get_current_user();
        $user_roles = get_option('real_notify_user_roles', array());

        if (!array_intersect($current_user->roles, $user_roles) && $user_type == "logged_in") {
            echo json_encode($response);
            wp_die();
        }

        $user_id = get_current_user_id();
        $last_check = get_user_meta($user_id, 'real_notify_last_check', true);

        if (!$last_check) {
            $last_check = 0;
        }
    } else {

        if (isset($_COOKIE['real_notify_last_check'])) {
            $last_check = intval($_COOKIE['real_notify_last_check']);
        } else {
            $last_check = 0;
        }
    }

    global $wpdb;
    $sql = $wpdb->prepare("SELECT * FROM $wpdb->options WHERE option_name LIKE %s", '_transient_real_notify_%');
    $notifications = $wpdb->get_results($sql);

    if (!empty($notifications)) {
        foreach ($notifications as $db_notification) {
            $id = str_replace('_transient_', '', $db_notification->option_name);
            if (false !== ($notification = get_transient($id))) {
                if ($notification['timestamp'] > $last_check) {
                    $response[$id] = $notification;
                }
            }
        }

        if (is_user_logged_in()) {
            update_user_meta($user_id, 'real_notify_last_check', $current_time);
        } else {
            setcookie('real_notify_last_check', $current_time, time() + 30 * 24 * 60 * 60, COOKIEPATH, COOKIE_DOMAIN);
        }
    }

    echo json_encode($response);
    wp_die();
}
add_action('wp_ajax_real_notify_check_notifications', 'real_notify_check_notifications');
add_action('wp_ajax_nopriv_real_notify_check_notifications', 'real_notify_check_notifications');
