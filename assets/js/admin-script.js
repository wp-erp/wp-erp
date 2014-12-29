/* jshint devel:true */
/* global wpErpCountries */
/* global wp */
/* global wpErp */

;(function($, window) {
    'use strict';

    // function erp_show_modal( title, content, button ) {
    //     var $modal = $( '.erp-modal' );

    //     title      = title || '';
    //     content    = content || '';
    //     button     = button || 'Submit';

    //     $( '.erp-modal-backdrop' ).show();
    //     $modal.find('h2').text( title );
    //     $modal.find('.button-primary').text( button );
    //     $modal.find( '.content').empty().html(content);
    //     $modal.show();
    // }

    $(function() {

        var WeDevs_ERP = {

            initialize: function() {
                this.self = this;

                $('#erp-employee-new').on('click', $.proxy(this.modalNewEmployee, this) );
                $('select#erp-country').on('change', this.populateState );

                $('#postimagediv').on( 'click', '#set-company-thumbnail', this.setCompanyLogo );
                $('#postimagediv').on( 'click', 'a.remove-logo', this.removeCompanyLogo );
            },

            hideModal: function() {

            },

            modalNewEmployee: function(e) {
                e.preventDefault();

                // var form = $('#erp-tmpl-employee').html();
                // erp_show_modal( 'New Employee', form, 'Create Employee');
                // console.log('hello');
                $('#dd').defaultPluginName({});
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
                    button: {
                        text: wpErp.set_logo,
                    }
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

        // close the modal window
        $('.erp-modal-backdrop, .erp-modal .close').on('click', function ( event ) {
            $('.erp-modal-backdrop, .erp-modal').hide();
            event.preventDefault();
        });

        // If pressing ESC close the modal
        $( window ).on( 'keydown', function( e ) {
            if ( 27 === e.keyCode ) {
                $( '.erp-modal-backdrop, .erp-modal' ).hide();
            }
        });

    });

})(jQuery, this);