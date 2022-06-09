<i class="fa fa-file-text-o"></i>

<div class="timeline-item" id="timeline-item-{{ feed.id }}" v-if="!isEditable">
    <tooltip content="<i class='fa fa-clock-o'></i>" :title="feed.created_at | formatDateTime"></tooltip>

    <h3 class="timeline-header" @click.prevent="toggleFooter">
        <span class="timeline-feed-avatar">
            <img v-bind:src="createdUserImg">
        </span>
        <span class="timeline-feed-header-text">
            {{{ headerText }}}
        </span>
    </h3>

    <div class="timeline-body" @click="toggleFooter">
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
            <new-note v-if="feed.type == 'new_note'" :feed="feed"></new-note>
        </form>
    </div>
</div>
