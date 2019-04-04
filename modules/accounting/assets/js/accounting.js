(function($) {

    var ERP_Accounting = {

        initialize: function() {

            $('body').on( 'click', '.erp-ac-ob-add-more', this.ob.moreField );
            $('body').on( 'click', '.erp-ac-ob-remove-field', this.ob.removeField );

        },

        ob: {

            moreField: function(e) {
                e.preventDefault();

                let clone = $('.erp-ac-ob-field-clone').first('.row').clone();
                $('.erp-ac-multiple-ob-field').append(clone).find('.fa-times-circle').show();
            },

            removeField: function(e) {
                e.preventDefault();

                let self = $(this),
                    sub_ob_wrap = $('.erp-ac-multiple-ob-field'),
                    row_length = sub_ob_wrap.find('.row').length;

                if ( row_length > 2 ) {
                    self.closest('.row').remove();

                    if ( row_length <= 3 ) {
                        sub_ob_wrap.find('.fa-times-circle').hide();
                    }
                }
            }
        },
    };

    $(function() {
        ERP_Accounting.initialize();
    });

})(jQuery);
