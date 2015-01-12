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

            $( 'select#erp-country').on( 'change', this.populateState );

            $( '#postimagediv').on( 'click', '#set-company-thumbnail', this.setCompanyLogo );
            $( '#postimagediv').on( 'click', 'a.remove-logo', this.removeCompanyLogo );

            // fire change events
            $( 'select#erp-country').change();
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

            var country = $(this).val(),
                empty = '<option val="-1">-------------</option>';

            if ( wpErpCountries[ country ] ) {
                var options = '',
                    state = wpErpCountries[ country ];

                for ( var index in state ) {
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