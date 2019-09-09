(function ($) {

    var ERP_Accounting = {

        initialize: function () {
            $('body').on('click', '.erp-ac-ob-add-more', this.ob.moreField);
            $('body').on('click', '.erp-ac-ob-remove-field', this.ob.removeField);
            $('body').on('change', '.erp-date-field', this.ob.validateDateRange);
        },

        ob: {
            moreField(e) {
                e.preventDefault();

                let clone = '<div class="erp-ac-ob-field-clone"><div class="row">' + '<label for="ob_names[]">Name</label><input type="text" name="ob_names[]"><label for="ob_starts[]"> Start Date</label><input type="text" class="erp-date-field" name="ob_starts[]"><label for="ob_ends[]"> End Date</label><input type="text" name="ob_ends[]" class="erp-date-field"> <span><i class="fa fa-times-circle erp-ac-ob-remove-field"></i></span></div></div>';

                $('.erp-ac-multiple-ob-field').append(clone).find('.fa-times-circle').show();

                $( '.erp-date-field').datepicker({
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    changeYear: true,
                    yearRange: '-50:+5',
                });
            },

            removeField(e) {
                e.preventDefault();

                let self = $(this),
                    sub_ob_wrap = $('.erp-ac-multiple-ob-field'),
                    row_length = sub_ob_wrap.find('.row').length;

                if (row_length) {
                    self.closest('.row').remove();
                }
            },

            validateDateRange(e) {
                e.preventDefault();
                let val = [];
                $('.erp-date-field').each(function(){
                    val.push($(this).val());
                });

                for ( let i = 2; i < val.length; ) {
                    if ( ( Date.parse(val[i]) >= Date.parse(val[i-2]) ) && ( Date.parse(val[i]) <= Date.parse(val[i-1]) ) ) {
                        alert(erp_acct_helper.fin_overlap_msg);
                        $(this).val('');
                    }
                    if ( Date.parse(val[i+1]) < Date.parse(val[i])  ) {
                        alert(erp_acct_helper.fin_val_comp_msg);
                        $(this).val('');
                    }
                    i += 2;
                }
            },
        },
    };

    $(function () {
        ERP_Accounting.initialize();
    });

})(jQuery);
