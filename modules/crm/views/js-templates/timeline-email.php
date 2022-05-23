<i v-if="( feed.extra.replied != 1 )" class="fa fa-envelope-o"></i>
<i v-if="( feed.extra.replied == 1 )" class="fa fa-reply"></i>

<div class="timeline-item" id="timeline-item-{{ feed.id }}" v-if="!isReplied">
    <tooltip content="<i class='fa fa-clock-o'></i>" :title="feed.created_at | formatDateTime"></tooltip>
    <tooltip v-if="emailViewedTime" content="<i class='fa fa-eye'></i>" :title="emailViewedTime"></tooltip>

    <h3 class="timeline-header" @click.prevent="toggleFooter">
        <span class="timeline-feed-avatar">
            <img v-bind:src="createdUserImg">
        </span>
        <span class="timeline-feed-header-text">
            {{{headerText}}}
        </span>
    </h3>

    <div class="timeline-body" @click="toggleFooter">
        <div class="timeline-email-subject">{{i18n.emailSubject}} : {{feed.email_subject}}</div>
        <div class="timeline-email-body">{{{feed.message}}}</div>
        <div class="timeline-activity-attachments" v-if="(feed.extra.attachments && feed.extra.attachments.length > 0)">
            <?php esc_html_e( 'Attachments : ', 'erp' ); ?>
            <ul>
                <li v-for="file in feed.extra.attachments"><a target="_blank" href="{{{file.url}}}">{{{file.name}}}</a></li>
            </ul>
        </div>
    </div>

    <div class="timeline-footer" v-if="(feed.extra.replied == 1 ) && showFooter && !isActivityPage()">
        <a href="#" @click.prevent="replyEmailFeed( feed )">{{i18n.reply}}</a>
    </div>
</div>

<div class="timeline-item" id="timeline-item-{{ feed.id }}" v-if="isReplied">

    <span class="time cancel-timeline-feed-edit" @click.prevent="cancelUpdate"><i class="fa fa-times"></i></span>

    <h3 class="timeline-header">
        <i class="fa fa-reply"></i> {{i18n.replyThisEmail}}
    </h3>

    <div class="timeline-body">
        <form action="" method="post" @submit.prevent = "updateCustomerFeed( feed.id )" id="erp-crm-activity-edit-feed-form">
            <email-note :feed="feed" v-if="isReplied"></email-note>
        </form>
    </div>
</div>
