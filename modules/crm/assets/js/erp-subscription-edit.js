;(function($) {
    'use strict';

    $('.erp-subscription-edit').on('submit', function (e) {
        e.preventDefault();

        var self      = $(this),
            form_data = $(this).serialize();

        $(this).addClass('doing-ajax')
               .find('.erp-subscription-edit-msg')
               .removeClass('error-msg warning-msg success-msg')
               .html('');

        $(this).find('input, button, textarea').prop('disabled', true);

        $.ajax({
            url: erpSubscriptionEdit.ajaxurl,
            method: 'post',
            dataType: 'json',
            data: {
                action: 'erp_subscript_edit_save_data',
                _wpnonce: erpSubscriptionEdit.nonce,
                form_data: form_data
            }

        }).done(function (response) {

            if (response.success && response.data.msg) {
                self.find('.erp-subscription-edit-msg')
                       .addClass('success-msg')
                       .html(response.data.msg);

            } else if (response.data.msg) {
                self.find('.erp-subscription-edit-msg')
                       .addClass('error-msg')
                       .html(response.data.msg);
            }

            self.removeClass('doing-ajax');

        }).always(function () {
            self.find('input, button, textarea').prop('disabled', false);
        });
    })

})(jQuery);
