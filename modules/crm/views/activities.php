
<?php
$feeds_tab = erp_crm_get_customer_feeds_nav();
$crm_users = erp_crm_get_crm_user();
?>
<div class="wrap erp erp-crm-activities erp-single-customer" id="wp-erp">

    <h2><?php _e( 'Activities', 'erp' ); ?></h2>
    <hr>
    <div class="erp-customer-feeds" id="erp-customer-feeds" v-cloak>

        <div class="activity-filter" id="erp-crm-activities-filter">

            <div class="filters">
                <select style="width:180px;" v-selecttwo="filterFeeds.type" class="select2" v-model="filterFeeds.type" id="activity-type" data-placeholder="<?php _e( 'Select a type', 'erp' ) ?>">
                    <option value=""><?php _e( 'All Types', 'erp' ) ?></option>
                    <?php foreach ( $feeds_tab as $key => $value ) : ?>
                        <option value="<?php echo $key; ?>"><?php echo $value['title']; ?></option>
                    <?php endforeach ?>
                </select>
            </div>

            <div class="filters">
                <select style="width:260px;" v-selecttwo="filterFeeds.created_by" class="select2" v-model="filterFeeds.created_by" id="activity-created-by" data-placeholder="<?php _e( 'Created by..', 'erp' ) ?>">
                    <option value="-1"><?php _e( 'All', 'erp' ) ?></option>
                    <?php foreach ( $crm_users as $crm_user ) : ?>
                        <option value="<?php echo $crm_user->ID ?>"><?php echo $crm_user->display_name; ?></option>
                    <?php endforeach ?>
                </select>
            </div>

            <div class="filters">
                <select style="width:260px;" v-selecttwo="filterFeeds.customer_id" data-types="contact,company"  class="erp-crm-contact-list-dropdown" v-model="filterFeeds.customer_id" id="activity-created-for"  data-placeholder="<?php _e( 'Created for..', 'erp' ) ?>">
                    <option value=""></option>
                </select>
            </div>

            <div class="filters">
                <input type="search" v-datepicker v-model="filterFeeds.created_at" placeholder="<?php _e( 'Created Date..', 'erp' ); ?>">
            </div>

            <div class="clearfix"></div>
        </div>

        <div class="activity-content">

            <ul class="timeline" v-if = "feeds.length">

                <template v-for="( month, feed_obj ) in feeds | formatFeeds">

                    <li class="time-label">
                        <span class="bg-red">{{ month | formatDate 'F, Y' }}</span>
                    </li>

                    <li v-for="feed in feed_obj">
                        <timeline-feed :i18n="i18n" :is="loadTimelineComponent( feed.type )" :feed="feed"></timeline-feed>
                    </li>

                </template>
            </ul>

            <div class="feed-load-more" v-show="( feeds.length >= limit ) && !loadingFinish">
                <button @click="loadMoreContent( feeds )" class="button">
                    <i class="fa fa-cog fa-spin" v-if="loading"></i>
                    &nbsp;<span v-if="!loading"><?php _e( 'Load More', 'erp' ); ?></span>
                    &nbsp;<span v-else><?php _e( 'Loading..', 'erp' ); ?></span>
                </button>
            </div>

            <div class="no-activity-found" v-if="!feeds.length">
                <?php _e( 'No Activity found for this Contact', 'erp' ); ?>
            </div>

        </div>
    </div>

</div>
