<?php

// Add the options page to the WordPress menu
function zw_webapp_add_admin_menu() {
    add_options_page('ZuidWest Webapp', 'ZuidWest Webapp', 'manage_options', 'zw_webapp', 'zw_webapp_options_page');
}
add_action('admin_menu', 'zw_webapp_add_admin_menu');

// Register settings
function zw_webapp_settings_init() {
    register_setting('pluginPage', 'zw_webapp_settings', 'validate_zw_webapp_settings');
    
    add_settings_section(
        'zw_webapp_pluginPage_section',
        __('ZuidWest Webapp Settings', 'wordpress'),
        'zw_webapp_settings_section_callback',
        'pluginPage'
    );

    // Fields for the settings
    $fields = [
        ['theme_color', 'Theme Color'],
        ['progressier_id', 'Progressier ID'],
        ['auth_token', 'Authorization Token']
    ];

    foreach ($fields as $field) {
        add_settings_field(
            $field[0],
            __($field[1], 'wordpress'),
            'zw_webapp_settings_render',
            'pluginPage',
            'zw_webapp_pluginPage_section',
            $field
        );
    }
}
add_action('admin_init', 'zw_webapp_settings_init');

// Render settings
function zw_webapp_settings_render($args) {
    $options = get_option('zw_webapp_settings');
    $field_value = isset($options[$args[0]]) ? esc_attr($options[$args[0]]) : '';
    echo "<input type='text' name='zw_webapp_settings[" . esc_attr($args[0]) . "]' value='" . $field_value . "'>";
}

// Callback for settings section (can be expanded if needed)
function zw_webapp_settings_section_callback() { 
    // This can contain any additional description or content for the settings section
}

// Options page rendering
function zw_webapp_options_page() {
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
function validate_zw_webapp_settings($value) {
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

// TODO: Option to enable push debug