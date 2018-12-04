pluginWebpack([0],[
/* 0 */,
/* 1 */,
/* 2 */,
/* 3 */,
/* 4 */,
/* 5 */,
/* 6 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_ListTable_vue__ = __webpack_require__(18);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_50f2b730_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_ListTable_vue__ = __webpack_require__(78);
var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(71)
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
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_50f2b730_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_ListTable_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "assets/src/admin/components/list-table/ListTable.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-50f2b730", Component.options)
  } else {
    hotAPI.reload("data-v-50f2b730", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 7 */,
/* 8 */,
/* 9 */,
/* 10 */,
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
/* harmony default export */ __webpack_exports__["a"] = ({
  name: 'Accounting'
});

/***/ }),
/* 12 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__WP_MetaBox_vue__ = __webpack_require__(51);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__Menu_ERPMenu_vue__ = __webpack_require__(54);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
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
  data: function data() {
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
/* 13 */
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
  data: function data() {
    return {
      closed: false
    };
  },
  computed: {
    classes: function classes() {
      return ['postbox', this.closed ? 'closed' : ''];
    },
    styles: function styles() {
      return 'display: block;';
    }
  },
  methods: {
    handleToggle: function handleToggle(event) {
      this.closed = !this.closed;
      this.$emit('metaboxToggle', event);
    }
  }
});

/***/ }),
/* 14 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__wordpress_i18n__ = __webpack_require__(15);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
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
  name: "ERPMenu",
  props: {},
  data: function data() {
    return {
      menuItems: erp_acct_var.erp_acct_menus,
      dropDownClass: "erp-nav-dropdown",
      primaryNav: "erp-nav -primary",
      module_name: Object(__WEBPACK_IMPORTED_MODULE_0__wordpress_i18n__["__"])("Accounting", "erp"),
      svgData: "M221.9,0H17.1C6.8,0,0,6.8,0,17.1V324.3c0,10.2,6.8,17.1,17.1,17.1H221.9c10.2,0,17.1-6.8,17.1-17.1V17.1C238.9,6.8,232.1,0,221.9,0ZM68.3,307.2H34.1V273.1H68.2v34.1Zm0-68.3H34.1V204.8H68.2v34.1Zm0-68.2H34.1V136.6H68.2v34.1Zm68.2,136.5H102.4V273.1h34.1Zm0-68.3H102.4V204.8h34.1Zm0-68.2H102.4V136.6h34.1Zm68.3,136.5H170.7V273.1h34.1v34.1Zm0-68.3H170.7V204.8h34.1v34.1Zm0-68.2H170.7V136.6h34.1v34.1Zm0-68.3H34.1V34.1H204.8v68.3Zm0,0",
      current_url: window.location.href
    };
  },
  created: function created() {
    console.log(this.menuItems);
    this.init();
  },
  methods: {
    init: function init() {
      var container = document.querySelector(".erp-nav-container");

      if (container == null) {
        return;
      }

      var primary = container.querySelector(".-primary");
      primaryItems = container.querySelectorAll(".-primary > li:not(.-more)");
      container.classList.add("--jsfied"); // insert "more" button and duplicate the list

      primary.insertAdjacentHTML("beforeend", '<li class="-more"><button type="button" aria-haspopup="true" aria-expanded="false">More <span class="dashicons dashicons-arrow-down-alt2"></span></button><ul class="-secondary">' + primary.innerHTML + "</ul></li>");
      var secondary = container.querySelector(".-secondary");
      secondaryItems = [].slice.call(secondary.children);
      allItems = container.querySelectorAll("li");
      moreLi = primary.querySelector(".-more");
      moreBtn = moreLi.querySelector("button");
      moreBtn.addEventListener("click", function (e) {
        e.preventDefault();
        container.classList.toggle("--show-secondary");
        moreBtn.setAttribute("aria-expanded", container.classList.contains("--show-secondary"));
      }); // adapt tabs

      var doAdapt = function doAdapt() {
        // reveal all items for the calculation
        allItems.forEach(function (item) {
          item.classList.remove("--hidden");
        }); // hide items that won't fit in the Primary

        stopWidth = moreBtn.offsetWidth;
        hiddenItems = [];
        primaryWidth = primary.offsetWidth;
        primaryItems.forEach(function (item, i) {
          if (primaryWidth >= stopWidth + item.offsetWidth) {
            stopWidth += item.offsetWidth;
          } else {
            item.classList.add("--hidden");
            hiddenItems.push(i);
          }
        }); // toggle the visibility of More button and items in Secondary

        if (!hiddenItems.length) {
          moreLi.classList.add("--hidden");
          container.classList.remove("--show-secondary");
          moreBtn.setAttribute("aria-expanded", false);
        } else {
          secondaryItems.forEach(function (item, i) {
            if (!hiddenItems.includes(i)) {
              item.classList.add("--hidden");
            }
          });
        }
      };

      doAdapt(); // adapt immediately on load

      window.addEventListener("resize", doAdapt); // adapt on window resize
      // hide Secondary on the outside click

      document.addEventListener("click", function (e) {
        var el = e.target;

        while (el) {
          if (el === secondary || el === moreBtn) {
            return;
          }

          el = el.parentNode;
        }

        container.classList.remove("--show-secondary");
        moreBtn.setAttribute("aria-expanded", false);
      });
    },
    changeRoute: function changeRoute(slug) {
      this.$router.push(slug);
    }
  }
});

/***/ }),
/* 15 */,
/* 16 */
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
/* 17 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__list_table_ListTable_vue__ = __webpack_require__(6);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__http_js__ = __webpack_require__(79);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
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
    ListTable: __WEBPACK_IMPORTED_MODULE_0__list_table_ListTable_vue__["a" /* default */]
  },
  data: function data() {
    return {
      bulkActions: [{
        key: 'trash',
        label: 'Move to Trash',
        img: erp_acct_var.erp_assets + '/images/trash.png'
      }],
      columns: {
        'customer': {
          label: 'Customer Name'
        },
        'company': {
          label: 'Company'
        },
        'email': {
          label: 'Email'
        },
        'phone': {
          label: 'Phone'
        },
        'expense': {
          label: 'Expense'
        },
        'actions': {
          label: 'Actions'
        }
      },
      rows: [],
      paginationData: {
        totalItems: 0,
        totalPages: 0,
        perPage: 10,
        currentPage: this.$route.params.page === undefined ? 1 : parseInt(this.$route.params.page)
      }
    };
  },
  created: function created() {
    this.$on('modal-close', function () {
      this.showModal = false;
    });
    this.fetchItems();
  },
  computed: {
    row_data: function row_data() {
      var items = this.rows;
      items.map(function (item) {
        item.customer = item.first_name + ' ' + item.last_name;
        item.expense = '55555';
      });
      return items;
    }
  },
  methods: {
    fetchItems: function fetchItems() {
      var _this = this;

      this.rows = [];
      __WEBPACK_IMPORTED_MODULE_1__http_js__["a" /* default */].get('customers').then(function (response) {
        _this.rows = response.data;
        _this.paginationData.totalItems = parseInt(response.headers['x-wp-total']);
        _this.paginationData.totalPages = parseInt(response.headers['x-wp-totalpages']);
      }).catch(function (error) {
        console.log(error);
      }).then(function () {//ready
      });
    }
  }
});

/***/ }),
/* 18 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__BulkActionsTpl_vue__ = __webpack_require__(72);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__dropdown_Dropdown_vue__ = __webpack_require__(75);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* eslint-disable */


/* harmony default export */ __webpack_exports__["a"] = ({
  name: 'ListTable',
  components: {
    BulkActionsTpl: __WEBPACK_IMPORTED_MODULE_0__BulkActionsTpl_vue__["a" /* default */],
    Dropdown: __WEBPACK_IMPORTED_MODULE_1__dropdown_Dropdown_vue__["a" /* default */]
  },
  props: {
    columns: {
      type: Object,
      required: true,
      default: function _default() {}
    },
    rows: {
      type: Array,
      // String, Number, Boolean, Function, Object, Array
      required: true,
      default: function _default() {
        return [];
      }
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
      default: function _default() {
        return [];
      }
    },
    bulkActions: {
      type: Array,
      required: false,
      default: function _default() {
        return [];
      }
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
  data: function data() {
    return {
      bulkLocal: '-1',
      checkedItems: []
    };
  },
  computed: {
    hasActions: function hasActions() {
      return this.actions.length > 0;
    },
    itemsTotal: function itemsTotal() {
      return this.totalItems || this.rows.length;
    },
    hasPagination: function hasPagination() {
      return this.itemsTotal > this.perPage;
    },
    disableFirst: function disableFirst() {
      if (this.currentPage === 1 || this.currentPage === 2) {
        return true;
      }

      return false;
    },
    disablePrev: function disablePrev() {
      if (this.currentPage === 1) {
        return true;
      }

      return false;
    },
    disableNext: function disableNext() {
      if (this.currentPage === this.totalPages) {
        return true;
      }

      return false;
    },
    disableLast: function disableLast() {
      if (this.currentPage === this.totalPages || this.currentPage == this.totalPages - 1) {
        return true;
      }

      return false;
    },
    columnsCount: function columnsCount() {
      return Object.keys(this.columns).length;
    },
    colspan: function colspan() {
      var columns = Object.keys(this.columns).length;

      if (this.showCb) {
        columns += 1;
      }

      return columns;
    },
    selectAll: {
      get: function get() {
        if (!this.rows.length) {
          return false;
        }

        return this.rows ? this.checkedItems.length == this.rows.length : false;
      },
      set: function set(value) {
        var selected = [];
        var self = this;

        if (value) {
          this.rows.forEach(function (item) {
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
  created: function created() {
    var _this = this;

    this.$on('bulk-checkbox', function (e) {
      if (!e) {
        _this.checkedItems = [];
      }
    });
    this.$on('bulk-action-click', function (key) {
      _this.bulkLocal = key;

      _this.handleBulkAction();
    });
  },
  methods: {
    hideActionSeparator: function hideActionSeparator(action) {
      return action === this.actions[this.actions.length - 1].key;
    },
    actionClicked: function actionClicked(action, row, index) {
      this.$emit('action:click', action, row, index);
    },
    goToPage: function goToPage(page) {
      this.$emit('pagination', page);
    },
    goToCustomPage: function goToCustomPage(event) {
      var page = parseInt(event.target.value, 10);

      if (!isNaN(page) && page > 0 && page <= this.totalPages) {
        this.$emit('pagination', page);
      }
    },
    handleBulkAction: function handleBulkAction() {
      if (this.bulkLocal === '-1') {
        return;
      }

      this.$emit('bulk:click', this.bulkLocal, this.checkedItems);
    },
    isSortable: function isSortable(column) {
      if (column.hasOwnProperty('sortable') && column.sortable === true) {
        return true;
      }

      return false;
    },
    isSorted: function isSorted(column) {
      return column === this.sortBy;
    },
    handleSortBy: function handleSortBy(column) {
      var order = this.sortOrder === 'asc' ? 'desc' : 'asc';
      this.$emit('sort', column, order);
    }
  }
});

/***/ }),
/* 19 */
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
      default: function _default() {
        return [];
      }
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
  data: function data() {
    return {
      bulkSelectAll: this.selectAll
    };
  },
  computed: {
    hasBulkActions: function hasBulkActions() {
      return this.bulkActions.length > 0;
    }
  },
  methods: {
    changeBulkCheckbox: function changeBulkCheckbox() {
      this.$parent.$emit('bulk-checkbox', this.bulkSelectAll);
    },
    bulkActionSelect: function bulkActionSelect(key) {
      this.$parent.$emit('bulk-action-click', key);
    }
  }
});

/***/ }),
/* 20 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_popper_js__ = __webpack_require__(21);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* eslint no-underscore-dangle: 0 */
// Vue click outside
// https://jsfiddle.net/Linusborg/Lx49LaL8/

/* harmony default export */ __webpack_exports__["a"] = ({
  name: 'Dropdown',
  props: {
    dropdownClasses: {
      type: String,
      default: ''
    },
    disabled: {
      type: Boolean,
      default: false
    },
    placement: {
      type: String,
      default: 'bottom'
    }
  },
  data: function data() {
    return {
      visible: false
    };
  },
  watch: {
    visible: function visible(newValue, oldValue) {
      if (newValue !== oldValue) {
        if (newValue) {
          this.showMenu();
        } else {
          this.hideMenu();
        }
      }
    }
  },
  created: function created() {
    // Create non-reactive property
    this._popper = null;
  },
  mounted: function mounted() {
    window.addEventListener('click', this.closeDropdown);
  },
  beforeDestroy: function beforeDestroy() {
    this.visible = false;
    this.removePopper();
  },
  destroyed: function destroyed() {
    window.removeEventListener('click', this.closeDropdown);
  },
  methods: {
    toggleDropdown: function toggleDropdown() {
      this.visible = !this.visible;
    },
    showMenu: function showMenu() {
      if (this.disabled) {
        return;
      }

      var element = this.$el;
      this.createPopper(element);
    },
    hideMenu: function hideMenu() {
      this.$root.$emit('hidden');
      this.removePopper();
    },
    createPopper: function createPopper(element) {
      this.removePopper();
      this._popper = new __WEBPACK_IMPORTED_MODULE_0_popper_js__["default"](element, this.$refs.menu, {
        placement: this.placement
      });
    },
    removePopper: function removePopper() {
      if (this._popper) {
        // Ensure popper event listeners are removed cleanly
        this._popper.destroy();
      }

      this._popper = null;
    },
    closeDropdown: function closeDropdown(e) {
      if (!this.$el || this.elementContains(this.$el, e.target) || !this._popper || this.elementContains(this._popper, e.target)) {
        return;
      }

      this.visible = false;
    },
    elementContains: function elementContains(elm, otherElm) {
      if (typeof elm.contains === 'function') {
        return elm.contains(otherElm);
      }

      return false;
    }
  }
});

/***/ }),
/* 21 */,
/* 22 */,
/* 23 */,
/* 24 */,
/* 25 */,
/* 26 */,
/* 27 */,
/* 28 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__list_table_ListTable_vue__ = __webpack_require__(6);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
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
    ListTable: __WEBPACK_IMPORTED_MODULE_0__list_table_ListTable_vue__["a" /* default */]
  },
  data: function data() {
    return {
      bulkActions: [{
        key: 'trash',
        label: 'Move to Trash'
      }],
      columns: {
        'vendor': {
          label: 'Vendor Name'
        },
        'company': {
          label: 'Vendor Owner'
        },
        'email': {
          label: 'Email'
        },
        'phone': {
          label: 'Phone'
        },
        'expense': {
          label: 'Expense'
        },
        'actions': {
          label: 'Actions'
        }
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
  created: function created() {
    this.$on('modal-close', function () {
      this.showModal = false;
    });
  }
});

/***/ }),
/* 29 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__list_table_ListTable_vue__ = __webpack_require__(6);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
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
    ListTable: __WEBPACK_IMPORTED_MODULE_0__list_table_ListTable_vue__["a" /* default */]
  },
  data: function data() {
    return {
      bulkActions: [{
        key: 'trash',
        label: 'Move to Trash'
      }],
      columns: {
        'employee': {
          label: 'Employee Name'
        },
        'company': {
          label: 'Company'
        },
        'email': {
          label: 'Email'
        },
        'phone': {
          label: 'Phone'
        },
        'expense': {
          label: 'Expense'
        },
        'actions': {
          label: 'Actions'
        }
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
  created: function created() {
    this.$on('modal-close', function () {
      this.showModal = false;
    });
  }
});

/***/ }),
/* 30 */,
/* 31 */,
/* 32 */,
/* 33 */,
/* 34 */,
/* 35 */,
/* 36 */,
/* 37 */,
/* 38 */,
/* 39 */,
/* 40 */,
/* 41 */,
/* 42 */,
/* 43 */,
/* 44 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vue__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__App_vue__ = __webpack_require__(45);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__router__ = __webpack_require__(48);



__WEBPACK_IMPORTED_MODULE_0_vue__["default"].config.productionTip = false;
/* eslint-disable no-new */

new __WEBPACK_IMPORTED_MODULE_0_vue__["default"]({
  el: '#erp-accounting',
  router: __WEBPACK_IMPORTED_MODULE_2__router__["a" /* default */],
  render: function render(h) {
    return h(__WEBPACK_IMPORTED_MODULE_1__App_vue__["a" /* default */]);
  }
});

/***/ }),
/* 45 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_App_vue__ = __webpack_require__(11);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_6bc4b6d8_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_App_vue__ = __webpack_require__(47);
var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(46)
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

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 46 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 47 */
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
/* 48 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vue__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_vue_router__ = __webpack_require__(5);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_admin_components_Dashboard_vue__ = __webpack_require__(49);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_admin_components_ChartOfAccounts_vue__ = __webpack_require__(67);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4_admin_components_Peoples_Customers_vue__ = __webpack_require__(69);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5_admin_components_Peoples_Vendors_vue__ = __webpack_require__(99);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6_admin_components_Peoples_Employees_vue__ = __webpack_require__(102);







__WEBPACK_IMPORTED_MODULE_0_vue__["default"].use(__WEBPACK_IMPORTED_MODULE_1_vue_router__["default"]);
/* harmony default export */ __webpack_exports__["a"] = (new __WEBPACK_IMPORTED_MODULE_1_vue_router__["default"]({
  routes: [{
    path: '/',
    name: 'Dashboard',
    component: __WEBPACK_IMPORTED_MODULE_2_admin_components_Dashboard_vue__["a" /* default */]
  }, {
    path: '/customers',
    name: 'Customers',
    component: __WEBPACK_IMPORTED_MODULE_4_admin_components_Peoples_Customers_vue__["a" /* default */]
  }, {
    path: '/vendors',
    name: 'Vendors',
    component: __WEBPACK_IMPORTED_MODULE_5_admin_components_Peoples_Vendors_vue__["a" /* default */]
  }, {
    path: '/employees',
    name: 'Employees',
    component: __WEBPACK_IMPORTED_MODULE_6_admin_components_Peoples_Employees_vue__["a" /* default */]
  }]
}));

/***/ }),
/* 49 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_Dashboard_vue__ = __webpack_require__(12);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_45a0d6f4_hasScoped_true_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_Dashboard_vue__ = __webpack_require__(66);
var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(50)
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

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 50 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 51 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_MetaBox_vue__ = __webpack_require__(13);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_eca6e040_hasScoped_true_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_MetaBox_vue__ = __webpack_require__(53);
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
/* 52 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 53 */
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
/* 54 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_ERPMenu_vue__ = __webpack_require__(14);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_77b17d3c_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_ERPMenu_vue__ = __webpack_require__(65);
var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(55)
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
/* 55 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 56 */,
/* 57 */,
/* 58 */,
/* 59 */,
/* 60 */,
/* 61 */,
/* 62 */,
/* 63 */,
/* 64 */,
/* 65 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", { staticClass: "erp-nav-container" }, [
    _c("div", { staticClass: "erp-page-header" }, [
      _c("div", { staticClass: "module-icon" }, [
        _c(
          "svg",
          {
            attrs: {
              id: "Group_235",
              "data-name": "Group 235",
              xmlns: "http://www.w3.org/2000/svg",
              viewBox: "0 0 239 341.4"
            }
          },
          [
            _c("path", {
              staticClass: "cls-1",
              attrs: { id: "Path_281", "data-name": "Path 281", d: _vm.svgData }
            })
          ]
        )
      ]),
      _vm._v(" "),
      _c("h2", [_vm._v(_vm._s(_vm.module_name))])
    ]),
    _vm._v(" "),
    _c(
      "ul",
      { class: _vm.primaryNav },
      [
        _vm._l(_vm.menuItems, function(menu, index) {
          return [
            menu.hasOwnProperty("submenu")
              ? _c("li", { key: index, staticClass: "dropdown-nav" }, [
                  _c("a", { attrs: { href: _vm.current_url + menu.slug } }, [
                    _vm._v(_vm._s(menu.title))
                  ]),
                  _vm._v(" "),
                  _c(
                    "ul",
                    { class: _vm.dropDownClass },
                    _vm._l(menu.submenu, function(item, index) {
                      return _c("li", { key: index }, [
                        _c(
                          "a",
                          { attrs: { href: _vm.current_url + item.slug } },
                          [_vm._v(_vm._s(item.title))]
                        )
                      ])
                    })
                  )
                ])
              : _c("li", { key: index }, [
                  _c("a", { attrs: { href: _vm.current_url + menu.slug } }, [
                    _vm._v(_vm._s(menu.title))
                  ])
                ])
          ]
        })
      ],
      2
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
    require("vue-hot-reload-api")      .rerender("data-v-77b17d3c", esExports)
  }
}

/***/ }),
/* 66 */
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
/* 67 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_ChartOfAccounts_vue__ = __webpack_require__(16);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_ChartOfAccounts_vue___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_ChartOfAccounts_vue__);
/* unused harmony reexport namespace */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_02d5983b_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_ChartOfAccounts_vue__ = __webpack_require__(68);
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

/* unused harmony default export */ var _unused_webpack_default_export = (Component.exports);


/***/ }),
/* 68 */
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
/* 69 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_Customers_vue__ = __webpack_require__(17);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_384dc50a_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_Customers_vue__ = __webpack_require__(98);
var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(70)
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

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 70 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 71 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 72 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_BulkActionsTpl_vue__ = __webpack_require__(19);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_66769835_hasScoped_true_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_BulkActionsTpl_vue__ = __webpack_require__(74);
var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(73)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = "data-v-66769835"
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_BulkActionsTpl_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_66769835_hasScoped_true_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_BulkActionsTpl_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "assets/src/admin/components/list-table/BulkActionsTpl.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-66769835", Component.options)
  } else {
    hotAPI.reload("data-v-66769835", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 73 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 74 */
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
              return _c(
                "li",
                {
                  key: bulkAction.key,
                  on: {
                    click: function($event) {
                      _vm.bulkActionSelect(bulkAction.key)
                    }
                  }
                },
                [
                  _c("img", {
                    attrs: { src: bulkAction.img, alt: bulkAction.label }
                  }),
                  _vm._v(" "),
                  _c("span", [_vm._v(_vm._s(bulkAction.label))])
                ]
              )
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
    require("vue-hot-reload-api")      .rerender("data-v-66769835", esExports)
  }
}

/***/ }),
/* 75 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_Dropdown_vue__ = __webpack_require__(20);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_60309122_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_Dropdown_vue__ = __webpack_require__(77);
var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(76)
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_Dropdown_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_60309122_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_Dropdown_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "assets/src/admin/components/dropdown/Dropdown.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-60309122", Component.options)
  } else {
    hotAPI.reload("data-v-60309122", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 76 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 77 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    {
      staticClass: "dropdown",
      on: {
        click: function($event) {
          $event.preventDefault()
          return _vm.toggleDropdown($event)
        }
      }
    },
    [
      _vm._t("button", [
        _c(
          "button",
          {
            staticClass: "btn btn-secondary dropdown-toggle",
            attrs: {
              type: "button",
              "data-toggle": "dropdown",
              "aria-haspopup": "true",
              "aria-expanded": "false"
            }
          },
          [_vm._v("\n      Dropdown\n    ")]
        )
      ]),
      _vm._v(" "),
      _c(
        "div",
        {
          ref: "menu",
          class: ["dropdown-menu", _vm.dropdownClasses, { show: _vm.visible }],
          on: {
            click: function($event) {
              $event.stopPropagation()
            }
          }
        },
        [_vm._t("dropdown")],
        2
      )
    ],
    2
  )
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-60309122", esExports)
  }
}

/***/ }),
/* 78 */
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
                  _vm._v(" of\n            "),
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
                                "\n            " +
                                  _vm._s(value.label) +
                                  "\n          "
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
                      _vm._v(_vm._s(value.label))
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
            ? _vm._l(_vm.rows, function(row, i) {
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
                                "\n              " +
                                  _vm._s(row[key]) +
                                  "\n            "
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
                                      _c(
                                        "dropdown",
                                        { attrs: { placement: "left" } },
                                        [
                                          _c("template", { slot: "button" }, [
                                            _c("span", [_vm._v("⋮")])
                                          ]),
                                          _vm._v(" "),
                                          _c("template", { slot: "dropdown" }, [
                                            _c(
                                              "ul",
                                              {
                                                attrs: { slot: "action-items" },
                                                slot: "action-items"
                                              },
                                              _vm._l(_vm.actions, function(
                                                action
                                              ) {
                                                return _c(
                                                  "li",
                                                  {
                                                    key: action.key,
                                                    class: action.key
                                                  },
                                                  [
                                                    _c(
                                                      "a",
                                                      {
                                                        attrs: { href: "#" },
                                                        on: {
                                                          click: function(
                                                            $event
                                                          ) {
                                                            $event.preventDefault()
                                                            _vm.actionClicked(
                                                              action.key,
                                                              row,
                                                              i
                                                            )
                                                          }
                                                        }
                                                      },
                                                      [
                                                        _vm._v(
                                                          _vm._s(action.label)
                                                        )
                                                      ]
                                                    )
                                                  ]
                                                )
                                              })
                                            )
                                          ])
                                        ],
                                        2
                                      )
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
                    "\n            " +
                      _vm._s(_vm.currentPage) +
                      " of\n            "
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
    require("vue-hot-reload-api")      .rerender("data-v-50f2b730", esExports)
  }
}

/***/ }),
/* 79 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_axios__ = __webpack_require__(22);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_axios___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_axios__);

/* harmony default export */ __webpack_exports__["a"] = (__WEBPACK_IMPORTED_MODULE_0_axios___default.a.create({
  baseURL: erp_acct_var.site_url + '/wp-json/erp/v1/accounting/v1',
  headers: {
    'X-WP-Nonce': erp_acct_var.rest_nonce
  }
}));

/***/ }),
/* 80 */,
/* 81 */,
/* 82 */,
/* 83 */,
/* 84 */,
/* 85 */,
/* 86 */,
/* 87 */,
/* 88 */,
/* 89 */,
/* 90 */,
/* 91 */,
/* 92 */,
/* 93 */,
/* 94 */,
/* 95 */,
/* 96 */,
/* 97 */,
/* 98 */
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
      _c("list-table", {
        attrs: {
          tableClass: "wp-ListTable widefat fixed customer-list",
          "action-column": "actions",
          columns: _vm.columns,
          rows: _vm.row_data,
          "bulk-actions": _vm.bulkActions,
          "total-items": _vm.paginationData.totalItems,
          "total-pages": _vm.paginationData.totalPages,
          "per-page": _vm.paginationData.perPage,
          "current-page": _vm.paginationData.currentPage,
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
/* 99 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_Vendors_vue__ = __webpack_require__(28);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_0984f520_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_Vendors_vue__ = __webpack_require__(101);
var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(100)
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

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 100 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 101 */
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
/* 102 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_Employees_vue__ = __webpack_require__(29);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_3f2c203a_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_Employees_vue__ = __webpack_require__(104);
var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(103)
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

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 103 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 104 */
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
],[44]);