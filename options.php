<?php

// Add the options page to the WordPress menu
function zw_webapp_add_admin_menu() {
    add_options_page('ZuidWest Webapp', 'ZuidWest Webapp', 'manage_options', 'zw_webapp', 'zw_webapp_options_page');
}

add_action('admin_menu', 'zw_webapp_add_admin_menu');

// Register settings
function zw_webapp_settings_init() {
    register_setting('pluginPage', 'zw_webapp_settings');

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

function zw_webapp_settings_render($args) {
    $options = get_option('zw_webapp_settings');
    ?>
    <input type='text' name='zw_webapp_settings[<?php echo $args[0]; ?>]' value='<?php echo $options[$args[0]]; ?>'>
    <?php
}

function zw_webapp_settings_section_callback() {
    echo __('Set the values required for the ZuidWest Webapp integration.', 'wordpress');
}

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
