Vue.component('vtable', {
    template:
        '<div class="list-table-wrap {{ wrapperClass }}">'
            +'<div class="list-table-inner {{ tableWrapper }}">'
                +'<form method="get">'
                    +'<input type="hidden" name="page" value="erp-sales-customers">'
                    +'<p class="search-box">'
                        +'<label class="screen-reader-text" for="erp-customer-search-search-input">Search Contact:</label>'
                        +'<input type="search" id="erp-customer-search-search-input" name="s" value="">'
                        +'<input type="submit" name="customer_search" id="search-submit" class="button" value="Search Contact">'
                    +'</p>'
                    +'<ul class="subsubsub">'
                        +'<li class="all"><a href="http://localhost/wperp/wp-admin/admin.php?page=erp-sales-customers&amp;status=all" class="current">All <span class="count">(1)</span></a> |</li>'
                        +'<li class="customer"><a href="/wperp/wp-admin/admin.php?page=erp-sales-customers&amp;status=customer" class="status-customer">Customer <span class="count">(1)</span></a> |</li>'
                        +'<li class="lead"><a href="/wperp/wp-admin/admin.php?page=erp-sales-customers&amp;status=lead" class="status-lead">Lead <span class="count">(0)</span></a> |</li>'
                        +'<li class="opportunity"><a href="/wperp/wp-admin/admin.php?page=erp-sales-customers&amp;status=opportunity" class="status-opportunity">Opportunity <span class="count">(0)</span></a> |</li>'
                        +'<li class="subscriber"><a href="/wperp/wp-admin/admin.php?page=erp-sales-customers&amp;status=subscriber" class="status-subscriber">Subscriber <span class="count">(0)</span></a> |</li>'
                        +'<li class="trash"><a href="/wperp/wp-admin/admin.php?page=erp-sales-customers&amp;status=trash" class="status-trash">Trash <span class="count">(0)</span></a></li>'
                    +'</ul><input type="hidden" id="_wpnonce" name="_wpnonce" value="69260767e4"><input type="hidden" name="_wp_http_referer" value="/wperp/wp-admin/admin.php?page=erp-sales-customers">'

                    +'<div class="tablenav top">'
                        +'<div class="alignleft actions bulkactions">'
                            +'<label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>'
                            +'<select name="action" id="bulk-action-selector-top">'
                                +'<option value="-1">Bulk Actions</option>'
                                +'<option value="delete">Move to Trash</option>'
                                +'<option value="assing_group">Add to Contact group</option>'
                            +'</select>'
                            +'<input type="submit" id="doaction" class="button action" value="Apply">'
                        +'</div>'
                        +'<div class="tablenav-pages one-page">'
                            +'<span class="displaying-num">1 item</span>'
                            +'<span class="pagination-links"><span class="tablenav-pages-navspan" aria-hidden="true">«</span>'
                            +'<span class="tablenav-pages-navspan" aria-hidden="true">‹</span>'
                            +'<span class="paging-input"><label for="current-page-selector" class="screen-reader-text">Current Page</label><input class="current-page" id="current-page-selector" type="text" name="paged" value="1" size="1" aria-describedby="table-paging"> of <span class="total-pages">1</span></span>'
                            +'<span class="tablenav-pages-navspan" aria-hidden="true">›</span>'
                            +'<span class="tablenav-pages-navspan" aria-hidden="true">»</span></span>'
                        +'</div>'
                        +'<br class="clear">'
                    +'</div>'

                    +'<table class="wp-list-table widefat fixed striped {{ tableClass }}">'
                        +'<thead>'
                            +'<tr>'
                                +'<td id="cb" class="manage-column column-cb check-column">'
                                    +'<label class="screen-reader-text" for="cb-select-all-1">Select All</label>'
                                    +'<input id="cb-select-all-1" type="checkbox">'
                                +'</td>'
                                +'<template v-for="field in fields">'
                                    +'<th v-if="!isSortable( field )" scope="col" id="{{ field.name }}" class="manage-column column-{{ field.name }} column-primary">{{ field.title }}</th>'
                                    +'<th v-else scope="col" id="{{ field.name }}" class="manage-column column-{{ field.name }} sortable {{ sortOrder.direction }}">'
                                        +'<a href="#" v-click.prevent="orderBy( field )">'
                                            +'<span>{{ field.title }}</span>'
                                            +'<span class="sorting-indicator"></span>'
                                        +'</a>'
                                    +'</th>'
                                +'</template>'
                            +'</tr>'
                        +'</thead>'

                        +'<tbody id="the-list" data-wp-lists="list:{{ tableClass }}">'
                            +'<tr v-for="item in tableData">'
                                +'<th scope="row" class="check-column">'
                                    +'<input type="checkbox" class="{{ rowCheckboxId }}" name="{{ rowCheckboxName }}[]" value="">'
                                +'</th>'
                                + '<template v-for="( i, field ) in fields">'
                                    + '<template v-if="(i==0 )">'
                                        + '<td v-if="hasCallback(field)" class="has-row-actions column-primary {{ field.name }} column-{{ field.name }}" data-colname="{{ field.title }}">'
                                        + '{{{ callCallback(field, item ) }}}'
                                        + '<template v-if="hasRowAction()">'
                                            + '<div class="row-actions">'
                                                + '<template v-for="( rowActionIndex, rowAction ) in itemRowActions">'
                                                    + '<template v-if="hasRowActionCallback( rowAction )">'
                                                        + '{{{ callRowActionCallback( rowAction, item ) }}}'
                                                    + '</template>'
                                                    + '<template v-else>'
                                                        + '<span class="{{ rowAction.class }}">'
                                                            + '<a v-if="!hasPreventRowAction( rowAction )" href="{{{ rowActionLinkCallback( rowAction, item ) }}}" title="{{ rowAction.attrTitle }}">{{ rowAction.title }}</a>'
                                                            + '<a v-else href="#" @click.prevent="rowActionLinkCallback( rowAction, item )" title="{{ rowAction.attrTitle }}">{{ rowAction.title }}</a>'
                                                            + '<span v-if="rowActionIndex != ( itemRowActions.length - 1)"> | </span>'
                                                        + '</span>'
                                                    + '</template>'
                                                + '</template>'
                                            + '</div>'

                                            + '<button type="button" class="toggle-row">'
                                                + '<span class="screen-reader-text">Show more details</span>'
                                            + '</button>'
                                            + '<button type="button" class="toggle-row">'
                                                + '<span class="screen-reader-text">Show more details</span>'
                                            + '</button>'
                                        + '</template>'
                                        + '</td>'
                                        + '<td v-else class="has-row-actions column-primary column-name {{ field.name }} column-{{ field.name }}" data-colname="{{ field.title }}">'
                                            + '{{{ getObjectValue(item, field.name, "-") }}}'
                                        + '</td>'
                                    + '</template>'
                                    + '<template v-else>'
                                        + '<td v-if="hasCallback(field)" class="{{ field.name }} column-{{ field.name }}" data-colname="{{ field.title }}">'
                                        + '{{{ callCallback(field, item ) }}}'
                                        + '</td>'
                                        + '<td v-else class="{{ field.name }} column-{{ field.name }}" data-colname="{{ field.title }}">'
                                            + '{{{ getObjectValue(item, field.name, "-") }}}'
                                        + '</td>'
                                    + '</template>'
                                + '</template>'
                            +'</tr>'
                        +'</tbody>'

                        +'<tfoot>'
                            +'<tr>'
                                +'<td class="manage-column column-cb check-column">'
                                    +'<label class="screen-reader-text" for="cb-select-all-2">Select All</label>'
                                    +'<input id="cb-select-all-2" type="checkbox">'
                                +'</td>'
                                +'<template v-for="field in fields">'
                                    +'<th v-if="!isSortable( field )" scope="col" id="{{ field.name }}" class="manage-column column-{{ field.name }} column-primary">{{ field.title }}</th>'
                                    +'<th v-else scope="col" id="{{ field.name }}" class="manage-column column-{{ field.name }} sortable {{ sortOrder.direction }}">'
                                        +'<a href="#" v-click.prevent="orderBy( field )">'
                                            +'<span>{{ field.title }}</span>'
                                            +'<span class="sorting-indicator"></span>'
                                        +'</a>'
                                    +'</th>'
                                +'</template>'
                            +'</tr>'
                        +'</tfoot>'
                    +'</table>'

                    +'<div class="tablenav bottom">'

                        +'<div class="alignleft actions bulkactions">'
                            +'<label for="bulk-action-selector-bottom" class="screen-reader-text">Select bulk action</label>'
                            +'<select name="action2" id="bulk-action-selector-bottom">'
                                +'<option value="-1">Bulk Actions</option>'
                                +'<option value="delete">Move to Trash</option>'
                                +'<option value="assing_group">Add to Contact group</option>'
                            +'</select>'
                            +'<input type="submit" id="doaction2" class="button action" value="Apply">'
                        +'</div>'

                        +'<div class="tablenav-pages one-page">'
                            +'<span class="displaying-num">1 item</span>'
                            +'<span class="pagination-links">'
                                +'<span class="tablenav-pages-navspan" aria-hidden="true">«</span>'
                                +'<span class="tablenav-pages-navspan" aria-hidden="true">‹</span>'
                                +'<span class="screen-reader-text">Current Page</span><span id="table-paging" class="paging-input">1 of <span class="total-pages">1</span></span>'
                                +'<span class="tablenav-pages-navspan" aria-hidden="true">›</span>'
                                +'<span class="tablenav-pages-navspan" aria-hidden="true">»</span>'
                            +'</span>'
                        +'</div>'
                        +'<br class="clear">'
                    +'</div>'
                +'</form>'
            +'</div><!-- .list-table-inner -->'
        +'</div><!-- .list-table-wrap -->',

    props: {
        'wrapperClass': {
            type: String,
            default: function() {
                return ''
            }
        },

        'tableWrapper': {
            type: String,
            default: function() {
                return ''
            }
        },

        'tableClass': {
            type: String,
            default: function() {
                return ''
            }
        },

        'rowCheckboxId': {
            type: String,
            default: function() {
                return 'input-checkbox'
            }
        },

        'rowCheckboxName': {
            type: String,
            default: function() {
                return 'checkbox'
            }
        },

        'perPage': {
            type: Number,
            coerce: function(val) {
                return parseInt(val);
            },
            default: function() {
                return 5
            }
        },

        'fields': {
            type: Array,
            required: true
        },

        'action': {
            type: String,
            required: true
        },

        'perPage': {
            type: Number,
            coerce: function(val) {
                return parseInt(val);
            },
            default: function() {
                return 5
            }
        },

        itemRowActions: {
            type: Array,
            default: function() {
                return [];
            }
        },

        'sortOrder': {
            type: Object,
            default: function() {
                return {
                    field: '',
                    direction: 'desc'
                }
            }
        },
    },

    data: function() {
        return {
            eventPrefix: 'vtable:',
            tableData: null,
            tablePagination: null,
            currentPage: 1,
        }
    },

    computed: {
        sortIcon: function() {
            return this.sortOrder.direction == 'asc' ? this.ascendingIcon : this.descendingIcon
        },
    },

    methods: {

        isSortable: function( field ) {
            return !(typeof field.sortField == 'undefined')
        },

        orderBy: function( field ) {

        },

        hasPreventRowAction: function( rowAction ) {
            return rowAction.prevent ? true : false;
        },

        callPreventRowAction: function( rowAction ) {
            if ( ! this.hasCallback(field) ) {
                return;
            }

            var args = field.callback.split('|')
            var func = args.shift()

            if (typeof this.$parent[func] == 'function') {
                return (args.length > 0)
                    ? this.$parent[func].apply(this.$parent, [this.getObjectValue(item, field.name)].concat(args), item )
                    : this.$parent[func].call(this.$parent, this.getObjectValue(item, field.name), item)
            }

            return null
        },

        hasRowAction: function( index ) {
            return this.itemRowActions.length > 0;
        },

        hasCallback: function( item ) {
            return item.callback ? true : false
        },

        hasRowActionCallback: function( rowAction ) {
            return rowAction.callback ? true : false;
        },

        callRowActionCallback: function( rowAction, item ) {
            if ( ! this.hasRowActionCallback( rowAction ) ) {
                return;
            }

            var args = rowAction.callback.split('|')
            var func = args.shift()

            if (typeof this.$parent[func] == 'function') {
                return (args.length > 0)
                    ? this.$parent[func].apply(this.$parent, [rowAction.title].concat(args), item )
                    : this.$parent[func].call(this.$parent, [rowAction.title], item )
            }

            return null
        },

        callCallback: function( field, item ) {
            if ( ! this.hasCallback(field) ) {
                return;
            }

            var args = field.callback.split('|')
            var func = args.shift()

            if (typeof this.$parent[func] == 'function') {
                return (args.length > 0)
                    ? this.$parent[func].apply(this.$parent, [this.getObjectValue(item, field.name)].concat(args), item )
                    : this.$parent[func].call(this.$parent, this.getObjectValue(item, field.name), item)
            }

            return null
        },

        rowActionLinkCallback: function( field, item ) {
            if ( ! field.href ) {
                return '#';
            }

            var args = field.href.split('|')
            var func = args.shift()

            if (typeof this.$parent[func] == 'function') {
                return (args.length > 0)
                    ? this.$parent[func].apply(this.$parent, item )
                    : this.$parent[func].call(this.$parent, item)
            }

            return '#'
        },

        getObjectValue: function( object, path, defaultValue ) {
            defaultValue = (typeof defaultValue == 'undefined') ? null : defaultValue
            var obj = object

            if (path.trim() != '') {
                var keys = path.split('.')
                keys.forEach(function(key) {
                    if (typeof obj[key] != 'undefined' && obj[key] !== null) {
                        obj = obj[key]
                    } else {
                        obj = defaultValue;
                        return
                    }
                })
            }
            return obj
        },

        fetchData: function() {

            var self = this,
                data = {
                    action: this.action,
                    _wpnonce: wpVueTable.nonce
                };

            jQuery.post( wpVueTable.ajaxurl, data, function( resp ) {
                if ( resp.success ) {
                    self.tableData = resp.data
                } else {
                    alert(resp);
                }
            } )
        }
    },

    ready: function() {
        this.fetchData()
    }

});

