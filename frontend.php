<?php
// Link to the Manifest in the theme
add_action('wp_head', 'add_manifest_link');

function add_manifest_link() {
    // Add the manifest link tag
    echo '<link rel="manifest" href="https://progressier.app/<?php echo get_option("zw_webapp_settings")["progressier_id"]; ?>/progressier.json"/>' . "\n";
    echo '<script defer src="https://progressier.app/<?php echo get_option("zw_webapp_settings")["progressier_id"]; ?>/script.js"></script>' . "\n";
    echo '<meta name="theme-color" content="<?php echo get_option("zw_webapp_settings")["theme_color"]; ?>"/>' . "\n";
}
