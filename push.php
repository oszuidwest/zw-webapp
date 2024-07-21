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

    if ($post->post_type !== 'post') {
        return zw_webapp_set_debug_message($post_id, 'Not pushed - Not a post');
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return zw_webapp_set_debug_message($post_id, 'Not pushed - Doing autosave');
    }
    if (get_post_status($post_id) !== 'publish' || $post->post_status === 'trash') {
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
        'recipients' => ['users' => 'all'],
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

    if ($response_code !== 200) {
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
    $cache_key = 'zw_webapp_daily_push_count';
    $daily_push_count = wp_cache_get($cache_key);

    if ($daily_push_count === false) {
        $daily_push_count = array();

        for ($i = 0; $i <= 6; $i++) {
            // Calculate a weeks worth of dates (last 6 days including today)
            $date = date('Y-m-d', strtotime('-' . $i . ' days'));

            $date_query = array(
                array(
                    'year'  => date('Y', strtotime($date)),
                    'month' => date('m', strtotime($date)),
                    'day'   => date('d', strtotime($date)),
                ),
            );

            $meta_query = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Used in production with no issues
                array(
                    'key'     => 'push_sent',
                    'value'   => '1',
                    'compare' => '='
                )
            );

            $args = array(
                'date_query'     => $date_query,
                'post_status'    => 'publish',
                'meta_query'     => $meta_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Used in production with no issues
                'posts_per_page' => -1,
            );

            $query = new WP_Query($args);
            $daily_push_count[$date] = $query->found_posts;
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
