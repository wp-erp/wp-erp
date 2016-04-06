<div class="timeline-item" id="timeline-item-{{ feed.id }}" v-if="!isEditable && !isReplied">
    <tooltip content="<i class='fa fa-clock-o'></i>" :title="feed.created_at | formatDateTime"></tooltip>
    <tooltip v-if="emailViewedTime" content="<i class='fa fa-eye'></i>" :title="emailViewedTime"></tooltip>

    <h3 class="timeline-header" @click.prevent="toggleFooter">
        <timeline-header :feed="feed"></timeline-header>
    </h3>

    <div class="timeline-body" @click="toggleFooter">
        <timeline-body :feed="feed"></timeline-body>
    </div>

    <div class="timeline-footer" v-if="(feed.type != 'email') && showFooter">
        <a href="#" @click.prevent="editFeed( feed )"><?php _e( 'Edit', 'erp' ); ?> |</a>
        <a href="#" @click.prevent="deleteFeed( feed )"><?php _e( 'Delete', 'erp' ); ?></a>
    </div>

    <div class="timeline-footer" v-if="(feed.type == 'email') && (feed.extra.replied == 1 ) && showFooter">
        <a href="#" @click.prevent="replyEmailFeed( feed )"><?php _e( 'Reply', 'erp' ); ?></a>
    </div>

</div>

<div class="timeline-item" id="timeline-item-{{ feed.id }}" v-if="isEditable || isReplied">

    <span class="time cancel-timeline-feed-edit" @click.prevent="cancelUpdate"><i class="fa fa-times"></i></span>

    <h3 class="timeline-header" @click.prevent="toggleFooter" v-if="!isReplied">
        <i class="fa fa-pencil-square-o"></i> <?php _e( 'Edit this feed', 'erp' ); ?>
    </h3>

    <h3 class="timeline-header" @click.prevent="toggleFooter" v-if="isReplied">
        <i class="fa fa-reply"></i> <?php _e( 'Reply this email', 'erp' ); ?>
    </h3>

    <div class="timeline-body">
        <form action="" method="post" @submit.prevent = "updateCustomerFeed( feed.id )" id="erp-crm-activity-edit-feed-form">

            <new-note v-if="feed.type == 'new_note'" :feed="feed"></new-note>

            <log-activity :feed="feed" v-if="( feed.type == 'log_activity' && !isSchedule( feed.start_date ) )"></log-activity>

            <schedule-note :feed="feed" v-if="( feed.type == 'log_activity' && isSchedule( feed.start_date ) )"></schedule-note>

            <tasks-note :feed="feed" v-if="feed.type == 'tasks'"></tasks-note>

            <email-note :feed="feed" v-if="isReplied"></email-note>

            <?php do_action( 'erp_crm_edit_customer_feeds_nav_content' ); ?>

        </form>
    </div>
</div>

<?php do_action( 'erp_crm_customer_after_timline_feed_item' ); ?>


