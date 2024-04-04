/* jshint devel:true */
/* global wpErpCountries */
/* global wp */
/* global wpErp */

window.wperp = window.wperp || {};

;(function($) {
    'use strict';

    wperp.template = function ( id ) {
        var options = {
            evaluate:    /<#([\s\S]+?)#>/g,
            interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
            escape:      /\{\{([^\}]+?)\}\}(?!\})/g,
            variable:    'data'
        };

        return function ( data ) {
            return _.template( $( '#tmpl-' + id ).html(), null, options )( data );
        };
    };

    /**
     * Load script after dom replace
     *
     * @return {void}
     */
    wperp.scriptReload =  function( action, id ) {
        wp.ajax.send( {
            data: {
                action: action,
            },
            success: function(res) {
                $('#'+id).html(res.content);
            },
            error: function(error) {
                alert( error );
            }
        });
    };

    /**
     * Set Time Format from date
     *
     * @return {void}
     */
    wperp.timeFormat = function( date ) {
        if ( date == null ) return;
        var d = date.toString();
        date = new Date( d.substr(0, 4), d.substr(5, 2) - 1, d.substr(8, 2), d.substr(11, 2), d.substr(14, 2), d.substr(17, 2) );
        var hours = date.getHours();
        var minutes = date.getMinutes();
        var ampm = hours >= 12 ? 'pm' : 'am';
        hours = hours % 12;
        hours = hours ? hours : 12; // the hour '0' should be '12'
        minutes = minutes < 10 ? '0'+minutes : minutes;
        var strTime = hours + ':' + minutes + ' ' + ampm;
        return strTime;
    };

    wperp.dateFormat = function ( date, format ) {
        if ( date == null ) return;
        var d = date.toString();
        date = new Date( d.substr(0, 4), d.substr(5, 2)-1, d.substr(8, 2), d.substr(11, 2), d.substr(14, 2), d.substr(17, 2) );
        var month = ("0" + (date.getMonth() + 1)).slice(-2),
            day   = ("0" + date.getDate()).slice(-2),
            year  = date.getFullYear(),
            monthArray = [ "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ],
            monthShortArray = [ "Jan", "Feb", "Mar", "Apr", "May", "June", "July", "Aug", "Sept", "Oct", "Nov", "Dec" ],
            monthName = monthArray[date.getMonth()],
            monthShortName = monthShortArray[date.getMonth()];
        var pattern = {
            Y: year,
            m: month,
            F: monthName,
            M: monthShortName,
            d: day,
            j: day
        };

        var dateStr = format.replace(/Y|m|d|j|M|F/gi, function( matched ){
            return pattern[matched];
        });

        return dateStr;
    };

    wperp.parseCondition = function( value ) {
        var obj = {};
        var res = value.split(/([a-zA-Z0-9\s\-\_\+\.\,\:]+)/);
        if ( res[0] == '' ) {
            obj.condition = '';
            obj.val = res[1];
        } else {
            obj.condition = res[0];
            obj.val = res[1];
        }

        return obj;
    };


    wperp.erp_parse_str = function(str, array) {
        var strArr = String(str)
            .replace(/^&/, '')
            .replace(/^\?/, '')
            .replace(/&$/, '')
            .split('&'),
            sal = strArr.length,
            i, j, ct, p, lastObj, obj, lastIter, undef, chr, tmp, key, value,
            postLeftBracketPos, keys, keysLen,
            fixStr = function(str) {
                return decodeURIComponent(str.replace(/\+/g, '%20'));
            };
        if (!array) {
            array = this.window;
        }
        for (i = 0; i < sal; i++) {
            tmp = strArr[i].split('=');
            key = fixStr(tmp[0]);
            value = (tmp.length < 2) ? '' : fixStr(tmp[1]);

            while (key.charAt(0) === ' ') {
                key = key.slice(1);
            }
            if (key.indexOf('\x00') > -1) {
                key = key.slice(0, key.indexOf('\x00'));
            }
            if (key && key.charAt(0) !== '[') {
                keys = [];
                postLeftBracketPos = 0;
                for (j = 0; j < key.length; j++) {
                    if (key.charAt(j) === '[' && !postLeftBracketPos) {
                        postLeftBracketPos = j + 1;
                    } else if (key.charAt(j) === ']') {
                        if (postLeftBracketPos) {
                            if (!keys.length) {
                                keys.push(key.slice(0, postLeftBracketPos - 1));
                            }
                            keys.push(key.substr(postLeftBracketPos, j - postLeftBracketPos));
                            postLeftBracketPos = 0;
                            if (key.charAt(j + 1) !== '[') {
                                break;
                            }
                        }
                    }
                }
                if (!keys.length) {
                    keys = [key];
                }
                for (j = 0; j < keys[0].length; j++) {
                    chr = keys[0].charAt(j);
                    if (chr === ' ' || chr === '.' || chr === '[') {
                        keys[0] = keys[0].substr(0, j) + '_' + keys[0].substr(j + 1);
                    }
                    if (chr === '[') {
                        break;
                    }
                }

                obj = array;
                for (j = 0, keysLen = keys.length; j < keysLen; j++) {
                    key = keys[j].replace(/^['"]/, '')
                        .replace(/['"]$/, '');
                    lastIter = j !== keys.length - 1;
                    lastObj = obj;
                    if ((key !== '' && key !== ' ') || j === 0) {
                        if (obj[key] === undef) {
                            obj[key] = {};
                        }
                        obj = obj[key];
                    } else {
                        // To insert new dimension
                        ct = -1;
                        for (p in obj) {
                            if (obj.hasOwnProperty(p)) {
                                if (+p > ct && p.match(/^\d+$/g)) {
                                    ct = +p;
                                }
                            }
                        }
                        key = ct + 1;
                    }
                }
                lastObj[key] = value;
            }
        }
    };

    wperp.erpGetParamByName = function( name, url ) {
        url = url.toLowerCase();
        name = name.replace(/[\[\]]/g, "\\$&").toLowerCase();

        var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
            results = regex.exec(url);

        if ( !results ) {
            return null;
        }

        if ( !results[2] ) {
            return '';
        }
        return decodeURIComponent(results[2].replace(/\+/g, " "));
    };

    wperp.erpRemoveURLParameter = function ( url, parameter ) {
        //prefer to use l.search if you have a location/link object
        var urlparts= url.split('?');
        if (urlparts.length>=2) {

            var prefix= encodeURIComponent(parameter)+'=';
            var pars= urlparts[1].split(/[&;]/g);

            //reverse iteration as may be destructive
            for (var i= pars.length; i-- > 0;) {
                //idiom for string.startsWith
                if (pars[i].lastIndexOf(prefix, 0) !== -1) {
                    pars.splice(i, 1);
                }
            }

            url= urlparts[0] + (pars.length > 0 ? '?' + pars.join('&') : "");
            return url;
        } else {
            return url;
        }
    };

    wperp.swalSpinnerVisible = function() {
        swal({
            title: '',
            html: true,
            showCancelButton: false,
            showConfirmButton: false,
        });

        $('.la-ball-fall').css({
            'opacity' : 1,
            'visibility' : 'visible',
            'top' : '-17px',
            'color' : '#008ec2'
        });
    };

    wperp.swalSpinnerHidden = function() {
        $('.la-ball-fall').css({
            'opacity' : 0,
            'visibility' : 'hidden',
        });
    };

    var WeDevs_ERP = {

        /**
         * Initialize the events
         *
         * @return {void}
         */
        initialize: function() {
            $( 'body').on( 'mouseover', '.pro-popup', this.proPopupTooltip );
            $( 'body').on( 'click', '.pro-popup-main', this.proPopup );
            $( 'body').on( 'click', '.reports-popup', this.proPopup );
            $( '.org-chart').on( 'click', '.pro-popup', this.proPopup );
            $( '#postimagediv').on( 'click', '#set-company-thumbnail', this.setCompanyLogo );
            $( '#postimagediv').on( 'click', 'a.remove-logo', this.removeCompanyLogo );

            $( 'body').on( 'click', 'a#erp-company-new-location', this.newCompanyLocation );
            $( '.erp-company-single').on( 'click', 'a.edit-location', this.editCompanyLocation );
            $( '.erp-company-single').on( 'click', 'a.remove-location', this.removeCompanyLocation );

            // on popup, country change event
            $( '.erp-hr-employees' ).on( 'click', 'a#erp-employee-new', this.modalOpen );
            $( 'body' ).on( 'click', 'a#erp-company-new-location', this.modalOpen );
            $( 'body' ).on( 'click', '#erp-customer-new', this.modalOpen );
            $( 'body' ).on( 'change', 'select.erp-country-select', this.populateState );

            $( 'body' ).on( 'erp-hr-after-new-location', this.afterNewLocation );

            $( '.erp-hr-audit-log' ).on( 'click', 'a.erp-audit-log-view-changes', this.viewLogChanges );
            $( 'body').on( 'change', '#filter_duration', this.customFilter );

            // PDF plugin notice
            $( 'body' ).on( 'click', '.notice-pdf .notice-dismiss', this.pdfNotice.dismiss );

            // newsletter subscribe
            $( 'body' ).on( 'click', '.email-subscribe-btn', this.emailSubscribe );

            // Validates custom required checkboxes
            $( "body" ).on( "change", "span.checkbox input[required]", this.validateCheckbox );

            // Danger Zone Input & Confirmation Modal
            $( '.erp-danger-zone' ).on( 'input', '#erp_reset_confirmation', this.onChangeDangerZoneInput );
            $( '.erp-danger-zone' ).on( 'click', 'button.tools-btn-submit', this.viewDangerZoneConfirmation );

            $( '.wrap.erp #message button.notice-dismiss' ).on( 'click', this.dismissNoticeMessage );

            this.initFields();
            this.initTipTip();
        },

        initTipTip: function() {
            $( '.erp-tips' ).tipTip( {
                defaultPosition: "top",
                fadeIn: 100,
                fadeOut: 100
            } );
        },
        proPopupTooltip: function (e){
            e.preventDefault();
            $('.pro-popup').mouseover(function (e) {
                $('.org-chart').mouseover(function (e) {
                    $('.erp-custom-menu-container').css('overflow-x', 'inherit')
                });
                $('.org-chart').mouseout(function (e) {
                    $('.erp-custom-menu-container').css('overflow-x', 'auto')
                });
                $(".erp-pro-tooltip-wrapper").remove();
                var tooltip = '<div class="erp-pro-tooltip-wrapper"><div class="erp-pro-tooltip-inner"> ' +
                    '<h4>Available in Pro. Also enjoy:</h4>' +
                    '<ul> ' +
                    '<li><span class="dashicons dashicons-yes"></span>23+ premium extensions</li> ' +
                    '<li><span class="dashicons dashicons-yes"></span>24/7 customer support</li> ' +
                    '<li><span class="dashicons dashicons-yes"></span>Recurring task automation</li> ' +
                    '<li><span class="dashicons dashicons-yes"></span>Work-From-Home feature</li> ' +
                    '<li><span class="dashicons dashicons-yes"></span>Option to choose extensions</li> ' +
                    '</ul> ' +
                    '<div class="tooltip-btn"> ' +
                    '<a class="tooltip_button" href="#">Upgrade to PRO ' +
                    '<svg width=\'16\' height=\'13\' viewBox=\'0 0 16 13\' fill=\'none\' xmlns=\'http://www.w3.org/2000/svg\'>\n' +
                    '<path d=\'M15.3705 3.8954C15.3729 3.93956 15.3697 3.9845 15.3586 4.02914L14.3717 9.18914C14.3219 9.38864 14.1437 9.52892 13.9388 9.53L8.01729 9.56H8.01501H2.09346C1.88746 9.56 1.70796 9.41924 1.6582 9.21872L0.671282 4.04372C0.659918 3.99788 0.656628 3.95168 0.659499 3.90638C0.277712 3.78572 0 3.42734 0 3.005C0 2.4839 0.422579 2.06 0.942056 2.06C1.46153 2.06 1.88411 2.4839 1.88411 3.005C1.88411 3.29846 1.75007 3.56102 1.54019 3.73448L2.77581 4.98332C3.08809 5.29898 3.52144 5.47994 3.96477 5.47994C4.48897 5.47994 4.98877 5.23022 5.30351 4.81184L7.33416 2.1128C7.16357 1.9418 7.05794 1.70552 7.05794 1.445C7.05794 0.9239 7.48052 0.5 8 0.5C8.51948 0.5 8.94206 0.9239 8.94206 1.445C8.94206 1.69772 8.84205 1.9271 8.68037 2.09684L8.68211 2.09894L10.698 4.80542C11.0127 5.22782 11.5143 5.48 12.04 5.48C12.4874 5.48 12.9081 5.30522 13.2244 4.98782L14.4678 3.74054C14.2535 3.56714 14.1159 3.30206 14.1159 3.005C14.1159 2.4839 14.5385 2.06 15.0579 2.06C15.5774 2.06 16 2.4839 16 3.005C16 3.41606 15.7365 3.76568 15.3705 3.8954ZM14.2754 10.97C14.2754 10.7215 14.0746 10.52 13.8268 10.52H2.22307C1.97533 10.52 1.77447 10.7215 1.77447 10.97V12.05C1.77447 12.2985 1.97533 12.5 2.22307 12.5H13.8268C14.0746 12.5 14.2754 12.2985 14.2754 12.05V10.97Z\' fill=\'white\' />\n' +
                    '</svg>' +
                    '</a> ' +
                    '</div> ' +
                    '</div></div>';
                $(this).closest('.pro-popup').append(tooltip);
            });
            $('.pro-popup').mouseout(function (e) {
                $(".erp-pro-tooltip-wrapper").remove()
            })
        },

        proPopup: function (e){
            e.preventDefault();
            $.erpPopup({
                title: '',
                button: '',
                id: 'erp-pro-popup-modal',
                content: wperp.template('erp-pro-popup-modal'),
                extraClass: 'larger',
                footer: false
            });
            var count = 2;
            setInterval(function () {
                var next = $("#img-" + count);
                if(count === 4){
                    count = 1;
                }else {
                    count++;
                }

                next.click()
            }, 4000)
        },
        afterNewLocation: function(e, res) {
            wperp.scriptReload( 'erp_hr_script_reload', 'tmpl-erp-new-employee' );
            $('.erp-hr-location-drop-down').append('<option selected="selected" value="'+res.id+'">'+res.title+'</option>');
            $('.erp-hr-location-drop-down').select2("val", res.id);
        },

        initFields: function() {
            $( '.erp-date-field').datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                yearRange: '-100:+5',
            });

            $( ".erp-date-picker-from" ).datepicker({
                dateFormat: 'yy-mm-dd',
                changeYear: true,
                changeMonth: true,
                numberOfMonths: 1,
                onClose: function( selectedDate ) {
                    $( ".erp-date-picker-to" ).datepicker( "option", "minDate", selectedDate );
                }
            });

            $( ".erp-date-picker-to" ).datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                numberOfMonths: 1,
                onClose: function( selectedDate ) {
                    $( ".erp-date-picker-from" ).datepicker( "option", "maxDate", selectedDate );
                }
            });

            $( '.erp-select2' ).select2({
                placeholder: $(this).attr('data-placeholder')
            });

            // Notify user to save the form
            var settingsForm  = $('.erp-settings').find('#mainform');
            var toolsForm     = $('.erp-tools-form');

            /* global wpErp */
            if (settingsForm.length) {
                settingsForm.areYouSure({ message: wpErp.formUnsavedMsg });
            }

            if (toolsForm.length) {
                toolsForm.areYouSure({ message: wpErp.formUnsavedMsg });
            }
        },

        viewLogChanges: function(e) {
            e.preventDefault();
            var self = $(this);

            wp.ajax.send( 'erp_audit_log_view', {
                data: {
                    id : self.data( 'id' ),
                    _wpnonce: wpErp.nonce
                },
                success: function(res) {
                    $.erpPopup({
                        title: res.title,
                        button: '',
                        id: 'erp-audit-log-popup',
                        content: res.content,
                        extraClass: 'midium',
                    });
                },
                error: function(error) {
                    alert( error );
                }
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
         * Populate the state dropdown based on default country
         *
         * @return {void}
         */

        modalOpen: function() {
            setTimeout(function() {
                $('select.erp-country-select').trigger('change');
            }, 499);
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
                state_field = $( '.state-field' ).find('#erp-state'),
                empty = '<option value="-1">- Select -</option>';

            if ( wpErpCountries[ country ] ) {
                var options = '',
                    state = wpErpCountries[ country ];

                for ( var index in state ) {
                    options = options + '<option value="' + index + '">' + state[ index ] + '</option>';
                }

                if ( $.isArray( wpErpCountries[ country ] ) ) {
                    state_field.html( empty );
                } else {
                    state_field.html( options );
                }

            } else {
                state_field.html( empty );
            }
        },

        pdfNotice: {
            dismiss: function(e) {
                $.ajax({
                    url: ajaxurl,
                    data: {
                        _wpnonce: wpErp.nonce,
                        action: 'dismiss_pdf_notice'
                    }
                });
            }
        },

        /**
         * date filter on audit log
         */
        customFilter: function () {
            if ( 'custom' != this.value ) {
                $( '#custom-input' ).remove();
            } else {
                var element = '<span id="custom-input"><span>From </span><input name="start" class="erp-date-field" type="text">&nbsp;<span>To </span><input name="end" class="erp-date-field" type="text"></span>&nbsp;';
                $( '#filter_duration' ).after( element );
                WeDevs_ERP.initFields();
            }
        },

        newCompanyLocation: function(e) {
            e.preventDefault();

            var self = $(this);

            $.erpPopup({
                title: self.data('title'),
                button: wpErp.create,
                id: 'erp-new-location',
                content: wp.template( 'erp-address' )({ company_id: self.data('id') }).trim(),
                extraClass: 'medium',
                onReady: function() {
                    $( '.select2' ).select2();
                },
                onSubmit: function(modal) {
                    wp.ajax.send( {
                        data: this.serialize(),
                        success: function(res) {
                            $('#company-locations').load( window.location.href + ' #company-locations-inside' );
                            if ( ! self.hasClass('erp-add-new-location') ) {
                                $('body').trigger( 'erp-hr-after-new-location', [res]);
                            };
                            modal.closeModal();
                        },
                        error: function(error) {
                            modal.showError( error );
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
                id: 'erp-edit-location',
                content: wp.template( 'erp-address' )( self.data('data') ),
                extraClass: 'medium',
                onReady: function() {
                    $( '.select2' ).select2();

                    $( 'li[data-selected]', this ).each(function() {
                        var self = $(this),
                            selected = self.data('selected');

                        if ( selected !== '' ) {
                            self.find( 'select' ).val( selected );
                        }
                    });

                    $( 'select.erp-country-select').change();
                },
                onSubmit: function(modal) {
                    wp.ajax.send( {
                        data: this.serializeObject(),
                        success: function() {
                            $('#company-locations').load( window.location.href + ' #company-locations-inside' );
                            modal.closeModal();
                        },
                        error: function(error) {
                            modal.showError( error );
                        }
                    });
                }
            });
        },

        removeCompanyLocation: function(e) {
            e.preventDefault();

            if ( confirm( wpErp.confirmMsg ) ) {
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
        },

        validEmail: function(email) {
            var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(email);
        },

        emailSubscribe: function(e) {
            e.preventDefault();

            var action = 'https://wedevs.us16.list-manage.com/subscribe/post-json?u=66e606cfe0af264974258f030&id=bc304ef91b&c=?';
            var subscribeEmail = $('.email-subscribe').val();

            if ( !WeDevs_ERP.validEmail(subscribeEmail) ) {
                return;
            }

            $(this).prop('disabled', true);

            $.ajax({
                url: action,
                data: {
                    EMAIL: subscribeEmail
                },
                type: 'GET',
                dataType: 'json',
                cache: false,
                contentType: "application/json; charset=utf-8"
            }).success(function(res) {
                $('.erp-newsletter').find('.erp-form-wrap').css('display', 'none');
                $('.erp-newsletter').find('.erp-thank-you').html(res.msg).css('display', 'block');
            });
        },

        validateCheckbox: function(e) {
            var self     = $(this),
                nameAttr = self.attr( "name" ),
                cbItems  = $( "input[name='" + nameAttr + "']" );

            if ( self[0].hasAttribute( "required" ) ) {
                var unchecked = 0;

                cbItems.each( function() {
                    var cb = $(this);

                    if ( cb.is( ":checked" ) ) {
                        cb.attr( "required", "required" );
                    } else {
                        cb.removeAttr( "required" );
                        unchecked++;
                    }
                });

                if ( cbItems.length === unchecked ) {
                    cbItems.each( function() {
                        $(this).attr( "required", "required" );
                    });
                }
            }
        },

        /**
         * Danger Zone Confirmation Popup
         *
         * @param object event
        */
        viewDangerZoneConfirmation: function( e ) {
            e.preventDefault();

            var confirmationElement   = $("#erp_reset_confirmation"),
                toolsSubmitButton     = $('.tools-btn-submit'),
                toolsLoadingButton    = $('.tools-btn-loading'),
                toolsErrorMessage     = $(".tools-error-message"),
                resetConfirmationText = confirmationElement.val(),
                isValidConfirmation   = ( typeof resetConfirmationText !== 'undefined' ) && ( String( resetConfirmationText ).length !== 0 ) && ( resetConfirmationText === 'Reset' );

            if ( ! isValidConfirmation ) {
                toolsErrorMessage.html( wpErpDangerZone.confirmResetBeforeContinue );
                confirmationElement.addClass('tools-error-input');
            } else {
                toolsErrorMessage.html('');
                confirmationElement.removeClass('tools-error-input');

                swal({
                    title             : wpErpDangerZone.resetErp,
                    text              : wpErpDangerZone.areYouSureReset,
                    showCancelButton  : true,
                    confirmButtonText : wpErpDangerZone.yesResetIt,
                    confirmButtonColor: "#FF4848",
                    imageUrl          : wpErpDangerZone.trashIcon
                },
                function() {
                    toolsSubmitButton.addClass('tools-submit-hidden');
                    toolsLoadingButton.removeClass('tools-submit-hidden');

                    $.ajax({
                        type    : "POST",
                        url     : ajaxurl,
                        dataType: 'json',
                        data    : $("#danger-zone-form").serialize()
                    })
                    .fail(function(xhr) {
                        swal('', wpErpDangerZone.somethingWrong, 'error');
                        toolsSubmitButton.removeClass('tools-submit-hidden');
                        toolsLoadingButton.addClass('tools-submit-hidden');
                    })
                    .done(function(response) {
                        if(response.success) {
                            toolsSubmitButton.removeClass('tools-submit-hidden');
                            toolsLoadingButton.addClass('tools-submit-hidden');

                            swal({
                                title: '',
                                text : response.data.message,
                                time : 3000,
                                type : 'success',
                                showConfirmButton: false,
                            });

                            setTimeout(function() {
                                window.location.href = response.data.redirected_url;
                            }, 1000);
                        } else {
                            toolsSubmitButton.removeClass('tools-submit-hidden');
                            toolsLoadingButton.addClass('tools-submit-hidden');
                            swal('', response.data, 'error');
                        }
                    });
                });
            }
        },

        /**
         * On change danger zone input
         *
         * @param object event
         */
        onChangeDangerZoneInput: function( e ) {
            var confirmationElement   = $("#erp_reset_confirmation"),
                resetConfirmationText = confirmationElement.val(),
                isValidConfirmation   = ( typeof resetConfirmationText !== 'undefined' ) && ( String( resetConfirmationText ).length !== 0 ) && ( resetConfirmationText === 'Reset' );

            if ( isValidConfirmation ) {
                $(".tools-error-message").html('');
                confirmationElement.removeClass('tools-error-input');
                confirmationElement.addClass('tools-success-input');
            } else {
                confirmationElement.removeClass('tools-success-input');
                $(".tools-error-message").html( wpErpDangerZone.confirmResetBeforeContinue );
                confirmationElement.addClass('tools-error-input');
            }
        },

        /**
         * Close notice message on click on 'x' button
         */
         dismissNoticeMessage: function( e ) {
             e.preventDefault();
             e.target.closest( 'div' ).remove();
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

// erpDropdown forked version of bootstrap dropdown
+function ($) {
    'use strict';

    // DROPDOWN CLASS DEFINITION
    // =========================

    var backdrop = '.erp-dropdown-backdrop'
    var toggle   = '[data-toggle="erp-dropdown"]'
    var ERPDropdown = function (element) {
        $(element).on('click.erp.dropdown', this.toggle)
    }

    ERPDropdown.VERSION = '1.0.0' // bootstrap dropdown 3.3.7

    function getParent($this) {
        var selector = $this.attr('data-target')

        if (!selector) {
            selector = $this.attr('href')
            selector = selector && /#[A-Za-z]/.test(selector) && selector.replace(/.*(?=#[^\s]*$)/, '') // strip for ie7
        }

        var $parent = selector && $(selector)

        return $parent && $parent.length ? $parent : $this.parent()
    }

    function clearMenus(e) {
        if (e && e.which === 3) return
        $(backdrop).remove()
        $(toggle).each(function () {
            var $this         = $(this)
            var $parent       = getParent($this)
            var relatedTarget = { relatedTarget: this }

            if (!$parent.hasClass('open')) return

            if (e && e.type == 'click' && /input|textarea/i.test(e.target.tagName) && $.contains($parent[0], e.target)) return

            $parent.trigger(e = $.Event('hide.erp.dropdown', relatedTarget))

            if (e.isDefaultPrevented()) return

            $this.attr('aria-expanded', 'false')
            $parent.removeClass('open').trigger($.Event('hidden.erp.dropdown', relatedTarget))
        })
    }

    ERPDropdown.prototype.toggle = function (e) {
        var $this = $(this)

        if ($this.is('.disabled, :disabled')) return

        var $parent  = getParent($this)
        var isActive = $parent.hasClass('open')

        clearMenus()

        if (!isActive) {
            if ('ontouchstart' in document.documentElement && !$parent.closest('.navbar-nav').length) {
                // if mobile we use a backdrop because click events don't delegate
                $(document.createElement('div'))
                    .addClass('erp-dropdown-backdrop')
                    .insertAfter($(this))
                    .on('click', clearMenus)
            }

            var relatedTarget = { relatedTarget: this }
            $parent.trigger(e = $.Event('show.erp.dropdown', relatedTarget))

            if (e.isDefaultPrevented()) return

            $this
                .trigger('focus')
                .attr('aria-expanded', 'true')

            $parent
                .toggleClass('open')
                .trigger($.Event('shown.erp.dropdown', relatedTarget))
        }

        return false
    }

    ERPDropdown.prototype.keydown = function (e) {
        if (!/(38|40|27|32)/.test(e.which) || /input|textarea/i.test(e.target.tagName)) return

        var $this = $(this)

        e.preventDefault()
        e.stopPropagation()

        if ($this.is('.disabled, :disabled')) return

        var $parent  = getParent($this)
        var isActive = $parent.hasClass('open')

        if (!isActive && e.which != 27 || isActive && e.which == 27) {
            if (e.which == 27) $parent.find(toggle).trigger('focus')
            return $this.trigger('click')
        }

        var desc = ' li:not(.disabled):visible a'
        var $items = $parent.find('.erp-dropdown-menu' + desc)

        if (!$items.length) return

        var index = $items.index(e.target)

        if (e.which == 38 && index > 0)                 index--         // up
        if (e.which == 40 && index < $items.length - 1) index++         // down
        if (!~index)                                    index = 0

        $items.eq(index).trigger('focus')
    }


    // DROPDOWN PLUGIN DEFINITION
    // ==========================

    function Plugin(option) {
        return this.each(function () {
            var $this = $(this)
            var data  = $this.data('erp.dropdown')

            if (!data) $this.data('erp.dropdown', (data = new ERPDropdown(this)))
            if (typeof option == 'string') data[option].call($this)
        })
    }

    var old = $.fn.erpDropdown

    $.fn.erpDropdown             = Plugin
    $.fn.erpDropdown.Constructor = ERPDropdown


    // DROPDOWN NO CONFLICT
    // ====================

    $.fn.erpDropdown.noConflict = function () {
        $.fn.erpDropdown = old
        return this
    }


    // APPLY TO STANDARD DROPDOWN ELEMENTS
    // ===================================

    $(document)
        .on('click.erp.dropdown.data-api', clearMenus)
        .on('click.erp.dropdown.data-api', '.erp-dropdown form', function (e) { e.stopPropagation() })
        .on('click.erp.dropdown.data-api', toggle, ERPDropdown.prototype.toggle)
        .on('keydown.erp.dropdown.data-api', toggle, ERPDropdown.prototype.keydown)
        .on('keydown.erp.dropdown.data-api', '.erp-dropdown-menu', ERPDropdown.prototype.keydown)


    // Dropdown Outside Click Detect and Hide Filter Dropdown
    // ======================================================

    window.addEventListener( 'click', function(e) {
        var dropdownArea             = document.querySelectorAll( '.wperp-filter-dropdown' )[0];
        var select2Container         = document.querySelector( '.select2-container--open' );
        var dateRangePicker          = document.querySelectorAll( '.daterangepicker' )[0];
        var targetNotContainDropdown = typeof dropdownArea !== 'undefined' && ! dropdownArea.contains( e.target );
        var clickOnDrPicker          = typeof dateRangePicker !== 'undefined' && dateRangePicker.contains( e.target );

        if ( targetNotContainDropdown && select2Container === null && ! clickOnDrPicker ){
            document.getElementById( 'erp-dropdown-content' ).classList.remove( 'show' );
        }
    });

}(jQuery);
