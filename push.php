<?php

add_action('save_post', 'schedule_push_notification_on_publish_or_update', 20, 3);
add_action('send_push_notification', 'send_push_to_api');
add_action('admin_notices', 'show_push_notif_debug_msg');

function schedule_push_notification_on_publish_or_update($post_id, $post, $update) {
    if ('post' !== $post->post_type) return set_debug_message('Not a post');
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return set_debug_message('Doing autosave');
    if ('publish' !== get_post_status($post_id) || 'trash' === $post->post_status) return set_debug_message('Post not published or is in trash');
    if (!get_field('push_post', $post_id)) return set_debug_message('ACF push_post field is not set to true');
    if (get_post_meta($post_id, 'push_sent', true)) return set_debug_message('Push already sent');

    wp_schedule_single_event(time() + 10, 'send_push_notification', [$post_id]);
    set_debug_message('Push notification scheduled');
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
    
    $title_prefix = "Nieuws";
    $title = empty($yoast_primary_term) ? $title_prefix : "{$title_prefix} | {$yoast_primary_term}";

    $response = wp_remote_post("https://progressier.app/xxxxxxx/send", [
        'headers' => [
            'Authorization' => 'Bearer xxxxxx',
            'Content-Type' => 'application/json',
        ],
        'body' => json_encode([
            "recipients" => new stdClass(),
            "url" => get_permalink($post_id),
            "title" => $title,
            "body" => get_post_field('post_title', $post_id),
        ]),
        'method' => 'POST',
        'data_format' => 'body',
    ]);

    $response_body = json_decode(wp_remote_retrieve_body($response), true);
    if ($response_body['status'] ?? null === 'success') add_post_meta($post_id, 'push_sent', '1', true);
}

function set_debug_message($msg) {
    set_transient('push_notif_debug_msg', $msg, 60);
}

function show_push_notif_debug_msg() {
    if ($msg = get_transient('push_notif_debug_msg')) {
        echo "<div class='notice notice-info is-dismissible'><p><strong>Push Notification Debug:</strong> {$msg}</p></div>";
        delete_transient('push_notif_debug_msg');
    }
}
