<?php
/**
 * Settings page and options handling for the ZuidWest Webapp plugin.
 *
 * @package ZuidWestWebapp
 */

defined( 'ABSPATH' ) || exit;

/**
 * Adds the plugin options page to the WordPress admin menu.
 *
 * @return void
 */
function zw_webapp_add_admin_menu(): void {
	add_options_page(
		'ZuidWest Webapp',
		'ZuidWest Webapp',
		'manage_options',
		'zw_webapp',
		'zw_webapp_options_page'
	);
}
add_action( 'admin_menu', 'zw_webapp_add_admin_menu' );

/**
 * Registers the plugin settings, section and fields.
 *
 * @return void
 */
function zw_webapp_settings_init(): void {
	register_setting( 'pluginPage', 'zw_webapp_settings', array( 'sanitize_callback' => 'zw_webapp_validate_settings' ) );

	add_settings_section(
		'zw_webapp_pluginPage_section',
		__( 'ZuidWest Webapp Settings', 'zw-webapp' ),
		'zw_webapp_settings_section_callback',
		'pluginPage'
	);

	// Fields for the settings.
	$fields = array(
		array( 'theme_color', __( 'Theme Color', 'zw-webapp' ) ),
		array( 'progressier_id', __( 'Progressier ID', 'zw-webapp' ) ),
		array( 'auth_token', __( 'Authorization Token', 'zw-webapp' ) ),
		array( 'show_push_debug', __( 'Enable push debug', 'zw-webapp' ) ),
	);

	foreach ( $fields as $field ) {
		add_settings_field(
			$field[0],
			$field[1],
			'zw_webapp_settings_field_callback',
			'pluginPage',
			'zw_webapp_pluginPage_section',
			array( 'id' => $field[0] )
		);
	}
}

add_action( 'admin_init', 'zw_webapp_settings_init' );

/**
 * Callback to render each settings field.
 *
 * @param array<string, mixed> $args Field arguments.
 * @return void
 */
function zw_webapp_settings_field_callback( array $args ): void {
	$options     = get_option( 'zw_webapp_settings' );
	$field_value = isset( $options[ $args['id'] ] ) ? esc_attr( $options[ $args['id'] ] ) : '';

	switch ( $args['id'] ) {
		case 'theme_color':
		case 'progressier_id':
			printf( '<input type="text" name="zw_webapp_settings[%s]" value="%s" autocomplete="off">', esc_attr( $args['id'] ), esc_attr( $field_value ) );
			break;

		case 'auth_token':
			printf( '<input type="password" name="zw_webapp_settings[%s]" value="%s" autocomplete="off">', esc_attr( $args['id'] ), esc_attr( $field_value ) );
			break;

		case 'show_push_debug':
			$checked = $field_value ? 'checked' : '';
			echo '<input type="checkbox" name="zw_webapp_settings[show_push_debug]" value="1" ' . esc_attr( $checked ) . ' autocomplete="off">';
			break;

		default:
			echo 'Invalid settings field: ' . esc_html( $args['id'] );
			break;
	}
}

/**
 * Renders the settings section description (currently empty).
 *
 * @return void
 */
function zw_webapp_settings_section_callback(): void {
	// Reserved for future description text.
}

/**
 * Renders the plugin options page.
 *
 * @return void
 */
function zw_webapp_options_page(): void {
	?>
	<form action='options.php' method='post'>
		<h2>ZuidWest Webapp</h2>
		<?php
		settings_fields( 'pluginPage' );
		do_settings_sections( 'pluginPage' );
		submit_button();
		?>
	</form>
	<?php
}

/**
 * Validates the submitted settings; required fields fall back to the previous value.
 *
 * @param array<string, mixed> $value Submitted settings.
 * @return array<string, mixed> Sanitised settings.
 */
function zw_webapp_validate_settings( array $value ): array {
	$old_value = get_option( 'zw_webapp_settings' );

	// List of fields that should not be empty.
	$required_fields = array( 'theme_color', 'progressier_id', 'auth_token' );

	foreach ( $required_fields as $field ) {
		if ( empty( $value[ $field ] ) ) {
			// Add an error message to be displayed in the admin.
			add_settings_error(
				'zw_webapp_settings',
				$field . '_error',
				sprintf( 'Error: %s cannot be empty.', $field ),
				'error'
			);

			// Return the old value to prevent the new empty value from being saved.
			return $old_value;
		}
	}

	// If all fields are valid, return the sanitised value.
	return $value;
}
