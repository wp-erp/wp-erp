pluginWebpack([0],[
/* 0 */,
/* 1 */,
/* 2 */,
/* 3 */,
/* 4 */,
/* 5 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_ListTable_vue__ = __webpack_require__(15);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_5a558fde_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_ListTable_vue__ = __webpack_require__(58);
var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(52)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_ListTable_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_5a558fde_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_ListTable_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "assets/src/admin/components/ListTable/ListTable.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-5a558fde", Component.options)
  } else {
    hotAPI.reload("data-v-5a558fde", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 6 */,
/* 7 */,
/* 8 */,
/* 9 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["a"] = ({
    name: 'Accounting'
});

/***/ }),
/* 10 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__WP_MetaBox_vue__ = __webpack_require__(41);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__Menu_ERPMenu_vue__ = __webpack_require__(44);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//




/* harmony default export */ __webpack_exports__["a"] = ({

    name: 'Dashboard',

    components: {
        MetaBox: __WEBPACK_IMPORTED_MODULE_0__WP_MetaBox_vue__["a" /* default */],
        ERPMenu: __WEBPACK_IMPORTED_MODULE_1__Menu_ERPMenu_vue__["a" /* default */]
    },

    data() {
        return {
            title1: 'Income & Expenses',
            title2: 'Bank Accounts',
            title3: 'Invoices owed to you',
            title4: 'Bills to pay',
            closable: true,
            msg: 'Accounting'
        };
    }
});

/***/ }),
/* 11 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["a"] = ({
    name: 'MetaBox',
    props: {
        title: String,
        closable: Boolean
    },
    data() {
        return {
            closed: false
        };
    },
    computed: {
        classes() {
            return ['postbox', this.closed ? 'closed' : ''];
        },
        styles() {
            return 'display: block;';
        }
    },
    methods: {
        handleToggle(event) {
            this.closed = !this.closed;
            this.$emit('metaboxToggle', event);
        }
    }
});

/***/ }),
/* 12 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["a"] = ({
    name: 'ERPMenu',

    props: {},
    data() {
        return {
            menuItems: erp_acct_var.erp_acct_menus
        };
    },
    created: function () {
        this.init();
    },

    methods: {
        init: function () {

            const container = document.querySelector('.erp-nav-container');

            if (container == null) {
                return;
            }
            primary = container.querySelector('.-primary');

            primaryItems = container.querySelectorAll('.-primary > li:not(.-more)');
            container.classList.add('--jsfied');

            // insert "more" button and duplicate the list
            primary.insertAdjacentHTML('beforeend', '<li class="-more"><button type="button" aria-haspopup="true" aria-expanded="false">More <span class="dashicons dashicons-arrow-down-alt2"></span></button><ul class="-secondary">' + primary.innerHTML + '</ul></li>');
            secondary = container.querySelector('.-secondary');
            secondaryItems = [].slice.call(secondary.children);
            allItems = container.querySelectorAll('li');
            moreLi = primary.querySelector('.-more');
            moreBtn = moreLi.querySelector('button');
            moreBtn.addEventListener('click', function (e) {
                e.preventDefault();
                container.classList.toggle('--show-secondary');
                moreBtn.setAttribute('aria-expanded', container.classList.contains('--show-secondary'));
            });

            // adapt tabs
            var doAdapt = function doAdapt() {
                // reveal all items for the calculation
                allItems.forEach(function (item) {
                    item.classList.remove('--hidden');
                });

                // hide items that won't fit in the Primary
                stopWidth = moreBtn.offsetWidth;
                hiddenItems = [];
                primaryWidth = primary.offsetWidth;
                primaryItems.forEach(function (item, i) {
                    if (primaryWidth >= stopWidth + item.offsetWidth) {
                        stopWidth += item.offsetWidth;
                    } else {
                        item.classList.add('--hidden');
                        hiddenItems.push(i);
                    }
                });

                // toggle the visibility of More button and items in Secondary
                if (!hiddenItems.length) {
                    moreLi.classList.add('--hidden');
                    container.classList.remove('--show-secondary');
                    moreBtn.setAttribute('aria-expanded', false);
                } else {
                    secondaryItems.forEach(function (item, i) {
                        if (!hiddenItems.includes(i)) {
                            item.classList.add('--hidden');
                        }
                    });
                }
            };

            doAdapt(); // adapt immediately on load
            window.addEventListener('resize', doAdapt); // adapt on window resize

            // hide Secondary on the outside click
            document.addEventListener('click', function (e) {
                var el = e.target;
                while (el) {
                    if (el === secondary || el === moreBtn) {
                        return;
                    }
                    el = el.parentNode;
                }
                container.classList.remove('--show-secondary');
                moreBtn.setAttribute('aria-expanded', false);
            });
        }
    }
});

/***/ }),
/* 13 */
/***/ (function(module, exports) {

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/***/ }),
/* 14 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__ListTable_ListTable_vue__ = __webpack_require__(5);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//



/* harmony default export */ __webpack_exports__["a"] = ({
    name: 'Customers',
    components: {
        ListTable: __WEBPACK_IMPORTED_MODULE_0__ListTable_ListTable_vue__["a" /* default */]
    },
    data() {
        return {
            bulkActions: [{
                key: 'trash',
                label: 'Move to Trash'
            }],
            columns: {
                'customer': { label: 'Customer Name' },
                'company': { label: 'Company' },
                'email': { label: 'Email' },
                'phone': { label: 'Phone' },
                'expense': { label: 'Expense' },
                'actions': { label: 'Actions' }
            },
            rows: [{
                id: 1,
                customer: 'John Smith',
                company: 'Com 1',
                email: 'asd@gmail.com',
                phone: '+32834239',
                expense: '20000'
            }, {
                id: 2,
                customer: 'John Doe',
                company: 'Com 2',
                email: 'fgh@gmail.com',
                phone: '+235235234',
                expense: '324234'
            }]
        };
    },
    created() {
        this.$on('modal-close', function () {
            this.showModal = false;
        });
    }
});

/***/ }),
/* 15 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__BulkActionsTpl_vue__ = __webpack_require__(53);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__RowActions_vue__ = __webpack_require__(55);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//




/* harmony default export */ __webpack_exports__["a"] = ({

    name: 'ListTable',

    components: {
        BulkActionsTpl: __WEBPACK_IMPORTED_MODULE_0__BulkActionsTpl_vue__["a" /* default */],
        RowActions: __WEBPACK_IMPORTED_MODULE_1__RowActions_vue__["a" /* default */]
    },

    props: {
        columns: {
            type: Object,
            required: true,
            default: () => {}
        },
        rows: {
            type: Array, // String, Number, Boolean, Function, Object, Array
            required: true,
            default: () => []
        },
        index: {
            type: String,
            default: 'id'
        },
        showCb: {
            type: Boolean,
            default: true
        },
        loading: {
            type: Boolean,
            default: false
        },
        actionColumn: {
            type: String,
            default: ''
        },
        actions: {
            type: Array,
            required: false,
            default: () => []
        },
        bulkActions: {
            type: Array,
            required: false,
            default: () => []
        },
        tableClass: {
            type: String,
            default: 'wp-list-table widefat fixed striped'
        },
        notFound: {
            type: String,
            default: 'No items found.'
        },
        totalItems: {
            type: Number,
            default: 0
        },
        totalPages: {
            type: Number,
            default: 1
        },
        perPage: {
            type: Number,
            default: 20
        },
        currentPage: {
            type: Number,
            default: 1
        },
        sortBy: {
            type: String,
            default: null
        },
        sortOrder: {
            type: String,
            default: 'asc'
        }
    },

    data() {
        return {
            bulkLocal: '-1',
            checkedItems: []
        };
    },

    computed: {

        hasActions() {
            return this.actions.length > 0;
        },

        itemsTotal() {
            return this.totalItems || this.rows.length;
        },

        hasPagination() {
            return this.itemsTotal > this.perPage;
        },

        disableFirst() {
            if (this.currentPage === 1 || this.currentPage === 2) {
                return true;
            }

            return false;
        },

        disablePrev() {
            if (this.currentPage === 1) {
                return true;
            }

            return false;
        },

        disableNext() {
            if (this.currentPage === this.totalPages) {
                return true;
            }

            return false;
        },

        disableLast() {
            if (this.currentPage === this.totalPages || this.currentPage == this.totalPages - 1) {
                return true;
            }

            return false;
        },

        columnsCount() {
            return Object.keys(this.columns).length;
        },

        colspan() {
            let columns = Object.keys(this.columns).length;

            if (this.showCb) {
                columns += 1;
            }

            return columns;
        },

        selectAll: {

            get() {
                if (!this.rows.length) {
                    return false;
                }

                return this.rows ? this.checkedItems.length == this.rows.length : false;
            },

            set(value) {
                const selected = [];

                const self = this;

                if (value) {
                    this.rows.forEach(item => {
                        if (item[self.index] !== undefined) {
                            selected.push(item[self.index]);
                        } else {
                            selected.push(item.id);
                        }
                    });
                }

                this.checkedItems = selected;
            }
        }
    },

    created() {
        this.$on('bulk-chkbx', e => {
            if (!e) {
                this.checkedItems = [];
            }
        });
    },

    methods: {

        hideActionSeparator(action) {
            return action === this.actions[this.actions.length - 1].key;
        },

        actionClicked(action, row) {
            this.$emit('action:click', action, row);
        },

        goToPage(page) {
            this.$emit('pagination', page);
        },

        goToCustomPage(event) {
            const page = parseInt(event.target.value, 10);

            if (!isNaN(page) && page > 0 && page <= this.totalPages) {
                this.$emit('pagination', page);
            }
        },

        handleBulkAction() {
            if (this.bulkLocal === '-1') {
                return;
            }

            this.$emit('bulk:click', this.bulkLocal, this.checkedItems);
        },

        isSortable(column) {
            if (column.hasOwnProperty('sortable') && column.sortable === true) {
                return true;
            }

            return false;
        },

        isSorted(column) {
            return column === this.sortBy;
        },

        handleSortBy(column) {
            const order = this.sortOrder === 'asc' ? 'desc' : 'asc';

            this.$emit('sort', column, order);
        }
    }
});

/***/ }),
/* 16 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["a"] = ({
    name: 'BulkActionsTpl',

    props: {
        bulkActions: {
            type: Array,
            required: false,
            default: () => []
        },

        showCb: {
            type: Boolean,
            default: true
        },

        columnsCount: {
            type: Number,
            default: 0
        },

        selectAll: {
            type: Boolean,
            default: false
        }
    },

    data() {
        return {
            bulkSelectAll: this.selectAll
        };
    },

    computed: {
        hasBulkActions() {
            return this.bulkActions.length > 0;
        }
    },

    methods: {
        changeBulkCheckbox() {
            this.$parent.$emit('bulk-chkbx', this.bulkSelectAll);
        }
    }
});

/***/ }),
/* 17 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["a"] = ({
    name: 'RowActions',

    props: {
        row: {
            type: Object,
            required: true
        },
        actions: {
            type: Array,
            required: false,
            default: () => []
        }
    },

    data() {
        return {
            showActions: false
        };
    },

    methods: {
        toggleActions() {
            this.showActions = !this.showActions;
        },

        actionClicked(actionKey, row) {
            console.log(actionKey, row);
        }
    }
});

/***/ }),
/* 18 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__ListTable_ListTable_vue__ = __webpack_require__(5);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//



/* harmony default export */ __webpack_exports__["a"] = ({
    name: 'Vendors',
    components: {
        ListTable: __WEBPACK_IMPORTED_MODULE_0__ListTable_ListTable_vue__["a" /* default */]
    },
    data() {
        return {
            bulkActions: [{
                key: 'trash',
                label: 'Move to Trash'
            }],
            columns: {
                'vendor': { label: 'Vendor Name' },
                'company': { label: 'Vendor Owner' },
                'email': { label: 'Email' },
                'phone': { label: 'Phone' },
                'expense': { label: 'Expense' },
                'actions': { label: 'Actions' }
            },
            rows: [{
                id: 1,
                vendor: 'John Smith',
                company: 'Com 1',
                email: 'asd@gmail.com',
                phone: '+32834239',
                expense: '20000'
            }, {
                id: 2,
                vendor: 'John Doe',
                company: 'Com 2',
                email: 'fgh@gmail.com',
                phone: '+235235234',
                expense: '324234'
            }]
        };
    },
    created() {
        this.$on('modal-close', function () {
            this.showModal = false;
        });
    }
});

/***/ }),
/* 19 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__ListTable_ListTable_vue__ = __webpack_require__(5);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//



/* harmony default export */ __webpack_exports__["a"] = ({
    name: 'Employees',
    components: {
        ListTable: __WEBPACK_IMPORTED_MODULE_0__ListTable_ListTable_vue__["a" /* default */]
    },
    data() {
        return {
            bulkActions: [{
                key: 'trash',
                label: 'Move to Trash'
            }],
            columns: {
                'employee': { label: 'Employee Name' },
                'company': { label: 'Company' },
                'email': { label: 'Email' },
                'phone': { label: 'Phone' },
                'expense': { label: 'Expense' },
                'actions': { label: 'Actions' }
            },
            rows: [{
                id: 1,
                employee: 'John Smith',
                company: 'Com 1',
                email: 'asd@gmail.com',
                phone: '+32834239',
                expense: '20000'
            }, {
                id: 2,
                employee: 'John Doe',
                company: 'Com 2',
                email: 'fgh@gmail.com',
                phone: '+235235234',
                expense: '324234'
            }]
        };
    },
    created() {
        this.$on('modal-close', function () {
            this.showModal = false;
        });
    }
});

/***/ }),
/* 20 */,
/* 21 */,
/* 22 */,
/* 23 */,
/* 24 */,
/* 25 */,
/* 26 */,
/* 27 */,
/* 28 */,
/* 29 */,
/* 30 */,
/* 31 */,
/* 32 */,
/* 33 */,
/* 34 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _vue = __webpack_require__(1);

var _vue2 = _interopRequireDefault(_vue);

var _App = __webpack_require__(35);

var _App2 = _interopRequireDefault(_App);

var _router = __webpack_require__(38);

var _router2 = _interopRequireDefault(_router);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

_vue2.default.config.productionTip = false;

/* eslint-disable no-new */
new _vue2.default({
    el: '#erp-accounting',
    router: _router2.default,
    render: function render(h) {
        return h(_App2.default);
    }
});

/***/ }),
/* 35 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_App_vue__ = __webpack_require__(9);
/* empty harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_6bc4b6d8_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_App_vue__ = __webpack_require__(37);
var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(36)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_App_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_6bc4b6d8_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_App_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "assets/src/admin/App.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-6bc4b6d8", Component.options)
  } else {
    hotAPI.reload("data-v-6bc4b6d8", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["default"] = (Component.exports);


/***/ }),
/* 36 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 37 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", { attrs: { id: "erp-accounting" } }, [_c("router-view")], 1)
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-6bc4b6d8", esExports)
  }
}

/***/ }),
/* 38 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _vue = __webpack_require__(1);

var _vue2 = _interopRequireDefault(_vue);

var _vueRouter = __webpack_require__(4);

var _vueRouter2 = _interopRequireDefault(_vueRouter);

var _Dashboard = __webpack_require__(39);

var _Dashboard2 = _interopRequireDefault(_Dashboard);

var _ChartOfAccounts = __webpack_require__(48);

var _ChartOfAccounts2 = _interopRequireDefault(_ChartOfAccounts);

var _Customers = __webpack_require__(50);

var _Customers2 = _interopRequireDefault(_Customers);

var _Vendors = __webpack_require__(60);

var _Vendors2 = _interopRequireDefault(_Vendors);

var _Employees = __webpack_require__(63);

var _Employees2 = _interopRequireDefault(_Employees);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

_vue2.default.use(_vueRouter2.default);

exports.default = new _vueRouter2.default({
    routes: [{
        path: '/',
        name: 'Dashboard',
        component: _Dashboard2.default
    }, {
        path: '/erp-accounting-charts',
        name: 'ChartOfAccounts',
        component: _ChartOfAccounts2.default
    }, {
        path: '/erp-accounting-customers',
        name: 'Customers',
        component: _Customers2.default
    }, {
        path: '/erp-accounting-vendors',
        name: 'Vendors',
        component: _Vendors2.default
    }, {
        path: '/erp-accounting-employees',
        name: 'Employees',
        component: _Employees2.default
    }]
});

/***/ }),
/* 39 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_Dashboard_vue__ = __webpack_require__(10);
/* empty harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_45a0d6f4_hasScoped_true_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_Dashboard_vue__ = __webpack_require__(47);
var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(40)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = "data-v-45a0d6f4"
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_Dashboard_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_45a0d6f4_hasScoped_true_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_Dashboard_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "assets/src/admin/components/Dashboard.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-45a0d6f4", Component.options)
  } else {
    hotAPI.reload("data-v-45a0d6f4", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["default"] = (Component.exports);


/***/ }),
/* 40 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 41 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_MetaBox_vue__ = __webpack_require__(11);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_eca6e040_hasScoped_true_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_MetaBox_vue__ = __webpack_require__(43);
var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(42)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = "data-v-eca6e040"
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_MetaBox_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_eca6e040_hasScoped_true_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_MetaBox_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "assets/src/admin/components/WP/MetaBox.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-eca6e040", Component.options)
  } else {
    hotAPI.reload("data-v-eca6e040", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 42 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 43 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", { class: _vm.classes, style: _vm.styles }, [
    _vm.closable
      ? _c(
          "button",
          {
            staticClass: "handlediv",
            attrs: { type: "button" },
            on: { click: _vm.handleToggle }
          },
          [
            _vm.closed
              ? _c("span", { staticClass: "dashicons dashicons-arrow-down" })
              : _c("span", { staticClass: "dashicons dashicons-arrow-up" })
          ]
        )
      : _vm._e(),
    _vm._v(" "),
    _c("h3", { staticClass: "hndle ui-sortable-handle" }, [
      _c("span", { staticClass: "wp-metabox-title" }, [
        _vm._v(_vm._s(_vm.title))
      ])
    ]),
    _vm._v(" "),
    _c("div", { staticClass: "inside" }, [
      _c(
        "div",
        { staticClass: "main" },
        [
          _vm._t("metabox-content"),
          _vm._v(" "),
          _c(
            "div",
            { staticClass: "wp-metabox-footer" },
            [_vm._t("metabox-footer")],
            2
          )
        ],
        2
      )
    ])
  ])
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-eca6e040", esExports)
  }
}

/***/ }),
/* 44 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_ERPMenu_vue__ = __webpack_require__(12);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_77b17d3c_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_ERPMenu_vue__ = __webpack_require__(46);
var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(45)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_ERPMenu_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_77b17d3c_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_ERPMenu_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "assets/src/admin/components/Menu/ERPMenu.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-77b17d3c", Component.options)
  } else {
    hotAPI.reload("data-v-77b17d3c", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 45 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 46 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", { staticClass: "erp-nav-container" }, [
    _vm._m(0),
    _vm._v(" "),
    _c(
      "ul",
      { staticClass: "erp-nav -primary" },
      [
        _vm._l(_vm.menuItems, function(menu, index) {
          return [
            menu.hasOwnProperty("submenu")
              ? _c("li", { staticClass: "dropdown-nav" }, [
                  _c("a", { attrs: { href: "#" } }, [
                    _vm._v(_vm._s(menu.title))
                  ]),
                  _vm._v(" "),
                  _c(
                    "ul",
                    { staticClass: "erp-nav-dropdown" },
                    _vm._l(menu.submenu, function(item) {
                      return _c("li", [
                        _c("a", { attrs: { href: "#" } }, [
                          _vm._v(_vm._s(item.title))
                        ])
                      ])
                    })
                  )
                ])
              : [
                  _c("li", [
                    _c("a", { attrs: { href: "#" } }, [
                      _vm._v(_vm._s(menu.title))
                    ])
                  ])
                ]
          ]
        })
      ],
      2
    )
  ])
}
var staticRenderFns = [
  function() {
    var _vm = this
    var _h = _vm.$createElement
    var _c = _vm._self._c || _h
    return _c("div", { staticClass: "erp-page-header" }, [
      _c("div", { staticClass: "module-icon" }),
      _vm._v(" "),
      _c("h2", [_vm._v("Accounting")])
    ])
  }
]
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-77b17d3c", esExports)
  }
}

/***/ }),
/* 47 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", { staticClass: "home" }, [
    _c("span", [_c("ERPMenu")], 1),
    _vm._v(" "),
    _c("div", { staticClass: "erp accounting-dashboard" }, [
      _c("h2", [_c("span", [_vm._v(_vm._s(_vm.msg))])]),
      _vm._v(" "),
      _c("div", { staticClass: "erp-acc-dashboard-1" }, [
        _c(
          "div",
          { staticClass: "erp-acc-dashboard-1-col" },
          [
            _c("MetaBox", { attrs: { title: _vm.title1, closable: false } }, [
              _c(
                "h1",
                { attrs: { slot: "metabox-content" }, slot: "metabox-content" },
                [_vm._v("Metabox Content")]
              )
            ])
          ],
          1
        ),
        _vm._v(" "),
        _c(
          "div",
          { staticClass: "erp-acc-dashboard-1-col" },
          [
            _c("MetaBox", { attrs: { title: _vm.title2, closable: false } }, [
              _c(
                "h1",
                { attrs: { slot: "metabox-content" }, slot: "metabox-content" },
                [_vm._v("Metabox Content2")]
              )
            ])
          ],
          1
        )
      ]),
      _vm._v(" "),
      _c("div", { staticClass: "erp-acc-dashboard-2" }, [
        _c(
          "div",
          { staticClass: "erp-acc-dashboard-2-col" },
          [
            _c("MetaBox", { attrs: { title: _vm.title3, closable: false } }, [
              _c(
                "h1",
                { attrs: { slot: "metabox-content" }, slot: "metabox-content" },
                [
                  _c("table", [
                    _c("tr", [
                      _c("td", [_vm._v("Coming Due")]),
                      _vm._v(" "),
                      _c("td", { staticClass: "price" }, [_vm._v("$0.00")])
                    ]),
                    _vm._v(" "),
                    _c("tr", [
                      _c("td", [_vm._v("1-30 days overdue")]),
                      _vm._v(" "),
                      _c("td", { staticClass: "price" }, [_vm._v("$0.00")])
                    ]),
                    _vm._v(" "),
                    _c("tr", [
                      _c("td", [_vm._v("31-60 days overdue")]),
                      _vm._v(" "),
                      _c("td", { staticClass: "price" }, [_vm._v("$0.00")])
                    ]),
                    _vm._v(" "),
                    _c("tr", [
                      _c("td", [_vm._v("61-90 days overdue")]),
                      _vm._v(" "),
                      _c("td", { staticClass: "price" }, [_vm._v("$0.00")])
                    ]),
                    _vm._v(" "),
                    _c("tr", [
                      _c("td", [_vm._v("> 90 days overdue")]),
                      _vm._v(" "),
                      _c("td", { staticClass: "price" }, [_vm._v("$0.00")])
                    ])
                  ])
                ]
              )
            ])
          ],
          1
        ),
        _vm._v(" "),
        _c(
          "div",
          { staticClass: "erp-acc-dashboard-2-col" },
          [
            _c("MetaBox", { attrs: { title: _vm.title4, closable: false } }, [
              _c(
                "div",
                { attrs: { slot: "metabox-content" }, slot: "metabox-content" },
                [
                  _c("table", [
                    _c("tr", [
                      _c("td", [_vm._v("Coming Due")]),
                      _vm._v(" "),
                      _c("td", { staticClass: "price" }, [_vm._v("$0.00")])
                    ]),
                    _vm._v(" "),
                    _c("tr", [
                      _c("td", [_vm._v("1-30 days overdue")]),
                      _vm._v(" "),
                      _c("td", { staticClass: "price" }, [_vm._v("$0.00")])
                    ]),
                    _vm._v(" "),
                    _c("tr", [
                      _c("td", [_vm._v("31-60 days overdue")]),
                      _vm._v(" "),
                      _c("td", { staticClass: "price" }, [_vm._v("$0.00")])
                    ]),
                    _vm._v(" "),
                    _c("tr", [
                      _c("td", [_vm._v("61-90 days overdue")]),
                      _vm._v(" "),
                      _c("td", { staticClass: "price" }, [_vm._v("$0.00")])
                    ]),
                    _vm._v(" "),
                    _c("tr", [
                      _c("td", [_vm._v("> 90 days overdue")]),
                      _vm._v(" "),
                      _c("td", { staticClass: "price" }, [_vm._v("$0.00")])
                    ])
                  ])
                ]
              )
            ])
          ],
          1
        )
      ])
    ])
  ])
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-45a0d6f4", esExports)
  }
}

/***/ }),
/* 48 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_ChartOfAccounts_vue__ = __webpack_require__(13);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_ChartOfAccounts_vue___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_ChartOfAccounts_vue__);
/* harmony namespace reexport (unknown) */ for(var __WEBPACK_IMPORT_KEY__ in __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_ChartOfAccounts_vue__) if(__WEBPACK_IMPORT_KEY__ !== 'default') (function(key) { __webpack_require__.d(__webpack_exports__, key, function() { return __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_ChartOfAccounts_vue__[key]; }) }(__WEBPACK_IMPORT_KEY__));
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_02d5983b_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_ChartOfAccounts_vue__ = __webpack_require__(49);
var disposed = false
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = null
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_ChartOfAccounts_vue___default.a,
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_02d5983b_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_ChartOfAccounts_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "assets/src/admin/components/ChartOfAccounts.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-02d5983b", Component.options)
  } else {
    hotAPI.reload("data-v-02d5983b", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["default"] = (Component.exports);


/***/ }),
/* 49 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _vm._m(0)
}
var staticRenderFns = [
  function() {
    var _vm = this
    var _h = _vm.$createElement
    var _c = _vm._self._c || _h
    return _c("div", { staticClass: "account-chart" }, [
      _c("h3", [_vm._v("Chart of Accounts")]),
      _vm._v(" "),
      _c("table", { staticClass: "table widefat striped ac-chart-table" }, [
        _c("thead", [
          _c("tr", [
            _c("th", { staticClass: "col-code" }, [_vm._v("Code")]),
            _vm._v(" "),
            _c("th", { staticClass: "col-name" }, [_vm._v("Name")]),
            _vm._v(" "),
            _c("th", { staticClass: "col-type" }, [_vm._v("Type")]),
            _vm._v(" "),
            _c("th", { staticClass: "col-transactions" }, [_vm._v("Entries")]),
            _vm._v(" "),
            _c("th", { staticClass: "col-action" }, [_vm._v("Actions")])
          ])
        ]),
        _vm._v(" "),
        _c("tbody", [
          _c("tr", [
            _c("td", { staticClass: "col-code" }, [_vm._v("100")]),
            _vm._v(" "),
            _c("td", { staticClass: "col-name" }, [_vm._v("url")]),
            _vm._v(" "),
            _c("td", { staticClass: "col-type" }, [_vm._v("Type")]),
            _vm._v(" "),
            _c("td", { staticClass: "col-transactions" }, [_vm._v("100")]),
            _vm._v(" "),
            _c("td", { staticClass: "col-action" }, [_vm._v("Action")])
          ])
        ])
      ])
    ])
  }
]
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-02d5983b", esExports)
  }
}

/***/ }),
/* 50 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_Customers_vue__ = __webpack_require__(14);
/* empty harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_384dc50a_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_Customers_vue__ = __webpack_require__(59);
var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(51)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_Customers_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_384dc50a_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_Customers_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "assets/src/admin/components/Peoples/Customers.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-384dc50a", Component.options)
  } else {
    hotAPI.reload("data-v-384dc50a", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["default"] = (Component.exports);


/***/ }),
/* 51 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 52 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 53 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_BulkActionsTpl_vue__ = __webpack_require__(16);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_a3d30818_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_BulkActionsTpl_vue__ = __webpack_require__(54);
var disposed = false
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = null
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_BulkActionsTpl_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_a3d30818_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_BulkActionsTpl_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "assets/src/admin/components/ListTable/BulkActionsTpl.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-a3d30818", Component.options)
  } else {
    hotAPI.reload("data-v-a3d30818", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 54 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("tr", [
    _vm.showCb
      ? _c("td", { staticClass: "manage-column column-cb check-column" }, [
          _c("input", {
            directives: [
              {
                name: "model",
                rawName: "v-model",
                value: _vm.bulkSelectAll,
                expression: "bulkSelectAll"
              }
            ],
            attrs: { type: "checkbox" },
            domProps: {
              checked: Array.isArray(_vm.bulkSelectAll)
                ? _vm._i(_vm.bulkSelectAll, null) > -1
                : _vm.bulkSelectAll
            },
            on: {
              change: [
                function($event) {
                  var $$a = _vm.bulkSelectAll,
                    $$el = $event.target,
                    $$c = $$el.checked ? true : false
                  if (Array.isArray($$a)) {
                    var $$v = null,
                      $$i = _vm._i($$a, $$v)
                    if ($$el.checked) {
                      $$i < 0 && (_vm.bulkSelectAll = $$a.concat([$$v]))
                    } else {
                      $$i > -1 &&
                        (_vm.bulkSelectAll = $$a
                          .slice(0, $$i)
                          .concat($$a.slice($$i + 1)))
                    }
                  } else {
                    _vm.bulkSelectAll = $$c
                  }
                },
                _vm.changeBulkCheckbox
              ]
            }
          })
        ])
      : _vm._e(),
    _vm._v(" "),
    _vm.hasBulkActions
      ? _c("th", { attrs: { colspan: _vm.columnsCount } }, [
          _c(
            "ul",
            _vm._l(_vm.bulkActions, function(bulkAction) {
              return _c("li", { key: bulkAction.key }, [
                _c("img", {
                  attrs: { src: bulkAction.img, alt: bulkAction.label }
                }),
                _vm._v(" "),
                _c("span", [_vm._v(_vm._s(bulkAction.label))])
              ])
            })
          )
        ])
      : _vm._e()
  ])
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-a3d30818", esExports)
  }
}

/***/ }),
/* 55 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_RowActions_vue__ = __webpack_require__(17);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_8cdca75c_hasScoped_true_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_RowActions_vue__ = __webpack_require__(57);
var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(56)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = "data-v-8cdca75c"
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_RowActions_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_8cdca75c_hasScoped_true_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_RowActions_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "assets/src/admin/components/ListTable/RowActions.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-8cdca75c", Component.options)
  } else {
    hotAPI.reload("data-v-8cdca75c", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 56 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 57 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("span", [
    _c("span", { on: { click: _vm.toggleActions } }, [_vm._v("⋮")]),
    _vm._v(" "),
    _c(
      "ul",
      {
        directives: [
          {
            name: "show",
            rawName: "v-show",
            value: _vm.showActions,
            expression: "showActions"
          }
        ],
        staticClass: "actions-box"
      },
      _vm._l(_vm.actions, function(action) {
        return _c("li", { key: action.key, class: action.key }, [
          _c(
            "a",
            {
              attrs: { href: "#" },
              on: {
                click: function($event) {
                  $event.preventDefault()
                  _vm.actionClicked(action.key, _vm.row)
                }
              }
            },
            [_vm._v(_vm._s(action.label))]
          )
        ])
      })
    )
  ])
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-8cdca75c", esExports)
  }
}

/***/ }),
/* 58 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", { class: { "table-loading": _vm.loading } }, [
    _vm.loading
      ? _c("div", { staticClass: "table-loader-wrap" }, [_vm._m(0)])
      : _vm._e(),
    _vm._v(" "),
    _c("div", { staticClass: "tablenav top" }, [
      _c("div", { staticClass: "alignleft actions" }, [_vm._t("filters")], 2),
      _vm._v(" "),
      _c("div", { staticClass: "tablenav-pages" }, [
        _c("span", { staticClass: "displaying-num" }, [
          _vm._v(_vm._s(_vm.itemsTotal) + " items")
        ]),
        _vm._v(" "),
        _vm.hasPagination
          ? _c("span", { staticClass: "pagination-links" }, [
              _vm.disableFirst
                ? _c(
                    "span",
                    {
                      staticClass: "tablenav-pages-navspan",
                      attrs: { "aria-hidden": "true" }
                    },
                    [_vm._v("«")]
                  )
                : _c(
                    "a",
                    {
                      staticClass: "first-page",
                      attrs: { href: "#" },
                      on: {
                        click: function($event) {
                          $event.preventDefault()
                          _vm.goToPage(1)
                        }
                      }
                    },
                    [
                      _c("span", { attrs: { "aria-hidden": "true" } }, [
                        _vm._v("«")
                      ])
                    ]
                  ),
              _vm._v(" "),
              _vm.disablePrev
                ? _c(
                    "span",
                    {
                      staticClass: "tablenav-pages-navspan",
                      attrs: { "aria-hidden": "true" }
                    },
                    [_vm._v("‹")]
                  )
                : _c(
                    "a",
                    {
                      staticClass: "prev-page",
                      attrs: { href: "#" },
                      on: {
                        click: function($event) {
                          $event.preventDefault()
                          _vm.goToPage(_vm.currentPage - 1)
                        }
                      }
                    },
                    [
                      _c("span", { attrs: { "aria-hidden": "true" } }, [
                        _vm._v("‹")
                      ])
                    ]
                  ),
              _vm._v(" "),
              _c("span", { staticClass: "paging-input" }, [
                _c("span", { staticClass: "tablenav-paging-text" }, [
                  _c("input", {
                    staticClass: "current-page",
                    attrs: {
                      type: "text",
                      name: "paged",
                      "aria-describedby": "table-paging",
                      size: "1"
                    },
                    domProps: { value: _vm.currentPage },
                    on: {
                      keyup: function($event) {
                        if (
                          !("button" in $event) &&
                          _vm._k(
                            $event.keyCode,
                            "enter",
                            13,
                            $event.key,
                            "Enter"
                          )
                        ) {
                          return null
                        }
                        return _vm.goToCustomPage($event)
                      }
                    }
                  }),
                  _vm._v(" of\n                        "),
                  _c("span", { staticClass: "total-pages" }, [
                    _vm._v(_vm._s(_vm.totalPages))
                  ])
                ])
              ]),
              _vm._v(" "),
              _vm.disableNext
                ? _c(
                    "span",
                    {
                      staticClass: "tablenav-pages-navspan",
                      attrs: { "aria-hidden": "true" }
                    },
                    [_vm._v("›")]
                  )
                : _c(
                    "a",
                    {
                      staticClass: "next-page",
                      attrs: { href: "#" },
                      on: {
                        click: function($event) {
                          $event.preventDefault()
                          _vm.goToPage(_vm.currentPage + 1)
                        }
                      }
                    },
                    [
                      _c("span", { attrs: { "aria-hidden": "true" } }, [
                        _vm._v("›")
                      ])
                    ]
                  ),
              _vm._v(" "),
              _vm.disableLast
                ? _c(
                    "span",
                    {
                      staticClass: "tablenav-pages-navspan",
                      attrs: { "aria-hidden": "true" }
                    },
                    [_vm._v("»")]
                  )
                : _c(
                    "a",
                    {
                      staticClass: "last-page",
                      attrs: { href: "#" },
                      on: {
                        click: function($event) {
                          $event.preventDefault()
                          _vm.goToPage(_vm.totalPages)
                        }
                      }
                    },
                    [
                      _c("span", { attrs: { "aria-hidden": "true" } }, [
                        _vm._v("»")
                      ])
                    ]
                  )
            ])
          : _vm._e()
      ])
    ]),
    _vm._v(" "),
    _c("table", { class: _vm.tableClass }, [
      _c(
        "thead",
        [
          _vm.checkedItems.length
            ? _c("bulk-actions-tpl", {
                attrs: {
                  "select-all": _vm.selectAll,
                  "bulk-actions": _vm.bulkActions,
                  "show-cb": _vm.showCb,
                  "columns-count": _vm.columnsCount
                }
              })
            : _c(
                "tr",
                [
                  _vm.showCb
                    ? _c(
                        "td",
                        { staticClass: "manage-column column-cb check-column" },
                        [
                          _c("input", {
                            directives: [
                              {
                                name: "model",
                                rawName: "v-model",
                                value: _vm.selectAll,
                                expression: "selectAll"
                              }
                            ],
                            attrs: { type: "checkbox" },
                            domProps: {
                              checked: Array.isArray(_vm.selectAll)
                                ? _vm._i(_vm.selectAll, null) > -1
                                : _vm.selectAll
                            },
                            on: {
                              change: function($event) {
                                var $$a = _vm.selectAll,
                                  $$el = $event.target,
                                  $$c = $$el.checked ? true : false
                                if (Array.isArray($$a)) {
                                  var $$v = null,
                                    $$i = _vm._i($$a, $$v)
                                  if ($$el.checked) {
                                    $$i < 0 &&
                                      (_vm.selectAll = $$a.concat([$$v]))
                                  } else {
                                    $$i > -1 &&
                                      (_vm.selectAll = $$a
                                        .slice(0, $$i)
                                        .concat($$a.slice($$i + 1)))
                                  }
                                } else {
                                  _vm.selectAll = $$c
                                }
                              }
                            }
                          })
                        ]
                      )
                    : _vm._e(),
                  _vm._v(" "),
                  _vm._l(_vm.columns, function(value, key) {
                    return _c(
                      "th",
                      {
                        key: key,
                        class: [
                          "column",
                          key,
                          { sortable: _vm.isSortable(value) },
                          { sorted: _vm.isSorted(key) },
                          { asc: _vm.isSorted(key) && _vm.sortOrder === "asc" },
                          {
                            desc: _vm.isSorted(key) && _vm.sortOrder === "desc"
                          }
                        ]
                      },
                      [
                        !_vm.isSortable(value)
                          ? [
                              _vm._v(
                                "\n                    " +
                                  _vm._s(value.label) +
                                  "\n                "
                              )
                            ]
                          : _c(
                              "a",
                              {
                                attrs: { href: "#" },
                                on: {
                                  click: function($event) {
                                    $event.preventDefault()
                                    _vm.handleSortBy(key)
                                  }
                                }
                              },
                              [
                                _c("span", [_vm._v(_vm._s(value.label))]),
                                _vm._v(" "),
                                _c("span", { staticClass: "sorting-indicator" })
                              ]
                            )
                      ],
                      2
                    )
                  })
                ],
                2
              )
        ],
        1
      ),
      _vm._v(" "),
      _c(
        "tfoot",
        [
          _vm.checkedItems.length
            ? _c("bulk-actions-tpl", {
                attrs: {
                  "select-all": _vm.selectAll,
                  "bulk-actions": _vm.bulkActions,
                  "show-cb": _vm.showCb,
                  "columns-count": _vm.columnsCount
                }
              })
            : _c(
                "tr",
                [
                  _vm.showCb
                    ? _c(
                        "td",
                        { staticClass: "manage-column column-cb check-column" },
                        [
                          _c("input", {
                            directives: [
                              {
                                name: "model",
                                rawName: "v-model",
                                value: _vm.selectAll,
                                expression: "selectAll"
                              }
                            ],
                            attrs: { type: "checkbox" },
                            domProps: {
                              checked: Array.isArray(_vm.selectAll)
                                ? _vm._i(_vm.selectAll, null) > -1
                                : _vm.selectAll
                            },
                            on: {
                              change: function($event) {
                                var $$a = _vm.selectAll,
                                  $$el = $event.target,
                                  $$c = $$el.checked ? true : false
                                if (Array.isArray($$a)) {
                                  var $$v = null,
                                    $$i = _vm._i($$a, $$v)
                                  if ($$el.checked) {
                                    $$i < 0 &&
                                      (_vm.selectAll = $$a.concat([$$v]))
                                  } else {
                                    $$i > -1 &&
                                      (_vm.selectAll = $$a
                                        .slice(0, $$i)
                                        .concat($$a.slice($$i + 1)))
                                  }
                                } else {
                                  _vm.selectAll = $$c
                                }
                              }
                            }
                          })
                        ]
                      )
                    : _vm._e(),
                  _vm._v(" "),
                  _vm._l(_vm.columns, function(value, key) {
                    return _c("th", { key: key, class: ["column", key] }, [
                      _vm._v(_vm._s(value.label) + "\n            ")
                    ])
                  })
                ],
                2
              )
        ],
        1
      ),
      _vm._v(" "),
      _c(
        "tbody",
        [
          _vm.rows.length
            ? _vm._l(_vm.rows, function(row) {
                return _c(
                  "tr",
                  { key: row[_vm.index] },
                  [
                    _vm.showCb
                      ? _c(
                          "th",
                          {
                            staticClass: "check-column",
                            attrs: { scope: "row" }
                          },
                          [
                            _c("input", {
                              directives: [
                                {
                                  name: "model",
                                  rawName: "v-model",
                                  value: _vm.checkedItems,
                                  expression: "checkedItems"
                                }
                              ],
                              attrs: { type: "checkbox", name: "item[]" },
                              domProps: {
                                value: row[_vm.index],
                                checked: Array.isArray(_vm.checkedItems)
                                  ? _vm._i(_vm.checkedItems, row[_vm.index]) >
                                    -1
                                  : _vm.checkedItems
                              },
                              on: {
                                change: function($event) {
                                  var $$a = _vm.checkedItems,
                                    $$el = $event.target,
                                    $$c = $$el.checked ? true : false
                                  if (Array.isArray($$a)) {
                                    var $$v = row[_vm.index],
                                      $$i = _vm._i($$a, $$v)
                                    if ($$el.checked) {
                                      $$i < 0 &&
                                        (_vm.checkedItems = $$a.concat([$$v]))
                                    } else {
                                      $$i > -1 &&
                                        (_vm.checkedItems = $$a
                                          .slice(0, $$i)
                                          .concat($$a.slice($$i + 1)))
                                    }
                                  } else {
                                    _vm.checkedItems = $$c
                                  }
                                }
                              }
                            })
                          ]
                        )
                      : _vm._e(),
                    _vm._v(" "),
                    _vm._l(_vm.columns, function(value, key) {
                      return _c(
                        "td",
                        {
                          key: key,
                          class: [
                            "column",
                            key,
                            {
                              selected: _vm.checkedItems.includes(
                                row[_vm.index]
                              )
                            }
                          ]
                        },
                        [
                          _vm._t(
                            key,
                            [
                              _vm._v(
                                "\n                        " +
                                  _vm._s(row[key]) +
                                  "\n                    "
                              )
                            ],
                            { row: row }
                          ),
                          _vm._v(" "),
                          _vm.actionColumn === key && _vm.hasActions
                            ? _c(
                                "div",
                                { staticClass: "row-actions" },
                                [
                                  _vm._t(
                                    "row-actions",
                                    [
                                      _c("row-actions", {
                                        attrs: {
                                          row: row,
                                          actions: _vm.actions
                                        }
                                      })
                                    ],
                                    { row: row }
                                  )
                                ],
                                2
                              )
                            : _vm._e()
                        ],
                        2
                      )
                    })
                  ],
                  2
                )
              })
            : _c("tr", [
                _c("td", { attrs: { colspan: _vm.colspan } }, [
                  _vm._v(_vm._s(_vm.notFound))
                ])
              ])
        ],
        2
      )
    ]),
    _vm._v(" "),
    _c("div", { staticClass: "tablenav bottom" }, [
      _c("div", { staticClass: "tablenav-pages" }, [
        _c("span", { staticClass: "displaying-num" }, [
          _vm._v(_vm._s(_vm.itemsTotal) + " items")
        ]),
        _vm._v(" "),
        _vm.hasPagination
          ? _c("span", { staticClass: "pagination-links" }, [
              _vm.disableFirst
                ? _c(
                    "span",
                    {
                      staticClass: "tablenav-pages-navspan",
                      attrs: { "aria-hidden": "true" }
                    },
                    [_vm._v("«")]
                  )
                : _c(
                    "a",
                    {
                      staticClass: "first-page",
                      attrs: { href: "#" },
                      on: {
                        click: function($event) {
                          $event.preventDefault()
                          _vm.goToPage(1)
                        }
                      }
                    },
                    [
                      _c("span", { attrs: { "aria-hidden": "true" } }, [
                        _vm._v("«")
                      ])
                    ]
                  ),
              _vm._v(" "),
              _vm.disablePrev
                ? _c(
                    "span",
                    {
                      staticClass: "tablenav-pages-navspan",
                      attrs: { "aria-hidden": "true" }
                    },
                    [_vm._v("‹")]
                  )
                : _c(
                    "a",
                    {
                      staticClass: "prev-page",
                      attrs: { href: "#" },
                      on: {
                        click: function($event) {
                          $event.preventDefault()
                          _vm.goToPage(_vm.currentPage - 1)
                        }
                      }
                    },
                    [
                      _c("span", { attrs: { "aria-hidden": "true" } }, [
                        _vm._v("‹")
                      ])
                    ]
                  ),
              _vm._v(" "),
              _c("span", { staticClass: "paging-input" }, [
                _c("span", { staticClass: "tablenav-paging-text" }, [
                  _vm._v(
                    "\n                        " +
                      _vm._s(_vm.currentPage) +
                      " of\n                        "
                  ),
                  _c("span", { staticClass: "total-pages" }, [
                    _vm._v(_vm._s(_vm.totalPages))
                  ])
                ])
              ]),
              _vm._v(" "),
              _vm.disableNext
                ? _c(
                    "span",
                    {
                      staticClass: "tablenav-pages-navspan",
                      attrs: { "aria-hidden": "true" }
                    },
                    [_vm._v("›")]
                  )
                : _c(
                    "a",
                    {
                      staticClass: "next-page",
                      attrs: { href: "#" },
                      on: {
                        click: function($event) {
                          $event.preventDefault()
                          _vm.goToPage(_vm.currentPage + 1)
                        }
                      }
                    },
                    [
                      _c("span", { attrs: { "aria-hidden": "true" } }, [
                        _vm._v("›")
                      ])
                    ]
                  ),
              _vm._v(" "),
              _vm.disableLast
                ? _c(
                    "span",
                    {
                      staticClass: "tablenav-pages-navspan",
                      attrs: { "aria-hidden": "true" }
                    },
                    [_vm._v("»")]
                  )
                : _c(
                    "a",
                    {
                      staticClass: "last-page",
                      attrs: { href: "#" },
                      on: {
                        click: function($event) {
                          $event.preventDefault()
                          _vm.goToPage(_vm.totalPages)
                        }
                      }
                    },
                    [
                      _c("span", { attrs: { "aria-hidden": "true" } }, [
                        _vm._v("»")
                      ])
                    ]
                  )
            ])
          : _vm._e()
      ])
    ])
  ])
}
var staticRenderFns = [
  function() {
    var _vm = this
    var _h = _vm.$createElement
    var _c = _vm._self._c || _h
    return _c("div", { staticClass: "table-loader-center" }, [
      _c("div", { staticClass: "table-loader" }, [_vm._v("Loading")])
    ])
  }
]
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-5a558fde", esExports)
  }
}

/***/ }),
/* 59 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    { staticClass: "app-customers" },
    [
      _c("h2", { staticClass: "add-new-customer" }, [
        _c("span", [_vm._v("Customers")]),
        _vm._v(" "),
        _c(
          "a",
          {
            attrs: { href: "#", id: "erp-customer-new" },
            on: {
              click: function($event) {
                _vm.showModal = true
              }
            }
          },
          [_vm._v("+ Add New Customer")]
        )
      ]),
      _vm._v(" "),
      _c("ListTable", {
        attrs: {
          tableClass: "wp-ListTable widefat fixed customer-list",
          "action-column": "actions",
          columns: _vm.columns,
          rows: _vm.rows,
          "bulk-actions": _vm.bulkActions,
          "total-items": 4,
          "total-pages": 2,
          "per-page": 2,
          "current-page": 1,
          actions: [
            { key: "edit", label: "Edit" },
            { key: "trash", label: "Delete" }
          ]
        },
        scopedSlots: _vm._u([
          {
            key: "title",
            fn: function(data) {
              return [
                _c("strong", [
                  _c("a", { attrs: { href: "#" } }, [
                    _vm._v(_vm._s(data.row.title))
                  ])
                ])
              ]
            }
          }
        ])
      })
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-384dc50a", esExports)
  }
}

/***/ }),
/* 60 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_Vendors_vue__ = __webpack_require__(18);
/* empty harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_0984f520_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_Vendors_vue__ = __webpack_require__(62);
var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(61)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_Vendors_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_0984f520_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_Vendors_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "assets/src/admin/components/Peoples/Vendors.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-0984f520", Component.options)
  } else {
    hotAPI.reload("data-v-0984f520", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["default"] = (Component.exports);


/***/ }),
/* 61 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 62 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    { staticClass: "app-vendors" },
    [
      _c("h2", { staticClass: "add-new-vendor" }, [
        _c("span", [_vm._v("Vendors")]),
        _vm._v(" "),
        _c(
          "a",
          {
            attrs: { href: "#", id: "erp-vendor-new" },
            on: {
              click: function($event) {
                _vm.showModal = true
              }
            }
          },
          [_vm._v("+ Add New Vendor")]
        )
      ]),
      _vm._v(" "),
      _c("ListTable", {
        attrs: {
          tableClass: "wp-ListTable widefat fixed vendor-list",
          "action-column": "actions",
          columns: _vm.columns,
          rows: _vm.rows,
          "bulk-actions": _vm.bulkActions,
          "total-items": 4,
          "total-pages": 2,
          "per-page": 2,
          "current-page": 1,
          actions: [
            { key: "edit", label: "Edit" },
            { key: "trash", label: "Delete" }
          ]
        },
        scopedSlots: _vm._u([
          {
            key: "title",
            fn: function(data) {
              return [
                _c("strong", [
                  _c("a", { attrs: { href: "#" } }, [
                    _vm._v(_vm._s(data.row.title))
                  ])
                ])
              ]
            }
          }
        ])
      })
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-0984f520", esExports)
  }
}

/***/ }),
/* 63 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_Employees_vue__ = __webpack_require__(19);
/* empty harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_3f2c203a_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_Employees_vue__ = __webpack_require__(65);
var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(64)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_Employees_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_3f2c203a_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_Employees_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "assets/src/admin/components/Peoples/Employees.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-3f2c203a", Component.options)
  } else {
    hotAPI.reload("data-v-3f2c203a", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["default"] = (Component.exports);


/***/ }),
/* 64 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 65 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    { staticClass: "app-employees" },
    [
      _c("h2", { staticClass: "add-new-employee" }, [
        _c("span", [_vm._v("Employees")]),
        _vm._v(" "),
        _c(
          "a",
          {
            attrs: { href: "#", id: "erp-employee-new" },
            on: {
              click: function($event) {
                _vm.showModal = true
              }
            }
          },
          [_vm._v("+ Add New Employee")]
        )
      ]),
      _vm._v(" "),
      _c("ListTable", {
        attrs: {
          tableClass: "wp-ListTable widefat fixed employee-list",
          "action-column": "actions",
          columns: _vm.columns,
          rows: _vm.rows,
          "bulk-actions": _vm.bulkActions,
          "total-items": 4,
          "total-pages": 2,
          "per-page": 2,
          "current-page": 1,
          actions: [
            { key: "edit", label: "Edit" },
            { key: "trash", label: "Delete" }
          ]
        },
        scopedSlots: _vm._u([
          {
            key: "title",
            fn: function(data) {
              return [
                _c("strong", [
                  _c("a", { attrs: { href: "#" } }, [
                    _vm._v(_vm._s(data.row.title))
                  ])
                ])
              ]
            }
          }
        ])
      })
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-3f2c203a", esExports)
  }
}

/***/ })
],[34]);