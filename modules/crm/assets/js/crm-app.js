/**************************************************************
 *****************    Vue Filters     *************************
 **************************************************************/


// Vue Filter for Formatting Time
Vue.filter('filterFeeds', function ( feedData, filterFeeds ) {

    var feedsData = _.filter( feedData, function( data ) {
        if ( filterFeeds.filterActivityType ) {

            if ( filterFeeds.filterActivityType == 'log_activity' ) {
                return ( data.type == filterFeeds.filterActivityType ) && !vm.isSchedule( data.start_date );
            }

            if ( filterFeeds.filterActivityType == 'schedule' ) {
                return ( data.type == 'log_activity' ) && vm.isSchedule( data.start_date );
            }

            return ( data.type == filterFeeds.filterActivityType );
        }

        return data;

    });

    var feedsData = _.filter( feedsData, function( data ) {
        if ( filterFeeds.filterCreatedBy ) {
            return ( data.created_by.ID == filterFeeds.filterCreatedBy );
        }
        return data;
    });

    var feedsData = _.filter( feedsData, function( data ) {
        if ( filterFeeds.filterCreatedFor ) {
            return ( data.user_id == filterFeeds.filterCreatedFor );
        }
        return data;
    });

     var feedsData = _.filter( feedsData, function( data ) {
        if ( filterFeeds.filterCreatedOn ) {
            return ( data.created_date == filterFeeds.filterCreatedOn );
        }
        return data;
    });

    return ( feedsData ) ? feedsData : [];
});


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

    if ( feed.type == 'log_activity' && ! vm.isSchedule( feed.start_date ) ) {
        if ( feed.log_type == 'email' ) {
            message = '<div class="timeline-email-subject">Subject : ' + feed.email_subject + '</div>' +
                  '<div class="timeline-email-body">' + feed.message + '</div>';
        }
    }

    if ( feed.type == 'log_activity' && vm.isSchedule( feed.start_date ) ) {
        var filters = vm.$options.filters,
            startDate = filters.formatDate( feed.start_date, 'j F' ),
            startTime = filters.formatAMPM( feed.start_date ),
            endDate = filters.formatDate( feed.end_date, 'j F' ),
            endTime = filters.formatAMPM( feed.end_date );


        if ( feed.extra.all_day == 'true' ) {
            if ( filters.formatDate( feed.start_date, 'Y-m-d' ) == filters.formatDate( feed.end_date, 'Y-m-d' ) ) {
                var datetime = startDate;
            } else {
                var datetime = startDate + ' to ' + endDate;
            }
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

/******************** End vue filters ***********************/



/*****************************************************************
 *******************     Vue Directive     ***********************
 ****************************************************************/

// Vue directive for Date picker
Vue.directive( 'datepicker', {
    params: ['datedisable'],
    twoWay: true,
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
                onClose: function (date) {
                    if ( date.match(/^(0?[1-9]|[12][0-9]|3[01])[\/\-\.](0?[1-9]|1[012])[\/\-\.]\d{4}$/) )
                        vm.$set(key, date);
                    else {
                        vm.$set(key, "");
                    }
                }
            });
        } else if ( this.params.datedisable == 'upcomming' ) {
            jQuery(this.el).datepicker({
                maxDate: 0,
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                yearRange: '-100:+0',
                onClose: function (date) {
                    if ( date.match(/^(0?[1-9]|[12][0-9]|3[01])[\/\-\.](0?[1-9]|1[012])[\/\-\.]\d{4}$/) )
                        vm.$set(key, date);
                    else {
                        vm.$set(key, "");
                    }
                }
            });
        } else {
            jQuery(this.el).datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                yearRange: '-100:+0',
                onClose: function (date) {
                    if ( date.match(/^(0?[1-9]|[12][0-9]|3[01])[\/\-\.](0?[1-9]|1[012])[\/\-\.]\d{4}$/) )
                        vm.$set(key, date);
                    else {
                        vm.$set(key, "");
                    }
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
        var self = this
        var vm = this.vm;
        var key = this.expression;

        var select = jQuery(this.el);

        select.on('change', function () {
            var search_key = jQuery(this).attr('data-searchkey');
            var search_key_index = jQuery(this).attr('data-searchkeyindex');

            if ( search_key && search_key_index ) {
                key = key.replace('search_key', search_key);
                key = key.replace('search_field_key', search_key_index);
            }
            vm.$set( key, select.val() );
        });

        select.select2({
            width : 'resolve',
            placeholder: jQuery(this.el).attr('data-placeholder'),
            allowClear: true
        });

    },
});

/************************ End Vue Directive **********************/


/******************************************************************
*******************      Component       **************************
*******************************************************************/

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
                        +' <span v-if="countUser == 1">and <strong>{{ feed.extra.invited_user[0].name }}</strong></span>'
                        +'<span v-if="( countUser != 0 ) && countUser != 1"> and <strong><tooltip :content="countUser" :title="invitedUser"></tooltip></strong></span>'
                    +'</span>'
                    +'<span v-if="isSchedule">'
                        +'have scheduled {{ logType }} with '
                        +'<strong>{{ createdForUser }}</strong>'
                            +' <span v-if="countUser == 1">and <strong>{{ feed.extra.invited_user[0].name }}</strong></span>'
                        +'<span v-if="( countUser != 0 ) && countUser != 1"> and <strong><tooltip :content="countUser" :title="invitedUser"></tooltip></strong></span>'
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
            return this.feed.extra.invited_user.map( function( elm ) {
                if ( elm.id == wpCRMvue.current_user_id ) {
                    return 'You';
                } else {
                    return elm.name;
                }
            } ).join("<br>");
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


Vue.component( 'timeline-item', {
    props: ['feed', 'disbaleFooter'],
    template : '#erp-crm-timeline-item-template',

    data: function() {
        return {
            feedData: { message: '' },
            showFooter : false,
            editfeedData: {},
            isEditable: false
        }
    },

    methods: {

        toggleFooter: function() {
            if ( this.disbaleFooter == 'true' ) {
                this.showFooter = false;
            } else {
                this.showFooter = !this.showFooter;
            }
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

        editFeed: function( feed ) {
            this.editfeedData = feed;
            this.isEditable = true;
        },

        cancelUpdate: function() {
            this.isEditable = false;
            this.editfeedData = {};
        },

        isSchedule: function( date ) {
            return new Date() < new Date( date );
        },

        updateCustomerFeed: function( feed_id ) {
            vm.addCustomerFeed( this, feed_id );
        },

        notify: function() {
            this.$broadcast('bindEditFeedData', this.feed );
        }
    },

    watch: {
        editfeedData: {
            deep: true,
            immediate: true,
            handler: function () {
                this.notify();
            }
        }
    }
});

/**
 * New Note Component
 *
 * @param {object} feedData
 * @param {boolean} isValid
 *
 * @return {void}
 */
Vue.component( 'new-note', {
    props: ['feed'],

    template: '#erp-crm-new-note-template',

    data: function() {
        return {
            feedData: {
                message: ''
            },
            isValid: false
        }
    },

    methods: {
        notify: function () {
            this.$dispatch('bindFeedData', this.feedData);
        },

        cancelUpdateFeed: function() {
            this.$parent.$data.isEditable = false;
            this.$parent.$data.editfeedData = {};
        }
    },

    computed: {

        validation: function() {
            return {
                message : !!this.feedData.message
            }
        },

        isValid: function() {
            var validation = this.validation

            if ( jQuery.isEmptyObject( validation ) ) return;

            return Object.keys( validation ).every(function(key){
                return validation[key]
            });
        }
    },

    watch: {
        feedData: {
            deep: true,
            immediate: true,
            handler: function () {
                this.notify();
            }
        }
    },

    activate: function (done) {

        var self = this;
        jQuery(this.$el).find('trix-editor').get(0).addEventListener('trix-change', function (e) {
            self.feedData.message = e.target.innerHTML;
        });

        done();
    }
});

/**
 * Log Activity Note Component
 *
 * @param {object} feedData
 * @param {boolean} isValid
 *
 * @return {void}
 */
Vue.component( 'log-activity', {
    props: ['feed'],

    template: '#erp-crm-log-activity-template',

    data: function() {
        return {
            feedData: {
                message: '',
                log_type: '',
                email_subject: '',
                inviteContact: '',
                dt: '',
                tp: ''
            },

            isValid: false
        }
    },

    methods: {
        notify: function () {
            this.$dispatch('bindFeedData', this.feedData );
        },

        cancelUpdateFeed: function() {
            this.$parent.$data.isEditable = false;
            this.$parent.$data.editfeedData = {};
        }
    },

    events: {
        'bindEditFeedData': function (feed ) {
            this.feedData.log_type      = feed.log_type;
            this.feedData.email_subject = ( feed.log_type == 'email' ) ? feed.email_subject : '';
            this.feedData.dt            = vm.$options.filters.formatDate( feed.start_date, 'Y-m-d' );
            this.feedData.tp            = vm.$options.filters.formatAMPM( feed.start_date );

            if ( feed.log_type == 'meeting' && ! _.isEmpty( feed.extra.invited_user ) ) {
                var invitedUser             = feed.extra.invited_user.map( function( elm ) { return elm.id } ).join(',');
                this.feedData.inviteContact = invitedUser;
                var self = jQuery( this.$el ).find( 'select.select2' );

                if ( String(invitedUser).indexOf(',') == '-1' ) {
                    self.select2().select2( 'val', invitedUser );
                } else {
                    self.select2().select2( 'val', invitedUser.split(',') );
                }
            };

        }
    },

    computed: {

        validation: function() {
            return {
                message : !!this.feedData.message,
                log_type : !!this.feedData.log_type,
                log_date : !!this.feedData.dt,
                log_time : !!this.feedData.tp,
                email_subject : ( this.feedData.log_type == 'email' ) ? !!this.feedData.email_subject : true
            }
        },

        isValid: function() {
            var validation = this.validation

            if ( jQuery.isEmptyObject( validation ) ) return;

            return Object.keys( validation ).every(function(key){
                return validation[key]
            });
        }
    },

    watch: {
        feedData: {
            deep: true,
            immediate: true,
            handler: function () {
                this.notify();
            }
        }
    },

    activate: function (done) {

        var self = this;
        jQuery(this.$el).find('trix-editor').get(0).addEventListener('trix-change', function (e) {
            self.feedData.message = e.path[0].innerHTML;
        });

        done();
    }

});

/**
 * Email Note Component
 *
 * @param  {[object]} feedData
 * @param  {Boolean} isValid
 *
 * @return {[void]}
 */
Vue.component( 'email-note', {
    template: '#erp-crm-email-note-template',

    data: function() {
        return {
            feedData: {
                message: ''
            },
            isValid: false
        }
    },

    methods: {
        notify: function () {
            this.$dispatch('bindFeedData', this.feedData );
        }
    },

    computed: {

        validation: function() {
            return {
                message : !!this.feedData.message,
                email_subject : !!this.feedData.email_subject,
            }
        },

        isValid: function() {
            var validation = this.validation

            if ( jQuery.isEmptyObject( validation ) ) return;

            return Object.keys( validation ).every(function(key){
                return validation[key]
            });
        }
    },

    watch: {
        feedData: {
            deep: true,
            immediate: true,
            handler: function () {
                this.notify();
            }
        }
    },

    activate: function (done) {

        var self = this;
        jQuery(this.$el).find('trix-editor').get(0).addEventListener('trix-change', function (e) {
            self.feedData.message = e.path[0].innerHTML;
        });

        done();
    }

});

/**
 * Schedule Note Component
 *
 * @param  {object} feedData
 * @param  {Boolean} isValid: false
 *
 * @return {[void]}
 */
Vue.component( 'schedule-note', {
    props: ['feed'],
    template: '#erp-crm-schedule-note-template',

    data: function() {
        return {
            feedData: {
                message                     : '',
                schedule_title              : '',
                schedule_type               : '',
                notification_via            : '',
                notification_time           : '',
                notification_time_interval  : '',
                allow_notification          : false,
                all_day                     : false,
                dtStart                     : '',
                tpStart                     : '',
                dtEnd                       : '',
                tpEnd                       : '',
                inviteContact               : ''
            },

            isValid: false
        }
    },

    events: {
        'bindEditFeedData': function (feed ) {
            var invitedUser = feed.extra.invited_user.map( function( elm ) { return elm.id } ).join(',');
            this.feedData.all_day                    = feed.extra.all_day == 'true' ? true : false;
            this.feedData.allow_notification         = feed.extra.allow_notification == 'true' ? true : false;
            this.feedData.schedule_title             = feed.extra.schedule_title;
            this.feedData.schedule_type              = feed.log_type;
            this.feedData.notification_via           = feed.extra.notification_via;
            this.feedData.notification_time          = feed.extra.notification_time;
            this.feedData.notification_time_interval = feed.extra.notification_time_interval;
            this.feedData.dtStart                    = vm.$options.filters.formatDate( feed.start_date, 'Y-m-d' );
            this.feedData.tpStart                    = vm.$options.filters.formatAMPM( feed.start_date );
            this.feedData.dtEnd                      = vm.$options.filters.formatDate( feed.end_date, 'Y-m-d' );
            this.feedData.tpEnd                      = vm.$options.filters.formatAMPM( feed.end_date );
            this.feedData.inviteContact              = invitedUser;

            var self = jQuery( this.$el ).find( 'select.select2' );

            if ( String(invitedUser).indexOf(',') == '-1' ) {
                self.select2().select2( 'val', invitedUser );
            } else {
                self.select2().select2( 'val', invitedUser.split(',') );
            }

        }
    },

    methods: {
        notify: function () {
            this.$dispatch( 'bindFeedData', this.feedData );
        },

        cancelUpdateFeed: function() {
            this.$parent.$data.isEditable = false;
            this.$parent.$data.editfeedData = {};
        }
    },

    computed: {

        validation: function() {
            return {
                message                     : !!this.feedData.message,
                schedule_title              : !!this.feedData.schedule_title,
                startDate                   : !!this.feedData.dtStart,
                startTime                   : ( ! this.feedData.all_day ) ? !!this.feedData.tpStart : true,
                endDate                     : !!this.feedData.dtEnd,
                endTime                     : ( ! this.feedData.all_day ) ? !!this.feedData.tpEnd : true,
                schedule_type               : !!this.feedData.schedule_type,
                notification_via            : ( this.feedData.allow_notification ) ? !!this.feedData.notification_via : true,
                notification_time_interval  : ( this.feedData.allow_notification ) ? !!this.feedData.notification_time_interval : true,
                notification_time           : ( this.feedData.allow_notification ) ? !!this.feedData.notification_time : true,
            }
        },

        isValid: function() {
            var validation = this.validation

            if ( jQuery.isEmptyObject( validation ) ) return;

            return Object.keys( validation ).every(function(key){
                return validation[key]
            });
        }
    },

    watch: {
        feedData: {
            deep: true,
            immediate: true,
            handler: function () {
                this.notify();
            }
        }
    },

    activate: function (done) {

        var self = this;
        jQuery(this.$el).find('trix-editor').get(0).addEventListener('trix-change', function (e) {
            self.feedData.message = e.path[0].innerHTML;
        });

        done();
    }

});

/********************* End Component *****************************/


/****************************************************************
***************       Main Vue Instance       *******************
****************************************************************/

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
        feeds: [],
        validation: {},
        feedData : {},
        isValid: false,
        customer_id : null,
        showFooter: false,
        offset: 0,
        limit : 2,
        loading: false,
        loadingFinish: false,
        filterFeeds: {
            type: '',
            created_by: '',
            customer_id: '',
            created_at: ''
        }
    },

    events: {
        'bindFeedData': function (feedData) {
            this.feedData = feedData;
        }
    },

    compiled: function() {
        this.fetchFeeds();
    },

    watch: {
        filterFeeds: {
            deep: true,
            handler: function ( newVal ) {
                this.fetchFeeds( newVal );
            }
        }
    },

    methods: {

        loadMoreContent: function( feeds ) {
            vm.progreassStart('.feed-load-more');
            this.loading = true;
            this.offset = this.offset + this.limit;

            var data = {
                action : 'erp_crm_get_customer_activity',
                customer_id : this.customer_id,
                limit: this.limit,
                offset: this.offset,
            };

            if ( this.filterFeeds.customer_id ) {
                data.customer_id = this.filterFeeds.customer_id;
            }

            if ( this.filterFeeds.created_by ) {
                data.created_by = this.filterFeeds.created_by;
            }

            if ( this.filterFeeds.created_at ) {
                data.created_at = this.filterFeeds.created_at;
            }

            if ( this.filterFeeds.type ) {
                data.type = this.filterFeeds.type;
            }

            if ( ! vm.loadingFinish ) {
                jQuery.post( wpCRMvue.ajaxurl, data, function( resp ) {
                    vm.progreassDone(true);

                    if ( resp.data.length ) {
                        setTimeout( function() {
                            vm.loading = false;
                            vm.feeds = vm.feeds.concat( resp.data );
                        }, 500 );
                    } else {
                        vm.loading = false;
                        vm.loadingFinish = true;
                    }
                });
            }
        },

        toggleFooter: function( e ) {
            jQuery( e.target ).closest('li').find('.timeline-footer').toggle();
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
         * Add customer feeds
         *
         * @return {void}
         */
        addCustomerFeed: function( comp, feed_id ) {

            if ( feed_id ) {
                this.feedData.id = feed_id;
                vm.progreassStart( '#timeline-item-'+feed_id );
            } else {
                vm.progreassStart('#erp-crm-feed-nav-content');
            }

            this.feedData._wpnonce = wpCRMvue.nonce;

            if ( this.feedData.type == 'log_activity' ) {
                this.feedData.log_date = this.feedData.dt;
                this.feedData.log_time = this.feedData.tp;
                this.feedData.invite_contact = this.feedData.inviteContact;
            };

            if ( this.feedData.type == 'schedule' ) {
                this.feedData.start_date     = this.feedData.dtStart;
                this.feedData.start_time     = this.feedData.tpStart;
                this.feedData.end_date       = this.feedData.dtEnd;
                this.feedData.end_time       = this.feedData.tpEnd;
                this.feedData.invite_contact = this.feedData.inviteContact;
            };

            jQuery.post( wpCRMvue.ajaxurl, this.feedData, function( resp ) {

                if ( feed_id ) {
                    vm.progreassDone(true);

                    setTimeout( function() {
                        vm.feeds = _.map( vm.feeds, function( feed ){
                            if ( feed.id == resp.data.id ) {
                               return resp.data;
                            }
                           return feed;
                        });

                        if ( vm.feeds.length > vm.limit ) {
                            vm.feeds.$remove( vm.feeds[vm.feeds.length-1] );
                        }

                        comp.isEditable = false;
                    }, 500 )

                } else {
                    vm.feeds.splice( 0, 0, resp.data );

                    if ( vm.feeds.length > vm.limit ) {
                        vm.feeds.$remove( vm.feeds[vm.feeds.length-1] );
                    }

                    document.getElementById("erp-crm-activity-feed-form").reset();

                    if ( vm.feedData.type == 'log_activity' ) {
                        vm.feedData.log_type      = '';
                        vm.feedData.email_subject = '';
                        vm.feedData.dt            = '';
                        vm.feedData.tp            = '';
                        vm.feedData.inviteContact = [];

                    };

                    if ( vm.feedData.type == 'email' ) {
                        vm.feedData.email_subject = '';
                    };

                    if ( vm.feedData.type == 'schedule' ) {
                        jQuery('#erp-crm-activity-invite-contact').select2().select2( "val", "" );
                        vm.feedData.all_day                    = false;
                        vm.feedData.allow_notification         = false;
                        vm.feedData.schedule_title             = '';
                        vm.feedData.schedule_type              = '';
                        vm.feedData.notification_via           = '';
                        vm.feedData.notification_time          = '';
                        vm.feedData.notification_time_interval = '';
                        vm.feedData.start_date                 = '';
                        vm.feedData.start_time                 = '';
                        vm.feedData.end_date                   = '';
                        vm.feedData.end_time                   = '';
                        vm.feedData.dtStart                    = '';
                        vm.feedData.tpStart                    = '';
                        vm.feedData.dtEnd                      = '';
                        vm.feedData.tpEnd                      = '';
                        vm.feedData.inviteContact              = [];
                    };

                    vm.progreassDone();
                }
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
        fetchFeeds: function( filter ) {

            var data = {
                action : 'erp_crm_get_customer_activity',
                customer_id : this.customer_id,
                limit: this.limit,
                offset: this.offset,
            };


            if ( filter ) {
                vm.progreassStart('#erp-crm-activities-filter');

                data.customer_id = filter.customer_id;
                data.created_by = filter.created_by;
                data.created_at = filter.created_at;
                data.type = filter.type;
                data.offset = 0;
                this.offset = 0;
                this.loadingFinish = false;
            }

            jQuery.post( wpCRMvue.ajaxurl, data, function( resp ) {
                vm.feeds = resp.data;
                if ( filter ) {
                    vm.progreassDone();
                }
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
        progreassDone: function( force ) {
            if ( force ) {
                NProgress.done( force );
            } else {
                NProgress.done();
            }
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
});

/******************** End Main Vue instance **********************/
