<?php

add_action('save_post', 'zw_webapp_schedule_push_notification', 20, 3);
add_action('send_push_notification', 'zw_webapp_call_api');
add_action('edit_form_top', 'zw_webapp_show_debug_message', 10, 1);

function zw_webapp_schedule_push_notification(int $post_id, WP_Post $post, bool $update): void
{
    if (defined('WP_CLI') && WP_CLI) {
        zw_webapp_set_debug_message($post_id, 'Not pushed - Refusing to push on cli-triggered actions');
        return;
    }

    $send_push = false;
    $send_push = apply_filters('zw_webapp_send_notification', $send_push, $post_id);
    if (!$send_push) {
        zw_webapp_set_debug_message($post_id, 'Not pushed - Filter zw_webapp_send_notification returned false, or was not hooked');
        return;
    }

    if ($post->post_type !== 'post') {
        zw_webapp_set_debug_message($post_id, 'Not pushed - Not a post');
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        zw_webapp_set_debug_message($post_id, 'Not pushed - Doing autosave');
        return;
    }
    if (get_post_status($post_id) !== 'publish' || $post->post_status === 'trash') {
        zw_webapp_set_debug_message($post_id, 'Not pushed - Post not published or is in trash');
        return;
    }
    if (get_post_meta($post_id, 'push_sent', true)) {
        zw_webapp_set_debug_message($post_id, 'Not pushed - Push already sent');
        return;
    }

    // Check if a push notification is already scheduled for this post
    if (wp_next_scheduled('send_push_notification', [$post_id])) {
        zw_webapp_set_debug_message($post_id, 'Not pushed - Push notification already scheduled for this post');
        return;
    }

    wp_schedule_single_event(time(), 'send_push_notification', [$post_id]);
    zw_webapp_set_debug_message($post_id, 'Push notification scheduled');
}

function zw_webapp_get_featured_image_url(int $post_id): ?string
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

function zw_webapp_call_api(int $post_id): void
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
        'body' => html_entity_decode(get_post($post_id)->post_title),
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
        zw_webapp_set_debug_message($post_id, 'Error sending push: ' . $response->get_error_message());
        return;
    }

    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);

    if ($response_code !== 200) {
        zw_webapp_set_debug_message($post_id, 'Error sending push: ' . $response_code . ' - ' . $response_body);
        return;
    }

    update_post_meta($post_id, 'push_sent', true);
    zw_webapp_invalidate_push_count_cache();
    zw_webapp_set_debug_message($post_id, 'Push sent successfully');
}

function zw_webapp_set_debug_message(int $post_id, string $message): void
{
    $options = get_option('zw_webapp_settings');
    if (!isset($options['show_push_debug']) || !$options['show_push_debug']) {
        return;
    }

    add_post_meta($post_id, 'zw_webapp_debug_msg', $message);
}

function zw_webapp_show_debug_message(WP_Post $post): void
{
    $messages = get_post_meta($post->ID, 'zw_webapp_debug_msg');
    if ($messages) {
        ?>
        <div class="notice notice-info">
            <p>
            <?php
            foreach ($messages as $index => $message) {
                if ($index > 0) {
                    echo '<br />';
                }
                echo esc_html($message);
            }
            ?>
            </p>
        </div>
        <?php
        delete_post_meta($post->ID, 'zw_webapp_debug_msg');
    }
}

/**
 * @return array<string, int>
 */
function zw_webapp_get_daily_push_count(): array
{
    global $wpdb;
    $cache_key = 'zw_webapp_daily_push_count';
    $daily_push_count = wp_cache_get($cache_key);

    if ($daily_push_count === false) {
        // Use a single direct SQL query instead of 7 separate WP_Query calls
        $results = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT
                    DATE(p.post_date) as push_date,
                    COUNT(*) as count
                FROM
                    ' . $wpdb->posts . ' p
                    JOIN ' . $wpdb->postmeta . ' pm ON p.ID = pm.post_id
                WHERE
                    pm.meta_key = %s
                    AND pm.meta_value = %s
                    AND p.post_type = %s
                    AND p.post_status = %s
                    AND p.post_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                GROUP BY
                    DATE(p.post_date)
                ORDER BY
                    push_date DESC',
                'push_sent',
                '1',
                'post',
                'publish'
            )
        );

        // Initialize counts for all 7 days (today + 6 previous days)
        $daily_push_count = array();
        for ($i = 0; $i <= 6; $i++) {
            $date = date('Y-m-d', strtotime('-' . $i . ' days'));
            $daily_push_count[$date] = 0;
        }

        // Fill in actual counts from query results
        if ($results) {
            foreach ($results as $row) {
                $daily_push_count[$row->push_date] = (int) $row->count;
            }
        }

        // Sort by date (newest first)
        krsort($daily_push_count);

        // Cache for one hour
        wp_cache_set($cache_key, $daily_push_count, '', HOUR_IN_SECONDS);
    }

    return $daily_push_count;
}

function zw_webapp_invalidate_push_count_cache(): void
{
    $cache_key = 'zw_webapp_daily_push_count';
    wp_cache_delete($cache_key);
}
