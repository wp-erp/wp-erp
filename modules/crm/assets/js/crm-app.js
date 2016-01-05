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
    var month = date.getMonth(),
        day   = date.getDate(),
        year  = date.getFullYear(),
        monthArray = [ "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ],
        monthShortArray = [ "Jan", "Feb", "Mar", "Apr", "May", "June", "July", "Aug", "Sept", "Oct", "Nov", "Dec" ],
        monthName = monthArray[date.getMonth()],
        monthShortName = monthShortArray[date.getMonth()];

    var pattern = {
        Y: year,
        m: (month+1),
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

// Vue filter for formatting Feed Header content
Vue.filter('formatFeedHeader', function ( feed ) {
    var header;
    var createdName = ( feed.created_by.ID == wpCRMvue.current_user_id ) ? 'You' : feed.created_by.display_name;
    var createdFor = ( feed.contact.type == 'company' ) ? feed.contact.company : feed.contact.first_name + ' ' + feed.contact.last_name;

    switch( feed.type ) {
        case 'new_note':
            header = '<span class="timeline-feed-avatar"><img src="'+ feed.created_by.avatar + '"></span><span class="timeline-feed-header-text"><strong>' + createdName + '</strong> cerated a note for <strong>' + createdFor + '</strong></span>';
            break;

        case 'email':
            header = '<span class="timeline-feed-avatar"><img src="'+ feed.created_by.avatar + '"></span><span class="timeline-feed-header-text"><strong>' + createdName + '</strong> sent a email to <strong>' + createdFor + '</strong></span>';
            break;

        case 'log_activity':
            var logType = ( feed.log_type == 'sms' || feed.log_type == 'email' ) ? 'an ' + feed.log_type : 'a ' + feed.log_type;
            header = '<span class="timeline-feed-avatar"><img src="'+ feed.created_by.avatar + '"></span><span class="timeline-feed-header-text"><strong>' + createdName + '</strong> logged ' + logType + ' on ' +  this.$options.filters.formatDate( feed.log_date, 'F, j' ) + ' @ ' +  this.$options.filters.formatAMPM(  feed.log_date + ' ' + feed.log_time ) + ' for <strong>' + createdFor + '</strong></span>';
            // header = '<span class="timeline-feed-avatar"><img src="'+ feed.created_by.avatar + '"></span><span class="timeline-feed-header-text"><strong>' + createdName + '</strong> created a log for <strong>' + createdFor + '</strong></span>';
            break;
    }

    return header;
});

// Vue filter for formatting Feeds message body as a group by object
Vue.filter( 'formatFeedContent', function ( message, feed ) {

    if ( feed.type == 'email') {
        message = '<div class="timeline-email-subject">Subject : ' + feed.email_subject + '</div>' +
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

// Vue directive for Date picker
Vue.directive( 'datepicker', {
    bind: function () {
        var vm = this.vm;
        var key = this.expression;

        jQuery(this.el).datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
            yearRange: '-100:+0',
            onSelect: function (date) {
                vm.$set(key, date);
            }
        });
    },
    update: function (val) {
        jQuery(this.el).datepicker('setDate', val);
    }
});


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
        feedData : { 'message' : '' },
        isValid: false,
        customer_id : null,
        dt: '',
        showFooter: false
    },

    compiled: function() {
        this.fetchFeeds()
        this.dt = this.currentDate()
    },

    methods: {

        toggleFooter: function( e ) {
            jQuery( e.target ).closest('li').find('.timeline-footer').toggle();
        },

        editFeed: function( feed ) {

            jQuery.erpPopup({
                title: 'Edit Feed',
                button: 'Save',
                id: 'erp-customer-feed-edit',
                content: wperp.template('erp-crm-customer-edit-feed')( feed ).trim(),
                onReady: function () {
                    var modal = this;

                    jQuery('.erp-date-field').datepicker({
                        dateFormat: 'yy-mm-dd',
                        changeMonth: true,
                        changeYear: true,
                        yearRange: '-100:+0',
                    });

                    jQuery( 'select[data-selected]', modal ).each(function() {
                        var self = jQuery(this),
                            selected = self.data('selected');
                        if ( selected !== '' ) {
                            self.val( selected );
                        }
                    });
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
                            alert( error );
                        }
                    });
                }
            });
        },

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

            vm.progreassStart('erp-crm-feed-nav-content');
            this.feedData._wpnonce = wpCRMvue.nonce;

            if ( this.feedData.type == 'log_activity' ) {
                this.feedData.log_date = this.dt;
            };

            jQuery.post( wpCRMvue.ajaxurl, this.feedData, function( resp ) {
                vm.feeds.splice( 0, 0, resp.data );
                document.getElementById("erp-crm-activity-feed-form").reset();
                vm.feedData.dt = this.dt;
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
            NProgress.configure({ parent: '#'+id });
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
    },

    computed: {

        /**
         * Set Datepicker current date
         *
         * @return {[string]} [date string]
         */

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
                    log_time : !!this.feedData.log_time,
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
    }
});

// Bind trix-editor value with v-model message
document.addEventListener('trix-change', function (e) {
    vm.feedData.message = e.path[0].innerHTML;
});