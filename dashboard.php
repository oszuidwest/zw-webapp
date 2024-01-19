<?php
// dashboard.php

function zw_add_dashboard_widgets() {
    wp_add_dashboard_widget(
        'zw_dashboard_recent_pushes',         // Widget slug.
        'Pushberichten',                      // Title.
        'zw_dashboard_widget_display'         // Display function.
    );
}
add_action('wp_dashboard_setup', 'zw_add_dashboard_widgets');

function zw_dashboard_widget_display() {
    // Fetch the recent pushed articles and count of daily pushes.
    $recent_articles = []; // Placeholder
    $daily_push_count = []; // Placeholder

    // Display recent articles
    echo '<h3>Gepushte artikelen</h3>';
    if (!empty($recent_articles)) {
        echo '<ul>';
        foreach ($recent_articles as $article) {
            echo '<li>' . esc_html($article->title) . '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>Recent niets gepusht.</p>';
    }

    // Display daily push count
    echo '<h3>Hoeveelheid pushberichten</h3>';
    if (!empty($daily_push_count)) {
        foreach ($daily_push_count as $date => $count) {
            echo '<p>' . esc_html($date) . ': ' . esc_html($count) . '</p>';
        }
    } else {
        echo '<p>Geen data beschikbaar.</p>';
    }
}
