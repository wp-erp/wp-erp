Vue.component('vtable', {
    template:
        '<div class="vtable-wrap list-table-wrap {{ wrapperClass }}">'
            +'<div class="list-table-inner {{ tableWrapper }}">'
                +'<form method="get">'
                    +'<p class="search-box {{ search.wrapperClass }}">'
                        +'<label class="screen-reader-text" for="{{ search.inputId }}">{{ search.screenReaderText }}</label>'
                        +'<input type="search" v-model="searchQuery" id="{{ search.inputId }}" value="" name="s" placeholder="{{ search.placeholder }}" @input="searchCloseAction( searchQuery )" >'
                        +'<input type="submit" @click.prevent="searchAction( searchQuery )" id="{{ search.btnId }}" class="button" value="{{ search.btnText }}">'
                    +'</p>'
                    +'<ul v-if="!hasTopNavFilter()" class="subsubsub">'
                        +'<li v-for="( key, filter ) in topNavFilter.data" class="{{key}}"><a href="#" @click.prevent="callTopNavFilterAction( key, filter )" :class="{ \'current\': iscurrentTopNavFilter( key ) }">{{ filter.label }} <span class="count">({{ filter.count }})</span></a> <span v-if="!ifTopNavFilterLastItem( key )"> | </span></li>'
                    +'</ul>'

                    +'<div class="tablenav top">'
                        +'<div class="alignleft actions bulkactions" v-if="hasBulkAction()">'
                            +'<label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>'
                            +'<select name="action" id="bulk-action-selector-top" v-model="bulkaction1">'
                                +'<option value="-1">Bulk Actions</option>'
                                +'<option v-for="actions in bulkactions.defaultAction" value="{{ actions.id }}">{{ actions.text }}</option>'
                            +'</select>'
                            +'<input type="submit" id="doaction" @click.prevent="handleBulkAction(bulkaction1)" class="button action" value="Apply">'
                        +'</div>'

                        +'<div class="tablenav-pages" :class="{ \'one-page\': hidePagination }">'
                            +'<span class="displaying-num">{{ totalItem }} item</span>'
                            +'<span class="pagination-links">'
                                +'<span v-if="isFirstPage()" class="tablenav-pages-navspan" aria-hidden="true">«</span>'
                                +'<a v-else class="first-page" href="#" @click.prevent="goFirstPage()"><span class="screen-reader-text">First page</span><span aria-hidden="true">«</span></a>'

                                +'<span v-if="currentPage == 1"class="tablenav-pages-navspan" aria-hidden="true">‹</span>'
                                +'<a v-else class="prev-page" href="#" @click.prevent="goToPage(\'prev\')"><span class="screen-reader-text">Previous page</span><span aria-hidden="true">‹</span></a>'

                                +'<span class="screen-reader-text">Current Page</span><input @keydown.enter.prevent="goToPage(pageNumberInput)" class="current-page" id="current-page-selector" v-model="pageNumberInput" type="text" value="1" size="1" aria-describedby="table-paging"> of <span class="total-pages">{{ totalPage }}</span>'

                                +'<span v-if="currentPage == totalPage"class="tablenav-pages-navspan" aria-hidden="true">›</span>'
                                +'<a v-else class="next-page" href="#" @click.prevent="goToPage(\'next\')"><span class="screen-reader-text">Next page</span><span aria-hidden="true">›</span></a>'

                                +'<span v-if="isLastPage()" class="tablenav-pages-navspan" aria-hidden="true">»</span>'
                                +'<a v-else class="last-page" href="#" @click.prevent="goLastPage()"><span class="screen-reader-text">Last page</span><span aria-hidden="true">»</span></a>'
                            +'</span>'
                        +'</div>'
                        +'<br class="clear">'
                    +'</div>'
                    +'<div class="vtbale-table-wrapper">'
                        +'<table class="vtable wp-list-table widefat fixed striped {{ tableClass }}">'
                            +'<thead>'
                                +'<tr>'
                                    +'<td id="cb" class="manage-column column-cb check-column">'
                                        +'<label class="screen-reader-text" for="cb-select-all-1">Select All</label>'
                                        +'<input id="cb-select-all-1" type="checkbox">'
                                    +'</td>'
                                    +'<template v-for="(i,field) in fields">'
                                        +'<template v-if="i==0">'
                                            +'<th v-if="!isSortable( field )" scope="col" id="{{ field.name }}" class="manage-column column-{{ field.name }} column-primary">{{ field.title }}</th>'
                                            +'<th v-else scope="col" id="{{ field.name }}" class="manage-column column-{{ field.name }} column-primary sortable {{ sortOrder.direction }}">'
                                                +'<a href="#" @click.prevent="orderBy( field )">'
                                                    +'<span>{{ field.title }}</span>'
                                                    +'<span class="sorting-indicator"></span>'
                                                +'</a>'
                                            +'</th>'
                                        +'</template>'
                                        +'<template v-else>'
                                            +'<th v-if="!isSortable( field )" scope="col" id="{{ field.name }}" class="manage-column column-{{ field.name }}">{{ field.title }}</th>'
                                            +'<th v-else scope="col" id="{{ field.name }}" class="manage-column column-{{ field.name }} sortable {{ sortOrder.direction }}">'
                                                +'<a href="#" @click.prevent="orderBy( field )">'
                                                    +'<span>{{ field.title }}</span>'
                                                    +'<span class="sorting-indicator"></span>'
                                                +'</a>'
                                            +'</th>'
                                        +'</template>'
                                    +'</template>'
                                +'</tr>'
                            +'</thead>'

                            +'<tbody id="the-list" data-wp-lists="list:{{ tableClass }}" class="vtbale-tbody">'
                                +'<tr v-if="( tableData.length > 0 )" v-for="(itemIndex, item) in tableData" transition="vtable-item">'
                                    +'<th scope="row" class="check-column">'
                                        +'<input type="checkbox" v-model="checkboxItems" class="{{ rowCheckboxId }}" name="{{ rowCheckboxName }}[]" data-field="{{ rowCheckboxField }}" value="{{ item[rowCheckboxField] }}">'
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
                                                                + '<a v-if="!hasPreventRowAction( rowAction )" href="{{{ rowActionLinkCallback( rowAction, item, itemIndex ) }}}" title="{{ rowAction.attrTitle }}">{{ rowAction.title }}</a>'
                                                                + '<a v-else href="#" @click.prevent="callAction( rowAction.action, item, itemIndex )" title="{{ rowAction.attrTitle }}">{{ rowAction.title }}</a>'
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
                                +'<tr v-if="( tableData.length < 1 ) || !isLoaded">'
                                    +'<td colspan="{{ this.fields.length+1 }}"><span v-if="!isLoaded">Loading...</span><span v-else>No result found</span></td>'
                                +'</tr>'
                            +'</tbody>'

                            +'<tfoot>'
                                +'<tr>'
                                    +'<td class="manage-column column-cb check-column">'
                                        +'<label class="screen-reader-text" for="cb-select-all-2">Select All</label>'
                                        +'<input id="cb-select-all-2" type="checkbox">'
                                    +'</td>'
                                    +'<template v-for="(i,field) in fields">'
                                        +'<template v-if="i==0">'
                                            +'<th v-if="!isSortable( field )" scope="col" id="{{ field.name }}" class="manage-column column-{{ field.name }} column-primary">{{ field.title }}</th>'
                                            +'<th v-else scope="col" id="{{ field.name }}" class="manage-column column-{{ field.name }} column-primary sortable {{ sortOrder.direction }}">'
                                                +'<a href="#" @click.prevent="orderBy( field )">'
                                                    +'<span>{{ field.title }}</span>'
                                                    +'<span class="sorting-indicator"></span>'
                                                +'</a>'
                                            +'</th>'
                                        +'</template>'
                                        +'<template v-else>'
                                            +'<th v-if="!isSortable( field )" scope="col" id="{{ field.name }}" class="manage-column column-{{ field.name }}">{{ field.title }}</th>'
                                            +'<th v-else scope="col" id="{{ field.name }}" class="manage-column column-{{ field.name }} sortable {{ sortOrder.direction }}">'
                                                +'<a href="#" @click.prevent="orderBy( field )">'
                                                    +'<span>{{ field.title }}</span>'
                                                    +'<span class="sorting-indicator"></span>'
                                                +'</a>'
                                            +'</th>'
                                        +'</template>'
                                    +'</template>'
                                +'</tr>'
                            +'</tfoot>'
                        +'</table>'
                        +'<div class="vtable-loader-bg" v-if="ajaxloader"></div>'
                        +'<div class="vtable-loader" v-if="ajaxloader"></div>'
                    +'</div>'
                    +'<div class="tablenav bottom">'
                        +'<div class="alignleft actions bulkactions" v-if="hasBulkAction()">'
                            +'<label for="bulk-action-selector-bottom" class="screen-reader-text">Select bulk action</label>'
                            +'<select name="action2" id="bulk-action-selector-bottom" v-model="bulkaction2">'
                                +'<option value="-1">Bulk Actions</option>'
                                +'<option v-for="actions in bulkactions.defaultAction" value="{{ actions.id }}">{{ actions.text }}</option>'
                            +'</select>'
                            +'<input type="submit" id="doaction2" @click.prevent="handleBulkAction( bulkaction2 )" class="button action" value="Apply">'
                        +'</div>'

                        +'<div class="tablenav-pages" :class="{ \'one-page\': hidePagination }">'
                            +'<span class="displaying-num">{{ totalItem }} item</span>'
                            +'<span class="pagination-links">'
                                +'<span v-if="isFirstPage()" class="tablenav-pages-navspan" aria-hidden="true">«</span>'
                                +'<a v-else class="first-page" href="#" @click.prevent="goFirstPage()"><span class="screen-reader-text">First page</span><span aria-hidden="true">«</span></a>'

                                +'<span v-if="currentPage == 1"class="tablenav-pages-navspan" aria-hidden="true">‹</span>'
                                +'<a v-else class="prev-page" href="#" @click.prevent="goToPage(\'prev\')"><span class="screen-reader-text">Previous page</span><span aria-hidden="true">‹</span></a>'

                                +'<span class="screen-reader-text">Current Page</span><span id="table-paging" class="paging-input">{{ currentPage }} of <span class="total-pages">{{ totalPage }}</span></span>'

                                +'<span v-if="currentPage == totalPage"class="tablenav-pages-navspan" aria-hidden="true">›</span>'
                                +'<a v-else class="next-page" href="#" @click.prevent="goToPage(\'next\')"><span class="screen-reader-text">Next page</span><span aria-hidden="true">›</span></a>'

                                +'<span v-if="isLastPage()" class="tablenav-pages-navspan" aria-hidden="true">»</span>'
                                +'<a v-else class="last-page" href="#" @click.prevent="goLastPage()"><span class="screen-reader-text">Last page</span><span aria-hidden="true">»</span></a>'
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

        'rowCheckboxField': {
            type: String,
            default: function() {
                return 'id'
            }
        },

        'rowCheckboxName': {
            type: String,
            default: function() {
                return 'checkbox'
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

        'bulkactions': {
            type: Object,
            default: function() {
                return {}
            }
        },

        'perPage': {
            type: Number,
            coerce: function(val) {
                return val ? parseInt(val) : 5;
            },
            default: function() {
                return 5;
            }
        },

        'itemRowActions': {
            type: Array,
            default: function() {
                return [];
            }
        },

        'topNavFilter': {
            type: Object,
            default: function() {
                return {};
            }
        },

        'sortOrder': {
            type: Object,
            default: function() {
                return {
                    field: 'id',
                    direction: 'desc'
                }
            }
        },

        'additionalParams': {
            type: Object,
            default: function() {
                return {};
            }
        },

        'search': {
            type: Object,
            default: function() {
                return {
                    params: 's',
                    wrapperClass: '',
                    screenReaderText: 'Search Contact',
                    inputId: 'search-input',
                    btnText: 'Search Contact',
                    btnId: 'search-submit'
                }
            }
        }
    },

    data: function() {
        return {
            tableData: [],
            totalItem: 0,
            totalPage: 0,
            lastPage: 0,
            currentPage: 1,
            pageOffset:0,
            pageNumberInput:1,
            hidePagination : false,
            searchQuery: '',
            currentTopNavFilter: '',
            activeTopNavFilter: '',
            checkboxItems: [],
            bulkaction1: '-1',
            bulkaction2: '-1',
            ajaxloader: false,
            isLoaded: false
        }
    },

    computed: {
        sortIcon: function() {
            return this.sortOrder.direction == 'asc' ? this.ascendingIcon : this.descendingIcon
        },

        totalPage: function() {
            return Math.ceil( this.totalItem/this.perPage);
        },

        lastPage: function() {
            return this.totalPage;
        },

        pageOffset: function() {
            return (this.currentPage-1)*this.perPage;
        },

        hidePagination: function() {
            return this.perPage >= this.totalItem;
        },

        currentTopNavFilter: function() {
            return ( this.activeTopNavFilter ) ? this.activeTopNavFilter : this.topNavFilter.default;
        },

        columnCount: function() {
            return this.fields.length+1;
        }
    },

    methods: {

        handleBulkAction: function(action) {
            this.$dispatch('vtable:default-bulk-action', action, this.checkboxItems );
            this.checkboxItems = [];
            this.bulkaction1 = this.bulkaction2 = '-1';
        },

        hasBulkAction: function() {
            return Object.keys( this.bulkactions ).length > 0;
        },

        ifTopNavFilterLastItem: function( currentKey ) {
            var keys = Object.keys( this.topNavFilter.data )

            if ( keys[keys.length-1] == currentKey ) {
                return true;
            }
            return false;
        },

        iscurrentTopNavFilter: function( key ) {
            return this.currentTopNavFilter == key;
        },

        isFirstPage: function() {
            return this.currentPage == 1;
        },

        isLastPage: function() {
            return this.currentPage == this.totalPage;
        },

        goFirstPage: function() {
            this.currentPage = 1;
            this.fetchData();

        },

        goLastPage: function() {
            this.currentPage = this.totalPage;
            this.fetchData();
        },

        hasTopNavFilter: function() {
            return this.topNavFilter.data.length > 0;
        },

        goToPage: function(direction) {
            if ( direction == 'prev' ) {
                this.currentPage--;
            } else if ( direction == 'next' ) {
                this.currentPage++;
            } else {
                if ( ! isNaN( direction ) ) {
                    console.log( direction );
                    this.currentPage = direction;
                }
            }

            this.fetchData();

            return false;
        },

        isSortable: function( field ) {
            return !(typeof field.sortField == 'undefined')
        },

        orderBy: function( field ) {
            if ( ! this.isSortable(field)) {
                return
            }

            if (this.sortOrder.field == field.sortField ) {
                this.sortOrder.direction = this.sortOrder.direction == 'asc' ? 'desc' : 'asc'
            } else {
                this.sortOrder.direction = 'asc'
            }

            this.sortOrder.field = field.sortField ? field.sortField : field.name ;

            this.fetchData();
        },

        hasPreventRowAction: function( rowAction ) {
            return rowAction.action ? true : false;
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

        callAction: function( action, data, index ) {
            this.$dispatch( 'vtable:action', action, data, index );
        },

        callTopNavFilterAction: function( action, label ) {
            this.activeTopNavFilter = action;

            if ( typeof this.additionalParams === 'undefined' ) {
                this.additionalParams = {};
            }

            // var obj = {};
            // obj[this.topNavFilter.field] = action;
            this.additionalParams['status'] = action;

            this.fetchData();
        },

        searchAction: function( query ) {
            var query = query.trim();

            if ( query !== '' ) {
                if ( typeof this.additionalParams === 'undefined' ) {
                    this.additionalParams = {};
                }

                this.additionalParams[this.search.params] = query;
                this.fetchData();
            }
        },

        searchCloseAction: function( query ) {
            if ( query == '' ) {
                this.additionalParams = {};
                this.currentPage = 1
                this.fetchData();
            }
        },

        fetchData: function() {
            var self = this,
                data = {
                    action: this.action,
                    _wpnonce: wpVueTable.nonce
                };

            var params = [
                'order=' + this.sortOrder.direction,
                'orderby=' + this.sortOrder.field,
                'number=' + this.perPage,
                'offset=' + this.pageOffset
            ];

            this.ajaxloader = true;

            // console.log( params );

            var url = params.join('&')
            var postData = jQuery.param(data) + '&' + url;

            if ( typeof this.additionalParams !== 'undefined' ) {
                if ( Object.keys(this.additionalParams).length > 0  ) {
                    postData += '&'+jQuery.param( this.additionalParams );
                }
            }

            console.log( postData );

            jQuery.post( wpVueTable.ajaxurl, postData, function( resp ) {
                if ( resp.success ) {
                    self.ajaxloader = false;
                    self.isLoaded = true;
                    self.tableData = resp.data.data;
                    self.totalItem = resp.data.total_items;
                    if ( resp.data.data.length > 0 ) {
                        this.pageNumberInput = this.totalPage;
                    }
                } else {
                    alert(resp);
                }
            } );
        }
    },

    events: {

        'vtable:reload': function() {
            this.fetchData()
        },

        'vtable:refresh': function() {
            this.currentPage = 1
            this.fetchData()
        }
    },

    ready: function() {
        this.fetchData()
        this.pageNumberInput = this.currentPage;
    }

});

