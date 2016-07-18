<?php

/**
 * Return contact feeds localize strings
 *
 * @since 1.1.2
 *
 * @return array
 */
function erp_crm_get_contact_feeds_localize_string() {
    return [
        'you'                       => __( 'You', 'erp' ),
        'edit'                      => __( 'Edit', 'erp' ),
        'delete'                    => __( 'Delete', 'erp' ),
        'an'                        => __( 'an', 'erp' ),
        'a'                         => __( 'a', 'erp' ),
        'others'                    => __( 'others', 'erp' ),
        'editThisFeed'              => __( 'Edit this feed', 'erp' ),
        'newNoteHeadertext'         => sprintf( __( '<strong>%1s</strong> created a note for <strong>%2s</strong>', 'erp' ), '{{createdUserName}}', '{{createdForUser}}' ),
        'emailSubject'              => __( 'Subject', 'erp' ),
        'reply'                     => __( 'Reply', 'erp' ),
        'replyThisEmail'            => __( 'Reply this email', 'erp' ),
        'viewdOn'                   => sprintf( __( 'Viewd on %s', 'erp' ), '{{viewdOn}}' ),
        'emailHeadertext'           => sprintf( __( '<strong>%1s</strong> sent an email to <strong>%2s</strong>', 'erp' ), '{{createdUserName}}', '{{createdForUser}}' ),
        'replyEmailHeadertext'      => sprintf( __( '<strong>%1s</strong> replied to <strong>%2s</strong>', 'erp' ), '{{createdForUser}}', '{{createdUserName}}' ),
        'logHeaderText'             => sprintf( __( 'You logged %1s on %2s for <strong>%3s</strong>', 'erp' ), '{{logType}}', '{{logDateTime}}', '{{createdForUser}}' ),
        'logHeaderTextSingleUser'   => sprintf( __( 'You logged %1s on %2s for <strong>%3s</strong> and <strong>%4s</strong>', 'erp' ), '{{logType}}', '{{logDateTime}}', '{{createdForUser}}', '{{otherUser}}' ),
    ];
}
