(function($) {
    'use strict';

    $(document).ready(function() {

        // Color picker
        $('.sds-color-picker').wpColorPicker();

        // Tabs
        $('.nav-tab').on('click', function(e) {
            e.preventDefault();
            var tabId = $(this).data('tab');

            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');

            $('.sds-tab-content').hide();
            $('#' + tabId).show();
        });

        // Media uploader
        $('.sds-upload-btn').on('click', function(e) {
            e.preventDefault();
            var $container = $(this).closest('.sds-media-upload');
            var $input = $container.find('.sds-media-id');
            var $preview = $container.find('.sds-media-preview');
            var $removeBtn = $container.find('.sds-remove-btn');

            var frame = wp.media({
                title: 'Select Logo',
                button: { text: 'Use this image' },
                multiple: false,
                library: { type: 'image' }
            });

            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $input.val(attachment.id);
                $preview.html('<img src="' + attachment.url + '" style="max-width: 200px; height: auto;" />');
                $removeBtn.show();
            });

            frame.open();
        });

        // Remove media
        $('.sds-remove-btn').on('click', function(e) {
            e.preventDefault();
            var $container = $(this).closest('.sds-media-upload');
            $container.find('.sds-media-id').val('');
            $container.find('.sds-media-preview').html('');
            $(this).hide();
        });

        // CodeMirror for CSS editor
        if (typeof sdsCodeEditor !== 'undefined') {
            var editorEl = document.getElementById('sds_pdf_custom_css');
            if (editorEl) {
                wp.codeEditor.initialize(editorEl, sdsCodeEditor);
            }
        }
    });

})(jQuery);
