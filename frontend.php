<?php
/**
 * Adds the Progressier manifest link and head tags to the frontend.
 *
 * @package ZuidWestWebapp
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'zw_webapp_enqueue_scripts' );
add_action( 'wp_head', 'zw_webapp_head_tags' );

/**
 * Enqueues the Progressier script when the plugin is configured.
 *
 * @return void
 */
function zw_webapp_enqueue_scripts(): void {
	$webapp_settings = get_option( 'zw_webapp_settings' );

	if ( ! empty( $webapp_settings['progressier_id'] ) ) {
		$base_url = 'https://progressier.app/' . $webapp_settings['progressier_id'];

		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NoExplicitVersion -- External Progressier script, version not controlled by us.
		wp_enqueue_script( 'progressier', $base_url . '/script.js', array(), false, array( 'strategy' => 'defer' ) );
	}
}

/**
 * Outputs the Progressier manifest link and theme-color meta tag in the document head.
 *
 * @return void
 */
function zw_webapp_head_tags(): void {
	$webapp_settings = get_option( 'zw_webapp_settings' );

	if ( ! empty( $webapp_settings['progressier_id'] ) ) {
		$base_url = 'https://progressier.app/' . $webapp_settings['progressier_id'];
		echo '<link rel="manifest" href="' . esc_url( $base_url . '/progressier.json' ) . '"/>' . "\n";
	}

	// Check for required settings.
	if ( ! empty( $webapp_settings['theme_color'] ) ) {
		echo '<meta name="theme-color" content="' . esc_attr( $webapp_settings['theme_color'] ) . '"/>' . "\n";
	}
}
