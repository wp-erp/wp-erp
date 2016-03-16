
<?php
$contacts = erp_crm_get_contact_dropdown();
?>
<div class="wrap erp erp-crm-customer erp-single-customer" id="wp-erp">

    <h2><?php _e( 'Activities', 'wp-erp' ); ?></h2>

    <div class="erp-customer-feeds" id="erp-customer-feeds" v-cloak>

        <div class="activity-filter">

            <div class="filters">
                <select style="width:180px;" v-selecttwo="filterActivityType" class="select2" v-model="filterActivityType" id="activity-type">
                    <option value=""><?php _e( 'All Types', 'wp-erp' ) ?></option>
                    <option value="new_note"><?php _e( 'Notes', 'wp-erp' ) ?></option>
                    <option value="email"><?php _e( 'Email', 'wp-erp' ) ?></option>
                    <option value="log_activity"><?php _e( 'Log Activity', 'wp-erp' ) ?></option>
                    <option value="schedule"><?php _e( 'Schedules', 'wp-erp' ) ?></option>
                </select>
            </div>

            <div class="filters">
                <select style="width:180px;" v-selecttwo="filterCreatedBy" class="select2" v-model="filterCreatedBy" id="activity-created-by">
                    <option value=""><?php _e( 'Created By All', 'wp-erp' ) ?></option>
                    <?php echo erp_crm_get_emplyees(); ?>
                </select>
            </div>

            <div class="filters">
                <select style="width:180px;" v-selecttwo="filterCreatedFor" class="select2" v-model="filterCreatedFor" id="activity-created-for">
                    <option value=""><?php _e( 'Created for all', 'wp-erp' ) ?></option>
                    <?php foreach ( $contacts as $contact_id => $contact_name ) : ?>
                        <option value="<?php echo $contact_id; ?>"><?php echo $contact_name; ?></option>
                    <?php endforeach ?>
                </select>
            </div>

            <div class="filters">
                <input type="text" v-datepicker v-model="filterCreatedOn" placeholder="<?php _e( 'Created Date..', 'wp-erp' ); ?>">
            </div>

            <div class="clearfix"></div>
        </div>

        <div class="activity-content">

            <ul class="timeline" v-if = "feeds.length">

                <template v-for="( month, feed_obj ) in feeds | filterFeedType filterActivityType | formatFeeds">

                    <li class="time-label">
                        <span class="bg-red">{{ month | formatDate 'F, Y' }}</span>
                    </li>

                    <li v-for="feed in feed_obj">

                        <i v-if="feed.type == 'email'" class="fa fa-envelope-o" @click.prevent="toggleFooter"></i>
                        <i v-if="feed.type == 'new_note'" class="fa fa-file-text-o" @click.prevent="toggleFooter"></i>
                        <i v-if="feed.type == 'log_activity'" class="fa fa-list" @click.prevent="toggleFooter"></i>
                        <i v-if="( feed.type == 'log_activity' && isSchedule( feed.start_date )  )" class="fa fa-calendar-check-o" @click.prevent="toggleFooter"></i>

                        <timeline-item :feed="feed" disbale-footer="true"></timeline-item>

                    </li>

                </template>

            </ul>

            <div class="feed-load-more" v-if="feeds.length > limit">
                <button @click="loadMoreContent( feeds )" class="button">
                    <i class="fa fa-cog fa-spin" v-if="loading"></i>
                    &nbsp;<span v-if="!loading"><?php _e( 'Load More', 'wp-erp' ); ?></span>
                    &nbsp;<span v-else><?php _e( 'Loading..', 'wp-erp' ); ?></span>
                </button>
            </div>

            <div class="no-activity-found" v-if="!feeds.length">
                <?php _e( 'No Activity found for this Contact', 'wp-erp' ); ?>
            </div>
        </div>
    </div>

</div>
