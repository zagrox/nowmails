jQuery(document).ready(function($) {
    // Tab functionality
    $('.nowmails-tab').on('click', function(e) {
        e.preventDefault();
        const target = $(this).data('target');
        
        // Update active tab
        $('.nowmails-tab').removeClass('active');
        $(this).addClass('active');
        
        // Show target content
        $('.nowmails-tab-content').removeClass('active');
        $(target).addClass('active');
    });

    // Form validation
    $('.nowmails-form').on('submit', function(e) {
        const $form = $(this);
        const $submitButton = $form.find('button[type="submit"]');
        
        // Disable submit button
        $submitButton.prop('disabled', true);
        
        // Show loading state
        $submitButton.html('<span class="spinner is-active"></span> Saving...');
    });

    // API Key visibility toggle
    $('.toggle-api-key').on('click', function(e) {
        e.preventDefault();
        const $input = $(this).prev('input');
        const $icon = $(this).find('i');
        
        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $icon.removeClass('dashicons-hidden').addClass('dashicons-visibility');
        } else {
            $input.attr('type', 'password');
            $icon.removeClass('dashicons-visibility').addClass('dashicons-hidden');
        }
    });

    // Color picker initialization
    if ($.fn.wpColorPicker) {
        $('.nowmails-color-picker').wpColorPicker();
    }

    // Media uploader
    $('.nowmails-media-upload').on('click', function(e) {
        e.preventDefault();
        const $button = $(this);
        const $input = $button.prev('input');
        const $preview = $button.next('.media-preview');
        
        const frame = wp.media({
            title: 'Select or Upload Media',
            button: {
                text: 'Use this media'
            },
            multiple: false
        });
        
        frame.on('select', function() {
            const attachment = frame.state().get('selection').first().toJSON();
            $input.val(attachment.url);
            if ($preview.length) {
                $preview.html('<img src="' + attachment.url + '" alt="">');
            }
        });
        
        frame.open();
    });

    // Subaccount management
    $('.add-subaccount').on('click', function(e) {
        e.preventDefault();
        const $form = $('#subaccount-form');
        $form.slideDown();
    });

    $('.cancel-subaccount').on('click', function(e) {
        e.preventDefault();
        const $form = $('#subaccount-form');
        $form.slideUp();
        $form[0].reset();
    });

    // Template management
    $('.preview-template').on('click', function(e) {
        e.preventDefault();
        const templateId = $(this).data('template-id');
        const $modal = $('#template-preview-modal');
        
        // Load template content via AJAX
        $.ajax({
            url: nowmailsAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'nowmails_preview_template',
                nonce: nowmailsAdmin.nonce,
                template_id: templateId
            },
            success: function(response) {
                if (response.success) {
                    $modal.find('.modal-content').html(response.data);
                    $modal.show();
                } else {
                    alert('Failed to load template preview');
                }
            }
        });
    });

    // Analytics date range picker
    if ($.fn.datepicker) {
        $('.nowmails-date-range').datepicker({
            range: true,
            dateFormat: 'yy-mm-dd',
            onSelect: function(selectedDates) {
                const [start, end] = selectedDates.split(' - ');
                $('#analytics-start-date').val(start);
                $('#analytics-end-date').val(end);
            }
        });
    }

    // Close modal
    $('.close-modal').on('click', function(e) {
        e.preventDefault();
        $(this).closest('.modal').hide();
    });

    // Close modal on outside click
    $(window).on('click', function(e) {
        if ($(e.target).hasClass('modal')) {
            $('.modal').hide();
        }
    });
}); 