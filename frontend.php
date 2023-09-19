<?php
// Link to the Manifest in the theme
add_action('wp_head', 'add_manifest_link');

function add_manifest_link() {
    
    echo '<meta name="theme-color" content="#1f2937"/>' . "\n";
}
