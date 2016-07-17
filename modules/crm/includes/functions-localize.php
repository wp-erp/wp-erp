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
        'you'        => __( 'You', 'erp' ),
        'edit'        => __( 'Edit', 'erp' ),
        'delete'        => __( 'Delete', 'erp' ),
        'editThisFeed'        => __( 'Edit this feed', 'erp' ),
        'headertext' => sprintf( __( '<strong>%1s</strong> created a note for <strong>%2s</strong>', 'erp' ), '{{createdUserName}}', '{{createdForUser}}' ),
    ];
}
