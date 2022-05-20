<i class="fa fa-check-square-o"></i>

<div class="timeline-item" id="timeline-item-{{ feed.id }}" v-if="!isEditable">
    <tooltip content="<i class='fa fa-clock-o'></i>" :title="feed.created_at | formatDateTime"></tooltip>

    <h3 class="timeline-header" @click.prevent="toggleFooter">
        <span class="timeline-feed-avatar">
            <img v-bind:src="createdUserImg">
        </span>
        <span class="timeline-feed-header-text">
            {{{ headerText }}}
            <template v-if="countUser == 1"><strong>{{invitedSingleUser}}</strong></template>
            <span v-if="( countUser != 0 && countUser != 1 )"><strong><tooltip :content="countUser" :title="invitedUser"></tooltip></strong></span>
        </span>
    </h3>

    <div class="timeline-body" @click="toggleFooter">
        <div class="timeline-email-subject" v-if="isTasks"><i class="fa fa-bookmark"></i> &nbsp; {{ feed.extra.task_title }} &nbsp;|&nbsp;  <i class="fa fa-check-square-o"></i> &nbsp;{{ i18n.taskDate }} : {{ datetime }}</div>
        <div class="timeline-email-body">{{{ feed.message }}}</div>
        <div class="timeline-activity-attachments" v-if="(feed.extra.attachments && feed.extra.attachments.length > 0)">
            <?php esc_html_e( 'Attachments : ', 'erp' ); ?>
            <ul>
                <li v-for="file in feed.extra.attachments"><a target="_blank" href="{{{file.url}}}">{{{file.name}}}</a></li>
            </ul>
        </div>
    </div>

    <div class="timeline-footer" v-show="!isActivityPage() && showFooter">
        <a href="#" @click.prevent="editFeed( feed )">{{ i18n.edit }} |</a>
        <a href="#" @click.prevent="deleteFeed( feed )">{{ i18n.delete }}</a>
    </div>
</div>

<div class="timeline-item" id="timeline-item-{{ feed.id }}" v-if="isEditable">

    <span class="time cancel-timeline-feed-edit" @click.prevent="cancelUpdate"><i class="fa fa-times"></i></span>

    <h3 class="timeline-header" @click.prevent="toggleFooter">
        <i class="fa fa-pencil-square-o"></i> {{ i18n.editThisFeed }}
    </h3>

    <div class="timeline-body">
        <form action="" method="post" @submit.prevent = "updateCustomerFeed( feed.id )" id="erp-crm-activity-edit-feed-form">
            <tasks-note :feed="feed"></tasks-note>
        </form>
    </div>
</div>
