<?php
function zw_webapp_add_dashboard_widgets() {
    wp_add_dashboard_widget(
        'zw_webapp_dashboard_recent_pushes',   // Widget ID
        'Recent Pushed Articles',             // Title
        'zw_webapp_dashboard_widget_display'  // Display function
    );
}
add_action('wp_dashboard_setup', 'zw_webapp_add_dashboard_widgets');

function zw_webapp_dashboard_widget_display() {
    // Fetch the recent pushed articles
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => 5, // Adjust number of posts to display
        'meta_key' => 'push_sent',
        'meta_value' => '1',
        'orderby' => 'date',
        'order' => 'DESC'
    );
    $recent_pushed_posts = new WP_Query($args);

    // Display recent articles
    echo '<div id="published-posts" class="activity-block">';
    echo '<h3>Recent Pushed Articles</h3>';
    if ($recent_pushed_posts->have_posts()) {
        echo '<ul>';

        $today = current_time('Y-m-d');
        $year = current_time('Y');

        while ($recent_pushed_posts->have_posts()) {
            $recent_pushed_posts->the_post();
            $time = get_the_time('U');

            if (gmdate('Y-m-d', $time) === $today) {
                $relative = __('Today');
            } elseif (gmdate('Y', $time) !== $year) {
                $relative = date_i18n(__('M jS Y'), $time);
            } else {
                $relative = date_i18n(__('M jS'), $time);
            }

            echo '<li class="post-item">';
            echo '<span class="post-date">' . $relative . '</span>';
            echo '<a href="' . get_edit_post_link() . '">' . get_the_title() . '</a>';
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>No recent articles.</p>';
    }
    echo '</div>';
    wp_reset_postdata();

    // Display daily push count
    echo '<div id="zw-webapp-daily-push" class="activity-block">';
    echo '<h3>Daily Push Count</h3>';
    $daily_push_count = zw_webapp_get_daily_push_count();
    if (!empty($daily_push_count)) {
        echo '<ul>';
        foreach ($daily_push_count as $date => $count) {
            echo '<li class="post-count-item">';
            // Format the date as per WordPress settings, localized
            $formatted_date = date_i18n(get_option('date_format'), strtotime($date));
            echo '<span class="post-count-date">' . esc_html($formatted_date) . ':</span> ';
            echo '<span class="post-count-number">' . esc_html($count) . '</span>';
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>No data available.</p>';
    }
    echo '</div>';
}

function zw_webapp_get_daily_push_count() {
    global $wpdb;
    $query = "
        SELECT DATE(post_date) as push_date, COUNT(*) as count
        FROM $wpdb->posts
        WHERE ID IN (
            SELECT post_id
            FROM $wpdb->postmeta
            WHERE meta_key = 'push_sent' AND meta_value = '1'
        )
        GROUP BY push_date
        ORDER BY push_date DESC
        LIMIT 7";

    return $wpdb->get_results($query, OBJECT_K);
}
