window.wpErpVue = window.wpErpVue || {};

;(function($, erpVue ) {

    var erp = erpVue || {};

    erp.ToolTip = {
        props: ['title', 'content' ],
        template: '<span class="time erp-tips" v-tiptip data-title="{{ title }}">{{{ content }}}</span>',
    };

    erp.TimeLineHeader = {
        props: [ 'feed' ],

        template: '<span class="timeline-feed-avatar">'
                        + '<img v-bind:src="createdUserImg">'
                    +'</span>'
                    +'<span class="timeline-feed-header-text">'
                        +'<strong v-if="!isRepliedEmail">{{createdUserName}} </strong>'
                        +'<strong v-if="isRepliedEmail">{{createdForUser}} </strong>'
                        +'<span v-if="isNote">created a note for <strong>{{ createdForUser }}</strong></span>'
                        +'<span v-if="isEmail">sent an email to <strong>{{ createdForUser }}</strong></span>'
                        +'<span v-if="isRepliedEmail">replied to <strong>{{ createdUserName }}r</strong> email</span>'
                        +'<span v-if="isLog">'
                            +'logged {{ logType }} on {{ logDateTime | formatDateTime }} for <strong>{{ createdForUser }}</strong>'
                            +' <span v-if="countUser == 1">and <strong>{{ feed.extra.invited_user[0].name }}</strong></span>'
                            +'<span v-if="( countUser != 0 ) && countUser != 1"> and <strong><tooltip :content="countUser" :title="invitedUser"></tooltip></strong></span>'
                        +'</span>'
                        +'<span v-if="isSchedule">'
                            +'have scheduled {{ logType }} with '
                            +'<strong>{{ createdForUser }}</strong>'
                                +' <span v-if="countUser == 1">and <strong>{{ invitedSingleUser }}</strong></span>'
                            +'<span v-if="( countUser != 0 ) && countUser != 1"> and <strong><tooltip :content="countUser" :title="invitedUser"></tooltip></strong></span>'
                        +'</span>'
                        +'<span v-if="isTasks">created a task for </strong>'
                            +' <span v-if="countUser == 1"><strong>{{ invitedSingleUser }}</strong></span>'
                            +'<span v-if="( countUser != 0 ) && countUser != 1"><strong><tooltip :content="countUser" :title="invitedUser"></tooltip></strong></span>'
                        +'</span>'
                        + wpCRMvue.timeline_feed_header
                    +'</span>',

        components: {
            'tooltip' : erp.ToolTip
        },

        computed: {

            countUser: function () {
                var count = this.feed.extra.invited_user.length;
                if ( this.feed.type == 'tasks' ) {
                    return ( count <= 1 ) ? count : count + ' peoples';
                } else {
                    return ( count <= 1 ) ? count : count + ' others';
                }
            },

            invitedSingleUser: function() {
                if ( this.feed.extra.invited_user[0].id == wpCRMvue.current_user_id ) {
                    if ( this.feed.type == 'tasks' ) {
                        return 'Yourself';
                    }
                    return 'You';
                } else {
                    return this.feed.extra.invited_user[0].name;
                }
            },

            invitedUser: function() {
                var self = this;
                return this.feed.extra.invited_user.map( function( elm ) {
                    if ( elm.id == wpCRMvue.current_user_id ) {
                        if ( self.feed.type == 'tasks' ) {
                            return 'Yourself';
                        }
                        return 'You';
                    } else {
                        return elm.name;
                    }
                } ).join("<br>");
            },

            isNote: function() {
                return ( this.feed.type == 'new_note' );
            },

            isTasks: function() {
                return ( this.feed.type == 'tasks' );
            },

            isEmail: function() {
                return ( this.feed.type == 'email' ) && this.feed.extra.replied != 1;
            },

            isRepliedEmail: function() {
                return ( this.feed.type == 'email' ) && this.feed.extra.replied == 1;
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
                return _.contains( this.feed.contact.types, 'company' ) ? this.feed.contact.company : this.feed.contact.first_name + ' ' + this.feed.contact.last_name;
            },

            logType: function() {
                return ( this.feed.log_type == 'sms' || this.feed.log_type == 'email' ) ? 'an ' + this.feed.log_type : 'a ' + this.feed.log_type;
            },

            logDateTime: function() {
                return this.feed.start_date;
            }
        }
    };

    erp.TimeLineBody = {
        props: [ 'feed' ],

        template: '<div class="timeline-email-subject" v-if="isEmail || isRepliedEmail || ( isLog && islogTypeEmail )">Subject : {{feed.email_subject }}</div>'
                  + '<div class="timeline-email-subject" v-if="isSchedule"><i class="fa fa-bookmark"></i> &nbsp; {{ feed.extra.schedule_title }}  &nbsp;|&nbsp;  <i class="fa fa-calendar-check-o"></i> &nbsp;{{ datetime }}</div>'
                  + '<div class="timeline-email-subject" v-if="isTasks"><i class="fa fa-bookmark"></i> &nbsp; {{ feed.extra.task_title }} &nbsp;|&nbsp;  <i class="fa fa-check-square-o"></i> &nbsp;Task Date : {{ datetime }}</div>'
                  + '<div class="timeline-email-body" v-if="isAll">{{{ feed.message }}}</div>'
                  + wpCRMvue.timeline_feed_body,

        computed: {

            isNote: function() {
                return ( this.feed.type == 'new_note' );
            },

            isTasks: function() {
                return ( this.feed.type == 'tasks' );
            },

            isEmail: function() {
                return ( this.feed.type == 'email' ) && this.feed.extra.replied != 1;
            },

            isRepliedEmail: function() {
                return ( this.feed.type == 'email' ) && this.feed.extra.replied == 1;
            },

            isLog: function() {
                return ( this.feed.type == 'log_activity' ) && !( new Date() < new Date( this.feed.start_date ) );
            },

            isSchedule: function() {
                return ( this.feed.type == 'log_activity' ) && ( new Date() < new Date( this.feed.start_date ) );
            },

            islogTypeEmail: function() {
                return this.feed.log_type == 'email';
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

                if ( this.isTasks ) {
                    startDate = wperp.dateFormat( this.feed.start_date, 'j F' ),
                    startTime = wperp.timeFormat( this.feed.start_date ),
                    datetime = startDate + ' at ' + startTime;
                }

                return datetime;
            },

            isAll: function() {
                return this.isNote || this.isTasks || this.isEmail || this.isRepliedEmail || this.isLog || this.isSchedule;
            }
        }
    };

})(jQuery, window.wpErpVue );

