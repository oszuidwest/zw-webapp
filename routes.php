<?php
// Register the manifest route
add_action('rest_api_init', 'register_manifest_route');

function register_manifest_route() {
    register_rest_route('webapp', '/manifest/', array(
        'methods' => 'GET',
        'callback' => 'generate_manifest',
    ));
}
