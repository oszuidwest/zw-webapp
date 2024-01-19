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
        'meta_value' => '1'
    );
    $recent_pushed_posts = new WP_Query($args);

    // Display recent articles
    echo '<h3>Gepushte artikelen</h3>';
    if ($recent_pushed_posts->have_posts()) {
        echo '<ul>';
        while ($recent_pushed_posts->have_posts()) {
            $recent_pushed_posts->the_post();
            echo '<li>' . get_the_title() . ' - ' . get_the_date() . '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>Recent niets gepusht.</p>';
    }
    wp_reset_postdata();

    // Display daily push count
    echo '<h3>Hoeveelheid pushberichten</h3>';
    $daily_push_count = zw_webapp_get_daily_push_count();
    if (!empty($daily_push_count)) {
        foreach ($daily_push_count as $date => $count) {
            echo '<p>' . esc_html($date) . ': ' . esc_html($count) . '</p>';
        }
    } else {
        echo '<p>No data available.</p>';
    }
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
