# ZuidWest Webapp

The ZuidWest Webapp plugin seamlessly integrates the [Progressier PWA](https://progressier.com/) into a WordPress website. This integration includes a manifest, an options/settings page in the WordPress admin dashboard, and the capability to send push notifications upon the publishing or updating of posts.

## Features

- **Manifest Integration**: Incorporates a link to the manifest within the theme, configurable via user-defined settings.
- **Options Page**: Provides a dedicated page in the WordPress admin dashboard for managing the plugin's settings.
- **Push Notifications**: Enables sending of push notifications whenever a post is published or updated.
- **Push Analytics**: Includes a dashboard widget that displays articles that have been pushed and the amount of push notifications sent in the last 7 days.

## Installation

1. Download the plugin files.
2. Upload the plugin files to the `/wp-content/plugins/` directory.
3. Activate the plugin through the 'Plugins' screen in WordPress.
4. Access the 'ZuidWest Webapp' screen under Options to configure the plugin.
5. Integrate the filters into your theme.

## Integrating in Your Theme

This plugin offers two filters: `zw_webapp_send_notification` determines whether a push notification is sent, defaulting to false. This means that **by default, push notifications are not triggered**. You can attach custom logic, a metabox, or an ACF field to control this behavior. The `zw_webapp_title` filter allows customization of the notification title, which defaults to 'Nieuws'. This filter can be overridden to display, for instance, your site's name or the post's category. Our integration details are available in [this commit](https://github.com/oszuidwest/streekomroep-wp/commit/2f47ef4d259b3826b7643653cb47a567833cd73a).

## Usage

1. **Setting Up the Manifest**: Go to the 'ZuidWest Webapp' options page in the WordPress admin dashboard to configure the necessary settings.
2. **Push Notifications**: Enable debugging and confirm that your posts are set to trigger push notifications upon publishing or updating. Debug messages related to push notifications are accessible in the admin dashboard.
3. **Disable debugging**: Once everything is set, disable debugging.
