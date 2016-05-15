<?php $statuses = erp_crm_customer_get_status_count( 'contact' ); ?>
<div class="wrap erp-crm-customer" id="wp-erp">

    <h2>Contact<a href="#" id="erp-customer-new" class="erp-contact-new add-new-h2" data-type="contact" title="Add New Contact">Add New Contact</a></h2>

    <vtable v-ref:vtable
        wrapper-class="erp-crm-list-table-wrap"
        table-class="customers"
        row-checkbox-id="erp-crm-customer-id-checkbox"
        row-checkbox-name="customer_id"
        action="erp-crm-get-contacts"
        per-page="20"
        :fields=fields
        :item-row-actions=itemRowActions
        :search="search"
        :top-nav-filter="topNavFilter"
        :bulkactions="bulkactions"
    ></vtable>

</div>

<script>
;(function($) {

    var tableColumns = [
        {
            name: 'name',
            title: 'Full Name',
            callback: 'full_name',
            sortField: 'id',
        },
        {
            name: 'email',
            title: 'Email Address'

        },
        {
            name: 'phone',
            title: 'Phone'
        },
        {
            name: 'life_stage',
            title: 'Life stage'
        },
        {
            name: 'created',
            title: 'Created At',
            sortField: 'created'
        }
    ];

    var bulkactions = {

        defaultAction: [
            {
                'id' : 'delete',
                'text' : 'Delete'
            },

            {
                'id' : 'assing_group',
                'text' : 'Assing Group'
            }
        ]

    }

    new Vue({
        el: '#wp-erp',
        data : {
            fields: tableColumns,
            itemRowActions: [
                {
                    title: 'Edit',
                    attrTitle: 'Edit this contact',
                    class: 'edit',
                    action: 'edit'
                },
                {
                    title: 'View',
                    attrTitle: 'View this contact',
                    class: 'view',
                    action: 'view',
                },
                {
                    title: 'Delete',
                    attrTitle: 'Delete this contact',
                    class: 'delete',
                    action: 'delete'
                }
            ],
            topNavFilter: {
                data: <?php echo json_encode( $statuses ); ?>,
                default: 'all',
                field: 'status'
            },
            bulkactions: bulkactions
        },

        methods: {
            full_name: function( value, item ) {
                var link  = '<a href="' + item.details_url + '"><strong>' + item.first_name + ' '+ item.last_name + '</strong></a>';
                return item.avatar + link;
            },

            render_href_action: function( item ) {
                return '<?php echo add_query_arg( [ 'page' => 'erp-sales-customers', 'action' => 'view' ], admin_url('admin.php') ) ?>&id=' + item.id;
            },
        },

        events: {
            'vtable:action': function(action, data) {
                // handle row action with data
            },

            'vtable:default-bulk-action': function( action, ids ) {
                // Handle bulk action when action is something with ID's
                console.log( action, ids );
                this.$nextTick(function() {
                    this.$broadcast('vtable:refresh')
                })

            }
        }
    });
})(jQuery)
</script>
