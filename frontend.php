
<?php
/**
 * Add link to the Manifest in the theme.
 */
add_action('wp_head', 'add_manifest_link');

function add_manifest_link() {
    // Get the options
    $webapp_settings = get_option("zw_webapp_settings");

    // Ensure we have the required settings
    if (!$webapp_settings || !isset($webapp_settings["progressier_id"]) || !isset($webapp_settings["theme_color"])) {
        return;
    }

    $progressier_id = esc_attr($webapp_settings["progressier_id"]);
    $theme_color = esc_attr($webapp_settings["theme_color"]);

    // Construct the manifest link and script URLs
    $manifest_url = "https://progressier.app/{$progressier_id}/progressier.json";
    $script_url = "https://progressier.app/{$progressier_id}/script.js";
?>

    <!-- Adding manifest link and script tags -->
    <link rel="manifest" href="<?php echo esc_url($manifest_url); ?>"/>
    <script defer src="<?php echo esc_url($script_url); ?>"></script>
    <meta name="theme-color" content="<?php echo esc_attr($theme_color); ?>"/>

<?php
}
