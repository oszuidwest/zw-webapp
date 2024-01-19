<?php

function zw_webapp_add_dashboard_widgets()
{
    wp_add_dashboard_widget(
        'zw_webapp_dashboard_recent_pushes',
        'Recent Pushed Articles',
        'zw_webapp_dashboard_widget_display'
    );
}
add_action('wp_dashboard_setup', 'zw_webapp_add_dashboard_widgets');

function zw_webapp_dashboard_widget_display()
{
    // Recent Pushed Articles
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => 5,
        'meta_key' => 'push_sent',
        'meta_value' => '1',
        'orderby' => 'date',
        'order' => 'DESC'
    );
    $recent_pushed_posts = new WP_Query($args);

    echo '<div id="published-posts" class="activity-block">';

    echo '<h3>Recent Pushed Articles</h3>';

    if ($recent_pushed_posts->have_posts()) {
        echo '<ul>';

        while ($recent_pushed_posts->have_posts()) {
            $recent_pushed_posts->the_post();

            $time = get_the_time('U');
            $formatted_date_time = date_i18n('j M, H:i', $time);
            $post_edit_link = get_edit_post_link();
            $post_title = get_the_title();

            printf(
                '<li><span>%1$s</span> <a href="%2$s">%3$s</a></li>',
                esc_html($formatted_date_time),
                esc_url($post_edit_link),
                esc_html($post_title)
            );
        }

        echo '</ul>';
    } else {
        echo '<p>No recent articles have been pushed.</p>';
    }

    echo '</div>';

    // Daily Push Count
    $daily_push_count = zw_webapp_get_daily_push_count();
    echo '<div id="zw-webapp-daily-push-count" class="activity-block">';
    echo '<h3>Daily Push Count</h3>';

    if (!empty($daily_push_count)) {
        echo '<ul>';
        foreach ($daily_push_count as $date => $count) {
            $formatted_date = date_i18n('j M', strtotime($date));
            echo sprintf(
                '<li><span>%1$s:</span> <span>%2$s</span></li>',
                esc_html($formatted_date),
                esc_html($count)
            );
        }
        echo '</ul>';
    } else {
        echo '<p>No push data available.</p>';
    }
    echo '</div>';

    wp_reset_postdata();
}
