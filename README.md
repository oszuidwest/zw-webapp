# ZuidWest Webapp Plugin for WordPress

The ZuidWest Webapp plugin integrates the [Progressier PWA](https://progressier.com/) into a WordPress website. It offers integration with a manifest, provides an options/settings page in the WordPress admin dashboard and enables push notifications upon post publish or update.

## Features

- **Manifest Integration**: Adds a link to the manifest in the theme based on user-defined settings.
- **Options Page**: A dedicated options/settings page in the WordPress admin dashboard to manage the plugin's settings.
- **Push Notifications**: Sends push notifications when a post is published or updated.

## Installation

1. Download the plugin files.
2. Upload the plugin files to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' screen in WordPress.
4. Use the 'ZuidWest Webapp' screen under Options to configure the plugin.
5. Integrate the filters in your theme

## Integrating in your theme

This plug-in provides two filters: `zw_webapp_send_notification` controls if a push is being sent. It can be true of false and defaults to false. So by default it will not trigger push notifications. You can hook-up custom logic, a metabox or as we did an ACF field to control this. The filter `zw_webapp_title` controls the title of the notification. By default it's 'Nieuws' and this filter can override it. For example you can set the name of your site, or the category of a post. Our integration can be found in [this commit](https://github.com/oszuidwest/streekomroep-wp/commit/2f47ef4d259b3826b7643653cb47a567833cd73a).

## Usage

1. **Setting up the Manifest**: Navigate to the 'ZuidWest Webapp' options page in the WordPress admin dashboard and provide the required settings.
2. **Push Notifications**: Ensure your posts trigger push notifications upon publish or update. Debug messages related to push notifications can be viewed in the admin dashboard.
