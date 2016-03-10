/**
 * Save Search Component
 *
 * @return {void}
 */
Vue.component( 'save-search', {
    props: {
        searchFields: {
            type: Object,
            default: function() {
                return {};
            }
        },
        index: '',
        totalSearchItem:'',
    },

    template: '#erp-crm-save-search-item',

    data: function() {
        return {
            andSelection: '',
            orSelection: '',
            searchData: [],
            isdisabled:false,
            marginClass: {
                'marginbottomonly' : true
            }
        }
    },

    watch: {
        searchFields: {
            deep: true,
            immediate: true,
            handler: function () {
                jQuery('.selecttwo').trigger('change');

                if ( this.isdisabled ) {
                    this.marginClass.marginbottomonly = false;
                } else {
                    this.marginClass.marginbottomonly = true;
                }
            }
        },

        andSelection: function( newVal, oldVal ){
            this.andAdd( this.index );
        },

        orSelection: function( newVal, oldVal ){
            this.orAdd( this.index );
        }

    },

    computed: {
        isdisabled: function() {
            var hasValue = [];

            if ( !_.isEmpty( this.searchFields ) ) {
                _.each( this.searchFields, function( val ) {
                    if ( _.isEmpty( val ) ) {
                        hasValue.push(true);
                    } else {
                        hasValue.push(false);
                    }
                });

                if ( _.contains( hasValue, false ) ) {
                    return false;
                } else {
                    return true;
                }

            } else {
                return true;
            }
            // console.log( hasValue );

            return true;
        }
    },

    methods: {

        andAdd: function( index ) {

            if ( ! this.andSelection ) {
                return;
            }

            if ( !saveSearch.searchItem[index].hasOwnProperty(this.andSelection) ) {
                saveSearch.$set('searchItem[' + index +']["' + this.andSelection + '"]', []);
            }

            var obj = jQuery.extend({}, wpCRMSaveSearch.searchFields[this.andSelection]);

            if ( wpCRMSaveSearch.searchFields[this.andSelection].hasOwnProperty('options') ) {
                obj.options = wpCRMSaveSearch.searchFields[this.andSelection].options;
            }

            saveSearch.searchItem[index][this.andSelection].push( obj );

            this.andSelection = '';

        },

        orAdd: function(){
            if ( ! this.orSelection ) {
                return;
            }

            var object = {};

            var obj = jQuery.extend({}, wpCRMSaveSearch.searchFields[this.orSelection]);

            object[ this.orSelection ] = [obj]

            saveSearch.searchItem.push( object );

            this.orSelection = '';
        },

        hasValue: function( obj, key, value ) {
            return obj.hasOwnProperty(key) && obj[key] === value;
        },

        removeSearchField: function( searchVal, searchField ) {

            searchVal.$remove(searchField);

            var isEmpty = true;
            jQuery.each(this.searchFields, function () {
                if (this.length) {
                    isEmpty = false;
                }
            });

            if (isEmpty) {
                if ( saveSearch.searchItem.length == 1 )  {
                    return;
                }
                saveSearch.searchItem.$remove(this.searchFields);
            }

        }
    }
});

var saveSearch = new Vue({
    el: '#erp-crm-save-search',

    data: {
        searchItem: [],
        totalSearchItem: 0,
        isNewSave:false,
        saveSearchOptions: {},
        saveSearchData: '',
        classObject: {
            'border-top-only': true
        },
        searchFilterButtonText: 'Advance Search'
    },


    created: function() {
        this.renderSearchFields();
        this.renderSaveSearchOptions();
    },

    watch: {
        searchItem: {
            deep: true,
            immediate: true,
            handler: function () {
                jQuery('.selecttwo').trigger('change');
                this.totalSearchItem = this.searchItem.length;
            }
        },

        saveSearchOptions: {
            deep: true,
            immediate: true,
            handler: function () {
                jQuery('.selecttwo').trigger('change');
            }
        }
    },

    methods: {

        toggleAdvanceSearchFilter: function() {
            this.showAdvanceFilter = !this.showAdvanceFilter;

            if ( this.showAdvanceFilter ) {
                this.classObject = {
                    'border-top-only': false
                }
            } else {
                this.classObject = {
                    'border-top-only': true
                }
            }
        },

        renderSaveSearchOptions: function() {
            var self = this,
                data = {
                    action : 'erp_crm_get_save_search_item',
                    _wpnonce : wpCRMSaveSearch.nonce
                };

            jQuery.get( wpCRMSaveSearch.ajaxurl, data, function( resp ) {
                if ( resp.success ) {
                    self.saveSearchOptions = resp.data;
                }
            });

        },

        createNewSearch: function(e) {
            var self = this,
                form = jQuery('#erp-crm-save-search-form'),
                data = {
                    action : 'erp_crm_create_new_save_search',
                    form_data : form.serialize(),
                    _wpnonce : wpCRMSaveSearch.nonce
                }

            jQuery.post( wpCRMSaveSearch.ajaxurl, data, function( resp ) {
                if ( resp.success ) {

                    if ( resp.data.global == '0'  ) {
                        jQuery('#erp_customer_filter_by_save_searches')
                            .find( 'optgroup#own-search' )
                            .append('<option value="'+resp.data.id+'">'+resp.data.search_name+'</option>');
                    } else {
                        jQuery('#erp_customer_filter_by_save_searches')
                            .find( 'optgroup#global-search' )
                            .append('<option value="'+resp.data.id+'">'+resp.data.search_name+'</option>');
                    }

                    jQuery('#erp_customer_filter_by_save_searches').select2().select2('val', resp.data.id);
                    jQuery('#erp_customer_filter_by_save_searches').trigger('change');

                    self.isNewSave = false;

                    // var obj = {};

                    // if ( resp.data.global == '0' ) {

                    //     obj.id=resp.data.id.toString();
                    //     obj.text=resp.data.search_name;
                    //     obj.selected='selected';

                    //     self.saveSearchOptions[0].options.push( obj );
                    // } else {

                    //     obj.id=resp.data.id.toString();
                    //     obj.text=resp.data.search_name;
                    //     obj.selected='selected';

                    //     self.saveSearchOptions[1].options.push( obj )
                    // }

                    // self.saveSearchData = obj.id;

                } else {
                    alert( resp.data );
                };
            });
        },

        saveSearch: function(){
            this.isNewSave = !this.isNewSave;
        },

        cancelSaveSearch: function() {
            this.isNewSave = false;
        },

        renderSearchFields: function() {
            var self = this;
            var queryString = window.location.search;
            var orSelection = queryString.split('&or&');
            var res = [];

            _.each( orSelection, function( orSelect, index ) {
                var arr = {};
                var result = {};
                var mainObj = {};
                var keys = Object.keys( wpCRMSaveSearch.searchFields );

                self.parse_str( orSelect, arr );

                for ( type in arr ) {
                    if ( keys.indexOf(type) > -1) {
                        result[type] = arr[type];
                    }
                }

                _.each( result, function( value, index ) {
                    var fieldArr = [];

                    if ( _.isObject( value ) ) {
                        _.each( value, function( val, i ) {
                            var obj = {};

                            var seachVal  = self.parseCondition(val);
                            obj.title     = wpCRMSaveSearch.searchFields[index].title;
                            obj.type      = wpCRMSaveSearch.searchFields[index].type;
                            obj.text      = seachVal.val;
                            obj.condval   = seachVal.condition;
                            obj.condition = wpCRMSaveSearch.searchFields[index].condition;

                            if ( obj.type == 'dropdown' ) {
                                obj.options = wpCRMSaveSearch.searchFields[index].options;
                            }

                            fieldArr.push(obj);
                            mainObj[index] = fieldArr;

                        });
                    } else {
                        var obj = {};
                        var seachVal = self.parseCondition(value);
                        obj.title     = wpCRMSaveSearch.searchFields[index].title;
                        obj.type      = wpCRMSaveSearch.searchFields[index].type;
                        obj.text      = seachVal.val;
                        obj.condval   = seachVal.condition;
                        obj.condition = wpCRMSaveSearch.searchFields[index].condition;

                        if ( obj.type == 'dropdown' ) {
                            obj.options = wpCRMSaveSearch.searchFields[index].options;
                        }

                        fieldArr.push(obj);
                        mainObj[index] = fieldArr;
                    }
                });

                res.push( mainObj );
            });

            this.searchItem = res;
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

        parse_str: function(str, array) {

            var strArr = String(str)
                .replace(/^&/, '')
                .replace(/^\?/, '')
                .replace(/&$/, '')
                .split('&'),
                sal = strArr.length,
                i, j, ct, p, lastObj, obj, lastIter, undef, chr, tmp, key, value,
                postLeftBracketPos, keys, keysLen,
                fixStr = function(str) {
                    return decodeURIComponent(str.replace(/\+/g, '%20'));
                };

            if (!array) {
                array = this.window;
            }

            for (i = 0; i < sal; i++) {
                tmp = strArr[i].split('=');
                key = fixStr(tmp[0]);
                value = (tmp.length < 2) ? '' : fixStr(tmp[1]);

                while (key.charAt(0) === ' ') {
                    key = key.slice(1);
                }
                if (key.indexOf('\x00') > -1) {
                    key = key.slice(0, key.indexOf('\x00'));
                }
                if (key && key.charAt(0) !== '[') {
                    keys = [];
                    postLeftBracketPos = 0;
                    for (j = 0; j < key.length; j++) {
                        if (key.charAt(j) === '[' && !postLeftBracketPos) {
                            postLeftBracketPos = j + 1;
                        } else if (key.charAt(j) === ']') {
                            if (postLeftBracketPos) {
                                if (!keys.length) {
                                    keys.push(key.slice(0, postLeftBracketPos - 1));
                                }
                                keys.push(key.substr(postLeftBracketPos, j - postLeftBracketPos));
                                postLeftBracketPos = 0;
                                if (key.charAt(j + 1) !== '[') {
                                    break;
                                }
                            }
                        }
                    }
                    if (!keys.length) {
                        keys = [key];
                    }
                    for (j = 0; j < keys[0].length; j++) {
                        chr = keys[0].charAt(j);
                        if (chr === ' ' || chr === '.' || chr === '[') {
                            keys[0] = keys[0].substr(0, j) + '_' + keys[0].substr(j + 1);
                        }
                        if (chr === '[') {
                            break;
                        }
                    }

                    obj = array;
                    for (j = 0, keysLen = keys.length; j < keysLen; j++) {
                        key = keys[j].replace(/^['"]/, '')
                            .replace(/['"]$/, '');
                        lastIter = j !== keys.length - 1;
                        lastObj = obj;
                        if ((key !== '' && key !== ' ') || j === 0) {
                            if (obj[key] === undef) {
                                obj[key] = {};
                            }
                            obj = obj[key];
                        } else {
                            // To insert new dimension
                            ct = -1;
                            for (p in obj) {
                                if (obj.hasOwnProperty(p)) {
                                    if (+p > ct && p.match(/^\d+$/g)) {
                                        ct = +p;
                                    }
                                }
                            }
                            key = ct + 1;
                        }
                    }
                    lastObj[key] = value;
                }
            }
        }
    }
});
