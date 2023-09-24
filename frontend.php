<?php
/**
 * Add link to the Manifest in the theme.
 */
add_action('wp_head', 'add_manifest_link');

function add_manifest_link() {
    $webapp_settings = get_option("zw_webapp_settings");

    // Check for required settings
    if (empty($webapp_settings["progressier_id"]) || empty($webapp_settings["theme_color"])) {
        return;
    }

    $progressier_id = esc_attr($webapp_settings["progressier_id"]);
    $theme_color = esc_attr($webapp_settings["theme_color"]);
    $base_url = "https://progressier.app/{$progressier_id}";

    // Output the tags
    echo '<link rel="manifest" href="' . esc_url("{$base_url}/progressier.json") . '"/>';
    echo '<script defer src="' . esc_url("{$base_url}/script.js") . '"></script>';
    echo '<meta name="theme-color" content="' . $theme_color . '"/>';
}
