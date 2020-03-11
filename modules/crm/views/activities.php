
<?php
$feeds_tab = erp_crm_get_customer_feeds_nav();
$crm_users = erp_crm_get_crm_user();
?>
<div class="wrap erp erp-crm-activities erp-single-customer" id="wp-erp">

    <h2><?php esc_attr_e( 'Activities', 'erp' ); ?></h2>
    <hr>
    <div class="erp-customer-feeds" id="erp-customer-feeds" v-cloak>

        <div class="activity-filter" id="erp-crm-activities-filter">

            <div class="filters">
                <select style="width:180px;" v-selecttwo="filterFeeds.type" class="select2" v-model="filterFeeds.type" id="activity-type" data-placeholder="<?php esc_attr_e( 'Select a type', 'erp' ) ?>">
                    <option value=""><?php esc_attr_e( 'All Types', 'erp' ) ?></option>
                    <?php foreach ( $feeds_tab as $key => $value ) : ?>
                        <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $value['title'] ); ?></option>
                    <?php endforeach ?>
                </select>
            </div>

            <?php if ( ! current_user_can( 'erp_crm_agent' ) ) : ?>
            <div class="filters">
                <select style="width:260px;" v-selecttwo="filterFeeds.created_by" class="select2" v-model="filterFeeds.created_by" id="activity-created-by" data-placeholder="<?php esc_attr_e( 'Created by..', 'erp' ) ?>">
                    <option value="-1"><?php esc_attr_e( 'All', 'erp' ) ?></option>
                    <?php foreach ( $crm_users as $crm_user ) : ?>
                        <option value="<?php echo esc_attr( $crm_user->ID ) ?>"><?php echo esc_attr( $crm_user->display_name ); ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <?php endif ?>

            <div class="filters">
                <select style="width:260px;" v-selecttwo="filterFeeds.customer_id" data-types="contact,company"  class="erp-crm-contact-list-dropdown" v-model="filterFeeds.customer_id" id="activity-created-for"  data-placeholder="<?php esc_attr_e( 'Created for contact or company ..', 'erp' ) ?>">
                    <option value=""></option>
                </select>
            </div>

            <div class="filters">
                <input type="search" v-datepicker v-model="filterFeeds.created_at" placeholder="<?php esc_attr_e( 'Created Date..', 'erp' ); ?>">
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
                        <?php do_action('erp_add_custom_content_after_feed', '{{ feed.contact.id }}' );?>
                    </li>

                </template>
            </ul>

            <div class="feed-load-more" v-show="( feeds.length >= limit ) && !loadingFinish">
                <button @click="loadMoreContent( feeds )" class="button">
                    <i class="fa fa-cog fa-spin" v-if="loading"></i>
                    &nbsp;<span v-if="!loading"><?php esc_attr_e( 'Load More', 'erp' ); ?></span>
                    &nbsp;<span v-else><?php esc_attr_e( 'Loading..', 'erp' ); ?></span>
                </button>
            </div>

            <div class="no-activity-found" v-if="!feeds.length">
                <?php esc_attr_e( 'No Activity found for this Contact', 'erp' ); ?>
            </div>

        </div>
    </div>

</div>
