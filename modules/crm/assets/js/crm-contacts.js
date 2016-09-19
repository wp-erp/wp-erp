;( function($, wperp) {
    // Vue.config.debug = 1;

    var mixin = {
        methods: {
            initFields: function() {
                $( '.erp-date-field').datepicker({
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    changeYear: true,
                    yearRange: '-100:+0',
                });

                $( '.erp-select2' ).select2({
                    placeholder: $(this).attr('data-placeholder')
                });
            },

            printObjectValue: function( key, obj, defaultVal ) {
                defaultVal = ( typeof defaultVal == 'undefined' ) ? '—' : defaultVal;
                value = ( obj[key] != '' && obj[key] != '-1' ) ? obj[key] : defaultVal;
                return value;
            },

            handlePostboxToggle: function() {
                var self = $(event.target),
                    postboxDiv = self.closest('.postbox');

                if ( postboxDiv.hasClass('closed') ) {
                    postboxDiv.removeClass('closed');
                } else {
                    postboxDiv.addClass('closed');
                }
            },

            initSearchCrmAgent: function() {
                $( 'select#erp-select-user-for-assign-contact' ).select2({
                    allowClear: true,
                    placeholder: 'Filter by Owner',
                    minimumInputLength: 3,
                    ajax: {
                        url: wpErpCrm.ajaxurl,
                        dataType: 'json',
                        delay: 250,
                        escapeMarkup: function( m ) {
                            return m;
                        },
                        data: function (params) {
                            return {
                                q: params.term, // search term
                                _wpnonce: wpErpCrm.nonce,
                                action: 'erp-search-crm-user'
                            };
                        },
                        processResults: function ( data, params ) {
                            var terms = [];

                            if ( data) {
                                $.each( data.data, function( id, text ) {
                                    terms.push({
                                        id: id,
                                        text: text
                                    });
                                });
                            }

                            if ( terms.length ) {
                                return { results: terms };
                            } else {
                                return { results: '' };
                            }
                        },
                        cache: true
                    }
                });
            },

            initContactListAjax: function() {
                $( 'select.erp-crm-contact-list-dropdown' ).select2({
                    allowClear: true,
                    placeholder: $(this).attr( 'data-placeholder' ),
                    minimumInputLength: 3,
                    ajax: {
                        url: wpErpCrm.ajaxurl,
                        dataType: 'json',
                        delay: 250,
                        escapeMarkup: function( m ) {
                            return m;
                        },
                        data: function (params) {
                            return {
                                s: params.term, // search term
                                _wpnonce: wpErpCrm.nonce,
                                types: $(this).attr( 'data-types' ).split(','),
                                action: 'erp-search-crm-contacts'
                            };
                        },
                        processResults: function ( data, params ) {
                            var terms = [];
                            if ( data) {
                                $.each( data.data, function( id, text ) {
                                    terms.push({
                                        id: id,
                                        text: text
                                    });
                                });
                            }

                            if ( terms.length ) {
                                return { results: terms };
                            } else {
                                return { results: '' };
                            }
                        },
                        cache: true
                    }
                });
            },

            reRenderFilterFromUrl: function( queryString ) {
                var self = this;
                var filters = [];
                var orSelection = queryString.split('&or&');

                jQuery.each( orSelection, function( index, orSelect ) {
                    var arr = {};
                    var r = [];
                    var keys = Object.keys( wpErpCrm.searchFields );

                    wperp.erp_parse_str( orSelect, arr );

                    for ( type in arr ) {
                        if ( keys.indexOf(type) > -1) {
                            if ( typeof arr[type] == 'object' ) {
                                for ( key in arr[type] ) {
                                    var parseCondition = wperp.parseCondition( arr[type][key] );
                                    var obj = {
                                        key: type,
                                        condition: parseCondition.condition,
                                        value: parseCondition.val,
                                        editable: false,
                                        title: '',
                                    }

                                    r.push( obj );
                                }
                            } else {
                                var parseCondition = wperp.parseCondition( arr[type] );
                                var obj = {
                                    key: type,
                                    condition: parseCondition.condition,
                                    value: parseCondition.val,
                                    editable: false,
                                    title: '',
                                }

                                r.push( obj );
                            }

                        }
                    }
                    filters.push( r );
                });

                return filters;
            },

            setPhoto: function(e) {
                e.preventDefault();
                e.stopPropagation();

                var frame;

                if ( frame ) {
                    frame.open();
                    return;
                }

                frame = wp.media({
                    title: wpErpCrm.customer_upload_photo,
                    button: { text: wpErpCrm.customer_set_photo }
                });

                frame.on('select', function() {
                    var selection = frame.state().get('selection');

                    selection.map( function( attachment ) {
                        attachment = attachment.toJSON();

                        var html = '<img src="' + attachment.url + '" alt="" />';
                            html += '<input type="hidden" id="customer-photo-id" name="photo_id" value="' + attachment.id + '" />';
                            html += '<a href="#" class="erp-remove-photo">&times;</a>';

                        $( '.photo-container', '.erp-customer-form' ).html( html );
                    });
                });

                frame.open();
            },

            removePhoto: function(e) {
                e.preventDefault();

                var html = '<a href="#" id="erp-set-customer-photo" class="button button-small">' + wpErpCrm.customer_upload_photo + '</a>';
                    html += '<input type="hidden" name="photo_id" id="custossmer-photo-id" value="0">';

                $( '.photo-container', '.erp-customer-form' ).html( html );
            },

        }
    }

    if ( $( '.erp-crm-customer-listing' ).length > 0 ) {

        var tableColumns = [
            {
                name: 'name',
                title: ( wpErpCrm.contact_type == 'contact') ? 'Contact name' : 'Company name',
                callback: 'fullName',
                sortField: 'first_name',
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
                title: 'Life stage',
                callback: 'lifeStage',
            },

            {
                name: 'contact_owner',
                title: 'Owner',
                callback: 'contactOwner',
            },

            {
                name: 'created',
                title: 'Created At',
                sortField: 'created'
            }
        ];

        var bulkactions = [
            {
                id : 'delete',
                text : 'Delete',
                showIf : 'whenNotTrased'
            },

            {
                id : 'permanent_delete',
                text : 'Permanent Delete',
                showIf : 'showPermanentDelete'
            },

            {
                id : 'restore',
                text : 'Restore',
                showIf : 'onlyTrased'
            },

            {
                id : 'assign_group',
                text : 'Assign Group',
                showIf : 'whenNotTrased'
            }
        ];

        var extraBulkAction = {
            'filterContactOwner' : {
                name: 'filter_assign_contact',
                type: 'select', // or text|email|number|url|datefield
                id: 'erp-select-user-for-assign-contact',
                class: 'erp-filter-contact-owner',
                placeholder: 'Filter by Owner',
                options: [
                    {
                        id : '',
                        text: ''
                    }
                ]
            },

            'filterSaveAdvanceFiter' : {
                name: 'filter_save_filter',
                type: 'select_optgroup', // or text|email|number|url|datefield
                id: 'erp-select-save-advance-filter',
                class: 'erp-save-advance-filter',
                placeholder: 'Filter by Segment',
                default: {
                    id: '',
                    text: '--Select save filter --'
                },
                options: wpErpCrm.saveAdvanceSearch
            }

        }

        Vue.component( 'filter-item', {
            props: [ 'field', 'fieldIndex', 'index', 'editableMode' ],

            template:
                '<div class="filter-item">'
                    + '<div class="filter-content" v-if="field.editable">'
                        + '<div class="filter-left">'
                            + '<select id="filter-key" v-model="fieldObj.filterKey">'
                                + '<option value="">--Select a field--</option>'
                                + '<option v-for="( searchKey, searchField ) in searchFields" value="{{ searchKey }}">{{ searchField.title }}</option>'
                            + '</select>'
                            + '<select id="filter-condition" v-model="fieldObj.filterCondition" v-if="fieldObj.filterKey" @change="setCondiationWiseValue( fieldObj.filterCondition )">'
                                + '<option v-for="( conditionSign, condition ) in searchFields[fieldObj.filterKey].condition" value="{{ conditionSign }}">{{ condition }}</option>'
                            + '</select>'
                            + '<template v-if="fieldObj.filterKey && isSomeCondition( fieldObj.filterCondition )">'
                                + '<input type="{{ searchFields[fieldObj.filterKey].type }}" v-if="ifSelectedTextField( searchFields[fieldObj.filterKey].type )" class="input-text" v-model="fieldObj.filterValue">'
                                + '<input type="text" v-if="searchFields[fieldObj.filterKey].type == \'date\'" v-datepicker class="input-text" v-model="fieldObj.filterValue">'
                                + '<input type="number" v-if="searchFields[fieldObj.filterKey].type == \'number\'" min="0" step="1" class="input-text" v-model="fieldObj.filterValue">'
                                + '<select v-if="searchFields[fieldObj.filterKey].type == \'dropdown\'" class="input-select" v-model="fieldObj.filterValue">'
                                    + '{{{ searchFields[fieldObj.filterKey].options }}}'
                                + '</select>'
                            + '</template>'
                        + '</div>'
                        + '<div class="filter-right">'
                            + '<a href="#" @click.prevent="applyFilter(field)"><i class="fa fa-check" aria-hidden="true"></i></a>'
                            + '<a href="#" @click.prevent="removeFilter(field)"><i class="fa fa-times" aria-hidden="true"></i></a>'
                        + '</div>'
                        + '<div class="clearfix"></div>'
                    + '</div>'
                    + '<div class="filter-details" v-else @click.prevent="editFilterItem( field )">'
                        + '<template v-if="isHasOrHasNotViaValue( field.value )">'
                            + '<div class="filter-left">'
                                + '<span style="color:#0085ba; font-style:italic; margin:0px 2px;">{{ field.value.replace("_", " ") | capitalize }}</span> {{ searchFields[field.key].title }}'
                            + '</div>'
                        + '</template>'
                        + '<template v-else>'
                            + '<div class="filter-left">'
                                + '{{ searchFields[field.key].title }} <span style="color:#0085ba; font-style:italic; margin:0px 2px;">{{ searchFields[field.key].condition[field.condition] }}</span> '
                                + '<span v-if="!field.title">{{ field.value }}</span>'
                                + '<span v-else>{{ field.title }}</span>'
                            + '</div>'
                        + '</template>'
                        + '<div class="filter-right">'
                            + '<a href="#" @click.prevent="removeFilter(field)"><i class="fa fa-times" aria-hidden="true"></i></a>'
                        + '</div>'
                    + '</div>'
                + '</div>',

            data: function() {
                return {
                    fieldObj : {
                        filterKey : '',
                        filterCondition : '',
                        filterValue : ''
                    },

                    isEditable: false,
                    searchFields: []
                }
            },

            computed: {
                searchFields: function() {
                    return wpErpCrm.searchFields;
                }
            },

            methods: {

                setCondiationWiseValue: function( condition ) {
                    if ( condition == '!%' ) {
                        this.fieldObj.filterValue = 'has_not';
                    } else if ( condition == '%' ) {
                        this.fieldObj.filterValue = 'if_has';
                    } else {
                        this.fieldObj.filterValue = '';
                    }
                },

                getSymbolForSomeCondition: function( value ) {
                    switch(value) {
                        case 'has_not':
                            return '!%';
                            break;
                        case 'if_has':
                            return '%';
                            break;
                        default:
                            return '';
                    }
                },

                isHasOrHasNotViaValue: function( value ) {
                    if ( value == 'has_not' || value == 'if_has' ) {
                        return true;
                    }
                    return false;
                },

                isSomeCondition: function( condition ) {
                    if ( condition == '!%' ||  condition == '%' ) {
                        return false;
                    }
                    return true;
                },

                ifSelectedTextField: function( type ) {
                    return ( type == 'text' || type == 'url' || type == 'email' ) ? true : false;
                },

                applyFilter: function() {
                    if ( ! this.fieldObj.filterKey || ( ! this.fieldObj.filterValue && this.isSomeCondition( this.fieldObj.filterCondition ) ) ) {
                        return;
                    }

                    if ( this.fieldObj.filterCondition == '%' || this.fieldObj.filterCondition == '!%' ) {
                        this.fieldObj.filterCondition = '';
                    }

                    this.field.editable = false;
                    this.$dispatch( 'changeFilterObject', this.fieldObj, this.fieldIndex, this.index, false );
                },

                removeFilter: function( field ) {
                    this.$dispatch( 'removeFilterObject',  this.fieldObj, this.fieldIndex, this.index, field.editable );
                },

                editFilterItem: function( field ) {
                    if ( this.editableMode ) {
                        return;
                    }

                    this.isEditable = true;
                    this.fieldObj.filterKey = field.key;
                    this.fieldObj.filterCondition = this.isHasOrHasNotViaValue( field.value ) ? this.getSymbolForSomeCondition( field.value ) : field.condition;
                    this.fieldObj.filterValue = this.isHasOrHasNotViaValue( field.value ) ? '' : field.value;
                    this.field.editable = true;

                    this.$dispatch( 'isEditableMode', true );
                }
            }
        });

        Vue.component( 'advance-search', {
            props: [ 'showHideSegment' ],

            mixins: [ mixin ],

            template:
                '<div id="erp-contact-advance-search-segment" v-show="showHideSegment">'
                    +'<div class="erp-advance-search-filters">'
                        + '<div class="erp-advance-search-or-wrapper" v-for="(index,fieldItem) in fields">'
                            + '<div class="or-divider" v-show="( this.fields.length > 1 ) && ( index != 0)">'
                                + '<hr>'
                                + '<span>Or</span>'
                            + '</div>'
                            + '<button :disabled="editableMode" class="add-filter button" @click.prevent="addNewFilter( index )"><i class="fa fa-filter" aria-hidden="true"></i> Add Filter</button>'
                            + '<filter-item :editable-mode=editableMode :field=field :field-index=fieldIndex :index=index v-for="( fieldIndex, field ) in fieldItem"></filter-item>'
                            + '<button :disabled="editableMode" class="add-filter button" v-show="( this.fields[index].length > 0 && index == this.fields.length-1 )" @click.prevent="addNewOrFilter( index )"><i class="fa fa-filter" aria-hidden="true"></i> Or Filter</button>'
                            + '<div class="clearfix"></div>'
                        + '</div>'
                    + '</div>'
                    + '<div class="erp-advance-search-action-wrapper" v-if="ifHasAnyFilter()">'
                        + '<div class="saveasnew-wrapper" v-show="isNewSave">'
                            + '<input type="text" class="save-search-name" v-model="saveSearchObj.searchName" placeholder="Name this Segment..">'
                            + '<label for="save-search-global"><input type="checkbox" id="save-search-global" class="save-search-global" v-model="saveSearchObj.searchItGlobal"> Make segment available for all users</label>'
                            + '<input type="submit" class="button button-primary" v-if="isUpdate" @click.prevent="searchSave(\'update\')" value="Update">'
                            + '<input type="submit" class="button button-primary" v-if="!isUpdate" @click.prevent="searchSave(\'save\')" value="Save">'
                            + '<input type="submit" class="button" v-if="isUpdate" @click.prevent="cancelSave(\'update\')" value="Cancel">'
                            + '<input type="submit" class="button" v-if="!isUpdate" @click.prevent="cancelSave(\'save\')" value="Cancel">'
                        + '</div>'
                        + '<button :disabled="editableMode" class="button button-primary" v-show="!isNewSave" @click.prevent="saveAsNew()">Save new Segment</button>'
                        + '<button :disabled="editableMode" class="button" v-show="isUpdateSaveSearch && !isNewSave" @click.prevent="updateSave()">Update this Segment</button>'
                        + '<button :disabled="editableMode" class="erp-button-danger button" style="float:right;" v-show="isUpdateSaveSearch && !isNewSave" @click.prevent="removeSegment()">Delete this Segment</button>'
                        + '<button :disabled="editableMode" class="button" style="float:right;" v-show="!isNewSave" @click.prevent="resetFilter()">Reset all filter</button>'
                    + '</div>'
                + '</div>',

            data: function() {
                return {
                    editableMode: false,
                    fields: [
                        [
                        ]
                    ],
                    isNewSave: false,
                    isUpdate: false,
                    isUpdateSaveSearch: false,
                    saveSearchObj: {
                        searchName: '',
                        searchItGlobal: false,
                    }
                }
            },

            methods: {

                resetFilter: function() {
                    this.$dispatch('resetAllFilters');
                    this.fields = [[]];
                },

                removeSegment: function() {
                    var self = this;

                    if ( confirm( wpErpCrm.delConfirmCustomer ) ) {
                        var filterID = wperp.erpGetParamByName( 'filter_save_filter', window.location.search );
                        wp.ajax.send( 'erp-crm-delete-search-segment', {
                            data: {
                                _wpnonce: wpErpCrm.nonce,
                                filterId: filterID
                            },
                            success: function(res) {
                                contact.extraBulkAction.filterSaveAdvanceFiter.options = contact.extraBulkAction.filterSaveAdvanceFiter.options.filter( function( items ) {
                                    var options = items.options.filter( function( arr ) {
                                        return arr.id !== filterID;
                                    } );

                                    items.options = options;
                                    return items;
                                });

                                setTimeout( function() {
                                    $('select#erp-select-save-advance-filter').trigger('change');
                                    contact.setAdvanceFilter();
                                },500);

                                self.resetFilter();

                                self.$nextTick(function() {
                                    this.$broadcast('vtable:reload')
                                });
                            },
                            error: function(res) {
                                alert( res );
                            }
                        });
                    }
                },

                cancelSave: function( flag ) {
                    if ( flag == 'save' ) {
                        this.isNewSave = false;
                    } else {
                        this.isNewSave = false;
                        this.isUpdateSaveSearch = true;
                    }
                    this.saveSearchObj.searchName = '';
                    this.saveSearchObj.searchItGlobal = false;
                },

                updateSave: function() {
                    var self = this,
                        data = {
                            action: 'erp_crm_get_save_search_data',
                            search_id: wperp.erpGetParamByName('filter_save_filter', window.location.search ),
                            _wpnonce: wpErpCrm.nonce
                        }

                    $.post( wpErpCrm.ajaxurl, data, function( resp ) {
                        if ( resp.success ) {
                            self.isUpdateSaveSearch = false;
                            self.isNewSave = true;
                            self.isUpdate = true;
                            self.saveSearchObj.searchName     = resp.data.search_name;
                            self.saveSearchObj.searchItGlobal = ( resp.data.global == 0 ) ? false : true;
                        } else {
                            alert( resp.data );
                        };
                    });
                },

                searchSave: function( flag ) {
                    var self = this;
                    var queryUrl = contact.makeQueryStringFromFilter( this.fields );
                    var data = {
                        action : 'erp_crm_create_new_save_search',
                        form_data : {
                            id: ( flag == 'save' ) ? '0' : wperp.erpGetParamByName( 'filter_save_filter', window.location.search ),
                            search_name: this.saveSearchObj.searchName,
                            search_it_global: this.saveSearchObj.searchItGlobal,
                            search_fields: queryUrl,
                            type: wpErpCrm.contact_type
                        },
                        _wpnonce : wpErpCrm.nonce
                    };

                    if ( ! queryUrl ) {
                        alert( 'You have not any filter for saving' );
                    }

                    jQuery.post( wpErpCrm.ajaxurl, data, function( resp ) {
                        if ( resp.success ) {
                            if ( ! self.isUpdate ) {
                                if ( contact.extraBulkAction.filterSaveAdvanceFiter.options.length < 1 ) {
                                    if ( resp.data.global == '0' ) {
                                        contact.extraBulkAction.filterSaveAdvanceFiter.options.push( {
                                            id: 'own_search',
                                            name: 'Own Search',
                                            options: [ {
                                                id: resp.data.id,
                                                text: resp.data.search_name,
                                                value: resp.data.search_val,
                                            } ]
                                        }, {
                                            id: 'global_search',
                                            name: 'Global Search',
                                            options: []
                                        } );
                                    } else {
                                        contact.extraBulkAction.filterSaveAdvanceFiter.options.push({
                                            id: 'global_search',
                                            name: 'Global Search',
                                            options: [ {
                                                id: resp.data.id,
                                                text: resp.data.search_name,
                                                value: resp.data.search_val,
                                            } ]
                                        }, {
                                            id: 'own_search',
                                            name: 'Own Search',
                                            options: []
                                        });
                                    }
                                } else {
                                    contact.extraBulkAction.filterSaveAdvanceFiter.options = contact.extraBulkAction.filterSaveAdvanceFiter.options.filter( function( item ) {
                                        if ( resp.data.global == '0' ) {

                                            if ( item.id == 'own_search' ) {
                                                item.options.push( {
                                                    id: resp.data.id,
                                                    text: resp.data.search_name,
                                                    value: resp.data.search_val,
                                                });
                                            }
                                            return item;
                                        } else {
                                            if ( item.id == 'global_search' ) {
                                                item.options.push( {
                                                    id: resp.data.id,
                                                    text: resp.data.search_name,
                                                    value: resp.data.search_val
                                                });
                                            }
                                            return item;
                                        }
                                    });
                                }

                                self.isNewSave = false;
                                self.saveSearchObj.searchName     = '';
                                self.saveSearchObj.searchItGlobal = false;

                                setTimeout( function() {
                                    $('select#erp-select-save-advance-filter').val( resp.data.id ).trigger('change');
                                    contact.setAdvanceFilter();
                                }, 500 );

                            } else {
                                jQuery('#erp-select-save-advance-filter').find('option[value="'+ resp.data.id +'"]').text( resp.data.search_name );
                                setTimeout( function() {
                                    $('select#erp-select-save-advance-filter').trigger('change');
                                    contact.setAdvanceFilter();
                                },500);

                                self.isNewSave = false;
                                self.isUpdateSaveSearch = true;
                            }

                        } else {
                            alert( resp.data );
                        };
                    });
                },

                saveAsNew: function() {
                    this.isNewSave = true;
                    this.isUpdate = false;
                },

                ifHasAnyFilter: function() {
                    if ( this.fields.length > 0 ) {
                        return this.fields[0].length > 0 ? true : false;
                    }

                    return false;
                },

                addNewFilter: function( index ) {
                    if( this.editableMode ) {
                        return;
                    }

                    this.fields[index].push({
                        key: '',
                        condition: '',
                        value: '',
                        editable: true,
                        title: '',
                    });

                    this.editableMode = true;
                    this.isUpdateSaveSearch = ( wperp.erpGetParamByName('filter_save_filter', window.location.search ) ) ? true : false;
                },

                addNewOrFilter: function() {
                    this.fields.push( [
                        {
                            key: '',
                            condition: '',
                            value: '',
                            editable: true,
                            title: ''
                        }
                    ]);
                    this.editableMode = true;
                    this.isUpdateSaveSearch = ( wperp.erpGetParamByName('filter_save_filter', window.location.search ) ) ? true : false;
                },

                parseCondition: function( value ) {
                    var obj = {};
                    var res = value.split(/([a-zA-Z0-9\s\-\_\+\.\:]+)/);
                    if ( res[0] == '' ) {
                        obj.condition = '';
                        obj.val = res[1];
                    } else {
                        obj.condition = res[0];
                        obj.val = res[1];
                    }

                    return obj;
                },

                renderFilterFromUrl: function() {
                    this.fields = this.reRenderFilterFromUrl( window.location.search );
                }
            },

            ready: function() {
                this.renderFilterFromUrl();
                this.isUpdateSaveSearch = ( wperp.erpGetParamByName('filter_save_filter', window.location.search ) ) ? true : false;
                this.showHideSegment = ( this.ifHasAnyFilter() ) ? true : false;
            },

            events: {
                'changeFilterObject': function( fieldObj, fieldIndex, index, editableMode ) {
                    this.fields[index][fieldIndex].key = fieldObj.filterKey;
                    this.fields[index][fieldIndex].condition = fieldObj.filterCondition;
                    this.fields[index][fieldIndex].value = fieldObj.filterValue;
                    this.fields[index][fieldIndex].title = '';
                    this.editableMode = editableMode;

                    this.$dispatch( 'filterContactList', this.fields );
                },

                'removeFilterObject': function( fieldObj, fieldIndex, index, isEditable ) {

                    if ( isEditable ) {
                        this.editableMode = false;
                    }

                    this.fields[index].$remove( this.fields[index][fieldIndex] );

                    if ( this.fields[index].length == 0 && this.fields.length > 1 ) {
                        this.fields.splice( index, 1 );
                    }

                    this.$dispatch( 'filterContactList', this.fields );
                },

                'setFilterFields': function( fields ) {
                    if ( typeof fields == 'boolean' ) {
                        this.isUpdateSaveSearch = false;
                    } else {
                        this.fields = fields;
                        this.isUpdateSaveSearch = true;
                    }
                    this.editableMode = false;
                },

                isEditableMode: function( isEditable ) {
                    this.editableMode = isEditable;
                }
            },

            watch: {
                fields: {
                    deep: true,
                    handler: function (newFields) {
                        var component = this,
                            i = 0;

                        for( i = 0; i < newFields.length; i++ ) {
                            $( newFields[i] ).each( function ( j ) {
                                var field = wpErpCrm.searchFields[ this.key ];

                                if ( field && 'dropdown' === field.type && field.options ) {
                                    var select = $( '<select>' + field.options + '</select>' );
                                    component.fields[i][j].title = select.find( '[value="' + component.fields[i][j].value + '"]' ).html();
                                }
                            });
                        }
                    }
                }
            }
        });

        var contact = new Vue({
            el: '#wp-erp',
            mixins: [mixin],
            data : {
                wpnonce: wpVueTable.nonce,
                fields: tableColumns,
                itemRowActions: [
                    {
                        title: 'Edit',
                        attrTitle: 'Edit this contact',
                        class: 'edit',
                        action: 'edit',
                        showIf: 'checkPermission'
                    },
                    {
                        title: 'View',
                        attrTitle: 'View this contact',
                        class: 'view',
                        action: 'view',
                        callback: 'contact_view_link'
                    },
                    {
                        title: 'Delete',
                        attrTitle: 'Delete this contact',
                        class: 'delete',
                        action: 'delete',
                        showIf: 'whenNotTrased'
                    },
                    {
                        title: 'Permanent Delete',
                        attrTitle: 'Permanent Delete this contact',
                        class: 'delete',
                        action: 'permanent_delete',
                        showIf: 'showPermanentDelete'
                    },
                    {
                        title: 'Restore',
                        attrTitle: 'Restore this contact',
                        class: 'restore',
                        action: 'restore',
                        showIf: 'onlyTrased'
                    },
                ],
                topNavFilter: {
                    data: wpErpCrm.statuses,
                    default: 'all',
                    field: 'status'
                },
                bulkactions: bulkactions,
                extraBulkAction: extraBulkAction,
                additionalParams: {
                    'type' : wpErpCrm.contact_type
                },
                search: {
                    params: 's',
                    wrapperClass: '',
                    screenReaderText: ( wpErpCrm.contact_type == 'company' ) ? 'Search Compnay' : 'Search Contact',
                    inputId: 'search-input',
                    btnId: 'search-submit',
                    placeholder: ( wpErpCrm.contact_type == 'company' ) ? 'Search Compnay' : 'Search Contact',
                },
                isRequestDone: false,
                showHideSegment: false,
                segmentBtnText: '',
            },

            computed: {
                segmentBtnText: function() {
                    return ( this.showHideSegment ) ? '<i class="fa fa-search" aria-hidden="true"></i> Hide Search Segment' : '<i class="fa fa-search" aria-hidden="true"></i> Search Segment';
                }
            },

            methods: {
                fullName: function( value, item ) {
                    if ( wpErpCrm.contact_type == 'contact' ) {
                        var link  = '<a href="' + item.details_url + '"><strong>' + item.first_name + ' '+ item.last_name + '</strong></a>';
                    } else {
                        var link  = '<a href="' + item.details_url + '"><strong>' + item.company + '</strong></a>';
                    }
                    return item.avatar.img + link;
                },

                lifeStage: function( value, item ) {
                    return wpErpCrm.life_stages[value];
                },

                contact_view_link: function( action, item ) {
                    return '<span class="view"><a href="' + item.details_url + '" title="View this contact">View</a><span> | </span></span>';
                },

                contactOwner: function( value, item ) {
                    return ( Object.keys( item.assign_to ).length > 0 ) ? '<a>' + item.assign_to.display_name + '</a>' : '—';
                },

                checkPermission: function( item ) {
                    if ( typeof item == 'undefined' ) {
                        return;
                    }

                    if( wpErpCrm.isCrmManager ) {
                        return true;
                    }

                    if ( wpErpCrm.isAgent && wpErpCrm.current_user_id == item.assign_to.id ) {
                        return true;
                    }

                    return false;
                },

                showPermanentDelete: function( item ) {
                    if ( this.$refs.vtable.currentTopNavFilter == 'trash' ) {
                        if ( typeof item == 'undefined' ) {
                            return true;
                        }

                        if( wpErpCrm.isCrmManager ) {
                            return true;
                        }

                        if ( wpErpCrm.isAgent ) {
                            return false;
                        }
                    }

                    return false;
                },

                onlyTrased: function( item ) {
                    if ( this.$refs.vtable.currentTopNavFilter == 'trash' ) {
                        if ( typeof item == 'undefined' ) {
                            return true;
                        }

                        if( wpErpCrm.isCrmManager ) {
                            return true;
                        }

                        if ( wpErpCrm.isAgent && wpErpCrm.current_user_id == item.assign_to.id ) {
                            return true;
                        }
                    }
                    return false;
                },

                whenNotTrased: function( item ) {
                    if ( this.$refs.vtable.currentTopNavFilter != 'trash' ) {
                        if ( typeof item == 'undefined' ) {
                            return true;
                        }

                        if( wpErpCrm.isCrmManager ) {
                            return true;
                        }

                        if ( wpErpCrm.isAgent && wpErpCrm.current_user_id == item.assign_to.id ) {
                            return true;
                        }
                    }

                    return false;
                },

                addContact: function( type, title ) {
                    var self = this;

                    $.erpPopup({
                        title: title,
                        button: wpErpCrm.add_submit,
                        id: 'erp-crm-new-contact',
                        content: wperp.template('erp-crm-new-contact')(  wpErpCrm.customer_empty  ).trim(),
                        extraClass: 'midium',
                        onReady: function() {
                            self.initFields();
                        },
                        onSubmit: function(modal) {
                            modal.disableButton();

                            wp.ajax.send( {
                                data: this.serialize(),
                                success: function( res ) {
                                    modal.enableButton();
                                    modal.closeModal();
                                    self.$refs.vtable.tableData.unshift(res.data);
                                    self.$nextTick(function() {
                                        this.$broadcast('vtable:reload');
                                    });
                                 },
                                error: function(error) {
                                    modal.enableButton();
                                    modal.showError( error );
                                }
                            });
                        }
                    });
                },

                editContact: function( data, index ) {
                    var self = this;

                    $.erpPopup({
                        title: 'Edit this ' + wpErpCrm.contact_type,
                        button: wpErpCrm.update_submit,
                        id: 'erp-customer-edit',
                        onReady: function() {
                            var modal = this;
                            $( 'header', modal).after( $('<div class="loader"></div>').show() );
                            wp.ajax.send( 'erp-crm-customer-get', {
                                data: {
                                    id: data.id,
                                    _wpnonce: wpErpCrm.nonce
                                },
                                success: function( response ) {
                                    var html = wp.template('erp-crm-new-contact')( response );
                                    $( '.content', modal ).html( html );
                                    $( '.loader', modal).remove();

                                    $( 'li[data-selected]', modal ).each(function() {
                                        var self = $(this),
                                            selected = self.data('selected');

                                        if ( selected !== '' ) {
                                            self.find( 'select' ).val( selected );
                                        }
                                    });

                                    $('select#erp-customer-type').trigger('change');
                                    $( 'select.erp-country-select').change();

                                    $( 'li[data-selected]', modal ).each(function() {
                                        var self = $(this),
                                            selected = self.data('selected');

                                        if ( selected !== '' ) {
                                            self.find( 'select' ).val( selected );
                                        }
                                    });

                                    _.each( $( 'input[type=checkbox].erp-crm-contact-group-class' ), function( el, i) {
                                        var optionsVal = $(el).val();
                                        if( _.contains( response.group_id, optionsVal ) ) {
                                            $(el).prop('checked', true );
                                        }
                                    });

                                    self.initFields();
                                }
                            });
                        },
                        onSubmit: function(modal) {
                            modal.disableButton();

                            wp.ajax.send( {
                                data: this.serialize(),
                                success: function(res) {
                                    modal.enableButton();
                                    modal.closeModal();
                                    self.$refs.vtable.tableData.$set( index, res.data );
                                    self.$refs.vtable.topNavFilter.data = res.statuses;
                                },
                                error: function(error) {
                                    modal.enableButton();
                                    modal.showError( error );
                                }
                            });
                        }
                    });
                },

                deleteContact: function( data, type, hard, isBulk ) {
                    var self = this;

                    if ( isBulk ) {
                        self.$refs.vtable.ajaxloader = true;
                    }

                    if ( confirm( wpErpCrm.delConfirmCustomer ) ) {
                        wp.ajax.send( 'erp-crm-customer-delete', {
                            data: {
                                _wpnonce: wpErpCrm.nonce,
                                id: ( isBulk ) ? data : data.id,
                                hard: ( hard == true ) ? 1 : 0,
                                type: type
                            },
                            success: function(res) {
                                if ( isBulk ) {
                                    self.$nextTick(function() {
                                        this.$broadcast('vtable:reload')
                                    });
                                    self.$refs.vtable.ajaxloader = false;
                                } else {
                                    self.$refs.vtable.tableData.$remove( data );
                                    self.$nextTick(function() {
                                        this.$broadcast('vtable:reload')
                                    });
                                }
                                self.$refs.vtable.topNavFilter.data = res.statuses;
                            },
                            error: function(res) {
                                alert( res );
                                self.$refs.vtable.ajaxloader = false;
                            }
                        });
                    } else {
                        self.$refs.vtable.ajaxloader = false;
                    }
                },

                restoreContact: function( data, type, isBulk ) {
                    var self = this;

                    if ( isBulk ) {
                        self.$refs.vtable.ajaxloader = true;
                    }

                    if ( confirm( wpErpCrm.confirm ) ) {
                        wp.ajax.send( 'erp-crm-customer-restore', {
                            data: {
                                _wpnonce: wpErpCrm.nonce,
                                id: ( isBulk ) ? data : data.id,
                                type: type
                            },
                            success: function(res) {
                                if ( isBulk ) {
                                    self.$nextTick(function() {
                                        this.$broadcast('vtable:reload')
                                    });
                                    self.$refs.vtable.ajaxloader = false;
                                } else {
                                    self.$refs.vtable.tableData.$remove( data );
                                    self.$nextTick(function() {
                                        this.$broadcast('vtable:reload')
                                    });
                                }
                                self.$refs.vtable.topNavFilter.data = res.statuses;
                            },
                            error: function(res) {
                                alert( res );
                                self.$refs.vtable.ajaxloader = false;
                            }
                        });
                    } else {
                        self.$refs.vtable.ajaxloader = false;
                    }
                },

                assignContact: function( ids, type ) {
                    var self = this;

                    if ( ids.length > 0 ) {
                        $.erpPopup({
                            title: wpErpCrm.popup.customer_assign_group,
                            button: wpErpCrm.add_submit,
                            id: 'erp-crm-customer-bulk-assign-group',
                            content: wperp.template('erp-crm-new-bulk-contact-group')({ user_id:ids }).trim(),
                            extraClass: 'smaller',

                            onSubmit: function(modal) {
                                modal.disableButton();

                                wp.ajax.send( {
                                    data: this.serialize(),
                                    success: function( res ) {
                                        modal.enableButton();
                                        modal.closeModal();
                                        self.$broadcast('vtable:refresh');
                                    },
                                    error: function(error) {
                                        modal.enableButton();
                                        modal.showError( error );
                                        self.$refs.vtable.ajaxloader = false;
                                    }
                                });
                            }
                        }); //popup

                    } else {
                        alert( wpErpCrm.checkedConfirm );
                        self.$refs.vtable.ajaxloader = false;
                    }
                },

                checkEmailForContact: function(e) {

                    var self = $(e.target),
                        form = self.closest('form'),
                        val = self.val(),
                        type = form.find('#erp-customer-type').val(),
                        id   = form.find('#erp-customer-id').val();

                    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

                    if ( val == '' || !re.test( val ) ) {
                        return false;
                    }

                    if ( id != '0' ) {
                        return false;
                    }

                    wp.ajax.send( 'erp_people_exists', {
                        data: {
                            email: val,
                            _wpnonce: wpErpCrm.nonce
                        },
                        success: function() {
                            form.find('.modal-suggession').fadeOut( 300, function() {
                                $(this).remove();
                                form.find('.content-container').css({ 'marginTop': '0px' });
                            });
                            form.find('button[type=submit]' ).removeAttr( 'disabled' );
                        },
                        error: function( response ) {
                            form.find('button[type=submit]' ).attr( 'disabled', 'disabled');

                            if ( $.inArray( 'contact', response.types ) != -1 || $.inArray( 'company', response.types ) != -1 ) {
                                form.find('.modal-suggession').remove();
                                form.find('header.modal-header').append('<div class="modal-suggession">' + wpErpCrm.contact_exit + '</div>');
                            } else {
                                form.find('.modal-suggession').remove();
                                form.find('header.modal-header').append('<div class="modal-suggession">' + wpErpCrm.make_contact_text + ' ' + type + ' ? <a href="#" id="erp-crm-create-contact-other-type" data-type="'+ type +'" data-user_id="'+ response.id +'">' + wpErpCrm.create_contact_text + ' ' + type + '</a></div>');
                            }

                            $('.modal-suggession').hide().slideDown( function() {
                                form.find('.content-container').css({ 'marginTop': '15px' });
                            });
                        }
                    });
                },

                makeUserAsContact: function(e) {
                    e.preventDefault();

                    var selfVue = this;

                    var self = $(e.target),
                        type = self.data('type'),
                        user_id = self.data('user_id');


                    if ( this.isRequestDone ) {
                        return;
                    }

                    this.isRequestDone = true;
                    self.closest('.modal-suggession').append('<div class="erp-loader" style="top:9px; right:10px;"></div>');

                    wp.ajax.send( 'erp-crm-convert-user-to-contact', {
                        data: {
                            user_id: user_id,
                            type: type,
                            _wpnonce: wpErpCrm.nonce
                        },
                        success: function() {
                            this.isRequestDone = false;
                            self.closest('.modal-suggession').find('.erp-loader').remove();
                            self.closest('.erp-modal').remove();
                            $('.erp-modal-backdrop').remove();

                            $.erpPopup({
                                title: wpErpCrm.update_submit + ' ' + type,
                                button: wpErpCrm.update_submit,
                                id: 'erp-customer-edit',
                                onReady: function() {
                                    var modal = this;

                                    $( 'header', modal).after( $('<div class="loader"></div>').show() );

                                    wp.ajax.send( 'erp-crm-customer-get', {
                                        data: {
                                            id: user_id,
                                            _wpnonce: wpErpCrm.nonce
                                        },
                                        success: function( response ) {
                                            var html = wp.template('erp-crm-new-contact')( response );
                                            $( '.content', modal ).html( html );
                                            $( '.loader', modal).remove();

                                            $( 'li[data-selected]', modal ).each(function() {
                                                var self = $(this),
                                                    selected = self.data('selected');

                                                if ( selected !== '' ) {
                                                    self.find( 'select' ).val( selected );
                                                }
                                            });

                                            $('select#erp-customer-type').trigger('change');
                                            $( '.erp-select2' ).select2();
                                            $( 'select.erp-country-select').change();

                                            $( 'li[data-selected]', modal ).each(function() {
                                                var self = $(this),
                                                    selected = self.data('selected');

                                                if ( selected !== '' ) {
                                                    self.find( 'select' ).val( selected );
                                                }
                                            });

                                            _.each( $( 'input[type=checkbox].erp-crm-contact-group-class' ), function( el, i) {
                                                var optionsVal = $(el).val();
                                                if( _.contains( response.group_id, optionsVal ) ) {
                                                    $(el).prop('checked', true );
                                                }
                                            });


                                            selfVue.initFields();
                                        }
                                    });
                                },
                                onSubmit: function(modal) {
                                    modal.disableButton();

                                    wp.ajax.send( {
                                        data: this.serialize(),
                                        success: function(response) {
                                            modal.enableButton();
                                            modal.closeModal();
                                            selfVue.$refs.vtable.tableData.unshift(response.data);
                                            selfVue.$refs.vtable.topNavFilter.data = response.statuses;
                                        },
                                        error: function(error) {
                                            modal.enableButton();
                                            modal.showError( error );
                                        }
                                    });
                                }
                            });

                        },
                        error: function( response ) {
                            isRequestDone = false;
                            alert(response);
                        }
                    });
                },

                setContactOwnerSearchValue: function() {
                    var value = this.$refs.vtable.getParamByName('filter_assign_contact');
                    if ( value ) {
                        $('select#erp-select-user-for-assign-contact')
                            .append('<option value="' + this.$refs.vtable.customData.filter_assign_contact.id + '" selected>' + this.$refs.vtable.customData.filter_assign_contact.display_name + '</option>').trigger('change')
                    }
                },

                makeQueryStringFromFilter: function( fields ) {
                    var queryString = [];
                    var queryUrl = '';

                    if ( fields.length < 0 ) {
                        return queryUrl;
                    }

                    $.each( fields, function( index, filter ) {
                        var str = [];
                        if ( filter.length < 0 ) {
                            return;
                        }
                        $.each( filter, function( i, filterObj ) {
                            var s = filterObj.key + '[]=' +filterObj.condition+filterObj.value
                            str.push(s);
                        });

                        queryString.push( str.join('&') );
                    });
                    queryUrl = queryString.join('&or&');

                    return queryUrl ;
                },

                setAdvanceFilter: function() {
                    $('select#erp-select-save-advance-filter').select2({
                        placeholder: $(this).attr('data-placeholder'),
                        allowClear: true
                    })
                },

                addSearchSegment: function() {
                    this.showHideSegment = !this.showHideSegment;
                }
            },

            ready: function() {
                var self = this;
                $( 'body' ).on( 'click', 'a#erp-set-customer-photo', this.setPhoto );
                $( 'body' ).on( 'click', 'a.erp-remove-photo', this.removePhoto );
                $( 'body' ).on( 'focusout', 'input#erp-crm-new-contact-email', this.checkEmailForContact );
                $( 'body' ).on( 'click', 'a#erp-crm-create-contact-other-type', this.makeUserAsContact );
                this.initSearchCrmAgent();
                this.setContactOwnerSearchValue();
                this.setAdvanceFilter();

                if ( wperp.erpGetParamByName('filter_save_filter', window.location.search ) !== null ) {
                    self.showHideSegment = true;
                }
            },

            events: {
                'filterContactList': function( fields ) {
                    var queryUrl = this.makeQueryStringFromFilter( fields );
                    var hasSaveFilterParam = wperp.erpGetParamByName( 'filter_save_filter', window.location.search.replace( '?', '' ) );
                    var addSaveFilterParam = ( hasSaveFilterParam === null ) ? '&filter_save_filter=' : '';

                    this.$refs.vtable.additionalUrlString['advanceFilter']= queryUrl + addSaveFilterParam;
                    this.$refs.vtable.fetchData();
                },

                'vtable:action': function( action, data, index ) {
                    if ( 'edit' == action ) {
                        this.editContact( data, index );
                    }

                    if ( 'delete' == action ) {
                        this.deleteContact( data, wpErpCrm.contact_type, false, false );
                    }

                    if ( 'restore' == action ) {
                        this.restoreContact( data, wpErpCrm.contact_type,false );
                    }

                    if ( 'permanent_delete' == action ) {
                        this.deleteContact( data, wpErpCrm.contact_type, true, false );
                    }
                },

                'vtable:default-bulk-action': function( action, ids ) {
                    // Handle bulk action when action is something with ID's
                    if ( 'delete' === action ) {
                        this.deleteContact( ids, wpErpCrm.contact_type, false, true );
                    }

                    if ( 'permanent_delete' === action ) {
                        this.deleteContact( ids, wpErpCrm.contact_type, true, true );
                    }

                    if ( 'restore' === action ) {
                        this.restoreContact( ids, wpErpCrm.contact_type, true );
                    }

                    if ( 'assign_group' === action ) {
                        this.assignContact( ids, wpErpCrm.contact_type );
                    }
                },

                'vtable:extra-bulk-action': function( data, ids ) {

                    if ( data.hasOwnProperty('filter_save_filter') ) {
                        if ( data.filter_save_filter != '' ) {
                            var queryString = '';
                            $.each( this.extraBulkAction.filterSaveAdvanceFiter.options, function( index, item ) {
                                $.each( item.options, function( i, option ) {
                                    if ( data.filter_save_filter == option.id ) {
                                        queryString = option.value;
                                    }
                                });
                            } );

                            this.showHideSegment = true;
                            fields = this.reRenderFilterFromUrl( queryString );
                            this.$broadcast( 'setFilterFields', fields );
                            this.$refs.vtable.additionalUrlString['advanceFilter']= queryString;
                        } else {
                            this.$broadcast( 'setFilterFields', false );
                        }
                    }
                },

                'resetAllFilters': function() {
                    this.$refs.vtable.additionalUrlString['advanceFilter'] = '';

                    delete this.$refs.vtable.additionalParams['filter_save_filter'];

                    this.$nextTick(function(){
                        this.$broadcast('vtable:reload');
                    });
                    setTimeout( function() {
                        var finalUrl = wperp.erpRemoveURLParameter( window.location.search, 'filter_save_filter' );
                        window.history.pushState( null, null, finalUrl );
                        $('select#erp-select-save-advance-filter').val('').trigger('change');
                    },500);

                }
            }
        });
    }

    if ( $( '.erp-single-customer' ).length > 0 && ! $( '.erp-crm-activities' ).length > 0) {
        Vue.component( 'contact-company-relation', {
            props: [ 'id', 'title', 'type', 'addButtonTxt' ],

            mixins:[mixin],

            template:
                '<div class="postbox customer-company-info">'
                    + '<div class="erp-handlediv" @click.prevent="handlePostboxToggle()" title="Click to toggle"><br></div>'
                    + '<h3 class="erp-hndle" @click.prevent="handlePostboxToggle()"><span>{{ title }}</span></h3>'
                    + '<div class="inside company-profile-content">'
                        + '<div class="company-list">'
                            + '<div v-for="item in items" class="postbox closed">'
                                + '<div class="erp-handlediv" @click.prevent="handlePostboxToggle()" title="Click to toggle"><br></div>'
                                + '<h3 class="erp-hndle" @click="handlePostboxToggle()">'
                                    + '<span class="customer-avatar">{{{ item.contact_details.avatar.img }}}</span>'
                                    + '<span class="customer-name">'
                                        + '<a href="{{ item.contact_details.details_url }}" target="_blank" v-if="isCompany( item.contact_details.types )">{{ item.contact_details.company }}</a>'
                                        + '<a href="{{ item.contact_details.details_url }}" target="_blank" v-else>{{ item.contact_details.first_name }}&nbsp;{{ item.contact_details.last_name }}</a>'
                                    + '</span>'
                                + '</h3>'
                                + '<div class="action">'
                                    + '<a href="#" @click.prevent="removeCompany( item )" class="erp-customer-delete-company" data-id="{{ item.contact_details.id }}"><i class="fa fa-trash-o"></i></a>'
                                + '</div>'
                                + '<div class="inside company-profile-content">'
                                    + '<ul class="erp-list separated">'
                                        + '<li><label>Phone</label><span class="sep"> : </span><span class="value" v-if="item.contact_details.phone"><a href="tel:{{ item.contact_details.phone }}">{{ printObjectValue( \'phone\', item.contact_details ) }}</a></span><span v-else>—</span></li>'
                                        + '<li><label>Mobile</label><span class="sep"> : </span><span class="value" v-if="item.contact_details.mobile"><a href="tel:{{ item.contact_details.mobile }}">{{ printObjectValue( \'mobile\', item.contact_details ) }}</a></span><span v-else>—</span></li>'
                                        + '<li><label>Fax</label><span class="sep"> : </span><span class="value">{{ printObjectValue( \'fax\', item.contact_details ) }}</span></li>'
                                        + '<li><label>Website</label><span class="sep"> : </span><span class="value" v-if="item.contact_details.website"><a href="{{ item.contact_details.website }}">{{ printObjectValue( \'website\', item.contact_details ) }}</a></span><span v-else>—</span></li>'
                                        + '<li><label>Street 1</label><span class="sep"> : </span><span class="value">{{ printObjectValue( \'street_1\', item.contact_details ) }}</span></li>'
                                        + '<li><label>Street 2</label><span class="sep"> : </span><span class="value">{{ printObjectValue( \'street_2\', item.contact_details ) }}</span></li>'
                                        + '<li><label>City</label><span class="sep"> : </span><span class="value">{{ printObjectValue( \'city\', item.contact_details ) }}</span></li>'
                                        + '<li><label>State</label><span class="sep"> : </span><span class="value">{{ printObjectValue( \'state\', item.contact_details ) }}</span></li>'
                                        + '<li><label>Country</label><span class="sep"> : </span><span class="value">{{ printObjectValue( \'country\', item.contact_details ) }}</span></li>'
                                        + '<li><label>Postal Code</label><span class="sep"> : </span><span class="value">{{ printObjectValue( \'postal_code\', item.contact_details ) }}</span></li>'
                                    + '</ul>'
                                + '</div>'
                            + '</div>'
                            + '<a href="#" @click.prevent="addCompany()" data-id="" data-type="assign_company" title="{{ addButtonTxt }}" class="button button-primary" id="erp-customer-add-company"><i class="fa fa-plus"></i> {{ addButtonTxt }}</a>'
                        + '</div>'
                    + '</div>'
                + '</div><!-- .postbox -->',

            data: function() {
                return {
                    items : [],
                    assignType : ''
                }
            },

            computed: {
                assignType: function() {
                    return ( wpErpCrm.contact_type == 'contact' ) ? 'assign_company' : 'assign_customer';
                }
            },

            methods: {

                isCompany: function( type ) {
                    return $.inArray( 'company', type ) < 0 ? false : true
                },

                removeCompany: function( item ) {
                    var self = this

                    if ( confirm( wpErpCrm.confirm ) ) {
                        wp.ajax.send( 'erp-crm-customer-remove-company', {
                            data: {
                                id: item.id,
                                _wpnonce: wpErpCrm.nonce
                            },
                            success: function( res ) {
                                self.items.$remove(item);
                            }
                        });
                    }
                },

                addCompany: function() {
                    var self = this,
                        data = {
                            id : this.id,
                            type : this.assignType,
                        };

                    $.erpPopup({
                        title: this.addButtonTxt,
                        button: wpErpCrm.save_submit,
                        id: 'erp-crm-single-contact-company',
                        content: wperp.template('erp-crm-new-assign-company')( data ).trim(),
                        extraClass: 'smaller',
                        onReady: function() {
                            self.initContactListAjax();
                        },
                        onSubmit: function(modal) {
                            modal.disableButton();

                            wp.ajax.send( {
                                data: this.serialize(),
                                success: function(res) {
                                    self.fetchData();
                                    modal.enableButton();
                                    modal.closeModal();
                                },
                                error: function(error) {
                                    modal.enableButton();
                                    modal.showError( error );
                                }
                            });
                        }
                    }); //popup
                },

                fetchData: function() {
                    var self = this,
                        data = {
                            id: this.id,
                            action: 'erp-crm-get-contact-companies',
                            type: this.type,
                            _wpnonce: wpErpCrm.nonce
                        };

                    jQuery.post( wpErpCrm.ajaxurl, data, function( resp ) {
                        if ( resp.success ) {
                            self.items = resp.data;
                        } else {
                            alert(resp);
                        }
                    } );
                }
            },

            ready: function() {
                this.fetchData();
            }
        });

        Vue.component( 'contact-assign-group', {
            props: [ 'id', 'title', 'addButtonTxt', 'isPermitted' ],

            mixins:[mixin],

            template:
                '<div class="postbox customer-mail-subscriber-info">'
                    + '<div class="erp-handlediv" @click.prevent="handlePostboxToggle()" title="Click to toggle"><br></div>'
                    + '<h3 class="erp-hndle" @click.prevent="handlePostboxToggle()"><span>{{ title }}</span></h3>'
                    + '<div class="inside contact-group-content">'
                        + '<div class="contact-group-list">'
                            + '<p v-if="isItems" v-for="item in items">{{ item.groups.name }}'
                                + '<tooltip :content="subscriberInfo( item )" :title="subscribeInfoToolTip(item)"></tooltip>'
                            + '</p>'
                            + '<div v-if="!isItems && !isPermitted">No group found</div>'
                            + '<a href="#" v-if="isPermitted" @click.prevent="assigContactGroup()" id="erp-contact-update-assign-group" data-id="" title="{{ addButtonTxt }}"><i class="fa fa-plus"></i> {{ addButtonTxt }}</a>'
                        + '</div>'
                    + '</div>'
                + '</div><!-- .postbox -->',

            data: function() {
                return {
                    items: [],
                    isItems: false
                }
            },

            computed: {
                isItems: function() {
                    return this.items.length > 0;
                }
            },

            methods: {

                subscriberInfo: function( item ) {
                    return '<i class="fa fa-info-circle"></i>';
                },

                subscribeInfoToolTip: function ( item ) {
                    if ( 'subscribe' == item.status ) {
                        return 'Subscribed at ' + wperp.dateFormat( item.subscribe_at, 'Y-m-d' );
                    } else {
                        return 'Unsubscribed at ' + wperp.dateFormat( item.unsubscribe_at, 'Y-m-d' );
                    }

                    return '';
                },

                assigContactGroup: function() {
                    var self = this,
                    query_id = self.id;

                    $.erpPopup({
                        title: self.title,
                        button: wpErpCrm.update_submit,
                        id: 'erp-crm-edit-contact-subscriber',
                        extraClass: 'smaller',
                        onReady: function() {
                            var modal = this;

                            $( 'header', modal).after( $('<div class="loader"></div>').show() );

                            wp.ajax.send( 'erp-crm-edit-contact-subscriber', {
                                data: {
                                    id: query_id,
                                    _wpnonce: wpErpCrm.nonce
                                },
                                success: function( res ) {
                                    var html = wp.template( 'erp-crm-assign-subscriber-contact' )( { group_id : res.groups, user_id: query_id } );
                                    $( '.content', modal ).html( html );
                                    _.each( $( 'input[type=checkbox].erp-crm-contact-group-class' ), function( el, i) {
                                        var optionsVal = $(el).val();
                                        if( _.contains( res.groups, optionsVal ) && res.results[optionsVal].status == 'subscribe' ) {
                                            $(el).prop('checked', true );
                                        }
                                        if ( _.contains( res.groups, optionsVal ) && res.results[optionsVal].status == 'unsubscribe' ) {
                                            $(el).closest('label').find('span.checkbox-value')
                                                .append('<span class="unsubscribe-group">' + res.results[optionsVal].unsubscribe_message + '</span>');
                                        };
                                    });

                                    $( '.loader', modal ).remove();
                                }
                            });
                        },

                        onSubmit: function(modal) {
                            modal.disableButton();

                            wp.ajax.send( {
                                data: this.serialize(),
                                success: function(res) {
                                    self.fetchData();
                                    modal.enableButton();
                                    modal.closeModal();
                                },
                                error: function(error) {
                                    modal.enableButton();
                                    modal.showError( error );
                                }
                            });
                        }

                    });
                },

                fetchData: function() {
                    var self = this,
                        data = {
                            id: this.id,
                            action: 'erp-crm-get-assignable-group',
                            _wpnonce: wpErpCrm.nonce
                        };

                    jQuery.post( wpErpCrm.ajaxurl, data, function( resp ) {
                        if ( resp.success ) {
                            self.items = resp.data;
                        } else {
                            alert(resp);
                        }
                    } );
                }
            },

            ready: function() {
                this.fetchData();
            }
        });

        var contactSingle = new Vue({
            el: '#wp-erp',

            mixins: [mixin],

            methods: {

                editContact: function( type, id, title ) {
                    var self = this;

                    $.erpPopup({
                        title: title,
                        button: wpErpCrm.update_submit,
                        id: 'erp-customer-edit',
                        onReady: function() {
                            var modal = this;

                            $( 'header', modal).after( $('<div class="loader"></div>').show() );

                            wp.ajax.send( 'erp-crm-customer-get', {
                                data: {
                                    id: id,
                                    _wpnonce: wpErpCrm.nonce
                                },
                                success: function( response ) {
                                    var html = wp.template('erp-crm-new-contact')( response );
                                    $( '.content', modal ).html( html );
                                    $( '.loader', modal).remove();

                                    $( 'li[data-selected]', modal ).each(function() {
                                        var self = $(this),
                                            selected = self.data('selected');

                                        if ( selected !== '' ) {
                                            self.find( 'select' ).val( selected );
                                        }
                                    });

                                    $('select#erp-customer-type').trigger('change');
                                    $( 'select.erp-country-select').change();

                                    $( 'li[data-selected]', modal ).each(function() {
                                        var self = $(this),
                                            selected = self.data('selected');

                                        if ( selected !== '' ) {
                                            self.find( 'select' ).val( selected );
                                        }
                                    });

                                    _.each( $( 'input[type=checkbox].erp-crm-contact-group-class' ), function( el, i) {
                                        var optionsVal = $(el).val();
                                        if( _.contains( response.group_id, optionsVal ) ) {
                                            $(el).prop('checked', true );
                                        }
                                    });

                                    self.initFields();
                                }
                            });
                        },
                        onSubmit: function(modal) {
                            modal.disableButton();

                            wp.ajax.send( {
                                data: this.serialize(),
                                success: function(response) {
                                    modal.enableButton();
                                    modal.closeModal();
                                    $( '.erp-single-customer-row' ).load( window.location.href + ' .left-content' );
                                },
                                error: function(error) {
                                    modal.enableButton();
                                    modal.showError( error );
                                }
                            });
                        }
                    });
                },

                assignContact: function() {
                    var mainWrap = $(event.target).closest('.erp-crm-assign-contact');

                    mainWrap.find('.user-wrap').hide();
                    mainWrap.find('span#erp-crm-edit-assign-contact-to-agent').hide();
                    this.initSearchCrmAgent();
                    mainWrap.find('.assign-form').fadeIn();
                },

                saveAssignContact: function() {
                    var self = this;

                    var target = $(event.target),
                        form = target.closest('form'),
                        data = {
                            action : 'erp-crm-save-assign-contact',
                            _wpnonce: wpErpCrm.nonce,
                            formData: form.serialize()
                        };

                    form.find('.assign-form-loader').removeClass('erp-hide');

                    wp.ajax.send( {
                        data: data,
                        success: function( res ) {
                            $('.user-wrap').load( window.location.href + ' .user-wrap-content', function() {
                                self.initSearchCrmAgent();
                                form.find('.assign-form-loader').addClass('erp-hide');
                                var mainWrap = target.closest('.erp-crm-assign-contact');
                                mainWrap.find('.assign-form').hide();
                                mainWrap.find('.user-wrap').fadeIn();
                                mainWrap.find('span#erp-crm-edit-assign-contact-to-agent').fadeIn();
                            } );
                        },
                        error: function(error) {
                            form.find('.assign-form-loader').addClass('erp-hide');
                            alert( error );
                        }
                    });
                },

                cancelAssignContact: function() {
                    var target = $(event.target);
                    var mainWrap = target.closest('.erp-crm-assign-contact');
                    mainWrap.find('span#erp-crm-edit-assign-contact-to-agent').fadeIn();
                    mainWrap.find('.assign-form').hide();
                    mainWrap.find('.user-wrap').fadeIn();
                }
            },

            ready: function() {
                $( 'body' ).on( 'click', 'a#erp-set-customer-photo', this.setPhoto );
                $( 'body' ).on( 'click', 'a.erp-remove-photo', this.removePhoto );
            }
        });
    }

})(jQuery, window.wperp );

