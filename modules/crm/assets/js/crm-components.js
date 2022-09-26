window.wpErpVue = window.wpErpVue || {};

;(function($, erpVue ) {

    /*****************************************************************
     *******************     Vue Directive     ***********************
     ****************************************************************/
    Vue.filter( 'formatDate', function (date, format ) {
        return wperp.dateFormat( date, format );
    });

    // Vue filter for formatting Feeds as a group by object
    Vue.filter( 'formatFeeds', function ( feeds ) {
        var feedsData = _.groupBy( feeds, function( data ) {
            return data.created_timeline_date;
        });

        return feedsData;
    });

    // Vue filter for formatting Feeds as a group by object
    Vue.filter('formatDateTime', function ( date ) {
        return wperp.dateFormat( date, 'F, j' ) + ' at ' + wperp.timeFormat( date )
    });

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
                    yearRange: '-100:+5',
                    onClose: function (date) {
                        vm.$set(key, date);
                    }
                });
            } else if ( this.params.datedisable == 'upcomming' ) {
                jQuery(this.el).datepicker({
                    maxDate: 0,
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    changeYear: true,
                    yearRange: '-100:+5',
                    onClose: function (date) {
                        vm.$set(key, date);
                    }
                });
            } else {
                jQuery(this.el).datepicker({
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    changeYear: true,
                    yearRange: '-100:+5',
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
        bind:function() {
            var self   = this;
            var vm     = this.vm;
            var key    = this.expression;
            var select = jQuery(this.el);
       
            select.on('change', function() {
                var search_key = jQuery(this).attr('data-searchkey');
                var search_key_index = jQuery(this).attr('data-searchkeyindex');
                if ( search_key && search_key_index ) {
                    key = key.replace('search_key', search_key);
                    key = key.replace('search_field_key', search_key_index);
                }
                vm.$set(key, select.val());
           });

            select.select2({
                placeholder: jQuery(this.el).attr('data-placeholder'),
                allowClear: true
            });
        },

        update: function (newValue, oldValue) {
            var self   = this;
            var select = jQuery(self.el);

            if ( newValue && !oldValue ) {
                select.val(newValue);
                select.trigger('change');
            }
        },
    });

    /** Vue MIXIN for timeline items */

    var TimilineMixin = {
        data: function() {
            return {
                feedData: { message: '' },
                editfeedData: {},
                showFooter: false,
                isEditable: false,
                isReplied: false
            }
        },
        methods: {
            toggleFooter: function() {
                if ( wpCRMvue.isAdmin || wpCRMvue.isCrmManager || this.checkOwnFeeds() ) {
                    if ( this.disbaleFooter == 'true' ) {
                        this.showFooter = false;
                    } else {
                        this.showFooter = !this.showFooter;
                    }
                }
            },

            checkOwnFeeds: function() {
                return ( wpCRMvue.isAgent ) && this.feed.created_by.ID == wpCRMvue.current_user_id;
            },

            editFeed: function( feed ) {
                this.editfeedData = feed;
                this.isEditable = true;
            },

            cancelUpdate: function() {
                this.isEditable = false;
                this.editfeedData = {};
            },

            replyEmailFeed: function( feed ) {
                this.editfeedData = feed;
                this.isReplied = true;
                this.isEditable = false;
            },

            isActivityPage: function() {
                return ( wpCRMvue.isActivityPage === undefined ) ? false : true;
            },

            deleteFeed: function( feed ) {
                var data = {
                    action : 'erp_crm_delete_customer_activity',
                    feed_id : feed.id,
                    _wpnonce : wpCRMvue.nonce
                };


                if ( confirm( wpCRMvue.confirm ) ) {
                    vm.progressStart('#timeline-item-'+feed.id );
                    jQuery.post( wpCRMvue.ajaxurl, data, function( resp ) {
                        if ( resp.success ) {
                            vm.progreassDone(true);
                            setTimeout( function() {
                                vm.feeds.$remove( feed );
                            }, 500);
                        } else {
                            alert( resp.data );
                        };
                    });
                };
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
    };

    /************************ End Vue Directive **********************/

    var erp = erpVue || {};

    erp.ToolTip = {
        props: ['title', 'content' ],
        template: '<span class="time erp-tips" v-tiptip data-title="{{ title }}">{{{ content }}}</span>',
    };

    Vue.component( 'tooltip', erp.ToolTip );

    Vue.component( 'new-note-component', {
        props: [ 'i18n', 'feed' ],

        mixins: [ TimilineMixin ],

        data: function() {
            return {
                headerText: '',
            }
        },

        template: '#erp-crm-timeline-feed-new-note',

        computed: {
            headerText: function() {
                return this.i18n.newNoteHeadertext ?
                        this.i18n.newNoteHeadertext
                        .replace( '{{createdUserName}}', this.createdUserName )
                        .replace( '{{createdForUser}}', this.createdForUser )
                        : '';
            },

            createdUserImg: function() {
                return this.feed.created_by.avatar;
            },

            createdUserName: function() {
                return ( this.feed.created_by.ID == wpCRMvue.current_user_id ) ? this.i18n.you : this.feed.created_by.display_name;
            },

            createdForUser: function() {
                return _.contains( this.feed.contact.types, 'company' ) ? this.feed.contact.company : this.feed.contact.first_name + ' ' + this.feed.contact.last_name;
            },
        }
    });

    Vue.component( 'email-component', {
        props: [ 'i18n', 'feed' ],

        mixins: [ TimilineMixin ],

        template: '#erp-crm-timeline-feed-email',

        data: function() {
            return {
                headerText: '',
                emailViewedTime: false,
            }
        },

        computed: {
            headerText: function() {
                var headerText      = this.i18n.emailHeadertext ? this.i18n.emailHeadertext
                                        .replace( '{{createdUserName}}', this.createdUserName )
                                        .replace( '{{createdForUser}}', this.createdForUser )
                                        : '';

                var replyHeaderText = this.i18n.replyEmailHeadertext ? this.i18n.replyEmailHeadertext
                                        .replace( '{{createdUserName}}', this.createdUserName )
                                        .replace( '{{createdForUser}}', this.createdForUser )
                                        : '';

                return ( ! this.isRepliedEmail ) ? headerText : replyHeaderText;
            },

            emailViewedTime: function() {
                if ( this.feed.extra.email_opened_at ) {
                    if ( ! $.isArray(this.feed.extra.email_opened_at) ) {
                        this.feed.extra.email_opened_at = [this.feed.extra.email_opened_at];
                    }

                    var dateTime = this.feed.extra.email_opened_at.map(function (viewedAt) {
                        return vm.$options.filters.formatDateTime( viewedAt );
                    });

                    return this.i18n.viewedAt.replace( '{{ emailViewedTime }}', dateTime.join(', ') );

                } else {
                    return false;
                }
            },

            createdUserImg: function() {
                return this.feed.created_by.avatar;
            },

            createdUserName: function() {
                return ( this.feed.created_by.ID == wpCRMvue.current_user_id ) ? this.i18n.you : this.feed.created_by.display_name;
            },

            createdForUser: function() {
                return _.contains( this.feed.contact.types, 'company' ) ? this.feed.contact.company : this.feed.contact.first_name + ' ' + this.feed.contact.last_name;
            },

            isRepliedEmail: function() {
                return ( this.feed.type == 'email' ) && this.feed.extra.replied == 1;
            },
        }
    } );

    Vue.component( 'log-activity-component', {
        props: [ 'i18n', 'feed' ],

        mixins: [ TimilineMixin ],

        template: '#erp-crm-timeline-feed-log-activity',

        data: function() {
            return {
                headerText: '',
            }
        },

        computed: {
            headerText: function() {
                if ( this.countUser == 1 ) {
                    return this.i18n.logHeaderTextSingleUser ?
                    this.i18n.logHeaderTextSingleUser
                        .replace( '{{createdUserName}}', this.createdUserName )
                        .replace( '{{logType}}', this.logType )
                        .replace( '{{logDateTime}}', this.logDateTime )
                        .replace( '{{createdForUser}}', this.createdForUser )
                        .replace( '{{otherUser}}', this.feed.extra.invited_user[0].name )
                        : '';
                } else {
                    return this.i18n.logHeaderText ?
                        this.i18n.logHeaderText
                        .replace( '{{createdUserName}}', this.createdUserName )
                        .replace( '{{logType}}', this.logType )
                        .replace( '{{logDateTime}}', this.logDateTime )
                        .replace( '{{createdForUser}}', this.createdForUser )
                        : '';
                }
            },

            headerScheduleText: function() {
                if ( this.countUser == 1 ) {
                    return this.i18n.scheduleHeaderTextSingleUser ?
                        this.i18n.scheduleHeaderTextSingleUser
                        .replace( '{{createdUserName}}', this.createdUserName )
                        .replace( '{{logType}}', this.logType )
                        .replace( '{{createdForUser}}', this.createdForUser )
                        .replace( '{{otherUser}}', this.feed.extra.invited_user[0].name )
                        : '';
                } else {
                    return this.i18n.scheduleHeaderText ?
                        this.i18n.scheduleHeaderText
                        .replace( '{{createdUserName}}', this.createdUserName )
                        .replace( '{{logType}}', this.logType )
                        .replace( '{{createdForUser}}', this.createdForUser )
                        : '';
                }
            },


            countUser: function () {
                if (!this.feed.extra.invited_user) {
                    return 0;
                }
                var count = this.feed.extra.invited_user.length;
                return ( count <= 1 ) ? count : count + ' ' + this.i18n.others;
            },

            invitedUser: function() {
                var self = this;
                return this.feed.extra.invited_user ? this.feed.extra.invited_user.map( function( elm ) {
                    if ( elm.id == wpCRMvue.current_user_id ) {
                        return self.i18n.you;
                    } else {
                        return elm.name;
                    }
                } ).join("<br>") : '';
            },

            createdUserImg: function() {
                return this.feed.created_by.avatar;
            },

            createdUserName: function() {
                return ( this.feed.created_by.ID == wpCRMvue.current_user_id ) ? this.i18n.you : this.feed.created_by.display_name;
            },

            createdForUser: function() {
                return _.contains( this.feed.contact.types, 'company' ) ? this.feed.contact.company : this.feed.contact.first_name + ' ' + this.feed.contact.last_name;
            },

            logType: function() {
                return ( this.feed.log_type == 'email' ) ? this.i18n.an + ' ' + this.feed.log_type : this.i18n.a + ' ' + this.feed.log_type;
            },

            islogTypeEmail: function() {
                return this.feed.log_type == 'email';
            },

            logDateTime: function() {
                return vm.$options.filters.formatDateTime( this.feed.start_date );
            },

            isLog: function() {
                return ( this.feed.type == 'log_activity' ) && !( new Date() < new Date( this.feed.start_date ) );
            },

            isSchedule: function() {
                return ( this.feed.type == 'log_activity' ) && ( new Date() < new Date( this.feed.start_date ) );
            },

            datetime: function() {
                var datetime;

                if ( this.isSchedule ) {
                        var startDate = wperp.dateFormat( this.feed.start_date, 'j F' ),
                        startTime = wperp.timeFormat( this.feed.start_date ),
                        endDate = wperp.dateFormat( this.feed.end_date, 'j F' ),
                        endTime = wperp.timeFormat( this.feed.end_date );


                    if ( this.feed.extra.all_day == 'true' ) {
                        if ( wperp.dateFormat( this.feed.start_date, 'Y-m-d' ) == wperp.dateFormat( this.feed.end_date, 'Y-m-d' ) ) {
                            var datetime = startDate;
                        } else {
                            var datetime = startDate + ' to ' + endDate;
                        }
                    } else {
                        if ( wperp.dateFormat( this.feed.start_date, 'Y-m-d' ) == wperp.dateFormat( this.feed.end_date, 'Y-m-d' ) ) {
                            var datetime = startDate + ' at ' + startTime + ' to ' + endTime;
                        } else {
                            var datetime = startDate + ' at ' + startTime + ' to ' + endDate + ' at ' + endTime;
                        }
                    }
                }

                return datetime;
            }
        }
    });

    Vue.component( 'tasks-component', {
        props: [ 'i18n', 'feed' ],

        mixins: [ TimilineMixin ],

        data: function() {
            return {
                headerText: '',
            }
        },

        template: '#erp-crm-timeline-feed-task-note',

        computed: {
            headerText: function() {
                return this.i18n.taskHeaderText ?
                        this.i18n.taskHeaderText
                        .replace( '{{createdUserName}}', this.createdUserName )
                        .replace( '{{createdForUser}}', this.createdForUser )
                        : '';
            },

            countUser: function () {
                if (!this.feed.extra.invited_user) {
                    return 0;
                }
                var count = this.feed.extra.invited_user.length;
                return ( count <= 1 ) ? count : count + ' peoples';
            },

            invitedSingleUser: function() {
                if ( this.feed.extra.invited_user[0].id == wpCRMvue.current_user_id ) {
                    return this.i18n.yourself;
                } else {
                    return this.feed.extra.invited_user[0].name;
                }
            },

            invitedUser: function() {
                var self = this;
                return this.feed.extra.invited_user.map( function( elm ) {
                    if ( elm.id == wpCRMvue.current_user_id ) {
                            return self.i18n.yourself;
                    } else {
                        return elm.name;
                    }
                } ).join("<br>");
            },

            createdUserImg: function() {
                return this.feed.created_by.avatar;
            },

            createdUserName: function() {
                return ( this.feed.created_by.ID == wpCRMvue.current_user_id ) ? this.i18n.you : this.feed.created_by.display_name;
            },

            createdForUser: function() {
                return _.contains( this.feed.contact.types, 'company' ) ? this.feed.contact.company : this.feed.contact.first_name + ' ' + this.feed.contact.last_name;
            },

            datetime: function() {
                var datetime;
                startDate = wperp.dateFormat( this.feed.start_date, 'j F' ),
                startTime = wperp.timeFormat( this.feed.start_date ),
                datetime = startDate + ' at ' + startTime;
                return datetime;
            }
        }

    } );

})(jQuery, window.wpErpVue );

