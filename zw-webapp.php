<?php
/**
 * Plugin Name: ZuidWest Webapp
 * Description: Integrates Progressier PWA
 * Version: 2.1
 * Author: Streekomroep ZuidWest
 */

require_once plugin_dir_path(__FILE__) . 'frontend.php';
require_once plugin_dir_path(__FILE__) . 'push.php';
require_once plugin_dir_path(__FILE__) . 'options.php';
require_once plugin_dir_path(__FILE__) . 'dashboard.php';

function zw_webapp_invalidate_push_count_cache() {
    $cache_key = 'zw_webapp_daily_push_count';
    wp_cache_delete($cache_key);
}