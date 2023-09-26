<?php

add_action('save_post', 'schedule_push_notification_on_publish_or_update', 20, 3);
add_action('send_push_notification', 'send_push_to_api');
add_action('admin_notices', 'show_push_notif_debug_msg');

function schedule_push_notification_on_publish_or_update($post_id, $post, $update) {
    if ('post' !== $post->post_type) return set_debug_message('Not pushed - Not a post');
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return set_debug_message('Not pushed - Doing autosave');
    if ('publish' !== get_post_status($post_id) || 'trash' === $post->post_status) return set_debug_message('Not pushed - Post not published or is in trash');
    if (!get_field('push_post', $post_id)) return set_debug_message('Not pushed - The push_post field is not set to true');
    if (get_post_meta($post_id, 'push_sent', true)) return set_debug_message('Not pushed - Push already sent');
    
    // Check if a push notification is already scheduled for this post
    if (wp_next_scheduled('send_push_notification', [$post_id])) {
        return set_debug_message('Not pushed - Push notification already scheduled for this post');
    }

    wp_schedule_single_event(time() + 10, 'send_push_notification', [$post_id]);
    set_debug_message('Push notification scheduled');
}

function get_featured_image_url($post_id) {
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

function send_push_to_api($post_id) {
    $yoast_primary_term = get_post_meta($post_id, '_yoast_wpseo_primary_regio', true) ?: '';
    if ($yoast_primary_term) {
        $term = get_term($yoast_primary_term, 'regio');
        $yoast_primary_term = $term ? $term->name : '';
    } else {
        $terms = get_the_terms($post_id, 'regio');
        $yoast_primary_term = $terms && !is_wp_error($terms) ? $terms[0]->name : '';
    }

    $post_rank = get_field('post_ranking', $post_id);
    
    $title_prefix = '';  // Set default prefix to empty string

    if (in_array(1, $post_rank)) {
        $title_prefix = 'Breaking';
    } elseif (in_array(3, $post_rank)) {
        $title_prefix = 'Leestip';
    }

    // If the prefix is 'Breaking' or 'Leestip', use only the prefix
    if ($title_prefix === 'Breaking' || $title_prefix === 'Leestip') {
        $title = $title_prefix;
    } else {
        // Check if there's a prefix and yoast_primary_term, else use only the prefix or term
        if (!empty($title_prefix) && !empty($yoast_primary_term)) {
            $title = '{$title_prefix} | {$yoast_primary_term}';
        } else {
            $title = $title_prefix ?: $yoast_primary_term;
        }
    }

    $image_url = get_featured_image_url($post_id);

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
        return set_debug_message('Error sending push: ' . $response->get_error_message());
    }

    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);

    if (200 !== $response_code) {
        return set_debug_message('Error sending push: {$response_code} - {$response_body}');
    }

    update_post_meta($post_id, 'push_sent', true);
    set_debug_message('Push sent successfully');
}

function set_debug_message($message) {
    $options = get_option('zw_webapp_settings');
    if (!isset($options['show_push_debug']) || !$options['show_push_debug']) return;

    update_option('zw_webapp_debug_msg', $message);
}

function show_push_notif_debug_msg() {
    $message = get_option('zw_webapp_debug_msg');
    if ($message) {
        echo '<div class='notice notice-info'><p>{$message}</p></div>';
        delete_option('zw_webapp_debug_msg');
    }
}
