<?php
/**
 * Plugin Name: ZuidWest Webapp
 * Description: Generates a manifest.json using wp-json API and uses the site icon set in the Customizer.
 * Version: 0.1
 * Author: Raymon Mens
 */

// Register the manifest route
add_action('rest_api_init', 'register_manifest_route');

function register_manifest_route() {
    register_rest_route('webapp', '/manifest/', array(
        'methods' => 'GET',
        'callback' => 'generate_manifest',
    ));
}

function generate_manifest() {
    $full_name = get_bloginfo('name');
    $short_name_array = explode(' ', $full_name);
    $short_name = $short_name_array[0];

    $icons = array();

    if (has_site_icon()) {
        $sizes = array(192, 512);

        foreach ($sizes as $size) {
            $icons[] = array(
                'src' => get_site_icon_url($size),
                'sizes' => $size . 'x' . $size,
                'type' => 'image/png'
            );
        }
    }

$manifest = array(
    'name' => $full_name,
    'short_name' => $short_name,
    'description' => get_bloginfo('description'),
    'start_url' => '/?utm_source=WebApp',
    'display' => 'standalone',
    'background_color' => '#ffffff',
    'theme_color' => '#000000',
    'orientation' => 'portrait-primary',
    'icons' => $icons,
    'shortcuts' => array(
        array(
            'name' => 'Nieuws',
            'short_name' => 'Nieuws',
            'description' => 'Bekijk het laatste nieuws',
            'url' => '/nieuws/?utm_source=WebApp'
        ),
        array(
            'name' => 'Live Radio',
            'short_name' => 'Live Radio',
            'description' => 'Luister naar ZuidWest FM',
            'url' => '/fm-live/?utm_source=WebApp'
        ),
        array(
            'name' => 'Live TV',
            'short_name' => 'Live TV',
            'description' => 'Kijk naar ZuidWest TV',
            'url' => '/tv-live/?utm_source=WebApp'
        )
    )
);


    $response = new WP_REST_Response($manifest);
    $response->set_headers(['Content-Type' => 'application/json; charset=' . get_option('blog_charset')]);

    return $response;
}

// Link to the Manifest in the theme
add_action('wp_head', 'add_manifest_link');

function add_manifest_link() {
    echo '<link rel="manifest" href="' . esc_url(get_rest_url(null, '/webapp/manifest/')) . '">';
}
