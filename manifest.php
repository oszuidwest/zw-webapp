<?php
// Generate a Manifest and place it in the wp-json api
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
