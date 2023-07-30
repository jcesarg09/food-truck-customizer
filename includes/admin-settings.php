<?php
// admin-settings.php

function ftc_settings_page_html() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Retrieve the plugin settings from the database
    $settings = get_option('ftc_settings', array());
    ?>
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
        <label for="taxRate">Tax Rate (%):</label>
        <input type="number" id="taxRate" name="ftc_settings[tax_rate]" value="<?php echo isset($settings['tax_rate']) ? esc_attr($settings['tax_rate']) : ''; ?>">

        <label for="defaultImageUrl">Default Food Truck Image URL:</label>
        <input type="text" id="defaultImageUrl" name="ftc_settings[default_image_url]" value="<?php echo isset($settings['default_image_url']) ? esc_attr($settings['default_image_url']) : ''; ?>">

        <label for="currencySymbol">Currency Symbol:</label>
        <input type="text" id="currencySymbol" name="ftc_settings[currency_symbol]" value="<?php echo isset($settings['currency_symbol']) ? esc_attr($settings['currency_symbol']) : '$'; ?>">

        <label for="equipmentCategories">Equipment Categories:</label>
        <textarea name="ftc_settings[equipment_categories]" id="equipmentCategories" rows="5" cols="50"><?php echo isset($settings['equipment_categories']) ? esc_textarea($settings['equipment_categories']) : ''; ?></textarea>
    </div>
    <?php
}
