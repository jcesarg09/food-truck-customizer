jQuery(document).ready(function($) {
    // WordPress Media Uploader
    var custom_uploader;

    $('#upload_image_button').click(function(e) {
        e.preventDefault();

        // If the uploader object has already been created, reopen the dialog
        if (custom_uploader) {
            custom_uploader.open();
            return;
        }

        // Extend the wp.media object
        custom_uploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Image',
            button: {
                text: 'Choose Image'
            },
            multiple: false
        });

        // When an image is selected, run a callback
        custom_uploader.on('select', function() {
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            $('#upload_image').val(attachment.url);
        });

        // Open the uploader dialog
        custom_uploader.open();
    });

    // Additional admin functionalities can be added here
    // For example, event listeners, AJAX calls, etc.
});
