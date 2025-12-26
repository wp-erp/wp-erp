<?php

/**
 * Return contact feeds localize strings
 *
 * @since 1.1.2
 *
 * @return array
 */
function erp_crm_get_contact_feeds_localize_string() {
    return apply_filters( 'erp_crm_contact_feed_localize_string', [
        'you'                          => __( 'You', 'erp' ),
        'edit'                         => __( 'Edit', 'erp' ),
        'delete'                       => __( 'Delete', 'erp' ),
        'an'                           => __( 'an', 'erp' ),
        'a'                            => __( 'a', 'erp' ),
        'others'                       => __( 'others', 'erp' ),
        'editThisFeed'                 => __( 'Edit this feed', 'erp' ),
        // translators: %1$s is the creator user name, %2$s is the user for whom note was created
        'newNoteHeadertext'            => sprintf(__('<strong>%1$s</strong> created a note for <strong>%2$s</strong>', 'erp'), '{{createdUserName}}', '{{createdForUser}}'),
        'emailSubject'                 => __( 'Subject', 'erp' ),
        'reply'                        => __( 'Reply', 'erp' ),
        'replyThisEmail'               => __( 'Reply this email', 'erp' ),
        // translators: %s is the timestamp when email was viewed
        'viewedAt'                     => sprintf( __( 'Viewed at: %s', 'erp' ), '{{ emailViewedTime }}' ),
        // translators: %1$s is the creator user name, %2$s is the recipient user name
        'emailHeadertext'              => sprintf(__('<strong>%1$s</strong> sent an email to <strong>%2$s</strong>', 'erp'), '{{createdUserName}}', '{{createdForUser}}'),
        // translators: %1$s is the user who replied, %2$s is the original sender
        'replyEmailHeadertext'         => sprintf(__('<strong>%1$s</strong> replied to <strong>%2$s</strong>', 'erp'), '{{createdForUser}}', '{{createdUserName}}'),
        // translators: %1$s is the user name, %2$s is the log type, %3$s is the date time, %4$s is the user name for whom log was created
        'logHeaderText'                => sprintf(__('<strong>%1$s</strong> logged %2$s on %3$s for <strong>%4$s</strong>', 'erp'), '{{createdUserName}}', '{{logType}}', '{{logDateTime}}', '{{createdForUser}}'),
        // translators: %1$s is the user name, %2$s is the schedule type, %3$s is the user name with whom schedule was made
        'scheduleHeaderText'           => sprintf(__('<strong>%1$s</strong> have scheduled %2$s with <strong>%3$s</strong>', 'erp'), '{{createdUserName}}', '{{logType}}', '{{createdForUser}}'),
        // translators: %1$s is the user name, %2$s is the log type, %3$s is the date time, %4$s is the first user name, %5$s is the second user name
        'logHeaderTextSingleUser'      => sprintf(__('<strong>%1$s</strong> logged %2$s on %3$s for <strong>%4$s</strong> and <strong>%5$s</strong>', 'erp'), '{{createdUserName}}', '{{logType}}', '{{logDateTime}}', '{{createdForUser}}', '{{otherUser}}'),
        // translators: %1$s is the user name, %2$s is the schedule type, %3$s is the first user name, %4$s is the second user name
        'scheduleHeaderTextSingleUser' => sprintf(__('<strong>%1$s</strong> have scheduled %2$s with <strong>%3$s</strong> and <strong>%4$s</strong>', 'erp'), '{{createdUserName}}', '{{logType}}', '{{createdForUser}}', '{{otherUser}}'),
        'taskDate'                     => __( 'Task Date', 'erp' ),
        // translators: %1$s is the user name who created the task
        'taskHeaderText'               => sprintf(__('<strong>%1$s</strong> created a task for', 'erp'), '{{createdUserName}}'),
    ] );
}
