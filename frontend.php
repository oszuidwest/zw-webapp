<?php
/**
 * Add link to the Manifest in the theme.
 */
add_action('wp_enqueue_scripts', 'zw_webapp_enqueue_scripts');
add_action('wp_head', 'zw_webapp_head_tags');

function zw_webapp_enqueue_scripts()
{
    $webapp_settings = get_option('zw_webapp_settings');

    if (!empty($webapp_settings['progressier_id'])) {
        $base_url = 'https://progressier.app/' . $webapp_settings['progressier_id'];
        wp_enqueue_script('progressier', $base_url . '/script.js', [], false, ['strategy' => 'defer']);
    }
}

function zw_webapp_head_tags()
{
    $webapp_settings = get_option('zw_webapp_settings');

    if (!empty($webapp_settings['progressier_id'])) {
        $base_url = 'https://progressier.app/' . $webapp_settings['progressier_id'];
        echo '<link rel="manifest" href="' . esc_url($base_url . '/progressier.json') . '"/>' . "\n";
    }

    // Check for required settings
    if (!empty($webapp_settings['theme_color'])) {
        $theme_color = esc_attr($webapp_settings['theme_color']);
        echo '<meta name="theme-color" content="' . $theme_color . '"/>' . "\n";
    }
}
