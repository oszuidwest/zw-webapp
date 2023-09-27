<?php
/**
 * Add link to the Manifest in the theme.
 */
add_action('wp_head', 'zw_webapp_add_manifest_link');

function zw_webapp_add_manifest_link()
{
    $webapp_settings = get_option('zw_webapp_settings');

    // Check for required settings
    if (empty($webapp_settings['progressier_id']) || empty($webapp_settings['theme_color'])) {
        return;
    }

    $progressier_id = esc_attr($webapp_settings['progressier_id']);
    $theme_color = esc_attr($webapp_settings['theme_color']);
    $base_url = 'https://progressier.app/{$progressier_id}';

    // Output the tags
    echo '<link rel="manifest" href="' . esc_url($base_url . '/progressier.json') . '"/>' . "\n";
add_action('wp_enqueue_scripts', function() {
    global $webapp_settings;
    $script_url = esc_url('https://progressier.app/' . $webapp_settings['progressier_id'] . '/script.js');
    wp_enqueue_script('progressier_script', $script_url, array(), null, true);
});
    echo '<meta name="theme-color" content="' . $theme_color . '"/>' . "\n";
}
