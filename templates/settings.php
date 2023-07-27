<!-- File: templates/settings.php -->

<div class="wrap">
    <h1><?= esc_html(get_admin_page_title()); ?></h1>
    <form action="options.php" method="post">
        <?php
        // Output security fields for the registered setting "ftc_settings"
        settings_fields('ftc_settings');
        // Output setting sections and their fields
        do_settings_sections('ftc_settings');
        // Output save settings button
        submit_button('Save Settings');
        ?>
    </form>
</div>
