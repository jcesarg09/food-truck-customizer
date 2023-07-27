// File: assets/js/admin.js

jQuery(document).ready(function($) {
    // Functionality for uploading custom equipment image
    $('#ftc_upload_image_button').click(function(e) {
        e.preventDefault();
        var image = wp.media({
            title: 'Upload Image',
            multiple: false
        }).open().on('select', function(e) {
            var uploaded_image = image.state().get('selection').first();
            var image_url = uploaded_image.toJSON().url;
            $('#ftc_equipment_image').val(image_url);
        });
    });

    // Functionality for uploading default template image
    var mediaUploader;

    $('#ftc_upload_template_button').click(function(e) {
        e.preventDefault();

        // If the uploader object has already been created, reopen the dialog
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        // Extend the wp.media object
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Image',
            button: {
                text: 'Choose Image'
            },
            multiple: false
        });

        // When a file is selected, grab the URL and set it as the text field's value
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#ftc_default_template').val(attachment.url);
        });

        // Open the uploader dialog
        mediaUploader.open();
    });

    // Functionality for uploading custom template image
    $('#ftc_upload_template_image_button').click(function() {
        var custom_uploader = wp.media({
            title: 'Choose Template Image',
            button: {
                text: 'Use this image'
            },
            multiple: false // Set this to true to allow multiple files to be selected
        }).on('select', function() {
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            $('#ftc_template_image').val(attachment.url);
        }).open();
    });
});

