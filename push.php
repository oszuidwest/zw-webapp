<?php

add_action('save_post', 'zw_webapp_schedule_push_notification', 20, 3);
add_action('send_push_notification', 'zw_webapp_call_api');
add_action('edit_form_top', 'zw_webapp_show_debug_message', 10, 1);

function zw_webapp_schedule_push_notification($post_id, $post, $update)
{
    if (defined('WP_CLI') && WP_CLI) {
        zw_webapp_set_debug_message($post_id, 'Not pushed - Refusing to push on cli-triggered actions');
        return;
    }

    $send_push = false;
    $send_push = apply_filters('zw_webapp_send_notification', $send_push, $post_id);
    if (!$send_push) {
        return zw_webapp_set_debug_message($post_id, 'Not pushed - Filter zw_webapp_send_notification returned false, or was not hooked');
    }

    if ('post' !== $post->post_type) {
        return zw_webapp_set_debug_message($post_id, 'Not pushed - Not a post');
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return zw_webapp_set_debug_message($post_id, 'Not pushed - Doing autosave');
    }
    if ('publish' !== get_post_status($post_id) || 'trash' === $post->post_status) {
        return zw_webapp_set_debug_message($post_id, 'Not pushed - Post not published or is in trash');
    }
    if (get_post_meta($post_id, 'push_sent', true)) {
        return zw_webapp_set_debug_message($post_id, 'Not pushed - Push already sent');
    }

    // Check if a push notification is already scheduled for this post
    if (wp_next_scheduled('send_push_notification', [$post_id])) {
        return zw_webapp_set_debug_message($post_id, 'Not pushed - Push notification already scheduled for this post');
    }

    wp_schedule_single_event(time(), 'send_push_notification', [$post_id]);
    zw_webapp_set_debug_message($post_id, 'Push notification scheduled');
}

function zw_webapp_get_featured_image_url($post_id)
{
    $thumbnail_id = get_post_thumbnail_id($post_id);
    if (!$thumbnail_id) {
        return null;
    }

    $image_url = wp_get_attachment_image_url($thumbnail_id, 'large');
    if (!$image_url) {
        $image_url = wp_get_attachment_image_url($thumbnail_id, 'full');
    }

    return $image_url;
}

function zw_webapp_call_api($post_id)
{
    $title = apply_filters('zw_webapp_title', 'Nieuws', $post_id);

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
        return zw_webapp_set_debug_message($post_id, 'Error sending push: ' . $response->get_error_message());
    }

    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);

    if (200 !== $response_code) {
        return zw_webapp_set_debug_message($post_id, 'Error sending push: ' . $response_code . ' - ' . $response_body);
    }

    update_post_meta($post_id, 'push_sent', true);
    zw_webapp_invalidate_push_count_cache();
    zw_webapp_set_debug_message($post_id, 'Push sent successfully');
}

function zw_webapp_set_debug_message($post_id, $message)
{
    $options = get_option('zw_webapp_settings');
    if (!isset($options['show_push_debug']) || !$options['show_push_debug']) {
        return;
    }

    add_post_meta($post_id, 'zw_webapp_debug_msg', $message);
}

function zw_webapp_show_debug_message(WP_Post $post)
{
    $messages = get_post_meta($post->ID, 'zw_webapp_debug_msg');
    if ($messages) {
        echo '<div class="notice notice-info"><p>' . implode('<br />', $messages) . '</p></div>';
        delete_post_meta($post->ID, 'zw_webapp_debug_msg');
    }
}

function zw_webapp_get_daily_push_count() 
{
    global $wpdb;
    $cache_key = 'zw_webapp_daily_push_count';
    $daily_push_count = wp_cache_get($cache_key);

    if (false === $daily_push_count) {
        $six_days_ago = date('Y-m-d', strtotime('-6 days'));

        $results = $wpdb->get_results($wpdb->prepare('
            SELECT DATE(post_date) AS push_date, COUNT(*) AS count
            FROM ' . $wpdb->posts . ' p
            JOIN ' . $wpdb->postmeta . ' pm ON p.ID = pm.post_id
            WHERE pm.meta_key = \'push_sent\' AND pm.meta_value = \'1\'
            AND post_status = \'publish\' AND post_date >= %s
            GROUP BY push_date
            ORDER BY push_date DESC
        ', $six_days_ago), OBJECT_K);

        // Initialize daily push count array with the last 6 days (including today) set to 0
        $daily_push_count = array();
        for ($i = 0; $i <= 6; $i++) {
            $date = date('Y-m-d', strtotime('-' . $i . ' days'));
            $daily_push_count[$date] = 0;
        }

        // Update counts for days with results
        foreach ($results as $result) {
            if (array_key_exists($result->push_date, $daily_push_count)) {
                $daily_push_count[$result->push_date] = $result->count;
            }
        }

        wp_cache_set($cache_key, $daily_push_count);
    }

    return $daily_push_count;
}

function zw_webapp_invalidate_push_count_cache()
{
    $cache_key = 'zw_webapp_daily_push_count';
    wp_cache_delete($cache_key);
}
