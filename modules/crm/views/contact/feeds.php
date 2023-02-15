<?php
global $current_user;
$feeds_tab = erp_crm_get_customer_feeds_nav();
?>
<div class="erp-customer-feeds" id="erp-customer-feeds" v-cloak>
    <input type="hidden" v-model="customer_id" value="<?php echo esc_attr( $customer->id ); ?>" name="customer_id">
    <div class="activity-form">
        <ul class="erp-list list-inline nav-item">
            <?php foreach ( $feeds_tab as $name => $value ) { ?>
                <li :class="'<?php echo esc_attr( $name ); ?>' == tabShow ? 'active': ''">
                    <a href="#<?php echo esc_attr( $name ); ?>" @click.prevent="showTab('<?php echo esc_attr( $name ); ?>')">
                        <?php echo wp_kses_post( sprintf( '%s %s', $value['icon'], esc_attr( $value['title'] ) ) ); ?>
                    </a>
                </li>
            <?php } ?>
        </ul>

        <div class="nav-content" id="erp-crm-feed-nav-content">
            <form action="" method="post" @submit.prevent="addCustomerFeed()" id="erp-crm-activity-feed-form" enctype="multipart/form-data" autocomplete="off">

                <new-note v-if="tabShow == 'new_note'" :feed="" keep-alive></new-note>

                <email-note v-if="tabShow == 'email'"></email-note>

                <log-activity v-if="tabShow == 'log_activity'"></log-activity>

                <schedule-note v-if="tabShow == 'schedule'"></schedule-note>

                <tasks-note v-if="tabShow == 'tasks'"></tasks-note>

                <?php do_action( 'erp_crm_feeds_nav_content' ); ?>

            </form>
        </div>
    </div>

    <div class="activity-content">
        <div class="activity-feed" style="margin-bottom: 10px">
            <select name="" id="" v-model="findFeeds.type">
                <option value="all"><?php esc_html_e( 'All Activities', 'erp' ); ?></option>
                <option value="email"><?php esc_html_e( 'Email', 'erp' ); ?></option>
                <option value="tasks"><?php esc_html_e( 'Task', 'erp' ); ?></option>
                <option value="log_activity"><?php esc_html_e( 'Schedule', 'erp' ); ?></option>
                <option value="new_note"><?php esc_html_e( 'Note', 'erp' ); ?></option>
            </select>
            <input type="text" placeholder="<?php esc_attr_e( 'From', 'erp' ); ?>" class="erp-date-field" v-model="findFeeds.created_from">
            <input type="text" placeholder="<?php esc_attr_e( 'To', 'erp' ); ?>" class="erp-date-field" v-model="findFeeds.created_to">
            <input type="submit" value="<?php esc_attr_e( 'Filter', 'erp' ); ?>" class="button action" @click="searchFeeds">
        </div>
        <ul class="timeline" v-if = "feeds.length">
            <template v-for="( month, feed_obj ) in feeds | formatFeeds">

                <li class="time-label">
                    <span class="bg-red">{{ month | formatDate 'F, Y' }}</span>
                </li>


                <li v-for="feed in feed_obj">
                    <timeline-feed :i18n="i18n" :is="loadTimelineComponent( feed.type )" :feed="feed"></timeline-feed>
                    <?php do_action( 'erp_add_custom_content_after_feed', intval( $_GET['id'] ) ); ?>
                </li>

            </template>
        </ul>
        <div class="feed-load-more" v-show="( feeds.length >= limit ) && !loadingFinish">
            <button @click="loadMoreContent( feeds )" class="button">
                <i class="fa fa-cog fa-spin" v-if="loading"></i>
                &nbsp;<span v-if="!loading"><?php esc_html_e( 'Load More', 'erp' ); ?></span>
                &nbsp;<span v-else><?php esc_html_e( 'Loading..', 'erp' ); ?></span>
            </button>
        </div>

        <div class="no-activity-found" v-if="!feeds.length">
            <?php esc_html_e( 'No Activity found for this Contact', 'erp' ); ?>
        </div>
    </div>
</div>
