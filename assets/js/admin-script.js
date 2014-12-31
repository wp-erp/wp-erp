/* jshint devel:true */
/* global wpErpCountries */
/* global wp */
/* global wpErp */
/* global _ */

;(function($, window) {
    'use strict';

    $(function() {

        var WeDevs_ERP = {

            initialize: function() {
                this.self = this;

                console.log(window);

                $( 'select#erp-country').on( 'change', this.populateState );

                $( '#postimagediv').on( 'click', '#set-company-thumbnail', this.setCompanyLogo );
                $( '#postimagediv').on( 'click', 'a.remove-logo', this.removeCompanyLogo );

                // modal windows
                $( '.erp-hr-depts').on( 'click', 'a#erp-new-dept', this.modalNewDepartment );

                // this.modalNewDepartment();

                // fire change events
                $( 'select#erp-country').change();
            },

            hideModal: function() {

            },

            modalNewDepartment: function(e) {
                if ( typeof e !== 'undefined' ) {
                    e.preventDefault();
                }

                $.erpPopup({
                    title: wpErp.popup.dept_title,
                    button: wpErp.popup.dept_submit,
                    content: $('#erp-tmpl-new-dept').html(),
                    extraClass: 'smaller',
                    onSubmit: function(modal) {
                        var input = this.serialize();

                        $.post(wpErp.ajaxurl, input, function(resp) {
                            // console.log(resp.data);

                            if ( resp.success === true ) {
                                var row = $('#erp-tmpl-dept-row').html(),
                                    table = $('table.department-list-table');

                                if ( table ) {
                                    var cls = $('tr:last', table).attr('class'),
                                        cls = ( cls === 'odd' ) ? 'alternate' : 'odd';

                                    resp.data.cls = cls;
                                    row = _.template( row, resp.data );
                                    table.append(row);
                                    modal.closeModal();
                                }
                            } else {
                                alert( resp.data );
                            }
                        });
                    }
                });
            },

            setCompanyLogo: function(e) {
                e.preventDefault();
                e.stopPropagation();

                var frame;

                if ( frame ) {
                    frame.open();
                    return;
                }

                frame = wp.media({
                    title: wpErp.upload_logo,
                    button: { text: wpErp.set_logo }
                });

                frame.on('select', function() {
                    var selection = frame.state().get('selection');

                    selection.map( function( attachment ) {
                        attachment = attachment.toJSON();

                        var html = '<img src="' + attachment.url + '" alt="" />';
                            html += '<input type="hidden" name="company_logo_id" value="' + attachment.id + '" />';
                            html += '<a href="#" class="remove-logo">' + wpErp.remove_logo + '</a>';

                        $( '.inside', '#postimagediv' ).html( html );
                    });
                });

                frame.open();
            },

            removeCompanyLogo: function(e) {
                e.preventDefault();

                var html = '<a href="#" id="set-company-thumbnail" class="thickbox">' + wpErp.upload_logo + '</a>';

                $( '.inside', '#postimagediv' ).html( html );
            },

            populateState: function() {
                if ( typeof wpErpCountries === 'undefined' ) {
                    return false;
                }

                var country = $(this).val(),
                    empty = '<option val="-1">-------------</option>';

                console.log( country);
                console.log( wpErpCountries[ country ] );

                if ( wpErpCountries[ country ] ) {
                    var options = '',
                        state = wpErpCountries[ country ];

                    for( var index in state ) {
                        options = options + '<option value="' + index + '">' + state[ index ] + '</option>';
                    }

                    if ( $.isArray( wpErpCountries[ country ] ) ) {
                        $('#erp-state').html( empty );
                    } else {
                        $('#erp-state').html( options );
                    }

                    // console.log(options);
                } else {
                    $('#erp-state').html( empty );
                }
            }
        };

        WeDevs_ERP.initialize();
    });

})(jQuery, this);