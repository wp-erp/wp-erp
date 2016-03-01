/* jshint devel:true */
/* global wpErpHr */
/* global wp */

;(function($) {
    'use strict';

    var WeDevs_ERP_HR = {

        /**
         * Initialize the events
         *
         * @return {void}
         */
        initialize: function() {
            // Dasboard Overview
            $( 'ul.erp-dashboard-announcement' ).on( 'click', 'a.mark-read', this.dashboard.markAnnouncementRead );
            $( 'ul.erp-dashboard-announcement' ).on( 'click', 'a.view-full', this.dashboard.viewAnnouncement );

            // Department
            $( 'body' ).on( 'click', 'a#erp-new-dept', this.department.create );
            $( '.erp-hr-depts' ).on( 'click', 'a.submitdelete', this.department.remove );
            $( '.erp-hr-depts' ).on( 'click', 'span.edit a', this.department.edit );

            // Designation
            $( 'body' ).on( 'click', 'a#erp-new-designation', this.designation.create );
            $( '.erp-hr-designation' ).on( 'click', 'a.submitdelete', this.designation.remove );
            $( '.erp-hr-designation' ).on( 'click', 'span.edit a', this.designation.edit );

            // employee
            $( '.erp-hr-employees' ).on( 'click', 'a#erp-employee-new', this.employee.create );
            $( '.erp-hr-employees' ).on( 'click', 'span.edit a', this.employee.edit );
            $( '.erp-hr-employees' ).on( 'click', 'a.submitdelete', this.employee.remove );
            $( '.erp-hr-employees' ).on( 'click', 'a#erp-empl-status', this.employee.updateJobStatus );
            $( '.erp-hr-employees' ).on( 'click', 'a#erp-empl-compensation', this.employee.updateJobStatus );
            $( '.erp-hr-employees' ).on( 'click', 'a#erp-empl-jobinfo', this.employee.updateJobStatus );
            $( '.erp-hr-employees' ).on( 'click', 'td.action a.remove', this.employee.removeHistory );
            $( '.erp-hr-employees' ).on( 'click', 'a#erp-employee-print', this.employee.printData );

            // Single Employee
            $( '.erp-employee-single' ).on( 'click', 'a#erp-employee-terminate', this.employee.terminateEmployee );
            $( '.erp-employee-single' ).on( 'click', 'a#erp-employee-activate', this.employee.activateEmployee );

            // Performance
            $( '.erp-hr-employees' ).on( 'click', 'a#erp-empl-performance-reviews', this.employee.updatePerformance );
            $( '.erp-hr-employees' ).on( 'click', 'a#erp-empl-performance-comments', this.employee.updatePerformance );
            $( '.erp-hr-employees' ).on( 'click', 'a#erp-empl-performance-goals', this.employee.updatePerformance );
            $( '.erp-hr-employees' ).on( 'click', '.performance-tab-wrap td.action a.performance-remove', this.employee.removePerformance );
            // work experience
            $( '.erp-hr-employees' ).on( 'click', 'a#erp-empl-add-exp', this.employee.general.create );
            $( '.erp-hr-employees' ).on( 'click', 'a.work-experience-edit', this.employee.general.create );
            $( '.erp-hr-employees' ).on( 'click', 'a.work-experience-delete', this.employee.general.remove );

            // education
            $( '.erp-hr-employees' ).on( 'click', 'a#erp-empl-add-education', this.employee.general.create );
            $( '.erp-hr-employees' ).on( 'click', 'a.education-edit', this.employee.general.create );
            $( '.erp-hr-employees' ).on( 'click', 'a.education-delete', this.employee.general.remove );

            // dependent
            $( '.erp-hr-employees' ).on( 'click', 'a#erp-empl-add-dependent', this.employee.general.create );
            $( '.erp-hr-employees' ).on( 'click', 'a.dependent-edit', this.employee.general.create );
            $( '.erp-hr-employees' ).on( 'click', 'a.dependent-delete', this.employee.general.remove );

            // notes
            $( '.erp-hr-employees' ).on( 'submit', '.note-tab-wrap form', this.employee.addNote );
            $( '.erp-hr-employees' ).on( 'click', '.note-tab-wrap input#erp-load-notes', this.employee.loadNotes );
            $( '.erp-hr-employees' ).on( 'click', '.note-tab-wrap a.delete_note', this.employee.deleteNote );

            // photos
            $( 'body' ).on( 'click', 'a#erp-set-emp-photo', this.employee.setPhoto );
            $( 'body' ).on( 'click', 'a.erp-remove-photo', this.employee.removePhoto );

            // Trigger
            $('body').on( 'erp-hr-after-new-dept', this.department.afterNew );
            $('body').on( 'erp-hr-after-new-desig', this.designation.afterNew );

            this.initTipTip();

            // this.employee.addWorkExperience();
        },

        initTipTip: function() {
            $( '.erp-tips' ).tipTip( {
                defaultPosition: "top",
                fadeIn: 100,
                fadeOut: 100
            } );
        },

        initDateField: function() {
            $( '.erp-date-field').datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                yearRange: '-100:+0',
            });
        },

        reloadPage: function() {
            $( '.erp-area-left' ).load( window.location.href + ' #erp-area-left-inner' );
        },

        dashboard : {
            markAnnouncementRead: function(e) {
                e.preventDefault();
                var self = $(this);

                if ( ! self.closest( 'li' ).hasClass('unread') ) {
                    return;
                }

                wp.ajax.send( 'erp_hr_announcement_mark_read', {
                    data: {
                        id : self.data( 'row_id' ),
                        _wpnonce: wpErpHr.nonce
                    },
                    success: function(res) {
                        self.closest( 'li' ).removeClass( 'unread' );
                    },
                    error: function(error) {
                        alert( error );
                    }
                });
            },

            viewAnnouncement: function(e) {
                e.preventDefault();
                var self = $(this);

                wp.ajax.send( 'erp_hr_announcement_view', {
                    data: {
                        id : self.data( 'row_id' ),
                        _wpnonce: wpErpHr.nonce
                    },
                    success: function(res) {
                        $.erpPopup({
                            title: res.title,
                            button: '',
                            id: 'erp-hr-announcement',
                            content: '<p>'+ res.content +'</p>',
                            extraClass: 'midium',
                        });
                    },
                    error: function(error) {
                        alert( error );
                    }
                });
            }
        },

        department: {

            /**
             * After create new department
             *
             * @return {void}
             */
            afterNew: function( e, res ) {
                var selectdrop = $('.erp-hr-dept-drop-down');
                wperp.scriptReload( 'erp_hr_script_reload', 'tmpl-erp-new-employee' );
                selectdrop.append('<option selected="selected" value="'+res.id+'">'+res.title+'</option>');
                selectdrop.select2().select2("val", res.id);
            },

            /**
             * Reload the department area
             *
             * @return {void}
             */
            reload: function() {
                $( '#erp-dept-table-wrap' ).load( window.location.href + ' #erp-dept-table-wrap' );
            },

            /**
             * Template reload after insert, edit, delete
             *
             * @return {void}
             */
            tempReload: function() {
                wperp.scriptReload( 'erp_hr_new_dept_tmp_reload', 'tmpl-erp-new-dept' );
            },

            /**
             * Create new department
             *
             * @param  {event}
             */
            create: function(e) {
                e.preventDefault();
                var self = $(this),
                    is_single = self.data('single');

                $.erpPopup({
                    title: wpErpHr.popup.dept_title,
                    button: wpErpHr.popup.dept_submit,
                    id: 'erp-hr-new-department',
                    content: wperp.template('erp-new-dept')().trim(),
                    extraClass: 'smaller',
                    onSubmit: function(modal) {
                        wp.ajax.send( {
                            data: this.serialize(),
                            success: function(res) {
                                WeDevs_ERP_HR.department.reload();

                                if ( is_single != '1' ) {
                                    $('body').trigger( 'erp-hr-after-new-dept', [res]);
                                } else {
                                    WeDevs_ERP_HR.department.tempReload();
                                }

                                modal.closeModal();
                            },
                            error: function(error) {
                                alert( error );
                            }
                        });
                    }
                }); //popup
            },

            /**
             * Edit a department in popup
             *
             * @param  {event}
             */
            edit: function(e) {
                e.preventDefault();

                var self = $(this);

                $.erpPopup({
                    title: wpErpHr.popup.dept_update,
                    button: wpErpHr.popup.dept_update,
                    id: 'erp-hr-new-department',
                    content: wp.template('erp-new-dept')().trim(),
                    extraClass: 'smaller',
                    onReady: function() {
                        var modal = this;

                        $( 'header', modal).after( $('<div class="loader"></div>').show() );

                        wp.ajax.send( 'erp-hr-get-dept', {
                            data: {
                                id: self.data('id'),
                                _wpnonce: wpErpHr.nonce
                            },
                            success: function(response) {
                                $( '.loader', modal).remove();

                                $('#dept-title', modal).val( response.name );
                                $('#dept-desc', modal).val( response.data.description );
                                $('#dept-parent', modal).val( response.data.parent );
                                $('#dept-lead', modal).val( response.data.lead );
                                $('#dept-id', modal).val( response.id );
                                $('#dept-action', modal).val( 'erp-hr-update-dept' );

                                // disable current one
                                $('#dept-parent option[value="' + self.data('id') + '"]', modal).attr( 'disabled', 'disabled' );

                            }
                        });
                    },
                    onSubmit: function(modal) {
                        wp.ajax.send( {
                            data: this.serialize(),
                            success: function() {
                                WeDevs_ERP_HR.department.reload();
                                WeDevs_ERP_HR.department.tempReload();
                                modal.closeModal();
                            },
                            error: function(error) {
                                alert( error );
                            }
                        });
                    }
                });
            },

            /**
             * Delete a department
             *
             * @param  {event}
             */
            remove: function(e) {
                e.preventDefault();

                var self = $(this);

                if ( confirm( wpErpHr.delConfirmDept ) ) {
                    wp.ajax.send( 'erp-hr-del-dept', {
                        data: {
                            '_wpnonce': wpErpHr.nonce,
                            id: self.data( 'id' )
                        },
                        success: function() {
                            self.closest('tr').fadeOut( 'fast', function() {
                                $(this).remove();
                                WeDevs_ERP_HR.department.tempReload();
                            });
                        },
                        error: function(response) {
                            alert( response );
                        }
                    });
                }
            },

        },

        designation: {

            /**
             * After create new desination
             *
             * @return {void}
             */
            afterNew: function( e, res ) {
                var selectdrop = $('.erp-hr-desi-drop-down');

                wperp.scriptReload( 'erp_hr_script_reload', 'tmpl-erp-new-employee' );
                selectdrop.append('<option selected="selected" value="'+res.id+'">'+res.title+'</option>');
                WeDevs_ERP_HR.employee.select2AddMoreActive('erp-hr-desi-drop-down');
                selectdrop.select2("val", res.id);
            },

            /**
             * Reload the department area
             *
             * @return {void}
             */
            reload: function() {
                $( '.erp-hr-designation' ).load( window.location.href + ' .erp-hr-designation' );
            },

            /**
             * Create designation
             *
             * @param  {event}
             *
             * @return {void}
             */
            create: function(e) {
                e.preventDefault();
                var is_single = $(this).data('single');
                $.erpPopup({
                    title: wpErpHr.popup.desig_title,
                    button: wpErpHr.popup.desig_submit,
                    id: 'erp-hr-new-designation',
                    content: wp.template( 'erp-new-desig' )().trim(),
                    extraClass: 'smaller',
                    onSubmit: function(modal) {
                        wp.ajax.send( {
                            data: this.serialize(),
                            success: function(res) {
                                WeDevs_ERP_HR.designation.reload();
                                if ( is_single != '1' ) {
                                    $('body').trigger( 'erp-hr-after-new-desig', [res] );
                                }
                                modal.closeModal();
                            },
                            error: function(error) {
                                alert( error );
                            }
                        });
                    }
                });
            },

            /**
             * Edit a department in popup
             *
             * @param  {event}
             */
            edit: function(e) {
                e.preventDefault();

                var self = $(this);

                $.erpPopup({
                    title: wpErpHr.popup.desig_update,
                    button: wpErpHr.popup.desig_update,
                    content: wp.template( 'erp-new-desig' )().trim(),
                    id: 'erp-hr-new-designation',
                    extraClass: 'smaller',
                    onReady: function() {
                        var modal = this;

                        $( 'header', modal).after( $('<div class="loader"></div>').show() );

                        wp.ajax.send( 'erp-hr-get-desig', {
                            data: {
                                id: self.data('id'),
                                _wpnonce: wpErpHr.nonce
                            },
                            success: function(response) {
                                $( '.loader', modal).remove();

                                $('#desig-title', modal).val( response.name );
                                $('#desig-desc', modal).val( response.data.description );
                                $('#desig-id', modal).val( response.id );
                                $('#desig-action', modal).val( 'erp-hr-update-desig' );
                            }
                        });
                    },
                    onSubmit: function(modal) {
                        wp.ajax.send( {
                            data: this.serialize(),
                            success: function() {
                                WeDevs_ERP_HR.designation.reload();

                                modal.closeModal();
                            },
                            error: function(error) {
                                alert( error );
                            }
                        });
                    }
                });
            },

            /**
             * Delete a department
             *
             * @param  {event}
             */
            remove: function(e) {
                e.preventDefault();

                var self = $(this);

                if ( confirm( wpErpHr.delConfirmDept ) ) {
                    wp.ajax.send( 'erp-hr-del-desig', {
                        data: {
                            '_wpnonce': wpErpHr.nonce,
                            id: self.data( 'id' )
                        },
                        success: function() {
                            self.closest('tr').fadeOut( 'fast', function() {
                                $(this).remove();
                            });
                        },
                        error: function(response) {
                            alert( response );
                        }
                    });
                }
            },
        },

        employee: {

            /**
             * Reload the department area
             *
             * @return {void}
             */
            reload: function() {
                $( '.erp-hr-employees' ).load( window.location.href + ' .erp-hr-employees' );
            },

            /**
             * Set photo popup
             *
             * @param {event}
             */
            setPhoto: function(e) {
                e.preventDefault();
                e.stopPropagation();

                var frame;

                if ( frame ) {
                    frame.open();
                    return;
                }

                frame = wp.media({
                    title: wpErpHr.emp_upload_photo,
                    button: { text: wpErpHr.emp_set_photo }
                });

                frame.on('select', function() {
                    var selection = frame.state().get('selection');

                    selection.map( function( attachment ) {
                        attachment = attachment.toJSON();

                        var html = '<img src="' + attachment.url + '" alt="" />';
                            html += '<input type="hidden" id="emp-photo-id" name="personal[photo_id]" value="' + attachment.id + '" />';
                            html += '<a href="#" class="erp-remove-photo">&times;</a>';

                        $( '.photo-container', '.erp-employee-form' ).html( html );
                    });
                });

                frame.open();
            },

            /**
             * Remove an employees avatar
             *
             * @param  {event}
             */
            removePhoto: function(e) {
                e.preventDefault();

                var html = '<a href="#" id="erp-set-emp-photo" class="button button-small">' + wpErpHr.emp_upload_photo + '</a>';
                    html += '<input type="hidden" name="personal[photo_id]" id="emp-photo-id" value="0">';

                $( '.photo-container', '.erp-employee-form' ).html( html );
            },

            /**
             * Create a new employee modal
             *
             * @param  {event}
             */
            create: function(e) {
                if ( typeof e !== 'undefined' ) {
                    e.preventDefault();
                }

                if ( typeof wpErpHr.employee_empty === 'undefined' ) {
                    return;
                }

                $.erpPopup({
                    title: wpErpHr.popup.employee_title,
                    button: wpErpHr.popup.employee_create,
                    id: "erp-new-employee-popup",
                    content: wperp.template('erp-new-employee')( wpErpHr.employee_empty ).trim(),

                    onReady: function() {
                        WeDevs_ERP_HR.initDateField();
                        WeDevs_ERP_HR.employee.select2Action('erp-hrm-select2');
                        WeDevs_ERP_HR.employee.select2AddMoreContent();
                    },

                    /**
                     * Handle the onsubmit function
                     *
                     * @param  {modal}
                     */
                    onSubmit: function(modal) {
                        $( 'button[type=submit]', '.erp-modal' ).attr( 'disabled', 'disabled' );

                        wp.ajax.send( 'erp-hr-employee-new', {
                            data: this.serialize(),
                            success: function(response) {
                                WeDevs_ERP_HR.employee.reload();
                                modal.enableButton();
                                modal.closeModal();
                            },
                            error: function(error) {
                                modal.enableButton();
                                alert( error );
                            }
                        });
                    }
                });
            },

            /**
             * select2 with add more button content
             *
             * @return  {void}
             */
            select2AddMoreContent: function() {
                var selects = $('.erp-hrm-select2-add-more');
                $.each( selects, function( key, element ) {
                   WeDevs_ERP_HR.employee.select2AddMoreActive(element);
                });
            },

            /**
             * select2 with add more button active
             *
             * @return  {void}
             */
            select2AddMoreActive: function(element) {
                var id = $(element).data('id');
                $(element).select2({
                    width: 'element',
                    "language": {
                        noResults: function(){
                           return '<a href="#" class="button button-primary" id="'+id+'">Add New</a>';
                        }
                    },
                    escapeMarkup: function (markup) {
                        return markup;
                    }

                });
            },

            /**
             * select2 action
             *
             * @return  {void}
             */
            select2Action: function(element) {
                $('.'+element).select2({
                    width: 'element',
                });
            },

            /**
             * Edit an employee
             *
             * @param  {event}
             */
            edit: function(e) {
                e.preventDefault();

                var self = $(this);

                $.erpPopup({
                    title: wpErpHr.popup.employee_update,
                    button: wpErpHr.popup.employee_update,
                    id: 'erp-employee-edit',
                    onReady: function() {
                        var modal = this;
                        
                        $( 'header', modal).after( $('<div class="loader"></div>').show() );

                        wp.ajax.send( 'erp-hr-emp-get', {
                            data: {
                                id: self.data('id'),
                                _wpnonce: wpErpHr.nonce
                            },
                            success: function(response) {
                                var html = wp.template('erp-new-employee')( response );
                                $( '.content', modal ).html( html );
                                $( '.loader', modal).remove();

                                WeDevs_ERP_HR.initDateField();

                                $( 'li[data-selected]', modal ).each(function() {
                                    var self = $(this),
                                        selected = self.data('selected');

                                    if ( selected !== '' ) {
                                        self.find( 'select' ).val( selected );
                                    }
                                });

                                // disable current one
                                $('#work_reporting_to option[value="' + response.id + '"]', modal).attr( 'disabled', 'disabled' );
                            }
                        });
                    },
                    onSubmit: function(modal) {
                        modal.disableButton();

                        wp.ajax.send( {
                            data: this.serialize(),
                            success: function(response) {
                                WeDevs_ERP_HR.employee.reload();
                                modal.enableButton();
                                modal.closeModal();
                            },
                            error: function(error) {
                                modal.enableButton();
                                alert( error );
                            }
                        });
                    }
                });
            },

            /**
             * Remove an employee
             *
             * @param  {event}
             */
            remove: function(e) {
                e.preventDefault();

                var self = $(this);

                if ( confirm( wpErpHr.delConfirmEmployee ) ) {
                    wp.ajax.send( 'erp-hr-emp-delete', {
                        data: {
                            '_wpnonce': wpErpHr.nonce,
                            id: self.data( 'id' ),
                            hard: self.data( 'hard' )
                        },
                        success: function() {
                            self.closest('tr').fadeOut( 'fast', function() {
                                $(this).remove();
                                WeDevs_ERP_HR.employee.reload();
                            });
                        },
                        error: function(response) {
                            alert( response );
                        }
                    });
                }
            },

            general: {

                create: function(e) {
                    if ( typeof e !== 'undefined' ) {
                        e.preventDefault();
                    }

                    var self = $(this);

                    $.erpPopup({
                        title: self.data('title'),
                        content: wp.template( self.data('template' ) )( self.data('data') ),
                        extraClass: 'smaller',
                        id: 'erp-hr-new-general',
                        button: self.data('button'),
                        onReady: function() {
                            WeDevs_ERP_HR.initDateField();
                        },
                        onSubmit: function(modal) {
                            wp.ajax.send( {
                                data: this.serializeObject(),
                                success: function() {
                                    WeDevs_ERP_HR.reloadPage();
                                    modal.closeModal();
                                },
                                error: function(error) {
                                    modal.enableButton();
                                    alert( error );
                                }
                            });
                        }
                    });
                },

                remove: function(e) {
                    e.preventDefault();

                    var self = $(this);

                    if ( confirm( wpErpHr.confirm ) ) {
                        wp.ajax.send( self.data('action'), {
                            data: {
                                id: self.data('id'),
                                _wpnonce: wpErpHr.nonce
                            },
                            success: function() {
                                WeDevs_ERP_HR.reloadPage();
                            }
                        });
                    }
                },
            },

            updateJobStatus: function(e) {
                if ( typeof e !== 'undefined' ) {
                    e.preventDefault();
                }

                var self = $(this);

                $.erpPopup({
                    title: self.data('title'),
                    button: wpErpHr.popup.update_status,
                    id: 'erp-hr-update-job-status',
                    content: '',
                    extraClass: 'smaller',
                    onReady: function() {
                        var html = wp.template( self.data('template') )(window.wpErpCurrentEmployee);
                        $( '.content', this ).html( html );
                        WeDevs_ERP_HR.initDateField();

                        $( '.row[data-selected]', this ).each(function() {
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
                                WeDevs_ERP_HR.reloadPage();
                                modal.closeModal();
                            },
                            error: function(error) {
                                modal.enableButton();
                                alert( error );
                            }
                        });
                    }
                });
            },

            removeHistory: function(e) {
                e.preventDefault();

                if ( confirm( wpErpHr.confirm ) ) {
                    wp.ajax.send( 'erp-hr-emp-delete-history', {
                        data: {
                            id: $(this).data('id'),
                            _wpnonce: wpErpHr.nonce
                        },
                        success: function() {
                            WeDevs_ERP_HR.reloadPage();
                        }
                    });
                }
            },

            printData: function(e) {
                e.preventDefault();
                window.print();
            },

            addNote: function(e) {
                e.preventDefault();

                var form = $(this),
                    submit = form.find( 'input[type=submit]');

                submit.attr('disabled', 'disabled');

                wp.ajax.send({
                    data: form.serializeObject(),
                    success: function() {
                        $.get( window.location.href, function(data) {
                            $('ul.notes-list').prepend( $(data).find( 'ul.notes-list li' ).first() );
                            $('ul.notes-list li').last().remove();
                            form.find('textarea').val('');
                            submit.removeAttr( 'disabled' );
                        });

                    },
                    error: function() {
                        submit.removeAttr('disabled');
                    }
                });
            },

            loadNotes: function(e) {
                e.preventDefault();

                var self = $(this),
                    data = {
                        action : 'erp-load-more-notes',
                        user_id : self.data('user_id'),
                        total_no : self.data('total_no'),
                        offset_no : self.data('offset_no')
                    };

                var spiner = '<span class="erp-loader" style="margin:4px 0px 0px 10px"></span>';

                self.closest('p')
                    .append( spiner )
                    .find('.erp-loader')
                    .show();

                self.attr( 'disabled', true );

                wp.ajax.send({
                    data: data,
                    success: function( resp ) {
                        self.data( 'offset_no', parseInt(data.total_no)+parseInt(data.offset_no) );
                        $(resp.content).appendTo(self.closest('.note-tab-wrap').find('ul.notes-list')).hide().fadeIn();
                        self.removeAttr( 'disabled' );
                        $('.erp-loader').remove();
                    },
                    error: function( error ) {
                        alert(error);
                    }
                });
            },

            deleteNote: function(e) {
                e.preventDefault();

                if ( confirm( wpErpHr.delConfirmEmployeeNote ) ) {

                    var self = $(this),
                        data = {
                            action: 'erp-delete-employee-note',
                            note_id: self.data('note_id'),
                            _wpnonce : wpErpHr.nonce
                        };

                    wp.ajax.send({
                        data: data,
                        success: function( resp ) {
                            self.closest('li').fadeOut( 400, function() {
                                $(this).remove();
                            });
                        },
                        error: function( error ) {
                            alert(error);
                        }
                    });
                }
            },

            updatePerformance: function(e) {

                if ( typeof e !== 'undefined' ) {
                    e.preventDefault();
                }

                var self = $(this);

                $.erpPopup({
                    title: self.data('title'),
                    button: wpErpHr.popup.update_status,
                    id: 'erp-hr-update-performance',
                    content: '',
                    extraClass: 'smaller',
                    onReady: function() {
                        var html = wp.template( self.data('template') )(window.wpErpCurrentEmployee);
                        $( '.content', this ).html( html );
                        WeDevs_ERP_HR.initDateField();
                        WeDevs_ERP_HR.employee.select2Action('erp-hrm-select2');

                        $( '.row[data-selected]', this ).each(function() {
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
                                WeDevs_ERP_HR.reloadPage();
                                modal.closeModal();
                            },
                            error: function(error) {
                                modal.enableButton();
                                alert( error );
                            }
                        });
                    }
                });
            },

            removePerformance: function(e) {
                e.preventDefault();

                if ( confirm( wpErpHr.confirm ) ) {
                    wp.ajax.send({
                        data: {
                            action: 'erp-hr-emp-delete-performance',
                            id: $(this).data('id'),
                            _wpnonce: wpErpHr.nonce
                        },
                        success: function() {
                            WeDevs_ERP_HR.reloadPage();
                        }
                    });
                }
            },

            terminateEmployee: function(e) {

                if ( typeof e !== 'undefined' ) {
                    e.preventDefault();
                }

                var self = $(this);

                if ( self.data('data') ) {
                    var terminateData = self.data('data');
                } else {
                    var terminateData = window.wpErpCurrentEmployee;
                }

                $.erpPopup({
                    title: self.data('title'),
                    button: wpErpHr.popup.terminate,
                    id: 'erp-hr-employee-terminate',
                    content: '',
                    extraClass: 'smaller',
                    onReady: function() {
                        var html = wp.template( self.data('template') )( terminateData );
                        $( '.content', this ).html( html );
                        WeDevs_ERP_HR.initDateField();

                        $( '.row[data-selected]', this ).each(function() {
                            var self = $(this),
                                selected = self.data('selected');

                            if ( selected !== '' ) {
                                self.find( 'select' ).val( selected );
                            }
                        });

                        WeDevs_ERP_HR.employee.select2Action('erp-hrm-select2');
                    },
                    onSubmit: function(modal) {
                        wp.ajax.send( {
                            data: this.serializeObject(),
                            success: function() {
                                WeDevs_ERP_HR.reloadPage();
                                modal.closeModal();
                            },
                            error: function(error) {
                                modal.enableButton();
                                alert( error );
                            }
                        });
                    }
                });

            },

            activateEmployee: function(e) {
                e.preventDefault();

                if ( confirm( wpErpHr.confirm ) ) {
                    wp.ajax.send({
                        data: {
                            action: 'erp-hr-emp-activate',
                            id: $(this).data('id'),
                            _wpnonce: wpErpHr.nonce
                        },
                        success: function() {
                            WeDevs_ERP_HR.reloadPage();
                        }
                    });
                }
            }

        }
    };

    $(function() {
        WeDevs_ERP_HR.initialize();
    });
})(jQuery);