# Real Notify

**Plugin Name:** Real Notify  
**Description:** A plugin to notify users of new posts in real-time.  
**Version:** 1.0  
**Author:** ronak3899  
**Author URI:** [https://profiles.wordpress.org/ronak3899/](https://profiles.wordpress.org/ronak3899/)  
**Text Domain:** real-notify  

## Description

Real Notify is a WordPress plugin that sends real-time notifications to logged-in users whenever a new post is published. Notifications appear as pop-ups on the user's screen and are powered by the WordPress Heartbeat API.

## Installation

1. **Download the Plugin**  
   You can download the plugin from the [GitHub repository](https://github.com/yourusername/real-notify) or by cloning the repository.

2. **Upload the Plugin**  
   - Navigate to the `Plugins` menu in your WordPress dashboard.
   - Click `Add New` and then `Upload Plugin`.
   - Choose the downloaded `real-notify.zip` file and click `Install Now`.

3. **Activate the Plugin**  
   - After installation, click `Activate` to enable the plugin on your WordPress site.

## Usage

1. **No Configuration Required**  
   The plugin automatically displays notifications for all public post types when a new post is published.

2. **Check Notifications**  
   - Logged-in users will see a pop-up notification on their screen.
   - The notification includes the title and a link to the newly published post.

## Configuration

1. **Heartbeat Interval**  
   The plugin sets the Heartbeat API interval to 15 seconds. This interval determines how often the plugin checks for new notifications.

2. **Custom Post Types**  
   To include or exclude specific post types from notifications, edit the `$post_types` array in the plugin code.

## Customization

1. **Modify Notification Content**  
   You can customize the notification content and appearance by editing the `real_notify_published_post` function and the `script.js` file located in the `js` folder.

2. **Change Heartbeat Interval**  
   To adjust the frequency of the Heartbeat API checks, modify the `$settings['interval']` value in the `real_notify_heartbeat_settings` function.

## Functionality

1. **Notification Handling**  
   - Notifications are generated when a post is published and stored using the `set_transient` function.
   - Logged-in users receive pop-up notifications if they are currently on the site and their Heartbeat API check finds new notifications.

2. **User-Specific Notifications**  
   - Notifications are shown only to logged-in users.
   - Non-logged-in users will not receive notifications.

## Troubleshooting

- **No Notifications?**  
  Ensure that the Heartbeat API is enabled and that you are logged in as a user with notifications.

- **Notifications Not Showing**  
  Check the plugin’s JavaScript console for errors and ensure the plugin is correctly enqueued.