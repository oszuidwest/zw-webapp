<?php
// Link to the Manifest in the theme
add_action('wp_head', 'add_manifest_link');

function add_manifest_link() {
    echo '<link rel="manifest" href="' . esc_url(get_rest_url(null, '/webapp/manifest/')) . '">';
}
