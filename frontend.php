<?php
// Link to the Manifest in the theme
add_action('wp_head', 'add_manifest_link');

function add_manifest_link() {
    // Get the options
    $webapp_settings = get_option("zw_webapp_settings");
    $progressier_id = esc_attr($webapp_settings["progressier_id"]);
    $theme_color = esc_attr($webapp_settings["theme_color"]);

    // Construct the manifest link and script URLs
    $manifest_url = "https://progressier.app/{$progressier_id}/progressier.json";
    $script_url = "https://progressier.app/{$progressier_id}/script.js";

    // Add the manifest link tag
    echo '<link rel="manifest" href="' . esc_url($manifest_url) . '"/>' . "\\n";
    echo '<script defer src="' . esc_url($script_url) . '"></script>' . "\\n";
    echo '<meta name="theme-color" content="' . esc_attr($theme_color) . '"/>' . "\\n";
}
