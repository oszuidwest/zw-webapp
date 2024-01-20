<?php

function zw_webapp_add_dashboard_widgets()
{
    wp_add_dashboard_widget(
        'zw_webapp_dashboard_recent_pushes',
        'ZuidWest Webapp',
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
        'meta_value' => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Used in production with no issues.
        'orderby' => 'date',
        'order' => 'DESC'
    );
    $recent_pushed_posts = new WP_Query($args);

    echo '<div id="published-posts" class="activity-block">';

    echo '<h3>Recent gepusht</h3>';

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
        echo '<p>Er zijn recent geen artikelen gepusht.</p>';
    }

	// Nitpicking
    echo '<style>
            #zw_webapp_dashboard_recent_pushes .inside { margin: 0; padding-bottom: 0; }
          </style>';

    echo '</div>';

	// Push Count
	$daily_push_count = zw_webapp_get_daily_push_count();
	echo '<div id="zw-webapp-daily-push-count">';
	echo '<h3 style="margin-bottom: 15px;">Gepushte artikelen per dag</h3>';

	if (!empty($daily_push_count)) {
	    echo '<ul style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; list-style: none; padding: 0;">';

	    foreach ($daily_push_count as $date => $count) {
	        $formatted_date = date_i18n('j M', strtotime($date));
	        // Check if the count is 1 to decide between 'push' and 'pushes'
	        $push_text = $count == 1 ? 'push' : 'pushes';
	        printf(
	            '<li style="background-color: #f7f7f7; padding: 8px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);"><span>%1$s: %2$s %3$s</span></li>',
	            esc_html($formatted_date),
	            esc_html($count),
	            esc_html($push_text)
	        );
	    }

	    echo '</ul>';
	} else {
	    echo '<p>Geen data over pushberichten beschikbaar.</p>';
	}
	echo '</div>';

	wp_reset_postdata();
}
