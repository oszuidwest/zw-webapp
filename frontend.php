<?php
declare(strict_types=1);
// Link to the Manifest in the theme
add_action('wp_head', 'add_manifest_link');

function add_manifest_link() {
    
    echo '<meta name="mobile-web-app-capable" content="yes">';
    echo '<meta name="apple-touch-fullscreen" content="yes">';
    echo '<meta name="application-name" content="' . esc_attr(get_bloginfo('name')) . '">';
    echo '<meta name="theme-color" content="#1f2937">';

    echo '<link rel="manifest" href="' . esc_url(get_rest_url(null, '/webapp/manifest/')) . '">';
}
