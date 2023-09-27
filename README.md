# ZuidWest Webapp Plugin for WordPress

The ZuidWest Webapp plugin integrates the [Progressier PWA](https://progressier.com/) into our WordPress website. It offers integration with a manifest, provides an options/settings page in the WordPress admin dashboard, and enables push notifications upon post publish or update.

## Features

- **Manifest Integration**: Adds a link to the manifest in the theme based on user-defined settings.
- **Options Page**: A dedicated options/settings page in the WordPress admin dashboard to manage the plugin's settings.
- **Push Notifications**: Schedules and sends push notifications when a post is published or updated.

## Installation

///TODO: Add documentation about pushadapter

1. Download the plugin files.
2. Upload the plugin files to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' screen in WordPress.
4. Use the 'ZuidWest Webapp' screen under Options to configure the plugin.

## Usage

1. **Setting up the Manifest**: Navigate to the 'ZuidWest Webapp' options page in the WordPress admin dashboard and provide the required `progressier_id` and `theme_color` settings.
2. **Push Notifications**: Ensure your posts trigger push notifications upon publish or update. Debug messages related to push notifications can be viewed in the admin dashboard.
