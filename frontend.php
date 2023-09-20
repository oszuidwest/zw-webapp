<?php
// Link to the Manifest in the theme
add_action('wp_head', 'add_manifest_link');

function add_manifest_link() {
    // Add the manifest link tag
    echo '<link rel="manifest" href="https://progressier.app/m8bdFVT3WcoUOcqtAg3e/progressier.json"/>' . "\n";
    echo '<script defer src="https://progressier.app/m8bdFVT3WcoUOcqtAg3e/script.js"></script>' . "\n";
    echo '<meta name="theme-color" content="#1f2937"/>' . "\n";
}
