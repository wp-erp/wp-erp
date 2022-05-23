<i v-if="isLog" class="fa fa-list"></i>
<i v-if="isSchedule" class="fa fa-calendar-check-o"></i>

<div class="timeline-item" id="timeline-item-{{ feed.id }}" v-if="!isEditable">
    <tooltip content="<i class='fa fa-clock-o'></i>" :title="feed.created_at | formatDateTime"></tooltip>

    <h3 class="timeline-header" @click.prevent="toggleFooter">
        <span class="timeline-feed-avatar">
            <img v-bind:src="createdUserImg">
        </span>
        <span class="timeline-feed-header-text">
            <template v-if="isLog">{{{ headerText }}}</template>
            <template v-if="isSchedule">{{{ headerScheduleText }}}</template>
            <span v-if="( countUser != 0 && countUser != 1 )"> <?php esc_html_e( 'and', 'erp' ); ?> <strong><tooltip :content="countUser" :title="invitedUser"></tooltip></strong></span>
        </span>
    </h3>

    <div class="timeline-body" @click="toggleFooter">
        <div class="timeline-email-subject" v-if="isLog && islogTypeEmail">{{i18n.emailSubject}} : {{feed.email_subject}}</div>
        <div class="timeline-email-subject" v-if="isSchedule"><i class="fa fa-bookmark"></i> &nbsp; {{ feed.extra.schedule_title }}  &nbsp;|&nbsp;  <i class="fa fa-calendar-check-o"></i> &nbsp;{{ datetime }}</div>
        <div class="timeline-email-body">{{{ feed.message }}}</div>

        <div class="timeline-activity-attachments" v-if="(feed.extra.attachments && feed.extra.attachments.length > 0)">
            <?php esc_attr_e( 'Attachments : ', 'erp' ); ?>
            <ul>
                <li v-for="file in feed.extra.attachments"><a target="_blank" href="{{{file.url}}}">{{{file.name}}}</a></li>
            </ul>
        </div>
    </div>

    <div class="timeline-footer" v-if="!isActivityPage() && showFooter">
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
            <log-activity :feed="feed" v-if="isLog"></log-activity>
            <schedule-note :feed="feed" v-if="isSchedule"></schedule-note>
        </form>
    </div>
</div>
