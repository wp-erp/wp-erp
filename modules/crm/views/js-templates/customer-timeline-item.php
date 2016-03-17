<div class="timeline-item" id="timeline-item-{{ feed.id }}" v-if="!isEditable">
    <tooltip content="<i class='fa fa-clock-o'></i>" :title="feed.created_at | formatDateTime"></tooltip>

    <h3 class="timeline-header" @click.prevent="toggleFooter">
        <timeline-header :feed="feed"></timeline-header>
    </h3>

    <div class="timeline-body" @click="toggleFooter">
        {{{ feed.message | formatFeedContent feed }}}
    </div>
    <div class="timeline-footer" v-if="showFooter" v-if="(feed.type != 'email')">
        <a href="#" @click.prevent="editFeed( feed )"><?php _e( 'Edit', 'wp-erp' ); ?> |</a>
        <a href="#" @click.prevent="deleteFeed( feed )"><?php _e( 'Delete', 'wp-erp' ); ?></a>
    </div>

</div>

<div class="timeline-item" id="timeline-item-{{ feed.id }}" v-if="isEditable">

    <span class="time cancel-timeline-feed-edit" @click.prevent="cancelUpdate"><i class="fa fa-times"></i></span>

    <h3 class="timeline-header" @click.prevent="toggleFooter">
        <i class="fa fa-pencil-square-o"></i> <?php _e( 'Edit this feed', 'wp-erp' ); ?>
    </h3>

    <div class="timeline-body">
        <form action="" method="post" @submit.prevent = "updateCustomerFeed( feed.id )" id="erp-crm-activity-edit-feed-form">

            <new-note v-if="feed.type == 'new_note'" :feed="feed"></new-note>

            <log-activity :feed="feed" v-if="( feed.type == 'log_activity' && !isSchedule( feed.start_date ) )"></log-activity>

            <schedule-note :feed="feed" v-if="( feed.type == 'log_activity' && isSchedule( feed.start_date ) )"></schedule-note>

            <tasks-note :feed="feed" v-if="feed.type == 'tasks'"></tasks-note>

        </form>
    </div>
</div>
