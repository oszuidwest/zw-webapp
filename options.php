<?php

/**
 * Add the options page to the WordPress menu.
 */
function zw_webapp_add_admin_menu()
{
    add_options_page(
        'ZuidWest Webapp',
        'ZuidWest Webapp',
        'manage_options',
        'zw_webapp',
        'zw_webapp_options_page'
    );
}
add_action('admin_menu', 'zw_webapp_add_admin_menu');

/**
 * Register settings for the webapp.
 */
function zw_webapp_settings_init()
{
    register_setting('pluginPage', 'zw_webapp_settings', ['sanitize_callback' => 'zw_webapp_validate_settings']);

    add_settings_section(
        'zw_webapp_pluginPage_section',
        __('ZuidWest Webapp Settings', 'wordpress'),
        'zw_webapp_settings_section_callback',
        'pluginPage'
    );

    // Fields for the settings
    $fields = [
        ['theme_color', __('Theme Color', 'wordpress')],
        ['progressier_id', __('Progressier ID', 'wordpress')],
        ['auth_token', __('Authorization Token', 'wordpress')],
        ['show_push_debug', __('Show push debug', 'wordpress')]
    ];

    foreach ($fields as $field) {
        add_settings_field(
            $field[0],
            $field[1],
            'zw_webapp_settings_field_callback',
            'pluginPage',
            'zw_webapp_pluginPage_section',
            ['id' => $field[0]]
        );
    }
}

add_action('admin_init', 'zw_webapp_settings_init');

/**
 * Callback to render each settings field.
 */
function zw_webapp_settings_field_callback($args)
{
    $options = get_option('zw_webapp_settings');
    $field_value = isset($options[$args['id']]) ? esc_attr($options[$args['id']]) : '';

    switch ($args['id']) {
        case 'theme_color':
        case 'progressier_id':
            echo sprintf('<input type="text" name="zw_webapp_settings[%s]" value="%s" autocomplete="off">', esc_attr($args['id']), $field_value);
            break;

        case 'auth_token':
            echo sprintf('<input type="password" name="zw_webapp_settings[%s]" value="%s" autocomplete="off">', esc_attr($args['id']), $field_value);
            break;

        case 'show_push_debug':
            $checked = $field_value ? 'checked' : '';
            echo '<input type="checkbox" name="zw_webapp_settings[show_push_debug]" value="1" ' . $checked . ' autocomplete="off">';
            break;

        default:
            echo 'Invalid settings field: ' . esc_html($args['id']);
            break;
    }
}

// Callback for settings section (can be expanded if needed)
function zw_webapp_settings_section_callback()
{
    // This can contain any additional description or content for the settings section
}

// Options page rendering
function zw_webapp_options_page()
{
    ?>
    <form action='options.php' method='post'>
        <h2>ZuidWest Webapp</h2>
        <?php
        settings_fields('pluginPage');
        do_settings_sections('pluginPage');
        submit_button();
        ?>
    </form>
    <?php
}

// Validation for the settings
function zw_webapp_validate_settings($value)
{
    $old_value = get_option('zw_webapp_settings');

    // List of fields that should not be empty
    $required_fields = ['theme_color', 'progressier_id', 'auth_token'];

    foreach ($required_fields as $field) {
        if (empty($value[$field])) {
            // Add an error message to be displayed in the admin
            add_settings_error(
                'zw_webapp_settings',
                $field . '_error',
                sprintf('Error: %s cannot be empty.', $field),
                'error'
            );

            // Return the old value to prevent the new empty value from being saved
            return $old_value;
        }
    }

    // If all fields are valid, return the sanitized value
    return $value;
}

?>
