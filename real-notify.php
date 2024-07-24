<?php
/*
Plugin Name: Real Notify
Description: A plugin to notify users of new posts in real-time.
Version: 1.0
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
function real_notify_enqueue_assets() {
    wp_enqueue_script('heartbeat');
    wp_enqueue_style('real-notify-styles', REAL_NOTIFY_PLUGIN_URL . 'css/styles.css');
    wp_enqueue_script('real-notify-scripts', REAL_NOTIFY_PLUGIN_URL . 'js/script.js', array('jquery', 'heartbeat'), null, true);
}
add_action('wp_enqueue_scripts', 'real_notify_enqueue_assets');

function real_notify_heartbeat_settings($settings) {
    $settings['interval'] = 15; // Set the interval to 15 seconds
    return $settings;
}
add_filter('heartbeat_settings', 'real_notify_heartbeat_settings');

// Hook into publish actions
$args = array(
    'public'   => true,
);
$post_types = get_post_types($args, 'names');

// here add all post type in this array which we want to use for notification
// $post_types = array('post');
foreach ($post_types as $post_type) {
    add_action('publish_' . $post_type, 'real_notify_published_post');
}

function real_notify_published_post($post_id) {
    $post = get_post($post_id);
    $args = array(
        'title' => 'New Post By: ' . get_the_author_meta('display_name', $post->post_author),
        'content' => 'There is a new post published. See <a href="' . get_permalink($post_id) . '">' . $post->post_title . '</a>',
        'type' => 'info',
        'timestamp' => time()
    );
    // here all the post will shown to logged in user which are published in last 1hour =3600 s
    set_transient('real_notify_' . mt_rand(100000, 999999), $args, 3600); 
}

function real_notify_heartbeat_received($response, $data) {
    if (!is_user_logged_in()) {
        return $response;
    }

    if (!isset($data['notify_status']) || $data['notify_status'] !== 'ready') {
        return $response;
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $last_check = get_user_meta($user_id, 'real_notify_last_check', true);

    if (!$last_check) {
        $last_check = 0;
    }

    $sql = $wpdb->prepare("SELECT * FROM $wpdb->options WHERE option_name LIKE %s", '_transient_real_notify_%');

    $notifications = $wpdb->get_results($sql);

    if (empty($notifications)) {
        return $response;
    }

    $response['real_notify'] = array();

    foreach ($notifications as $db_notification) {
        $id = str_replace('_transient_', '', $db_notification->option_name);

        if (false !== ($notification = get_transient($id))) {
            if ($notification['timestamp'] > $last_check) {
                $response['real_notify'][$id] = $notification;
                update_user_meta($user_id, 'real_notify_last_check', $notification['timestamp']);
            }
        }
    }

    return $response;
}
add_filter('heartbeat_received', 'real_notify_heartbeat_received', 10, 2);