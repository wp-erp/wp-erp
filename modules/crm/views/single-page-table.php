<?php $statuses = erp_crm_customer_get_status_count( 'contact' ); ?>
<div class="wrap erp-crm-customer" id="wp-erp">

    <h2>Contact<a href="#" id="erp-customer-new" class="erp-contact-new add-new-h2" data-type="contact" title="Add New Contact">Add New Contact</a></h2>

    <div class="list-table-wrap erp-crm-list-table-wrap">
        <div class="list-table-inner">
            <form method="get">
                <input type="hidden" name="page" value="erp-sales-customers">
                <p class="search-box">
                    <label class="screen-reader-text" for="erp-customer-search-search-input">Search Contact:</label>
                    <input type="search" id="erp-customer-search-search-input" name="s" value="">
                    <input type="submit" name="customer_search" id="search-submit" class="button" value="Search Contact">
                </p>
                <ul class="subsubsub">
                    <li class="all"><a href="http://localhost/wperp/wp-admin/admin.php?page=erp-sales-customers&amp;status=all" class="current">All <span class="count">(1)</span></a> |</li>
                    <li class="customer"><a href="/wperp/wp-admin/admin.php?page=erp-sales-customers&amp;status=customer" class="status-customer">Customer <span class="count">(1)</span></a> |</li>
                    <li class="lead"><a href="/wperp/wp-admin/admin.php?page=erp-sales-customers&amp;status=lead" class="status-lead">Lead <span class="count">(0)</span></a> |</li>
                    <li class="opportunity"><a href="/wperp/wp-admin/admin.php?page=erp-sales-customers&amp;status=opportunity" class="status-opportunity">Opportunity <span class="count">(0)</span></a> |</li>
                    <li class="subscriber"><a href="/wperp/wp-admin/admin.php?page=erp-sales-customers&amp;status=subscriber" class="status-subscriber">Subscriber <span class="count">(0)</span></a> |</li>
                    <li class="trash"><a href="/wperp/wp-admin/admin.php?page=erp-sales-customers&amp;status=trash" class="status-trash">Trash <span class="count">(0)</span></a></li>
                </ul>
                <input type="hidden" id="_wpnonce" name="_wpnonce" value="69260767e4">
                <input type="hidden" name="_wp_http_referer" value="/wperp/wp-admin/admin.php?page=erp-sales-customers">

                <div class="tablenav top">
                    <div class="alignleft actions bulkactions">
                        <label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
                        <select name="action" id="bulk-action-selector-top">
                            <option value="-1">Bulk Actions</option>
                            <option value="delete">Move to Trash</option>
                            <option value="assing_group">Add to Contact group</option>
                        </select>
                        <input type="submit" id="doaction" class="button action" value="Apply">
                    </div>
                    <div class="tablenav-pages one-page">
                        <span class="displaying-num">1 item</span>
                        <span class="pagination-links"><span class="tablenav-pages-navspan" aria-hidden="true">«</span>
                        <span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
                        <span class="paging-input">
                            <label for="current-page-selector" class="screen-reader-text">Current Page</label>
                            <input class="current-page" id="current-page-selector" type="text" name="paged" value="1" size="1" aria-describedby="table-paging"> of <span class="total-pages">1</span>
                        </span>
                        <span class="tablenav-pages-navspan" aria-hidden="true">›</span>
                        <span class="tablenav-pages-navspan" aria-hidden="true">»</span></span>
                    </div>
                    <br class="clear">
                </div>

                <table class="wp-list-table widefat fixed striped customers">
                    <thead>
                        <tr>
                            <td id="cb" class="manage-column column-cb check-column">
                                <label class="screen-reader-text" for="cb-select-all-1">Select All</label>
                                <input id="cb-select-all-1" type="checkbox">
                            </td>
                            <th scope="col" id="name" class="manage-column column-name column-primary">Contact Name</th>
                            <th scope="col" id="email" class="manage-column column-email">Email</th>
                            <th scope="col" id="phone_number" class="manage-column column-phone_number">Phone</th>
                            <th scope="col" id="life_stages" class="manage-column column-life_stages">Life Stage</th>
                            <th scope="col" id="crm_owner" class="manage-column column-crm_owner">Owner</th>
                            <th scope="col" id="created" class="manage-column column-created sortable desc">
                                <a href="http://localhost/wperp/wp-admin/admin.php?page=erp-sales-customers&amp;orderby=created&amp;order=asc">
                                    <span>Created at</span>
                                    <span class="sorting-indicator"></span>
                                </a>
                            </th>
                        </tr>
                    </thead>

                    <tbody id="the-list" data-wp-lists="list:customer">
                        <tr>
                            <th scope="row" class="check-column">
                                <input type="checkbox" class="erp-crm-customer-id-checkbox" name="customer_id[]" value="45">
                            </th>
                            <td class="name column-name has-row-actions column-primary" data-colname="Contact Name">
                                <img alt="" src="http://0.gravatar.com/avatar/?s=32&amp;d=mm&amp;r=g" srcset="http://0.gravatar.com/avatar/?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo avatar-default" height="32" width="32">
                                <a href="http://localhost/wperp/wp-admin/admin.php?page=erp-sales-customers&amp;action=view&amp;id=45"><strong>Nurul Amin</strong></a>
                                <div class="row-actions">
                                    <span class="edit">
                                        <a href="" data-id="45" title="Edit this customer">Edit</a> |
                                    </span>
                                    <span class="view">
                                        <a href="http://localhost/wperp/wp-admin/admin.php?page=erp-sales-customers&amp;action=view&amp;id=45" title="View this customer">View</a> |
                                    </span>
                                    <span class="delete">
                                        <a href="" class="submitdelete" data-id="45" data-type="contact" data-hard="0" title="Delete this item">Delete</a>
                                    </span>
                                </div>

                                <button type="button" class="toggle-row">
                                    <span class="screen-reader-text">Show more details</span>
                                </button>
                                <button type="button" class="toggle-row">
                                    <span class="screen-reader-text">Show more details</span>
                                </button>
                            </td>
                            <td class="email column-email" data-colname="Email">
                                <a href="mailto:amin.ict@gmail.com">amin.ict@gmail.com</a>
                            </td>
                            <td class="phone_number column-phone_number" data-colname="Phone">—</td>
                            <td class="life_stages column-life_stages" data-colname="Life Stage">Customer</td>
                            <td class="crm_owner column-crm_owner" data-colname="Owner">
                                <a href="http://localhost/wperp/wp-admin/admin.php?page=erp-sales-customers&amp;filter_assign_contact=27">Hello Sabbir</a>
                            </td>
                            <td class="created column-created" data-colname="Created at">26-04-2016</td>
                        </tr>
                    </tbody>

                    <tfoot>
                        <tr>
                            <td class="manage-column column-cb check-column">
                                <label class="screen-reader-text" for="cb-select-all-2">Select All</label>
                                <input id="cb-select-all-2" type="checkbox">
                            </td>
                            <th scope="col" class="manage-column column-name column-primary">Contact Name</th>
                            <th scope="col" class="manage-column column-email">Email</th>
                            <th scope="col" class="manage-column column-phone_number">Phone</th>
                            <th scope="col" class="manage-column column-life_stages">Life Stage</th>
                            <th scope="col" class="manage-column column-crm_owner">Owner</th>
                            <th scope="col" class="manage-column column-created sortable desc">
                                <a href="http://localhost/wperp/wp-admin/admin.php?page=erp-sales-customers&amp;orderby=created&amp;order=asc">
                                    <span>Created at</span>
                                    <span class="sorting-indicator"></span>
                                </a>
                            </th>
                        </tr>
                    </tfoot>
                </table>

                <div class="tablenav bottom">

                    <div class="alignleft actions bulkactions">
                        <label for="bulk-action-selector-bottom" class="screen-reader-text">Select bulk action</label>
                            <select name="action2" id="bulk-action-selector-bottom">
                                <option value="-1">Bulk Actions</option>
                                <option value="delete">Move to Trash</option>
                                <option value="assing_group">Add to Contact group</option>
                            </select>
                            <input type="submit" id="doaction2" class="button action" value="Apply">
                    </div>

                    <div class="tablenav-pages">
                        <span class="displaying-num">1 item</span>
                        <span class="pagination-links">
                            <span class="tablenav-pages-navspan" aria-hidden="true">«</span>
                            <span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
                            <span class="screen-reader-text">Current Page</span><span id="table-paging" class="paging-input">1 of <span class="total-pages">1</span></span>
                            <span class="tablenav-pages-navspan" aria-hidden="true">›</span>
                            <span class="tablenav-pages-navspan" aria-hidden="true">»</span>
                        </span>
                    </div>
                    <br class="clear">
                </div>
            </form>
        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->

    <br>
    <hr>
    <br>

    <vtable v-ref:vtable
        wrapper-class="erp-crm-list-table-wrap"
        table-class="customers"
        row-checkbox-id="erp-crm-customer-id-checkbox"
        row-checkbox-name="customer_id"
        action="erp-crm-get-contacts"
        :fields=fields
        per-page="20"
        :item-row-actions=itemRowActions
        :additional-params=additionalParams
        :search="search"
        :top-nav-filter="topNavFilter"
    ></vtable>

</div>

<script>
;(function($) {

    var tableColumns = [
        {
            name: 'name',
            title: 'Full Name',
            callback: 'full_name',
            sortField: 'id'
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
    ]

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
                    action: 'view'
                },
                {
                    title: 'Delete',
                    attrTitle: 'Delete this contact',
                    class: 'delete',
                    action: 'delete'
                }
            ],
            additionalParams: [],
            topNavFilter: {
                data: <?php echo json_encode( $statuses ); ?>,
                default: 'all'
            }
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
            },

            'vtable:top-nav-action': function(action, label) {
                this.additionalParams = [
                    'status=' + action
                ];

                this.$nextTick(function() {
                    this.$broadcast('vtable:refresh')
                })
            },
        }
    });
})(jQuery)
</script>
