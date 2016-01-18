// jQuery(document).on('ready', function() {
//     console.log( jQuery( '.erptips' ).length );
// });

// Vue Filter for Formatting Time
Vue.filter('formatAMPM', function (date) {
    date = new Date( date );
    var hours = date.getHours();
    var minutes = date.getMinutes();
    var ampm = hours >= 12 ? 'pm' : 'am';
    hours = hours % 12;
    hours = hours ? hours : 12; // the hour '0' should be '12'
    minutes = minutes < 10 ? '0'+minutes : minutes;
    var strTime = hours + ':' + minutes + ' ' + ampm;
    return strTime;
});

// Vue Filter for formatting date
Vue.filter('formatDate', function ( date, format ) {
    date = new Date( date );
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

    if ( format ) {
        dateStr = format.replace(/Y|m|d|j|M|F/gi, function( matched ){
            return pattern[matched];
        });
    } else {
        dateStr = wpCRMvue.date_format.replace(/Y|m|d|j|M|F/gi, function( matched ){
            return pattern[matched];
        });
    }

    return dateStr;
});

// Vue filter for formatting Feeds message body as a group by object
Vue.filter( 'formatFeedContent', function ( message, feed ) {

    if ( feed.type == 'email' ) {
        message = '<div class="timeline-email-subject">Subject : ' + feed.email_subject + '</div>' +
                  '<div class="timeline-email-body">' + feed.message + '</div>';
    };

    if ( feed.type == 'log_activity' && this.isSchedule( feed.start_date ) ) {
        var filters = this.$options.filters,
            startDate = filters.formatDate( feed.start_date, 'j F' ),
            startTime = filters.formatAMPM( feed.start_date ),
            endDate = filters.formatDate( feed.end_date, 'j F' ),
            endTime = filters.formatAMPM( feed.end_date );


        if ( feed.extra.all_day == 'true' ) {
            var datetime = startDate + ' to ' + endDate;
        } else {
            if ( filters.formatDate( feed.start_date, 'Y-m-d' ) == filters.formatDate( feed.end_date, 'Y-m-d' ) ) {
                var datetime = startDate + ' at ' + startTime + ' to ' + endTime;
            } else {
                var datetime = startDate + ' at ' + startTime + ' to ' + endDate + ' at ' + endTime;
            }
        }

        message = '<div class="timeline-email-subject"><i class="fa fa-bookmark"></i> &nbsp;' + feed.extra.schedule_title + '  &nbsp;|&nbsp;  <i class="fa fa-calendar-check-o"></i> &nbsp;' + datetime + '</div>' +
            '<div class="timeline-email-body">' + feed.message + '</div>';
    };

    return message;
});


// Vue filter for formatting Feeds as a group by object
Vue.filter('formatFeeds', function ( feeds ) {
    var feedsData = _.groupBy( feeds, function( data ) {
        return data.created_timeline_date;
    });

    return feedsData;
});

// Vue filter for formatting Feeds as a group by object
Vue.filter('formatDateTime', function ( date ) {
    return this.$options.filters.formatDate( date, 'F, j' ) + ' at ' + this.$options.filters.formatAMPM( date )
});


// Vue directive for Date picker
Vue.directive( 'datepicker', {
    params: ['datedisable'],

    bind: function () {
        var vm = this.vm;
        var key = this.expression;

        if ( this.params.datedisable == 'previous' ) {
            jQuery(this.el).datepicker({
                minDate: 0,
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                yearRange: '-100:+0',
                onSelect: function (date) {
                    vm.$set(key, date);
                }
            });
        } else if ( this.params.datedisable == 'upcomming' ) {
            jQuery(this.el).datepicker({
                maxDate: 0,
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                yearRange: '-100:+0',
                onSelect: function (date) {
                    vm.$set(key, date);
                }
            });
        } else {
            jQuery(this.el).datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                yearRange: '-100:+0',
                onSelect: function (date) {
                    vm.$set(key, date);
                }
            });
        };

    },
    update: function (val) {
        jQuery(this.el).datepicker('setDate', val);
    }
});

// Vue directive for Date picker
Vue.directive( 'timepicker', {
    bind: function () {
        var vm = this.vm;
        var key = this.expression;

        jQuery(this.el).timepicker({
            'scrollDefault': 'now',
            'step': 15
        });
    },

    update: function (val) {
        jQuery(this.el).timepicker('setTime', val);
    }
});

// Vue directive for Date picker
Vue.directive( 'tiptip', {
    bind: function () {
        jQuery(this.el).tipTip( {
            defaultPosition: "top",
            fadeIn: 100,
            fadeOut: 100,
            content: this.el.__vue__.title
        } );
    }
});


// Select2 Direcetive
Vue.directive('selecttwo', {
    bind: function () {
        var vm = this.vm;
        var key = this.expression;

        var select = jQuery(this.el);

        select.on('change', function () {
            vm.$set( key, select.val() );
        });

        select.select2({
            width : 'resolve',
        });
    }
});


var ToolTip = Vue.extend({
    props: ['title', 'content' ],
    template: '<span class="time erp-tips" v-tiptip title="{{ title }}">{{{ content }}}</span>',
});


var TimeLineHeader = Vue.extend({
    props: [ 'feed' ],

    template: '<span class="timeline-feed-avatar">'
                    + '<img v-bind:src="createdUserImg">'
                +'</span>'
                +'<span class="timeline-feed-header-text">'
                    +'<strong>{{createdUserName}} </strong>'
                    +'<span v-if="isNote">created a note for <strong>{{ createdForUser }}</strong></span>'
                    +'<span v-if="isEmail">sent an email to <strong>{{ createdForUser }}</strong></span>'
                    +'<span v-if="isLog">'
                        +'logged {{ logType }} on {{ logDateTime | formatDateTime }} for <strong>{{ createdForUser }}</strong>'
                    +'</span>'
                    +'<span v-if="isSchedule">'
                        +'have scheduled {{ logType }} with '
                        +'<strong>{{ createdForUser }}</strong>'
                            +' <span v-if="countUser">and</span> <strong v-if="countUser == 1">{{ feed.extra.invited_user[0].name }}</strong>'
                        +'<strong v-if="countUser > 1"><tooltip :content="countUser" :title="invitedUser"></tooltip></strong>'
                    +'</span>'
                +'</span>',

    components: {
        'tooltip' : ToolTip
    },

    computed: {

        countUser: function () {
            var count = this.feed.extra.invited_user.length;
            return ( count <= 1 ) ? count : count + ' others';
        },

        invitedUser: function() {
            return this.feed.extra.invited_user.map( function( elm ) { return elm.name } ).join("<br>");
        },

        isNote: function() {
            return ( this.feed.type == 'new_note' );
        },

        isEmail: function() {
            return ( this.feed.type == 'email' );
        },

        isLog: function() {
            return ( this.feed.type == 'log_activity' ) && !( new Date() < new Date( this.feed.start_date ) );
        },

        isSchedule: function() {
            return ( this.feed.type == 'log_activity' ) && ( new Date() < new Date( this.feed.start_date ) );
        },

        createdUserImg: function() {
            return this.feed.created_by.avatar;
        },

        createdUserName: function() {
            return ( this.feed.created_by.ID == wpCRMvue.current_user_id ) ? 'You' : this.feed.created_by.display_name;
        },

        createdForUser: function() {
            return ( this.feed.contact.type == 'company' ) ? this.feed.contact.company : this.feed.contact.first_name + ' ' + this.feed.contact.last_name;
        },

        logType: function() {
            return ( this.feed.log_type == 'sms' || this.feed.log_type == 'email' ) ? 'an ' + this.feed.log_type : 'a ' + this.feed.log_type;
        },

        logDateTime: function() {
            return this.feed.start_date;
        }
    }
});

Vue.component( 'tooltip', ToolTip );
Vue.component( 'timeline-header', TimeLineHeader );

/**
 * Main Vue instance
 *
 * @param {object} [el, data, method, computed, compiled]
 *
 * @since 1.0
 *
 * @return void
 */
var vm = new Vue({
    el: '#erp-customer-feeds',

    data: {
        tabShow: 'new_note',
        feeds: {},
        validation: {},
        feedData : { 'message' : '', 'all_day': false, 'allow_notification' : false },
        isValid: false,
        customer_id : null,
        dt: '',
        tp: '',
        showFooter: false,
        // isSchedule: true
    },

    compiled: function() {
        this.fetchFeeds()
        this.dt = this.currentDate()
        this.tp = this.currentTime()
    },

    methods: {

        toggleFooter: function( e ) {
            jQuery( e.target ).closest('li').find('.timeline-footer').toggle();
        },

        /**
         * Eidt customer activity feed
         *
         * @param  {[object]} feed
         *
         * @return {[object]}
         */
        editFeed: function( feed ) {

            jQuery.erpPopup({
                title: 'Edit Feed',
                button: 'Save',
                id: 'erp-customer-feed-edit',
                content: wperp.template('erp-crm-customer-edit-feed')( feed ).trim(),
                onReady: function () {
                    var modal = this;

                    jQuery('.select2').select2({
                        width : 'resolve',
                    });

                    jQuery('.erp-date-field').datepicker({
                        dateFormat: 'yy-mm-dd',
                        changeMonth: true,
                        changeYear: true,
                        yearRange: '-100:+0',
                    });

                    jQuery( '.erp-time-field' ).timepicker({
                        'scrollDefault': 'now',
                        'step': 15
                    });

                    jQuery( 'select[data-selected]', modal ).each(function() {
                        var self = jQuery(this),
                            selected = self.data('selected');
                        if ( selected !== '' ) {
                            self.val( selected );
                        }
                    });

                    jQuery( 'select[data-selected].select2').each( function() {
                        var self = jQuery(this),
                            selected = self.data('selected');
                        if ( selected !== '' ) {
                            if ( String(selected).indexOf(',') == '-1' ) {
                                self.select2().select2( 'val', selected );
                            } else {
                                self.select2().select2( 'val', selected.split(',') );
                            }
                        }
                    });

                    jQuery( 'input[type=checkbox][data-checked]', modal ).each(function() {
                        var self = jQuery(this),
                            checked = self.data('checked');
                        if ( checked !== '' ) {
                            self.prop( 'checked', checked );
                        }
                    });

                    jQuery( 'input[type=checkbox][data-checked]', modal ).trigger('change');

                },
                onSubmit: function(modal) {
                    wp.ajax.send( {
                        data: this.serialize(),
                        success: function(res) {
                            vm.feeds = _.map( vm.feeds, function( feed ){
                                if ( feed.id == res.id ) {
                                   return res;
                                }
                               return feed;
                            });
                            modal.closeModal();
                        },
                        error: function(error) {
                            vm.progreassDone();
                            alert( error );
                        }
                    });
                }
            });
        },

        /**
         * Set TimePicker current time
         *
         * @return {[string]} [time string]
         */
        currentTime: function() {
            date = new Date();
            var hours = date.getHours();
            var minutes = date.getMinutes();
            var ampm = hours >= 12 ? 'pm' : 'am';
            hours = hours % 12;
            hours = hours ? hours : 12; // the hour '0' should be '12'
            minutes = minutes < 10 ? '0'+minutes : minutes;
            var strTime = hours + ':' + minutes + ' ' + ampm;
            return strTime;
        },

        /**
         * Set Datepicker current date
         *
         * @return {[string]} [date string]
         */
        currentDate : function() {
            var today = new Date();
            var dd = today.getDate();
            var mm = today.getMonth()+1;
            var yyyy = today.getFullYear();

            if( dd < 10 ) {
                dd='0'+dd
            }

            if( mm < 10 ) {
                mm='0'+mm
            }

            today = yyyy+'-'+mm+'-'+dd;
            return today;
        },

        /**
         * Delete Activity feed
         *
         * @param  {[object]} feed
         *
         * @return {[void | alert]}
         */
        deleteFeed: function( feed ) {
            var data = {
                action : 'erp_crm_delete_customer_activity',
                feed_id : feed.id,
                _wpnonce : wpCRMvue.nonce
            };

            if ( confirm( wpCRMvue.confirm ) ) {
                jQuery.post( wpCRMvue.ajaxurl, data, function( resp ) {
                    if ( resp.success ) {
                        vm.feeds.$remove( feed )
                    } else {
                        alert( resp.data );
                    };
                });
            };
        },

        /**
         * Add customer feeds
         *
         * @return {void}
         */
        addCustomerFeed: function() {

            vm.progreassStart('#erp-crm-feed-nav-content');
            this.feedData._wpnonce = wpCRMvue.nonce;

            if ( this.feedData.type == 'log_activity' ) {
                this.feedData.log_date = this.dt;
                this.feedData.log_time = this.tp;
            };

            if ( this.feedData.type == 'schedule' ) {
                this.feedData.start_date     = this.dtStart;
                this.feedData.start_time     = this.tpStart;
                this.feedData.end_date       = this.dtEnd;
                this.feedData.end_time       = this.tpEnd;
                this.feedData.invite_contact = this.inviteContact;
            };

            jQuery.post( wpCRMvue.ajaxurl, this.feedData, function( resp ) {
                vm.feeds.splice( 0, 0, resp.data );

                document.getElementById("erp-crm-activity-feed-form").reset();

                if ( vm.feedData.type == 'log_activity' ) {
                    vm.feedData.log_type = '';
                    vm.dt = '';
                    vm.tp = '';
                };

                if ( vm.feedData.type == 'email' ) {
                    vm.feedData.email_subject = '';
                };


                if ( vm.feedData.type == 'schedule' ) {
                    jQuery('#erp-crm-activity-invite-contact').select2().select2( "val", "" );
                    vm.feedData.all_day = false;
                    vm.feedData.allow_notification = false;
                    vm.feedData.schedule_title = '';
                    vm.feedData.schedule_type = '';
                    vm.feedData.notification_via = '';
                    vm.feedData.notification_time = '';
                    vm.feedData.notification_time_interval = '';
                    vm.feedData.start_date     = '';
                    vm.feedData.start_time     = '';
                    vm.feedData.end_date       = '';
                    vm.feedData.end_time       = '';
                };

                vm.progreassDone();
            });
        },

        /**
         * Show tab according to his ID
         *
         * @param  {string} id
         */
        showTab: function( id ){
            this.tabShow = id;
        },

        /**
         * Fetch all feeds when page loaded
         *
         * @return {[object]}
         */
        fetchFeeds: function() {
            var data = {
                action : 'erp_crm_get_customer_activity',
                customer_id : this.customer_id
            };

            jQuery.post( wpCRMvue.ajaxurl, data, function( resp ) {
                vm.feeds = resp.data;
            });


        },

        /**
         * Start Progressbar
         *
         * @param  {[string]} id
         */
        progreassStart: function( id ) {
            NProgress.configure({ parent: id });
            NProgress.start();
        },

        /**
         * Stop Progressbar
         *
         * @param  {[string]} id
         */
        progreassDone: function( id ) {
            NProgress.done();
        },

        /**
         * Check is Schedule
         *
         * @param  {[string]} date
         *
         * @return {Boolean}
         */
        isSchedule: function( date ) {
            return new Date() < new Date( date );
        }

    },

    computed: {

        /**
         * Apply feed form validation
         *
         * @return {[void]}
         */
        validation: function() {

            if ( this.feedData.type == 'new_note' ) {
                return {
                    message : !!this.feedData.message
                }
            }

            if ( this.feedData.type == 'email' ) {
                return {
                    message : !!this.feedData.message,
                    email_subject : !!this.feedData.email_subject,
                }
            }

            if ( this.feedData.type == 'log_activity' ) {
                return {
                    message : !!this.feedData.message,
                    log_type : !!this.feedData.log_type,
                    log_date : !!this.dt,
                    log_time : !!this.tp,
                }
            }


            if ( this.feedData.type == 'schedule' ) {
                return {
                    message : !!this.feedData.message,
                    schedule_title : !!this.feedData.schedule_title,
                    startDate : !!this.dtStart,
                    startTime : ( ! this.feedData.all_day ) ? !!this.tpStart : true,
                    endDate : !!this.dtEnd,
                    endTime : ( ! this.feedData.all_day ) ? !!this.tpEnd : true,
                    schedule_type : !!this.feedData.schedule_type,
                    notification_via: ( this.feedData.allow_notification ) ? !!this.feedData.notification_via : true,
                    notification_time_interval: ( this.feedData.allow_notification ) ? !!this.feedData.notification_time_interval : true,
                    notification_time: ( this.feedData.allow_notification ) ? !!this.feedData.notification_time : true,
                }
            }
        },

        /**
         * Check whole form is valid or not for form submission
         *
         * @return {Boolean}
         */
        isValid: function() {
            var validation = this.validation

            if ( jQuery.isEmptyObject( validation ) ) return;

            return Object.keys( validation ).every(function(key){
                return validation[key]
            });
        }
    },
});

// Bind trix-editor value with v-model message
document.addEventListener('trix-change', function (e) {
    vm.feedData.message = e.path[0].innerHTML;
});


