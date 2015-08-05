/* jshint devel:true */
/* global wpErpCountries */
/* global wp */
/* global wpErp */

;(function($) {
    'use strict';

    var WeDevs_ERP = {

        /**
         * Initialize the events
         *
         * @return {void}
         */
        initialize: function() {


            $( '#postimagediv').on( 'click', '#set-company-thumbnail', this.setCompanyLogo );
            $( '#postimagediv').on( 'click', 'a.remove-logo', this.removeCompanyLogo );

            $( 'body').on( 'click', 'a#erp-company-new-location', this.newCompanyLocation );
            $( '.erp-company-single').on( 'click', 'a.edit-location', this.editCompanyLocation );
            $( '.erp-company-single').on( 'click', 'a.remove-location', this.removeCompanyLocation );

            // on popup, country change event
            $( 'body' ).on('change', 'select.erp-country-select', this.populateState );
            $('body').on( 'erp-hr-after-new-location', this.afterNewLocation );

            this.initDateField();
        },

        afterNewLocation: function(e, res) {
            $('.erp-hr-location-drop-down').append('<option selected="selected" value="'+res.id+'">'+res.title+'</option>');
            $('.erp-hr-location-drop-down').select2("val", res.id);
        },

        initDateField: function() {
            $( '.erp-date-field').datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true
            });
        },

        /**
         * Upload and set company logo
         *
         * @param {event}
         */
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

        /**
         * Remove the company logo
         *
         * @param  {event}
         *
         * @return {void}
         */
        removeCompanyLogo: function(e) {
            e.preventDefault();

            var html = '<a href="#" id="set-company-thumbnail" class="thickbox">' + wpErp.upload_logo + '</a>';

            $( '.inside', '#postimagediv' ).html( html );
        },

        /**
         * Populate the state dropdown based on selected country
         *
         * @return {void}
         */
        populateState: function() {
            if ( typeof wpErpCountries === 'undefined' ) {
                return false;
            }

            var self = $(this),
                country = self.val(),
                parent = self.closest( self.data('parent') ),
                empty = '<option val="-1">-------------</option>';

            if ( wpErpCountries[ country ] ) {
                var options = '',
                    state = wpErpCountries[ country ];

                for ( var index in state ) {
                    options = options + '<option value="' + index + '">' + state[ index ] + '</option>';
                }

                if ( $.isArray( wpErpCountries[ country ] ) ) {
                    parent.find('select.erp-state-select').html( empty );
                } else {
                    parent.find('select.erp-state-select').html( options );
                }

                // console.log(options);
            } else {
                parent.find('select.erp-state-select').html( empty );
            }
        },

        newCompanyLocation: function(e) {
            e.preventDefault();

            var self = $(this);

            $.erpPopup({
                title: self.data('title'),
                button: wpErp.create,
                id: 'erp-new-location',
                content: wp.template( 'erp-address' )({ company_id: self.data('id') }),
                extraClass: 'medium',
                onSubmit: function(modal) {
                    wp.ajax.send( {
                        data: this.serializeObject(),
                        success: function(res) {
                            $('#company-locations').load( window.location.href + ' #company-locations-inside' );
                            $('body').trigger( 'erp-hr-after-new-location', [res]);
                            modal.closeModal();
                        },
                        error: function(error) {
                            alert( error );
                        }
                    });
                }
            });
        },

        editCompanyLocation: function(e) {
            e.preventDefault();

            var self = $(this);

            $.erpPopup({
                title: wpErp.update_location,
                button: wpErp.update_location,
                content: wp.template( 'erp-address' )( self.data('data') ),
                extraClass: 'medium',
                onReady: function() {
                    $( 'li[data-selected]', this ).each(function() {
                        var self = $(this),
                            selected = self.data('selected');

                        if ( selected !== '' ) {
                            self.find( 'select' ).val( selected );
                        }
                    });

                    $( 'select.erp-country-select').change();

                    $( 'li[data-selected]', this ).each(function() {
                        var self = $(this),
                            selected = self.data('selected');

                        if ( selected !== '' ) {
                            self.find( 'select' ).val( selected );
                        }
                    });
                },
                onSubmit: function(modal) {
                    wp.ajax.send( {
                        data: this.serializeObject(),
                        success: function() {
                            $('#company-locations').load( window.location.href + ' #company-locations-inside' );

                            modal.closeModal();
                        },
                        error: function(error) {
                            alert( error );
                        }
                    });
                }
            });
        },

        removeCompanyLocation: function(e) {
            e.preventDefault();

            if ( confirm( wpErpHr.confirm ) ) {
                wp.ajax.send( 'erp-delete-comp-location', {
                    data: {
                        id: $(this).data('id'),
                        _wpnonce: wpErp.nonce
                    },
                    success: function() {
                        $('#company-locations').load( window.location.href + ' #company-locations-inside' );
                    }
                });
            }
        }
    };

    $(function() {
        WeDevs_ERP.initialize();
    });

})(jQuery, this);

/**
 * A nifty plugin to converty form to serialize object
 *
 * @link http://stackoverflow.com/questions/1184624/convert-form-data-to-js-object-with-jquery
 */
jQuery.fn.serializeObject = function() {
    var o = {};
    var a = this.serializeArray();
    jQuery.each(a, function() {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};