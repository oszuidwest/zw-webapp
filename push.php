<?php

add_action('save_post', 'zw_webapp_schedule_push_notification', 20, 3);
add_action('send_push_notification', 'zw_webapp_call_api');
add_action('admin_notices', 'zw_webapp_show_debug_message');
add_filter('zw_webapp_title', 'zw_webapp_push_title', 10, 2);

function zw_webapp_schedule_push_notification($post_id, $post, $update)
{
    if ('post' !== $post->post_type) {
        return zw_webapp_set_debug_message('Not pushed - Not a post');
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return zw_webapp_set_debug_message('Not pushed - Doing autosave');
    }
    if ('publish' !== get_post_status($post_id) || 'trash' === $post->post_status) {
        return zw_webapp_set_debug_message('Not pushed - Post not published or is in trash');
    }
    if (!get_field('push_post', $post_id)) {
        return zw_webapp_set_debug_message('Not pushed - The push_post field is not set to true');
    }
    if (get_post_meta($post_id, 'push_sent', true)) {
        return zw_webapp_set_debug_message('Not pushed - Push already sent');
    }

    // Check if a push notification is already scheduled for this post
    if (wp_next_scheduled('send_push_notification', [$post_id])) {
        return zw_webapp_set_debug_message('Not pushed - Push notification already scheduled for this post');
    }

    wp_schedule_single_event(time(), 'send_push_notification', [$post_id]);
    zw_webapp_set_debug_message('Push notification scheduled');
}

function zw_webapp_get_featured_image_url($post_id)
{
    $thumbnail_id = get_post_thumbnail_id($post_id);
    if (!$thumbnail_id) {
        return false;
    }

    $image_url = wp_get_attachment_image_url($thumbnail_id, 'large');
    if (!$image_url) {
        $image_url = wp_get_attachment_image_url($thumbnail_id, 'full');
    }

    return $image_url;
}

function zw_webapp_push_title($title, $post_id)
{
    $post_rank = get_field('post_ranking', $post_id);
    if (in_array(1, $post_rank)) {
        return 'Breaking';
    }
    if (in_array(3, $post_rank)) {
        return 'Leestip';
    }

    $yoast_primary_term = get_post_meta($post_id, '_yoast_wpseo_primary_regio', true) ?: '';
    if ($yoast_primary_term) {
        $term = get_term($yoast_primary_term, 'regio');
        $yoast_primary_term = $term ? $term->name : '';
    } else {
        $terms = get_the_terms($post_id, 'regio');
        $yoast_primary_term = $terms && !is_wp_error($terms) ? $terms[0]->name : '';
    }

    if (!empty($yoast_primary_term)) {
        return $yoast_primary_term;
    }

    return '';
}

function zw_webapp_call_api($post_id)
{
    $title = apply_filter('zw_webapp_title', 'Nieuws', $post_id);

    $image_url = zw_webapp_get_featured_image_url($post_id);

    // Append the UTM source parameter to the URL
    $base_url = get_permalink($post_id);
    $utm_url = add_query_arg('utm_source', 'push', $base_url);

    $body_content = [
        'recipients' => new stdClass(),
        'url' => $utm_url,
        'title' => $title,
        'body' => get_post($post_id)->post_title,
    ];

    if ($image_url) {
        $body_content['image'] = $image_url;
    }

    $response = wp_remote_post('https://progressier.app/' . get_option('zw_webapp_settings')['progressier_id'] . '/send', [
        'headers' => [
            'Authorization' => 'Bearer ' . get_option('zw_webapp_settings')['auth_token'],
            'Content-Type' => 'application/json',
        ],
        'body' => json_encode($body_content)
    ]);

    if (is_wp_error($response)) {
        return zw_webapp_set_debug_message('Error sending push: ' . $response->get_error_message());
    }

    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);

    if (200 !== $response_code) {
        return zw_webapp_set_debug_message('Error sending push: {$response_code} - {$response_body}');
    }

    update_post_meta($post_id, 'push_sent', true);
    zw_webapp_set_debug_message('Push sent successfully');
}

function zw_webapp_set_debug_message($message)
{
    $options = get_option('zw_webapp_settings');
    if (!isset($options['show_push_debug']) || !$options['show_push_debug']) {
        return;
    }

    update_option('zw_webapp_debug_msg', $message);
}

function zw_webapp_show_debug_message()
{
    $message = get_option('zw_webapp_debug_msg');
    if ($message) {
        echo '<div class="notice notice-info"><p>' . $message . '</p></div>';
        delete_option('zw_webapp_debug_msg');
    }
}
