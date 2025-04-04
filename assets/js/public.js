jQuery(document).ready(function($) {
    $('#nowmails-subscribe-form').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $successMessage = $form.find('.form-message.success');
        const $errorMessage = $form.find('.form-message.error');
        
        // Hide any existing messages
        $successMessage.hide();
        $errorMessage.hide();
        
        const email = $form.find('input[name="email"]').val();
        
        $.ajax({
            url: nowmailsPublic.ajaxurl,
            type: 'POST',
            data: {
                action: 'nowmails_subscribe',
                nonce: nowmailsPublic.nonce,
                email: email
            },
            success: function(response) {
                if (response.success) {
                    $successMessage.show();
                    $form.find('input[name="email"]').val('');
                } else {
                    $errorMessage.text(response.data).show();
                }
            },
            error: function() {
                $errorMessage.text('An error occurred. Please try again later.').show();
            }
        });
    });
}); 