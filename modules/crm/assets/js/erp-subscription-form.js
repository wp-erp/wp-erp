;(function($) {
    'use strict';

    $('body').on('submit', '.erp-subscription-form', function (e) {
        e.preventDefault();

        var self      = $(this),
            form_data = $(this).serialize();

        $(this).addClass('doing-ajax')
               .find('.erp-subscription-form-msg')
               .removeClass('error-msg warning-msg success-msg')
               .html('');

        $(this).find('input, button, textarea').prop('disabled', true);

        $.ajax({
            url: erpSubscriptionForm.ajaxurl,
            method: 'post',
            dataType: 'json',
            data: {
                action: 'erp_subscript_form_save_data',
                _wpnonce: erpSubscriptionForm.nonce,
                form_data: form_data
            }

        }).done(function (response) {

            if (response.success && response.data.msg) {
                self.find('.erp-subscription-form-msg')
                       .addClass('success-msg')
                       .html(response.data.msg);

                self.removeClass('doing-ajax').get(0).reset();

            } else if (response.data.msg) {
                self.find('.erp-subscription-form-msg')
                       .addClass('error-msg')
                       .html(response.data.msg);

                self.removeClass('doing-ajax');
            }


        }).always(function () {
            self.find('input, button, textarea').prop('disabled', false);
        });
    })

})(jQuery);
