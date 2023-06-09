pluginWebpack([1],[
/* 0 */,
/* 1 */,
/* 2 */,
/* 3 */,
/* 4 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_BaseLayout_vue__ = __webpack_require__(51);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_16af2479_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_BaseLayout_vue__ = __webpack_require__(128);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_BaseLayout_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_16af2479_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_BaseLayout_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/layouts/BaseLayout.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-16af2479", Component.options)
  } else {
    hotAPI.reload("data-v-16af2479", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 5 */,
/* 6 */,
/* 7 */,
/* 8 */,
/* 9 */,
/* 10 */,
/* 11 */,
/* 12 */,
/* 13 */,
/* 14 */,
/* 15 */,
/* 16 */,
/* 17 */,
/* 18 */,
/* 19 */,
/* 20 */,
/* 21 */,
/* 22 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_SubmitButton_vue__ = __webpack_require__(50);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_52bbe600_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_SubmitButton_vue__ = __webpack_require__(117);
var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(116)
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_SubmitButton_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_52bbe600_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_SubmitButton_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/base/SubmitButton.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-52bbe600", Component.options)
  } else {
    hotAPI.reload("data-v-52bbe600", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 23 */,
/* 24 */,
/* 25 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "a", function() { return generateFormDataFromObject; });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_typeof__ = __webpack_require__(12);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_typeof___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_typeof__);


/**
 * Generate Form Data from Object
 *
 * @since 1.8.6
 *
 * @param object object data
 *
 * @return Object FormData Object
 */
var generateFormDataFromObject = function generateFormDataFromObject(object) {
  var formData = new FormData();
  buildFormData(formData, object);
  return formData;
};

var buildFormData = function buildFormData(formData, data, parentKey) {
  if (data && __WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_typeof___default()(data) === 'object' && !(data instanceof Date) && !(data instanceof File)) {
    Object.keys(data).forEach(function (key) {
      buildFormData(formData, data[key], parentKey ? "".concat(parentKey, "[").concat(key, "]") : key);
    });
  } else {
    var value = data == null ? '' : data;
    formData.append(parentKey, value);
  }
};
/**
 * Get Base 64 string from file
 *
 * @since 1.8.6
 *
 * @param object object data
 *
 * @return Promise
 */


var getBase64StringFromFile = function getBase64StringFromFile(file) {
  return new Promise(function (resolve, reject) {
    var reader = new FileReader();
    reader.readAsDataURL(file);

    reader.onload = function () {
      var encoded = reader.result.toString().replace(/^data:(.*,)?/, '');

      if (encoded.length % 4 > 0) {
        encoded += '='.repeat(4 - encoded.length % 4);
      }

      resolve(encoded);
    };

    reader.onerror = function (error) {
      return reject(error);
    };
  });
};

/***/ }),
/* 26 */,
/* 27 */,
/* 28 */,
/* 29 */,
/* 30 */,
/* 31 */,
/* 32 */,
/* 33 */,
/* 34 */,
/* 35 */,
/* 36 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_DatePicker_vue__ = __webpack_require__(47);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_49b6181c_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_DatePicker_vue__ = __webpack_require__(112);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_DatePicker_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_49b6181c_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_DatePicker_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/base/DatePicker.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-49b6181c", Component.options)
  } else {
    hotAPI.reload("data-v-49b6181c", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 37 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_BaseContentLayout_vue__ = __webpack_require__(53);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_796c2e84_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_BaseContentLayout_vue__ = __webpack_require__(127);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_BaseContentLayout_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_796c2e84_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_BaseContentLayout_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/layouts/BaseContentLayout.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-796c2e84", Component.options)
  } else {
    hotAPI.reload("data-v-796c2e84", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 38 */,
/* 39 */,
/* 40 */,
/* 41 */,
/* 42 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__popperjs_core__ = __webpack_require__(88);
//
//
//
//
//
//
//
//
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
    var _this = this;

    // Create non-reactive property
    this._popper = null;
    this.$parent.$on('action:click', function () {
      _this.visible = false;
    });
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
      this.initPopper(element);
    },
    hideMenu: function hideMenu() {
      this.$root.$emit('hidden');
      this.removePopper();
    },
    initPopper: function initPopper(element) {
      this.removePopper();
      this._popper = new __WEBPACK_IMPORTED_MODULE_0__popperjs_core__["a" /* createPopper */](element, this.$refs.menu, {
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
/* 43 */,
/* 44 */,
/* 45 */,
/* 46 */,
/* 47 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
//
//
//
//
/* harmony default export */ __webpack_exports__["a"] = ({
  props: ['value', 'dependency'],
  mounted: function mounted() {
    var self = this,
        limit_date = self.dependency == 'datepickter-from' ? 'maxDate' : 'minDate';
    jQuery(self.$el).datepicker({
      dateFormat: 'yy-mm-dd',
      changeYear: true,
      changeMonth: true,
      numberOfMonths: 1,
      yearRange: "-100:+5",
      onClose: function onClose(selectedDate) {
        jQuery('.' + self.dependency).datepicker('option', limit_date, selectedDate);
      },
      onSelect: function onSelect(dateText) {
        self.$emit('input', dateText);
      }
    });
  },
  methods: {
    changeDateInput: function changeDateInput(e) {
      this.$emit('input', e.target.value);
    }
  }
});

/***/ }),
/* 48 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_MultiSelect_vue__ = __webpack_require__(49);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_1894e21a_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_MultiSelect_vue__ = __webpack_require__(115);
var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(113)
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_MultiSelect_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_1894e21a_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_MultiSelect_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/select/MultiSelect.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-1894e21a", Component.options)
  } else {
    hotAPI.reload("data-v-1894e21a", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 49 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vue_multiselect__ = __webpack_require__(90);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vue_multiselect___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_vue_multiselect__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__select_debounce__ = __webpack_require__(114);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_vue_multiselect_dist_vue_multiselect_min_css__ = __webpack_require__(91);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_vue_multiselect_dist_vue_multiselect_min_css___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_2_vue_multiselect_dist_vue_multiselect_min_css__);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
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
  name: 'MultiSelect',
  components: {
    Multiselect: __WEBPACK_IMPORTED_MODULE_0_vue_multiselect___default.a
  },
  props: {
    value: {
      type: null,
      required: true
    },
    options: {
      type: Array | Object,
      default: function _default() {
        return [];
      }
    },
    multiple: {
      type: Boolean,
      default: false
    },
    disabled: {
      type: Boolean,
      default: false
    },
    placeholder: {
      type: String,
      default: __('Please search', 'erp')
    },
    id: {
      type: String,
      required: false,
      default: 'id'
    }
  },
  data: function data() {
    return {
      noResult: false,
      isLoading: false,
      results: [],
      icon: erp_settings_var.erp_assets + '/images/wperp-settings/select-arrow.png'
    };
  },
  watch: {
    options: function options() {
      this.results = [];
      this.isLoading = false;
    }
  },
  methods: {
    onSelect: function onSelect(selected) {
      if (this.multiple) {
        this.results.push(selected);
        this.$emit('input', this.results);
      } else {
        this.$emit('input', selected);
      }
    },

    /**
     * Process options data with object
     */
    getOptions: function getOptions(options) {
      var keys = Object.keys(options);
      var data = [];
      keys.forEach(function (key) {
        var singleData = {
          id: key !== null ? key.toString() : '',
          name: options[key]
        };
        data.push(singleData);
      });
      return data;
    },
    onRemove: function onRemove(removed) {
      this.results = this.results.filter(function (element) {
        return element.id !== removed.id;
      });
      this.$emit('input', this.results);
    },
    onDropdownOpen: function onDropdownOpen(id) {
      this.$root.$emit('dropdown-open');
    },
    asyncFind: Object(__WEBPACK_IMPORTED_MODULE_1__select_debounce__["a" /* default */])(function (query) {
      // this.isLoading = true;
      this.$root.$emit('options-query', query);
    }, 1)
  }
});

/***/ }),
/* 50 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
//
//
//
//
//
//
/* harmony default export */ __webpack_exports__["a"] = ({
  name: 'SubmitButton',
  props: {
    text: {
      type: String,
      default: __('Submit', 'erp')
    },
    working: {
      type: Boolean,
      default: false
    },
    customClass: {
      type: String,
      required: false,
      default: ''
    }
  },
  computed: {
    fullClass: function fullClass() {
      return 'wperp-btn btn--primary settings-button ' + this.customClass;
    }
  }
});

/***/ }),
/* 51 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__menu_SettingsSubMenu_vue__ = __webpack_require__(118);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__layouts_BaseContentLayout_vue__ = __webpack_require__(37);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
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
  name: "BaseLayout",
  components: {
    SettingsSubMenu: __WEBPACK_IMPORTED_MODULE_0__menu_SettingsSubMenu_vue__["a" /* default */],
    BaseContentLayout: __WEBPACK_IMPORTED_MODULE_1__layouts_BaseContentLayout_vue__["a" /* default */]
  },
  data: function data() {
    return {
      sectionTitle: "",
      subSectionTitle: "",
      subSectionDescription: "",
      subMenus: [],
      allFields: [],
      inputFields: [],
      single_option: true
    };
  },
  props: {
    section_id: {
      type: String,
      required: true
    },
    sub_section_id: {
      type: String,
      required: true
    },
    sub_sub_section_id: {
      type: String,
      required: false
    },
    enable_content: {
      type: Boolean,
      required: false
    },
    options: {
      type: Object,
      required: false
    },
    enableSubSectionTitle: {
      type: Boolean,
      required: false,
      default: true
    },
    disableMenu: {
      type: Boolean,
      required: false,
      default: false
    },
    disableSectionTitle: {
      type: Boolean,
      required: false,
      default: false
    }
  },
  created: function created() {
    var _this = this;

    // process the menus and get the sections data
    var menus = erp_settings_var.erp_settings_menus;
    var parentMenu = menus.find(function (menu) {
      return menu.id === _this.section_id;
    });
    this.sectionTitle = this.getSectionTitle(parentMenu);
    this.subMenus = parentMenu.sections;
    var fields = [];
    this.single_option = parentMenu.single_option; // Check if second level sub_section_id is provided, like email_connect > gmail

    if (typeof this.sub_sub_section_id !== 'undefined' && this.sub_sub_section_id.length > 0) {
      var subSectionFields = parentMenu.fields[this.sub_section_id];
      subSectionFields.map(function (subSectionField) {
        if (subSectionField.type === 'sub_sections') {
          fields = subSectionField.sub_sections[_this.sub_sub_section_id].fields;
        }
      });
    } else {
      if (parentMenu.single_option) {
        fields = parentMenu.fields[this.sub_section_id];
      } else {
        if (this.section_id !== this.sub_section_id) {
          fields = parentMenu.fields[this.sub_section_id];
        } else {
          fields = parentMenu.fields;
        }
      }
    }

    this.allFields = this.inputFields = fields;

    if (typeof fields !== 'undefined' && Object.keys(fields).length > 0) {
      var _fields$, _fields$2;

      this.subSectionTitle = (_fields$ = fields[0]) === null || _fields$ === void 0 ? void 0 : _fields$.title;
      this.subSectionDescription = (_fields$2 = fields[0]) === null || _fields$2 === void 0 ? void 0 : _fields$2.desc;

      if (this.enable_content) {
        // Process the fields and get the real input fields
        var inputFields = [];
        fields.forEach(function (field) {
          if (field.type !== "title" && field.type !== "sectionend") {
            field.value = null;
            inputFields.push(field);
          }
        });
        this.inputFields = inputFields;
      }
    }
  }
});

/***/ }),
/* 52 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__base_Dropdown_vue__ = __webpack_require__(87);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
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
  name: "SettingsSubMenu",
  props: {
    parent_id: {
      type: String,
      required: true
    },
    menus: {
      type: Object,
      required: false
    }
  },
  data: function data() {
    return {
      dropdownMenuStartPos: 5,
      dropdownMenuEndPos: 5
    };
  },
  created: function created() {
    if (this.menus.length > 5) {
      this.dropdownMenuStartPos = 5;
      this.dropdownMenuEndPos = this.menus.length;
    }
  },
  components: {
    Dropdown: __WEBPACK_IMPORTED_MODULE_0__base_Dropdown_vue__["a" /* default */]
  },
  methods: {
    activeRouteClass: function activeRouteClass(index) {
      var currentRouteName = this.$route.name;
      var routeClassName = '';

      switch (currentRouteName) {
        case 'HRWorkDays':
        case 'AcCustomer':
        case 'CrmContacts':
        case 'GeneralEmail':
        case 'WCSynchronization':
        case 'WCOrderSync':
          routeClassName = 'router-link-active';
          break;

        default:
          break;
      }

      if (routeClassName.length === 0 || index !== 0) {
        routeClassName = '';
      }

      return routeClassName;
    }
  }
});

/***/ }),
/* 53 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_defineProperty__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_defineProperty___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_defineProperty__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__base_DatePicker_vue__ = __webpack_require__(36);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__base_SubmitButton_vue__ = __webpack_require__(22);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__base_ImagePicker_vue__ = __webpack_require__(120);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4__base_Tooltip_vue__ = __webpack_require__(55);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5__select_MultiSelect_vue__ = __webpack_require__(48);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6__layouts_partials_InputDesc_vue__ = __webpack_require__(124);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_7__partials_Switch_vue__ = __webpack_require__(58);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_8__utils_FormDataHandler__ = __webpack_require__(25);


function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { __WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_defineProperty___default()(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//








var $ = jQuery;
/* harmony default export */ __webpack_exports__["a"] = ({
  name: "BaseContentLayout",
  components: {
    SubmitButton: __WEBPACK_IMPORTED_MODULE_2__base_SubmitButton_vue__["a" /* default */],
    ImagePicker: __WEBPACK_IMPORTED_MODULE_3__base_ImagePicker_vue__["a" /* default */],
    Tooltip: __WEBPACK_IMPORTED_MODULE_4__base_Tooltip_vue__["a" /* default */],
    InputDesc: __WEBPACK_IMPORTED_MODULE_6__layouts_partials_InputDesc_vue__["a" /* default */],
    RadioSwitch: __WEBPACK_IMPORTED_MODULE_7__partials_Switch_vue__["a" /* default */],
    DatePicker: __WEBPACK_IMPORTED_MODULE_1__base_DatePicker_vue__["a" /* default */],
    MultiSelect: __WEBPACK_IMPORTED_MODULE_5__select_MultiSelect_vue__["a" /* default */]
  },
  data: function data() {
    return {
      fields: [],
      optionsMutable: this.options
    };
  },
  props: {
    inputs: {
      type: Array | Object,
      required: true
    },
    section_id: {
      type: String,
      required: true
    },
    sub_section_id: {
      type: String,
      required: true
    },
    single_option: {
      type: Boolean,
      required: true
    },
    sub_sub_section_title: {
      type: String,
      required: false
    },
    sub_sub_section_id: {
      type: String,
      required: false,
      default: ''
    },
    hide_submit: {
      type: Boolean,
      required: false,
      default: false
    },
    options: {
      type: Object,
      required: false,
      default: function _default() {
        return {
          action: '',
          recurrent: false,
          fields: []
        };
      }
    }
  },
  created: function created() {
    this.getSettingsData();
  },
  methods: {
    /**
     * Get Settings Data
     */
    getSettingsData: function getSettingsData() {
      var self = this;
      self.$store.dispatch("spinner/setSpinner", true);
      var requestData = window.settings.hooks.applyFilters("requestData", _objectSpread(_objectSpread({}, self.inputs), {}, {
        single_option: !self.single_option ? self.section_id : null,
        section_id: self.section_id,
        sub_section_id: self.sub_section_id,
        sub_sub_section_id: self.sub_sub_section_id,
        _wpnonce: erp_settings_var.nonce,
        action: 'erp-settings-get-data'
      }));
      var postData = Object(__WEBPACK_IMPORTED_MODULE_8__utils_FormDataHandler__["a" /* generateFormDataFromObject */])(requestData);
      $.ajax({
        url: erp_settings_var.ajax_url,
        type: "POST",
        data: postData,
        processData: false,
        contentType: false,
        success: function success(response) {
          self.$store.dispatch("spinner/setSpinner", false);

          if (response.success) {
            self.fields = response.data; // Process returned data to show for vue

            response.data.forEach(function (item, index) {
              if ('multicheck' === item.type) {
                var initialCheckedData = []; // First assign to false or uncheck if nothing found from response

                Object.keys(item.options).forEach(function (optionKey) {
                  initialCheckedData[optionKey] = false;
                });

                if (item.value !== null && item.value !== false) {
                  Object.keys(item.options).forEach(function (optionKey) {
                    initialCheckedData[optionKey] = typeof item.value[optionKey] !== 'undefined' ? true : false;
                  });
                }

                self.fields[index]['value'] = initialCheckedData;
              } else if ('select' === item.type) {
                Object.keys(item.options).forEach(function (optionKey) {
                  if (optionKey === item.value) {
                    self.fields[index]['value'] = {
                      id: optionKey,
                      name: item.options[optionKey]
                    };
                  }
                });
              } else if ('hidden-fixed' === item.type) {
                self.fields[index]['value'] = self.inputs.find(function (input) {
                  return input.id === item.id;
                })['value'];
              } else if ('html' === item.type) {
                self.fields[index]['value'] = self.inputs.find(function (input) {
                  return input.id === item.id;
                })['value'];
              }
            });
          }
        }
      });
    },

    /**
     * Submit settings global form data
     */
    onFormSubmit: function onFormSubmit() {
      var self = this;
      self.$store.dispatch("spinner/setSpinner", true);
      var requestDataPost = {}; // Process fields and send to post data

      self.fields.forEach(function (item) {
        if (item !== null && typeof item.id !== 'undefined') {
          requestDataPost[item.id] = item.value;
          var initialCheckedData = [];

          if (item.type === 'multicheck') {
            Object.keys(item.options).forEach(function (optionKey) {
              if (item.value[optionKey] !== false) initialCheckedData[optionKey] = optionKey;
            });
            requestDataPost[item.id] = initialCheckedData;
          }

          if (item.type === 'checkbox' && (item.value === false || item.value === 'no')) {
            requestDataPost[item.id] = null;
          }

          if (item.type === 'select' && (item.value !== "" || item.value !== null)) {
            requestDataPost[item.id] = item.value ? item.value.id : '';
          }
        }
      });

      if (typeof self.sub_sub_section_id !== 'undefined' && self.sub_sub_section_id !== '') {
        requestDataPost['sub_sub_section'] = self.sub_sub_section_id;
      }

      if (typeof self.optionsMutable.fields !== 'undefined' && Array.isArray(self.optionsMutable.fields)) {
        self.optionsMutable.fields.forEach(function (field) {
          requestDataPost[field.key] = field.value;
        });
      }

      var requestData = _objectSpread(_objectSpread({}, requestDataPost), {}, {
        _wpnonce: erp_settings_var.nonce,
        action: !self.optionsMutable.action ? "erp-settings-save" : self.optionsMutable.action,
        module: self.section_id,
        section: self.sub_section_id
      });

      requestData = window.settings.hooks.applyFilters("requestData", requestData);
      var postData = Object(__WEBPACK_IMPORTED_MODULE_8__utils_FormDataHandler__["a" /* generateFormDataFromObject */])(requestData);
      $.ajax({
        url: erp_settings_var.ajax_url,
        type: "POST",
        data: postData,
        processData: false,
        contentType: false,
        success: function success(response) {
          self.$store.dispatch("spinner/setSpinner", false);

          if (response.success) {
            self.showAlert("success", response.data.message);
            self.$store.dispatch("formdata/setFormData", requestData);
          } else {
            self.showAlert("error", response.data);
          }
        }
      });

      if (!self.optionsMutable.recurrent) {
        self.optionsMutable = {
          action: '',
          recurrent: false,
          fields: []
        };
      }
    },

    /**
     * Change Image Type Inputs
     */
    changeImage: function changeImage(value, index) {
      this.fields[index]['value'] = value;
    },

    /**
     * Toggle switch
     */
    toggleSwitch: function toggleSwitch(index) {
      this.fields[index]['value'] = this.fields[index]['value'] == 'yes' ? 'no' : 'yes';
    },

    /**
     * Change Radio Type Inputs
     */
    changeRadioInput: function changeRadioInput(index, key) {
      this.fields[index]['value'] = key;
    }
  },
  watch: {
    options: {
      handler: function handler(newVal, oldValue) {
        this.optionsMutable = newVal;
      },
      deep: true
    }
  }
});

/***/ }),
/* 54 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vue_dropify__ = __webpack_require__(121);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vue_dropify___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_vue_dropify__);
//
//
//
//
//
//
//
//
//
//
//
//
//
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
  name: "ImagePicker",
  props: {
    value: {
      required: true
    }
  },
  components: {
    VueDropify: __WEBPACK_IMPORTED_MODULE_0_vue_dropify___default.a
  },
  data: function data() {
    return {
      imageValue: this.value
    };
  },
  methods: {
    changeImage: function changeImage() {
      if (this.imageValue !== null && this.imageValue.length > 0) {
        this.$emit("changeImage", this.imageValue[0]);
      } else {
        this.$emit("changeImage", '');
      }
    }
  }
});

/***/ }),
/* 55 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_Tooltip_vue__ = __webpack_require__(56);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_6774445d_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_Tooltip_vue__ = __webpack_require__(123);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_Tooltip_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_6774445d_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_Tooltip_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/base/Tooltip.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-6774445d", Component.options)
  } else {
    hotAPI.reload("data-v-6774445d", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 56 */
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
/* harmony default export */ __webpack_exports__["a"] = ({
  name: 'Tooltip',
  props: {
    input: {
      type: Object,
      required: true
    }
  },
  methods: {
    getTooltipText: function getTooltipText() {
      var toolTipText = '';

      if (this.input.tooltip) {
        if (typeof this.input.tooltip_text !== 'undefined' && this.input.tooltip_text !== '') {
          toolTipText = this.input.tooltip_text;
        } else if (typeof this.input.desc !== 'undefined' && this.input.desc !== '') {
          toolTipText = this.input.desc;
        }
      }

      return toolTipText;
    }
  }
});

/***/ }),
/* 57 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
//
//
//
//
/* harmony default export */ __webpack_exports__["a"] = ({
  name: "InputDesc",
  props: {
    input: {
      type: Object,
      required: true
    }
  },
  computed: {
    desc: function desc() {
      return this.input.desc.replace(/\\"/g, '"');
    }
  },
  methods: {
    /**
     * Check if description will be enable for input
     */
    isEnableDescription: function isEnableDescription(input) {
      return input.desc && input.desc.length > 0 && !input.tooltip && input.type !== 'checkbox';
    }
  }
});

/***/ }),
/* 58 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_Switch_vue__ = __webpack_require__(59);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_bca00e3a_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_Switch_vue__ = __webpack_require__(126);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_Switch_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_bca00e3a_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_Switch_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/layouts/partials/Switch.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-bca00e3a", Component.options)
  } else {
    hotAPI.reload("data-v-bca00e3a", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 59 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_bulma_switch_dist_css_bulma_switch_min_css__ = __webpack_require__(207);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_bulma_switch_dist_css_bulma_switch_min_css___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_bulma_switch_dist_css_bulma_switch_min_css__);
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["a"] = ({
  name: "RadioSwitch",
  props: {
    label: String,
    id: String,
    value: '',
    type: {
      type: String,
      required: false,
      default: 'default'
    }
  },
  computed: {
    switchType: function switchType() {
      return "switch is-rounded is-".concat(this.type);
    },
    checked: function checked() {
      if (this.value == true || this.value == 'yes' || this.value == 'on') {
        return 'checked';
      }

      return '';
    }
  },
  methods: {
    toggle: function toggle() {
      this.$emit('toggle');
    }
  }
});

/***/ }),
/* 60 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_Modal_vue__ = __webpack_require__(61);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_30c61af2_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_Modal_vue__ = __webpack_require__(129);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_Modal_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_30c61af2_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_Modal_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/base/Modal.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-30c61af2", Component.options)
  } else {
    hotAPI.reload("data-v-30c61af2", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 61 */
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
/* harmony default export */ __webpack_exports__["a"] = ({
  name: 'Modal',
  props: {
    header: {
      type: Boolean,
      required: false,
      default: false
    },
    footer: {
      type: Boolean,
      required: false,
      default: false
    },
    title: {
      type: String,
      required: false,
      default: ''
    },
    hasForm: {
      type: Boolean,
      required: false,
      default: false
    },
    size: {
      type: String,
      required: false,
      default: 'md'
    }
  },
  computed: {
    modalContentClass: function modalContentClass() {
      return "wperp-modal-dialog wperp-modal-dialog-".concat(this.size);
    }
  }
});

/***/ }),
/* 62 */,
/* 63 */,
/* 64 */,
/* 65 */,
/* 66 */,
/* 67 */,
/* 68 */,
/* 69 */,
/* 70 */,
/* 71 */,
/* 72 */,
/* 73 */,
/* 74 */,
/* 75 */,
/* 76 */,
/* 77 */,
/* 78 */,
/* 79 */,
/* 80 */,
/* 81 */,
/* 82 */,
/* 83 */,
/* 84 */,
/* 85 */,
/* 86 */,
/* 87 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_Dropdown_vue__ = __webpack_require__(42);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_2b3919b2_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_Dropdown_vue__ = __webpack_require__(111);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_Dropdown_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_2b3919b2_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_Dropdown_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/base/Dropdown.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-2b3919b2", Component.options)
  } else {
    hotAPI.reload("data-v-2b3919b2", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
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
/* 98 */,
/* 99 */,
/* 100 */,
/* 101 */,
/* 102 */,
/* 103 */,
/* 104 */,
/* 105 */,
/* 106 */,
/* 107 */,
/* 108 */,
/* 109 */,
/* 110 */,
/* 111 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    {
      staticClass: "dropdown wperp-has-dropdown",
      on: {
        click: function($event) {
          $event.preventDefault()
          return _vm.toggleDropdown.apply(null, arguments)
        }
      }
    },
    [
      _vm._t("button", function() {
        return [
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
            [
              _vm._v(
                "\n            " +
                  _vm._s(_vm.__("Dropdown", "erp")) +
                  "\n        "
              )
            ]
          )
        ]
      }),
      _vm._v(" "),
      _c(
        "div",
        {
          ref: "menu",
          class: [
            "dropdown-popper dropdown-menu",
            _vm.dropdownClasses,
            { show: _vm.visible }
          ],
          on: {
            click: function($event) {
              $event.stopPropagation()
            }
          }
        },
        [
          _c("div", { staticClass: "popper__arrow", attrs: { "x-arrow": "" } }),
          _vm._v(" "),
          _vm._t("dropdown")
        ],
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
    require("vue-hot-reload-api")      .rerender("data-v-2b3919b2", esExports)
  }
}

/***/ }),
/* 112 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("input", {
    attrs: { type: "text", autocomplete: "off" },
    domProps: { value: _vm.value },
    on: { input: _vm.changeDateInput }
  })
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-49b6181c", esExports)
  }
}

/***/ }),
/* 113 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 114 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony default export */ __webpack_exports__["a"] = (function (fn, delay) {
  var timeoutID = null;
  return function () {
    clearTimeout(timeoutID);
    var args = arguments;
    var that = this;
    timeoutID = setTimeout(function () {
      fn.apply(that, args);
    }, delay);
  };
});

/***/ }),
/* 115 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "multiselect",
    {
      attrs: {
        value: _vm.value,
        options: _vm.getOptions(_vm.options),
        multiple: _vm.multiple,
        "close-on-select": !_vm.multiple,
        loading: _vm.isLoading,
        placeholder: _vm.placeholder,
        disabled: _vm.disabled,
        label: "name",
        "track-by": _vm.id
      },
      on: {
        open: _vm.onDropdownOpen,
        remove: _vm.onRemove,
        select: _vm.onSelect,
        "search-change": _vm.asyncFind
      },
      scopedSlots: _vm._u([
        {
          key: "singleLabel",
          fn: function(ref) {
            var option = ref.option
            return [
              _c("span", { domProps: { innerHTML: _vm._s(option.name) } })
            ]
          }
        },
        {
          key: "option",
          fn: function(ref) {
            var option = ref.option
            return [
              _c("span", { domProps: { innerHTML: _vm._s(option.name) } })
            ]
          }
        }
      ])
    },
    [
      _c("span", { attrs: { slot: "noResult" }, slot: "noResult" }, [
        _vm._v(_vm._s(_vm.__("Oops! No item found.", "erp")))
      ]),
      _vm._v(" "),
      _c(
        "span",
        {
          staticClass: "multiselect-custom-arrow",
          attrs: { slot: "caret" },
          slot: "caret"
        },
        [_c("img", { attrs: { src: _vm.icon, alt: "" } })]
      )
    ]
  )
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-1894e21a", esExports)
  }
}

/***/ }),
/* 116 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 117 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("button", { class: _vm.fullClass, attrs: { type: "submit" } }, [
    _vm._v("\n    " + _vm._s(_vm.text) + "\n")
  ])
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-52bbe600", esExports)
  }
}

/***/ }),
/* 118 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_SettingsSubMenu_vue__ = __webpack_require__(52);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_0d023f84_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_SettingsSubMenu_vue__ = __webpack_require__(119);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_SettingsSubMenu_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_0d023f84_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_SettingsSubMenu_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/menu/SettingsSubMenu.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-0d023f84", Component.options)
  } else {
    hotAPI.reload("data-v-0d023f84", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 119 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return Object.keys(_vm.menus).length > 0
    ? _c("div", { staticClass: "settings-submenu-navbar" }, [
        _c(
          "ul",
          { staticClass: "settings-sub-menu" },
          [
            _vm._l(_vm.menus, function(menu, key, index) {
              return [
                index < _vm.dropdownMenuStartPos
                  ? _c(
                      "li",
                      { key: key },
                      [
                        _c(
                          "router-link",
                          {
                            class: _vm.activeRouteClass(index),
                            attrs: {
                              tag: "li",
                              to: "/" + _vm.parent_id + "/" + key
                            }
                          },
                          [
                            _c("a", { attrs: { href: "#" } }, [
                              _c("span", { staticClass: "menu-name" }, [
                                _vm._v(_vm._s(menu))
                              ])
                            ])
                          ]
                        )
                      ],
                      1
                    )
                  : _vm._e()
              ]
            }),
            _vm._v(" "),
            _vm.dropdownMenuStartPos > 0 && Object.keys(_vm.menus).length > 5
              ? _c(
                  "dropdown",
                  [
                    _c("template", { slot: "button" }, [
                      _c("a", { attrs: { href: "#" } }, [
                        _vm._v(" " + _vm._s(_vm.__("More", "erp")) + "   "),
                        _c("i", { staticClass: "fa fa-chevron-down" })
                      ])
                    ]),
                    _vm._v(" "),
                    _c("template", { slot: "dropdown" }, [
                      _c(
                        "ul",
                        { attrs: { role: "menu" } },
                        [
                          _vm._l(_vm.menus, function(menu, key, index) {
                            return [
                              index >= _vm.dropdownMenuStartPos
                                ? _c(
                                    "li",
                                    {
                                      key: index,
                                      staticClass: "dropdown-list-item"
                                    },
                                    [
                                      _c(
                                        "router-link",
                                        {
                                          attrs: {
                                            to: "/" + _vm.parent_id + "/" + key
                                          }
                                        },
                                        [
                                          _c(
                                            "span",
                                            { staticClass: "menu-name" },
                                            [_vm._v(_vm._s(menu))]
                                          )
                                        ]
                                      )
                                    ],
                                    1
                                  )
                                : _vm._e()
                            ]
                          })
                        ],
                        2
                      )
                    ])
                  ],
                  2
                )
              : _vm._e()
          ],
          2
        )
      ])
    : _vm._e()
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-0d023f84", esExports)
  }
}

/***/ }),
/* 120 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_ImagePicker_vue__ = __webpack_require__(54);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_0464c3ba_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_ImagePicker_vue__ = __webpack_require__(122);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_ImagePicker_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_0464c3ba_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_ImagePicker_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/base/ImagePicker.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-0464c3ba", Component.options)
  } else {
    hotAPI.reload("data-v-0464c3ba", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 121 */,
/* 122 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    [
      typeof this.imageValue === "string" && this.imageValue.length > 0
        ? _c("vue-dropify", {
            attrs: {
              accept: "image/*",
              uploadIcon: "fa fa-cloud-upload",
              message: _vm.__("Upload Image", "erp"),
              src: typeof this.imageValue === "string" ? this.imageValue : ""
            },
            on: { change: _vm.changeImage },
            model: {
              value: _vm.imageValue,
              callback: function($$v) {
                _vm.imageValue = $$v
              },
              expression: "imageValue"
            }
          })
        : _c("vue-dropify", {
            attrs: {
              accept: "image/*",
              uploadIcon: "fa fa-cloud-upload",
              message: _vm.__("Upload Image", "erp")
            },
            on: { change: _vm.changeImage },
            model: {
              value: _vm.imageValue,
              callback: function($$v) {
                _vm.imageValue = $$v
              },
              expression: "imageValue"
            }
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
    require("vue-hot-reload-api")      .rerender("data-v-0464c3ba", esExports)
  }
}

/***/ }),
/* 123 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _vm.input.tooltip
    ? _c("span", { staticClass: "erp-settings-tooltip" }, [
        _c(
          "svg",
          {
            attrs: {
              width: "13px",
              height: "13px",
              viewBox: "0 0 13 13",
              version: "1.1",
              xmlns: "http://www.w3.org/2000/svg",
              "xmlns:xlink": "http://www.w3.org/1999/xlink"
            }
          },
          [
            _c(
              "g",
              {
                attrs: {
                  id: "Tooltip",
                  stroke: "none",
                  "stroke-width": "1",
                  fill: "none",
                  "fill-rule": "evenodd"
                }
              },
              [
                _c(
                  "g",
                  {
                    attrs: {
                      transform: "translate(-639.000000, -350.000000)",
                      fill: "#CECFD3",
                      "fill-rule": "nonzero"
                    }
                  },
                  [
                    _c("path", {
                      attrs: {
                        d:
                          "M645.499725,350 C641.910349,350 639,352.910349 639,356.499725 C639,360.089101 641.910349,363 645.499725,363 C649.089101,363 652,360.089101 652,356.499725 C652,352.910349 649.089101,350 645.499725,350 Z M646.852825,360.073693 C646.518265,360.205757 646.251937,360.305905 646.05219,360.375238 C645.852995,360.444571 645.621333,360.479238 645.357757,360.479238 C644.952762,360.479238 644.63746,360.38019 644.412952,360.182646 C644.188444,359.985101 644.076741,359.73473 644.076741,359.430434 C644.076741,359.312127 644.084995,359.191069 644.101503,359.06781 C644.118561,358.94455 644.145524,358.805884 644.182392,358.650159 L644.601143,357.171048 C644.638011,357.029079 644.669926,356.894265 644.695238,356.768804 C644.72055,356.642243 644.732656,356.526138 644.732656,356.420487 C644.732656,356.232296 644.693587,356.100233 644.616,356.025947 C644.537312,355.951661 644.389291,355.915344 644.168635,355.915344 C644.060783,355.915344 643.94963,355.931302 643.835725,355.964868 C643.722921,355.999534 643.624974,356.030899 643.544635,356.061714 L643.655238,355.606095 C643.92927,355.494392 644.191746,355.398646 644.442116,355.319407 C644.692487,355.239069 644.929101,355.19945 645.151958,355.19945 C645.554201,355.19945 645.86455,355.297397 646.083005,355.49109 C646.30036,355.685333 646.409862,355.937905 646.409862,356.248254 C646.409862,356.312635 646.402159,356.425989 646.387302,356.587767 C646.372444,356.750095 646.344381,356.898116 646.303661,357.034032 L645.887111,358.508741 C645.852995,358.627048 645.82273,358.762413 645.795217,358.913735 C645.768254,359.065058 645.755048,359.180614 645.755048,359.258201 C645.755048,359.454095 645.798519,359.58781 645.886561,359.658794 C645.973503,359.729778 646.125926,359.765545 646.34163,359.765545 C646.443429,359.765545 646.557333,359.747386 646.686095,359.712169 C646.813757,359.676952 646.906201,359.645587 646.964529,359.618624 L646.852825,360.073693 Z M646.77909,354.087915 C646.584847,354.268402 646.350984,354.358646 646.077503,354.358646 C645.804571,354.358646 645.569058,354.268402 645.373164,354.087915 C645.17837,353.907429 645.079873,353.687873 645.079873,353.43145 C645.079873,353.175577 645.178921,352.955471 645.373164,352.773333 C645.569058,352.590646 645.804571,352.499852 646.077503,352.499852 C646.350984,352.499852 646.585397,352.590646 646.77909,352.773333 C646.973333,352.955471 647.07073,353.175577 647.07073,353.43145 C647.07073,353.688423 646.973333,353.907429 646.77909,354.087915 Z",
                        id: "Shape"
                      }
                    })
                  ]
                )
              ]
            )
          ]
        ),
        _vm._v(" "),
        _c("span", {
          staticClass: "tooltiptext",
          domProps: { innerHTML: _vm._s(_vm.getTooltipText()) }
        })
      ])
    : _vm._e()
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-6774445d", esExports)
  }
}

/***/ }),
/* 124 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_InputDesc_vue__ = __webpack_require__(57);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_7d3443c8_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_InputDesc_vue__ = __webpack_require__(125);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_InputDesc_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_7d3443c8_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_InputDesc_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/layouts/partials/InputDesc.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-7d3443c8", Component.options)
  } else {
    hotAPI.reload("data-v-7d3443c8", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 125 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _vm.isEnableDescription(_vm.input)
    ? _c("p", {
        staticClass: "erp-form-input-hint",
        domProps: { innerHTML: _vm._s(_vm.desc) }
      })
    : _vm._e()
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-7d3443c8", esExports)
  }
}

/***/ }),
/* 126 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", { staticClass: "field" }, [
    _c("input", {
      class: _vm.switchType,
      attrs: { type: "checkbox", name: "switchRounded" },
      domProps: { checked: _vm.checked }
    }),
    _vm._v(" "),
    _c(
      "label",
      { attrs: { for: "switchRounded" }, on: { click: _vm.toggle } },
      [_vm._v(_vm._s(_vm.label))]
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
    require("vue-hot-reload-api")      .rerender("data-v-bca00e3a", esExports)
  }
}

/***/ }),
/* 127 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "form",
    {
      staticClass: "wperp-form",
      attrs: { action: "", method: "post", enctype: "multipart/form-data" },
      on: {
        submit: function($event) {
          $event.preventDefault()
          return _vm.onFormSubmit.apply(null, arguments)
        }
      }
    },
    [
      typeof _vm.sub_sub_section_title !== "undefined" &&
      _vm.sub_sub_section_title.length > 0
        ? _c("h3", {
            staticClass: "sub-sub-title",
            domProps: { innerHTML: _vm._s(_vm.sub_sub_section_title) }
          })
        : _vm._e(),
      _vm._v(" "),
      _vm._t("extended-data-before"),
      _vm._v(" "),
      _vm._l(_vm.fields, function(input, index) {
        return _c("div", { key: index }, [
          _c(
            "div",
            { staticClass: "wperp-form-group" },
            [
              _c(
                "label",
                { attrs: { for: "erp-" + _vm.fields[index]["id"] } },
                [
                  _c("span", { domProps: { innerHTML: _vm._s(input.title) } }),
                  _vm._v(" "),
                  input ? _c("tooltip", { attrs: { input: input } }) : _vm._e()
                ],
                1
              ),
              _vm._v(" "),
              input.type === "select"
                ? [
                    _c("multi-select", {
                      attrs: {
                        options: input.options,
                        id: "erp-" + _vm.fields[index]["id"],
                        placeholder:
                          "Please select " + _vm.fields[index]["title"]
                      },
                      model: {
                        value: _vm.fields[index]["value"],
                        callback: function($$v) {
                          _vm.$set(_vm.fields[index], "value", $$v)
                        },
                        expression: "fields[index]['value']"
                      }
                    }),
                    _vm._v(" "),
                    _c("input-desc", { attrs: { input: input } })
                  ]
                : input.type === "label"
                ? [
                    _c("div", {
                      attrs: { id: "erp-" + _vm.fields[index]["id"] },
                      domProps: { innerHTML: _vm._s(input.value) }
                    }),
                    _vm._v(" "),
                    _c("input-desc", { attrs: { input: input } })
                  ]
                : input.type === "hidden"
                ? [
                    _c("input", {
                      attrs: {
                        type: "hidden",
                        id: "erp-" + _vm.fields[index]["id"]
                      },
                      domProps: { value: _vm.fields[index]["value"] }
                    })
                  ]
                : input.type === "hidden-fixed"
                ? [
                    _c("input", {
                      attrs: {
                        type: "hidden",
                        id: "erp-" + _vm.fields[index]["id"]
                      },
                      domProps: { value: _vm.fields[index]["value"] }
                    })
                  ]
                : input.type === "checkbox"
                ? _c(
                    "div",
                    { staticClass: "form-check" },
                    [
                      _c("label", { staticClass: "form-check-label" }, [
                        _c("input", {
                          directives: [
                            {
                              name: "model",
                              rawName: "v-model",
                              value: _vm.fields[index]["value"],
                              expression: "fields[index]['value']"
                            }
                          ],
                          staticClass: "form-check-input",
                          attrs: {
                            type: "checkbox",
                            id: "erp-" + _vm.fields[index]["id"]
                          },
                          domProps: {
                            checked: Array.isArray(_vm.fields[index]["value"])
                              ? _vm._i(_vm.fields[index]["value"], null) > -1
                              : _vm.fields[index]["value"]
                          },
                          on: {
                            change: function($event) {
                              var $$a = _vm.fields[index]["value"],
                                $$el = $event.target,
                                $$c = $$el.checked ? true : false
                              if (Array.isArray($$a)) {
                                var $$v = null,
                                  $$i = _vm._i($$a, $$v)
                                if ($$el.checked) {
                                  $$i < 0 &&
                                    _vm.$set(
                                      _vm.fields[index],
                                      "value",
                                      $$a.concat([$$v])
                                    )
                                } else {
                                  $$i > -1 &&
                                    _vm.$set(
                                      _vm.fields[index],
                                      "value",
                                      $$a
                                        .slice(0, $$i)
                                        .concat($$a.slice($$i + 1))
                                    )
                                }
                              } else {
                                _vm.$set(_vm.fields[index], "value", $$c)
                              }
                            }
                          }
                        }),
                        _vm._v(" "),
                        _vm._m(0, true),
                        _vm._v(" "),
                        _c("span", {
                          staticClass: "form-check-label-light",
                          domProps: { innerHTML: _vm._s(input.desc) }
                        })
                      ]),
                      _vm._v(" "),
                      _c("input-desc", { attrs: { input: input } })
                    ],
                    1
                  )
                : input.type === "radio"
                ? _c(
                    "div",
                    [
                      _c("radio-switch", {
                        attrs: {
                          value: _vm.fields[index]["value"],
                          id: "erp-" + _vm.fields[index]["id"]
                        },
                        on: {
                          toggle: function($event) {
                            return _vm.toggleSwitch(index)
                          }
                        }
                      }),
                      _vm._v(" "),
                      _c("input-desc", { attrs: { input: input } })
                    ],
                    1
                  )
                : input.type === "multicheck"
                ? _c(
                    "div",
                    { staticClass: "form-check" },
                    [
                      _vm._l(input.options, function(
                        checkOption,
                        checkKey,
                        index2
                      ) {
                        return _c(
                          "label",
                          { key: index2, staticClass: "form-check-label" },
                          [
                            _c("input", {
                              directives: [
                                {
                                  name: "model",
                                  rawName: "v-model",
                                  value: _vm.fields[index]["value"][checkKey],
                                  expression: "fields[index]['value'][checkKey]"
                                }
                              ],
                              staticClass: "form-check-input",
                              attrs: {
                                type: "checkbox",
                                id: "erp-" + _vm.fields[index]["id"][checkKey]
                              },
                              domProps: {
                                checked: Array.isArray(
                                  _vm.fields[index]["value"][checkKey]
                                )
                                  ? _vm._i(
                                      _vm.fields[index]["value"][checkKey],
                                      null
                                    ) > -1
                                  : _vm.fields[index]["value"][checkKey]
                              },
                              on: {
                                change: function($event) {
                                  var $$a =
                                      _vm.fields[index]["value"][checkKey],
                                    $$el = $event.target,
                                    $$c = $$el.checked ? true : false
                                  if (Array.isArray($$a)) {
                                    var $$v = null,
                                      $$i = _vm._i($$a, $$v)
                                    if ($$el.checked) {
                                      $$i < 0 &&
                                        _vm.$set(
                                          _vm.fields[index]["value"],
                                          checkKey,
                                          $$a.concat([$$v])
                                        )
                                    } else {
                                      $$i > -1 &&
                                        _vm.$set(
                                          _vm.fields[index]["value"],
                                          checkKey,
                                          $$a
                                            .slice(0, $$i)
                                            .concat($$a.slice($$i + 1))
                                        )
                                    }
                                  } else {
                                    _vm.$set(
                                      _vm.fields[index]["value"],
                                      checkKey,
                                      $$c
                                    )
                                  }
                                }
                              }
                            }),
                            _vm._v(" "),
                            _vm._m(1, true),
                            _vm._v(" "),
                            _c("span", {
                              staticClass: "form-check-label-light",
                              domProps: { innerHTML: _vm._s(checkOption) }
                            })
                          ]
                        )
                      }),
                      _vm._v(" "),
                      _c("input-desc", { attrs: { input: input } })
                    ],
                    2
                  )
                : input.type === "text" ||
                  input.type === "textarea" ||
                  input.type === "password" ||
                  input.type === "email"
                ? _c(
                    "div",
                    [
                      input.type === "text" && input.class !== "erp-date-field"
                        ? _c("input", {
                            directives: [
                              {
                                name: "model",
                                rawName: "v-model",
                                value: _vm.fields[index]["value"],
                                expression: "fields[index]['value']"
                              }
                            ],
                            staticClass: "wperp-form-field",
                            attrs: {
                              placeholder: _vm.fields[index]["placeholder"]
                                ? _vm.fields[index]["placeholder"]
                                : "",
                              id: "erp-" + _vm.fields[index]["id"],
                              disabled: _vm.fields[index]["disabled"]
                                ? true
                                : false
                            },
                            domProps: { value: _vm.fields[index]["value"] },
                            on: {
                              input: function($event) {
                                if ($event.target.composing) {
                                  return
                                }
                                _vm.$set(
                                  _vm.fields[index],
                                  "value",
                                  $event.target.value
                                )
                              }
                            }
                          })
                        : input.type === "text" &&
                          input.class === "erp-date-field"
                        ? _c("date-picker", {
                            staticClass: "wperp-form-field",
                            attrs: {
                              placeholder: _vm.__("Select date", "erp"),
                              id: "erp-" + _vm.fields[index]["id"]
                            },
                            model: {
                              value: _vm.fields[index]["value"],
                              callback: function($$v) {
                                _vm.$set(_vm.fields[index], "value", $$v)
                              },
                              expression: "fields[index]['value']"
                            }
                          })
                        : input.type === "textarea"
                        ? _c("textarea", {
                            directives: [
                              {
                                name: "model",
                                rawName: "v-model",
                                value: _vm.fields[index]["value"],
                                expression: "fields[index]['value']"
                              }
                            ],
                            staticClass: "wperp-form-field",
                            attrs: {
                              cols: "45",
                              rows: "4",
                              id: "erp-" + _vm.fields[index]["id"],
                              disabled: _vm.fields[index]["disabled"]
                                ? true
                                : false
                            },
                            domProps: { value: _vm.fields[index]["value"] },
                            on: {
                              input: function($event) {
                                if ($event.target.composing) {
                                  return
                                }
                                _vm.$set(
                                  _vm.fields[index],
                                  "value",
                                  $event.target.value
                                )
                              }
                            }
                          })
                        : input.type === "password"
                        ? _c("input", {
                            directives: [
                              {
                                name: "model",
                                rawName: "v-model",
                                value: _vm.fields[index]["value"],
                                expression: "fields[index]['value']"
                              }
                            ],
                            staticClass: "wperp-form-field",
                            attrs: {
                              type: "password",
                              id: "erp-" + _vm.fields[index]["id"],
                              disabled: _vm.fields[index]["disabled"]
                                ? true
                                : false
                            },
                            domProps: { value: _vm.fields[index]["value"] },
                            on: {
                              input: function($event) {
                                if ($event.target.composing) {
                                  return
                                }
                                _vm.$set(
                                  _vm.fields[index],
                                  "value",
                                  $event.target.value
                                )
                              }
                            }
                          })
                        : input.type === "email"
                        ? _c("input", {
                            directives: [
                              {
                                name: "model",
                                rawName: "v-model",
                                value: _vm.fields[index]["value"],
                                expression: "fields[index]['value']"
                              }
                            ],
                            staticClass: "wperp-form-field",
                            attrs: {
                              type: "email",
                              id: "erp-" + _vm.fields[index]["id"],
                              disabled: _vm.fields[index]["disabled"]
                                ? true
                                : false
                            },
                            domProps: { value: _vm.fields[index]["value"] },
                            on: {
                              input: function($event) {
                                if ($event.target.composing) {
                                  return
                                }
                                _vm.$set(
                                  _vm.fields[index],
                                  "value",
                                  $event.target.value
                                )
                              }
                            }
                          })
                        : _vm._e(),
                      _vm._v(" "),
                      _c("input-desc", { attrs: { input: input } })
                    ],
                    1
                  )
                : input.type === "image"
                ? _c(
                    "div",
                    [
                      _c("image-picker", {
                        attrs: {
                          value: _vm.fields[index]["value"],
                          id: "erp-" + _vm.fields[index]["id"]
                        },
                        on: {
                          changeImage: function(value) {
                            return _vm.changeImage(value, index)
                          }
                        },
                        model: {
                          value: _vm.fields[index]["value"],
                          callback: function($$v) {
                            _vm.$set(_vm.fields[index], "value", $$v)
                          },
                          expression: "fields[index]['value']"
                        }
                      })
                    ],
                    1
                  )
                : input.type === "html"
                ? _c("div", { domProps: { innerHTML: _vm._s(input.value) } })
                : _vm._e()
            ],
            2
          )
        ])
      }),
      _vm._v(" "),
      _vm._t("extended-data"),
      _vm._v(" "),
      !_vm.hide_submit
        ? _c(
            "div",
            { staticClass: "wperp-form-group" },
            [
              _c("submit-button", {
                attrs: { text: _vm.__("Save Changes", "erp") }
              }),
              _vm._v(" "),
              _c("div", { staticClass: "clearfix" })
            ],
            1
          )
        : _vm._e()
    ],
    2
  )
}
var staticRenderFns = [
  function() {
    var _vm = this
    var _h = _vm.$createElement
    var _c = _vm._self._c || _h
    return _c("span", { staticClass: "form-check-sign" }, [
      _c("span", { staticClass: "check" })
    ])
  },
  function() {
    var _vm = this
    var _h = _vm.$createElement
    var _c = _vm._self._c || _h
    return _c("span", { staticClass: "form-check-sign" }, [
      _c("span", { staticClass: "check" })
    ])
  }
]
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-796c2e84", esExports)
  }
}

/***/ }),
/* 128 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    [
      !_vm.disableSectionTitle
        ? _c("h2", { staticClass: "section-title" }, [
            _vm._v(_vm._s(_vm.sectionTitle))
          ])
        : _vm._e(),
      _vm._v(" "),
      Object.keys(_vm.subMenus).length > 0 && !_vm.disableMenu
        ? [
            _c("settings-sub-menu", {
              attrs: { menus: _vm.subMenus, parent_id: _vm.section_id }
            })
          ]
        : _vm._e(),
      _vm._v(" "),
      _c(
        "div",
        {
          staticClass: "settings-box",
          attrs: {
            id: "erp-settings-box-" + _vm.section_id + "-" + _vm.sub_section_id
          }
        },
        [
          _vm.subSectionTitle && _vm.enableSubSectionTitle
            ? _c(
                "h3",
                { staticClass: "sub-section-title" },
                [
                  _vm._t("subSectionTitle", function() {
                    return [_vm._v(_vm._s(_vm.subSectionTitle))]
                  })
                ],
                2
              )
            : _vm._e(),
          _vm._v(" "),
          _vm.subSectionDescription && _vm.enableSubSectionTitle
            ? _c("p", {
                staticClass: "sub-section-description",
                domProps: { innerHTML: _vm._s(_vm.subSectionDescription) }
              })
            : _vm._e(),
          _vm._v(" "),
          !_vm.enable_content ? _vm._t("default") : _vm._e(),
          _vm._v(" "),
          _vm.enable_content
            ? _c(
                "base-content-layout",
                {
                  attrs: {
                    section_id: _vm.section_id,
                    sub_section_id: _vm.sub_section_id,
                    sub_sub_section_id: _vm.sub_sub_section_id,
                    inputs: _vm.inputFields,
                    single_option: _vm.single_option,
                    options: _vm.options
                  }
                },
                [
                  _c(
                    "div",
                    { attrs: { slot: "extended-data" }, slot: "extended-data" },
                    [_vm._t("extended-data")],
                    2
                  )
                ]
              )
            : _vm._e()
        ],
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
    require("vue-hot-reload-api")      .rerender("data-v-16af2479", esExports)
  }
}

/***/ }),
/* 129 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    {
      class: [
        "wperp-modal",
        "wperp-modal-open",
        _vm.hasForm ? "wperp-has-form" : ""
      ]
    },
    [
      _c("div", { class: _vm.modalContentClass }, [
        _c("div", { staticClass: "wperp-modal-content" }, [
          _c(
            "div",
            { staticClass: "wperp-modal-header" },
            [
              _vm.header
                ? _vm._t("header", function() {
                    return [_c("h3", [_vm._v(_vm._s(_vm.title))])]
                  })
                : _vm._e(),
              _vm._v(" "),
              _c(
                "span",
                {
                  staticClass: "modal-close",
                  on: {
                    click: function($event) {
                      return _vm.$emit("close")
                    }
                  }
                },
                [_c("i", { staticClass: "flaticon-close" })]
              )
            ],
            2
          ),
          _vm._v(" "),
          _c("div", { staticClass: "wperp-modal-body" }, [_vm._t("body")], 2),
          _vm._v(" "),
          _vm.footer
            ? _c(
                "div",
                { staticClass: "wperp-modal-footer" },
                [_vm._t("footer")],
                2
              )
            : _vm._e()
        ])
      ])
    ]
  )
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-30c61af2", esExports)
  }
}

/***/ }),
/* 130 */,
/* 131 */,
/* 132 */,
/* 133 */,
/* 134 */,
/* 135 */,
/* 136 */,
/* 137 */,
/* 138 */,
/* 139 */,
/* 140 */,
/* 141 */,
/* 142 */,
/* 143 */,
/* 144 */,
/* 145 */,
/* 146 */,
/* 147 */,
/* 148 */,
/* 149 */,
/* 150 */,
/* 151 */,
/* 152 */,
/* 153 */,
/* 154 */,
/* 155 */,
/* 156 */,
/* 157 */,
/* 158 */,
/* 159 */,
/* 160 */,
/* 161 */,
/* 162 */,
/* 163 */,
/* 164 */,
/* 165 */,
/* 166 */,
/* 167 */,
/* 168 */,
/* 169 */,
/* 170 */,
/* 171 */,
/* 172 */,
/* 173 */,
/* 174 */,
/* 175 */,
/* 176 */,
/* 177 */,
/* 178 */,
/* 179 */,
/* 180 */,
/* 181 */,
/* 182 */,
/* 183 */,
/* 184 */,
/* 185 */,
/* 186 */,
/* 187 */,
/* 188 */,
/* 189 */,
/* 190 */,
/* 191 */,
/* 192 */,
/* 193 */,
/* 194 */,
/* 195 */,
/* 196 */,
/* 197 */,
/* 198 */,
/* 199 */,
/* 200 */,
/* 201 */,
/* 202 */,
/* 203 */,
/* 204 */,
/* 205 */,
/* 206 */,
/* 207 */,
/* 208 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vuex__ = __webpack_require__(5);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__components_menu_SettingsMenu_vue__ = __webpack_require__(431);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__components_searchbar_SearchBar_vue__ = __webpack_require__(433);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_vue_loading_overlay__ = __webpack_require__(86);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_vue_loading_overlay___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_3_vue_loading_overlay__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4_vue_loading_overlay_dist_vue_loading_css__ = __webpack_require__(211);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4_vue_loading_overlay_dist_vue_loading_css___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_4_vue_loading_overlay_dist_vue_loading_css__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5__assets_font_flaticon_css__ = __webpack_require__(436);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5__assets_font_flaticon_css___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_5__assets_font_flaticon_css__);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
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
  name: 'Settings',
  components: {
    SettingsMenu: __WEBPACK_IMPORTED_MODULE_1__components_menu_SettingsMenu_vue__["a" /* default */],
    SearchBar: __WEBPACK_IMPORTED_MODULE_2__components_searchbar_SearchBar_vue__["a" /* default */],
    Loading: __WEBPACK_IMPORTED_MODULE_3_vue_loading_overlay___default.a
  },
  computed: Object(__WEBPACK_IMPORTED_MODULE_0_vuex__["b" /* mapState */])({
    loader: function loader(state) {
      return state.spinner.loader;
    }
  })
});

/***/ }),
/* 209 */
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
/* harmony default export */ __webpack_exports__["a"] = ({
  name: "SettingsMenu",
  data: function data() {
    return {
      menus: erp_settings_var.erp_settings_menus
    };
  }
});

/***/ }),
/* 210 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_defineProperty__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_defineProperty___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_defineProperty__);


function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { __WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_defineProperty___default()(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
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
  name: "SearchBar",
  data: function data() {
    return {
      searchText: "",
      allItems: [],
      searchedItems: []
    };
  },
  created: function created() {
    this.getAllItems();
  },
  methods: {
    /**
     * Get all items in searchable list
     */
    getAllItems: function getAllItems() {
      var _this = this;

      var menus = erp_settings_var.erp_settings_menus;
      var allItems = [];
      menus.forEach(function (menu, index) {
        var searchItem = {
          id: menu.id,
          label: menu.label,
          parentLabel: _this.getSectionTitle(menu),
          desc: menu.desc,
          parentId: null,
          icon: menu.icon,
          url: "/".concat(menu.id)
        };
        allItems.push(searchItem); // Push fields in the array

        var isFieldArray = _this.isArray(menu.fields);

        Object.keys(menu.fields).forEach(function (key) {
          var fieldItem = menu.fields[key];

          if (isFieldArray) {
            if (typeof fieldItem.title != "undefined" && fieldItem.title.length > 0) {
              var newFeildItem = _objectSpread(_objectSpread({}, searchItem), {}, {
                id: fieldItem.id,
                label: fieldItem.title,
                parentLabel: searchItem.parentLabel,
                desc: fieldItem.desc
              });

              allItems.push(newFeildItem);
            }
          } else {
            var subSectionKey = key;
            Object.keys(fieldItem).forEach(function (subKey) {
              var subField = fieldItem[subKey];

              if (typeof subField.title != "undefined" && subField.title.length > 0) {
                var _newFeildItem = _objectSpread(_objectSpread({}, searchItem), {}, {
                  id: subField.id,
                  label: subField.title,
                  parentLabel: searchItem.parentLabel,
                  desc: subField.desc,
                  url: "".concat(searchItem.url, "/").concat(subSectionKey)
                });

                allItems.push(_newFeildItem);
              }
            });
          }
        });
      });
      this.allItems = allItems;
      this.searchedItems = allItems;
    }
  },
  watch: {
    /**
     * Watch `search` text key using Regex and filter by -
     * Label or Parent Menu Label or description
     *
     * @return void
     */
    searchText: function searchText() {
      var searchText = this.searchText !== null ? String(this.searchText).toLowerCase() : '';
      var regex = new RegExp(searchText, "i");
      this.searchedItems = this.allItems.filter(function (item) {
        var label = typeof item.label !== 'undefined' && item.label !== null ? String(item.label).toLowerCase() : '';
        var parentLabel = typeof item.parentLabel !== 'undefined' && item.parentLabel !== null ? String(item.parentLabel).toLowerCase() : '';
        var desc = typeof item.desc !== 'undefined' && item.desc !== null ? String(item.desc).toLowerCase() : '';
        var fullMatchedString = "".concat(label, " ").concat(parentLabel, " ").concat(desc);
        return regex.test(fullMatchedString);
      });
    },

    /**
     * If any route change, then reset the search bar
     */
    $route: function $route() {
      this.searchText = '';
      this.searchedItems = this.allItems;
    }
  }
});

/***/ }),
/* 211 */,
/* 212 */,
/* 213 */
/***/ (function(module, exports) {

module.exports = "data:application/vnd.ms-fontobject;base64,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EhMODgmJEgENDhQRDQ4BkREBDg4SEw0OAa8JHwEXFQUBTAEZAR8BFgcCAQgZG50GDwEfAg8CnCMVCAKAEQINDhMSDQ4JCRACDg0TEw0NAXIRAQ4NFBINDQEODREUDg4TEQ4gDhAVDQ4TEg1ADhISDg4SEw3+egwcAgbBtSMXCAIBCBms7QQBFQEEDAEZBkELDg8BSgYVBQAAAAAOAK4AAQAAAAAAAAA+AH4AAQAAAAAAAQAIAM8AAQAAAAAAAgAGAOYAAQAAAAAAAwAjATUAAQAAAAAABAAIAWsAAQAAAAAABQAQAZYAAQAAAAAABgAIAbkAAwABBAkAAAB8AAAAAwABBAkAAQAQAL0AAwABBAkAAgAMANgAAwABBAkAAwBGAO0AAwABBAkABAAQAVkAAwABBAkABQAgAXQAAwABBAkABgAQAacAQwByAGUAYQB0AGUAZAAgAGIAeQAgAEEAcABhAGMAaABlACAAdwBpAHQAaAAgAEYAbwBuAHQARgBvAHIAZwBlACAAMgAuADAAIAAoAGgAdAB0AHAAOgAvAC8AZgBvAG4AdABmAG8AcgBnAGUALgBzAGYALgBuAGUAdAApAABDcmVhdGVkIGJ5IEFwYWNoZSB3aXRoIEZvbnRGb3JnZSAyLjAgKGh0dHA6Ly9mb250Zm9yZ2Uuc2YubmV0KQAARgBsAGEAdABpAGMAbwBuAABGbGF0aWNvbgAATQBlAGQAaQB1AG0AAE1lZGl1bQAARgBvAG4AdABGAG8AcgBnAGUAIAAyAC4AMAAgADoAIABGAGwAYQB0AGkAYwBvAG4AIAA6ACAAOAAtADEALQAyADAAMQA5AABGb250Rm9yZ2UgMi4wIDogRmxhdGljb24gOiA4LTEtMjAxOQAARgBsAGEAdABpAGMAbwBuAABGbGF0aWNvbgAAVgBlAHIAcwBpAG8AbgAgADAAMAAxAC4AMAAwADAAIAAAVmVyc2lvbiAwMDEuMDAwIAAARgBsAGEAdABpAGMAbwBuAABGbGF0aWNvbgAAAgAAAAAAAP/AABkAAAAAAAAAAAAAAAAAAAAAAAAAAAAyAAAAAQACAAMBAgEDAQQBBQEGAQcBCAEJAQoBCwEMAQ0BDgEPARABEQESARMBFAEVARYBFwEYARkBGgEbARwBHQEeAR8BIAEhASIBIwEkASUBJgEnASgBKQEqASsBLAEtAS4BLwd1bmlGMTAwB3VuaUYxMDEHdW5pRjEwMgd1bmlGMTAzB3VuaUYxMDQHdW5pRjEwNQd1bmlGMTA2B3VuaUYxMDcHdW5pRjEwOAd1bmlGMTA5B3VuaUYxMEEHdW5pRjEwQgd1bmlGMTBDB3VuaUYxMEQHdW5pRjEwRQd1bmlGMTBGB3VuaUYxMTAHdW5pRjExMQd1bmlGMTEyB3VuaUYxMTMHdW5pRjExNAd1bmlGMTE1B3VuaUYxMTYHdW5pRjExNwd1bmlGMTE4B3VuaUYxMTkHdW5pRjExQQd1bmlGMTFCB3VuaUYxMUMHdW5pRjExRAd1bmlGMTFFB3VuaUYxMUYHdW5pRjEyMAd1bmlGMTIxB3VuaUYxMjIHdW5pRjEyMwd1bmlGMTI0B3VuaUYxMjUHdW5pRjEyNgd1bmlGMTI3B3VuaUYxMjgHdW5pRjEyOQd1bmlGMTJBB3VuaUYxMkIHdW5pRjEyQwd1bmlGMTJEAAAAAAAB//8AAgAAAAEAAAAAzD2izwAAAADYWj/oAAAAANhaP+g="

/***/ }),
/* 214 */
/***/ (function(module, exports) {

module.exports = "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBzdGFuZGFsb25lPSJubyI/Pgo8IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4iICJodHRwOi8vd3d3LnczLm9yZy9HcmFwaGljcy9TVkcvMS4xL0RURC9zdmcxMS5kdGQiID4KPCEtLQoyMDE5LTEtODogQ3JlYXRlZC4KLS0+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPG1ldGFkYXRhPgpDcmVhdGVkIGJ5IEZvbnRGb3JnZSAyMDEyMDczMSBhdCBUdWUgSmFuICA4IDEyOjU0OjE2IDIwMTkKIEJ5IEFwYWNoZQpDcmVhdGVkIGJ5IEFwYWNoZSB3aXRoIEZvbnRGb3JnZSAyLjAgKGh0dHA6Ly9mb250Zm9yZ2Uuc2YubmV0KQo8L21ldGFkYXRhPgo8ZGVmcz4KPGZvbnQgaWQ9IkZsYXRpY29uIiBob3Jpei1hZHYteD0iNTEyIiA+CiAgPGZvbnQtZmFjZSAKICAgIGZvbnQtZmFtaWx5PSJGbGF0aWNvbiIKICAgIGZvbnQtd2VpZ2h0PSI1MDAiCiAgICBmb250LXN0cmV0Y2g9Im5vcm1hbCIKICAgIHVuaXRzLXBlci1lbT0iNTEyIgogICAgcGFub3NlLTE9IjIgMCA2IDMgMCAwIDAgMCAwIDAiCiAgICBhc2NlbnQ9IjQ0OCIKICAgIGRlc2NlbnQ9Ii02NCIKICAgIGJib3g9Ii0wLjAwMTYwNDAyIC02NC4wMDEgNTEyLjAwMSA0NDguMDAxIgogICAgdW5kZXJsaW5lLXRoaWNrbmVzcz0iMjUuNiIKICAgIHVuZGVybGluZS1wb3NpdGlvbj0iLTUxLjIiCiAgICB1bmljb2RlLXJhbmdlPSJVKzAwMjAtRjEyRCIKICAvPgogICAgPG1pc3NpbmctZ2x5cGggLz4KICAgIDxnbHlwaCBnbHlwaC1uYW1lPSJzcGFjZSIgdW5pY29kZT0iICIgaG9yaXotYWR2LXg9IjIwMCIgCiAvPgogICAgPGdseXBoIGdseXBoLW5hbWU9InVuaUYxMEYiIHVuaWNvZGU9IiYjeGYxMGY7IiAKZD0iTTMzNC4yNzMgMjU3LjMyOGMtNy41NjgzNiAtMzAuNDc2NiAtMjUuMjMyNCAtMzcuMTg5NSAtMzQuNjU4MiAtMzguNTM3MWMtNS44MDQ2OSAtMTIuMjIxNyAtMTguNTY2NCAtMzcuMjAxMiAtMzEuMzM0IC00OS43NTFjLTUuMTAzNTIgLTUuMDE1NjIgLTExLjczMjQgLTkuMjQ5MDIgLTE5LjcwOSAtMTIuNTgxMWMtMTAuMTc5NyAtNC4yNTM5MSAtMjAuOTMzNiAtNi40MTExMyAtMzEuOTU3IC02LjQxMTEzCmMtMTEuMDIxNSAwIC0yMS43NzM0IDIuMTU3MjMgLTMxLjk1MzEgNi40MTExM2MtNy45NzQ2MSAzLjMzMDA4IC0xNC42MDc0IDcuNTYzNDggLTE5LjcxMDkgMTIuNTgxMWMtMTIuNzY3NiAxMi41NDc5IC0yNS41MzEyIDM3LjUyOTMgLTMxLjMzNTkgNDkuNzUxYy05LjQyMTg4IDEuMzQ3NjYgLTI3LjA4NTkgOC4wNTg1OSAtMzQuNjU2MiAzOC41MzcxYy00LjA0Njg4IDE2LjI5ODggLTMuMDExNzIgMjkuNTg1IDMuMDgyMDMgMzkuNDkwMgpjMi42OTUzMSA0LjM3NzkzIDUuOTYwOTQgNy4zNjAzNSA5LjAwMTk1IDkuMzc4OTFjMC4wMDc4MTI1IDMuMjUzOTEgMC4xMzY3MTkgNy4zMjcxNSAwLjQ5NjA5NCAxMS45NmMtNS40NzQ2MSA4LjAxMTcyIC0xMi45MTggMjUuMjk1OSAtMy42OTkyMiA1My4yNzI1YzkuNzQyMTkgMjkuNTc4MSAzMS43NzE1IDM0LjE0NTUgNDIuNDAyMyAzNC41NDFjMy41OTc2NiA3LjAzNzExIDEwLjM4ODcgMTcuMDI3MyAyMi43MTg4IDI2LjIxMTkKYzEzLjY5MTQgMTAuMjAwMiAzMS41NjY0IDE1LjgxNzQgNTAuMzMyIDE1LjgxNzRjMjMuMDk1NyAwIDQ2LjMzNCAtOC41NTg1OSA2Ny4yMDUxIC0yNC43NTFjMzMuODM1OSAtMjYuMjUzOSAzMC45Mzc1IC04NC40OTAyIDMwLjEwNzQgLTk1LjA2MzVjMS4yMjY1NiAtOC44NjMyOCAxLjU2ODM2IC0xNi41NTE4IDEuNTgyMDMgLTIxLjk4NTRjMy4wNDEwMiAtMi4wMTk1MyA2LjMwODU5IC01LjAwMDk4IDkuMDAxOTUgLTkuMzc5ODgKYzYuMDkzNzUgLTkuOTA3MjMgNy4xMzA4NiAtMjMuMTkxNCAzLjA4Mzk4IC0zOS40OTIyek00MzMuMjMgMzAuMzc3di04Mi42NjQxYzAgLTYuNDY3NzcgLTQuOTgwNDcgLTExLjcxMTkgLTExLjEyMyAtMTEuNzExOWgtNDEwLjk4NGMtNi4xNDI1OCAwIC0xMS4xMjMgNS4yNDQxNCAtMTEuMTIzIDExLjcxMTl2ODIuNjY0MWMwIDMzLjg2NjIgMTkuMjk0OSA2My44NzcgNDkuMTU2MiA3Ni40NTYxbDk3LjI0MDIgNDAuOTU4CmMyLjc5ODgzIDEuMTc2NzYgNS45Mjk2OSAxLjExMzI4IDguNjgzNTkgLTAuMTg4NDc3YzIuNzUgLTEuMjk4ODMgNC44ODA4NiAtMy43MTc3NyA1LjkwNjI1IC02LjcwMjE1bDI0LjQ0NzMgLTcxLjE2OGw0LjgzNzg5IDE0LjY1MzNsLTcuNjk5MjIgMTkuODIzMmMtMS40MDIzNCAzLjYxMjMgLTEuMDA3ODEgNy43MjI2NiAxLjA2MDU1IDEwLjk2MjljMi4wNjY0MSAzLjIzOTI2IDUuNTI5MyA1LjE4MzU5IDkuMjMyNDIgNS4xODM1OWg0Ny41MDIKYzMuNzA1MDggMCA3LjE2OTkyIC0xLjk0NDM0IDkuMjM0MzggLTUuMTgzNTljMi4wNjY0MSAtMy4yNDAyMyAyLjQ2MDk0IC03LjM1MDU5IDEuMDYwNTUgLTEwLjk2MjlsLTcuNzAxMTcgLTE5LjgyMzJsNC44Mzk4NCAtMTQuNjUzM2wyNC40NDczIDcxLjE2OGMxLjAyMzQ0IDIuOTg0MzggMy4xNTYyNSA1LjQwMzMyIDUuOTA2MjUgNi43MDIxNWMyLjc1MTk1IDEuMjk5OCA1Ljg4NDc3IDEuMzYyMyA4LjY4MTY0IDAuMTg4NDc3Cmw5Ny4yNDAyIC00MC45NThjMjkuODYxMyAtMTIuNTc5MSA0OS4xNTQzIC00Mi41ODk4IDQ5LjE1NDMgLTc2LjQ1NjF6TTM3NC4wMjMgMjEuMzg5NmMwIDYuNDY3NzcgLTQuOTgwNDcgMTEuNzExOSAtMTEuMTE5MSAxMS43MTE5aC03NC42NzU4Yy02LjE0MjU4IDAgLTExLjEyMyAtNS4yNDQxNCAtMTEuMTIzIC0xMS43MTE5di0zNi41MTc2YzAgLTYuNDY3NzcgNC45ODA0NyAtMTEuNzExOSAxMS4xMjMgLTExLjcxMTloNzQuNjc1OApjNi4xMzg2NyAwIDExLjExOTEgNS4yNDQxNCAxMS4xMTkxIDExLjcxMTl2MzYuNTE3NnoiIC8+CiAgICA8Z2x5cGggZ2x5cGgtbmFtZT0idW5pRjEwQSIgdW5pY29kZT0iJiN4ZjEwYTsiIApkPSJNNTEyIDE1NS40MjloLTIxOS40Mjl2LTIxOS40MjloLTczLjE0MjZ2MjE5LjQyOWgtMjE5LjQyOXY3My4xNDI2aDIxOS40Mjl2MjE5LjQyOWg3My4xNDI2di0yMTkuNDI5aDIxOS40Mjl2LTczLjE0MjZ6IiAvPgogICAgPGdseXBoIGdseXBoLW5hbWU9InVuaUYxMEUiIHVuaWNvZGU9IiYjeGYxMGU7IiAKZD0iTTM3Ni4xNTYgNDAwLjQwM2M5Ljc2MDc0IDAgMTcuNjc0OCAtNi45MjY3NiAxNy42NzM4IC0xNS40NzM2di00Ny41OTQ3YzAgLTguNTQ1OSAtNy45MTUwNCAtMTUuNDczNiAtMTcuNjc2OCAtMTUuNDczNmgtMzU4LjQ3N2MtOS43NjI3IDAgLTE3LjY3NjggNi45Mjg3MSAtMTcuNjc2OCAxNS40NzM2djQ3LjU5NDdjMCA4LjU0NTkgNy45MTUwNCAxNS40NzM2IDE3LjY3NjggMTUuNDczNmg5Ni44MjUydjMyLjEyMwpjMCA4LjU0NTkgNy45MTUwNCAxNS40NzM2IDE3LjY3NjggMTUuNDczNmgxMjkuNDc1YzkuNzYyNyAwIDE3LjY3NjggLTYuOTI4NzEgMTcuNjc2OCAtMTUuNDczNnYtMzIuMTIzaDk2LjgyNjJ6TTI0My45NzYgNDAwLjQwM3YxNi42NTE0aC05NC4xMjAxdi0xNi42NTE0aDk0LjEyMDF6TTM1LjgwNDcgMjY1LjE0M2gzNDQuNzM2bC0xNC42NTUzIC0zMDIuNjAyCmMtMC40MDAzOTEgLTguMjgzMiAtOC4xODk0NSAtMTQuODE3NCAtMTcuNjYwMiAtMTQuODE3NGgtMjgwLjEwNGMtOS40NzA3IDAgLTE3LjI1OTggNi41MzQxOCAtMTcuNjYwMiAxNC44MTY0ek0xNTQuNzM5IDEwLjY0NjV2MTkxLjU3NWMwIDguNTQ0OTIgLTcuOTE1MDQgMTUuNDcyNyAtMTcuNjc2OCAxNS40NzI3cy0xNy42NzU4IC02LjkyODcxIC0xNy42NzU4IC0xNS40NzI3di0xOTEuNTc1CmMwIC04LjU0NDkyIDcuOTE1MDQgLTE1LjQ3MjcgMTcuNjc1OCAtMTUuNDcyN2M5Ljc2MjcgMCAxNy42NzY4IDYuOTI3NzMgMTcuNjc2OCAxNS40NzI3ek0yMjUuODQ3IDEwLjY0NjV2MTkxLjU3NWMwIDguNTQ0OTIgLTcuOTE0MDYgMTUuNDcyNyAtMTcuNjc2OCAxNS40NzI3cy0xNy42NzY4IC02LjkyODcxIC0xNy42NzY4IC0xNS40NzI3di0xOTEuNTc1YzAgLTguNTQ0OTIgNy45MTQwNiAtMTUuNDcyNyAxNy42NzY4IC0xNS40NzI3CnMxNy42NzY4IDYuOTI3NzMgMTcuNjc2OCAxNS40NzI3ek0yOTYuOTU0IDEwLjY0NjV2MTkxLjU3NWMwIDguNTQ0OTIgLTcuOTE0MDYgMTUuNDcyNyAtMTcuNjc2OCAxNS40NzI3cy0xNy42NzY4IC02LjkyODcxIC0xNy42NzY4IC0xNS40NzI3di0xOTEuNTc1YzAgLTguNTQ0OTIgNy45MTQwNiAtMTUuNDcyNyAxNy42NzY4IC0xNS40NzI3czE3LjY3NjggNi45Mjc3MyAxNy42NzY4IDE1LjQ3Mjd6IiAvPgogICAgPGdseXBoIGdseXBoLW5hbWU9InVuaUYxMkQiIHVuaWNvZGU9IiYjeGYxMmQ7IiAKZD0iTTI4OC4wMDcgMTI4LjA3OWMtMC4wMDU4NTkzOCA2LjE2NjAyIC0wLjA4MjAzMTIgMTIuMzMzIDAuMDEyNjk1MyAxOC40OThjMC4xMjY5NTMgOC4xODc1IDUuMjk0OTIgMTMuMzQxOCAxMy41NDMgMTMuMzk5NGMxMi4zMzExIDAuMDg2OTE0MSAyNC42NjQxIDAuMDk3NjU2MiAzNi45OTQxIC0wLjAwMjkyOTY5YzguMTgwNjYgLTAuMDY4MzU5NCAxMy4zNTU1IC01LjI5Mzk1IDEzLjQxODkgLTEzLjUzNDIKYzAuMDk0NzI2NiAtMTIuMzMxMSAwLjEwMDU4NiAtMjQuNjY0MSAtMC4wMDM5MDYyNSAtMzYuOTk1MWMtMC4wNjgzNTk0IC04LjE2MzA5IC01LjMwODU5IC0xMy4zNDY3IC0xMy41NDU5IC0xMy40MTQxYy0xMi4zMzExIC0wLjA5OTYwOTQgLTI0LjY2NDEgLTAuMTExMzI4IC0zNi45OTUxIDAuMDAzOTA2MjVjLTguMTk2MjkgMC4wNzYxNzE5IC0xMy4yNjg2IDUuMzEzNDggLTEzLjQyMTkgMTMuNTQ3OQpjLTAuMDU4NTkzOCAzLjE2NTA0IC0wLjAwMzkwNjI1IDYuMzMyMDMgLTAuMDAxOTUzMTIgOS40OTkwMnY4Ljk5ODA1ek0xNjAuMDIgMTI3Ljk0M2MtMC4wMTA3NDIyIDYuMTY0MDYgLTAuMDk0NzI2NiAxMi4zMzAxIDAuMDA5NzY1NjIgMTguNDkzMmMwLjE0MTYwMiA4LjMxNDQ1IDUuMTQ5NDEgMTMuNDQyNCAxMy40MTAyIDEzLjUyMDVjMTIuNDk0MSAwLjExOTE0MSAyNC45OTIyIDAuMTIzMDQ3IDM3LjQ4NjMgLTAuMDEzNjcxOQpjNy42Njk5MiAtMC4wODM5ODQ0IDEyLjkzMzYgLTUuMjQ4MDUgMTMuMDI1NCAtMTIuODc5OWMwLjE1ODIwMyAtMTIuODI3MSAwLjE1NjI1IC0yNS42NTkyIC0wLjAyMDUwNzggLTM4LjQ4NjNjLTAuMDk3NjU2MiAtNy4wNTk1NyAtNS4zMjEyOSAtMTIuMzc1IC0xMi4zNzUgLTEyLjQ4NDRjLTEyLjk5MTIgLTAuMjA0MTAyIC0yNS45OTIyIC0wLjIyMTY4IC0zOC45ODU0IC0wLjAwOTc2NTYyCmMtNy4zNDQ3MyAwLjExOTE0MSAtMTIuMzQ4NiA1LjQ0ODI0IC0xMi41MjA1IDEyLjg2NzJjLTAuMTQ1NTA4IDYuMzI3MTUgLTAuMDMwMjczNCAxMi42NjExIC0wLjAzMDI3MzQgMTguOTkzMnpNMzUyIDIyMy45MDJjMC4wMDE5NTMxMiAtNS45OTkwMiAwLjA1MzcxMDkgLTExLjk5OSAtMC4wMDk3NjU2MiAtMTcuOTk3MWMtMC4wODk4NDM4IC04LjUyNjM3IC01LjIxNzc3IC0xMy44MjIzIC0xMy42MTcyIC0xMy44ODQ4CmMtMTIuMzMxMSAtMC4wODg4NjcyIC0yNC42NjMxIC0wLjA4Nzg5MDYgLTM2Ljk5MzIgMC4wMDI5Mjk2OWMtNy45MjM4MyAwLjA2MDU0NjkgLTEzLjI2NDYgNS4yNTY4NCAtMTMuMzQzOCAxMy4xMDI1Yy0wLjEyNjk1MyAxMi42NjMxIC0wLjEzODY3MiAyNS4zMjkxIDAuMDA3ODEyNSAzNy45OTEyYzAuMDg3ODkwNiA3LjU3ODEyIDUuNDAxMzcgMTIuNzc0NCAxMy4wNjE1IDEyLjg0NzcKYzEyLjY2MjEgMC4xMjIwNyAyNS4zMjkxIDAuMTM4NjcyIDM3Ljk5MTIgLTAuMDA3ODEyNWM3LjU1NjY0IC0wLjA4ODg2NzIgMTIuNjYwMiAtNS40NTExNyAxMi45MDIzIC0xMy4wNTg2YzAuMDA1ODU5MzggLTAuMTY2OTkyIDAuMDAwOTc2NTYyIC0wLjMzMzAwOCAwLjAwMDk3NjU2MiAtMC41di0xOC40OTYxek0xOTIuMzk5IDI1Ni4wMDFjNS45OTgwNSAtMC4wMDE5NTMxMiAxMS45OTUxIDAuMDUyNzM0NCAxNy45OTIyIC0wLjAxNTYyNQpjOC4yMDMxMiAtMC4wOTI3NzM0IDEzLjUwNDkgLTUuMjI5NDkgMTMuNTc4MSAtMTMuMzg2N2MwLjExMTMyOCAtMTIuMzI5MSAwLjA5NjY3OTcgLTI0LjY1OTIgMC4wMDU4NTkzOCAtMzYuOTg3M2MtMC4wNTk1NzAzIC04LjIyNzU0IC01LjIzODI4IC0xMy41MDk4IC0xMy4zODA5IC0xMy41ODJjLTEyLjMyNzEgLTAuMTA4Mzk4IC0yNC42NTYyIC0wLjA5OTYwOTQgLTM2Ljk4NDQgLTAuMDA1ODU5MzgKYy04LjQzNzUgMC4wNjY0MDYyIC0xMy41NTk2IDUuMzQ5NjEgLTEzLjU5NTcgMTMuODc3OWMtMC4wNTE3NTc4IDExLjk5NTEgLTAuMDU2NjQwNiAyMy45OTEyIC0wLjAwMjkyOTY5IDM1Ljk4NzNjMC4wMzkwNjI1IDguODMxMDUgNS4xNjYwMiAxMy45OTAyIDEzLjg5NDUgMTQuMTA2NGMzLjE2NDA2IDAuMDQyOTY4OCA2LjMzMDA4IDAuMDA1ODU5MzggOS40OTcwNyAwLjAwNTg1OTM4aDguOTk2MDl6TTMxOS45NjYgMjg4LjAxCmMtNi4xNjUwNCAwLjAwNzgxMjUgLTEyLjMzMiAtMC4wNzcxNDg0IC0xOC40OTUxIDAuMDI5Mjk2OWMtOC4yMzgyOCAwLjE0MjU3OCAtMTMuMzc5OSA1LjI1NTg2IC0xMy40NDM0IDEzLjUwMmMtMC4wOTY2Nzk3IDEyLjMzMDEgLTAuMDc2MTcxOSAyNC42NjExIDAuMDA3ODEyNSAzNi45OTEyYzAuMDU1NjY0MSA4LjE4ODQ4IDUuMjcwNTEgMTMuMzc5OSAxMy40ODM0IDEzLjQzMzYKYzEyLjMyOTEgMC4wODAwNzgxIDI0LjY2MTEgMC4xMDU0NjkgMzYuOTkwMiAwLjAwNTg1OTM4YzguMjI3NTQgLTAuMDY2NDA2MiAxMy4zNzMgLTUuMjI5NDkgMTMuNDQ1MyAtMTMuNDkyMmMwLjEwOTM3NSAtMTIuMzI5MSAwLjEyNTk3NyAtMjQuNjYxMSAwLjAwMjkyOTY5IC0zNi45OTEyYy0wLjA4MjAzMTIgLTguMjU5NzcgLTUuMjEzODcgLTEzLjMwMzcgLTEzLjQ5NjEgLTEzLjQ1NwpjLTYuMTYzMDkgLTAuMTEzMjgxIC0xMi4zMzAxIC0wLjAyMTQ4NDQgLTE4LjQ5NTEgLTAuMDIxNDg0NHpNMTkyLjUgMzUxLjk5OWM1Ljk5OTAyIDAuMDAwOTc2NTYyIDExLjk5OCAwLjA3NzE0ODQgMTcuOTk1MSAtMC4wMTg1NTQ3YzguMjA1MDggLTAuMTI4OTA2IDEzLjQwOTIgLTUuMjczNDQgMTMuNDc3NSAtMTMuNDg1NGMwLjEwMzUxNiAtMTIuMzMwMSAwLjEwMjUzOSAtMjQuNjYyMSAwIC0zNi45OTEyCmMtMC4wNjczODI4IC04LjE5NDM0IC01LjI5MDA0IC0xMy40MTAyIC0xMy40ODYzIC0xMy40Nzc1Yy0xMi4zMzAxIC0wLjEwMDU4NiAtMjQuNjYyMSAtMC4xMDM1MTYgLTM2Ljk5MjIgMGMtOC4yMDcwMyAwLjA3MTI4OTEgLTEzLjQwMjMgNS4yODEyNSAtMTMuNDY3OCAxMy40OTAyYy0wLjA5OTYwOTQgMTIuMzMxMSAtMC4wOTc2NTYyIDI0LjY2MjEgMCAzNi45OTIyCmMwLjA2NDQ1MzEgOC4yMDgwMSA1LjI2MzY3IDEzLjM1MDYgMTMuNDc4NSAxMy40NzE3YzYuMzMwMDggMC4wOTI3NzM0IDEyLjY2MzEgMC4wMTg1NTQ3IDE4Ljk5NTEgMC4wMTg1NTQ3ek0wIC0zOC4wMDF2MTJjNi4xNTIzNCAxOS42NTMzIDE5Ljg5NDUgMjcuNDUzMSAzOS45MjU4IDI2LjA4NjljNy44OTQ1MyAtMC41MzkwNjIgMTUuODU2NCAtMC4wODg4NjcyIDI0LjA3NjIgLTAuMDg4ODY3MnY1LjYwMDU5bDAuMDAwOTc2NTYyIDE5Mi45ODUKYzAuMDAwOTc2NTYyIDcyLjE2MTEgLTAuMDIxNDg0NCAxNDQuMzIyIDAuMDI1MzkwNiAyMTYuNDgzYzAuMDA5NzY1NjIgMTQuNTI5MyA4LjA5MTggMjYuMjQyMiAyMC45ODgzIDMwLjk2MjljMS42Nzc3MyAwLjYxMjMwNSAzLjMyMzI0IDEuMzExNTIgNC45ODM0IDEuOTcwN2gzMzJjMC4zODc2OTUgLTAuMjgzMjAzIDAuNzM1MzUyIC0wLjY5NDMzNiAxLjE2Nzk3IC0wLjgzMDA3OApjMTYuNzkzOSAtNS4yNjE3MiAyNC44MzY5IC0xNi4xOTE0IDI0LjgzNTkgLTMzLjU5OTZjLTAuMDExNzE4OCAtMTM2LjE1NyAtMC4wMDY4MzU5NCAtMjcyLjMxNSAwLjAwMzkwNjI1IC00MDguNDczYzAgLTEuNjA0NDkgMC4xNDU1MDggLTMuMjA2MDUgMC4yMDk5NjEgLTQuNTgzMDFjMC41ODIwMzEgLTAuMjY2NjAyIDAuNzI2NTYyIC0wLjM4OTY0OCAwLjg3MDExNyAtMC4zODk2NDgKYzEwLjQ5NzEgLTAuMDUwNzgxMiAyMC45OTUxIC0wLjAwNzgxMjUgMzEuNDkxMiAtMC4xNTQyOTdjMTMuNDY3OCAtMC4xOTA0MyAyNS4xMjUgLTguNzMwNDcgMjkuNjAxNiAtMjEuNDY0OGMwLjUzNzEwOSAtMS41MjYzNyAxLjIwOTk2IC0zLjAwNTg2IDEuODE5MzQgLTQuNTA2ODR2LTEyYy0wLjI4NTE1NiAtMC4zODU3NDIgLTAuNzAwMTk1IC0wLjczMDQ2OSAtMC44MzY5MTQgLTEuMTYzMDkKYy01LjMzMzk4IC0xNi44NjEzIC0xNi4wNzkxIC0yNC44MzAxIC0zMy41OTE4IC0yNC44MzJjLTYxLjMxMTUgLTAuMDA2ODM1OTQgLTEyMi42MjQgLTAuMDAyOTI5NjkgLTE4My45MzcgLTAuMDAyOTI5NjloLTUuNjMyODF2Ni4wNDg4M2MtMC4wMDI5Mjk2OSAyNS4xNTgyIDAuMDAyOTI5NjkgNTAuMzE1NCAtMC4wMTQ2NDg0IDc1LjQ3MzZjLTAuMDA1ODU5MzggOS4zODY3MiAtNS4wNjI1IDE0LjQzNzUgLTE0LjUxMDcgMTQuNDY4OApjLTExLjMyODEgMC4wMzYxMzI4IC0yMi42NTcyIDAuMDAzOTA2MjUgLTMzLjk4NzMgMC4wMDg3ODkwNmMtMTAuNzUyIDAuMDAzOTA2MjUgLTE1LjQ5MzIgLTQuNzM5MjYgLTE1LjQ5MTIgLTE1LjQ5MzJjMC4wMDM5MDYyNSAtMjQuOTkwMiAwIC00OS45ODI0IDAgLTc0Ljk3MzZ2LTUuMjEwOTRjLTEuMTc5NjkgLTAuMTY3OTY5IC0xLjY2NDA2IC0wLjI5Njg3NSAtMi4xNDc0NiAtMC4yOTY4NzUKYy02My42NDQ1IC0wLjAwODc4OTA2IC0xMjcuMjg5IC0wLjA3NTE5NTMgLTE5MC45MzQgMC4wNDQ5MjE5Yy0xMi45NjI5IDAuMDI0NDE0MSAtMjQuNDY4OCA4LjY5ODI0IC0yOC45NTggMjAuOTM3NWMtMC42MTYyMTEgMS42NzY3NiAtMS4zMDQ2OSAzLjMyNzE1IC0xLjk1ODk4IDQuOTkwMjN6IiAvPgogICAgPGdseXBoIGdseXBoLW5hbWU9InVuaUYxMUIiIHVuaWNvZGU9IiYjeGYxMWI7IiAKZD0iTTEyOCA0OHY2NGgyNTZ2LTY0di05NmgtMjU2djk2ek0xNjAgODB2LTMyaDE5MnYzMmgtMTkyek0xNjAgMTZ2LTMyaDE5MnYzMmgtMTkyek0zODQgMzM2di02NGgtMjU2djY0djk2aDI1NnYtOTZ6TTQ0OCAzMzZjMzIgMCA2NCAtMzIgNjQgLTY0di0xNjBjMCAtMzIgLTMyIC02NCAtNjQgLTY0aC0zMnY2NHYzMmgtMzIwdi0zMnYtNjRoLTMyYy0zMiAwIC02NCAzMiAtNjQgNjR2MTYwYzAgMzIgMzIgNjQgNjQgNjRoMzJ2LTY0di0zMmgzMjB2MzIKdjY0aDMyeiIgLz4KICAgIDxnbHlwaCBnbHlwaC1uYW1lPSJ1bmlGMTA1IiB1bmljb2RlPSImI3hmMTA1OyIgCmQ9Ik0zMTguMzc1IDM2Mi41NTRsMTA0LjE1MiAtMTA0LjY2MmwtMjYzLjYzOSAtMjY0LjkzbC0xMDQuMDkzIDEwNC42NjJ6TTUwMS41NTkgMzg3Ljc5NWMxMy45MjI5IC0xMy45OTAyIDEzLjkyMjkgLTM2LjU1MjcgMCAtNTAuNTQ0OWwtNTEuODk3NSAtNTIuMTUyM2wtMTA0LjE1MyAxMDQuNjYzbDQ0LjQ5MzIgNDQuNzFjMTguMDExNyAxOC4wMzgxIDQ3LjE1OTIgMTguMDM4MSA2NS4xMDk0IDB6TTAuMjkwMDM5IC00OS40ODYzCmwyNi4zMDU3IDExOC43NzFsMTA0LjA5MyAtMTA0LjY2MWwtMTE2LjA2MSAtMjguMjc4M2MtOC41MzEyNSAtMi4wODM5OCAtMTYuMjMzNCA1LjU5NjY4IC0xNC4zMzc5IDE0LjE2ODl6IiAvPgogICAgPGdseXBoIGdseXBoLW5hbWU9InVuaUYxMDciIHVuaWNvZGU9IiYjeGYxMDc7IiAKZD0iTTE5Ni45MjYgMjExLjY5MmMtNjUuMjUzOSAwIC0xMTguMTU2IDUyLjg5ODQgLTExOC4xNTYgMTE4LjE1M2MwIDY1LjI1NTkgMTcuMzczIDExOC4xNTQgMTE4LjE1NiAxMTguMTU0czExOC4xNTIgLTUyLjg5ODQgMTE4LjE1MiAtMTE4LjE1NGMwIC02NS4yNTQ5IC01Mi45MDIzIC0xMTguMTUzIC0xMTguMTUyIC0xMTguMTUzek0zOTMuNjI1IDYuNzg5MDYKYzAuMTIzMDQ3IC03LjIyNDYxIDAuMTg3NSAtOC40NzQ2MSAwLjIyMjY1NiAtNy44MzAwOGMtMC4wMDU4NTkzOCAtMS45MTUwNCAtMC4wMDc4MTI1IC00LjczMDQ3IC0wLjAwNzgxMjUgLTguNzc4MzJjMCAwIC0yOC45OTIyIC01NC4xODA3IC0xOTYuOTEyIC01NC4xODA3Yy0xNjcuOTIyIDAgLTE5Ni45MSA1NC4xODA3IC0xOTYuOTEgNTQuMTgwN2MwIDYuMzAwNzggLTAuMDA5NzY1NjIgOS45MTQwNiAtMC4wMTc1NzgxIDExLjg2ODIKYzAuMDM1MTU2MiAtMS4wNDI5NyAwLjExOTE0MSAtMC41NzEyODkgMC4yOTg4MjggOC40MTAxNmMyLjIyMDcgMTA5Ljk4NyAxOS4xMDE2IDE0MS42OTcgMTM5LjUzNSAxNjEuODQ5YzAgMCAxNy4xNDI2IC0yMC4yNDkgNTcuMDkzOCAtMjAuMjQ5czU3LjA5MTggMjAuMjQ5IDU3LjA5MTggMjAuMjQ5YzEyMS43NjYgLTIwLjM3NCAxMzcuNjc2IC01Mi41NjA1IDEzOS42MDUgLTE2NS41MTl6IiAvPgogICAgPGdseXBoIGdseXBoLW5hbWU9InVuaUYxMjgiIHVuaWNvZGU9IiYjeGYxMjg7IiAKZD0iTTEzOC4xNTEgMTk2LjQ5NmMtMTguNzEgLTguNjI0MDIgLTMzLjYwNjQgLTE5LjQ4MDUgLTQ0LjY4NjUgLTMyLjU2MTVsLTkxLjgyOTEgLTEwNy45MTNsLTEuMzYxMzMgLTEuNjI1OThjMCAwLjcyNDYwOSAtMC4wNDY4NzUgMS44NjIzIC0wLjEzNjcxOSAzLjQwMzMyYy0wLjA5MDgyMDMgMS41NDY4OCAtMC4xMzc2OTUgMi42ODM1OSAtMC4xMzc2OTUgMy40MTMwOXYyNjEuNTg3CmMwIDE2LjcxMTkgNS45OTMxNiAzMS4wNjM1IDE3Ljk4MjQgNDMuMDUwOGMxMS45OTAyIDExLjk5MDIgMjYuMzQxOCAxNy45ODczIDQzLjA1NDcgMTcuOTg3M2g4Ny4xOTQzYzE2LjcxMTkgMCAzMS4wNjM1IC01Ljk5ODA1IDQzLjA1MzcgLTE3Ljk4NzNjMTEuOTg5MyAtMTEuOTg2MyAxNy45ODM0IC0yNi4zMzc5IDE3Ljk4MzQgLTQzLjA0OTh2LTguNzE5NzNoMTQ4LjIzNGMxNi43MTU4IDAgMzEuMDYxNSAtNS45OTQxNCA0My4wNTM3IC0xNy45ODI0CmMxMS45OTMyIC0xMS45OTAyIDE3Ljk5MDIgLTI2LjM0ODYgMTcuOTkwMiAtNDMuMDYxNXYtNDMuNTk5NmgtMjI2LjcxN2MtMTcuMDczMiAwIC0zNC45Njg4IC00LjMxNDQ1IC01My42Nzg3IC0xMi45NDA0ek01MDQuNzgyIDE3MS4wMTljNC44MTczOCAtMi4zNjIzIDcuMjIxNjggLTYuMjY1NjIgNy4yMTc3NyAtMTEuNzExOWMwIC01LjY0MDYyIC0yLjgxMjUgLTExLjYzMDkgLTguNDQyMzggLTE3Ljk5MDJsLTkxLjU1NjYgLTEwNy45MDMKYy03LjgxMjUgLTkuMjY5NTMgLTE4Ljc1MzkgLTE3LjEyNyAtMzIuODMxMSAtMjMuNTc1MmMtMTQuMDgwMSAtNi40NDYyOSAtMjcuMTA3NCAtOS42NzM4MyAtMzkuMDk0NyAtOS42NzM4M2gtMjk2LjQ3N2MtNi4xNzc3MyAwIC0xMS42NzE5IDEuMTgxNjQgLTE2LjQ4NTQgMy41NDY4OGMtNC44MTI1IDIuMzU3NDIgLTcuMjIwNyA2LjI2NDY1IC03LjIyMDcgMTEuNzEyOWMwIDUuNjM0NzcgMi44MTY0MSAxMS42MzA5IDguNDQ5MjIgMTcuOTg5MwpsOTEuNTU1NyAxMDcuOTAzYzcuODA5NTcgOS4yNzI0NiAxOC43NTQ5IDE3LjExODIgMzIuODMzIDIzLjU3NTJjMTQuMDc2MiA2LjQ1MTE3IDI3LjExMDQgOS42Nzc3MyAzOS4wOTk2IDkuNjc3NzNoMjk2LjQ2OGM2LjE3OTY5IDAgMTEuNjcyOSAtMS4xODU1NSAxNi40ODQ0IC0zLjU1MDc4eiIgLz4KICAgIDxnbHlwaCBnbHlwaC1uYW1lPSJ1bmlGMTJDIiB1bmljb2RlPSImI3hmMTJjOyIgCmQ9Ik0wIDQyNGMwIDEzLjI0OCAxNC4zMzY5IDI0IDMyIDI0aDMydi0zODRoLTMyYy0xNy42NjMxIDAgLTMyIDEwLjc1MiAtMzIgMjR2MzM2ek00NzcuMDkxIDQ0OGMxOS4yNjk1IDAgMzQuOTA5MiAtMTAuNzUyIDM0LjkwOTIgLTI0di0zMzZjMCAtMTMuMjQ4IC0xNS42Mzk2IC0yNCAtMzQuOTA5MiAtMjRoLTM0OS4wOTF2Mzg0aDM0OS4wOTF6TTMyOS42IDM1NS41MDRjMzAuOTUzMSAwIDU2LjAxODYgLTIzLjQ0MDQgNTYuMDE4NiAtNTIuMzA2NgpjMCAtMjguOTAxNCAtMjUuMTM4NyAtNTIuMzQxOCAtNTYuMDE4NiAtNTIuMzQxOGMtMzAuOTUyMSAwIC01Ni4wNTU3IDIzLjQ0MDQgLTU2LjA1NTcgNTIuMzQxOHMyNS4xMDM1IDUyLjMwNjYgNTYuMDU1NyA1Mi4zMDY2ek0yMzkuMDc1IDE1OS41NDJjMCAtNTMuMDE1NiAxODMuMzQ3IC01My4wMTU2IDE4My4zODIgMGMwIDUyLjk1NyAtNDEuMDk2NyA5NS45NzA3IC05MS42NzE5IDk1Ljk3MDcKYy01MC42NDk0IDAgLTkxLjcxIC00Mi45ODU0IC05MS43MSAtOTUuOTcwN3oiIC8+CiAgICA8Z2x5cGggZ2x5cGgtbmFtZT0idW5pRjEwQiIgdW5pY29kZT0iJiN4ZjEwYjsiIApkPSJNNTA3LjM1MyAyMDIuNzU1YzIuOTY1ODIgLTIuNzkxMDIgNC42NDU1MSAtNi42ODI2MiA0LjY0NTUxIC0xMC43NTQ5cy0xLjY4MDY2IC03Ljk2NDg0IC00LjY0NzQ2IC0xMC43NTQ5bC04My42OTE0IC03OC43Njk1Yy00LjI4OTA2IC00LjAzOTA2IC0xMC41NzAzIC01LjEzOTY1IC0xNS45ODA1IC0yLjgwMjczYy01LjQwOTE4IDIuMzM2OTEgLTguOTExMTMgNy42NjYwMiAtOC45MTExMyAxMy41NTg2djM0LjQ2MTloLTk4LjQ2MTkKdi05OC40NjE5aDM0LjQ2MTljNS44OTM1NSAwIDExLjIyMTcgLTMuNTAyOTMgMTMuNTU4NiAtOC45MTExM2MyLjMzNjkxIC01LjQwOTE4IDEuMjM2MzMgLTExLjY5MDQgLTIuODAyNzMgLTE1Ljk4MDVsLTc4Ljc2OTUgLTgzLjY5MTRjLTIuNzkxMDIgLTIuOTY1ODIgLTYuNjgyNjIgLTQuNjQ3NDYgLTEwLjc1NDkgLTQuNjQ3NDZzLTcuOTY0ODQgMS42ODA2NiAtMTAuNzU0OSA0LjY0NzQ2bC03OC43Njk1IDgzLjY5MTQKYy00LjAzOTA2IDQuMjkwMDQgLTUuMTM5NjUgMTAuNTcxMyAtMi44MDI3MyAxNS45ODA1YzIuMzM2OTEgNS40MDgyIDcuNjY2MDIgOC45MTExMyAxMy41NTg2IDguOTExMTNoMzQuNDYxOXY5OC40NjE5aC05OC40NjE5di0zNC40NjE5YzAgLTUuODkzNTUgLTMuNTAyOTMgLTExLjIyMTcgLTguOTExMTMgLTEzLjU1ODZjLTUuNDEwMTYgLTIuMzM3ODkgLTExLjY5MDQgLTEuMjM2MzMgLTE1Ljk4MDUgMi44MDI3M2wtODMuNjkxNCA3OC43Njk1CmMtMi45NjU4MiAyLjc5MTAyIC00LjY0NzQ2IDYuNjgyNjIgLTQuNjQ3NDYgMTAuNzU0OXMxLjY4MTY0IDcuOTYzODcgNC42NDc0NiAxMC43NTQ5bDgzLjY5MjQgNzguNzY5NWM0LjI5MDA0IDQuMDM4MDkgMTAuNTcwMyA1LjEzOTY1IDE1Ljk4MDUgMi44MDI3M2M1LjQwOTE4IC0yLjMzNjkxIDguOTExMTMgLTcuNjY2MDIgOC45MTExMyAtMTMuNTU4NnYtMzQuNDYxOWg5OC40NjE5djk4LjQ2MTloLTM0LjQ2MTkKYy01Ljg5MzU1IDAgLTExLjIyMTcgMy41MDI5MyAtMTMuNTU4NiA4LjkxMTEzYy0yLjMzNjkxIDUuNDA5MTggLTEuMjM2MzMgMTEuNjkwNCAyLjgwMjczIDE1Ljk4MDVsNzguNzY5NSA4My42OTI0YzIuNzkxMDIgMi45NjU4MiA2LjY4MjYyIDQuNjQ3NDYgMTAuNzU0OSA0LjY0NzQ2czcuOTYzODcgLTEuNjgxNjQgMTAuNzU0OSAtNC42NDc0Nmw3OC43NzA1IC04My42OTI0CmM0LjAzOTA2IC00LjI5MDA0IDUuMTM5NjUgLTEwLjU3MTMgMi44MDI3MyAtMTUuOTgwNWMtMi4zMzY5MSAtNS40MDgyIC03LjY2NjAyIC04LjkxMTEzIC0xMy41NTg2IC04LjkxMTEzaC0zNC40NjE5di05OC40NjE5aDk4LjQ2MTl2MzQuNDYxOWMwIDUuODkzNTUgMy41MDI5MyAxMS4yMjE3IDguOTExMTMgMTMuNTU4NmM1LjQxMDE2IDIuMzM3ODkgMTEuNjkxNCAxLjIzNTM1IDE1Ljk4MDUgLTIuODAyNzN6IiAvPgogICAgPGdseXBoIGdseXBoLW5hbWU9InVuaUYxMkEiIHVuaWNvZGU9IiYjeGYxMmE7IiAKZD0iTTE0NC4wOTEgNjcuNjU3Mmw2Ny4yOTMgOTAuNjk2M2w2My42MzM4IC05MC42OTYzaC0xMC45NzE3aC0xMTkuOTU1ek0zMzQuOTAyIDMyMy42NTdjLTMuNjEwMzUgMC40Mjg3MTEgLTYuMTg5NDUgMy43MDMxMiAtNS43NTc4MSA3LjMxNTQzdjk1LjA4NWwxMDMuMTMxIC0xMDIuNGgtOTUuODE3NGMtMC41MTY2MDIgLTAuMDYxNTIzNCAtMS4wMzkwNiAtMC4wNjE1MjM0IC0xLjU1NTY2IDB6TTI5MC4zNzcgMjA5LjU1NQpjLTguNDgyNDIgMCAtMTUuMzU5NCA2Ljg3Njk1IC0xNS4zNTk0IDE1LjM1OTR2MGMwIDguNDgyNDIgNi44NzY5NSAxNS4zNTk0IDE1LjM1OTQgMTUuMzU5NHMxNS4zNTk0IC02Ljg3Njk1IDE1LjM1OTQgLTE1LjM1OTRzLTYuODc2OTUgLTE1LjM1OTQgLTE1LjM1OTQgLTE1LjM1OTR6TTMzNi40NTggMjk0LjRoMTE3LjAyOXYtMzE0LjUxNWMwIC0yNC4yMzgzIC0xOS42NDk0IC00My44ODU3IC00My44ODU3IC00My44ODU3aC0zMDcuMjAxCmMtMjQuMjM4MyAwIC00My44ODU3IDE5LjY0OTQgLTQzLjg4NTcgNDMuODg1N3Y0MjQuMjI5YzAgMjQuMjM2MyAxOS42NDc1IDQzLjg4NTcgNDMuODg1NyA0My44ODU3aDE5Ny40ODd2LTExNy4wMjljMCAtMjAuMTk3MyAxNi4zNzIxIC0zNi41NzAzIDM2LjU3MDMgLTM2LjU3MDN6TTI5MC4zNzcgMjY5LjUzYy0yNC42NDE2IDAgLTQ0LjYxNjIgLTE5Ljk3NDYgLTQ0LjYxNjIgLTQ0LjYxNjJzMTkuOTc0NiAtNDQuNjE2MiA0NC42MTYyIC00NC42MTYyCnM0NC42MTcyIDE5Ljk3NDYgNDQuNjE3MiA0NC42MTYycy0xOS45NzU2IDQ0LjYxNjIgLTQ0LjYxNzIgNDQuNjE2MnpNMzk1LjcwMyA0Ny45MDgyYzIuNDM2NTIgNS4yMzgyOCAxLjg3NzkzIDExLjM3ODkgLTEuNDYyODkgMTYuMDg5OGwtNTUuNTg4OSA3NC42MDU1Yy0yLjQ1MjE1IDMuNzYyNyAtNi40OTcwNyA2LjE4OTQ1IC0xMC45NzE3IDYuNTgzMDFjLTQuNTAwOTggMC4yOTM5NDUgLTguODYzMjggLTEuNjE1MjMgLTExLjcwMjEgLTUuMTE5MTQKbC0yOS45ODgzIC0zNC4zNzc5bC02Mi45MDMzIDg5LjIzNTRjLTIuNTg2OTEgMy44ODg2NyAtNy4wNDEwMiA2LjExNTIzIC0xMS43MDIxIDUuODUxNTZjLTQuNTc4MTIgLTAuMTA5Mzc1IC04Ljg2OTE0IC0yLjI1MzkxIC0xMS43MDMxIC01Ljg1MTU2bC05Ni41NDc5IC0xMzAuMTk0Yy0zLjMzMTA1IC00Ljk4ODI4IC0zLjg4MjgxIC0xMS4zMzMgLTEuNDYyODkgLTE2LjgyMjMKYzIuMzQ4NjMgLTUuMzE0NDUgNy4zODE4NCAtOC45NDkyMiAxMy4xNjUgLTkuNTA5NzdoMjY3LjcwM2M1Ljc4MzIgMC41NjA1NDcgMTAuODE0NSA0LjE5NTMxIDEzLjE2NSA5LjUwOTc3ek0zMDMuNTQzIDc5LjM2MDRsMjIuNjczOCAyNS41OTg2bDI3LjA2MzUgLTM3LjMwMThoLTQyLjQyMjl6IiAvPgogICAgPGdseXBoIGdseXBoLW5hbWU9InVuaUYxMjYiIHVuaWNvZGU9IiYjeGYxMjY7IiAKZD0iTTE4Ni41NDMgMTY1LjUzMnYwdi00MC41NDg4YzAgLTYuNDIxODggLTUuMjAyMTUgLTExLjYyMDEgLTExLjYyMTEgLTExLjYyMDFoLTQwLjU1NDdjLTYuNDExMTMgMCAtMTEuNjE0MyA1LjE5ODI0IC0xMS42MTQzIDExLjYyMDF2NDAuNTQ4OGMwIDYuNDE0MDYgNS4yMDMxMiAxMS42MDk0IDExLjYxNDMgMTEuNjA5NGg0MC41NTQ3YzYuNDE4OTUgMCAxMS42MjExIC01LjE5NTMxIDExLjYyMTEgLTExLjYwOTR6TTI4Ny44OTMgMTY1LjUzMgp2LTQwLjU0ODhjMCAtNi40MjE4OCAtNS4yMDMxMiAtMTEuNjIwMSAtMTEuNjA5NCAtMTEuNjIwMWgtNDAuNTU5NmMtNi40MTIxMSAwIC0xMS42MTUyIDUuMTk4MjQgLTExLjYxNTIgMTEuNjIwMXY0MC41NDg4YzAgNi40MTQwNiA1LjIwMzEyIDExLjYwOTQgMTEuNjE1MiAxMS42MDk0aDQwLjU1OTZjNi40MDYyNSAwIDExLjYwOTQgLTUuMTk1MzEgMTEuNjA5NCAtMTEuNjA5NHpNMzg5LjI0NyAxNjUuNTMydi00MC41NDg4CmMwIC02LjQyMTg4IC01LjIwMzEyIC0xMS42MjAxIC0xMS42MTQzIC0xMS42MjAxaC00MC41NTQ3Yy02LjQxODk1IDAgLTExLjYyMTEgNS4xOTgyNCAtMTEuNjIxMSAxMS42MjAxdjQwLjU0ODhjMCA2LjQxNDA2IDUuMjAyMTUgMTEuNjA5NCAxMS42MjExIDExLjYwOTRoNDAuNTU0N2M2LjQxMTEzIDAgMTEuNjE0MyAtNS4xOTUzMSAxMS42MTQzIC0xMS42MDk0ek0xODYuNTQzIDY0LjE3MTl2MHYtNDAuNTQxCmMwIC02LjQxNjk5IC01LjIwMjE1IC0xMS42MTUyIC0xMS42MjExIC0xMS42MTUyaC00MC41NTQ3Yy02LjQxMTEzIDAgLTExLjYxNDMgNS4xOTcyNyAtMTEuNjE0MyAxMS42MTUydjQwLjU0MWMwIDYuNDI0OCA1LjIwMzEyIDExLjYxNjIgMTEuNjE0MyAxMS42MTYyaDQwLjU1NDdjNi40MTg5NSAwIDExLjYyMTEgLTUuMTkxNDEgMTEuNjIxMSAtMTEuNjE2MnpNMjg3Ljg5MyA2NC4xNzE5di00MC41NDEKYzAgLTYuNDE2OTkgLTUuMjAzMTIgLTExLjYxNTIgLTExLjYwOTQgLTExLjYxNTJoLTQwLjU1OTZjLTYuNDEyMTEgMCAtMTEuNjE1MiA1LjE5NzI3IC0xMS42MTUyIDExLjYxNTJ2NDAuNTQxYzAgNi40MjQ4IDUuMjAzMTIgMTEuNjE2MiAxMS42MTUyIDExLjYxNjJoNDAuNTU5NmM2LjQwNjI1IDAgMTEuNjA5NCAtNS4xOTE0MSAxMS42MDk0IC0xMS42MTYyek0zODkuMjQ3IDY0LjE3MTl2MHYtNDAuNTQxCmMwIC02LjQxNjk5IC01LjIwMzEyIC0xMS42MTUyIC0xMS42MDk0IC0xMS42MTUyaC00MC41NTk2Yy02LjQxODk1IDAgLTExLjYyMTEgNS4xOTcyNyAtMTEuNjIxMSAxMS42MTUydjQwLjU0MWMwIDYuNDI0OCA1LjIwMjE1IDExLjYxNjIgMTEuNjIxMSAxMS42MTYyaDQwLjU1OTZjNi40MDYyNSAwIDExLjYwOTQgLTUuMTkxNDEgMTEuNjA5NCAtMTEuNjE2MnpNNDYxLjc2MiAzOTAuOTkKYzI0LjQ2MjkgLTAuNzM3MzA1IDQ0LjU2MjUgLTIwLjk3ODUgNDQuNTYyNSAtNDUuODU4NHYtMzYyLjk2OGMwIC0yNS4zMDQ3IC0yMC41ODIgLTQ2LjE2NSAtNDUuODkyNiAtNDYuMTY1aC00MDguODYzYy0yNS4zNDc3IDAgLTQ1Ljg5MjYgMjAuODE3NCAtNDUuODkyNiA0Ni4xNjV2MzYyLjk2OGMwIDI0Ljg3OTkgMjAuMTAwNiA0NS4xMjExIDQ0LjU2MzUgNDUuODU4NHYtNjEuOTM0NgpjMCAtMjcuOTk0MSAyMi43MDkgLTUwLjU1MjcgNTAuNjk3MyAtNTAuNTUyN2gzMS45ODkzYzI3Ljk4NzMgMCA1MC45OTcxIDIyLjU1ODYgNTAuOTk3MSA1MC41NTI3djYyLjE1NzJoMTQ0LjE1NHYtNjIuMTU3MmMwIC0yNy45OTQxIDIzLjAxMDcgLTUwLjU1MjcgNTEuMDAyOSAtNTAuNTUyN2gzMS45ODI0YzI3Ljk4OTMgMCA1MC42OTkyIDIyLjU1ODYgNTAuNjk5MiA1MC41NTI3djYxLjkzNDZ6TTQ0Ni45MDcgNC43NjY2djAKbC0wLjAwMDk3NjU2MiAxODcuNDg2YzAgMTAuOTU4IC04Ljg4MTg0IDE5Ljg0NDcgLTE5LjgzOTggMTkuODQ0N2gtMzQzLjAwN2MtMTAuOTYgMCAtMTkuODQwOCAtOC44ODY3MiAtMTkuODQwOCAtMTkuODQ0N3YtMTg3LjQ4NmMwIC0xMC45NTQxIDguODgxODQgLTE5Ljg0MDggMTkuODQwOCAtMTkuODQwOGgzNDMuMDA4YzEwLjk1OCAwIDE5LjgzOTggOC44ODY3MiAxOS44Mzk4IDE5Ljg0MDh6TTEwMC44MjQgMzExLjcwNwpjLTkuNjAzNTIgMCAtMTcuMzg3NyA3Ljc3MzQ0IC0xNy4zODc3IDE3LjM3NnYxMDEuNTM0YzAgOS42MDM1MiA3Ljc4NDE4IDE3LjM4MjggMTcuMzg3NyAxNy4zODI4aDMxLjYzNTdjOS42MDE1NiAwIDE3LjM4NzcgLTcuNzc5MyAxNy4zODc3IC0xNy4zODI4di0xMDEuNTM0YzAgLTkuNjAyNTQgLTcuNzg2MTMgLTE3LjM3NiAtMTcuMzg3NyAtMTcuMzc2aC0zMS42MzU3ek0zNzguNjczIDMxMS43MDcKYy05LjYwMTU2IDAgLTE3LjM4NjcgNy43NzM0NCAtMTcuMzg2NyAxNy4zNzZ2MTAxLjUzNGMwIDkuNjAzNTIgNy43ODUxNiAxNy4zODI4IDE3LjM4NjcgMTcuMzgyOGgzMS42MzU3YzkuNTk2NjggMCAxNy4zODI4IC03Ljc3OTMgMTcuMzgxOCAtMTcuMzgyOHYtMTAxLjUzNGMwIC05LjYwMjU0IC03Ljc4NjEzIC0xNy4zNzYgLTE3LjM4MTggLTE3LjM3NmgtMzEuNjM1N3oiIC8+CiAgICA8Z2x5cGggZ2x5cGgtbmFtZT0idW5pRjExNyIgdW5pY29kZT0iJiN4ZjExNzsiIApkPSJNNDcyLjIxIDIxNi4wMmwtOTYuMTE0MyAtOTYuMDgyYy01My4wNDY5IC01My4wNzkxIC0xMzkuMTA0IC01My4wNzkxIC0xOTIuMTYgMGMtOC4zNTY0NSA4LjMzMzAxIC0xNC44OTQ1IDE3LjcyMzYgLTIwLjYzNjcgMjcuNDA3Mmw0NC42NTkyIDQ0LjY1NTNjMi4xMjMwNSAyLjE0MDYyIDQuNzQ0MTQgMy4zNjcxOSA3LjI0OTAyIDQuODEyNWMzLjA4Nzg5IC0xMC41NDg4IDguNDc3NTQgLTIwLjUzNDIgMTYuNzczNCAtMjguODMxMQpjMjYuNDkyMiAtMjYuNTEzNyA2OS42MDA2IC0yNi40NzY2IDk2LjA3NzEgMGw5Ni4wODQgOTYuMDgwMWMyNi41MTE3IDI2LjUxMDcgMjYuNTExNyA2OS42MDg0IDAgOTYuMDk4NmMtMjYuNDc4NSAyNi40OTAyIC02OS41NzUyIDI2LjQ5MDIgLTk2LjA4NCAwbC0zNC4xNjggLTM0LjIwNDFjLTI3LjcyNjYgMTAuNzk1OSAtNTcuNTk4NiAxMy42OTgyIC04Ni42Nzg3IDkuNDM3NWw3Mi44MDc2IDcyLjgwNTcKYzUzLjA3OTEgNTMuMDY4NCAxMzkuMTEyIDUzLjA2ODQgMTkyLjE5MSAwYzUzLjA1MjcgLTUzLjA2NjQgNTMuMDUyNyAtMTM5LjExNCAwIC0xOTIuMTh6TTIxOC4xMjcgNTguMDI3M2MyNy43MDMxIC0xMC43NzY0IDU3LjU4MyAtMTMuNzAzMSA4Ni42Nzg3IC05LjQxNjk5bC03Mi44MjYyIC03Mi44MjEzYy01My4wNjg0IC01My4wNTE4IC0xMzkuMTEyIC01My4wNTE4IC0xOTIuMTgxIDAKYy01My4wNjI1IDUzLjA4MDEgLTUzLjA2MjUgMTM5LjEwMyAwIDE5Mi4xODdsOTYuMDgxMSA5Ni4wODRjNTMuMDY4NCA1My4wNjg0IDEzOS4xMzMgNTMuMDY4NCAxOTIuMTc1IDBjOC4zNjEzMyAtOC4zNDI3NyAxNC45MzI2IC0xNy42OTkyIDIwLjYzNjcgLTI3LjQyMDlsLTQ0LjY1NTMgLTQ0LjYzNzdjLTIuMTIyMDcgLTIuMTI1IC00LjcxMDk0IC0zLjMxNDQ1IC03LjIzMDQ3IC00Ljc3OTMKYy0zLjExNjIxIDEwLjUzNTIgLTguNTExNzIgMjAuNTE5NSAtMTYuNzg4MSAyOC43OTc5Yy0yNi40OTAyIDI2LjUwNzggLTY5LjU3MTMgMjYuNTA3OCAtOTYuMDgzIDBsLTk2LjA5NDcgLTk2LjA4MmMtMjYuNDkyMiAtMjYuNTAyOSAtMjYuNDkyMiAtNjkuNjAwNiAwIC05Ni4xMTQzYzI2LjQ5NDEgLTI2LjQ3NjYgNjkuNjA0NSAtMjYuNDc2NiA5Ni4wOTQ3IDB6IiAvPgogICAgPGdseXBoIGdseXBoLW5hbWU9InVuaUYxMUUiIHVuaWNvZGU9IiYjeGYxMWU7IiAKZD0iTTMyMCAzODRjMCAtMzUuMzQ4NiAtMjguNjUxNCAtNjQgLTY0IC02NHMtNjQgMjguNjUxNCAtNjQgNjRzMjguNjUxNCA2NCA2NCA2NHM2NCAtMjguNjUxNCA2NCAtNjR6TTMyMCAzODR6TTMyMCAtMGMwIC0zNS4zNDg2IC0yOC42NTE0IC02NCAtNjQgLTY0cy02NCAyOC42NTE0IC02NCA2NHMyOC42NTE0IDY0IDY0IDY0czY0IC0yOC42NTE0IDY0IC02NHpNMzIwIC0wek0zMjAgMTkyYzAgLTM1LjM0ODYgLTI4LjY1MTQgLTY0IC02NCAtNjQKcy02NCAyOC42NTE0IC02NCA2NHMyOC42NTE0IDY0IDY0IDY0czY0IC0yOC42NTE0IDY0IC02NHpNMzIwIDE5MnpNNTEyIDM4NGMwIC0zNS4zNDg2IC0yOC42NTE0IC02NCAtNjQgLTY0cy02NCAyOC42NTE0IC02NCA2NHMyOC42NTE0IDY0IDY0IDY0czY0IC0yOC42NTE0IDY0IC02NHpNNTEyIDM4NHpNNTEyIC0wYzAgLTM1LjM0ODYgLTI4LjY1MTQgLTY0IC02NCAtNjRzLTY0IDI4LjY1MTQgLTY0IDY0czI4LjY1MTQgNjQgNjQgNjQKczY0IC0yOC42NTE0IDY0IC02NHpNNTEyIC0wek01MTIgMTkyYzAgLTM1LjM0ODYgLTI4LjY1MTQgLTY0IC02NCAtNjRzLTY0IDI4LjY1MTQgLTY0IDY0czI4LjY1MTQgNjQgNjQgNjRzNjQgLTI4LjY1MTQgNjQgLTY0ek01MTIgMTkyek0xMjggMzg0YzAgLTM1LjM0ODYgLTI4LjY1MTQgLTY0IC02NCAtNjRzLTY0IDI4LjY1MTQgLTY0IDY0czI4LjY1MTQgNjQgNjQgNjRzNjQgLTI4LjY1MTQgNjQgLTY0ek0xMjggMzg0ek0xMjggLTAKYzAgLTM1LjM0ODYgLTI4LjY1MTQgLTY0IC02NCAtNjRzLTY0IDI4LjY1MTQgLTY0IDY0czI4LjY1MTQgNjQgNjQgNjRzNjQgLTI4LjY1MTQgNjQgLTY0ek0xMjggLTB6TTEyOCAxOTJjMCAtMzUuMzQ4NiAtMjguNjUxNCAtNjQgLTY0IC02NHMtNjQgMjguNjUxNCAtNjQgNjRzMjguNjUxNCA2NCA2NCA2NHM2NCAtMjguNjUxNCA2NCAtNjR6TTEyOCAxOTJ6IiAvPgogICAgPGdseXBoIGdseXBoLW5hbWU9InVuaUYxMDMiIHVuaWNvZGU9IiYjeGYxMDM7IiAKZD0iTTI4MS4zNDYgMzQ0LjQyN2wyMjAuMTQ2IC0yMjAuMTUyYzE0LjAxMDcgLTE0LjAwMzkgMTQuMDEwNyAtMzYuNzEgMCAtNTAuNzA3Yy0xMy45OTkgLTEzLjk5OSAtMzYuNzAzMSAtMTMuOTk5IC01MC43MDEyIDBsLTE5NC43OTUgMTk0LjhsLTE5NC43ODcgLTE5NC43OTRjLTE0LjAwNDkgLTEzLjk5OSAtMzYuNzA3IC0xMy45OTkgLTUwLjcwNjEgMGMtMTQuMDAzOSAxMy45OTggLTE0LjAwMzkgMzYuNzAzMSAwIDUwLjcwNwpsMjIwLjE0OCAyMjAuMTUyYzcuMDAyOTMgNi45OTkwMiAxNi4xNzA5IDEwLjQ5NDEgMjUuMzQzOCAxMC40OTQxYzkuMTc3NzMgMCAxOC4zNTI1IC0zLjUwMTk1IDI1LjM1MTYgLTEwLjV6IiAvPgogICAgPGdseXBoIGdseXBoLW5hbWU9InVuaUYxMTQiIHVuaWNvZGU9IiYjeGYxMTQ7IiAKZD0iTTUwOS4zOTQgNDI3LjAzMWMyLjI3NzM0IC0xLjk5ODA1IDMuMTY0MDYgLTUuMTUzMzIgMi4yNTQ4OCAtOC4wNDU5bC0xMzAuMDcxIC00MTQuMjY0Yy0wLjY2NDA2MiAtMi4xMTYyMSAtMi4yMTM4NyAtMy44Mzg4NyAtNC4yNDgwNSAtNC43MjU1OWMtMi4wMzQxOCAtMC44ODQ3NjYgLTQuMzUxNTYgLTAuODQ0NzI3IC02LjM1MzUyIDAuMTExMzI4bC05Mi4yMjg1IDQ0LjA2NzRsLTc0LjYwNjQgLTg2LjQ1MzEKYy0xLjQ4MDQ3IC0xLjcxNDg0IC0zLjYwODQgLTIuNjUzMzIgLTUuNzk0OTIgLTIuNjUzMzJjLTAuNzk3ODUyIDAgLTEuNjA0NDkgMC4xMjU5NzcgLTIuMzg5NjUgMC4zODI4MTJjLTIuOTM3NSAwLjk2NTgyIC01LjAwMzkxIDMuNjAxNTYgLTUuMjQxMjEgNi42ODQ1N2wtMTAuNjgyNiAxMzkuMTE4Yy0wLjE2Nzk2OSAyLjE4OTQ1IDAuNjEyMzA1IDQuMzQ1NyAyLjE0MzU1IDUuOTIwOWwxMDcuMjg5IDExMC4zNTlsLTEzNi42NjcgLTEwMi4xNDYKYy0yLjMwMTc2IC0xLjcyMDcgLTUuMzc1OTggLTIuMDA2ODQgLTcuOTUzMTIgLTAuNzQxMjExbC0xNDAuNTYzIDY4Ljk4MzRjLTIuNjQ4NDQgMS4yOTg4MyAtNC4zMTM0OCA0LjAwMzkxIC00LjI4MTI1IDYuOTUyMTVjMC4wMzIyMjY2IDIuOTQ3MjcgMS43NTM5MSA1LjYxNTIzIDQuNDI3NzMgNi44NTg0bDQ5Ni42OTQgMjMwLjc3OGMyLjc0OTAyIDEuMjc0NDEgNS45OTIxOSAwLjgxMDU0NyA4LjI3MTQ4IC0xLjE4ODQ4eiIgLz4KICAgIDxnbHlwaCBnbHlwaC1uYW1lPSJ1bmlGMTE2IiB1bmljb2RlPSImI3hmMTE2OyIgCmQ9Ik00MTYuNDc5IDEyMi41OTJjNTEuNTk0NyAwIDkzLjMzMTEgLTQxLjczNzMgOTMuMzA2NiAtOTMuMzA3NmMwIC01MS41MjQ0IC00MS43MzYzIC05My4yODQyIC05My4zMzAxIC05My4yODQyYy01MS40Nzc1IDAgLTkzLjI1OTggNDEuNzU5OCAtOTMuMjU5OCA5My4yODQyYzAgOS4xODE2NCAxLjc5Mjk3IDE3Ljg3NCA0LjI2MzY3IDI2LjIzOTNsLTE1Ny42NzEgODAuNjUzMwpjLTE3LjA1NzYgLTIyLjYyNzkgLTQzLjg1NjQgLTM3LjQ0ODIgLTc0LjM2MTMgLTM3LjQ0ODJjLTUxLjU0NTkgMCAtOTMuMjU5OCA0MS43NTk4IC05My4yNTk4IDkzLjI4MzJjMCA1MS41MjQ0IDQxLjcxMjkgOTMuMzA2NiA5My4yNTk4IDkzLjMwNjZjMjkuNjg4NSAwIDU1Ljg4MTggLTE0LjE2OCA3Mi45ODczIC0zNS44MTc0bDE1OC40MTcgODEuMDI2NGMtMi4wOTY2OCA3Ljc2MDc0IC0zLjU4ODg3IDE1Ljc3NjQgLTMuNTg4ODcgMjQuMjEyOQpjMCA1MS41IDQxLjc4MzIgOTMuMjU5OCA5My4yNjA3IDkzLjI1OThjNTEuNTkyOCAwIDkzLjMzMDEgLTQxLjc1OTggOTMuMzI5MSAtOTMuMjYwN2MwIC01MS41NDc5IC00MS43MzYzIC05My4yODQyIC05My4zMzAxIC05My4yODQyYy0zMC40NTggMCAtNTcuMjgwMyAxNC43NzQ0IC03NC4yNjg2IDM3LjM3ODlsLTE1Ny43NDEgLTgwLjY1MjNjMi40NzA3IC04LjM4OTY1IDQuMjQxMjEgLTE3LjAxMTcgNC4yNDEyMSAtMjYuMTY5OQpjMCAtOC40MTIxMSAtMS40OTAyMyAtMTYuNDA1MyAtMy41NjQ0NSAtMjQuMTQxNmwxNTguNDQgLTgxLjAyNjRjMTcuMDgyIDIxLjYyNiA0My4yMjc1IDM1Ljc0OCA3Mi44NjkxIDM1Ljc0OHoiIC8+CiAgICA8Z2x5cGggZ2x5cGgtbmFtZT0idW5pRjEwMSIgdW5pY29kZT0iJiN4ZjEwMTsiIApkPSJNMjU1Ljk5OSA0Ni4wNzMyYy05LjE3NTc4IDAgLTE4LjM1MDYgMy41MDM5MSAtMjUuMzQ2NyAxMC40OTYxbC0yMjAuMTQ4IDIyMC4xNWMtMTQuMDAzOSAxNC4wMDQ5IC0xNC4wMDM5IDM2LjcxIDAgNTAuNzA5YzEzLjk5OSAxMy45OTggMzYuNzAwMiAxMy45OTggNTAuNzA1MSAwbDE5NC43OSAtMTk0LjgwMmwxOTQuNzkyIDE5NC43OTVjMTQuMDAzOSAxMy45OTggMzYuNzAzMSAxMy45OTggNTAuNzAwMiAwCmMxNC4wMTE3IC0xMy45OTkgMTQuMDExNyAtMzYuNzA0MSAwIC01MC43MDlsLTIyMC4xNDUgLTIyMC4xNWMtNyAtNi45OTQxNCAtMTYuMTc0OCAtMTAuNDg5MyAtMjUuMzQ3NyAtMTAuNDg5M3oiIC8+CiAgICA8Z2x5cGggZ2x5cGgtbmFtZT0idW5pRjEyMiIgdW5pY29kZT0iJiN4ZjEyMjsiIApkPSJNNDY1LjQ1NSAzMzEuNjM2YzI1LjU5OTYgMCA0Ni41NDQ5IC0yMC45NDQzIDQ2LjU0MyAtNDYuNTQ1OWMwIC0yNS41OTk2IC0yMC45NDUzIC00Ni41NDQ5IC00Ni41NDQ5IC00Ni41NDQ5Yy00LjE4OTQ1IDAgLTguMTQ1NTEgMC40NjQ4NDQgLTExLjg2OTEgMS42Mjg5MWwtODIuODUwNiAtODIuNjE4MmMxLjE2NDA2IC0zLjcyMzYzIDEuNjI4OTEgLTcuOTEzMDkgMS42Mjg5MSAtMTIuMTAxNgpjMCAtMjUuNjAwNiAtMjAuOTQ1MyAtNDYuNTQ0OSAtNDYuNTQ0OSAtNDYuNTQ0OWMtMjUuNjAwNiAwIC00Ni41NDQ5IDIwLjk0NDMgLTQ2LjU0NDkgNDYuNTQ0OWMwIDQuMTg4NDggMC40NjQ4NDQgOC4zNzc5MyAxLjYyODkxIDEyLjEwMTZsLTU5LjM0NTcgNTkuMzQ2N2MtMy43MjM2MyAtMS4xNjQwNiAtNy45MTMwOSAtMS42Mjg5MSAtMTIuMTAxNiAtMS42Mjg5MWMtNC4xODk0NSAwIC04LjM3NzkzIDAuNDY0ODQ0IC0xMi4xMDI1IDEuNjI4OTEKbC0xMDUuODkxIC0xMDYuMTI0YzEuMTYzMDkgLTMuNzIzNjMgMS42Mjg5MSAtNy42ODA2NiAxLjYyODkxIC0xMS44NjkxYzAgLTI1LjYwMDYgLTIwLjk0NTMgLTQ2LjU0NDkgLTQ2LjU0NDkgLTQ2LjU0NDlzLTQ2LjU0NDkgMjAuOTQ0MyAtNDYuNTQ0OSA0Ni41NDQ5YzAgMjUuNTk5NiAyMC45NDUzIDQ2LjU0NTkgNDYuNTQ1OSA0Ni41NDQ5YzQuMTg5NDUgMCA4LjE0NTUxIC0wLjQ2NDg0NCAxMS44NjkxIC0xLjYyODkxbDEwNi4xMjUgMTA1Ljg5MQpjLTEuMTY0MDYgMy43MjQ2MSAtMS42Mjg5MSA3LjkxMzA5IC0xLjYyODkxIDEyLjEwMjVjMCAyNS41OTk2IDIwLjk0NTMgNDYuNTQ0OSA0Ni41NDQ5IDQ2LjU0NDlzNDYuNTQ0OSAtMjAuOTQ1MyA0Ni41NDQ5IC00Ni41NDQ5YzAgLTQuMTg5NDUgLTAuNDY0ODQ0IC04LjM3NzkzIC0xLjYyODkxIC0xMi4xMDI1bDU5LjM0NDcgLTU5LjM0NDdjMy43MjQ2MSAxLjE2NDA2IDcuOTEzMDkgMS42Mjg5MSAxMi4xMDI1IDEuNjI4OTEKYzQuMTg4NDggMCA4LjM3NzkzIC0wLjQ2NDg0NCAxMi4xMDE2IC0xLjYyODkxbDgyLjYxODIgODIuODUwNmMtMS4xNjQwNiAzLjcyNDYxIC0xLjYyODkxIDcuNjgwNjYgLTEuNjI4OTEgMTEuODY5MWMwIDI1LjYwMDYgMjAuOTQ1MyA0Ni41NDQ5IDQ2LjU0NDkgNDYuNTQ0OXoiIC8+CiAgICA8Z2x5cGggZ2x5cGgtbmFtZT0idW5pRjEwQyIgdW5pY29kZT0iJiN4ZjEwYzsiIApkPSJNMjguNzgxMiAxOTQuMzgzYy0xNS40MzM2IDAgLTI4Ljc4MTIgMTIuMjk5OCAtMjguNzgxMiAyNy43NzI1djIzLjc5M2MwIDE1LjQ3MjcgMTIuOTY4OCAyOC44MTY0IDI4LjM5NDUgMjguODE2NGgzNDMuNDA2bC0xMDguOTE0IDEwOC45MDJjLTUuMjc3MzQgNS4yODIyMyAtOC4xNjc5NyAxMS45NjA5IC04LjE2Nzk3IDE5LjQ3MzZjMCA3LjUxNzU4IDIuODkwNjIgMTQuNTY4NCA4LjE2Nzk3IDE5Ljg0NjdsMTYuNzU3OCAxNi44MjEzCmM1LjI2NTYyIDUuMjc5MyAxMi4yOTMgOC4xOTA0MyAxOS43NzczIDguMTkwNDNjNy41IDAgMTQuNTE5NSAtMi45MDYyNSAxOS43OTMgLTguMTkwNDNsMTg0LjYyNSAtMTg1LjIzMmM1LjI4OTA2IC01LjI5OTggOC4xNzk2OSAtMTIuMzc0IDguMTYwMTYgLTE5LjkzNzVjMC4wMTk1MzEyIC03LjUyMTQ4IC0yLjg3MTA5IC0xNC41OTI4IC04LjE2MDE2IC0xOS45MDA0bC0xODQuNjI1IC0xODUuMjE2CmMtNS4yNzM0NCAtNS4yODgwOSAtMTIuMjk2OSAtOC4xOTA0MyAtMTkuNzkzIC04LjE5MDQzYy03LjQ4NDM4IDAgLTE0LjUxMTcgMi45MjM4MyAtMTkuNzc3MyA4LjIxMDk0bC0xNi43NTc4IDE2LjgyMjNjLTUuMjc3MzQgNS4zMDA3OCAtOC4xNjc5NyAxMi40MTIxIC04LjE2Nzk3IDE5LjkyOTdjMCA3LjUxNjYgMi44OTA2MiAxNC42Nzk3IDguMTY3OTcgMTkuOTY2OGwxMDcuNzAzIDEwOC4yOTdoLTM0Mi42MDl6IiAvPgogICAgPGdseXBoIGdseXBoLW5hbWU9InVuaUYxMTgiIHVuaWNvZGU9IiYjeGYxMTg7IiAKZD0iTTM2MC43MjggNDQ4di00Ni41NDU5aC0yNzkuMjczdi0zMjUuODE3aC00Ni41NDQ5djMyNS44MTdjMCAyNS42MDA2IDIwLjk0NTMgNDYuNTQ1OSA0Ni41NDQ5IDQ2LjU0NTloMjc5LjI3M3pNNDMwLjU0NiAzNTQuOTA5YzI1LjU5OTYgMCA0Ni41NDQ5IC0yMC45NDUzIDQ2LjU0NDkgLTQ2LjU0NTl2LTMyNS44MTdjMCAtMjUuNjAwNiAtMjAuOTQ1MyAtNDYuNTQ1OSAtNDYuNTQ0OSAtNDYuNTQ1OWgtMjU2CmMtMjUuNjAwNiAwIC00Ni41NDU5IDIwLjk0NTMgLTQ2LjU0NTkgNDYuNTQ1OXYzMjUuODE3YzAgMjUuNjAwNiAyMC45NDUzIDQ2LjU0NTkgNDYuNTQ1OSA0Ni41NDU5aDI1NnpNNDMwLjU0NiAtMTcuNDU0MXYzMjUuODE3aC0yNTZ2LTMyNS44MTdoMjU2eiIgLz4KICAgIDxnbHlwaCBnbHlwaC1uYW1lPSJ1bmlGMTAyIiB1bmljb2RlPSImI3hmMTAyOyIgCmQ9Ik01MDAuNjE5IDMuMDc0MjJjNy41OTA4MiAtNy41OTA4MiAxMS4zODQ4IC0xNi44MjUyIDExLjM4MTggLTI3LjY5MDRjMCAtMTAuNjY2IC0zLjg5OTQxIC0xOS44OTc1IC0xMS42OTA0IC0yNy42ODk1Yy03Ljc4OTA2IC03Ljc5Mzk1IC0xNy4wMjQ0IC0xMS42OTI0IC0yNy42OTE0IC0xMS42OTI0Yy0xMS4wODU5IDAgLTIwLjMwNDcgMy44OTk0MSAtMjcuNjg5NSAxMS42OTI0bC0xMDUuNTM4IDEwNS4yMjgKYy0zNi43MTc4IC0yNS40MzE2IC03Ny42NDA2IC0zOC4xNTUzIC0xMjIuNzc0IC0zOC4xNTUzYy0yOS4zMzAxIDAgLTU3LjM4MDkgNS42OTA0MyAtODQuMTUyMyAxNy4wNzQyYy0yNi43Njc2IDExLjM4OTYgLTQ5Ljg0NzcgMjYuNzY5NSAtNjkuMjMyNCA0Ni4xNjExYy0xOS4zODY3IDE5LjM4MzggLTM0Ljc3MjUgNDIuNDU3IC00Ni4xNTUzIDY5LjIyNDZjLTExLjM4NDggMjYuNzc1NCAtMTcuMDc3MSA1NC44MTkzIC0xNy4wNzcxIDg0LjE1MDQKYzAgMjkuMzMzIDUuNjkyMzggNTcuMzgzOCAxNy4wNzcxIDg0LjE1NTNjMTEuMzgzOCAyNi43Njc2IDI2Ljc2ODYgNDkuODQ3NyA0Ni4xNTUzIDY5LjIzMjRjMTkuMzg0OCAxOS4zODQ4IDQyLjQ2IDM0Ljc3MTUgNjkuMjMyNCA0Ni4xNTMzYzI2Ljc2ODYgMTEuMzg2NyA1NC44MjIzIDE3LjA3OTEgODQuMTUyMyAxNy4wNzkxYzI5LjMzMyAwIDU3LjM4NDggLTUuNjkyMzggODQuMTYwMiAtMTcuMDc5MQpjMjYuNzY3NiAtMTEuMzgxOCA0OS44Mzk4IC0yNi43Njg2IDY5LjIyNDYgLTQ2LjE1MzNzMzQuNzcwNSAtNDIuNDYwOSA0Ni4xNTIzIC02OS4yMzI0YzExLjM4OTYgLTI2Ljc2ODYgMTcuMDg0IC01NC44MjIzIDE3LjA4NCAtODQuMTU1M2MwIC00NS4xMjMgLTEyLjcyMzYgLTg2LjA1MzcgLTM4LjE1NzIgLTEyMi43Njl6TTMxNC4wMDMgMTM0LjAwMmMyNi45NzM2IDI2Ljk3NDYgNDAuNDY1OCA1OS40Mjk3IDQwLjQ2NTggOTcuMzgxOApjMCAzNy45NDYzIC0xMy40ODczIDcwLjQxMDIgLTQwLjQ2NTggOTcuMzgwOWMtMjYuOTc3NSAyNi45NzE3IC01OS40Mzc1IDQwLjQ2MzkgLTk3LjM4NTcgNDAuNDYzOWMtMzcuOTQ1MyAwIC03MC40MTAyIC0xMy40ODYzIC05Ny4zODA5IC00MC40NjM5Yy0yNi45NzI3IC0yNi45NzA3IC00MC40NjM5IC01OS40MzQ2IC00MC40NjM5IC05Ny4zODA5YzAgLTM3Ljk1MjEgMTMuNDg3MyAtNzAuNDA3MiA0MC40NjM5IC05Ny4zODE4CmMyNi45NzU2IC0yNi45NzQ2IDU5LjQzNTUgLTQwLjQ2NjggOTcuMzgwOSAtNDAuNDY2OGMzNy45NTMxIDAgNzAuNDEyMSAxMy40ODkzIDk3LjM4NTcgNDAuNDY2OHoiIC8+CiAgICA8Z2x5cGggZ2x5cGgtbmFtZT0idW5pRjExMSIgdW5pY29kZT0iJiN4ZjExMTsiIApkPSJNNDk0LjgwNSAzNDIuNDc5bC0yNjEuMjU4IC0yNzQuNTk5Yy0xMS4xMDg0IC0xMS42NzY4IC0yNS44NzUgLTE4LjEwMjUgLTQxLjU4NCAtMTguMTAyNWMtMTUuNzA4IDAgLTMwLjQ3NDYgNi40MjU3OCAtNDEuNTgzIDE4LjEwMjVsLTEzMy4xNTIgMTM5Ljk1MmMtMTEuMTA4NCAxMS42NzU4IC0xNy4yMjc1IDI3LjE5NTMgLTE3LjIyNzUgNDMuNzA3YzAgMTYuNTE0NiA2LjExOTE0IDMyLjAzNTIgMTcuMjI3NSA0My43MTA5CmMxMS4xMDQ1IDExLjY3NTggMjUuODcxMSAxOC4xMDc0IDQxLjU4NCAxOC4xMDc0YzE1LjcwOCAwIDMwLjQ3OTUgLTYuNDMxNjQgNDEuNTgzIC0xOC4xMTEzbDkxLjU2NDUgLTk2LjIzNTRsMjE5LjY3IDIzMC44ODdjMTEuMTA4NCAxMS42NzY4IDI1Ljg3NSAxOC4xMDI1IDQxLjU4MyAxOC4xMDI1YzE1LjcwOSAwIDMwLjQ3NTYgLTYuNDI1NzggNDEuNTg0IC0xOC4xMDI1CmMyMi45Mzc1IC0yNC4xMDg0IDIyLjkzNzUgLTYzLjMxOTMgMC4wMDg3ODkwNiAtODcuNDE4OXoiIC8+CiAgICA8Z2x5cGggZ2x5cGgtbmFtZT0idW5pRjEyNCIgdW5pY29kZT0iJiN4ZjEyNDsiIApkPSJNNDQ4IDI3MC44NnYtMjcwLjg2YzAgLTM1LjM0MzggLTI4LjY1NjIgLTY0IC02NCAtNjRoLTMyMGMtMzUuMzQzOCAwIC02NCAyOC42NTYyIC02NCA2NHYzMjBjMCAzNS4zNDM4IDI4LjY1NjIgNjQgNjQgNjRoMjcwLjg0NGwtNjMuOTY4OCAtNjRoLTIwNi44NzV2LTMyMGgzMjB2MjA2Ljg0NHpNMzk4Ljg3NSA0MDIuNzVsNjcuODc1IC02Ny44OTA2bC0yMi42MjUgLTIyLjYyNWwtNjcuODc1IDY3Ljg5MDZ6TTQ0NC4xMjUgNDQ4Cmw2Ny44NzUgLTY3Ljg3NWwtMjIuNjI1IC0yMi42NDA2bC02Ny44NzUgNjcuODkwNnpNMTUwIDE1My44MTJsMjAzLjYyNSAyMDMuNjczbDY3Ljg3NSAtNjcuODkxNmwtMjAzLjYyNSAtMjAzLjY1NnpNMTI4IDY0djY0bDY0IC02NGgtNjR6IiAvPgogICAgPGdseXBoIGdseXBoLW5hbWU9InVuaUYxMDkiIHVuaWNvZGU9IiYjeGYxMDk7IiAKZD0iTTQ5MC42NjggMTQ5LjMzNGMxMi44MDA4IDAgMjEuMzMyIC04LjUzMzIgMjEuMzMyIC0yMS4zMzR2LTE3MC42NjZjMCAtMTIuODAwOCAtOC41MzEyNSAtMjEuMzM0IC0yMS4zMzIgLTIxLjMzNGgtNDY5LjMzMmMtMTIuODA0NyAwIC0yMS4zMzU5IDguNTMzMiAtMjEuMzM1OSAyMS4zMzR2NDY5LjMzM2MwIDEyLjc5OTggOC41MzEyNSAyMS4zMzMgMjEuMzM1OSAyMS4zMzNoMTcwLjY2NApjMTIuODA0NyAwIDIxLjMzNTkgLTguNTMzMiAyMS4zMzU5IC0yMS4zMzN2LTQyLjY2N2MwIC0xMi43OTk4IC04LjUzMTI1IC0yMS4zMzMgLTIxLjMzNTkgLTIxLjMzM2gtOTZjLTYuMzk4NDQgMCAtMTAuNjY0MSAtNC4yNjY2IC0xMC42NjQxIC0xMC42Njd2LTMyMGMwIC02LjQwMDM5IDQuMjY1NjIgLTEwLjY2NiAxMC42NjQxIC0xMC42NjZoMzIwYzYuNDAyMzQgMCAxMC42NjggNC4yNjU2MiAxMC42NjggMTAuNjY2djk2CmMwIDEyLjgwMDggOC41MzEyNSAyMS4zMzQgMjEuMzMyIDIxLjMzNGg0Mi42Njh6TTQ4Ni40MDIgNDQ4YzEzLjk2NDggMCAyMy4yNjk1IC05LjMwODU5IDI1LjU5NzcgLTI1LjU5OTZ2LTE4Ni4xODJjMCAtMTMuOTYzOSAtOS4zMDQ2OSAtMjMuMjczNCAtMjMuMjY5NSAtMjMuMjczNGgtNDYuNTQ2OWMtMTMuOTY0OCAwIC0yMy4yNjk1IDkuMzA5NTcgLTIzLjI2OTUgMjMuMjczNHYyMy4yNzI1CmMwIDExLjYzNTcgLTEzLjk2NDggMTYuMjkxIC0yMC45NDkyIDkuMzA5NTdsLTE4Ni4xODQgLTE4Ni4xODNjLTkuMzA0NjkgLTkuMzA4NTkgLTIzLjI2OTUgLTkuMzA4NTkgLTMyLjU4MiAwbC0zMi41NzgxIDMyLjU4MmMtOS4zMTI1IDkuMzA5NTcgLTkuMzEyNSAyMy4yNzI1IDAgMzIuNTgybDE4Ni4xODQgMTg2LjE4MmM2Ljk3NjU2IDkuMzA5NTcgMCAyMC45NDUzIC05LjMxMjUgMjAuOTQ1M2gtMjMuMjczNApjLTEzLjk2MDkgMCAtMjMuMjY5NSA5LjMwOTU3IC0yMy4yNjk1IDIzLjI3MjV2NDYuNTQ1OWMwIDEzLjk2MzkgOS4zMDg1OSAyMy4yNzI1IDIzLjI2OTUgMjMuMjcyNWgxODYuMTg0eiIgLz4KICAgIDxnbHlwaCBnbHlwaC1uYW1lPSJ1bmlGMTI5IiB1bmljb2RlPSImI3hmMTI5OyIgCmQ9Ik00ODkuNzI0IDE3MC4yNzFjMTQuODQ4NiAtMTguNTgzIDIyLjI3MjUgLTM5Ljc4MTIgMjIuMjc1NCAtNjMuNjAzNWMwIC0yOC4yNzI1IC0xMC4wMDEgLTUyLjQwNDMgLTMwLjAwMSAtNzIuNDAwNGMtMjAuMDAyOSAtMTkuOTk2MSAtNDQuMTM0OCAtMzAgLTcyLjM5NjUgLTMwaC0yOTAuMTM3Yy0zMi44ODc3IDAgLTYxLjAyMjUgMTEuNjg1NSAtODQuMzk4NCAzNS4wNzAzCmMtMjMuMzc1IDIzLjM3NyAtMzUuMDY2NCA1MS41MDQ5IC0zNS4wNjY0IDg0LjM5NDVjMCAyMy4xMTYyIDYuMjIxNjggNDQuNDQ1MyAxOC42NjQxIDY0LjAwMzljMTIuNDQ3MyAxOS41NTM3IDI5LjE1NzIgMzQuMjE4OCA1MC4xMzM4IDQzLjk5NzFjLTAuMzU0NDkyIDUuMzMxMDUgLTAuNTMzMjAzIDkuMTU0MyAtMC41MzMyMDMgMTEuNDY0OGMwIDM3LjY4OTUgMTMuMzMwMSA2OS44NzAxIDM5Ljk5OSA5Ni41MzAzCmMyNi42NjcgMjYuNjY3IDU4Ljg0NTcgNDAuMDAzOSA5Ni41MzIyIDQwLjAwMzljMjcuNzM2MyAwIDUzLjExNTIgLTcuNzMzNCA3Ni4xMzQ4IC0yMy4yMDIxYzIzLjAxOTUgLTE1LjQ2NDggMzkuNzgxMiAtMzUuOTk3MSA1MC4yNjU2IC02MS41OTc3YzEyLjYxOTEgMTEuMDIyNSAyNy4zNzIxIDE2LjUzNDIgNDQuMjYyNyAxNi41MzQyYzE4Ljg0NDcgMCAzNC45MzM2IC02LjY2Nzk3IDQ4LjI2NDYgLTE5Ljk5OApjMTMuMzI4MSAtMTMuMzM1IDE5Ljk5NjEgLTI5LjQyMTkgMTkuOTk2MSAtNDguMjY3NmMwIC0xMy41MDk4IC0zLjYzNzcgLTI1Ljc3NzMgLTEwLjkzMTYgLTM2Ljc5ODhjMjMuMTE1MiAtNS41MTI3IDQyLjA4ODkgLTE3LjU1MzcgNTYuOTM1NSAtMzYuMTMwOXpNMzM4Ljc5MSAxNzcuNDY3YzEuNjg3NSAxLjY4MzU5IDIuNTMwMjcgMy42OTE0MSAyLjUzMTI1IDUuOTk3MDdjMCAyLjQ5NDE0IC0wLjc5MTk5MiA0LjUzODA5IC0yLjM5MjU4IDYuMTM3NwpsLTkzLjg2NzIgOTMuODYzM2MtMS41OTc2NiAxLjYwMDU5IC0zLjY0MzU1IDIuNDAwMzkgLTYuMTMzNzkgMi40MDAzOWMtMi40ODczIDAgLTQuNTMzMiAtMC43OTk4MDUgLTYuMTMyODEgLTIuNDAwMzlsLTkzLjU5OTYgLTkzLjU5NzdjLTEuNzc2MzcgLTIuMTMxODQgLTIuNjY0MDYgLTQuMjY2NiAtMi42NjQwNiAtNi40MDIzNGMwIC0yLjQ4NjMzIDAuNzk4ODI4IC00LjUzMTI1IDIuMzk4NDQgLTYuMTMyODEKYzEuNTk2NjggLTEuNTk0NzMgMy42NDM1NSAtMi4zOTM1NSA2LjEyOTg4IC0yLjM5MzU1aDU5LjczMzR2LTkzLjg2OTFjMCAtMi4zMDg1OSAwLjg0NzY1NiAtNC4zMDg1OSAyLjUzMzIgLTUuOTk3MDdjMS42ODg0OCAtMS42OTQzNCAzLjY4ODQ4IC0yLjUzMTI1IDUuOTk5MDIgLTIuNTMxMjVoNTEuMjA4YzIuMzA3NjIgMCA0LjMwMjczIDAuODM3ODkxIDUuOTk2MDkgMi41MzEyNQpjMS42ODg0OCAxLjY4ODQ4IDIuNTMyMjMgMy42ODg0OCAyLjUzMjIzIDUuOTk3MDd2OTMuODY5MWg1OS43MzI0YzIuMzA3NjIgMCA0LjMwMjczIDAuODQzNzUgNS45OTYwOSAyLjUyODMyeiIgLz4KICAgIDxnbHlwaCBnbHlwaC1uYW1lPSJ1bmlGMTEzIiB1bmljb2RlPSImI3hmMTEzOyIgCmQ9Ik00MDIuMjg2IDExOC44NTdoMzYuNTcxM3YzNi41NzEzaC0zNjUuNzE1di0zNi41NzEzaDM2LjU3MTN2LTM2LjU3MTNoLTEwOS43MTR2MTgyLjg1Nmw1MTIgMC4wMTg1NTQ3di0xODIuODc1aC0xMDkuNzE0djM2LjU3MTN6TTQzOC44NTcgMTE4Ljg1N3pNMTQ2LjI4NiA0NDhoMjE5LjQyOHYtMTQ2LjI4NmgtMjE5LjQyOHYxNDYuMjg2ek0xNDYuMjg2IDMwMS43MTR6TTE0Ni4yODYgLTY0djE4Mi44NTdoMjE5LjQyOHYtMTgyLjg1NwpoLTIxOS40Mjh6TTE4Mi44NTcgMjY1LjE0M2gxNDYuMjg1di0zNi41NzEzaC0xNDYuMjg1djM2LjU3MTN6TTE4Mi44NTcgMTU1LjQyOWgxNDYuMjg1di0zNi41NzEzaC0xNDYuMjg1djM2LjU3MTN6TTE4Mi44NTcgMTE4Ljg1N3oiIC8+CiAgICA8Z2x5cGggZ2x5cGgtbmFtZT0idW5pRjEwMCIgdW5pY29kZT0iJiN4ZjEwMDsiIApkPSJNMTkyIDE5MmMwIDQyLjY2NjcgMjEuMzMzMyA2NCA2NCA2NGM0Mi42NjY3IDAgNjQgLTIxLjMzMzMgNjQgLTY0cy0yMS4zMzMzIC02NCAtNjQgLTY0Yy00Mi42NjY3IC03LjYyOTM5ZS0wNiAtNjQgMjEuMzMzMyAtNjQgNjR6TTE5MiAwYzAgNDIuNjY2NyAyMS4zMzMzIDY0IDY0IDY0YzQyLjY2NjcgMCA2NCAtMjEuMzMzMyA2NCAtNjRzLTIxLjMzMzMgLTY0IC02NCAtNjRjLTQyLjY2NjcgMCAtNjQgMjEuMzMzMyAtNjQgNjR6TTE5MiAzODQKYzAgNDIuNjY2NyAyMS4zMzMzIDY0IDY0IDY0YzQyLjY2NjcgMCA2NCAtMjEuMzMzMyA2NCAtNjRzLTIxLjMzMzMgLTY0IC02NCAtNjRjLTQyLjY2NjcgMCAtNjQgMjEuMzMzMyAtNjQgNjR6IiAvPgogICAgPGdseXBoIGdseXBoLW5hbWU9InVuaUYxMjAiIHVuaWNvZGU9IiYjeGYxMjA7IiAKZD0iTTUxMiAyMjAuNDQ0di01Ni44ODg3aC00MDMuOTExbDEwMi40IC0xMDIuNGwtMzkuODIyMyAtMzkuODIyM2wtMTcwLjY2NyAxNzAuNjY3bDE3MC42NjcgMTcwLjY2N2wzOS44MjIzIC0zOS44MjIzbC0xMDIuNCAtMTAyLjRoNDAzLjkxMXoiIC8+CiAgICA8Z2x5cGggZ2x5cGgtbmFtZT0idW5pRjEyNSIgdW5pY29kZT0iJiN4ZjEyNTsiIApkPSJNNDYwLjY3MSAyMzYuOTEyYy0zLjk3NjU2IDY4LjAxMDcgLTM5LjAwODggMTI3LjU1MSAtOTEuMTA3NCAxNjQuODkzbDM2LjU3MjMgMzYuNTcxM2M2MS40NjQ4IC00Ni44MzY5IDEwMi4wMTUgLTExOS4yMSAxMDUuODY0IC0yMDEuNDY0aC01MS4zMjkxek0xNDIuNTY0IDQwMS42NzZjLTUyLjIyNjYgLTM3LjIxMjkgLTg3LjI1ODggLTk2Ljc1MjkgLTkxLjIzNTQgLTE2NC43NjRoLTUxLjMyOTEKYzMuODQ5NjEgODIuMjUzOSA0NC4zOTk0IDE1NC42MjcgMTA1Ljg2NCAyMDEuNDY0ek00MDkuOTg1IDIyNC4wODF2LTEyOC4zMjFsNTEuMzI3MSAtNTEuMzI5MXYtMjUuNjY0MWgtNDEwLjYyNnYyNS42NjQxbDUxLjMyODEgNTEuMzI5MXYxMjguMzE5YzAgNzguOTE4IDQxLjk2MDkgMTQ0Ljc0NiAxMTUuNDg5IDE2Mi4xOTh2MTcuNDUyMWMwIDIxLjMwMTggMTcuMTk0MyAzOC40OTYxIDM4LjQ5NjEgMzguNDk2MQpzMzguNDk2MSAtMTcuMTk0MyAzOC40OTYxIC0zOC40OTYxdi0xNy40NTEyYzczLjUyODMgLTE3LjQ1MjEgMTE1LjQ4OSAtODMuMjgwMyAxMTUuNDg5IC0xNjIuMTk3ek0yNTYgLTU4LjIyNTZjLTI4LjM1ODQgMCAtNTEuMzI5MSAyMi45Njg4IC01MS4zMjcxIDUxLjMyNzFoMTAyLjY1NmMwIC03LjE4NTU1IC0xLjQxMzA5IC0xMy44NTg0IC0zLjk3ODUyIC0yMC4wMTc2CmMtNi41NDQ5MiAtMTUuMjY5NSAtMjAuMjc0NCAtMjYuODE4NCAtMzYuOTU3IC0zMC4yODMyYy0zLjMzNTk0IC0wLjY0MTYwMiAtNi43OTk4IC0xLjAyNjM3IC0xMC4zOTM2IC0xLjAyNjM3djB6IiAvPgogICAgPGdseXBoIGdseXBoLW5hbWU9InVuaUYxMUYiIHVuaWNvZGU9IiYjeGYxMWY7IiAKZD0iTTQyNC44NDYgMjQxLjIzM2MxNS4zNzYgMCAyNy45MTcgLTE1Ljg2NjIgMjcuOTE3IC0zNS40NTl2LTIzNC4xODljMCAtMTkuNTcxMyAtMTIuNTQyIC0zNS41ODUgLTI3LjkxNyAtMzUuNTg1aC0zMzcuNjkyYy0xNS4zNzYgMCAtMjcuOTE3IDE2LjAxMzcgLTI3LjkxNyAzNS41ODV2MjM0LjE4OWMwIDE5LjU5MzggMTIuNTQxIDM1LjQ1OSAyNy45MTYgMzUuNDU5aDkuOTc2NTZ2NDYuNjkyNApjMCA4Ni4zNjA0IDY4LjUzMjIgMTU4LjUwMyAxNTQuMTkyIDE2MC4wNDNjMi4zMzk4NCAwLjA0MTk5MjIgNy4wMTY2IDAuMDQxOTkyMiA5LjM1NzQyIDBjODUuNjU4MiAtMS41NDAwNCAxNTQuMTkxIC03My42ODI2IDE1NC4xOTEgLTE2MC4wNDN2LTQ2LjY5MjRoOS45NzU1OXpNMjg3LjYzNyA4OC45MTdjNy44NjUyMyA3Ljc0MjE5IDEyLjQ0NDMgMTguNDQ4MiAxMi40NDYzIDMwLjI5CmMwIDIyLjQ0MTQgLTE3LjM0NjcgNDEuNzI1NiAtMzkuNDAzMyA0Mi42MTYyYy0yLjMzNzg5IDAuMDk0NzI2NiAtNy4wMTk1MyAwLjA5NDcyNjYgLTkuMzU3NDIgMGMtMjIuMDU1NyAtMC44OTA2MjUgLTM5LjQwMzMgLTIwLjE3NDggLTM5LjQwMzMgLTQyLjYxNjJjMCAtMTEuODQxOCA0LjU3OTEgLTIyLjU0NzkgMTIuNDQ0MyAtMzAuMjl2LTcwLjc2MjdjMCAtOC4xMDI1NCA2Ljc4NDE4IC0xNC45NiAxNC44OTM2IC0xNC45NmgzMy40ODczCmM4LjEwODQgMCAxNC44OTI2IDYuODU3NDIgMTQuODkyNiAxNC45NnY3MC43NjI3ek0zNDkuMTE0IDI0MS4yMzNsMC4wMDA5NzY1NjIgNDYuNjkyNGMwIDUxLjQ0MzQgLTQxLjgwODYgOTMuOTc5NSAtOTMuMTE2MiA5My45Nzk1Yy01MS4zMDY2IDAgLTkzLjExMzMgLTQyLjUzNjEgLTkzLjExMzMgLTkzLjk3OTV2LTQ2LjY5MjRoODguNDM1NWg5LjM1NzQyaDg4LjQzNTV6IiAvPgogICAgPGdseXBoIGdseXBoLW5hbWU9InVuaUYxMDYiIHVuaWNvZGU9IiYjeGYxMDY7IiAKZD0iTTI1NS45OTMgNDQ4LjAwMWMxNDEuMzggMCAyNTYuMDA3IC0xMTQuNjE1IDI1Ni4wMDcgLTI1NS45OTVzLTExNC42MjcgLTI1Ni4wMDUgLTI1Ni4wMDcgLTI1Ni4wMDVjLTE0MS4zNzkgMCAtMjU1Ljk5MyAxMTQuNjMyIC0yNTUuOTkzIDI1Ni4wMDVzMTE0LjYxNCAyNTUuOTk1IDI1NS45OTMgMjU1Ljk5NXpNMzc3LjY4OSAyNjMuNzc0YzIuODY3MTkgMi44NjQyNiAyLjg2NzE5IDcuNTE2NiAwLjAwMzkwNjI1IDEwLjM3OTkKbC0zOS4xMjAxIDM5LjExMjNjLTIuODY0MjYgMi44NjcxOSAtNy41MTY2IDIuODY3MTkgLTEwLjM4MDkgMC4wMDc4MTI1bC0xOTUuMzYzIC0xOTUuMzdsMC4yMzQzNzUgLTAuMjM0Mzc1Yy0yLjIwNTA4IC0xLjMzNzg5IC0zLjgyMDMxIC0zLjUyMzQ0IC00LjQwMDM5IC02LjExMzI4bC0xMC4yODYxIC00Ni4xMzk2Yy0wLjcxNjc5NyAtMy4yMDk5NiAwLjI1MjkzIC02LjU2MDU1IDIuNTgxMDUgLTguODc5ODgKYzEuODEzNDggLTEuODE5MzQgNC4yNjA3NCAtMi44MTA1NSA2Ljc4MDI3IC0yLjgxMDU1YzAuNjk0MzM2IDAgMS4zOTc0NiAwLjA3NTE5NTMgMi4wODg4NyAwLjIzMDQ2OWw0Ni4xNTMzIDEwLjI5MWMyLjU5MDgyIDAuNTc1MTk1IDQuNzcyNDYgMi4xODY1MiA2LjExNDI2IDQuMzk0NTNsMC4yMjk0OTIgLTAuMjMwNDY5bDEyLjA5NzcgMTIuMDk3N2wtNDkuNDkyMiA0OS40OTEybDE4Ljc3NjQgMTguNzc2NGw0OS40OTIyIC00OS40ODYzCmwxMjEuMzk5IDEyMS4zODlsLTQ5LjQ5NTEgNDkuNDk1MWwxOC43NzczIDE4Ljc3NjRsNDkuNDk1MSAtNDkuNDkxMnoiIC8+CiAgICA8Z2x5cGggZ2x5cGgtbmFtZT0idW5pRjExRCIgdW5pY29kZT0iJiN4ZjExZDsiIApkPSJNMzAxLjI4IDE5MS45OTVsMjAxLjMzOCAtMjAxLjMyN2MxMi41MDg4IC0xMi40OTggMTIuNTA4OCAtMzIuNzc3MyAwIC00NS4yNzU0Yy02LjI1MzkxIC02LjI1NDg4IC0xNC40NTEyIC05LjM4MTg0IC0yMi42Mzc3IC05LjM4MTg0cy0xNi4zODI4IDMuMTI2OTUgLTIyLjYzNzcgOS4zODE4NGwtMjAxLjMzOCAyMDEuMzM4bC0yMDEuMzQ4IC0yMDEuMzM4CmMtNi4yNTQ4OCAtNi4yNTQ4OCAtMTQuNDUxMiAtOS4zODE4NCAtMjIuNjM3NyAtOS4zODE4NHMtMTYuMzgzOCAzLjEyNjk1IC0yMi42Mzc3IDkuMzcxMDljLTEyLjUwODggMTIuNDk4IC0xMi41MDg4IDMyLjc3NzMgMCA0NS4yNzU0bDIwMS4zNDggMjAxLjMzOGwtMjAxLjM0OCAyMDEuMzM3Yy0xMi41MDg4IDEyLjQ5OCAtMTIuNTA4OCAzMi43NzczIDAgNDUuMjc1NGMxMi41MDg4IDEyLjUwODggMzIuNzY2NiAxMi41MDg4IDQ1LjI3NTQgMApsMjAxLjM0OCAtMjAxLjMzN2wyMDEuMzM4IDIwMS4zMzdjMTIuNTA4OCAxMi41MDg4IDMyLjc2NjYgMTIuNTA4OCA0NS4yNzU0IDBjMTIuNTA4OCAtMTIuNDk4IDEyLjUwODggLTMyLjc3NzMgMCAtNDUuMjc1NHoiIC8+CiAgICA8Z2x5cGggZ2x5cGgtbmFtZT0idW5pRjEyQiIgdW5pY29kZT0iJiN4ZjEyYjsiIApkPSJNMTcwLjY2NyAxMzUuMTExYzAgLTMxLjQxODkgLTI1LjQ3MDcgLTU2Ljg4ODcgLTU2Ljg4OTYgLTU2Ljg4ODdjLTMxLjQxOCAwIC01Ni44ODg3IDI1LjQ2OTcgLTU2Ljg4ODcgNTYuODg4N2MwIDMxLjQxOCAyNS40NzA3IDU2Ljg4ODcgNTYuODg4NyA1Ni44ODg3YzMxLjQxODkgMCA1Ni44ODk2IC0yNS40NzA3IDU2Ljg4OTYgLTU2Ljg4ODd6TTQ1NS4xMTEgMTM1LjExMQpjMCAtMzEuNDE4OSAtMjUuNDcwNyAtNTYuODg4NyAtNTYuODg4NyAtNTYuODg4N2MtMzEuNDE4OSAwIC01Ni44ODk2IDI1LjQ2OTcgLTU2Ljg4OTYgNTYuODg4N2MwIDMxLjQxOCAyNS40NzA3IDU2Ljg4ODcgNTYuODg5NiA1Ni44ODg3YzMxLjQxOCAwIDU2Ljg4ODcgLTI1LjQ3MDcgNTYuODg4NyAtNTYuODg4N3pNMjI3LjU1NiAtNDkuNzc3M2MwIC03Ljk2Mjg5IC03LjA4NTk0IC0xNC4yMjI3IC0xNC45MjE5IC0xNC4yMjI3aC00MS45NjY4CnY1Ni44ODg3aDEzLjk4ODNjMjMuMjI0NiAwIDQyLjkwMDQgLTE5LjA1NTcgNDIuOTAwNCAtNDIuNjY2ek0xNC4yMjI3IC02NGMtNy45NjY4IDAgLTE0LjIyMjcgNi4yODUxNiAtMTQuMjIyNyAxNC4yODAzdjg1LjY4MzZjMCAyMS43MjI3IDE2LjA1NTcgMzkuNDEzMSAzNi44OTY1IDQyLjI1ODhjMTUuNjExMyAtMTcuMDc4MSAzNy44MDc2IC0yNy45Nzc1IDYyLjY1OTIgLTI3Ljk3NzVzNDcuMDQ3OSAxMC44OTk0IDYyLjY1OTIgMjcuOTc3NQpjMjAuODQwOCAtMi44NDU3IDM2Ljg5NjUgLTIwLjUzNjEgMzYuODk2NSAtNDIuMjU4OHYtMTUuNzA4Yy00LjU1MTc2IDAuODU4Mzk4IC05LjM4ODY3IDEuNDI3NzMgLTE0LjIyMjcgMS40Mjc3M2gtODUuMzMzYy03Ljk2NjggMCAtMTQuMjIyNyAtNi4yODEyNSAtMTQuMjIyNyAtMTQuMjgwM2MwIC03Ljk5NjA5IDYuMjU1ODYgLTE0LjI4MTIgMTQuMjIyNyAtMTQuMjgxMmg0Mi42Njd2LTU3LjEyMjFoLTEyOHpNMjU2IC00OS43NzczCmMwIDIzLjYxMDQgMTkuMDU1NyA0Mi42NjYgNDIuNjY3IDQyLjY2NmgxNC4yMjE3di01Ni44ODg3aC00Mi42NjZjLTcuOTY2OCAwIC0xNC4yMjI3IDYuMjU5NzcgLTE0LjIyMjcgMTQuMjIyN3pNNDc1LjEwNCA3OC4yMjI3YzIwLjg0MDggLTIuODQ1NyAzNi44OTY1IC0yMC41MzYxIDM2Ljg5NjUgLTQyLjI1ODh2LTg1LjY4MzZjMCAtNy45OTUxMiAtNi4yNTU4NiAtMTQuMjgwMyAtMTQuMjIyNyAtMTQuMjgwM2gtMTI4djU3LjEyMjFoNDIuNjY3CmM3Ljk2NjggMCAxNC4yMjI3IDYuMjg1MTYgMTQuMjIyNyAxNC4yODEyYzAgNy45OTkwMiAtNi4yNTU4NiAxNC4yODAzIC0xNC4yMjI3IDE0LjI4MDNoLTg1LjMzM2MtNC44MzM5OCAwIC05LjY3MDkgLTAuNTY5MzM2IC0xNC4yMjI3IC0xLjQyNzczdjE1LjcwOGMwIDIxLjcyMjcgMTYuMDU1NyAzOS40MTMxIDM2Ljg5NjUgNDIuMjU4OGMxNS42MTEzIC0xNy4wNzgxIDM3LjgwNzYgLTI3Ljk3NzUgNjIuNjU5MiAtMjcuOTc3NQpzNDcuMDQ3OSAxMC44OTk0IDYyLjY1OTIgMjcuOTc3NXpNNDgzLjU1NiAzMDQuNzljMCAtMjUuOTM3NSAtMjAuMzI2MiAtNDcuMDQgLTQ1LjMwOTYgLTQ3LjA0aC0xNS4xMDM1di0zMi45NDQzYy00LjkzNjUyIDAuODY5MTQxIC05LjkzMTY0IDEuNTgzOTggLTE1LjEwMzUgMS41ODM5OGMtMTAuODM1OSAwIC0yMS4wODg5IC0yLjMxMTUyIC0zMC43MzM0IC01Ljk0NTMxbC0zNS45MzI2IDM3LjMwNTdoLTE3MC43NDZsLTM1LjkzMjYgLTM3LjMwNTcKYy05LjY0MDYyIDMuNjMzNzkgLTE5Ljg5NzUgNS45NDUzMSAtMzAuNzMzNCA1Ljk0NTMxYy01LjE3MTg4IDAgLTEwLjE2NyAtMC43MTQ4NDQgLTE1LjEwMzUgLTEuNTgzOTh2MzIuOTQ0M2gtMTUuMTAzNWMtMjQuOTgzNCAwIC00NS4zMDk2IDIxLjEwMjUgLTQ1LjMwOTYgNDcuMDR2OTYuMTY5OWMwIDI1LjkzNzUgMjAuMzI2MiA0Ny4wNCA0NS4zMDk2IDQ3LjA0aDM2NC40OTJjMjQuOTgzNCAwIDQ1LjMwOTYgLTIxLjEwMjUgNDUuMzA5NiAtNDcuMDQKdi05Ni4xNjk5ek0zNzcuODMzIDMwNC43OWM4LjM0NTcgMCAxNS4xMDM1IDcuMDE0NjUgMTUuMTAzNSAxNS42Nzk3cy02Ljc1NzgxIDE1LjY3OTcgLTE1LjEwMzUgMTUuNjc5N2gtMjQzLjY2NmMtOC4zNDU3IDAgLTE1LjEwMzUgLTcuMDE0NjUgLTE1LjEwMzUgLTE1LjY3OTdzNi43NTc4MSAtMTUuNjc5NyAxNS4xMDM1IC0xNS42Nzk3aDI0My42NjZ6TTQwOC4wMzkgMzY5LjYwMQpjOC4zNDY2OCAwIDE1LjEwMzUgNy4wMTQ2NSAxNS4xMDM1IDE1LjY3OTdzLTYuNzU2ODQgMTUuNjc5NyAtMTUuMTAzNSAxNS42Nzk3aC0zMDQuMDc4Yy04LjM0NjY4IDAgLTE1LjEwMzUgLTcuMDE0NjUgLTE1LjEwMzUgLTE1LjY3OTdzNi43NTY4NCAtMTUuNjc5NyAxNS4xMDM1IC0xNS42Nzk3aDMwNC4wNzh6IiAvPgogICAgPGdseXBoIGdseXBoLW5hbWU9InVuaUYxMUMiIHVuaWNvZGU9IiYjeGYxMWM7IiAKZD0iTTUxMC4zOTEgMjIwLjQyNmMyLjE2Nzk3IC0xOS4zODk2IDIuMTQwNjIgLTM4Ljk2ODggLTAuMTE0MjU4IC01OC4yMTg4Yy0wLjg4OTY0OCAtNy40OTMxNiAtNy42NDM1NSAtMTMuMTQ2NSAtMTQuOTgzNCAtMTIuNTUwOGwtMy4xNjYwMiAwLjA4MjAzMTJjLTIyLjYyOTkgMCAtNDMuNjY4OSAtMTMuOTQwNCAtNTIuMzcxMSAtMzQuNzAzMWMtOS4yNTU4NiAtMjIuMDU2NiAtMy40NzQ2MSAtNDcuNzI4NSAxNC4zNDM4IC02My44NjQzCmM1LjU4NjkxIC01LjA1NTY2IDYuMjUzOTEgLTEzLjU4OTggMS41MjkzIC0xOS40NTYxYy0xMi4xNzQ4IC0xNS4xNjMxIC0yNi4xMDA2IC0yOC45MzY1IC00MS40MTUgLTQwLjk0NjNjLTUuODk1NTEgLTQuNTU0NjkgLTE0LjI1ODggLTMuODg5NjUgLTE5LjMyMDMgMS42MTYyMWMtMTUuMjMwNSAxNi43MzE0IC00Mi4zMzMgMjMuMDI5MyAtNjMuNDAwNCAxNC40MjQ4CmMtMjEuNzkzOSAtOC45MjM4MyAtMzUuODg0OCAtMzAuODY3MiAtMzUuMDI2NCAtNTQuNTkxOGMwLjI1IC03LjQ0OTIyIC01LjI1IC0xMy44NDI4IC0xMi42NzM4IC0xNC42NjExYy05LjM2NzE5IC0xLjA0MTk5IC0xOC43MzU0IC0xLjU1NjY0IC0yOC4xMDI1IC0xLjU1NjY0Yy05Ljg2ODE2IDAgLTE5LjcwOSAwLjU2OTMzNiAtMjkuNDkxMiAxLjcxMDk0Yy03LjQ3NzU0IDAuODYwMzUyIC0xMy4wMDc4IDcuNDE5OTIgLTEyLjU2MzUgMTQuOTU0MQpjMS4zNjAzNSAyMy45ODgzIC0xMi41MzkxIDQ2LjMyMDMgLTM0LjYzNTcgNTUuNTYzNWMtMjEuMDk1NyA4Ljg1NDQ5IC00OC41MDI5IDIuNTg0OTYgLTYzLjgxOTMgLTE0LjM1NjRjLTUuMDgzOTggLTUuNjAyNTQgLTEzLjYxOTEgLTYuMjY5NTMgLTE5LjQ4NDQgLTEuNTQyOTdjLTE0Ljk1NDEgMTIuMDM2MSAtMjguNjAzNSAyNS44MjIzIC00MC40OTkgNDAuOTcyNwpjLTQuNjE1MjMgNS44NTA1OSAtMy45MTk5MiAxNC4yNTk4IDEuNTU2NjQgMTkuMjkxYzE3LjUxMDcgMTYuMDUwOCAyMy4yNjM3IDQxLjQ4MzQgMTQuMzE0NSA2My4zMDI3Yy04Ljk3ODUyIDIxLjg0ODYgLTMwLjM4MDkgMzUuNjUwNCAtNTIuNjczOCAzNS4wODExYy03LjMzNTk0IDAgLTE1Ljk4MjQgNS4zNjAzNSAtMTYuNzg3MSAxMi41ODc5Yy0yLjE2Nzk3IDE5LjM4OTYgLTIuMTQwNjIgMzguOTg2MyAwLjExMTMyOCA1OC4yMTk3CmMwLjg4NzY5NSA3LjM3OTg4IDYuNjY4OTUgMTIuODI4MSAxNC43ODYxIDEyLjU2NzRjMjUuMDE0NiAwIDQ2Ljg2NTIgMTMuNTYxNSA1NS43MDIxIDM0LjYwNDVjOS4yNTY4NCAyMi4wNzEzIDMuNTAzOTEgNDcuNzM5MyAtMTQuMzEzNSA2My44NzVjLTUuNTU5NTcgNS4wNTc2MiAtNi4yNTQ4OCAxMy41NzgxIC0xLjUyODMyIDE5LjQ1N2MxMi4xMTgyIDE1LjEyMjEgMjYuMDcyMyAyOC45MDcyIDQxLjQxNSA0MC45NDQzCmM1Ljg2MzI4IDQuNTcwMzEgMTQuMjg2MSAzLjg5MjU4IDE5LjMxODQgLTEuNjI1YzE1LjIyOTUgLTE2Ljc0OCA0Mi40NDYzIC0yMi45NzU2IDYzLjM3MjEgLTE0LjQyNzdjMjEuODIyMyA4LjkzNzUgMzUuODg4NyAzMC44ODE4IDM1LjA1NDcgNTQuNjIwMWMtMC4yNSA3LjQzNTU1IDUuMjUzOTEgMTMuODI4MSAxMi42NzU4IDE0LjY0NzVjMTkuMTc5NyAyLjEyNzkzIDM4LjQ2OTcgMi4wNTk1NyA1Ny41OTE4IC0wLjE1MjM0NApjNy41MDc4MSAtMC44NzQwMjMgMTMuMDExNyAtNy40MjE4OCAxMi41NjQ1IC0xNC45NTIxYy0xLjM2MDM1IC0yMy45NzQ2IDEyLjUzODEgLTQ2LjMwOTYgMzQuNTc5MSAtNTUuNTgxMWMyMS4xNzk3IC04LjgzNjkxIDQ4LjU4ODkgLTIuNTU2NjQgNjMuODQ4NiAxNC4zNDI4YzUuMDU5NTcgNS42MDI1NCAxMy42MjExIDYuMjU0ODggMTkuNDU2MSAxLjU3MjI3YzE0LjkwMDQgLTExLjkzNzUgMjguNTQ3OSAtMjUuNzEgNDAuNTU1NyAtNDAuOTU4CmM0LjYxNTIzIC01Ljg1MDU5IDMuOTQ3MjcgLTE0LjI3MjUgLTEuNTU2NjQgLTE5LjMwMzdjLTE3LjQ4NDQgLTE2LjAzODEgLTIzLjIzNTQgLTQxLjQ4NTQgLTE0LjMxNjQgLTYzLjMwNDdjOC43MzA0NyAtMjEuMzAzNyAyOS41NDg4IC0zNS4wNzkxIDUzLjAwODggLTM1LjA3OTFjNy4yNTY4NCAwIDE1LjY0ODQgLTUuNDA4MiAxNi40NTggLTEyLjYwNTV6TTI1Ni40MDUgMTA2LjA2OApjNDcuMDg1OSAwIDg1LjM4NzcgMzguMzAxOCA4NS4zODc3IDg1LjM4OTZjMCA0Ny4wODU5IC0zOC4zMDE4IDg1LjM5MDYgLTg1LjM4NzcgODUuMzkwNmMtNDcuMDg4OSAwIC04NS4zOTA2IC0zOC4zMDQ3IC04NS4zOTA2IC04NS4zOTA2YzAgLTQ3LjA4NzkgMzguMzAxOCAtODUuMzg5NiA4NS4zOTA2IC04NS4zODk2eiIgLz4KICAgIDxnbHlwaCBnbHlwaC1uYW1lPSJ1bmlGMTA4IiB1bmljb2RlPSImI3hmMTA4OyIgCmQ9Ik0zODEuNzExIDE5Mi4wODljMTUuNzQzMiAtMTIuNTUxOCAxNi4yMzI0IC0zMy4zMDU3IDEuMDkyNzcgLTQ2LjM1NzRsLTk4Ljg3NCAtODEuOTYxOWMtMC4wMDk3NjU2MiAtMC4wMDg3ODkwNiAtMC4wMjE0ODQ0IC0wLjAxNzU3ODEgLTAuMDMxMjUgLTAuMDI1MzkwNmMtMTUuNDU0MSAtMTIuNzk2OSAtNDAuNDk1MSAtMTIuNzg1MiAtNTUuOTMxNiAwLjAyNTM5MDZsLTk3LjA5NDcgODAuNDg2MwpjLTE1LjM5MDYgMTIuMTg5NSAtMTcuMDE5NSAzMi4wOTg2IC0zLjc1Njg0IDQ1Ljg5OTRjMC43MDIxNDggMC42ODY1MjMgMS40Mzg0OCAxLjM0ODYzIDIuMjA2MDUgMS45ODQzOGMxNS40NTEyIDEyLjc5ODggNDAuNDkzMiAxMi43OSA1NS45MzE2IC0wLjAxNzU3ODFsMTIuNDU4IC0xMC4zMjcxbDE5Ljc3NTQgLTI5LjM0Mjh2MjYyLjI4Yy0wLjAzNjEzMjggMi4wMjgzMiAwLjE1NTI3MyA0LjA1NTY2IDAuNTcyMjY2IDYuMDU0NjkKYzMuNzE0ODQgMTcuODQxOCAyNC4xNzU4IDI5LjgwOTYgNDUuNzAwMiAyNi43Mjk1YzE5LjcwMTIgLTMuMjg2MTMgMzMuNjEwNCAtMTcuOTQyNCAzMi44MjYyIC0zNC41ODc5di0yNjAuNDc2bDEzLjY0NTUgMjAuOTgyNGMzLjYzMTg0IDUuNjAzNTIgOC4yMjg1MiAxMC43MzkzIDEzLjY0NDUgMTUuMjQ1MWwyLjU3MDMxIDIuMTMwODZjMTMuNzczNCAxMS4yMDIxIDM1LjExMjMgMTIuOTA5MiA1MS4yMTY4IDQuMDk4NjMKYzEuNDIzODMgLTAuODY1MjM0IDIuNzc3MzQgLTEuODA3NjIgNC4wNDg4MyAtMi44MjEyOXpNNTExLjk2NiAxNi41NzAzYzAuMDI0NDE0MSAtMC44MTU0MyAwLjAzNjEzMjggLTEuNjMxODQgMC4wMzQxNzk3IC0yLjQ0ODI0Yy0wLjExMzI4MSAtNDMuMjQ1MSAtMzcuMzM1OSAtNzguMjEzOSAtODMuMTM3NyAtNzguMTA3NGgtMzQ1LjcyM2MtMS4zMjUyIC0wLjAyNDQxNDEgLTIuNjUwMzkgLTAuMDE4NTU0NyAtMy45NzU1OSAwLjAxODU1NDcKYy00NS4wMjkzIDEuMjUzOTEgLTgwLjQ1NjEgMzYuNzM1NCAtNzkuMTI3OSA3OS4yNDh2MTk0LjYyNGMyLjIxODc1IDUwLjI2NzYgMzQuMTI4OSA4MC41NTY2IDg1LjMyMTMgODAuNTU2Nmg0Mi42NTUzYzE4Ljg0ODYgMCAzNC4xMjg5IC0xNC40MjY4IDM0LjEyODkgLTMyLjIyMjdzLTE1LjI4MDMgLTMyLjIyMjcgLTM0LjEyODkgLTMyLjIyMjdoLTQyLjY1NTNjLTExLjI2MTcgMCAtMTcuMDY0NSAwIC0xNy4wNjQ1IC0xNy41NjE1di0xOTEuODg1CmMxLjE5NTMxIC0xMS40Mzg1IDIuMzg5NjUgLTE0Ljk4MzQgMTguMjU4OCAtMTYuMTExM2gzMzguODk3YzE1LjAxNjYgMS40NTAyIDE4LjI1ODggNS4zMTczOCAxOC4yNTg4IDE3LjU2MTV2MTkwLjU5NmMtMS41MzUxNiAxNS4zMDU3IC00LjA5NDczIDE3LjIzOTMgLTE4LjU5OTYgMTcuMjM5M2gtMzkuNzUzOWMtMTguODQ4NiAwIC0zNC4xMjg5IDE0LjQyNjggLTM0LjEyODkgMzIuMjIyN3MxNS4yODAzIDMyLjIyMjcgMzQuMTI4OSAzMi4yMjI3Cmg0MS4yOWM1Mi4zODY3IC0yLjA5NDczIDgyLjA3OTEgLTMwLjEyNzkgODUuMzIxMyAtNzguNDYxOXYtMS45MzM1OXYtMTkzLjMzNXoiIC8+CiAgICA8Z2x5cGggZ2x5cGgtbmFtZT0idW5pRjExMCIgdW5pY29kZT0iJiN4ZjExMDsiIApkPSJNMjU2IDMwNS43NzdjMzEuMzc1IDAgNTYuODkwNiAtMjUuNTEzNyA1Ni44OTA2IC01Ni44ODg3cy0yNS41MTU2IC01Ni44ODg3IC01Ni44OTA2IC01Ni44ODg3cy01Ni44ODg3IDI1LjUxMzcgLTU2Ljg4ODcgNTYuODg4N3MyNS41MTM3IDU2Ljg4ODcgNTYuODg4NyA1Ni44ODg3ek0yODAuMjc5IDQxMC4xMjljNS41NTQ2OSAtNi4yOTg4MyA1LjU1NDY5IC0xNi41MDU5IDAgLTIyLjgwMjcKYy01LjU1NjY0IC02LjI5ODgzIC0xNC42NTIzIC02LjI5ODgzIC0yMC4yMDcgMGwtNC4xNjYwMiA0LjcyNDYxdi00MS43MDMxYzAgLTguOTEzMDkgLTYuMjY1NjIgLTE2LjEyNSAtMTQuMTI3IC0xNi4xMjVzLTE0LjIyMjcgNy4yMTE5MSAtMTQuMjIyNyAxNi4xMjV2NDEuNzAzMWwtNC4xNjYwMiAtNC43MjQ2MWMtNS41NTY2NCAtNi4yOTg4MyAtMTQuNTU2NiAtNi4yOTg4MyAtMjAuMTExMyAwCmMtNS41NTY2NCA2LjI5ODgzIC01LjU1NjY0IDE2LjUwMzkgMCAyMi44MDI3bDI4LjQ0MzQgMzMuMzI4MWM0LjU4MjAzIDUuMTk1MzEgMTQuMDU4NiA2Ljg2MjMgMjAuMTExMyAwek0yODAuMjc5IC0zLjI0MjE5YzUuNTU0NjkgLTYuMjg5MDYgNS41NTQ2OSAtMTYuNDgwNSAwLjAwMTk1MzEyIC0yMi43Njk1bC0yOC40NDczIC0zMy4yNzQ0Yy01LjQzMTY0IC02LjE0OTQxIC0xNC40NDE0IC02LjQxODk1IC0yMC4xMTEzIDAKbC0yOC40NDM0IDMzLjI3NDRjLTUuNTU2NjQgNi4yODkwNiAtNS41NTY2NCAxNi40Nzk1IDAgMjIuNzY5NWM1LjU1NDY5IDYuMjg5MDYgMTQuNTU0NyA2LjI4OTA2IDIwLjExMTMgMGw0LjE2NjAyIC00LjcxODc1djQxLjYzNjdjMCA4LjkwMDM5IDYuMzYxMzMgMTYuMTAxNiAxNC4yMjI3IDE2LjEwMTZzMTQuMTI3IC03LjIwMTE3IDE0LjEyNyAtMTYuMTAxNnYtNDEuNjM2N2w0LjE2NjAyIDQuNzE4NzUKYzUuNTU0NjkgNi4yODkwNiAxNC42NTA0IDYuMjg5MDYgMjAuMjA3IDB6TTk3LjY1MjMgMjIwLjQ0NGM4LjkxNDA2IDAgMTYuMTI3IC02LjM2MjMgMTYuMTI3IC0xNC4yMjM2cy03LjIxMjg5IC0xNC4yMjE3IC0xNi4xMjcgLTE0LjIyMDdoLTQxLjcwMTJsNC43MjQ2MSAtNC4xNjY5OWM2LjI5ODgzIC01LjU1NTY2IDYuMjk4ODMgLTE0LjU1NTcgMCAtMjAuMTEwNGMtNi4yOTg4MyAtNS41NTY2NCAtMTYuNTAzOSAtNS41NTY2NCAtMjIuODAyNyAwCmwtMzMuMzI2MiAyOC40NDM0Yy01LjE5OTIyIDQuNTgzOTggLTYuODY1MjMgMTQuMDU3NiAwIDIwLjExMTNsMzMuMzI2MiAyOC40NDUzYzYuMjk4ODMgNS41NTQ2OSAxNi41MDM5IDUuNTU0NjkgMjIuODAyNyAwYzYuMjk4ODMgLTUuNTU1NjYgNi4yOTg4MyAtMTQuNTU2NiAwIC0yMC4xMTEzbC00LjcyNDYxIC00LjE2Njk5aDQxLjcwMTJ6TTUwNy40MiAyMTYuMjc3YzYuNjQwNjIgLTUuODU4NCA1LjU1MDc4IC0xNS4yMTI5IDAgLTIwLjExMTMKbC0zMy4zMTY0IC0yOC40NDM0Yy02LjI5Njg4IC01LjU1NjY0IC0xNi40OTggLTUuNTU2NjQgLTIyLjc5NDkgMGMtNi4yOTY4OCA1LjU1NDY5IC02LjI5Njg4IDE0LjU1NDcgMCAyMC4xMTA0bDQuNzIyNjYgNC4xNjY5OWgtNDEuNjg3NWMtOC45MTAxNiAwIC0xNi4xMjExIDYuMzYwMzUgLTE2LjEyMTEgMTQuMjIyN2MwIDcuODYxMzMgNy4yMTA5NCAxNC4yMjE3IDE2LjEyMTEgMTQuMjIxN2g0MS42ODc1bC00LjcyMjY2IDQuMTY2OTkKYy02LjI5Njg4IDUuNTU0NjkgLTYuMjk2ODggMTQuNTU1NyAwIDIwLjExMTNjNi4yOTY4OCA1LjU1NDY5IDE2LjQ5OCA1LjU1NDY5IDIyLjc5NDkgMHpNMTM3LjcwNSA3My43MDUxYzYuMDIzNDQgLTYuMDIxNDggNi4wMjM0NCAtMTUuNzc3MyAwLjAwMTk1MzEyIC0yMS43OTg4bC0yOC4xODM2IC0yOC4xODM2aDYuMzgyODFjOC41MjE0OCAwIDE1LjQxOCAtNi44OTU1MSAxNS40MTggLTE1LjQxOApjMCAtOC41MjE0OCAtNi44OTY0OCAtMTUuNDE2IC0xNS40MTggLTE1LjQxNmgtNDMuNTk5NmMtNS41ODc4OSAwIC0xMi4wNjg0IDQuMjMyNDIgLTE0LjI1NzggOS42NjMwOWMtMC43MjA3MDMgMS43ODIyMyAtMS4xNTgyIDMuNzEwOTQgLTEuMTU4MiA1Ljc1MjkzdjQzLjYwMTZjMCA4LjUyMTQ4IDYuODk0NTMgMTUuNDE2IDE1LjQxNiAxNS40MTZzMTUuNDE2IC02Ljg5NDUzIDE1LjQxNiAtMTUuNDE2di02LjM4NDc3bDI4LjE4MzYgMjguMTgzNgpjNi4wMjE0OCA2LjAyMjQ2IDE1Ljc3NzMgNi4wMjI0NiAyMS43OTg4IDB6TTQzOS42OTUgMzkxLjExMWM4Ljk3NjU2IDAgMTUuNDE2IC03Ljg4NDc3IDE1LjQxNiAtMTUuNDE3di00My42MDA2YzAgLTguNTIxNDggLTYuODk0NTMgLTE1LjQxNiAtMTUuNDE2IC0xNS40MTZzLTE1LjQxNiA2Ljg5NDUzIC0xNS40MTYgMTUuNDE2djYuMzgzNzlsLTI4LjE4MzYgLTI4LjE4MzYKYy02LjAyMTQ4IC02LjAyMjQ2IC0xNS43NzkzIC02LjAyMjQ2IC0yMS43OTg4IDBjLTYuMDIzNDQgNi4wMjI0NiAtNi4wMjM0NCAxNS43NzgzIDAgMjEuNzk5OGwyOC4xODE2IDI4LjE4MzZoLTYuMzgyODFjLTguNTIxNDggMCAtMTUuNDE2IDYuODk0NTMgLTE1LjQxNiAxNS40MTdjMCA4LjUyMTQ4IDYuODk0NTMgMTUuNDE3IDE1LjQxNiAxNS40MTdoNDMuNTk5NnpNNDM5LjY5NSA2Ny4zMjIzCmM4LjUyMTQ4IDAgMTUuNDE2IC02Ljg5NDUzIDE1LjQxNiAtMTUuNDE2di00My42MDE2YzAgLTguOTc0NjEgLTcuODgyODEgLTE1LjQxNiAtMTUuNDE2IC0xNS40MTZoLTQzLjYwMTZjLTguNTE5NTMgMCAtMTUuNDE2IDYuODk0NTMgLTE1LjQxNiAxNS40MTZjMCA4LjUyMjQ2IDYuODk2NDggMTUuNDE4IDE1LjQxNiAxNS40MThoNi4zODQ3N2wtMjguMTgzNiAyOC4xODM2Yy02LjAyMTQ4IDYuMDIxNDggLTYuMDIxNDggMTUuNzc3MyAwIDIxLjc5ODgKYzYuMDIxNDggNi4wMjI0NiAxNS43NzkzIDYuMDIyNDYgMjEuNzk4OCAwbDI4LjE4NTUgLTI4LjE4MzZ2Ni4zODQ3N2MwIDguNTIxNDggNi44OTQ1MyAxNS40MTYgMTUuNDE2IDE1LjQxNnpNMTM3LjcwNyAzMzIuMDk0YzYuMDIxNDggLTYuMDIxNDggNi4wMjE0OCAtMTUuNzc3MyAwIC0yMS43OTk4cy0xNS43NzczIC02LjAyMjQ2IC0yMS44MDA4IDBsLTI4LjE4MzYgMjguMTgzNnYtNi4zODM3OQpjMCAtOC41MjE0OCAtNi44OTQ1MyAtMTUuNDE2IC0xNS40MTYgLTE1LjQxNnMtMTUuNDE2IDYuODk0NTMgLTE1LjQxNiAxNS40MTZ2NDMuNjAwNmMwIDcuMDQ4ODMgNi4xNDA2MiAxNS40MTcgMTUuNDE2IDE1LjQxN2g0My41OTk2YzguNTIxNDggMCAxNS40MTggLTYuODk1NTEgMTUuNDE4IC0xNS40MTdjMCAtOC41MjI0NiAtNi44OTY0OCAtMTUuNDE3IC0xNS40MTggLTE1LjQxN2gtNi4zODI4MXpNMzE5LjIyMyAxNjMuNTU1CmMxMy42NTIzIC0xNS4xMzA5IDIyLjEwOTQgLTM0Ljk1MzEgMjIuMTExMyAtNTYuODg4N3YtMTQuMjIxN2MwIC03Ljg2MjMgLTYuMjY5NTMgLTE0LjIyMTcgLTE0LjEzNDggLTE0LjIyMTdoLTE0Mi4zMDFjLTcuODY3MTkgMCAtMTQuMjMwNSA2LjM1OTM4IC0xNC4yMzA1IDE0LjIyMTd2MTQuMjIxN2MwIDIxLjkzNTUgOC41NTI3MyA0MS43NTc4IDIyLjIwNyA1Ni44ODg3CmMxNS42Mzg3IC0xNy4zMzAxIDM4LjAzNzEgLTI4LjQ0MzQgNjMuMTcxOSAtMjguNDQzNGMyNS4xMzg3IDAgNDcuNTM1MiAxMS4xMTMzIDYzLjE3NTggMjguNDQzNHoiIC8+CiAgICA8Z2x5cGggZ2x5cGgtbmFtZT0idW5pRjExMiIgdW5pY29kZT0iJiN4ZjExMjsiIApkPSJNMjU2IDM1OS4yNzljMTEyLjkzIDAgMjEwLjI2IC02Ni41NzEzIDI1NC45NyAtMTYyLjYzOGMxLjM3NDAyIC0yLjk2NjggMS4zNzQwMiAtNi4zNzIwNyAwIC05LjI4MzJjLTQ0LjcxIC05Ni4wNjY0IC0xNDIuMDQxIC0xNjIuNjM4IC0yNTQuOTcgLTE2Mi42MzhzLTIxMC4yNiA2Ni41NzEzIC0yNTQuOTcgMTYyLjYzOGMtMS4zNzQwMiAyLjk2NjggLTEuMzc0MDIgNi4zNzIwNyAwIDkuMjgzMgpjNDQuNzEgOTYuMDY2NCAxNDIuMDQgMTYyLjYzOCAyNTQuOTcgMTYyLjYzOHpNMjU2IDc2LjU3MDNjNjMuNzE0OCAwIDExNS40MDEgNTEuNjg3NSAxMTUuNDAxIDExNS40MDJzLTUxLjYzMTggMTE1LjQwMSAtMTE1LjQwMSAxMTUuNDAxYy02My43MTQ4IDAgLTExNS40MDEgLTUxLjYzMTggLTExNS40MDEgLTExNS40MDFjMCAtNjMuNzE0OCA1MS42MzE4IC0xMTUuNDAyIDExNS40MDEgLTExNS40MDJ6TTE4Mi4xMjMgMTkyLjAyNwpjMCA0OS4yNTEzIDI0LjYyNTcgNzMuODc3IDczLjg3NyA3My44NzdjNDkuMjUxMyAwIDczLjg3NyAtMjQuNjI1NyA3My44NzcgLTczLjg3N2MwIC00OS4yNTA3IC0yNC42MjU2IC03My44NzYgLTczLjg3NyAtNzMuODc2Yy00OS4yNTEzIC03LjYyOTM5ZS0wNiAtNzMuODc3IDI0LjYyNTMgLTczLjg3NyA3My44NzZ6IiAvPgogICAgPGdseXBoIGdseXBoLW5hbWU9InVuaUYxMDQiIHVuaWNvZGU9IiYjeGYxMDQ7IiAKZD0iTTUwOS44MTIgNDMzLjgxNmMtNC4xMTcxOSA5LjQ1MTE3IC0xMS4yNTc4IDE0LjE3NzcgLTIxLjQzNzUgMTQuMTgzNmgtNDY0Ljc2MmMtMTAuMTY4IDAgLTE3LjMxMjUgLTQuNzMyNDIgLTIxLjQyNTggLTE0LjE4MzZjLTQuMTE3MTkgLTkuOTM1NTUgLTIuNDI1NzggLTE4LjQyNTggNS4wODIwMyAtMjUuNDUzMWwxNzkuMDIgLTE3OS4yNzF2LTE3Ni43MjVjMCAtNi4zMTA1NSAyLjI5Njg4IC0xMS43NTg4IDYuODk4NDQgLTE2LjM3MTEKbDkyLjk0NTMgLTkzLjA4M2M0LjM1OTM4IC00LjYwMjU0IDkuODA0NjkgLTYuOTEzMDkgMTYuMzQ3NyAtNi45MTMwOWMyLjkwMjM0IDAgNS45MjU3OCAwLjYwOTM3NSA5LjA3MDMxIDEuODIxMjljOS40NDkyMiA0LjEyNDAyIDE0LjE2OCAxMS4yNzI1IDE0LjE2OCAyMS40NTQxdjI2OS44MTZsMTc5LjAxMiAxNzkuMjdjNy41MDc4MSA3LjAyNzM0IDkuMTk1MzEgMTUuNTE1NiA1LjA4MjAzIDI1LjQ1NTF6IiAvPgogICAgPGdseXBoIGdseXBoLW5hbWU9InVuaUYxMjEiIHVuaWNvZGU9IiYjeGYxMjE7IiAKZD0iTTEzOC4wOTggMy4zMDE3NmwtMTI2LjU4NSAxMjYuNDhjLTE1LjM1MDYgMTUuMzQ5NiAtMTUuMzUwNiA0MC4yNjc2IDAgNTUuNjE3MmwxOTAuNTA5IDE5MC40MDRjMTAuNjE5MSAxMC43MjM2IDI1LjEyNzkgMTYuNjExMyA0MC4yNjc2IDE2LjQwMTRsMTE0LjA3NCAtMS41NzcxNWMyMy4zMzk4IC0wLjMxNTQzIDQyLjE2MDIgLTE5LjEzNTcgNDIuNDc1NiAtNDIuNDc1NnYtMC4zMTU0MwpjNS4yNTY4NCAtMS44OTI1OCAxMC42MTgyIC0zLjg5MDYyIDE1Ljk4MDUgLTYuMDk4NjNjMjkuMTIzIC0xMi4wODk4IDU0Ljc3NjQgLTI4LjM4NjcgNzIuNDM5NSAtNDYuMDQ5OGwwLjk0NjI4OSAtMC45NDYyODljMjguNDkyMiAtMjkuMDE3NiAyNS4zMzc5IC01MS4wOTY3IDIwLjkyMjkgLTYxLjYxMDRjLTQuNDE2MDIgLTEwLjYxOTEgLTE3Ljg3NCAtMjguMzg2NyAtNTguNDU3IC0yOC41OTc3CmMtMTYuNDAxNCAtMC4xMDQ0OTIgLTM1LjAxMDcgMi43MzM0IC01NC4xNDU1IDguMDk1N2MtMi43MzM0IC02LjkzODQ4IC02LjkzODQ4IC0xMy40NTcgLTEyLjQwNjIgLTE4LjkyNDhsLTE5MC40MDMgLTE5MC40MDNjLTE1LjM1MDYgLTE1LjM1MDYgLTQwLjI2NzYgLTE1LjM1MDYgLTU1LjYxODIgMHpNMjg0Ljg3IDMyNC4wNzVjLTEyLjcyMTcgLTEyLjcyMDcgLTEyLjcyMTcgLTMzLjMyODEgLTAuMTA1NDY5IC00NS45NDQzCmMxMi43MjE3IC0xMi43MjE3IDMzLjMyODEgLTEyLjcyMTcgNDYuMDQ5OCAwYzAuNDIwODk4IDAuNTI1MzkxIDAuOTQ2Mjg5IDEuMDUwNzggMS4zNjcxOSAxLjU3NzE1YzEyLjkzMTYgLTkuNTY3MzggMjguOTEzMSAtMTguMzk5NCA0NS45NDUzIC0yNS41NDg4YzU4LjI0NjEgLTI0LjI4NjEgOTYuNjIxMSAtMTYuNzE2OCAxMDAuMTk1IC04LjIwMDJjMS4xNTYyNSAyLjczMzQgLTAuMzE1NDMgMTEuNjY5OSAtMTMuOTgzNCAyNS42NTMzCmwtMC42MzA4NTkgMC42MzA4NTljLTE0LjQwMzMgMTQuNDAzMyAtMzYuNzk3OSAyOC40OTIyIC02MS43MTQ4IDM4LjkwMDRjLTAuOTQ2Mjg5IDAuNDIwODk4IC0xLjc4ODA5IDAuNzM2MzI4IC0yLjczNDM4IDEuMTU3MjNsMC42MzA4NTkgLTQ1Ljg0MDhjLTUuMjU2ODQgMS43ODgwOSAtMTAuNzIzNiAzLjg5MDYyIC0xNi40MDE0IDYuMjAzMTJjLTE1Ljk4MDUgNi42MjQwMiAtMzAuOTEwMiAxNC44MjUyIC00My40MjE5IDIzLjY1NjIKYzEuNDcyNjYgOS43NzgzMiAtMS41NzYxNyAyMC4xODY1IC05LjE0NjQ4IDI3Ljc1NTljLTEyLjcyMTcgMTIuNzIxNyAtMzMuMzI4MSAxMi43MjE3IC00Ni4wNDk4IDB6IiAvPgogICAgPGdseXBoIGdseXBoLW5hbWU9InVuaUYxMUEiIHVuaWNvZGU9IiYjeGYxMWE7IiAKZD0iTTUwOS44MTMgNDMzLjQ2N2M0LjExNDI2IC05LjkyNDggMi40MjU3OCAtMTguNDAwNCAtNS4wODMwMSAtMjUuNDE4bC0xNzkuMDEgLTE3OS4wMTF2LTI2OS40MjVjMCAtMTAuMTY2IC00LjcyMjY2IC0xNy4zMDQ3IC0xNC4xNjggLTIxLjQyMjljLTMuMTQ3NDYgLTEuMjA5OTYgLTYuMTcxODggLTEuODE5MzQgLTkuMDc0MjIgLTEuODE5MzRjLTYuNTM4MDkgMCAtMTEuOTgyNCAyLjMwNzYyIC0xNi4zNDM4IDYuOTAzMzIKbC05Mi45NTAyIDkyLjk0ODJjLTQuNTk3NjYgNC42MDY0NSAtNi44OTg0NCAxMC4wNDU5IC02Ljg5ODQ0IDE2LjM0Nzd2MTc2LjQ2OWwtMTc5LjAxNiAxNzkuMDExYy03LjUwNTg2IDcuMDE3NTggLTkuMTk4MjQgMTUuNDk2MSAtNS4wODU5NCAyNS40MTdjNC4xMTYyMSA5LjQzODQ4IDExLjI1OTggMTQuMTYzMSAyMS40MjQ4IDE0LjE2MzFoNDY0Ljc3YzEwLjE3OTcgLTAuMDA0ODgyODEgMTcuMzEyNSAtNC43MjQ2MSAyMS40MzQ2IC0xNC4xNjMxegoiIC8+CiAgICA8Z2x5cGggZ2x5cGgtbmFtZT0idW5pRjExOSIgdW5pY29kZT0iJiN4ZjExOTsiIApkPSJNMzcxLjkwNSA0MDQuMDgxdi02OS4zNDQ3aC0zMjMuNjEydjY5LjM0NDdoOTYuMzcyMXY0My45MTg5aDEzMC44Njh2LTQzLjkxODloOTYuMzcyMXpNMTc5LjMzOCA0MTMuMzI3di05LjI0NjA5aDYxLjUyMjV2OS4yNDYwOWgtNjEuNTIyNXpNMzQxLjExMiAyMDAuMzFjLTc4LjkyOTcgLTQuMDA4NzkgLTE0MS45MTIgLTY5LjQ4MzQgLTE0MS45MTIgLTE0OS4zOTNjMCAtMzEuOTk2MSAxMC4xMDI1IC02MS42NzQ4IDI3LjI3ODMgLTg2LjAyMzQKaC0xMzEuOTU1bC0yMS45Nzc1IDMzNS4xN2gyNzUuMTA3ek0zNDguNzkgMTY1LjgzMmM2My4zNjUyIDAgMTE0LjkxNyAtNTEuNTQ5OCAxMTQuOTE3IC0xMTQuOTE1cy01MS41NTE4IC0xMTQuOTE3IC0xMTQuOTE3IC0xMTQuOTE3cy0xMTQuOTE3IDUxLjU1MjcgLTExNC45MTcgMTE0LjkxN3M1MS41NTE4IDExNC45MTUgMTE0LjkxNyAxMTQuOTE1ek00MTEuNDA0IDEyLjgxOTNsLTM4LjA5NzcgMzguMDk3N2wzOC4wOTc3IDM4LjA5NjcKbC0yNC41MTc2IDI0LjUxNzZsLTM4LjA5NjcgLTM4LjA5NzdsLTM4LjA5NzcgMzguMDk3N2wtMjQuNTE2NiAtMjQuNTE3NmwzOC4wOTY3IC0zOC4wOTY3bC0zOC4wOTY3IC0zOC4wOTc3bDI0LjUxNjYgLTI0LjUxNjZsMzguMDk3NyAzOC4wOTY3bDM4LjA5NjcgLTM4LjA5Njd6IiAvPgogICAgPGdseXBoIGdseXBoLW5hbWU9InVuaUYxMjciIHVuaWNvZGU9IiYjeGYxMjc7IiAKZD0iTTQxMy41MzggMzY5LjIzYzIxLjc1MiAwIDM5LjM4NDggLTE3LjYzMjggMzkuMzgzOCAtMzkuMzg0OHYtMzU0LjQ2MWMwIC0yMS43NTIgLTE3LjYzMTggLTM5LjM4NDggLTM5LjM4NDggLTM5LjM4NDhoLTMxNS4wNzZjLTIxLjc1MiAwIC0zOS4zODM4IDE3LjYzMjggLTM5LjM4MzggMzkuMzg0OHYzNTQuNDYxYzAgMjEuNzUyIDE3LjYzMjggMzkuMzg0OCAzOS4zODQ4IDM5LjM4NDhoNTQuNjcxOQpjNy4yODYxMyA4LjA5NzY2IDE2LjY0NTUgMTQuMjkxIDI3LjM5OTQgMTcuMzg5NmM5LjA3ODEyIDIuNjE1MjMgMTYuMDc0MiA5LjU4NDk2IDE4LjY4NDYgMTguNjYzMWM3LjA5NjY4IDI0LjY2ODkgMjkuODMxMSA0Mi43MTY4IDU2Ljc4MzIgNDIuNzE2OGMyNi45NTEyIDAgNDkuNjg3NSAtMTguMDQ3OSA1Ni43ODIyIC00Mi43MTc4YzIuNjA3NDIgLTkuMDY4MzYgOS41ODU5NCAtMTYuMDQ2OSAxOC42NTQzIC0xOC42NTMzCmMxMC43NjM3IC0zLjA5NjY4IDIwLjEzMjggLTkuMjk1OSAyNy40MjU4IC0xNy4zOTg0aDU0LjY3NDh6TTI1Ni4yNTMgNDA4LjYxNWMtMTAuODc2IDAgLTE5LjY5MjQgLTguODE2NDEgLTE5LjY5MjQgLTE5LjY5MjRjMCAtMTAuODc1IDguODE2NDEgLTE5LjY5MjQgMTkuNjkyNCAtMTkuNjkyNHMxOS42OTI0IDguODE3MzggMTkuNjkyNCAxOS42OTI0YzAgMTAuODc2IC04LjgxNjQxIDE5LjY5MjQgLTE5LjY5MjQgMTkuNjkyNHoKTTQxMy41MzggLTI0LjYxNTJ2MzU0LjQ2MWgtMzkuMzgzOHYtMzkuMzg0OGgtMjM2LjMwOHYzOS4zODQ4aC0zOS4zODQ4di0zNTQuNDYxaDMxNS4wNzZ6TTMzMC44OTYgMjE1Ljg0M2wyNy44NTA2IC0yNy44NTA2bC0xMzcuMzU0IC0xMzcuMzUybC03My41NDc5IDcyLjU1ODZsMjcuODQ5NiAyNy44NjgybDQ1LjY5ODIgLTQ0LjczMTR6IiAvPgogICAgPGdseXBoIGdseXBoLW5hbWU9InVuaUYxMjMiIHVuaWNvZGU9IiYjeGYxMjM7IiAKZD0iTTQ5LjE0MTYgMjY5LjE0MWMtMTEuODA3NiA3Ljk5OTAyIC0yMi45NTMxIDE5IC0zMy40MjY4IDMzYy0xMC40NzQ2IDEzLjk5OSAtMTUuNzE0OCAyNy4wMDI5IC0xNS43MTQ4IDM5LjAwMmMwIDE0Ljg1NjQgMy45NTIxNSAyNy4yMzczIDExLjg1NTUgMzcuMTQyNmM3LjkwNTI3IDkuOTAzMzIgMTkuMTkxNCAxNC44NTQ1IDMzLjg1NjQgMTQuODU0NWg0MjAuNTcyYzEyLjM3NzkgMCAyMy4wOTM4IC00LjQ3MzYzIDMyLjE0MDYgLTEzLjQyNzcKYzkuMDQ4ODMgLTguOTUxMTcgMTMuNTc1MiAtMTkuNzEzOSAxMy41NzUyIC0zMi4yODcxYzAgLTE1LjA0NTkgLTQuNjY0MDYgLTI5LjQyNzcgLTE0LjAwMjkgLTQzLjEzNjdjLTkuMzMyMDMgLTEzLjcxNDggLTIwLjk0ODIgLTI1LjQzMTYgLTM0Ljg1MjUgLTM1LjE0MTZjLTcxLjYxNjIgLTQ5LjcxNjggLTExNi4xOTIgLTgwLjY2OTkgLTEzMy43MTMgLTkyLjg1ODQKYy0xLjkwNDMgLTEuMzM1OTQgLTUuOTU1MDggLTQuMjM1MzUgLTEyLjE0MzYgLTguNzE1ODJjLTYuMTkyMzggLTQuNDc5NDkgLTExLjMzMyAtOC4wOTg2MyAtMTUuNDI4NyAtMTAuODYwNGMtNC4wOTU3IC0yLjc1ODc5IC05LjA0Nzg1IC01Ljg1MTU2IC0xNC44NTU1IC05LjI4MzJjLTUuODA1NjYgLTMuNDIxODggLTExLjI4NzEgLTYuMDAwOTggLTE2LjQyODcgLTcuNzA2MDUKYy01LjE0MzU1IC0xLjcxNzc3IC05LjkwNzIzIC0yLjU3MDMxIC0xNC4yODcxIC0yLjU3MDMxaC0wLjI4ODA4NmgtMC4yODcxMDljLTQuMzc5ODggMCAtOS4xNDE2IDAuODUxNTYyIC0xNC4yODUyIDIuNTcwMzFjLTUuMTQzNTUgMS43MDUwOCAtMTAuNjIwMSA0LjI4MTI1IC0xNi40MzA3IDcuNzA2MDVjLTUuODExNTIgMy40Mjc3MyAtMTAuNzYzNyA2LjUyMzQ0IC0xNC44NTk0IDkuMjgzMgpjLTQuMDkyNzcgMi43NjE3MiAtOS4yMzczIDYuMzgwODYgLTE1LjQyNzcgMTAuODYwNGMtNi4xODk0NSA0LjQ3NjU2IC0xMC4yMzU0IDcuMzc5ODggLTEyLjEzOTYgOC43MTU4MmMtMTcuMzM0IDEyLjE4ODUgLTQyLjI4NjEgMjkuNTY3NCAtNzQuODU3NCA1Mi4xMzc3cy01Mi4wOTQ3IDM2LjE0NDUgLTU4LjU3MTMgNDAuNzE0OHpNNDgzLjQyNSAyMzguNTcyYzEwLjg2MzMgNy40MjU3OCAyMC4zODM4IDE1LjcxNjggMjguNTc2MiAyNC44NTU1CnYtMjI2Ljg1MWMwIC0xMi41ODAxIC00LjQ3MjY2IC0yMy4zMzMgLTEzLjQyNzcgLTMyLjI4ODFjLTguOTUyMTUgLTguOTU2MDUgLTE5LjcxNTggLTEzLjQzMTYgLTMyLjI4NDIgLTEzLjQzMTZoLTQyMC41NzNjLTEyLjU3MjMgMCAtMjMuMzM2OSA0LjQ3NTU5IC0zMi4yODgxIDEzLjQzMTZjLTguOTUzMTIgOC45NTIxNSAtMTMuNDI3NyAxOS43MDkgLTEzLjQyNzcgMzIuMjg4MXYyMjYuODUxCmM4LjM4MDg2IC05LjMzMTA1IDE4IC0xNy42MjIxIDI4Ljg2MDQgLTI0Ljg1NTVjNjguOTUxMiAtNDYuODU1NSAxMTYuMjgzIC03OS43MTc4IDE0MS45OTkgLTk4LjU3MzJjMTAuODU2NCAtNy45OTcwNyAxOS42NjcgLTE0LjIzOTMgMjYuNDI2OCAtMTguNzEyOWM2Ljc2MTcyIC00LjQ3NTU5IDE1Ljc2MzcgLTkuMDQ3ODUgMjcgLTEzLjcxMTljMTEuMjM2MyAtNC42NzA5IDIxLjcxMzkgLTcgMzEuNDI3NyAtN2gwLjI4NzEwOWgwLjI5MTAxNgpjOS43MTU4MiAwIDIwLjE4NzUgMi4zMjkxIDMxLjQyMzggN2MxMS4yNDAyIDQuNjY0MDYgMjAuMjM2MyA5LjIzNjMzIDI3IDEzLjcxMTljNi43NjM2NyA0LjQ3MzYzIDE1LjU3MTMgMTAuNzE1OCAyNi40Mjg3IDE4LjcxMjljMzIuMzc2IDIzLjQzMDcgNzkuODEwNSA1Ni4yOTEgMTQyLjI4IDk4LjU3MzJ6IiAvPgogICAgPGdseXBoIGdseXBoLW5hbWU9InVuaUYxMTUiIHVuaWNvZGU9IiYjeGYxMTU7IiAKZD0iTTM1Mi44NDQgMTkxLjMyNmM0Ljg2OTE0IDAgOC44MDQ2OSAtMy45MzU1NSA4LjgwNDY5IC04LjgwMzcxYzAgLTQuODY5MTQgLTMuOTQ1MzEgLTguODA0NjkgLTguODA0NjkgLTguODA0NjloLTYyLjg4MDljLTMuOTM1NTUgLTE1LjE0MzYgLTE3LjYwODQgLTI2LjQxMzEgLTMzLjk2NjggLTI2LjQxMzFjLTE5LjQyMjkgMCAtMzUuMjE3OCAxNS43OTQ5IC0zNS4yMTc4IDM1LjIxNzgKYzAgMTYuMzY3MiAxMS4yNjk1IDMwLjAzMTIgMjYuNDEzMSAzMy45NjY4djg5LjI5MzljMCA0Ljg2OTE0IDMuOTQ0MzQgOC44MDQ2OSA4LjgwNDY5IDguODA0NjljNC44NTkzOCAwIDguODAzNzEgLTMuOTM1NTUgOC44MDM3MSAtOC44MDQ2OXYtODkuMjkzOWMxMi4zMTc0IC0zLjIwNTA4IDIxLjk1OCAtMTIuODQ1NyAyNS4xNjMxIC0yNS4xNjMxaDYyLjg4MDl6TTI1NS45OTYgMTY0LjkxMwpjOS43MTA5NCAwIDE3LjYwODQgNy44OTc0NiAxNy42MDg0IDE3LjYwOTRjMCA5LjcxMDk0IC03Ljg5NzQ2IDE3LjYwODQgLTE3LjYwODQgMTcuNjA4NGMtOS43MTE5MSAwIC0xNy42MDk0IC03Ljg5NzQ2IC0xNy42MDk0IC0xNy42MDg0YzAgLTkuNzExOTEgNy44OTc0NiAtMTcuNjA5NCAxNy42MDk0IC0xNy42MDk0ek00NDEuODczIDM2Mi4wMzRsLTE0Ljc2NDYgLTE0Ljc1NTkKYzQxLjE5NTMgLTQyLjc2MjcgNjYuNjA0NSAtMTAwLjgzNyA2Ni42MDQ1IC0xNjQuNzU2YzAgLTg3LjE0NTUgLTQ3LjE2NDEgLTE2My40NDQgLTExNy4yODIgLTIwNC44MzRsMjYuNjU5MiAtMjYuNjU5MmMzLjQ0MjM4IC0zLjQ0MjM4IDMuNDQyMzggLTkuMDA2ODQgMCAtMTIuNDQ5MmMtMS43MTY4IC0xLjcxNjggLTMuOTcwNyAtMi41ODAwOCAtNi4yMjQ2MSAtMi41ODAwOHMtNC41MDc4MSAwLjg2MzI4MSAtNi4yMjQ2MSAyLjU4MDA4CmwtMzAuMzc1IDMwLjM3NWMtMzEuNTAyIC0xNS40NDM0IC02Ni44ODY3IC0yNC4xNTA0IC0xMDQuMjcgLTI0LjE1MDRjLTM3LjM4MzggMCAtNzIuNzY4NiA4LjcwNzAzIC0xMDQuMjcxIDI0LjE1MDRsLTMwLjM3NSAtMzAuMzc1Yy0xLjcxNjggLTEuNzE2OCAtMy45NzA3IC0yLjU4MDA4IC02LjIyNDYxIC0yLjU4MDA4cy00LjUwNzgxIDAuODYzMjgxIC02LjIyNDYxIDIuNTgwMDgKYy0zLjQ0MjM4IDMuNDQyMzggLTMuNDQyMzggOS4wMDY4NCAwIDEyLjQ0OTJsMjYuNjU5MiAyNi42NTkyYy03MC4xMTgyIDQxLjM4OTYgLTExNy4yODIgMTE3LjY4OCAtMTE3LjI4MiAyMDQuODM0YzAgNjMuOTE4OSAyNS40MDkyIDEyMS45OTMgNjYuNjA0NSAxNjQuNzU2bC0xNC43NTU5IDE0Ljc1NTlsLTM2LjA2MjUgLTM2LjA2MjVjLTEuNjQ2NDggLTEuNjU1MjcgLTMuODkxNiAtMi41ODAwOCAtNi4yMjQ2MSAtMi41ODAwOApjLTIuMzMzOTggMCAtNC41NzAzMSAwLjkyNDgwNSAtNi4yMjU1OSAyLjU4MDA4Yy0yNy44ODI4IDI3Ljg3NCAtMjcuODgyOCA3My4yNDMyIDAgMTAxLjExOGMyNy44NzUgMjcuODc0IDczLjI0NDEgMjcuODc0IDEwMS4xMTggMGMxLjY1NTI3IC0xLjY0NjQ4IDIuNTgwMDggLTMuODgyODEgMi41ODAwOCAtNi4yMjQ2MXMtMC45MjQ4MDUgLTQuNTY5MzQgLTIuNTgwMDggLTYuMjI0NjFsLTQwLjE1NjIgLTQwLjE1NzJsMTQuOTY3OCAtMTQuOTU4CmM0Mi4wODQgMzcuNzE3OCA5Ny42MzA5IDYwLjcxNDggMTU4LjQ2MSA2MC43MTQ4YzYwLjgyOTEgMCAxMTYuMzc2IC0yMi45OTcxIDE1OC40NiAtNjAuNzE0OGwxNC45NTkgMTQuOTU4bC00MC4xNTYyIDQwLjE1NzJjLTEuNjU1MjcgMS42NDY0OCAtMi41ODAwOCAzLjg4MjgxIC0yLjU4MDA4IDYuMjI0NjFzMC45MjQ4MDUgNC41NjkzNCAyLjU4MDA4IDYuMjI0NjFjMjcuODc0IDI3Ljg3NCA3My4yNDMyIDI3Ljg3NCAxMDEuMTE4IDAKYzI3Ljg4MjggLTI3Ljg3NSAyNy44ODI4IC03My4yNDQxIDAgLTEwMS4xMThjLTEuNjQ2NDggLTEuNjU1MjcgLTMuODkxNiAtMi41ODAwOCAtNi4yMjU1OSAtMi41ODAwOGMtMi4zMzMwMSAwIC00LjU2OTM0IDAuOTI0ODA1IC02LjIyNDYxIDIuNTgwMDh6TTQ3Ny45MjcgNDE0LjY0MWMtMTguODkzNiAxOC45MDIzIC00OC40NTAyIDIwLjc5NTkgLTY5LjQ4MzQgNS43MTM4N2w3NS4xOTgyIC03NS4yMDcKYzE1LjA4MTEgMjEuMDUwOCAxMy4xNzk3IDUwLjU5ODYgLTUuNzE0ODQgNjkuNDkzMnpNMjguMzQ5NiAzNDUuMTU2bDc1LjE5ODIgNzUuMTk4MmMtMjEuMDMzMiAxNS4wNzMyIC01MC41ODk4IDEzLjE3OTcgLTY5LjQ4MzQgLTUuNzEzODdjLTE4Ljg5NDUgLTE4Ljg5NDUgLTIwLjc5NTkgLTQ4LjQ0MjQgLTUuNzE0ODQgLTY5LjQ4NDR6TTI1NS45OTYgLTM3LjU4NjljMTIxLjM3NiAwIDIyMC4xMDggOTguNzQxMiAyMjAuMTA4IDIyMC4xMDkKcy05OC43NDEyIDIyMC4xMDggLTIyMC4xMDggMjIwLjEwOGMtMTIxLjM2OCAwIC0yMjAuMTA5IC05OC43NDAyIC0yMjAuMTA5IC0yMjAuMTA4czk4Ljc0MTIgLTIyMC4xMDkgMjIwLjEwOSAtMjIwLjEwOXpNNDMxLjA0NCAyODQuMTVjMC4wODc4OTA2IC0wLjE0MDYyNSAwLjIzNzMwNSAtMC4yMjg1MTYgMC4zMzQ5NjEgLTAuMzc3OTNjMC4wODc4OTA2IC0wLjE1OTE4IDAuMDc5MTAxNiAtMC4zMjYxNzIgMC4xNTgyMDMgLTAuNDg0Mzc1CmMxNy4xMTUyIC0yOS42OTczIDI2Ljk2NzggLTY0LjA5NTcgMjYuOTY3OCAtMTAwLjc2NmMwIC0zNi42NzA5IC05Ljg1MjU0IC03MS4wNjA1IC0yNi45Njc4IC0xMDAuNzY3Yy0wLjA3MDMxMjUgLTAuMTU4MjAzIC0wLjA3MDMxMjUgLTAuMzMzOTg0IC0wLjE1ODIwMyAtMC40ODQzNzVjLTAuMTI0MDIzIC0wLjIxOTcyNyAtMC4zMDg1OTQgLTAuMzc3OTMgLTAuNDQ5MjE5IC0wLjU4MTA1NQpjLTE3LjY5NzMgLTMwLjI3NzMgLTQzLjAwMSAtNTUuNTQ1OSAtNzMuMjk2OSAtNzMuMjA4Yy0wLjE0OTQxNCAtMC4wOTY2Nzk3IC0wLjIyODUxNiAtMC4yNDYwOTQgLTAuMzc3OTMgLTAuMzMzOTg0Yy0wLjIyOTQ5MiAtMC4xMzI4MTIgLTAuNDc1NTg2IC0wLjE4NTU0NyAtMC43MDUwNzggLTAuMjkxMDE2Yy0yOS42NTIzIC0xNy4wMzYxIC02My45NjI5IC0yNi44MzU5IC0xMDAuNTQ1IC0yNi44MzU5CmMtMzYuNTgzIDAgLTcwLjg5MzYgOS43OTk4IC0xMDAuNTQ2IDI2LjgzNTljLTAuMjI5NDkyIDAuMTA1NDY5IC0wLjQ3NTU4NiAwLjE1ODIwMyAtMC43MDUwNzggMC4yOTEwMTZjLTAuMTQ5NDE0IDAuMDg3ODkwNiAtMC4yMzczMDUgMC4yMzczMDUgLTAuMzc3OTMgMC4zMzM5ODRjLTMwLjI5NTkgMTcuNjYyMSAtNTUuNTk5NiA0Mi45Mzk1IC03My4yOTY5IDczLjIwOApjLTAuMTQwNjI1IDAuMjAzMTI1IC0wLjMyNTE5NSAwLjM2MTMyOCAtMC40NDkyMTkgMC41ODEwNTVjLTAuMDg3ODkwNiAwLjE1OTE4IC0wLjA3OTEwMTYgMC4zMjYxNzIgLTAuMTU4MjAzIDAuNDg0Mzc1Yy0xNy4xMTUyIDI5LjY5NzMgLTI2Ljk2NzggNjQuMDk1NyAtMjYuOTY3OCAxMDAuNzY3YzAgMzYuNjY5OSA5Ljg1MjU0IDcxLjA1OTYgMjYuOTY3OCAxMDAuNzY2CmMwLjA3MDMxMjUgMC4xNTgyMDMgMC4wNzAzMTI1IDAuMzM0OTYxIDAuMTU4MjAzIDAuNDg0Mzc1YzAuMDg4ODY3MiAwLjE0OTQxNCAwLjIzODI4MSAwLjIyODUxNiAwLjMzNDk2MSAwLjM3NzkzYzE3LjY5NzMgMzAuMzY3MiA0My4wNDQ5IDU1LjcxNDggNzMuNDExMSA3My40MTExYzAuMTQ5NDE0IDAuMDk2Njc5NyAwLjIyODUxNiAwLjI0NzA3IDAuMzc3OTMgMC4zMzQ5NjEKYzAuMTUwMzkxIDAuMDk2Njc5NyAwLjMyNjE3MiAwLjA4Nzg5MDYgMC40ODQzNzUgMC4xNjY5OTJjMjkuNjk3MyAxNy4xMjQgNjQuMDk1NyAyNi45Njc4IDEwMC43NjcgMjYuOTY3OGMzNi42Njk5IDAgNzEuMDY4NCAtOS44NTI1NCAxMDAuNzY2IC0yNi45Njc4YzAuMTU4MjAzIC0wLjA3OTEwMTYgMC4zMjUxOTUgLTAuMDc5MTAxNiAwLjQ4NDM3NSAtMC4xNjY5OTIKYzAuMTQ5NDE0IC0wLjA4Nzg5MDYgMC4yMzczMDUgLTAuMjM4MjgxIDAuMzc3OTMgLTAuMzM0OTYxYzMwLjM1NzQgLTE3LjY5NjMgNTUuNzE0OCAtNDMuMDQzOSA3My40MTExIC03My40MTExek0zNTUuODYzIDI3LjE2MDJjMjIuMjMxNCAxNC4zNDI4IDQxLjE2MDIgMzMuMjcxNSA1NS40NzY2IDU1LjUxMTdsLTcuMjcyNDYgNC4xOTkyMmMtNC4yMDgwMSAyLjQzMDY2IC01LjY1MjM0IDcuODA5NTcgLTMuMjIyNjYgMTIuMDI3MwpjMi40MzA2NiA0LjE5OTIyIDcuODAwNzggNS42Njk5MiAxMi4wMjczIDMuMjIyNjZsNy4yOTg4MyAtNC4yMTc3N2MxMS44NTk0IDIyLjkwOTIgMTguOTU1MSA0OC42MDA2IDIwLjI1IDc1LjgyMzJoLTguMzU1NDdjLTQuODYwMzUgMCAtOC44MDQ2OSAzLjkzNTU1IC04LjgwNDY5IDguODA0NjljMCA0Ljg2ODE2IDMuOTQ0MzQgOC44MDM3MSA4LjgwNDY5IDguODAzNzFoOC4zNTU0NwpjLTEuMjk0OTIgMjcuMjE0OCAtOC4zOTA2MiA1Mi45MDUzIC0yMC4yNSA3NS44MjMybC03LjI5ODgzIC00LjIxNjhjLTEuMzkxNiAtMC44MDE3NTggLTIuOTA1MjcgLTEuMTc5NjkgLTQuMzkzNTUgLTEuMTc5NjljLTMuMDQ2ODggMCAtNS45OTYwOSAxLjU4Mzk4IC03LjYzMzc5IDQuNDAxMzdjLTIuNDI5NjkgNC4yMDg5OCAtMC45ODUzNTIgOS41OTc2NiAzLjIyMjY2IDEyLjAyNzNsNy4yNzI0NiA0LjE5OTIyCmMtMTQuMzM0IDIyLjIyMjcgLTMzLjI2MjcgNDEuMTYxMSAtNTUuNDk0MSA1NS40OTQxbC00LjE5OTIyIC03LjI3MjQ2Yy0xLjYyODkxIC0yLjgxNzM4IC00LjU4NjkxIC00LjQwMjM0IC03LjYzMzc5IC00LjQwMjM0Yy0xLjQ4NzMgMCAtMy4wMDE5NSAwLjM3ODkwNiAtNC4zOTM1NSAxLjE3OTY5Yy00LjIwODAxIDIuNDMwNjYgLTUuNjUyMzQgNy44MDk1NyAtMy4yMjE2OCAxMi4wMjczbDQuMjE2OCA3LjI5ODgzCmMtMjIuOTE4IDExLjg1OTQgLTQ4LjU5OTYgMTguOTY0OCAtNzUuODIzMiAyMC4yNXYtOC4zNTU0N2MwIC00Ljg2OTE0IC0zLjk0NDM0IC04LjgwNDY5IC04LjgwNDY5IC04LjgwNDY5Yy00Ljg1OTM4IDAgLTguODAzNzEgMy45MzU1NSAtOC44MDM3MSA4LjgwNDY5djguMzU1NDdjLTI3LjIyMzYgLTEuMjk0OTIgLTUyLjkwNTMgLTguMzkwNjIgLTc1LjgyMzIgLTIwLjI1bDQuMjE2OCAtNy4yOTg4MwpjMi40MzA2NiAtNC4yMDg5OCAwLjk4NjMyOCAtOS41OTY2OCAtMy4yMjE2OCAtMTIuMDI3M2MtMS4zOTE2IC0wLjgwMDc4MSAtMi45MDUyNyAtMS4xNzk2OSAtNC4zOTM1NSAtMS4xNzk2OWMtMy4wNDY4OCAwIC01Ljk5NjA5IDEuNTg0OTYgLTcuNjMzNzkgNC40MDIzNGwtNC4xOTkyMiA3LjI3MjQ2Yy0yMi4yMjI3IC0xNC4zMzMgLTQxLjE2MDIgLTMzLjI3MTUgLTU1LjQ5NDEgLTU1LjQ5NDFsNy4yNzI0NiAtNC4yMTY4CmM0LjIwODAxIC0yLjQyOTY5IDUuNjUyMzQgLTcuODA5NTcgMy4yMjI2NiAtMTIuMDI3M2MtMS42Mjg5MSAtMi44MTczOCAtNC41ODY5MSAtNC40MDEzNyAtNy42MzM3OSAtNC40MDEzN2MtMS40ODczIDAgLTMuMDAxOTUgMC4zNzc5MyAtNC4zOTM1NSAxLjE3OTY5bC03LjI5ODgzIDQuMjE2OGMtMTEuODU5NCAtMjIuOTA5MiAtMTguOTU1MSAtNDguNTk5NiAtMjAuMjUgLTc1LjgyMzJoOC4zNzMwNQpjNC44NjAzNSAwIDguODA0NjkgLTMuOTM1NTUgOC44MDQ2OSAtOC44MDM3MWMwIC00Ljg2OTE0IC0zLjk0NDM0IC04LjgwNDY5IC04LjgwNDY5IC04LjgwNDY5aC04LjM1NTQ3YzEuMjk0OTIgLTI3LjIxMzkgOC4zOTA2MiAtNTIuOTA1MyAyMC4yNSAtNzUuODIzMmw3LjI5ODgzIDQuMjE3NzdjNC4yMTc3NyAyLjQ1NjA1IDkuNTg3ODkgMC45ODUzNTIgMTIuMDI3MyAtMy4yMjI2NgpjMi40Mjk2OSAtNC4yMDg5OCAwLjk4NjMyOCAtOS41OTY2OCAtMy4yMjI2NiAtMTIuMDI3M2wtNy4yNzI0NiAtNC4xOTkyMmMxNC4zMzQgLTIyLjIyMjcgMzMuMjYyNyAtNDEuMTYwMiA1NS40OTQxIC01NS40OTQxbDQuMTk5MjIgNy4yNzI0NmMyLjQzMDY2IDQuMjAwMiA3Ljc5MTk5IDUuNjUyMzQgMTIuMDI3MyAzLjIyMjY2YzQuMjA4MDEgLTIuNDI5NjkgNS42NTIzNCAtNy44MDk1NyAzLjIyMTY4IC0xMi4wMjczbC00LjIxNjggLTcuMjk4ODMKYzIyLjkxOCAtMTEuODU5NCA0OC41OTk2IC0xOC45NjM5IDc1LjgyMzIgLTIwLjI1djguMzU1NDdjMCA0Ljg2OTE0IDMuOTQ0MzQgOC44MDQ2OSA4LjgwMzcxIDguODA0NjljNC44NjAzNSAwIDguODA0NjkgLTMuOTM1NTUgOC44MDQ2OSAtOC44MDQ2OXYtOC4zNTU0N2MyNy4yMjM2IDEuMjk0OTIgNTIuOTA1MyA4LjM5MDYyIDc1LjgyMzIgMjAuMjVsLTQuMjE2OCA3LjI5ODgzCmMtMi40MzA2NiA0LjIwODk4IC0wLjk4NjMyOCA5LjU5NzY2IDMuMjIxNjggMTIuMDI3M2M0LjIwODk4IDIuNDM4NDggOS41ODc4OSAwLjk4NjMyOCAxMi4wMjczIC0zLjIyMjY2eiIgLz4KICAgIDxnbHlwaCBnbHlwaC1uYW1lPSJ1bmlGMTBEIiB1bmljb2RlPSImI3hmMTBkOyIgCmQ9Ik0wLjc5Mjk2OSAyMTkuODI5Yy0xLjYxMTMzIDMuOTg5MjYgLTAuNzI0NjA5IDguNTczMjQgMi4yMzA0NyAxMS42MjVsMjA2LjU4NCAyMTMuNDMyYzEuOTQzMzYgMS45ODM0IDQuNTY0NDUgMy4xMTQyNiA3LjMxMjUgMy4xMTQyNmMyLjc0NjA5IDAgNS4zNjcxOSAtMS4xMzA4NiA3LjMxMDU1IC0zLjEzNDc3bDIwNS45ODYgLTIxMy40MzNjMi45NTExNyAtMy4wNTA3OCAzLjgxODM2IC03LjYzNTc0IDIuMjI2NTYgLTExLjYyMjEKYy0xLjYwOTM4IC0zLjk2Nzc3IC01LjM2NzE5IC02LjU3MDMxIC05LjUzNzExIC02LjU3MDMxaC0xMTMuNTc4di0yNjYuNTc3YzAgLTUuODg1NzQgLTQuNjI1IC0xMC42NjMxIC0xMC4zMjQyIC0xMC42NjMxaC0xNjUuMjAxYy01LjY5OTIyIDAgLTEwLjMyODEgNC43NzczNCAtMTAuMzI4MSAxMC42NjMxdjI2Ni41NzdoLTExMy4xNDFjLTQuMTcxODggMCAtNy45Mjk2OSAyLjYwMjU0IC05LjU0MTAyIDYuNTg4ODd6IiAvPgogIDwvZm9udD4KPC9kZWZzPjwvc3ZnPgo="

/***/ }),
/* 215 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__ = __webpack_require__(4);
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
  name: 'GeneralSettings',
  components: {
    BaseLayout: __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__["a" /* default */]
  }
});

/***/ }),
/* 216 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__ = __webpack_require__(4);
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["a"] = ({
  name: "HRWorkDays",
  components: {
    BaseLayout: __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__["a" /* default */]
  }
});

/***/ }),
/* 217 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__ = __webpack_require__(4);
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["a"] = ({
  name: "HRLeave",
  components: {
    BaseLayout: __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__["a" /* default */]
  }
});

/***/ }),
/* 218 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__ = __webpack_require__(4);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__base_DatePicker_vue__ = __webpack_require__(36);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__base_SubmitButton_vue__ = __webpack_require__(22);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__utils_FormDataHandler__ = __webpack_require__(25);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//




var $ = jQuery;
/* harmony default export */ __webpack_exports__["a"] = ({
  name: "HRLeaveYears",
  data: function data() {
    return {
      years_data: [{
        fy_name: "",
        description: "Year for leave",
        start_date: "",
        end_date: ""
      }]
    };
  },
  components: {
    BaseLayout: __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__["a" /* default */],
    DatePicker: __WEBPACK_IMPORTED_MODULE_1__base_DatePicker_vue__["a" /* default */],
    SubmitButton: __WEBPACK_IMPORTED_MODULE_2__base_SubmitButton_vue__["a" /* default */]
  },
  created: function created() {
    this.$store.dispatch("spinner/setSpinner", true);
    this.getFinancialYearsData();
  },
  methods: {
    submitHRLeaveYearsForm: function submitHRLeaveYearsForm() {
      var self = this;
      self.$store.dispatch("spinner/setSpinner", true);
      var requestData = window.settings.hooks.applyFilters("requestData", {
        fyears: self.years_data,
        _wpnonce: erp_settings_var.nonce,
        action: "erp-settings-financial-years-save"
      });
      var postData = Object(__WEBPACK_IMPORTED_MODULE_3__utils_FormDataHandler__["a" /* generateFormDataFromObject */])(requestData);
      $.ajax({
        url: erp_settings_var.ajax_url,
        type: "POST",
        data: postData,
        processData: false,
        contentType: false,
        success: function success(response) {
          self.$store.dispatch("spinner/setSpinner", false);

          if (response.success) {
            self.getFinancialYearsData();
            self.showAlert("success", response.data.message);
          } else {
            self.showAlert("error", response.data);
          }
        }
      });
    },
    addNewYear: function addNewYear() {
      this.years_data.push({
        fy_name: "",
        description: "Year for leave",
        start_date: "",
        end_date: "",
        id: null
      });
    },
    deleteYear: function deleteYear(index) {
      this.years_data.splice(index, 1);
    },
    getFinancialYearsData: function getFinancialYearsData() {
      var self = this;
      var requestData = window.settings.hooks.applyFilters("requestData", {
        _wpnonce: erp_settings_var.nonce,
        action: "erp-settings-get-hr-financial-years"
      });
      var postData = Object(__WEBPACK_IMPORTED_MODULE_3__utils_FormDataHandler__["a" /* generateFormDataFromObject */])(requestData);
      $.ajax({
        url: erp_settings_var.ajax_url,
        type: "POST",
        data: postData,
        processData: false,
        contentType: false,
        success: function success(response) {
          self.$store.dispatch("spinner/setSpinner", false);

          if (response.success) {
            if (response.data.length > 0) {
              self.years_data = [];
              response.data.forEach(function (item) {
                item.start_date = self.formatDateFromTimestamp(item.start_date);
                item.end_date = self.formatDateFromTimestamp(item.end_date);
                self.years_data.push(item);
              });
            }
          }
        }
      });
    },
    formatDateFromTimestamp: function formatDateFromTimestamp(timestamp) {
      if (timestamp === null || timestamp === "") {
        return "";
      }

      return new Date(timestamp * 1e3).toISOString().slice(0, 10);
    }
  }
});

/***/ }),
/* 219 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__ = __webpack_require__(4);
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["a"] = ({
  name: "HRMiscellaneous",
  components: {
    BaseLayout: __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__["a" /* default */]
  }
});

/***/ }),
/* 220 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__ = __webpack_require__(4);
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["a"] = ({
  name: "AcCustomer",
  components: {
    BaseLayout: __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__["a" /* default */]
  }
});

/***/ }),
/* 221 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__ = __webpack_require__(4);
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["a"] = ({
  name: "AcCurrency",
  components: {
    BaseLayout: __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__["a" /* default */]
  }
});

/***/ }),
/* 222 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__ = __webpack_require__(4);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__base_DatePicker_vue__ = __webpack_require__(36);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__base_SubmitButton_vue__ = __webpack_require__(22);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__utils_FormDataHandler__ = __webpack_require__(25);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//




var $ = jQuery;
/* harmony default export */ __webpack_exports__["a"] = ({
  name: "AcFinancialYears",
  data: function data() {
    return {
      years_data: [{
        name: '',
        start_date: '',
        end_date: '',
        description: 'Accounting Financial Years'
      }]
    };
  },
  components: {
    BaseLayout: __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__["a" /* default */],
    DatePicker: __WEBPACK_IMPORTED_MODULE_1__base_DatePicker_vue__["a" /* default */],
    SubmitButton: __WEBPACK_IMPORTED_MODULE_2__base_SubmitButton_vue__["a" /* default */]
  },
  created: function created() {
    this.$store.dispatch("spinner/setSpinner", true);
    this.getFinancialYearsData();
  },
  methods: {
    submitAcFinancialYearsForm: function submitAcFinancialYearsForm() {
      var self = this;
      self.$store.dispatch("spinner/setSpinner", true);
      var requestData = window.settings.hooks.applyFilters("requestData", {
        fyears: self.years_data,
        _wpnonce: erp_settings_var.nonce,
        action: "erp-settings-ac-financial-years-save"
      });
      var postData = Object(__WEBPACK_IMPORTED_MODULE_3__utils_FormDataHandler__["a" /* generateFormDataFromObject */])(requestData);
      $.ajax({
        url: erp_settings_var.ajax_url,
        type: "POST",
        data: postData,
        processData: false,
        contentType: false,
        success: function success(response) {
          self.$store.dispatch("spinner/setSpinner", false);

          if (response.success) {
            self.getFinancialYearsData();
            self.showAlert("success", response.data.message);
          } else {
            self.showAlert("error", response.data);
          }
        }
      });
    },
    addNewYear: function addNewYear() {
      this.years_data.push({
        name: '',
        start_date: '',
        end_date: '',
        description: 'Accounting Financial Years'
      });
    },
    deleteYear: function deleteYear(index) {
      this.years_data.splice(index, 1);
    },
    getFinancialYearsData: function getFinancialYearsData() {
      var self = this;
      var requestData = window.settings.hooks.applyFilters("requestData", {
        _wpnonce: erp_settings_var.nonce,
        action: "erp-settings-get-ac-financial-years"
      });
      var postData = Object(__WEBPACK_IMPORTED_MODULE_3__utils_FormDataHandler__["a" /* generateFormDataFromObject */])(requestData);
      $.ajax({
        url: erp_settings_var.ajax_url,
        type: "POST",
        data: postData,
        processData: false,
        contentType: false,
        success: function success(response) {
          self.$store.dispatch("spinner/setSpinner", false);

          if (response.success) {
            if (response.data.length > 0) {
              self.years_data = response.data;
            }
          }
        }
      });
    }
  }
});

/***/ }),
/* 223 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__ = __webpack_require__(4);
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
  name: "CrmContacts",
  components: {
    BaseLayout: __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__["a" /* default */]
  }
});

/***/ }),
/* 224 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__CrmContactFormLayout_vue__ = __webpack_require__(461);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__CrmContactFormSingle_vue__ = __webpack_require__(463);
//
//
//
//
//
//
//
//
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
  name: "CrmContactForm",
  data: function data() {
    return {
      forms: {},
      section_id: "erp-crm",
      sub_section_id: "contact_forms",
      localizedData: {},
      defaultLocalizedData: {},
      formName: 'contact_form_7'
    };
  },
  components: {
    CrmContactFormLayout: __WEBPACK_IMPORTED_MODULE_0__CrmContactFormLayout_vue__["a" /* default */],
    CrmContactFormSingle: __WEBPACK_IMPORTED_MODULE_1__CrmContactFormSingle_vue__["a" /* default */]
  },
  created: function created() {
    this.initializeDataByFormName(this.$route.params.id || this.formName);

    if (this.localizedData.length && this.localizedData.plugins.length > 0 && typeof this.$route.params.id === 'undefined') {
      this.$router.push("/erp-crm/contact_forms/".concat(this.localizedData.plugins[0]));
    }
  },
  methods: {
    /**
     * Reset Contact forms data based on `plugin` and `formId`
     *
     * @param object data
     *
     * @return void
     */
    resetContactFormData: function resetContactFormData(data) {
      var plugin = data.plugin,
          formId = data.formId,
          map = data.map,
          contactGroup = data.contactGroup,
          contactOwner = data.contactOwner;
      this.localizedData.forms[plugin][formId].map = map;
      this.localizedData.forms[plugin][formId].contactGroup = contactGroup;
      this.localizedData.forms[plugin][formId].contactOwner = contactOwner;
    },

    /**
     * Initialize Data by formName
     *
     * example formName - `contact_form_7`
     */
    initializeDataByFormName: function initializeDataByFormName(formName) {
      var _this = this;

      var menus = erp_settings_var.erp_settings_menus;
      var parentMenu = menus.find(function (menu) {
        return menu.id === _this.section_id;
      });
      var localizedData = parentMenu.fields.contact_forms.localized_data;
      var defaultLocalizedData = parentMenu.fields.contact_forms.localized_data;
      this.formName = formName;
      this.localizedData = {};

      if (typeof localizedData !== 'undefined') {
        this.defaultLocalizedData = defaultLocalizedData;
        this.localizedData = localizedData;
        this.forms = typeof localizedData.forms[this.formName] !== 'undefined' ? localizedData.forms[this.formName] : {};
      }
    }
  },
  watch: {
    $route: function $route(to, from) {
      // Update data if route params changed.
      if (typeof this.localizedData !== 'undefined') {
        this.initializeDataByFormName(to.params.id);
      }
    }
  }
});

/***/ }),
/* 225 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__ = __webpack_require__(4);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
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
  name: "CrmContactForm",
  data: function data() {
    return {
      section_id: "erp-crm",
      sub_section_id: "contact_forms",
      subSectionTitle: "",
      options: []
    };
  },
  components: {
    BaseLayout: __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__["a" /* default */]
  },
  created: function created() {
    var _this = this;

    var menus = erp_settings_var.erp_settings_menus;
    var parentMenu = menus.find(function (menu) {
      return menu.id === _this.section_id;
    });
    this.subSectionTitle = parentMenu.sections[this.sub_section_id];
    this.options = parentMenu.fields[this.sub_section_id];
  }
});

/***/ }),
/* 226 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_defineProperty__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_defineProperty___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_defineProperty__);


function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { __WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_defineProperty___default()(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
var $ = jQuery;
/* harmony default export */ __webpack_exports__["a"] = ({
  name: "CrmContactFormSingle",
  data: function data() {
    return {
      contactGroups: {},
      contactOwners: {},
      crmOptions: {},
      i18n: {},
      mappedData: "",
      nonce: "",
      activeDropDown: ""
    };
  },
  props: {
    plugin: {
      type: String,
      required: true
    },
    formId: {
      type: Number,
      required: true
    },
    formData: {
      type: Object,
      required: true
    },
    data: {
      type: Object,
      required: true
    }
  },
  created: function created() {
    // Process the data and put other things in local variable
    if (typeof this.data !== 'undefined') {
      this.contactGroups = this.data.contactGroups, this.contactOwners = this.data.contactOwners, this.crmOptions = this.data.crmOptions, this.i18n = this.data.i18n, this.mappedData = this.data.mappedData, this.nonce = this.data.nonce;
    }
  },
  computed: {
    totalFields: function totalFields() {
      return Object.keys(this.formData.fields).length;
    }
  },
  methods: {
    lastOfTypeClass: function lastOfTypeClass(index) {
      return index === this.totalFields - 1 ? 'cfi-mapping-row-last' : '';
    },
    getCRMOptionTitle: function getCRMOptionTitle(field) {
      var option = this.formData.map[field],
          title = '';

      if (option && option.indexOf('.') < 0) {
        title = this.crmOptions[option];
      } else if (option) {
        var arr = option.split('.');
        title = this.crmOptions[arr[0]].title + ' - ' + this.crmOptions[arr[0]].options[arr[1]];
      }

      return title ? title : this.i18n.notMapped;
    },
    optionIsAnObject: function optionIsAnObject(option) {
      return '[object Object]' === Object.prototype.toString.call(this.crmOptions[option]);
    },
    mapOption: function mapOption(field, option) {
      this.formData.map[field] = option;
    },
    mapChildOption: function mapChildOption(field, option, childOption) {
      this.formData.map[field] = option + '.' + childOption;
    },
    isMapped: function isMapped(field) {
      return !this.formData.map[field];
    },
    isOptionMapped: function isOptionMapped(field, option) {
      return this.formData.map[field] === option;
    },
    isChildOptionMapped: function isChildOptionMapped(field, option, childOption) {
      return this.formData.map[field] === option + '.' + childOption;
    },
    resetMapping: function resetMapping(field) {
      this.formData.map[field] = null;
    },
    setActiveDropDown: function setActiveDropDown(field) {
      this.activeDropDown = field === this.activeDropDown ? null : field;
    },
    save_mapping: function save_mapping(e) {
      e.preventDefault();
      this.makeAjaxRequest('erp_settings_save_contact_form');
    },
    reset_mapping: function reset_mapping(e) {
      e.preventDefault();
      this.makeAjaxRequest('erp_settings_reset_contact_form');
    },
    makeAjaxRequest: function makeAjaxRequest(action) {
      var self = this;
      var postData = {
        action: action,
        _wpnonce: self.data.nonce,
        plugin: self.plugin,
        formId: self.formId,
        map: self.formData.map,
        contactGroup: self.formData.contactGroup,
        contactOwner: self.formData.contactOwner
      };
      $.ajax({
        url: ajaxurl,
        method: 'post',
        dataType: 'json',
        data: postData
      }).done(function (response) {
        if ('erp_settings_reset_contact_form' === action && response.success) {
          var data = _objectSpread(_objectSpread({}, postData), {}, {
            map: response.map,
            contactGroup: response.contactGroup,
            contactOwner: response.contactOwner
          });

          self.$emit('reset_contact_form_data', data);
        }

        var type = response.success ? 'success' : 'error';

        if (response.msg) {
          self.showAlert(type, response.msg);
        }
      });
    }
  },
  watch: {
    'formData.map': {
      deep: true,
      handler: function handler(newVal) {
        this.formData.map = newVal;
      }
    },
    'formData.contactGroup': function formDataContactGroup(newVal) {
      this.formData.contactGroup = newVal;
    }
  }
});

/***/ }),
/* 227 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__ = __webpack_require__(4);
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
  name: "CrmSubscription",
  components: {
    BaseLayout: __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__["a" /* default */]
  }
});

/***/ }),
/* 228 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__ = __webpack_require__(4);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
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
  name: 'WooCommerce',
  data: function data() {
    return {
      assetSource: '',
      notice: '',
      proActivated: false,
      wcPurchased: false,
      wcActivated: false
    };
  },
  components: {
    BaseLayout: __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__["a" /* default */]
  },
  beforeCreate: function beforeCreate() {
    if (this.$store.state.wcActivated) {
      this.$router.push({
        name: 'WCOrderSync'
      });
    }
  },
  created: function created() {
    var menu = erp_settings_var.erp_settings_menus.find(function (menu) {
      return menu.id === 'erp-woocommerce';
    });
    this.assetSource = erp_settings_var.erp_assets;
    this.notice = menu.extra.notice;
    this.proActivated = menu.extra.pro_activated;
    this.wcPurchased = menu.extra.wc_purchased;
    this.wcActivated = menu.extra.wc_activated;

    if (this.wcActivated) {
      this.$router.push({
        name: "WCSynchronization"
      });
    }

    ;
  },
  computed: {
    wooSyncLogo: function wooSyncLogo() {
      return "".concat(this.assetSource, "/images/wperp-settings/wc-sync.png");
    },
    btnLink: function btnLink() {
      var link = erp_settings_var.erp_pro_link;
      var adminUrl = erp_settings_var.admin_url;

      if (this.proActivated && !this.wcActivated) {
        return "".concat(adminUrl, "?page=erp-extensions");
      }

      return link;
    },
    btnText: function btnText() {
      if (this.proActivated) {
        if (!this.wcPurchased) {
          return __('Get WooCommerce Extension', 'erp');
        } else if (!this.wcActivated) {
          return __('Activate WooCommerce', 'erp');
        }
      }

      return __('Get WP ERP Pro', 'erp');
    }
  }
});

/***/ }),
/* 229 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__ = __webpack_require__(4);
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["a"] = ({
  name: 'GeneralEmail',
  components: {
    BaseLayout: __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__["a" /* default */]
  }
});

/***/ }),
/* 230 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_defineProperty__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_defineProperty___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_defineProperty__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__layouts_BaseLayout_vue__ = __webpack_require__(4);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__layouts_BaseContentLayout_vue__ = __webpack_require__(37);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__SmtpEmail_vue__ = __webpack_require__(231);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4__GoogleEmail_vue__ = __webpack_require__(474);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5__ImapEmail_vue__ = __webpack_require__(476);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6__MailgunEmail_vue__ = __webpack_require__(478);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_7__utils_FormDataHandler__ = __webpack_require__(25);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_8_vuex__ = __webpack_require__(5);


function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { __WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_defineProperty___default()(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

 // Email components







var $ = jQuery;
/* harmony default export */ __webpack_exports__["a"] = ({
  name: 'EmailCConnect',
  components: {
    BaseLayout: __WEBPACK_IMPORTED_MODULE_1__layouts_BaseLayout_vue__["a" /* default */],
    BaseContentLayout: __WEBPACK_IMPORTED_MODULE_2__layouts_BaseContentLayout_vue__["a" /* default */],
    SmtpEmail: __WEBPACK_IMPORTED_MODULE_3__SmtpEmail_vue__["a" /* default */],
    ImapEmail: __WEBPACK_IMPORTED_MODULE_5__ImapEmail_vue__["a" /* default */],
    GoogleEmail: __WEBPACK_IMPORTED_MODULE_4__GoogleEmail_vue__["a" /* default */],
    MailgunEmail: __WEBPACK_IMPORTED_MODULE_6__MailgunEmail_vue__["a" /* default */]
  },
  data: function data() {
    return {
      mailConnections: []
    };
  },
  created: function created() {
    this.getMailConnections();
  },
  methods: {
    getMailConnections: function getMailConnections() {
      var self = this;
      var requestData = window.settings.hooks.applyFilters("requestData", {
        _wpnonce: erp_settings_var.nonce,
        action: 'erp_settings_get_email_providers'
      });
      var postData = Object(__WEBPACK_IMPORTED_MODULE_7__utils_FormDataHandler__["a" /* generateFormDataFromObject */])(requestData);
      var providers = [];
      $.ajax({
        url: erp_settings_var.ajax_url,
        type: "POST",
        data: postData,
        processData: false,
        contentType: false,
        success: function success(response) {
          if (response.success) {
            providers = response.data;
            var mailConnections = [];
            Object.keys(providers).forEach(function (key) {
              var connection = providers[key];
              mailConnections.push({
                type: connection.type,
                enableIcon: connection.icon_enable,
                disableIcon: connection.icon_disable,
                name: connection.name,
                slug: key,
                isActive: connection.is_active,
                isEnabled: connection.enabled
              });
            });
            self.mailConnections = mailConnections;
          }
        }
      });
    },
    toggleActiveConnection: function toggleActiveConnection(activeConnection, type) {
      this.mailConnections.filter(function (connection) {
        if (connection.type === type) {
          connection.isActive = false;
        }

        if (activeConnection.slug === connection.slug) {
          connection.isActive = true;
        }
      });
    }
  },
  computed: _objectSpread({
    outgoingConnections: function outgoingConnections() {
      return this.mailConnections.filter(function (mail) {
        return mail.type === 'outgoing';
      });
    },
    incomingConnections: function incomingConnections() {
      return this.mailConnections.filter(function (mail) {
        return mail.type === 'incoming';
      });
    },
    activeOutgoingEmail: function activeOutgoingEmail() {
      var activeOutgoingMails = this.outgoingConnections.filter(function (mail) {
        return mail.isActive;
      });

      if (activeOutgoingMails.length > 0) {
        return activeOutgoingMails[0].slug;
      } else {
        return 'smtp';
      }
    },
    activeIncomingEmail: function activeIncomingEmail() {
      var activeIncomingMails = this.incomingConnections.filter(function (mail) {
        return mail.isActive;
      });

      if (activeIncomingMails.length > 0) {
        return activeIncomingMails[0].slug;
      } else {
        return 'imap';
      }
    }
  }, Object(__WEBPACK_IMPORTED_MODULE_8_vuex__["b" /* mapState */])({
    formDatas: function formDatas(state) {
      return state.formdata.data;
    }
  })),
  watch: {
    formDatas: function formDatas(formData) {
      if (typeof formData !== 'undefined' && formData !== null) {
        this.getMailConnections();
      }
    }
  }
});

/***/ }),
/* 231 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_SmtpEmail_vue__ = __webpack_require__(232);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_248d80f5_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_SmtpEmail_vue__ = __webpack_require__(473);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_SmtpEmail_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_248d80f5_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_SmtpEmail_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/email/email-connect/SmtpEmail.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-248d80f5", Component.options)
  } else {
    hotAPI.reload("data-v-248d80f5", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 232 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__ = __webpack_require__(4);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
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
  name: 'SmtpEmail',
  components: {
    BaseLayout: __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__["a" /* default */]
  },
  data: function data() {
    return {
      smtpTestEmail: '',
      options: {
        action: '',
        recurrent: false,
        fields: []
      }
    };
  },
  methods: {
    testConnection: function testConnection() {
      this.options.action = 'erp_smtp_test_connection';
      this.options.fields.push({
        'key': 'test_email',
        'value': this.smtpTestEmail
      });
    }
  }
});

/***/ }),
/* 233 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__ = __webpack_require__(4);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__utils_FormDataHandler__ = __webpack_require__(25);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//


var $ = jQuery;
/* harmony default export */ __webpack_exports__["a"] = ({
  name: "GoogleEmail",
  components: {
    BaseLayout: __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__["a" /* default */]
  },
  data: function data() {
    return {
      gmailConnected: {
        link: '',
        status: false,
        is_connected: false,
        disconnect_url: ''
      }
    };
  },
  created: function created() {
    this.getAuthorizationUrl();
  },
  methods: {
    getAuthorizationUrl: function getAuthorizationUrl() {
      var self = this;
      self.$store.dispatch("spinner/setSpinner", true);
      var requestData = window.settings.hooks.applyFilters("requestData", {
        _wpnonce: erp_settings_var.nonce,
        action: "erp_check_gmail_connection_established"
      });
      var postData = Object(__WEBPACK_IMPORTED_MODULE_1__utils_FormDataHandler__["a" /* generateFormDataFromObject */])(requestData);
      $.ajax({
        url: erp_settings_var.ajax_url,
        type: "POST",
        data: postData,
        processData: false,
        contentType: false,
        success: function success(response) {
          self.$store.dispatch("spinner/setSpinner", false);

          if (response.success) {
            self.gmailConnected = response.data;
          }
        }
      });
    }
  }
});

/***/ }),
/* 234 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__ = __webpack_require__(4);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

var $ = jQuery;
/* harmony default export */ __webpack_exports__["a"] = ({
  name: "ImapEmail",
  components: {
    BaseLayout: __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__["a" /* default */]
  },
  data: function data() {
    return {
      imapTestString: __('Test Connection', 'erp')
    };
  },
  methods: {
    /**
     * Test imap connection settings can create valid connection or not
     */
    testImapConnection: function testImapConnection() {
      var self = this;
      self.imapTestString = __('Testing Connection', 'erp') + ' <i class="fa fa-spinner fa-spin"></i>';
      var data = {
        'action': 'erp_imap_test_connection',
        'mail_server': $('#erp-settings-box-erp-email-imap #erp-mail_server').val(),
        'username': $('#erp-settings-box-erp-email-imap #erp-username').val(),
        'password': $('#erp-settings-box-erp-email-imap #erp-password').val(),
        'protocol': $('#erp-settings-box-erp-email-imap #erp-protocol').val(),
        'port': $('#erp-settings-box-erp-email-imap #erp-port').val(),
        'authentication': $('#erp-settings-box-erp-email-imap #erp-authentication').val(),
        '_wpnonce': erp_settings_var.nonce
      };
      $.post(erp_settings_var.ajax_url, data, function (response) {
        var type = response.success ? 'success' : 'error';

        if (response.data) {
          self.$emit('inputImapStatus', 'imap_status');
          self.imapTestString = __(' Test Connection', 'erp');
          swal({
            title: '',
            text: response.data,
            type: type,
            confirmButtonText: __(' OK', 'erp'),
            confirmButtonColor: '#008ec2'
          });
        }
      });
    }
  }
});

/***/ }),
/* 235 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__ = __webpack_require__(4);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
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
  name: 'MailgunEmail',
  components: {
    BaseLayout: __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__["a" /* default */]
  },
  data: function data() {
    return {
      erp_mailgun_test_email: '',
      options: {
        action: '',
        recurrent: false,
        fields: []
      }
    };
  },
  methods: {
    testConnection: function testConnection() {
      this.options.action = 'erp_mailgun_test_connection';
      this.options.fields.push({
        'key': 'erp_mailgun_test_email',
        'value': this.erp_mailgun_test_email
      });
    }
  }
});

/***/ }),
/* 236 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_defineProperty__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_defineProperty___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_defineProperty__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__layouts_BaseLayout_vue__ = __webpack_require__(4);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__base_SubmitButton_vue__ = __webpack_require__(22);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__base_Modal_vue__ = __webpack_require__(60);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4_vue_trix__ = __webpack_require__(92);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5__utils_FormDataHandler__ = __webpack_require__(25);


function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { __WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_defineProperty___default()(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//





var $ = jQuery;
/* harmony default export */ __webpack_exports__["a"] = ({
  name: "EmailTemplate",
  components: {
    BaseLayout: __WEBPACK_IMPORTED_MODULE_1__layouts_BaseLayout_vue__["a" /* default */],
    SubmitButton: __WEBPACK_IMPORTED_MODULE_2__base_SubmitButton_vue__["a" /* default */],
    Modal: __WEBPACK_IMPORTED_MODULE_3__base_Modal_vue__["a" /* default */],
    VueTrix: __WEBPACK_IMPORTED_MODULE_4_vue_trix__["a" /* default */]
  },
  data: function data() {
    return {
      templates: [],
      isVisibleModal: false,
      singleTemplate: {},
      shortCodes: [],
      modalMode: 'create' // 'create' or 'edit'

    };
  },
  created: function created() {
    this.$store.dispatch("spinner/setSpinner", true);
    this.getTemplatesData();
  },
  methods: {
    /**
     * Get template lists
     */
    getTemplatesData: function getTemplatesData() {
      var self = this;
      var requestData = window.settings.hooks.applyFilters("requestData", {
        _wpnonce: erp_settings_var.nonce,
        action: "erp-crm-get-save-replies"
      });
      var postData = Object(__WEBPACK_IMPORTED_MODULE_5__utils_FormDataHandler__["a" /* generateFormDataFromObject */])(requestData);
      $.ajax({
        url: erp_settings_var.ajax_url,
        type: "POST",
        data: postData,
        processData: false,
        contentType: false,
        success: function success(response) {
          self.$store.dispatch("spinner/setSpinner", false);

          if (response.success) {
            self.templates = response.data.replies;
            self.shortCodes = response.data.short_codes;
          }
        }
      });
    },

    /**
     * Template Saving for create and edit
     */
    onFormSubmit: function onFormSubmit() {
      var self = this;
      var isUpdate = self.modalMode === 'edit' ? true : false;
      self.$store.dispatch("spinner/setSpinner", true);

      var requestData = _objectSpread(_objectSpread({}, self.singleTemplate), {}, {
        id: self.modalMode === 'edit' ? self.singleTemplate.id : 0,
        action: !isUpdate ? 'erp-crm-save-replies' : 'erp-crm-edit-save-replies',
        _wpnonce: wpErpCrm.nonce
      });

      requestData = window.settings.hooks.applyFilters("requestData", requestData);
      var postData = Object(__WEBPACK_IMPORTED_MODULE_5__utils_FormDataHandler__["a" /* generateFormDataFromObject */])(requestData);
      $.ajax({
        url: erp_settings_var.ajax_url,
        type: "POST",
        data: postData,
        processData: false,
        contentType: false,
        success: function success(response) {
          self.$store.dispatch("spinner/setSpinner", false);

          if (response.success) {
            if (isUpdate) {
              self.singleTemplate = {};
              self.popupModal({}, 'edit');
            } else {
              self.popupModal({}, 'create');
            }

            self.getTemplatesData();
            self.showAlert("success", response.data.message);
          } else {
            self.showAlert("error", response.data);
          }
        }
      });
    },

    /**
     * Popup template modal for create & edit
     */
    popupModal: function popupModal(template, modalMode) {
      if (this.isVisibleModal) {
        this.isVisibleModal = false;
      } else {
        this.isVisibleModal = true;
      }

      this.singleTemplate = modalMode === 'create' ? {} : template;
      this.modalMode = modalMode;
    },

    /**
     * Popup Delete modal and
     * On confirmation of deletion, delete
     */
    popupDeleteModal: function popupDeleteModal(template) {
      var self = this;
      swal({
        title: __('Delete', 'erp'),
        text: __('Are you sure to delete this ?', 'erp'),
        type: "warning",
        showCancelButton: true,
        cancelButtonText: __('Cancel', 'erp'),
        confirmButtonColor: "#DD6B55",
        confirmButtonText: __('Delete', 'erp'),
        closeOnConfirm: false
      }, function () {
        $.ajax({
          type: "POST",
          url: erp_settings_var.ajax_url,
          dataType: 'json',
          data: {
            id: template.id,
            _wpnonce: wpErpCrm.nonce,
            action: 'erp-crm-delete-save-replies'
          }
        }).fail(function (xhr) {
          self.showAlert('error', xhr);
        }).done(function (response) {
          swal.close();

          if (response.success) {
            self.showAlert('success', response.data.message);
            self.getTemplatesData();
          } else {
            self.showAlert('error', response.data);
          }
        });
      });
    },

    /**
     * Append short code in template body description box
     */
    appendShortCode: function appendShortCode() {
      var templateText = typeof this.singleTemplate.template === 'undefined' ? '' : this.singleTemplate.template;
      this.singleTemplate.template = templateText + this.singleTemplate.shortCode;
    }
  }
});

/***/ }),
/* 237 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__ = __webpack_require__(4);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__layouts_partials_Switch_vue__ = __webpack_require__(58);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__base_SubmitButton_vue__ = __webpack_require__(22);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__base_Modal_vue__ = __webpack_require__(60);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4_vue_trix__ = __webpack_require__(92);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5__base_Tooltip_vue__ = __webpack_require__(55);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
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
  name: "EmailNotification",
  components: {
    BaseLayout: __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__["a" /* default */],
    RadioSwitch: __WEBPACK_IMPORTED_MODULE_1__layouts_partials_Switch_vue__["a" /* default */],
    Modal: __WEBPACK_IMPORTED_MODULE_3__base_Modal_vue__["a" /* default */],
    VueTrix: __WEBPACK_IMPORTED_MODULE_4_vue_trix__["a" /* default */],
    SubmitButton: __WEBPACK_IMPORTED_MODULE_2__base_SubmitButton_vue__["a" /* default */],
    Tooltip: __WEBPACK_IMPORTED_MODULE_5__base_Tooltip_vue__["a" /* default */]
  },
  data: function data() {
    return {
      section: "erp-email",
      subSection: "notification",
      subSectionTitle: "",
      options: [],
      content: '',
      emailTemplates: {},
      singleTemplate: [],
      shortCodes: [],
      module: 'hrm',
      showModal: false,
      columns: [{
        name: __('Template Name', 'erp'),
        class: ''
      }, {
        name: __('Description', 'erp'),
        class: 'hide-sm'
      }, {
        name: __('Disable / Enable', 'erp'),
        class: ''
      }]
    };
  },
  created: function created() {
    var _this = this;

    var menuItems = erp_settings_var.erp_settings_menus;
    var parentMenu = menuItems.find(function (menu) {
      return menu.id === _this.section;
    });
    this.subSectionTitle = parentMenu.sections[this.subSection];
    this.options = parentMenu.fields[this.subSection];
    this.getEmailTemplates();
  },
  computed: {
    emails: function emails() {
      return Object.keys(this.emailTemplates).length && this.emailTemplates[this.module] !== undefined ? this.emailTemplates[this.module] : [];
    },
    numColumns: function numColumns() {
      return this.columns.length;
    }
  },
  methods: {
    getEmailTemplates: function getEmailTemplates() {
      var self = this;
      self.$store.dispatch("spinner/setSpinner", true);
      setTimeout(function () {
        wp.ajax.send({
          data: {
            action: "erp_get_email_templates",
            _wpnonce: erp_settings_var.nonce
          },
          success: function success(response) {
            self.emailTemplates = response;
            self.$store.dispatch("spinner/setSpinner", false);
          },
          error: function error(_error) {
            self.$store.dispatch("spinner/setSpinner", false);
            self.showAlert("error", _error);
          }
        });
      }, 200);
    },
    toggleStatus: function toggleStatus(email, index) {
      var self = this,
          status = email.is_enabled === 'yes' ? 'no' : 'yes';
      self.$store.dispatch("spinner/setSpinner", true);
      wp.ajax.send({
        data: {
          option_id: email.option_id,
          option_value: status,
          action: "erp_update_email_status",
          _wpnonce: erp_settings_var.nonce
        },
        success: function success(response) {
          self.$set(self.emailTemplates[self.module][index], 'is_enabled', status);
          self.$store.dispatch("spinner/setSpinner", false);
        },
        error: function error(_error2) {
          self.$store.dispatch("spinner/setSpinner", false);
          self.showAlert("error", _error2);
        }
      });
    },
    configureTemplate: function configureTemplate(template) {
      var self = this;
      self.$store.dispatch("spinner/setSpinner", true);
      wp.ajax.send({
        data: {
          template: template.id,
          action: "erp_get_single_email_template",
          _wpnonce: erp_settings_var.nonce
        },
        success: function success(response) {
          self.singleTemplate = response;
          self.singleTemplate.title = template.name;
          self.singleTemplate.description = template.description;
          self.shortCodes = response.tags;
          self.showModal = true;
          self.$store.dispatch("spinner/setSpinner", false);
        },
        error: function error(_error3) {
          self.$store.dispatch("spinner/setSpinner", false);
          self.showAlert("error", _error3);
        }
      });
    },
    onSubmit: function onSubmit() {
      var self = this;
      self.$store.dispatch("spinner/setSpinner", true);
      var requestData = {
        id: self.singleTemplate.id,
        is_enable: self.singleTemplate.is_enable,
        subject: self.singleTemplate.subject,
        heading: self.singleTemplate.heading,
        body: self.singleTemplate.body,
        action: 'erp_update_email_template',
        _wpnonce: erp_settings_var.nonce
      };
      requestData = window.settings.hooks.applyFilters("requestData", requestData);
      wp.ajax.send({
        data: requestData,
        success: function success(response) {
          self.$store.dispatch("spinner/setSpinner", false);
          self.showModal = false;
          self.showAlert("success", response);
        },
        error: function error(_error4) {
          self.$store.dispatch("spinner/setSpinner", false);
          self.showAlert("error", _error4);
        }
      });
    },
    toggleModal: function toggleModal() {
      this.showModal = !this.showModal;
    },
    switchValue: function switchValue() {
      var newValue = this.singleTemplate.is_enable === 'yes' ? 'no' : 'yes';
      this.$set(this.singleTemplate, 'is_enable', newValue);
    },
    setModule: function setModule(value) {
      var _this2 = this;

      this.$store.dispatch("spinner/setSpinner", true);
      setTimeout(function () {
        _this2.module = value;

        _this2.$store.dispatch("spinner/setSpinner", false);
      }, 150);
    }
  }
});

/***/ }),
/* 238 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_defineProperty__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_defineProperty___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_defineProperty__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__base_Modal_vue__ = __webpack_require__(60);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__layouts_BaseLayout_vue__ = __webpack_require__(4);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__base_SubmitButton_vue__ = __webpack_require__(22);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4__select_MultiSelect_vue__ = __webpack_require__(48);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5__layouts_BaseContentLayout_vue__ = __webpack_require__(37);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6_vuex__ = __webpack_require__(5);


function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { __WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_defineProperty___default()(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
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
  name: 'Integration',
  components: {
    Modal: __WEBPACK_IMPORTED_MODULE_1__base_Modal_vue__["a" /* default */],
    BaseLayout: __WEBPACK_IMPORTED_MODULE_2__layouts_BaseLayout_vue__["a" /* default */],
    MultiSelect: __WEBPACK_IMPORTED_MODULE_4__select_MultiSelect_vue__["a" /* default */],
    SubmitButton: __WEBPACK_IMPORTED_MODULE_3__base_SubmitButton_vue__["a" /* default */],
    BaseContentLayout: __WEBPACK_IMPORTED_MODULE_5__layouts_BaseContentLayout_vue__["a" /* default */]
  },
  data: function data() {
    return {
      section: 'erp-integration',
      subSection: '',
      integrations: {},
      singleItem: {},
      showModal: false,
      subSubSection: '',
      componentKey: 0,
      options: {
        action: '',
        recurrent: false
      },
      fieldOptions: {},
      selectedField: {
        id: '',
        name: ''
      },
      columns: [__('Integration', 'erp'), __('Description', 'erp'), '']
    };
  },
  created: function created() {
    var _this = this;

    var section = erp_settings_var.erp_settings_menus.find(function (menu) {
      return menu.id === _this.section;
    });
    this.integrations = section.extra.integrations;
  },
  watch: {
    selectedField: function selectedField(newVal) {
      if (newVal.id) {
        this.forceUpdateBody();
      }
    },
    formDatas: function formDatas(formData) {
      if (typeof formData !== 'undefined' && formData !== null) {
        this.testConnection();
      }
    }
  },
  computed: _objectSpread({
    numColumns: function numColumns() {
      return this.columns.length;
    },
    extraContent: function extraContent() {
      return this.singleItem.id === 'erp-dm';
    },
    hideSubmit: function hideSubmit() {
      return this.singleItem.id === 'salesforce-integration';
    },
    formFields: function formFields() {
      return this.selectedField && this.singleItem.form_fields[this.selectedField.id] !== undefined ? this.singleItem.form_fields[this.selectedField.id] : this.singleItem.form_fields;
    }
  }, Object(__WEBPACK_IMPORTED_MODULE_6_vuex__["b" /* mapState */])({
    formDatas: function formDatas(state) {
      return state.formdata.data;
    }
  })),
  methods: {
    configure: function configure(item, key) {
      if (key === 'mailchimp') {
        this.$router.push({
          name: 'MailchimpSettings'
        });
        return;
      }

      this.singleItem = item;
      this.subSection = key;

      if (key === 'sms') {
        this.selectedField.id = item.extra.selected_gateway;
        this.selectedField.name = item.sections[this.selectedField.id];
        this.fieldOptions = item.sections;
      }

      this.showModal = true;
    },
    toggleModal: function toggleModal() {
      this.showModal = false;
    },
    onSubmit: function onSubmit() {
      this.options.action = '';
      this.$refs.base.onFormSubmit();
    },
    forceUpdateBody: function forceUpdateBody() {
      this.componentKey += 1;
    },
    onSelect: function onSelect(selected) {
      this.selectedField = selected;
    },
    testConnection: function testConnection() {
      var options = Object.assign({}, options);
      options.action = 'wp-erp-sync-employees-dropbox';
      this.options = options;
    }
  }
});

/***/ }),
/* 239 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__ = __webpack_require__(4);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__base_SubmitButton_vue__ = __webpack_require__(22);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
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
  name: 'License',
  components: {
    BaseLayout: __WEBPACK_IMPORTED_MODULE_0__layouts_BaseLayout_vue__["a" /* default */],
    SubmitButton: __WEBPACK_IMPORTED_MODULE_1__base_SubmitButton_vue__["a" /* default */]
  },
  data: function data() {
    return {
      section: 'erp-license',
      extensions: {},
      columns: [__('Extension', 'erp'), __('Version', 'erp'), __('License Key', 'erp'), __('Status', 'erp')]
    };
  },
  created: function created() {
    var _this = this;

    var section = erp_settings_var.erp_settings_menus.find(function (menu) {
      return menu.id === _this.section;
    });
    this.extensions = section.extra.extensions;
  },
  computed: {
    numColumns: function numColumns() {
      return this.columns.length;
    }
  },
  methods: {
    saveSettings: function saveSettings() {
      this.$store.dispatch('spinner/setSpinner', true);
      var self = this;
      var data = {
        extensions: this.extensions,
        _wpnonce: erp_settings_var.nonce,
        action: 'erp_settings_save_licenses'
      };
      wp.ajax.send({
        data: data,
        success: function success(response) {
          self.$store.dispatch('spinner/setSpinner', false);
          self.showAlert('success', response);
        },
        error: function error(_error) {
          self.$store.dispatch('spinner/setSpinner', false);
          self.showAlert('error', _error);
        }
      });
    }
  }
});

/***/ }),
/* 240 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* WEBPACK VAR INJECTION */(function(process) {/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vue__ = __webpack_require__(19);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_vuex__ = __webpack_require__(5);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__modules_spinner__ = __webpack_require__(490);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__modules_formdata__ = __webpack_require__(491);




__WEBPACK_IMPORTED_MODULE_0_vue__["default"].use(__WEBPACK_IMPORTED_MODULE_1_vuex__["a" /* default */]);
var debug = process.env.NODE_ENV !== 'production';
var store = new __WEBPACK_IMPORTED_MODULE_1_vuex__["a" /* default */].Store({
  modules: {
    spinner: __WEBPACK_IMPORTED_MODULE_2__modules_spinner__["a" /* default */],
    formdata: __WEBPACK_IMPORTED_MODULE_3__modules_formdata__["a" /* default */]
  },
  strict: debug,
  plugins: []
});
/* harmony default export */ __webpack_exports__["a"] = (store);
/* WEBPACK VAR INJECTION */}.call(__webpack_exports__, __webpack_require__(11)))

/***/ }),
/* 241 */,
/* 242 */,
/* 243 */,
/* 244 */,
/* 245 */,
/* 246 */,
/* 247 */,
/* 248 */,
/* 249 */,
/* 250 */,
/* 251 */,
/* 252 */,
/* 253 */,
/* 254 */,
/* 255 */,
/* 256 */,
/* 257 */,
/* 258 */,
/* 259 */,
/* 260 */,
/* 261 */,
/* 262 */,
/* 263 */,
/* 264 */,
/* 265 */,
/* 266 */,
/* 267 */,
/* 268 */,
/* 269 */,
/* 270 */,
/* 271 */,
/* 272 */,
/* 273 */,
/* 274 */,
/* 275 */,
/* 276 */,
/* 277 */,
/* 278 */,
/* 279 */,
/* 280 */,
/* 281 */,
/* 282 */,
/* 283 */,
/* 284 */,
/* 285 */,
/* 286 */,
/* 287 */,
/* 288 */,
/* 289 */,
/* 290 */,
/* 291 */,
/* 292 */,
/* 293 */,
/* 294 */,
/* 295 */,
/* 296 */,
/* 297 */,
/* 298 */,
/* 299 */,
/* 300 */,
/* 301 */,
/* 302 */,
/* 303 */,
/* 304 */,
/* 305 */,
/* 306 */,
/* 307 */,
/* 308 */,
/* 309 */,
/* 310 */,
/* 311 */,
/* 312 */,
/* 313 */,
/* 314 */,
/* 315 */,
/* 316 */,
/* 317 */,
/* 318 */,
/* 319 */,
/* 320 */,
/* 321 */,
/* 322 */,
/* 323 */,
/* 324 */,
/* 325 */,
/* 326 */,
/* 327 */,
/* 328 */,
/* 329 */,
/* 330 */,
/* 331 */,
/* 332 */,
/* 333 */,
/* 334 */,
/* 335 */,
/* 336 */,
/* 337 */,
/* 338 */,
/* 339 */,
/* 340 */,
/* 341 */,
/* 342 */,
/* 343 */,
/* 344 */,
/* 345 */,
/* 346 */,
/* 347 */,
/* 348 */,
/* 349 */,
/* 350 */,
/* 351 */,
/* 352 */,
/* 353 */,
/* 354 */,
/* 355 */,
/* 356 */,
/* 357 */,
/* 358 */,
/* 359 */,
/* 360 */,
/* 361 */,
/* 362 */,
/* 363 */,
/* 364 */,
/* 365 */,
/* 366 */,
/* 367 */,
/* 368 */,
/* 369 */,
/* 370 */,
/* 371 */,
/* 372 */,
/* 373 */,
/* 374 */,
/* 375 */,
/* 376 */,
/* 377 */,
/* 378 */,
/* 379 */,
/* 380 */,
/* 381 */,
/* 382 */,
/* 383 */,
/* 384 */,
/* 385 */,
/* 386 */,
/* 387 */,
/* 388 */,
/* 389 */,
/* 390 */,
/* 391 */,
/* 392 */,
/* 393 */,
/* 394 */,
/* 395 */,
/* 396 */,
/* 397 */,
/* 398 */,
/* 399 */,
/* 400 */,
/* 401 */,
/* 402 */,
/* 403 */,
/* 404 */,
/* 405 */,
/* 406 */,
/* 407 */,
/* 408 */,
/* 409 */,
/* 410 */,
/* 411 */,
/* 412 */,
/* 413 */,
/* 414 */,
/* 415 */,
/* 416 */,
/* 417 */,
/* 418 */,
/* 419 */,
/* 420 */,
/* 421 */,
/* 422 */,
/* 423 */,
/* 424 */,
/* 425 */,
/* 426 */,
/* 427 */,
/* 428 */,
/* 429 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__App_vue__ = __webpack_require__(430);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__router__ = __webpack_require__(441);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__store_store__ = __webpack_require__(240);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__wordpress_hooks__ = __webpack_require__(40);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4__request__ = __webpack_require__(492);




window.erpSettingsHooks = Object(__WEBPACK_IMPORTED_MODULE_3__wordpress_hooks__["a" /* createHooks */])();
/* global settings_get_lib */

var Vue = settings_get_lib('Vue');
var VueSweetalert2 = settings_get_lib('VueSweetalert2');
var Loading = settings_get_lib('Loading');
var Vuelidate = settings_get_lib('Vuelidate');
var commonMixins = settings_get_lib('commonMixins');
var i18nMixin = settings_get_lib('i18nMixin');
var clickOutside = settings_get_lib('clickOutside'); // config

Vue.config.productionTip = false; // vue uses

Vue.use(VueSweetalert2);
Vue.use(Loading);
Vue.use(Vuelidate); // mixin

Vue.mixin(commonMixins);
Vue.mixin(i18nMixin); // vue click outside directive

Vue.directive('click-outside', clickOutside);


(function () {
  window.postRequest = __WEBPACK_IMPORTED_MODULE_4__request__["b" /* postRequest */];
  window.getRequest = __WEBPACK_IMPORTED_MODULE_4__request__["a" /* getRequest */];
})();

var settingsContainer = document.getElementById('erp-settings');

if (settingsContainer !== null) {
  window.erp_settings_vue_instance = new Vue({
    el: '#erp-settings',
    router: __WEBPACK_IMPORTED_MODULE_1__router__["a" /* default */],
    store: __WEBPACK_IMPORTED_MODULE_2__store_store__["a" /* default */],
    render: function render(h) {
      return h(__WEBPACK_IMPORTED_MODULE_0__App_vue__["a" /* default */]);
    }
  });
}

/***/ }),
/* 430 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_App_vue__ = __webpack_require__(208);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_bbad6d40_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_App_vue__ = __webpack_require__(440);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_App_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_bbad6d40_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_App_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/App.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-bbad6d40", Component.options)
  } else {
    hotAPI.reload("data-v-bbad6d40", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 431 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_SettingsMenu_vue__ = __webpack_require__(209);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_6ed0aa2c_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_SettingsMenu_vue__ = __webpack_require__(432);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_SettingsMenu_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_6ed0aa2c_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_SettingsMenu_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/menu/SettingsMenu.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-6ed0aa2c", Component.options)
  } else {
    hotAPI.reload("data-v-6ed0aa2c", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 432 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", { staticClass: "settings-navbar" }, [
    _c("h2", [_vm._v(_vm._s(_vm.__("Settings", "erp")))]),
    _vm._v(" "),
    _c(
      "ul",
      { staticClass: "settings-menu" },
      _vm._l(_vm.menus, function(menu, index) {
        return _c(
          "li",
          { key: index },
          [
            _c(
              "router-link",
              {
                class:
                  _vm.$route.name === "GeneralSettings" && index === 0
                    ? "router-link-active"
                    : "",
                attrs: { tag: "li", to: menu.slug }
              },
              [
                _c("a", { attrs: { href: "#" } }, [
                  _c("img", { attrs: { src: menu.icon, alt: "" } }),
                  _vm._v(" "),
                  _c("span", { staticClass: "menu-name" }, [
                    _vm._v(_vm._s(menu.label))
                  ]),
                  _vm._v(" "),
                  menu.extra.pro_label !== undefined && menu.extra.pro_label
                    ? _c("span", { staticClass: "pro-label" }, [_vm._v("PRO")])
                    : _vm._e()
                ])
              ]
            )
          ],
          1
        )
      }),
      0
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
    require("vue-hot-reload-api")      .rerender("data-v-6ed0aa2c", esExports)
  }
}

/***/ }),
/* 433 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_SearchBar_vue__ = __webpack_require__(210);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_e20e2202_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_SearchBar_vue__ = __webpack_require__(434);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_SearchBar_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_e20e2202_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_SearchBar_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/searchbar/SearchBar.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-e20e2202", Component.options)
  } else {
    hotAPI.reload("data-v-e20e2202", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 434 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", [
    _c("div", { staticClass: "search-area" }, [
      _c("input", {
        directives: [
          {
            name: "model",
            rawName: "v-model",
            value: _vm.searchText,
            expression: "searchText"
          }
        ],
        staticClass: "input-searchbar",
        attrs: { type: "search", placeholder: _vm.__("Search", "erp") },
        domProps: { value: _vm.searchText },
        on: {
          input: function($event) {
            if ($event.target.composing) {
              return
            }
            _vm.searchText = $event.target.value
          }
        }
      }),
      _vm._v(" "),
      _c(
        "svg",
        {
          attrs: {
            width: "12px",
            height: "12px",
            viewBox: "0 0 12 12",
            version: "1.1",
            xmlns: "http://www.w3.org/2000/svg",
            "xmlns:xlink": "http://www.w3.org/1999/xlink"
          }
        },
        [
          _c(
            "g",
            {
              attrs: {
                id: "Page-1",
                stroke: "none",
                "stroke-width": "1",
                fill: "none",
                "fill-rule": "evenodd"
              }
            },
            [
              _c(
                "g",
                {
                  attrs: {
                    id: "search",
                    transform: "translate(-1163.000000, -73.000000)",
                    fill: "#CBCBCB",
                    "fill-rule": "nonzero"
                  }
                },
                [
                  _c(
                    "g",
                    {
                      attrs: {
                        id: "search-inner",
                        transform: "translate(1012.000000, 64.000000)"
                      }
                    },
                    [
                      _c("path", {
                        attrs: {
                          d:
                            "M162.674231,19.2599522 L160.074438,16.6602072 C160.060622,16.6463904 160.044414,16.6367331 160.029976,16.6238725 C160.54153,15.8478884 160.840096,14.919012 160.840096,13.9200956 C160.840096,11.2027888 158.637307,9 155.920048,9 C153.202789,9 151,11.2027888 151,13.9200478 C151,16.637259 153.202741,18.8400956 155.92,18.8400956 C156.918964,18.8400956 157.847793,18.5415299 158.623777,18.0299761 C158.636637,18.0443665 158.646247,18.0605737 158.660064,18.0743904 L161.259904,20.6742311 C161.650454,21.0647331 162.283633,21.0647331 162.674231,20.6742311 C163.064733,20.2836813 163.064733,19.650502 162.674231,19.2599522 Z M155.920048,17.1344701 C154.144717,17.1344701 152.705578,15.6953307 152.705578,13.9200478 C152.705578,12.1447171 154.144765,10.7055777 155.920048,10.7055777 C157.695283,10.7055777 159.13447,12.1447649 159.13447,13.9200478 C159.13447,15.6953307 157.695283,17.1344701 155.920048,17.1344701 Z",
                          id: "Shape"
                        }
                      })
                    ]
                  )
                ]
              )
            ]
          )
        ]
      ),
      _vm._v(" "),
      _vm.searchText.length && _vm.searchedItems.length
        ? _c(
            "div",
            { staticClass: "search-suggestion-area" },
            _vm._l(_vm.searchedItems, function(item, index) {
              return _c(
                "div",
                { key: index, staticClass: "single-suggestion-item" },
                [
                  _c("router-link", { attrs: { to: item.url } }, [
                    _c("h4", {
                      domProps: { innerHTML: _vm._s(item.parentLabel) }
                    }),
                    _vm._v(" "),
                    _c("h6", {
                      domProps: { innerHTML: _vm._s("# " + item.label) }
                    }),
                    _vm._v(" "),
                    item.desc
                      ? _c("p", { domProps: { innerHTML: _vm._s(item.desc) } })
                      : _vm._e()
                  ])
                ],
                1
              )
            }),
            0
          )
        : _vm._e(),
      _vm._v(" "),
      _vm.searchText.length && !_vm.searchedItems.length
        ? _c("div", { staticClass: "search-suggestion-area" }, [
            _c("div", { staticClass: "single-suggestion-item" }, [
              _c("p", { staticClass: "text-danger" }, [
                _vm._v(
                  "\n                        " +
                    _vm._s(
                      _vm.__(
                        "Sorry ! Nothings found for your query. Please try again !",
                        "erp"
                      )
                    ) +
                    "\n                    "
                )
              ])
            ])
          ])
        : _vm._e()
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
    require("vue-hot-reload-api")      .rerender("data-v-e20e2202", esExports)
  }
}

/***/ }),
/* 435 */,
/* 436 */
/***/ (function(module, exports, __webpack_require__) {


var content = __webpack_require__(437);

if(typeof content === 'string') content = [[module.i, content, '']];

var transform;
var insertInto;



var options = {"hmr":true}

options.transform = transform
options.insertInto = undefined;

var update = __webpack_require__(21)(content, options);

if(content.locals) module.exports = content.locals;

if(false) {
	module.hot.accept("!!../../node_modules/css-loader/index.js!./flaticon.css", function() {
		var newContent = require("!!../../node_modules/css-loader/index.js!./flaticon.css");

		if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];

		var locals = (function(a, b) {
			var key, idx = 0;

			for(key in a) {
				if(!b || a[key] !== b[key]) return false;
				idx++;
			}

			for(key in b) idx--;

			return idx === 0;
		}(content.locals, newContent.locals));

		if(!locals) throw new Error('Aborting CSS HMR due to changed css-modules locals.');

		update(newContent);
	});

	module.hot.dispose(function() { update(); });
}

/***/ }),
/* 437 */
/***/ (function(module, exports, __webpack_require__) {

var escape = __webpack_require__(212);
exports = module.exports = __webpack_require__(20)(false);
// imports


// module
exports.push([module.i, "\t/*\n  \tFlaticon icon font: Flaticon\n  \tCreation date: 08/01/2019 12:54\n  \t*/\n\n@font-face {\n  font-family: \"Flaticon\";\n  src: url(" + escape(__webpack_require__(213)) + ");\n  src: url(" + escape(__webpack_require__(213)) + "?#iefix) format(\"embedded-opentype\"),\n       url(" + escape(__webpack_require__(438)) + ") format(\"woff\"),\n       url(" + escape(__webpack_require__(439)) + ") format(\"truetype\"),\n       url(" + escape(__webpack_require__(214)) + "#Flaticon) format(\"svg\");\n  font-weight: normal;\n  font-style: normal;\n}\n\n@media screen and (-webkit-min-device-pixel-ratio:0) {\n  @font-face {\n    font-family: \"Flaticon\";\n    src: url(" + escape(__webpack_require__(214)) + "#Flaticon) format(\"svg\");\n  }\n}\n\n[class^=\"flaticon-\"]:before, [class*=\" flaticon-\"]:before,\n[class^=\"flaticon-\"]:after, [class*=\" flaticon-\"]:after {\n  font-family: Flaticon;\n        font-size: 20px;\nfont-style: normal;\nmargin-left: 20px;\n}\n\n.flaticon-menu:before { content: \"\\F100\"; }\n.flaticon-arrow-down-sign-to-navigate:before { content: \"\\F101\"; }\n.flaticon-magnifying-glass:before { content: \"\\F102\"; }\n.flaticon-arrow-point-to-right:before { content: \"\\F103\"; }\n.flaticon-search-segment:before { content: \"\\F104\"; }\n.flaticon-edit:before { content: \"\\F105\"; }\n.flaticon-quick-edit:before { content: \"\\F106\"; }\n.flaticon-user:before { content: \"\\F107\"; }\n.flaticon-download:before { content: \"\\F108\"; }\n.flaticon-import:before { content: \"\\F109\"; }\n.flaticon-add-plus-button:before { content: \"\\F10A\"; }\n.flaticon-move:before { content: \"\\F10B\"; }\n.flaticon-arrow-right:before { content: \"\\F10C\"; }\n.flaticon-arrow-up:before { content: \"\\F10D\"; }\n.flaticon-trash:before { content: \"\\F10E\"; }\n.flaticon-leader:before { content: \"\\F10F\"; }\n.flaticon-opportunity:before { content: \"\\F110\"; }\n.flaticon-check:before { content: \"\\F111\"; }\n.flaticon-eye-close-up:before { content: \"\\F112\"; }\n.flaticon-printer:before { content: \"\\F113\"; }\n.flaticon-sent-mail:before { content: \"\\F114\"; }\n.flaticon-alarm-clock:before { content: \"\\F115\"; }\n.flaticon-share:before { content: \"\\F116\"; }\n.flaticon-link-symbol:before { content: \"\\F117\"; }\n.flaticon-copy-content:before { content: \"\\F118\"; }\n.flaticon-delete:before { content: \"\\F119\"; }\n.flaticon-filter-tool-black-shape:before { content: \"\\F11A\"; }\n.flaticon-printer-1:before { content: \"\\F11B\"; }\n.flaticon-settings-work-tool:before { content: \"\\F11C\"; }\n.flaticon-close:before { content: \"\\F11D\"; }\n.flaticon-menu-1:before { content: \"\\F11E\"; }\n.flaticon-locked-padlock:before { content: \"\\F11F\"; }\n.flaticon-left-arrow-key:before { content: \"\\F120\"; }\n.flaticon-tag:before { content: \"\\F121\"; }\n.flaticon-timeline:before { content: \"\\F122\"; }\n.flaticon-envelope:before { content: \"\\F123\"; }\n.flaticon-edit-1:before { content: \"\\F124\"; }\n.flaticon-notifications:before { content: \"\\F125\"; }\n.flaticon-calendar:before { content: \"\\F126\"; }\n.flaticon-list:before { content: \"\\F127\"; }\n.flaticon-document:before { content: \"\\F128\"; }\n.flaticon-cloud-storage-uploading-option:before { content: \"\\F129\"; }\n.flaticon-image:before { content: \"\\F12A\"; }\n.flaticon-group-contacts:before { content: \"\\F12B\"; }\n.flaticon-contact-book:before { content: \"\\F12C\"; }\n.flaticon-company:before { content: \"\\F12D\"; }\n.flaticon-info-circle:before { content: \"\\E004\"; }\n", ""]);

// exports


/***/ }),
/* 438 */
/***/ (function(module, exports) {

module.exports = "data:application/font-woff;base64,d09GRgABAAAAABhwAA0AAAAAJ5wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABGRlRNAAAYVAAAABoAAAAcfPIioE9TLzIAAAGgAAAASgAAAGBQW16HY21hcAAAAhwAAABHAAABSuIhFf9jdnQgAAACZAAAAAQAAAAEABEBRGdhc3AAABhMAAAACAAAAAj//wADZ2x5ZgAAAtAAABOMAAAfMJEyxPNoZWFkAAABMAAAAC8AAAA2EdPATWhoZWEAAAFgAAAAHwAAACQD8QHFaG10eAAAAewAAAAwAAAAbgTTATpsb2NhAAACaAAAAGYAAABmqJSghG1heHAAAAGAAAAAHwAAACAAhgEibmFtZQAAFlwAAAFBAAACcJ0XuHFwb3N0AAAXoAAAAKkAAAH2xn4NF3gBY2BkYADhI80TGOL5bb4ycDMxgMCNKPsXMPr////7mRgZDwK5HAxgaQBP7QzDAHgBY2BkYGA8+H8/gx4Tw///DAxMjEARVMAKAH97BLkAeAFjYGRgYDBi+MggxAACTEDMCBZzYNADCQAAIYwBqQB4AWNgYfzJ+IWBlYGB0YcxjYGBwR1Kf2WQZGhhYGBiYGNmgAFGAQYECEhzTWE4wKDwUZfxwP8DDHqMBxkcQWqQlCgwMAIAeVwMhgAAeAFj2M0gyAACq4D4BAMDEwPDATAfxMQNGJHUKDEYAGmYmDWKOlYo3wrCBQBFrgQQeAFjYGBghmIZBkYGEHAB8hjBfBYGDSDNBqQZGZgYFD7q/v/PwACkGf7///+YHyQKBYxsQAxjMwEJIEYBjCArhjcAAPZ/CRMAABEBRAAAACoAKgAqACoAQgBiAJYAtgDcAQYBQgGAAfwCXAJwAsQC9gMaA4QEDgUiBUIFbgWgBdAHHgdYB7IH5AgmCEwIkAk2CWQJ5Ao0CkoKogrsC0ALfAvGDKgM+A02DYIN6A6gDtoPmAAAeAGVeAWYG0eycNfMdPeQhkezWtFKo5VkWq2klcbOeu3NrX3+Hfu/GL44nNgH4RwzOsfMzMxk5zsGb/CY+Rz4/8f83jGPX/VIXiv0QJrprq6uhuouHCIRlxDyJjiPyISTuWNAOluPc4X8a+8Yo3dsPS5LCJJjskBTgT7OGfx563EQ+L7bd1t9N3Zf+KQrroDz0o+40CcEZ1o9tQrLsJrNaRAyXOgFDF8J39Wj4x8CgBXSA75zEoEDxCGkUe9Ac2HYq0Iy7OUDG8Cu6HcYYUW/5Ra9Ehp3zBl3iPrmmwXuDoI/iZBTqxLgegVSI2Q2HwYsxmmwmJMWtkm9ihQGnA0XmnUW5OE3RuS4xjXJysb9Z9XrZ+2/YN9Z9eZT9u177P79suE6kXl1s37WvgtGnRtXElv0PHYfIXDqFNkuEXiY2CdE+YDhIpzVmwvbYdiDwuQu9QpcMLlPI+NTwnNJcZ8hNppxrc5ZFcKgCvl+LxkO3GQIKXPSeYcx40Z+KdcotW404Lhl2caNxzTESMwGRIkzHvOMUI74Yj/QAnFeLTy3CJKIsyZsuzr9/VVwQtcXrtpoNXPpluJVj+caXHt1+ruroW6UjYWrN1pWWn7iVWXQxudI4CbCSUyIh8cvmKNDvBEbGD4RbrSfDJMwaSWReF//w9fjA0+ltMXoLTIYoMgKwJwimV2/+9Se34XVjKaOFJTeIikdKss6KJDr+r2n4nv6/uDFyItG1qO01JsDFAC8RT8MQkvBS+zAwhJskxNcfrgdKvJvH7pz/iXdc4GV2nsu29MuKgrolcGc2/ZKjHYWqtoPd3a3LC1t6ab/pspM4r5lGo7DqKqom3qtkAc6pWqwrkfWeF4lDXJIyA6eoLjWOUA2UQoldyHZBlEFQjfBpRHMR4GQr1o9RprBNlgEcU4boG5BGSoQ1bbBEg5HBIrxosCE8EzTeJg5Yx4xJR3ArBk5H+SiY3s5pkpG2ZSe262n75ek+hxIm5obrCCwNnBKgZlwwOTAjbZArW9tlFa1on5A1/dhBaDrKGVclnOOJAdm+qdQ45IqczgmwcwczM3c0lzvFX1QtRM6A4lrJwzmT/uwriHdk+9dyHcvX4aMq+bAFdvO2oKr0A36td4SLAz7fi8KM1wTm83ROWWy38xwggT+WTNNLV0vyo/ohqEfNgxYxrqZV3J6R8859PPc5zOcf55yNdJzOf11hv4RQQ4/FuUGHJSuGsZh3YAN058XBJHGcBCO8fnnuZXNQgis7d/A3cdhPIgH/UE/7Evk5Dkn8XmLKE5O0C2evl3caRz2e/kxA8jsIEa+xrwg3/1BXG8i2EPFRHbDPopjBH+i9CD3HhZrKjuXmvRcpmrxwzx+8P7R3/hvSSbRIxsYZvusEpKMT7PWQk0YbyQ4c961sum6Blxwvaq6aqR+TsUnQlC9Lv3Qza4xZbrXq4apuojXInw1B6vrx2cGx0b2ZzjBoTu61YEby5R+R1blb1PKH8uU9yn8MT/K0R/L8o9pLv0tZYyCTgjDeb6R6WuBlEmL9Ml2socQOCNDYvd4rKcFKcYD5jV51NdKBsIQI2m+N3wgGJ7MDYOnb1Z1XT2i82dzPb7ssXChjbh/4DpcbVgGPrvGlW7r+MArNTbPtKyoibrmui9Iv89Vlc+f4JrGH6ggZGxP4Tjy1CZ7ySWEAOPZv95qjv5oDxYSUeK/34sqwhqGeTQH4Rmehfyg42o1M8ZtiNB2VAGVdEGoSMYbdgwH8BB12rW56dRsU7WdaY1pCgXbMKqqHZYbVccGACZrlzKaPkLhxfwRqkpTlMucdRgqOZ3C90i+uMjoHoVzZQ9lAHWlYanijlSrodQLNpMZC2fqOK1TMDdWVcOQmf3nA5RzemB6nb5OUuguK2C6zgJrF1UkRMWisyEKgj8dz+TWTCY53nOPPJhcRB5HXkTeQT5FbjvjH5I8F5IZisYScJYJVuQL/RLqVYXMmpeFP2+NTGWUX9M2G6gQbTEoHhlMPLyIIr4/NqstnHRtAhD1xELZmL63Zq3GM2SLQp6t7Uqgx2uvkSI2yff+Yb5Wm6/t13XFVBTVVKke6GXZpCVdx6eMLYpYRTGPi5NaT43IqFFaw4rCLWNg/VpX+knDKDEhfBt1BbScVlJy0Mc2QkZ+3DWbE/0CYhky/StRamew38ur7OVMzU/vgV69U6t1Xmzg5iaWwR2kDzKNmmlOrq+cFDvFHZcUg2Y7V5CpEgKnGfuIka2jS/J4eQW2TG5FdKZfRMzkjsFb2/lGqiP+1sKMTTWN2jOFMhnHb90sFnEI0YDdM4KDf0//5M17z3PrXfeSO9xuHQ6l/+Z5L/X6De/w33uNPpEJkCk4BVeRHAlJIZOsKM8tqGdql++NIzeMHj/2/hfX0U++eFR97PDKyuGVda+FKy/cTenuC7PyphWBnX7ta4k64SNM3JubzV4mFUJm+4Na2A9jLOMEEOi66DxkBELRgFfPpq+efaREHjmbnrzjjjs+82r8PanRmP3MZ2Zh96vTV3/mM7C7cU5jZFN/gGt8jEwTQvJctkDEhHE9bg5aKOE8k/RkFv7I4dnA5Uv2SAoANaRrX6goL1MU+AV8HJX8oYoEGw/JEn2JIj3qSknaKWnS3+OEEzwEpEQuIJeRh5PryCvJfwifHWwAC2IRLgnj2StDPmLjw0q4iIJ5NNJNtGIcX1SYOVSMJGuIK4qE7kVYJ9jMbkv0DOeAR0OaNC3JG083m1HOdxmPsai35rtxE4tWpsRJH2fo5+VhIjQP23F9YRGauEqcrTkJhUF9CcS+BKpzD6gv+MlHeOH2mSpjK5s4giOatl32TTuwDS+g6zLb/017ZXlYZExSpWr3kd0q1owVh8srdkPmUrh+MZSktr3y6RW7LUnh4vpQwisqtZ29Rrppr9Muya/99A03fPoGeAkUi1Ccg851GPsVYXp6rTFfD7mqqdyTVE1TJY9LMpWoysO6goCkKrTR9toNqqiSRGVFUFOJSRL3QQwAX4wW1NReI7XpCde0Q9v0pYs07SIaTGcu7Av2zsvPvr6K7MhyZWqqIsvIS/X6sy/fidzI2e5VaZ21dau1DgHBnCzvKcnmHsfZY8qldvqYGz5zww2fuQk613agU8Rf5wyYfjdfVxSHKvE6d12sgIJGrZ7nFChXT/OmZk2kw04YE1Ino1O1STJNvUc+0xTSmHkDIV1sZJVRqkaBfCBuX8gjShW8s5Uk/zdR3lbqNJOkuan0DgWbSbNTejtV3lF6WtYw7X2zw4cM4/05oymom/st08jtR3WbyFFKZAuuOnLQ6JPRYvS2g5BWG5o8k2WaT9ayM/TTnPFmS3QzjDAS+Onh9qPbhr4JGDWDzcHhoL05qK+b291+9L7056O6fT8kOKxtOJtAcc0AMZvbQd3hu9v7Hn37qGrfp5/IpI7ndBeek0qmcN8EwhomzXhEFLMc90zY3Qe35sLV6T/MFX24zC8W/VMkKBYDIKcIrHbSL8DOoHhpMUi/ILCikb4UdqZfIIRQMo9rfDfz1UZmK9rZOllQ3MIQNrIgDKJYrrGx245aaAdaPOJREsHj0y8eec6RbQ9fPFh8Th78zuU7dly+cqzZnGo2C9kDr925c+NGTdN+JB/a2mnBuc9HgpXLz8POVmtEcq/8FtXXDUa6ja5WGHLMIyDFzNaiVOSyNxqMOfBdB45Zxo3prxwmIVL/5I2GZQt+yKnvSgQ+iZCWZbgdQpJBrQy1BLnh+PphbQlq0Wnfj3AZ4tPOvgzYeVQc3Mzq6uoMnh9Znmq3p2bS1ZmshuX55eXDR2dmlmcQRvBwe+qdU+1lRKwBmbwB8nQKTpAryCNFBsED1orrjDPhorBqobHEo81nJq6OXKKMCTRWGPzNZeYZsVFvKLoQQrgC2DXC5rOJBHLk8rAPAx9lbHYhVRSNyW6ZcTWnq36ka2bDcXzIWZZtUxXAd52GqemRp+o51Sq71FAUsLzq5ADbxQHl8n3IOSt7TE/v3tXr7erdkRH4jhuP+rWcynjFy4Ekacyr8NF8eUMz4xkfR5Zz90esKGeIvUgQ4275k3tiiYlcrIgnec/vJ62JiIHDpq9r/rT+ta/p07729Ql4dRIzARPirc3NiZr5+gKZJnUSkw1kI1kgA7JEtmEEu4ucS/YRQnDtzAE6p4GF08DwgbsaD9wFy7PD2Xs9q/dFpUfvi4O3ZNVyOq5Pjqv/BVbkL4sop7fBKnI9T87C872fbCzp9StQBj7ETKu1HuLMJITjnOR0+rUI8CED88v046LUL1qmdPmiZ5mRIzHJiUyN1Zm2lOxNLtB/EVr/ZIeh/U9W2Dn7MoDLzu680fTcAkDB9cxdImC8u4PmvtU5k99eS1TcW1iLeCuJuETS665ofeQjrSvu2Hxl+yMfaV+JZGhFTp36HaTwIuRlMyHJKMoQeZTIt0TKGrMWE/mwl5ny+eEgj3qDSoRaN9+MoybWCL/4mab5ZTd6rFfQ+KYi1KbmCijjXzZnXun7RR2C6WJnSlK8qCUBcH3Kl01dfpZRM064ADDlKZJfhPpm0Az1hAGH/KKvgcjROKUo2w7MyYqec0w9420BedtL+gid8Xdr/nCtNekVM3c4asGXW2jPGT0IxWZJWmQ5do1UHKGuFYC0lVr0oAR7S80iHGBceAbOFiXpGsr9kkAKQCDpVkk6iHDmJ38rAbyYVIWf9EXcJwxwFhWKvyXH3fqcjOaLDpO1FFbksxWp3+1tQ1XsetWpPLzXL5bCa4uyppmezbntmZ48/VD4oGsW/fQSv5hzrmw5Qd2264HThZy53stXin5+nX2OLzGuGpQaak7yVyIj91dij39l6btKJhqwnBkTQtf01iN5vO8KqRECkz6xxmO3NkhGAtOCJLJhEMHqbDFdLc7OFsFe/g4s2zuiHTt2RCvpf3xzxzcvWgY7/UXWuVxEXVn9zq2iG9/0F99Y+WZ+GfVFwXVvw3W/QDixSUw2oVTOQSupQDRrAY+RfxpGIS4s9GacwVZAxk31A3GA8C2522p0NsvpK9pduS9v7sB8P72iv5TkZ/LJ0pvDyhWKXqT/2jtUbsRXL72vfKi3dHX846MLhcLC0aULcp6Tzzte7oL0Q5VQ1wsURL5NT52AP+FZ2HhrHbIdbdXl5AbyKvIucoyQJPv4diabjf6bNv9f0vsVmAxGkrFhWIL+GBrIaCKwO/uOgbI0nBic2Zr+vdpf4HSd+DyxjvJHcNqmHAsBCkTW9fX/jmC3N41SdpVfnPYq4UxYeWVW2qaafkg1TRXOV830/eJTUVV8KfqdKDLo/acn4Mr9QNdkKz1QAQ+CaT99nZBVuNafhm1huRxuy8r0i18Qy4oCFnV+BdfvVRBlzQ6XySzZJCwC3DPIWxSGKtkmLkD8hZsXGa7vZh8x3ZGgw9udKHLSW0WZmI6lsGq7yhTLMS93DMMxX9z+l/Y/ll68u7QJHoN0bxN08DAnylHF8qemfEuhubbpmKaTXgQPa7fTt/2i9KL/U9ok7ALBAo6SfPatndnSVjjzybcvilhBK2GDBdmWF8RX737t/Zvdi6VG4fxC43WFxl9Dz1IvQVVPf6qPAGh/rxBcJwEFYIVGo6A1Chvrqqrq1+UCrmradVaQra3g2k8nRTIgpJEfmx2hYaLAxSMsEM0ZuqgOZMF6JnxnZBF+sGnr+vTO7oNnZ2DflnU7Hb9QbhtHqHSZpOKrKFupvCDTrbdvjjdsfXCvvmir8pZ9nSbmeoFbpbIiXyrjo8j0MplS+TJCCCdn4Z3djncmE514pEE2ou6tELIdotivJ4Monh+5+XyQ9CevUuyLjS8wP8RkEWxoNWOxbbzaGl5k/MqVZWMXhysPt3Lim0rlCQUv/aZXKHi3h+bs9PTs9BaVJYpFK9sVlStHqCLrYJ6bL67fcdFFQPjlVzxWM9H07klv96anPfigN/0EOz9VFEO/6ap7GLD6hYw9G48Y1k3PEm0iDrLJFOliLHCQPIY8g7yIkNOhjTKuuTAAAzxxPlLcBJ2pCBCyj5XZsScZkxjDZvgROfIqCPggG+ALZIg881bMRTXIYvCxs+FIPJ6tPyvguJbBH8k+a8G2Wqde79yssfWWO/1jpoZOYU/BCTk/j2mIfGzBszYw9aQTquzoeoE6j3NBUy/6tsZsp/HhhmMzzc509Tq/eI2g+bUowBNl+l1RvkMsgsuJ8pdM3VKYUdkhZ0oqlaQpx5E0U91ieYUt6vOxeQj7VVOTHNFPTgXFGrBmk0GtGBxGV+sf1rJrPHtUZX6ELGfnrRGfFFF2SCYYroj71zTfzY+TLh7kh1mc5edmZnI+3GkHgZ2+57jY3kx8ycWDvUP4oG6lRy0drrL09JO6BUcvqW6oVje8rxCGhdYWdOZ8wmc2yJDsJheT68mHCJldGCz0e/1eGIRBXI/rg/lB696ohWQNGvWUodXrYv8Edr6fZODk0FYGTQ6VB5i/9JcAG8l8Ust389g/H+a7SBlhXMm6LK5vAEEcY7BZD4My1Ls4vDXfghnL93LiPZoLvJwZerlVy/NzovFOz8p5vmVpL/IgZwVuzoJXuWBhN0Kf0KoQhRT2QgGqkOcSqIXi25gNVcmW3hqHqnTUlbKJLU1zJCvn46TwWBwvps5h5QaW5bvWjOWEGd2y5XnZ5OlTzZLEbvpUHKli1o/+qwIhKCYU2NmYksI5LKTkPwGRMsMjeAEsxhcAAmAYhsHnX197uLQcw/ZwzTHNGu6QWxY3HdMcshyb79EBZfY4AKDLRndkueke46EHWq6tR7JurieqbqsbWXck4GIOWIHuqHLVPUXuemDKU49U3UxP1N1CN6pux4h3n1ONHTEQxXSU6cLjsZuFKtylTW36ZmbfPgrzvs8odRB4GOhDKPhY6a/QsBcggbCakXKeMLtHjYo5IzrEL1MHGobxmBsDpcEeFiV6240+djV6VppdYf8EuOnEGyRU/qquGi9IRM3pkKj7uhru6y4W5WijjpNhaPaWFbEbPXd1H+lKBt7fowCpk14AgrFReEMa1MweIAg5GFGykDAdGQ/R30P9/PfW3eICNs2BYdwB3/nt1Tsq0+2FfeEYe3eQ2yMEHXqOXkEMxYYGI0VxQbo+5T9jbG2MUYe+PQG5/2qLAAAAeAFkykOiwwAUBdCc8KN2atv7H3Rl5bSPV0EYfOpxC/LX+67ra71SkVAklkhlfvz686+gqKSsoqqmrqGppa0j19XTNzA0MjYxNTO3sLSytrG1s3dwdMqeLc2lAQNBAACwkR6O3fMsZdf5i1GReT7Ox9D3HDgyMDIxs7CycebClRt3Hn+HngNHBkYmZhZWNs5cuHLjTv/Yc+DIwMjEzMLKxpkLV25vJ4NsGwAAAAAAAAH//wACeAFjYGBgZACCM7aLzoPoG1H2L2A0AEw/By4AAA=="

/***/ }),
/* 439 */
/***/ (function(module, exports) {

module.exports = "data:application/x-font-ttf;base64,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AAEACADPAAEAAAAAAAIABgDmAAEAAAAAAAMAIwE1AAEAAAAAAAQACAFrAAEAAAAAAAUAEAGWAAEAAAAAAAYACAG5AAMAAQQJAAAAfAAAAAMAAQQJAAEAEAC9AAMAAQQJAAIADADYAAMAAQQJAAMARgDtAAMAAQQJAAQAEAFZAAMAAQQJAAUAIAF0AAMAAQQJAAYAEAGnAEMAcgBlAGEAdABlAGQAIABiAHkAIABBAHAAYQBjAGgAZQAgAHcAaQB0AGgAIABGAG8AbgB0AEYAbwByAGcAZQAgADIALgAwACAAKABoAHQAdABwADoALwAvAGYAbwBuAHQAZgBvAHIAZwBlAC4AcwBmAC4AbgBlAHQAKQAAQ3JlYXRlZCBieSBBcGFjaGUgd2l0aCBGb250Rm9yZ2UgMi4wIChodHRwOi8vZm9udGZvcmdlLnNmLm5ldCkAAEYAbABhAHQAaQBjAG8AbgAARmxhdGljb24AAE0AZQBkAGkAdQBtAABNZWRpdW0AAEYAbwBuAHQARgBvAHIAZwBlACAAMgAuADAAIAA6ACAARgBsAGEAdABpAGMAbwBuACAAOgAgADgALQAxAC0AMgAwADEAOQAARm9udEZvcmdlIDIuMCA6IEZsYXRpY29uIDogOC0xLTIwMTkAAEYAbABhAHQAaQBjAG8AbgAARmxhdGljb24AAFYAZQByAHMAaQBvAG4AIAAwADAAMQAuADAAMAAwACAAAFZlcnNpb24gMDAxLjAwMCAAAEYAbABhAHQAaQBjAG8AbgAARmxhdGljb24AAAIAAAAAAAD/wAAZAAAAAAAAAAAAAAAAAAAAAAAAAAAAMgAAAAEAAgADAQIBAwEEAQUBBgEHAQgBCQEKAQsBDAENAQ4BDwEQAREBEgETARQBFQEWARcBGAEZARoBGwEcAR0BHgEfASABIQEiASMBJAElASYBJwEoASkBKgErASwBLQEuAS8HdW5pRjEwMAd1bmlGMTAxB3VuaUYxMDIHdW5pRjEwMwd1bmlGMTA0B3VuaUYxMDUHdW5pRjEwNgd1bmlGMTA3B3VuaUYxMDgHdW5pRjEwOQd1bmlGMTBBB3VuaUYxMEIHdW5pRjEwQwd1bmlGMTBEB3VuaUYxMEUHdW5pRjEwRgd1bmlGMTEwB3VuaUYxMTEHdW5pRjExMgd1bmlGMTEzB3VuaUYxMTQHdW5pRjExNQd1bmlGMTE2B3VuaUYxMTcHdW5pRjExOAd1bmlGMTE5B3VuaUYxMUEHdW5pRjExQgd1bmlGMTFDB3VuaUYxMUQHdW5pRjExRQd1bmlGMTFGB3VuaUYxMjAHdW5pRjEyMQd1bmlGMTIyB3VuaUYxMjMHdW5pRjEyNAd1bmlGMTI1B3VuaUYxMjYHdW5pRjEyNwd1bmlGMTI4B3VuaUYxMjkHdW5pRjEyQQd1bmlGMTJCB3VuaUYxMkMHdW5pRjEyRAAAAAAAAf//AAIAAAABAAAAAMw9os8AAAAA2Fo/6AAAAADYWj/o"

/***/ }),
/* 440 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", { attrs: { id: "erp-settings" } }, [
    _c("div", { staticClass: "settings-area" }, [
      _c("div", { staticClass: "settings-sidebar" }, [_c("settings-menu")], 1),
      _vm._v(" "),
      _c(
        "div",
        { staticClass: "settings-content" },
        [
          _c("h2", {
            attrs: {
              role: "erp-wp-notice",
              "data-text":
                "Don't remove me, I am super important for admin notice"
            }
          }),
          _vm._v(" "),
          _c("search-bar"),
          _vm._v(" "),
          _c("router-view"),
          _vm._v(" "),
          _c("loading", {
            attrs: {
              active: _vm.loader,
              loader: "spinner",
              color: "#1a9ed4",
              opacity: 0.8,
              isFullPage: false,
              width: 45
            },
            on: {
              "update:active": function($event) {
                _vm.loader = $event
              }
            }
          })
        ],
        1
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
    require("vue-hot-reload-api")      .rerender("data-v-bbad6d40", esExports)
  }
}

/***/ }),
/* 441 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vue__ = __webpack_require__(19);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_vue_router__ = __webpack_require__(130);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__components_general_GeneralSettings_vue__ = __webpack_require__(442);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__components_hr_workdays_HRWorkDays_vue__ = __webpack_require__(444);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4__components_hr_leave_HRLeave_vue__ = __webpack_require__(446);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5__components_hr_leave_years_HRLeaveYears_vue__ = __webpack_require__(448);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6__components_hr_miscellaneous_HRMiscellaneous_vue__ = __webpack_require__(450);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_7__components_act_customer_AcCustomer_vue__ = __webpack_require__(452);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_8__components_act_currency_AcCurrency_vue__ = __webpack_require__(454);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_9__components_act_financial_year_AcFinancialYears_vue__ = __webpack_require__(456);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_10__components_crm_contacts_CrmContacts_vue__ = __webpack_require__(458);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_11__components_crm_contact_forms_CrmContactForm_vue__ = __webpack_require__(460);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_12__components_crm_subscription_CrmSubscription_vue__ = __webpack_require__(466);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_13__components_woocommerce_WooCommerce_vue__ = __webpack_require__(468);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_14__components_email_general_GeneralEmail_vue__ = __webpack_require__(470);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_15__components_email_email_connect_EmailConnect_vue__ = __webpack_require__(472);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_16__components_email_email_connect_SmtpEmail_vue__ = __webpack_require__(231);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_17__components_email_templates_EmailTemplate_vue__ = __webpack_require__(481);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_18__components_email_notifications_EmailNotification_vue__ = __webpack_require__(483);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_19__components_integration_Integration_vue__ = __webpack_require__(486);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_20__components_license_License_vue__ = __webpack_require__(488);


 // HRM Components




 // Accounting Components



 // CRM Components



 // WooCommerce Components

 // Email Components





 // Integration Components

 // Old seperate extensions' license components


__WEBPACK_IMPORTED_MODULE_0_vue__["default"].use(__WEBPACK_IMPORTED_MODULE_1_vue_router__["a" /* default */]);
/* harmony default export */ __webpack_exports__["a"] = (new __WEBPACK_IMPORTED_MODULE_1_vue_router__["a" /* default */]({
  linkActiveClass: 'router-link-active',
  routes: settings.hooks.applyFilters('erp_settings_admin_routes', [{
    path: '/',
    component: __WEBPACK_IMPORTED_MODULE_2__components_general_GeneralSettings_vue__["a" /* default */],
    children: [{
      path: 'general',
      name: 'GeneralSettings',
      component: __WEBPACK_IMPORTED_MODULE_2__components_general_GeneralSettings_vue__["a" /* default */],
      alias: '/'
    }]
  }, {
    path: '/erp-hr',
    name: 'HR',
    component: {
      render: function render(c) {
        return c('router-view');
      }
    },
    children: [{
      path: 'workdays',
      name: 'HRWorkDays',
      component: __WEBPACK_IMPORTED_MODULE_3__components_hr_workdays_HRWorkDays_vue__["a" /* default */],
      alias: '/erp-hr'
    }, {
      path: 'leave',
      name: 'HRLeave',
      component: __WEBPACK_IMPORTED_MODULE_4__components_hr_leave_HRLeave_vue__["a" /* default */]
    }, {
      path: 'financial',
      name: 'HRLeaveYears',
      component: __WEBPACK_IMPORTED_MODULE_5__components_hr_leave_years_HRLeaveYears_vue__["a" /* default */]
    }, {
      path: 'miscellaneous',
      name: 'HRMiscellaneous',
      component: __WEBPACK_IMPORTED_MODULE_6__components_hr_miscellaneous_HRMiscellaneous_vue__["a" /* default */]
    }]
  }, {
    path: '/erp-ac',
    name: 'Ac',
    component: {
      render: function render(c) {
        return c('router-view');
      }
    },
    children: [{
      path: 'customers',
      name: 'AcCustomer',
      component: __WEBPACK_IMPORTED_MODULE_7__components_act_customer_AcCustomer_vue__["a" /* default */],
      alias: '/erp-ac'
    }, {
      path: 'currency_option',
      name: 'AcCurrency',
      component: __WEBPACK_IMPORTED_MODULE_8__components_act_currency_AcCurrency_vue__["a" /* default */]
    }, {
      path: 'opening_balance',
      name: 'AcFinancialYears',
      component: __WEBPACK_IMPORTED_MODULE_9__components_act_financial_year_AcFinancialYears_vue__["a" /* default */]
    }]
  }, {
    path: '/erp-crm',
    name: 'Crm',
    component: {
      render: function render(c) {
        return c('router-view');
      }
    },
    children: [{
      path: 'contacts',
      name: 'CrmContacts',
      component: __WEBPACK_IMPORTED_MODULE_10__components_crm_contacts_CrmContacts_vue__["a" /* default */],
      alias: '/erp-crm'
    }, {
      path: 'contact_forms',
      name: 'CrmContactFormLayout',
      component: {
        render: function render(c) {
          return c('router-view');
        }
      },
      children: [{
        path: '',
        component: __WEBPACK_IMPORTED_MODULE_11__components_crm_contact_forms_CrmContactForm_vue__["a" /* default */]
      }, {
        path: ':id',
        // All forms will be added automatically if added on backend.
        name: 'CrmContactForm',
        component: __WEBPACK_IMPORTED_MODULE_11__components_crm_contact_forms_CrmContactForm_vue__["a" /* default */]
      }]
    }, {
      path: 'subscription',
      name: 'CrmSubscription',
      component: __WEBPACK_IMPORTED_MODULE_12__components_crm_subscription_CrmSubscription_vue__["a" /* default */]
    }]
  }, {
    path: '/erp-woocommerce',
    name: 'WooCommerce',
    component: __WEBPACK_IMPORTED_MODULE_13__components_woocommerce_WooCommerce_vue__["a" /* default */]
  }, {
    path: '/erp-email',
    name: 'Email',
    component: {
      render: function render(c) {
        return c('router-view');
      }
    },
    children: [{
      path: 'general',
      name: 'GeneralEmail',
      component: __WEBPACK_IMPORTED_MODULE_14__components_email_general_GeneralEmail_vue__["a" /* default */],
      alias: '/erp-email'
    }, {
      path: 'email_connect',
      name: 'EmailConnect',
      component: __WEBPACK_IMPORTED_MODULE_15__components_email_email_connect_EmailConnect_vue__["a" /* default */]
    }, {
      path: 'smtp',
      name: 'SmtpEmail',
      component: __WEBPACK_IMPORTED_MODULE_16__components_email_email_connect_SmtpEmail_vue__["a" /* default */]
    }, {
      path: 'templates',
      name: 'EmailTemplate',
      component: __WEBPACK_IMPORTED_MODULE_17__components_email_templates_EmailTemplate_vue__["a" /* default */]
    }, {
      path: 'notification',
      name: 'EmailNotification',
      component: __WEBPACK_IMPORTED_MODULE_18__components_email_notifications_EmailNotification_vue__["a" /* default */]
    }]
  }, {
    name: 'erp-integration-root',
    path: '/erp-integration',
    component: {
      render: function render(c) {
        return c('router-view');
      }
    },
    children: [{
      path: '',
      name: 'Integration',
      component: __WEBPACK_IMPORTED_MODULE_19__components_integration_Integration_vue__["a" /* default */]
    }]
  }, {
    path: '/erp-license',
    name: 'License',
    component: __WEBPACK_IMPORTED_MODULE_20__components_license_License_vue__["a" /* default */]
  }])
}));

/***/ }),
/* 442 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_GeneralSettings_vue__ = __webpack_require__(215);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_c22bae48_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_GeneralSettings_vue__ = __webpack_require__(443);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_GeneralSettings_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_c22bae48_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_GeneralSettings_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/general/GeneralSettings.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-c22bae48", Component.options)
  } else {
    hotAPI.reload("data-v-c22bae48", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 443 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("base-layout", {
    attrs: {
      section_id: "general",
      sub_section_id: "general",
      enable_content: true
    }
  })
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-c22bae48", esExports)
  }
}

/***/ }),
/* 444 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_HRWorkDays_vue__ = __webpack_require__(216);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_33318d28_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_HRWorkDays_vue__ = __webpack_require__(445);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_HRWorkDays_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_33318d28_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_HRWorkDays_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/hr/workdays/HRWorkDays.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-33318d28", Component.options)
  } else {
    hotAPI.reload("data-v-33318d28", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 445 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("base-layout", {
    attrs: {
      section_id: "erp-hr",
      sub_section_id: "workdays",
      enable_content: true
    }
  })
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-33318d28", esExports)
  }
}

/***/ }),
/* 446 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_HRLeave_vue__ = __webpack_require__(217);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_291a3468_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_HRLeave_vue__ = __webpack_require__(447);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_HRLeave_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_291a3468_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_HRLeave_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/hr/leave/HRLeave.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-291a3468", Component.options)
  } else {
    hotAPI.reload("data-v-291a3468", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 447 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("base-layout", {
    attrs: {
      section_id: "erp-hr",
      sub_section_id: "leave",
      enable_content: true
    }
  })
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-291a3468", esExports)
  }
}

/***/ }),
/* 448 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_HRLeaveYears_vue__ = __webpack_require__(218);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_d3895756_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_HRLeaveYears_vue__ = __webpack_require__(449);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_HRLeaveYears_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_d3895756_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_HRLeaveYears_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/hr/leave-years/HRLeaveYears.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-d3895756", Component.options)
  } else {
    hotAPI.reload("data-v-d3895756", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 449 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "base-layout",
    { attrs: { section_id: "erp-hr", sub_section_id: "financial" } },
    [
      _c(
        "form",
        {
          staticClass: "wperp-form",
          attrs: { action: "", method: "post" },
          on: {
            submit: function($event) {
              $event.preventDefault()
              return _vm.submitHRLeaveYearsForm.apply(null, arguments)
            }
          }
        },
        [
          _c("div", { staticClass: "wperp-row" }, [
            _c(
              "div",
              {
                staticClass:
                  "wperp-form-group wperp-col-sm-4 wperp-col-xs-12 margin-bottom-10"
              },
              [_c("label", [_vm._v(" " + _vm._s(_vm.__("Name", "erp")))])]
            ),
            _vm._v(" "),
            _c(
              "div",
              {
                staticClass:
                  "wperp-form-group wperp-col-sm-4 wperp-col-xs-12 margin-bottom-10"
              },
              [_c("label", [_vm._v(" " + _vm._s(_vm.__("Start Date", "erp")))])]
            ),
            _vm._v(" "),
            _c(
              "div",
              {
                staticClass:
                  "wperp-form-group wperp-col-sm-4 wperp-col-xs-12 margin-bottom-10"
              },
              [_c("label", [_vm._v(" " + _vm._s(_vm.__("End Date", "erp")))])]
            )
          ]),
          _vm._v(" "),
          _vm._l(_vm.years_data, function(year, index) {
            return _c("div", { key: index, staticClass: "wperp-row" }, [
              _c(
                "div",
                {
                  staticClass:
                    " wperp-form-group wperp-col-sm-4 wperp-col-xs-12 margin-bottom-10"
                },
                [
                  _c("input", {
                    directives: [
                      {
                        name: "model",
                        rawName: "v-model",
                        value: year.fy_name,
                        expression: "year.fy_name"
                      }
                    ],
                    staticClass: "wperp-form-field",
                    domProps: { value: year.fy_name },
                    on: {
                      input: function($event) {
                        if ($event.target.composing) {
                          return
                        }
                        _vm.$set(year, "fy_name", $event.target.value)
                      }
                    }
                  })
                ]
              ),
              _vm._v(" "),
              _c(
                "div",
                {
                  staticClass:
                    "wperp-form-group wperp-col-sm-4 wperp-col-xs-12 margin-bottom-10"
                },
                [
                  _c("date-picker", {
                    staticClass: "wperp-form-field",
                    attrs: { placeholder: _vm.__("Start date", "erp") },
                    model: {
                      value: year.start_date,
                      callback: function($$v) {
                        _vm.$set(year, "start_date", $$v)
                      },
                      expression: "year.start_date"
                    }
                  })
                ],
                1
              ),
              _vm._v(" "),
              _c(
                "div",
                {
                  staticClass:
                    "wperp-form-group wperp-col-sm-4 wperp-col-xs-12 margin-bottom-10"
                },
                [
                  _c("date-picker", {
                    staticClass: "wperp-form-field",
                    attrs: { placeholder: _vm.__("End date", "erp") },
                    model: {
                      value: year.end_date,
                      callback: function($$v) {
                        _vm.$set(year, "end_date", $$v)
                      },
                      expression: "year.end_date"
                    }
                  }),
                  _vm._v(" "),
                  index > 0
                    ? _c(
                        "span",
                        {
                          staticClass: "settings-btn-cancel",
                          on: {
                            click: function($event) {
                              return _vm.deleteYear(index)
                            }
                          }
                        },
                        [_vm._v("x")]
                      )
                    : _vm._e()
                ],
                1
              )
            ])
          }),
          _vm._v(" "),
          _c("div", { staticClass: "wperp-form-group" }, [
            _c(
              "button",
              {
                staticClass: "wperp-btn wperp-btn-default",
                attrs: { type: "button" },
                on: { click: _vm.addNewYear }
              },
              [
                _vm._v(
                  "\n                + " +
                    _vm._s(_vm.__("Add New", "erp")) +
                    "\n            "
                )
              ]
            )
          ]),
          _vm._v(" "),
          _c(
            "div",
            { staticClass: "wperp-form-group" },
            [
              _c("submit-button", {
                attrs: { text: _vm.__("Save Changes", "erp") }
              }),
              _vm._v(" "),
              _c("div", { staticClass: "clearfix" })
            ],
            1
          )
        ],
        2
      )
    ]
  )
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-d3895756", esExports)
  }
}

/***/ }),
/* 450 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_HRMiscellaneous_vue__ = __webpack_require__(219);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_3fa9f5d6_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_HRMiscellaneous_vue__ = __webpack_require__(451);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_HRMiscellaneous_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_3fa9f5d6_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_HRMiscellaneous_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/hr/miscellaneous/HRMiscellaneous.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-3fa9f5d6", Component.options)
  } else {
    hotAPI.reload("data-v-3fa9f5d6", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 451 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("base-layout", {
    attrs: {
      section_id: "erp-hr",
      sub_section_id: "miscellaneous",
      enable_content: true
    }
  })
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-3fa9f5d6", esExports)
  }
}

/***/ }),
/* 452 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_AcCustomer_vue__ = __webpack_require__(220);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_05b841e8_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_AcCustomer_vue__ = __webpack_require__(453);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_AcCustomer_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_05b841e8_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_AcCustomer_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/act/customer/AcCustomer.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-05b841e8", Component.options)
  } else {
    hotAPI.reload("data-v-05b841e8", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 453 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("base-layout", {
    attrs: {
      section_id: "erp-ac",
      sub_section_id: "customers",
      enable_content: true
    }
  })
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-05b841e8", esExports)
  }
}

/***/ }),
/* 454 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_AcCurrency_vue__ = __webpack_require__(221);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_6d1bc12c_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_AcCurrency_vue__ = __webpack_require__(455);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_AcCurrency_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_6d1bc12c_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_AcCurrency_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/act/currency/AcCurrency.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-6d1bc12c", Component.options)
  } else {
    hotAPI.reload("data-v-6d1bc12c", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 455 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("base-layout", {
    attrs: {
      section_id: "erp-ac",
      sub_section_id: "currency_option",
      enable_content: true
    }
  })
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-6d1bc12c", esExports)
  }
}

/***/ }),
/* 456 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_AcFinancialYears_vue__ = __webpack_require__(222);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_50cf8a78_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_AcFinancialYears_vue__ = __webpack_require__(457);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_AcFinancialYears_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_50cf8a78_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_AcFinancialYears_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/act/financial-year/AcFinancialYears.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-50cf8a78", Component.options)
  } else {
    hotAPI.reload("data-v-50cf8a78", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 457 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "base-layout",
    { attrs: { section_id: "erp-ac", sub_section_id: "opening_balance" } },
    [
      _c(
        "form",
        {
          staticClass: "wperp-form",
          attrs: { action: "", method: "post" },
          on: {
            submit: function($event) {
              $event.preventDefault()
              return _vm.submitAcFinancialYearsForm.apply(null, arguments)
            }
          }
        },
        [
          _c("div", { staticClass: "wperp-row" }, [
            _c(
              "div",
              {
                staticClass:
                  " wperp-form-group wperp-col-sm-4 wperp-col-xs-12 margin-bottom-10"
              },
              [_c("label", [_vm._v(" " + _vm._s(_vm.__("Name", "erp")))])]
            ),
            _vm._v(" "),
            _c(
              "div",
              {
                staticClass:
                  " wperp-form-group wperp-col-sm-4 wperp-col-xs-12 margin-bottom-10"
              },
              [_c("label", [_vm._v(" " + _vm._s(_vm.__("Start Date", "erp")))])]
            ),
            _vm._v(" "),
            _c(
              "div",
              {
                staticClass:
                  " wperp-form-group wperp-col-sm-4 wperp-col-xs-12 margin-bottom-10"
              },
              [_c("label", [_vm._v(" " + _vm._s(_vm.__("End Date", "erp")))])]
            )
          ]),
          _vm._v(" "),
          _vm._l(_vm.years_data, function(year, index) {
            return _c("div", { key: index, staticClass: "wperp-row" }, [
              _c(
                "div",
                {
                  staticClass:
                    "wperp-form-group wperp-col-sm-4 wperp-col-xs-12 margin-bottom-10"
                },
                [
                  _c("input", {
                    directives: [
                      {
                        name: "model",
                        rawName: "v-model",
                        value: year.name,
                        expression: "year.name"
                      }
                    ],
                    staticClass: "wperp-form-field",
                    domProps: { value: year.name },
                    on: {
                      input: function($event) {
                        if ($event.target.composing) {
                          return
                        }
                        _vm.$set(year, "name", $event.target.value)
                      }
                    }
                  })
                ]
              ),
              _vm._v(" "),
              _c(
                "div",
                {
                  staticClass:
                    "wperp-form-group wperp-col-sm-4 wperp-col-xs-12 margin-bottom-10"
                },
                [
                  _c("date-picker", {
                    staticClass: "wperp-form-field",
                    attrs: { placeholder: _vm.__("Start date", "erp") },
                    model: {
                      value: year.start_date,
                      callback: function($$v) {
                        _vm.$set(year, "start_date", $$v)
                      },
                      expression: "year.start_date"
                    }
                  })
                ],
                1
              ),
              _vm._v(" "),
              _c(
                "div",
                {
                  staticClass:
                    "wperp-form-group wperp-col-sm-4 wperp-col-xs-12 margin-bottom-10"
                },
                [
                  _c("date-picker", {
                    staticClass: "wperp-form-field",
                    attrs: { placeholder: _vm.__("End date", "erp") },
                    model: {
                      value: year.end_date,
                      callback: function($$v) {
                        _vm.$set(year, "end_date", $$v)
                      },
                      expression: "year.end_date"
                    }
                  }),
                  _vm._v(" "),
                  index > 0
                    ? _c(
                        "span",
                        {
                          staticClass: "settings-btn-cancel",
                          on: {
                            click: function($event) {
                              return _vm.deleteYear(index)
                            }
                          }
                        },
                        [_vm._v("x")]
                      )
                    : _vm._e()
                ],
                1
              )
            ])
          }),
          _vm._v(" "),
          _c("div", { staticClass: "wperp-form-group" }, [
            _c(
              "button",
              {
                staticClass: "wperp-btn wperp-btn-default",
                attrs: { type: "button" },
                on: { click: _vm.addNewYear }
              },
              [
                _vm._v(
                  "\n                + " +
                    _vm._s(_vm.__("Add New", "erp")) +
                    "\n            "
                )
              ]
            )
          ]),
          _vm._v(" "),
          _c(
            "div",
            { staticClass: "wperp-form-group" },
            [
              _c("submit-button", {
                attrs: { text: _vm.__("Save Changes", "erp") }
              }),
              _vm._v(" "),
              _c("div", { staticClass: "clearfix" })
            ],
            1
          )
        ],
        2
      )
    ]
  )
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-50cf8a78", esExports)
  }
}

/***/ }),
/* 458 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_CrmContacts_vue__ = __webpack_require__(223);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_7a01fd84_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_CrmContacts_vue__ = __webpack_require__(459);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_CrmContacts_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_7a01fd84_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_CrmContacts_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/crm/contacts/CrmContacts.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-7a01fd84", Component.options)
  } else {
    hotAPI.reload("data-v-7a01fd84", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 459 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("base-layout", {
    attrs: {
      section_id: "erp-crm",
      sub_section_id: "contacts",
      enable_content: true,
      single_option: false
    }
  })
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-7a01fd84", esExports)
  }
}

/***/ }),
/* 460 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_CrmContactForm_vue__ = __webpack_require__(224);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_6ac6329c_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_CrmContactForm_vue__ = __webpack_require__(465);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_CrmContactForm_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_6ac6329c_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_CrmContactForm_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/crm/contact-forms/CrmContactForm.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-6ac6329c", Component.options)
  } else {
    hotAPI.reload("data-v-6ac6329c", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 461 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_CrmContactFormLayout_vue__ = __webpack_require__(225);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_30735c34_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_CrmContactFormLayout_vue__ = __webpack_require__(462);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_CrmContactFormLayout_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_30735c34_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_CrmContactFormLayout_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/crm/contact-forms/CrmContactFormLayout.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-30735c34", Component.options)
  } else {
    hotAPI.reload("data-v-30735c34", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 462 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "base-layout",
    {
      attrs: {
        section_id: _vm.section_id,
        sub_section_id: _vm.sub_section_id,
        enable_content: false,
        enableSubSectionTitle: false
      }
    },
    [
      typeof _vm.options.sub_sections === "undefined" && _vm.options.length > 0
        ? _c("div", [
            _c("h3", { staticClass: "sub-section-title" }, [
              _vm._v(
                "\n            " + _vm._s(_vm.options[0].title) + "\n        "
              )
            ]),
            _vm._v(" "),
            _c("div", { domProps: { innerHTML: _vm._s(_vm.options[0].desc) } })
          ])
        : _c("div", [
            _vm.subSectionTitle
              ? _c("h3", { staticClass: "sub-section-title" }, [
                  _vm._v(
                    "\n            " +
                      _vm._s(_vm.subSectionTitle) +
                      "\n        "
                  )
                ])
              : _vm._e()
          ]),
      _vm._v(" "),
      _c(
        "div",
        [
          _c(
            "ul",
            { staticClass: "sub-sub-menu" },
            _vm._l(_vm.options.sub_sections, function(menu, key) {
              return _c(
                "li",
                { key: key },
                [
                  _c(
                    "router-link",
                    {
                      attrs: {
                        to:
                          "/" +
                          _vm.section_id +
                          "/" +
                          _vm.sub_section_id +
                          "/" +
                          key
                      }
                    },
                    [
                      _c("span", { staticClass: "menu-name" }, [
                        _vm._v(_vm._s(menu))
                      ])
                    ]
                  )
                ],
                1
              )
            }),
            0
          ),
          _vm._v(" "),
          _vm._t("default")
        ],
        2
      )
    ]
  )
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-30735c34", esExports)
  }
}

/***/ }),
/* 463 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_CrmContactFormSingle_vue__ = __webpack_require__(226);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_09de1c04_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_CrmContactFormSingle_vue__ = __webpack_require__(464);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_CrmContactFormSingle_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_09de1c04_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_CrmContactFormSingle_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/crm/contact-forms/CrmContactFormSingle.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-09de1c04", Component.options)
  } else {
    hotAPI.reload("data-v-09de1c04", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 464 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", [
    _c("h3", { staticClass: "sub-section-title mb-20" }, [
      _vm._v(" " + _vm._s(_vm.formData.title) + " ")
    ]),
    _vm._v(" "),
    _c("form", { staticClass: "wperp-form", attrs: { method: "post" } }, [
      _c(
        "table",
        {
          staticClass: "erp-settings-table widefat",
          staticStyle: { "max-width": "600px" }
        },
        [
          _c("thead", [
            _c("tr", [
              _c("th", [_vm._v(_vm._s(_vm.__("Form Field", "erp")))]),
              _vm._v(" "),
              _c("th", [_vm._v(_vm._s(_vm.__("CRM Contact Option	", "erp")))]),
              _vm._v(" "),
              _c("th")
            ])
          ]),
          _vm._v(" "),
          _vm._l(Object.keys(_vm.formData.fields), function(fieldKey) {
            return _c("tbody", { key: fieldKey }, [
              _c("tr", { attrs: { valign: "top" } }, [
                _c("td", [_vm._v(_vm._s(_vm.formData.fields[fieldKey]))]),
                _vm._v(" "),
                _c("td", [_vm._v(_vm._s(_vm.getCRMOptionTitle(fieldKey)))]),
                _vm._v(" "),
                _c("td", [
                  _c(
                    "button",
                    {
                      staticClass: "button button-default",
                      attrs: {
                        type: "button",
                        disabled: _vm.isMapped(fieldKey)
                      },
                      on: {
                        click: function($event) {
                          return _vm.resetMapping(fieldKey)
                        }
                      }
                    },
                    [_c("i", { staticClass: "dashicons dashicons-no-alt" })]
                  ),
                  _vm._v(" "),
                  _c(
                    "button",
                    {
                      staticClass: "button button-default",
                      attrs: { type: "button" },
                      on: {
                        click: function($event) {
                          return _vm.setActiveDropDown(fieldKey)
                        }
                      }
                    },
                    [
                      _c("i", {
                        staticClass: "dashicons dashicons-screenoptions"
                      })
                    ]
                  )
                ])
              ]),
              _vm._v(" "),
              _c(
                "tr",
                {
                  directives: [
                    {
                      name: "show",
                      rawName: "v-show",
                      value: fieldKey === _vm.activeDropDown,
                      expression: "fieldKey === activeDropDown"
                    }
                  ],
                  staticClass: "cfi-option-row"
                },
                [
                  _c(
                    "td",
                    {
                      staticClass: "cfi-contact-options",
                      attrs: { colspan: "3" }
                    },
                    [
                      _vm._l(_vm.data.crmOptions, function(
                        optionTitle,
                        option,
                        index
                      ) {
                        return _c("span", { key: index }, [
                          !_vm.optionIsAnObject(option)
                            ? _c(
                                "button",
                                {
                                  class: [
                                    "button",
                                    _vm.isOptionMapped(fieldKey, option)
                                      ? "button-primary active"
                                      : ""
                                  ],
                                  staticStyle: { margin: "3px" },
                                  attrs: { type: "button" },
                                  on: {
                                    click: function($event) {
                                      return _vm.mapOption(fieldKey, option)
                                    }
                                  }
                                },
                                [
                                  _vm._v(
                                    "\n                            " +
                                      _vm._s(optionTitle) +
                                      "\n                            "
                                  )
                                ]
                              )
                            : _vm._e()
                        ])
                      }),
                      _vm._v(" "),
                      _vm._l(_vm.data.crmOptions, function(
                        option,
                        options,
                        index2
                      ) {
                        return _c(
                          "span",
                          { key: "parent-" + index2 },
                          _vm._l(options.options, function(
                            childOptionTitle,
                            childOption,
                            index3
                          ) {
                            return _c("span", { key: "child-" + index3 }, [
                              _vm.optionIsAnObject(option)
                                ? _c(
                                    "button",
                                    {
                                      class: [
                                        "button",
                                        _vm.isChildOptionMapped(
                                          _vm.field,
                                          option,
                                          childOption
                                        )
                                          ? "button-primary active"
                                          : ""
                                      ],
                                      staticStyle: { margin: "3px" },
                                      attrs: { type: "button" },
                                      on: {
                                        click: function($event) {
                                          return _vm.mapChildOption(
                                            _vm.field,
                                            option,
                                            childOption
                                          )
                                        }
                                      }
                                    },
                                    [
                                      _vm._v(
                                        "\n                                    " +
                                          _vm._s(
                                            options.title +
                                              " - " +
                                              childOptionTitle
                                          ) +
                                          "\n                                "
                                      )
                                    ]
                                  )
                                : _vm._e()
                            ])
                          }),
                          0
                        )
                      })
                    ],
                    2
                  )
                ]
              )
            ])
          })
        ],
        2
      ),
      _vm._v(" "),
      _c("div", { staticClass: "wperp-form-group mt-10" }, [
        _c("label", [_vm._v(_vm._s(_vm.i18n.labelContactGroups))]),
        _vm._v(" "),
        _c(
          "select",
          {
            directives: [
              {
                name: "model",
                rawName: "v-model",
                value: _vm.formData.contactGroup,
                expression: "formData.contactGroup"
              }
            ],
            on: {
              change: function($event) {
                var $$selectedVal = Array.prototype.filter
                  .call($event.target.options, function(o) {
                    return o.selected
                  })
                  .map(function(o) {
                    var val = "_value" in o ? o._value : o.value
                    return val
                  })
                _vm.$set(
                  _vm.formData,
                  "contactGroup",
                  $event.target.multiple ? $$selectedVal : $$selectedVal[0]
                )
              }
            }
          },
          [
            _c("option", { attrs: { value: "0" } }, [
              _vm._v(_vm._s(_vm.i18n.labelSelectGroup))
            ]),
            _vm._v(" "),
            _vm._l(_vm.data.contactGroups, function(groupTitle, groupId) {
              return _c(
                "option",
                { key: groupId, domProps: { value: groupId } },
                [
                  _vm._v(
                    "\n                    " +
                      _vm._s(groupTitle) +
                      "\n                "
                  )
                ]
              )
            })
          ],
          2
        )
      ]),
      _vm._v(" "),
      _c("div", { staticClass: "wperp-form-group mt-10" }, [
        _c("label", [
          _vm._v(_vm._s(_vm.i18n.labelContactOwner) + " "),
          _c("span", { staticClass: "required" }, [_vm._v("*")])
        ]),
        _vm._v(" "),
        _c(
          "select",
          {
            directives: [
              {
                name: "model",
                rawName: "v-model",
                value: _vm.formData.contactOwner,
                expression: "formData.contactOwner"
              }
            ],
            on: {
              change: function($event) {
                var $$selectedVal = Array.prototype.filter
                  .call($event.target.options, function(o) {
                    return o.selected
                  })
                  .map(function(o) {
                    var val = "_value" in o ? o._value : o.value
                    return val
                  })
                _vm.$set(
                  _vm.formData,
                  "contactOwner",
                  $event.target.multiple ? $$selectedVal : $$selectedVal[0]
                )
              }
            }
          },
          [
            _c("option", { attrs: { value: "0" } }, [
              _vm._v(_vm._s(_vm.i18n.labelSelectGroup))
            ]),
            _vm._v(" "),
            _vm._l(_vm.data.contactOwners, function(userName, userId) {
              return _c(
                "option",
                { key: userId, domProps: { value: userId } },
                [
                  _vm._v(
                    "\n                    " +
                      _vm._s(userName) +
                      "\n                "
                  )
                ]
              )
            })
          ],
          2
        )
      ]),
      _vm._v(" "),
      _c("div", { staticClass: "wperp-form-group mt-10" }, [
        _c(
          "button",
          {
            staticClass:
              "wperp-btn btn--default settings-button-reset pull-left",
            attrs: { type: "button" },
            on: { click: _vm.reset_mapping }
          },
          [
            _vm._v(
              "\n                " +
                _vm._s(_vm.__("Reset", "erp")) +
                "\n            "
            )
          ]
        ),
        _vm._v(" "),
        _c(
          "button",
          {
            staticClass: "wperp-btn btn--primary settings-button ml-10 mt-0",
            attrs: { type: "button" },
            on: { click: _vm.save_mapping }
          },
          [
            _vm._v(
              "\n                " +
                _vm._s(_vm.__("Save Changes", "erp")) +
                "\n            "
            )
          ]
        ),
        _vm._v(" "),
        _c("div", { staticClass: "clearfix" })
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
    require("vue-hot-reload-api")      .rerender("data-v-09de1c04", esExports)
  }
}

/***/ }),
/* 465 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "crm-contact-form-layout",
    [
      _vm._l(Object.keys(_vm.forms), function(formKey, index) {
        return _c(
          "div",
          { key: index },
          [
            _c("crm-contact-form-single", {
              attrs: {
                formData: _vm.forms[formKey],
                data: _vm.localizedData,
                plugin: _vm.formName,
                formId: parseInt(formKey)
              },
              on: { reset_contact_form_data: _vm.resetContactFormData }
            }),
            _vm._v(" "),
            _c("hr", {
              staticStyle: {
                "border-top": "0px !important",
                "border-bottom": "1px solid #e1e1e1 !important",
                "padding-top": "15px",
                "margin-bottom": "25px"
              }
            })
          ],
          1
        )
      })
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
    require("vue-hot-reload-api")      .rerender("data-v-6ac6329c", esExports)
  }
}

/***/ }),
/* 466 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_CrmSubscription_vue__ = __webpack_require__(227);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_dab4da5c_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_CrmSubscription_vue__ = __webpack_require__(467);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_CrmSubscription_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_dab4da5c_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_CrmSubscription_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/crm/subscription/CrmSubscription.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-dab4da5c", Component.options)
  } else {
    hotAPI.reload("data-v-dab4da5c", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 467 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("base-layout", {
    attrs: {
      section_id: "erp-crm",
      sub_section_id: "subscription",
      enable_content: true,
      single_option: false
    }
  })
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-dab4da5c", esExports)
  }
}

/***/ }),
/* 468 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_WooCommerce_vue__ = __webpack_require__(228);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_34c3c8cd_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_WooCommerce_vue__ = __webpack_require__(469);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_WooCommerce_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_34c3c8cd_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_WooCommerce_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/woocommerce/WooCommerce.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-34c3c8cd", Component.options)
  } else {
    hotAPI.reload("data-v-34c3c8cd", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 469 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    { attrs: { id: "erp-wc-sync" } },
    [
      _vm.notice
        ? _c(
            "base-layout",
            {
              attrs: {
                section_id: "erp-woocommerce",
                sub_section_id: "erp-woocommerce"
              }
            },
            [
              _c("div", { staticClass: "wc-sync-logo" }, [
                _c("img", { attrs: { src: _vm.wooSyncLogo, id: "wc-sync-bg" } })
              ]),
              _vm._v(" "),
              _c("div", { staticClass: "wc-sync-notice" }, [
                _c("h4", [
                  _vm._v(_vm._s(_vm.__("Connect with WooCommerce", "erp")))
                ]),
                _vm._v(" "),
                _c("p", { domProps: { innerHTML: _vm._s(_vm.notice) } })
              ]),
              _vm._v(" "),
              _c(
                "div",
                { staticClass: "wperp-form-group erp-wc-sync-notice-btn" },
                [
                  _c(
                    "a",
                    {
                      staticClass: "wperp-btn btn--primary",
                      attrs: {
                        href: _vm.btnLink,
                        target: "_blank",
                        id: "erp-wc-sync-notice-btn"
                      }
                    },
                    [
                      _vm._v(
                        "\n                " +
                          _vm._s(_vm.btnText) +
                          "\n            "
                      )
                    ]
                  )
                ]
              )
            ]
          )
        : _vm._e()
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
    require("vue-hot-reload-api")      .rerender("data-v-34c3c8cd", esExports)
  }
}

/***/ }),
/* 470 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_GeneralEmail_vue__ = __webpack_require__(229);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_5e500174_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_GeneralEmail_vue__ = __webpack_require__(471);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_GeneralEmail_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_5e500174_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_GeneralEmail_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/email/general/GeneralEmail.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-5e500174", Component.options)
  } else {
    hotAPI.reload("data-v-5e500174", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 471 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("base-layout", {
    attrs: {
      section_id: "erp-email",
      sub_section_id: "general",
      enable_content: true
    }
  })
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-5e500174", esExports)
  }
}

/***/ }),
/* 472 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_EmailConnect_vue__ = __webpack_require__(230);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_1c79836f_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_EmailConnect_vue__ = __webpack_require__(480);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_EmailConnect_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_1c79836f_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_EmailConnect_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/email/email-connect/EmailConnect.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-1c79836f", Component.options)
  } else {
    hotAPI.reload("data-v-1c79836f", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 473 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "base-layout",
    {
      attrs: {
        section_id: "erp-email",
        sub_section_id: "smtp",
        enable_content: true,
        enableSubSectionTitle: false,
        disableMenu: true,
        disableSectionTitle: true,
        options: _vm.options
      }
    },
    [
      _c(
        "div",
        { attrs: { slot: "extended-data" }, slot: "extended-data" },
        [
          _vm._t("extended-data", function() {
            return [
              _c("div", { staticClass: "wperp-form-group test-connection" }, [
                _c("div", { staticClass: "connection-outgoing" }, [
                  _c("label", { attrs: { for: "smtp_test_email_address" } }, [
                    _vm._v(_vm._s(_vm.__("Test Mail", "erp")))
                  ]),
                  _vm._v(" "),
                  _c("p", [
                    _vm._v(
                      _vm._s(
                        _vm.__("An Email Address to Test the Connection", "erp")
                      )
                    )
                  ]),
                  _vm._v(" "),
                  _c("input", {
                    directives: [
                      {
                        name: "model",
                        rawName: "v-model",
                        value: _vm.smtpTestEmail,
                        expression: "smtpTestEmail"
                      }
                    ],
                    staticClass: "wperp-form-field",
                    attrs: {
                      placeholder: _vm.__("Email here", "erp"),
                      id: "smtp_test_email_address"
                    },
                    domProps: { value: _vm.smtpTestEmail },
                    on: {
                      input: function($event) {
                        if ($event.target.composing) {
                          return
                        }
                        _vm.smtpTestEmail = $event.target.value
                      }
                    }
                  }),
                  _vm._v(" "),
                  _c(
                    "button",
                    {
                      staticClass:
                        "wperp-btn btn--secondary btn-test-connection",
                      attrs: { id: "smtp-test-connection" },
                      on: { click: _vm.testConnection }
                    },
                    [_vm._v(_vm._s(_vm.__("Send Test Email", "erp")))]
                  )
                ])
              ])
            ]
          })
        ],
        2
      )
    ]
  )
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-248d80f5", esExports)
  }
}

/***/ }),
/* 474 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_GoogleEmail_vue__ = __webpack_require__(233);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_69867eb2_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_GoogleEmail_vue__ = __webpack_require__(475);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_GoogleEmail_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_69867eb2_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_GoogleEmail_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/email/email-connect/GoogleEmail.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-69867eb2", Component.options)
  } else {
    hotAPI.reload("data-v-69867eb2", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 475 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "base-layout",
    {
      attrs: {
        section_id: "erp-email",
        sub_section_id: "gmail",
        enable_content: true,
        enableSubSectionTitle: false,
        disableMenu: true,
        disableSectionTitle: true
      }
    },
    [
      _c("template", { slot: "extended-data" }, [
        _vm.gmailConnected !== null && _vm.gmailConnected.status
          ? _c("div", { staticStyle: { "margin-bottom": "30px" } }, [
              _c(
                "a",
                {
                  staticClass: "button",
                  attrs: { href: _vm.gmailConnected.link, target: "_blank" }
                },
                [
                  _vm._v(
                    "\n                " +
                      _vm._s(
                        _vm.__("Click to Authorize your gmail account", "erp")
                      ) +
                      "\n            "
                  )
                ]
              ),
              _vm._v(" "),
              _vm.gmailConnected.is_connected
                ? _c(
                    "a",
                    {
                      staticClass: "button",
                      staticStyle: { "margin-left": "20px" },
                      attrs: {
                        href: _vm.gmailConnected.disconnect_url,
                        target: "_blank"
                      }
                    },
                    [
                      _vm._v(
                        "\n                " +
                          _vm._s(_vm.__("Disconnect", "erp")) +
                          "\n            "
                      )
                    ]
                  )
                : _vm._e()
            ])
          : _vm._e()
      ])
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
    require("vue-hot-reload-api")      .rerender("data-v-69867eb2", esExports)
  }
}

/***/ }),
/* 476 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_ImapEmail_vue__ = __webpack_require__(234);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_731408d0_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_ImapEmail_vue__ = __webpack_require__(477);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_ImapEmail_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_731408d0_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_ImapEmail_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/email/email-connect/ImapEmail.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-731408d0", Component.options)
  } else {
    hotAPI.reload("data-v-731408d0", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 477 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "base-layout",
    {
      attrs: {
        section_id: "erp-email",
        sub_section_id: "imap",
        enable_content: true,
        enableSubSectionTitle: false,
        disableMenu: true,
        disableSectionTitle: true
      }
    },
    [
      _c("template", { slot: "extended-data" }, [
        _c("div", { staticClass: "test-connection" }, [
          _c("div", { staticClass: "connection-incoming" }, [
            _c("label", [_vm._v(_vm._s(_vm.__("Test Connection", "erp")))]),
            _vm._v(" "),
            _c("p", [
              _vm._v(
                _vm._s(
                  _vm.__(
                    "Click on the Above Button Before Saving the Setting",
                    "erp"
                  )
                )
              )
            ]),
            _vm._v(" "),
            _c("button", {
              staticClass: "wperp-btn btn--secondary btn-test-connection",
              attrs: { type: "button" },
              domProps: { innerHTML: _vm._s(_vm.imapTestString) },
              on: { click: _vm.testImapConnection }
            })
          ])
        ])
      ])
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
    require("vue-hot-reload-api")      .rerender("data-v-731408d0", esExports)
  }
}

/***/ }),
/* 478 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_MailgunEmail_vue__ = __webpack_require__(235);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_5306e798_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_MailgunEmail_vue__ = __webpack_require__(479);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_MailgunEmail_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_5306e798_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_MailgunEmail_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/email/email-connect/MailgunEmail.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-5306e798", Component.options)
  } else {
    hotAPI.reload("data-v-5306e798", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 479 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "base-layout",
    {
      attrs: {
        section_id: "erp-email",
        sub_section_id: "mailgun",
        enable_content: true,
        enableSubSectionTitle: false,
        disableMenu: true,
        disableSectionTitle: true,
        options: _vm.options
      }
    },
    [
      _c(
        "div",
        { attrs: { slot: "extended-data" }, slot: "extended-data" },
        [
          _vm._t("extended-data", function() {
            return [
              _c("div", { staticClass: "wperp-form-group test-connection" }, [
                _c("div", { staticClass: "connection-outgoing" }, [
                  _c("label", { attrs: { for: "erp_mailgun_test_email" } }, [
                    _vm._v(_vm._s(_vm.__("Test Mail", "erp")))
                  ]),
                  _vm._v(" "),
                  _c("p", [
                    _vm._v(
                      _vm._s(
                        _vm.__("An Email Address to Test the Connection", "erp")
                      )
                    )
                  ]),
                  _vm._v(" "),
                  _c("input", {
                    directives: [
                      {
                        name: "model",
                        rawName: "v-model",
                        value: _vm.erp_mailgun_test_email,
                        expression: "erp_mailgun_test_email"
                      }
                    ],
                    staticClass: "wperp-form-field",
                    attrs: {
                      placeholder: _vm.__("Email here", "erp"),
                      id: "erp_mailgun_test_email",
                      type: "email"
                    },
                    domProps: { value: _vm.erp_mailgun_test_email },
                    on: {
                      input: function($event) {
                        if ($event.target.composing) {
                          return
                        }
                        _vm.erp_mailgun_test_email = $event.target.value
                      }
                    }
                  }),
                  _vm._v(" "),
                  _c(
                    "button",
                    {
                      staticClass:
                        "wperp-btn btn--secondary btn-test-connection",
                      attrs: { id: "mailgun-test-connection" },
                      on: { click: _vm.testConnection }
                    },
                    [_vm._v(_vm._s(_vm.__("Send Test Email", "erp")))]
                  )
                ])
              ])
            ]
          })
        ],
        2
      )
    ]
  )
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-5306e798", esExports)
  }
}

/***/ }),
/* 480 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "base-layout",
    {
      attrs: {
        section_id: "erp-email",
        sub_section_id: "email_connect",
        enable_content: false,
        enableSubSectionTitle: false
      }
    },
    [
      _c("div", { staticClass: "email-connect-area" }, [
        _c("div", { staticClass: "email-card email-connect-outgoing" }, [
          _c("h4", [_vm._v(_vm._s(_vm.__("Outgoing Email Setting", "erp")))]),
          _vm._v(" "),
          _c(
            "div",
            { staticClass: "email-icons" },
            _vm._l(_vm.outgoingConnections, function(connection) {
              return _c(
                "div",
                {
                  key: connection.slug,
                  class:
                    "email-icon pointer " +
                    (connection.slug === _vm.activeOutgoingEmail
                      ? "active-email-icon"
                      : ""),
                  on: {
                    click: function($event) {
                      return _vm.toggleActiveConnection(connection, "outgoing")
                    }
                  }
                },
                [
                  _c("img", {
                    attrs: {
                      src: connection.isEnabled
                        ? connection.enableIcon
                        : connection.disableIcon,
                      alt: ""
                    }
                  }),
                  _vm._v(" "),
                  connection.isEnabled
                    ? _c(
                        "span",
                        { staticClass: "checkbox-icon checkbox-active" },
                        [_c("i", { staticClass: "fa fa-check-circle" })]
                      )
                    : _c("span", {
                        staticClass: "checkbox-icon checkbox-inactive",
                        on: {
                          click: function($event) {
                            return _vm.toggleActiveConnection(
                              connection,
                              "outgoing"
                            )
                          }
                        }
                      }),
                  _vm._v(" "),
                  _c("p", [_vm._v(_vm._s(connection.name))])
                ]
              )
            }),
            0
          ),
          _vm._v(" "),
          _vm.activeOutgoingEmail === "smtp"
            ? _c("div", [_c("smtp-email")], 1)
            : _c("div", [_c("mailgun-email")], 1)
        ]),
        _vm._v(" "),
        _c("div", { staticClass: "email-card email-connect-incoming" }, [
          _c("h4", [_vm._v(_vm._s(_vm.__("Incoming Email Setting", "erp")))]),
          _vm._v(" "),
          _c(
            "div",
            { staticClass: "email-icons" },
            _vm._l(_vm.incomingConnections, function(connection) {
              return _c(
                "div",
                {
                  key: connection.slug,
                  class:
                    "email-icon pointer " +
                    (connection.slug === _vm.activeIncomingEmail
                      ? "active-email-icon"
                      : ""),
                  on: {
                    click: function($event) {
                      return _vm.toggleActiveConnection(connection, "incoming")
                    }
                  }
                },
                [
                  _c("img", {
                    attrs: {
                      src: connection.isEnabled
                        ? connection.enableIcon
                        : connection.disableIcon,
                      alt: ""
                    }
                  }),
                  _vm._v(" "),
                  connection.isEnabled
                    ? _c(
                        "span",
                        { staticClass: "checkbox-icon checkbox-active" },
                        [_c("i", { staticClass: "fa fa-check-circle" })]
                      )
                    : _c("span", {
                        staticClass: "checkbox-icon checkbox-inactive",
                        on: {
                          click: function($event) {
                            return _vm.toggleActiveConnection(
                              connection,
                              "incoming"
                            )
                          }
                        }
                      }),
                  _vm._v(" "),
                  _c("p", [_vm._v(_vm._s(connection.name))])
                ]
              )
            }),
            0
          ),
          _vm._v(" "),
          _vm.activeIncomingEmail === "imap"
            ? _c("div", [_c("imap-email")], 1)
            : _c("div", [_c("google-email")], 1)
        ])
      ])
    ]
  )
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-1c79836f", esExports)
  }
}

/***/ }),
/* 481 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_EmailTemplate_vue__ = __webpack_require__(236);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_0adc08c5_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_EmailTemplate_vue__ = __webpack_require__(482);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_EmailTemplate_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_0adc08c5_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_EmailTemplate_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/email/templates/EmailTemplate.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-0adc08c5", Component.options)
  } else {
    hotAPI.reload("data-v-0adc08c5", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 482 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "base-layout",
    {
      attrs: {
        section_id: "erp-email",
        sub_section_id: "templates",
        enable_content: false,
        single_option: false,
        enableSubSectionTitle: false
      }
    },
    [
      _c("h3", { staticClass: "sub-section-title pull-left" }, [
        _vm._v(_vm._s(_vm.__("Saved Replies", "erp")))
      ]),
      _vm._v(" "),
      _c(
        "button",
        {
          staticClass:
            "wperp-btn btn--primary settings-button header-right-button",
          attrs: { type: "button" },
          on: {
            click: function($event) {
              return _vm.popupModal({}, "create")
            }
          }
        },
        [
          _c("i", { staticClass: "fa fa-plus" }),
          _vm._v(" " + _vm._s(_vm.__("Add New", "erp")) + "\n    ")
        ]
      ),
      _vm._v(" "),
      _c("div", { staticClass: "clearfix" }),
      _vm._v(" "),
      _c("table", { staticClass: "erp-settings-table widefat" }, [
        _c("thead", [
          _c("tr", [
            _c("th", [_vm._v(_vm._s(_vm.__("Template Name", "erp")))]),
            _vm._v(" "),
            _c("th", [_vm._v(_vm._s(_vm.__("Subject", "erp")))]),
            _vm._v(" "),
            _c("th", [_vm._v(_vm._s(_vm.__("Enable/Disable", "erp")))])
          ])
        ]),
        _vm._v(" "),
        _c(
          "tbody",
          _vm._l(_vm.templates, function(template, index) {
            return _c("tr", { key: index, attrs: { valign: "top" } }, [
              _c("td", [_vm._v(_vm._s(template.name))]),
              _vm._v(" "),
              _c("td", [_vm._v(_vm._s(template.subject))]),
              _vm._v(" "),
              _c("td", [
                _c(
                  "span",
                  {
                    staticClass: "action",
                    on: {
                      click: function($event) {
                        return _vm.popupModal(template, "edit")
                      }
                    }
                  },
                  [_c("i", { staticClass: "fa fa-pencil" })]
                ),
                _vm._v(" "),
                _c(
                  "span",
                  {
                    staticClass: "action",
                    on: {
                      click: function($event) {
                        return _vm.popupDeleteModal(template)
                      }
                    }
                  },
                  [_c("i", { staticClass: "fa fa-trash" })]
                )
              ])
            ])
          }),
          0
        )
      ]),
      _vm._v(" "),
      _c("modal", {
        directives: [
          {
            name: "show",
            rawName: "v-show",
            value: _vm.isVisibleModal,
            expression: "isVisibleModal"
          }
        ],
        attrs: {
          title:
            _vm.modalMode === "create"
              ? _vm.__("Add new Template", "erp")
              : _vm.__("Edit", "erp"),
          header: true,
          footer: true,
          hasForm: true
        },
        on: {
          close: function($event) {
            return _vm.popupModal({}, _vm.modalMode)
          }
        },
        scopedSlots: _vm._u([
          {
            key: "body",
            fn: function() {
              return [
                _c(
                  "form",
                  {
                    staticClass: "wperp-form",
                    attrs: { method: "post" },
                    on: {
                      submit: function($event) {
                        $event.preventDefault()
                        return _vm.onFormSubmit.apply(null, arguments)
                      }
                    }
                  },
                  [
                    _c("div", { staticClass: "wperp-form-group" }, [
                      _c("label", [
                        _vm._v(_vm._s(_vm.__("Name", "erp")) + " "),
                        _c("span", { staticClass: "required" }, [_vm._v("*")])
                      ]),
                      _vm._v(" "),
                      _c("input", {
                        directives: [
                          {
                            name: "model",
                            rawName: "v-model",
                            value: _vm.singleTemplate.name,
                            expression: "singleTemplate.name"
                          }
                        ],
                        staticClass: "wperp-form-field",
                        attrs: { required: "" },
                        domProps: { value: _vm.singleTemplate.name },
                        on: {
                          input: function($event) {
                            if ($event.target.composing) {
                              return
                            }
                            _vm.$set(
                              _vm.singleTemplate,
                              "name",
                              $event.target.value
                            )
                          }
                        }
                      })
                    ]),
                    _vm._v(" "),
                    _c("div", { staticClass: "wperp-form-group" }, [
                      _c("label", [_vm._v(_vm._s(_vm.__("Subject", "erp")))]),
                      _vm._v(" "),
                      _c("input", {
                        directives: [
                          {
                            name: "model",
                            rawName: "v-model",
                            value: _vm.singleTemplate.subject,
                            expression: "singleTemplate.subject"
                          }
                        ],
                        staticClass: "wperp-form-field",
                        domProps: { value: _vm.singleTemplate.subject },
                        on: {
                          input: function($event) {
                            if ($event.target.composing) {
                              return
                            }
                            _vm.$set(
                              _vm.singleTemplate,
                              "subject",
                              $event.target.value
                            )
                          }
                        }
                      })
                    ]),
                    _vm._v(" "),
                    _c("div", { staticStyle: { "margin-bottom": "60px" } }, [
                      _c(
                        "div",
                        {
                          staticClass: "wperp-form-group",
                          staticStyle: {
                            clear: "both",
                            position: "absolute",
                            right: "18px"
                          }
                        },
                        [
                          _c("label", [
                            _vm._v(_vm._s(_vm.__("Short Codes", "erp")))
                          ]),
                          _vm._v(" "),
                          _c(
                            "select",
                            {
                              directives: [
                                {
                                  name: "model",
                                  rawName: "v-model",
                                  value: _vm.singleTemplate.shortCode,
                                  expression: "singleTemplate.shortCode"
                                }
                              ],
                              staticClass: "wperp-form-field",
                              on: {
                                change: [
                                  function($event) {
                                    var $$selectedVal = Array.prototype.filter
                                      .call($event.target.options, function(o) {
                                        return o.selected
                                      })
                                      .map(function(o) {
                                        var val =
                                          "_value" in o ? o._value : o.value
                                        return val
                                      })
                                    _vm.$set(
                                      _vm.singleTemplate,
                                      "shortCode",
                                      $event.target.multiple
                                        ? $$selectedVal
                                        : $$selectedVal[0]
                                    )
                                  },
                                  _vm.appendShortCode
                                ]
                              }
                            },
                            _vm._l(_vm.shortCodes, function(
                              shortCode,
                              key,
                              index
                            ) {
                              return _c("option", { key: index }, [
                                _vm._v(
                                  "\n                                " +
                                    _vm._s(key) +
                                    "\n                            "
                                )
                              ])
                            }),
                            0
                          )
                        ]
                      )
                    ]),
                    _vm._v(" "),
                    _c(
                      "div",
                      { staticClass: "wperp-form-group" },
                      [
                        _c("label", [_vm._v(_vm._s(_vm.__("Body", "erp")))]),
                        _vm._v(" "),
                        _c("VueTrix", {
                          attrs: {
                            placeholder: "Enter content",
                            localStorage: ""
                          },
                          model: {
                            value: _vm.singleTemplate.template,
                            callback: function($$v) {
                              _vm.$set(_vm.singleTemplate, "template", $$v)
                            },
                            expression: "singleTemplate.template"
                          }
                        })
                      ],
                      1
                    )
                  ]
                )
              ]
            },
            proxy: true
          },
          {
            key: "footer",
            fn: function() {
              return [
                _c(
                  "span",
                  { on: { click: _vm.onFormSubmit } },
                  [
                    _c("submit-button", {
                      attrs: {
                        text:
                          _vm.modalMode === "create"
                            ? _vm.__("Add New", "erp")
                            : _vm.__("Save", "erp"),
                        customClass: "pull-right"
                      }
                    })
                  ],
                  1
                ),
                _vm._v(" "),
                _c(
                  "span",
                  {
                    on: {
                      click: function($event) {
                        return _vm.popupModal({}, _vm.modalMode)
                      }
                    }
                  },
                  [
                    _c("submit-button", {
                      staticStyle: { "margin-right": "10px" },
                      attrs: {
                        text: _vm.__("Cancel", "erp"),
                        customClass: "wperp-btn-cancel pull-right"
                      }
                    })
                  ],
                  1
                )
              ]
            },
            proxy: true
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
    require("vue-hot-reload-api")      .rerender("data-v-0adc08c5", esExports)
  }
}

/***/ }),
/* 483 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_EmailNotification_vue__ = __webpack_require__(237);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_12aaadb6_hasScoped_true_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_EmailNotification_vue__ = __webpack_require__(485);
var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(484)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = "data-v-12aaadb6"
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_EmailNotification_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_12aaadb6_hasScoped_true_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_EmailNotification_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/email/notifications/EmailNotification.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-12aaadb6", Component.options)
  } else {
    hotAPI.reload("data-v-12aaadb6", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 484 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 485 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "base-layout",
    {
      attrs: {
        section_id: _vm.section,
        sub_section_id: _vm.subSection,
        enable_content: false
      }
    },
    [
      _c("div", { attrs: { id: "erp-email-templates" } }, [
        _c(
          "ul",
          { staticClass: "sub-sub-menu" },
          _vm._l(_vm.options.sub_sections, function(menu, key, index) {
            return _c("li", { key: key }, [
              _c(
                "a",
                {
                  class: key === _vm.module ? "router-link-active" : "",
                  on: {
                    click: function($event) {
                      return _vm.setModule(key)
                    }
                  }
                },
                [
                  _c("span", { staticClass: "menu-name" }, [
                    _vm._v(_vm._s(menu))
                  ])
                ]
              )
            ])
          }),
          0
        ),
        _vm._v(" "),
        _c(
          "table",
          { staticClass: "erp-settings-table widefat email-template-table" },
          [
            _c("thead", [
              _c(
                "tr",
                _vm._l(_vm.columns, function(column, index) {
                  return _c("th", { key: index, class: column.class }, [
                    _vm._v(_vm._s(column.name))
                  ])
                }),
                0
              )
            ]),
            _vm._v(" "),
            Object.keys(_vm.emails).length
              ? _c(
                  "tbody",
                  _vm._l(_vm.emails, function(email, index) {
                    return _c("tr", { key: index, attrs: { valign: "top" } }, [
                      _c("td", [
                        _c(
                          "span",
                          {
                            staticClass: "template-name",
                            on: {
                              click: function($event) {
                                return _vm.configureTemplate(email)
                              }
                            }
                          },
                          [
                            _vm._v(
                              "\n                            " +
                                _vm._s(email.name) +
                                "\n                        "
                            )
                          ]
                        )
                      ]),
                      _vm._v(" "),
                      _c("td", { staticClass: "hide-sm" }, [
                        _vm._v(_vm._s(email.description))
                      ]),
                      _vm._v(" "),
                      _c(
                        "td",
                        [
                          email.disable_allowed
                            ? _c("radio-switch", {
                                attrs: {
                                  value: email.is_enabled,
                                  id: email.id
                                },
                                on: {
                                  toggle: function($event) {
                                    return _vm.toggleStatus(email, index)
                                  }
                                }
                              })
                            : _vm._e()
                        ],
                        1
                      ),
                      _vm._v(" "),
                      _c("td", [
                        _c(
                          "button",
                          {
                            staticClass: "wperp-btn btn--primary button",
                            attrs: { id: email.option_id },
                            on: {
                              click: function($event) {
                                return _vm.configureTemplate(email)
                              }
                            }
                          },
                          [
                            _vm._v(
                              "\n                            " +
                                _vm._s(_vm.__("Configure", "erp")) +
                                "\n                        "
                            )
                          ]
                        )
                      ])
                    ])
                  }),
                  0
                )
              : _c("tbody", [
                  _c("tr", { attrs: { "col-span": _vm.numColumns } }, [
                    _c("th", [
                      _vm._v(_vm._s(_vm.__("No templates found.", "erp")))
                    ])
                  ])
                ])
          ]
        )
      ]),
      _vm._v(" "),
      _vm.showModal
        ? _c("modal", {
            attrs: {
              title: _vm.__("Configure Template", "erp"),
              header: true,
              footer: true,
              hasForm: true
            },
            on: { close: _vm.toggleModal },
            scopedSlots: _vm._u(
              [
                _vm.singleTemplate
                  ? {
                      key: "body",
                      fn: function() {
                        return [
                          _c("h4", [_vm._v(_vm._s(_vm.singleTemplate.title))]),
                          _vm._v(" "),
                          _c("p", [
                            _vm._v(_vm._s(_vm.singleTemplate.description))
                          ]),
                          _vm._v(" "),
                          _c(
                            "form",
                            {
                              staticClass: "wperp-form",
                              attrs: { method: "post" },
                              on: {
                                submit: function($event) {
                                  $event.preventDefault()
                                  return _vm.onSubmit.apply(null, arguments)
                                }
                              }
                            },
                            [
                              _vm.singleTemplate.disable_allowed
                                ? _c(
                                    "div",
                                    { staticClass: "wperp-form-group" },
                                    [
                                      _c("label", [
                                        _vm._v(
                                          _vm._s(
                                            _vm.__("Disable/Enable", "erp")
                                          )
                                        )
                                      ]),
                                      _vm._v(" "),
                                      _c("radio-switch", {
                                        attrs: { id: _vm.singleTemplate.id },
                                        on: { toggle: _vm.switchValue },
                                        model: {
                                          value: _vm.singleTemplate.is_enable,
                                          callback: function($$v) {
                                            _vm.$set(
                                              _vm.singleTemplate,
                                              "is_enable",
                                              $$v
                                            )
                                          },
                                          expression: "singleTemplate.is_enable"
                                        }
                                      })
                                    ],
                                    1
                                  )
                                : _vm._e(),
                              _vm._v(" "),
                              _c("div", { staticClass: "wperp-form-group" }, [
                                _c("label", [
                                  _vm._v(_vm._s(_vm.__("Subject", "erp")))
                                ]),
                                _vm._v(" "),
                                _c("input", {
                                  directives: [
                                    {
                                      name: "model",
                                      rawName: "v-model",
                                      value: _vm.singleTemplate.subject,
                                      expression: "singleTemplate.subject"
                                    }
                                  ],
                                  staticClass: "wperp-form-field",
                                  domProps: {
                                    value: _vm.singleTemplate.subject
                                  },
                                  on: {
                                    input: function($event) {
                                      if ($event.target.composing) {
                                        return
                                      }
                                      _vm.$set(
                                        _vm.singleTemplate,
                                        "subject",
                                        $event.target.value
                                      )
                                    }
                                  }
                                })
                              ]),
                              _vm._v(" "),
                              _c("div", { staticClass: "wperp-form-group" }, [
                                _c("label", [
                                  _vm._v(_vm._s(_vm.__("Heading", "erp")))
                                ]),
                                _vm._v(" "),
                                _c("input", {
                                  directives: [
                                    {
                                      name: "model",
                                      rawName: "v-model",
                                      value: _vm.singleTemplate.heading,
                                      expression: "singleTemplate.heading"
                                    }
                                  ],
                                  staticClass: "wperp-form-field",
                                  domProps: {
                                    value: _vm.singleTemplate.heading
                                  },
                                  on: {
                                    input: function($event) {
                                      if ($event.target.composing) {
                                        return
                                      }
                                      _vm.$set(
                                        _vm.singleTemplate,
                                        "heading",
                                        $event.target.value
                                      )
                                    }
                                  }
                                })
                              ]),
                              _vm._v(" "),
                              _c(
                                "div",
                                { staticClass: "wperp-form-group" },
                                [
                                  _c("label", [
                                    _vm._v(_vm._s(_vm.__("Body", "erp")))
                                  ]),
                                  _vm._v(" "),
                                  _c("vue-trix", {
                                    attrs: { placeholder: "Enter content" },
                                    model: {
                                      value: _vm.singleTemplate.body,
                                      callback: function($$v) {
                                        _vm.$set(
                                          _vm.singleTemplate,
                                          "body",
                                          $$v
                                        )
                                      },
                                      expression: "singleTemplate.body"
                                    }
                                  })
                                ],
                                1
                              ),
                              _vm._v(" "),
                              _vm.shortCodes.length
                                ? _c(
                                    "div",
                                    { staticClass: "wperp-form-group" },
                                    [
                                      _c(
                                        "label",
                                        [
                                          _c("span", [
                                            _vm._v(
                                              _vm._s(
                                                _vm.__("Template Tags", "erp")
                                              )
                                            )
                                          ]),
                                          _vm._v(" "),
                                          _c("tooltip", {
                                            attrs: {
                                              input: {
                                                tooltip: true,
                                                tooltip_text: _vm.__(
                                                  "You may use these template tags inside subject, heading, body and those will be replaced by original values",
                                                  "erp"
                                                )
                                              }
                                            }
                                          })
                                        ],
                                        1
                                      ),
                                      _vm._v(" "),
                                      _c(
                                        "div",
                                        { staticClass: "email-template-tags" },
                                        _vm._l(_vm.shortCodes, function(
                                          tag,
                                          key
                                        ) {
                                          return _c("span", { key: key }, [
                                            _vm._v(_vm._s(tag))
                                          ])
                                        }),
                                        0
                                      )
                                    ]
                                  )
                                : _vm._e()
                            ]
                          )
                        ]
                      },
                      proxy: true
                    }
                  : {
                      key: "default",
                      fn: function(undefined) {
                        return _c("div", { staticClass: "regen-sync-loader" })
                      }
                    },
                {
                  key: "footer",
                  fn: function() {
                    return [
                      _c(
                        "span",
                        { on: { click: _vm.onSubmit } },
                        [
                          _c("submit-button", {
                            attrs: {
                              text: _vm.__("Save", "erp"),
                              customClass: "pull-right"
                            }
                          })
                        ],
                        1
                      ),
                      _vm._v(" "),
                      _c(
                        "span",
                        { on: { click: _vm.toggleModal } },
                        [
                          _c("submit-button", {
                            staticStyle: { "margin-right": "7px" },
                            attrs: {
                              text: _vm.__("Cancel", "erp"),
                              customClass: "wperp-btn-cancel pull-right"
                            }
                          })
                        ],
                        1
                      )
                    ]
                  },
                  proxy: true
                }
              ],
              null,
              true
            )
          })
        : _vm._e()
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
    require("vue-hot-reload-api")      .rerender("data-v-12aaadb6", esExports)
  }
}

/***/ }),
/* 486 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_Integration_vue__ = __webpack_require__(238);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_16127a71_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_Integration_vue__ = __webpack_require__(487);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_Integration_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_16127a71_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_Integration_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/integration/Integration.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-16127a71", Component.options)
  } else {
    hotAPI.reload("data-v-16127a71", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 487 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    { attrs: { id: "erp-integration" } },
    [
      _c(
        "base-layout",
        { attrs: { section_id: _vm.section, sub_section_id: _vm.section } },
        [
          _c("table", { staticClass: "erp-settings-table widefat" }, [
            _c("thead", [
              _c(
                "tr",
                _vm._l(_vm.columns, function(column, index) {
                  return _c("th", { key: index }, [
                    _vm._v(
                      "\n                        " +
                        _vm._s(column) +
                        "\n                    "
                    )
                  ])
                }),
                0
              )
            ]),
            _vm._v(" "),
            Object.keys(_vm.integrations).length
              ? _c(
                  "tbody",
                  _vm._l(_vm.integrations, function(item, key) {
                    return _c("tr", { key: key, attrs: { valign: "top" } }, [
                      _c("td", [
                        _c(
                          "span",
                          {
                            staticClass: "integration-title",
                            on: {
                              click: function($event) {
                                return _vm.configure(item, key)
                              }
                            }
                          },
                          [
                            _vm._v(
                              "\n                            " +
                                _vm._s(item.title) +
                                "\n                        "
                            )
                          ]
                        )
                      ]),
                      _vm._v(" "),
                      _c("td", [_vm._v(_vm._s(item.description))]),
                      _vm._v(" "),
                      _c("td", [
                        _c(
                          "button",
                          {
                            staticClass: "wperp-btn btn--primary",
                            attrs: { id: item.id },
                            on: {
                              click: function($event) {
                                return _vm.configure(item, key)
                              }
                            }
                          },
                          [
                            _vm._v(
                              "\n                            " +
                                _vm._s(_vm.__("Configure", "erp")) +
                                "\n                        "
                            )
                          ]
                        )
                      ])
                    ])
                  }),
                  0
                )
              : _c("tbody", [
                  _c("tr", { attrs: { "col-span": _vm.numColumns } }, [
                    _c("th", [
                      _vm._v(_vm._s(_vm.__("No templates found.", "erp")))
                    ])
                  ])
                ])
          ]),
          _vm._v(" "),
          _vm.showModal
            ? _c("modal", {
                attrs: {
                  title: _vm.singleItem.title + _vm.__(" Integration", "erp"),
                  header: true,
                  footer: true,
                  hasForm: true
                },
                on: { close: _vm.toggleModal },
                scopedSlots: _vm._u(
                  [
                    {
                      key: "body",
                      fn: function() {
                        return [
                          _vm.singleItem.id === "erp-sms"
                            ? _c(
                                "div",
                                { staticClass: "wperp-form-group" },
                                [
                                  _c(
                                    "label",
                                    {
                                      attrs: { for: "erp-sms-selected-gateway" }
                                    },
                                    [
                                      _vm._v(
                                        _vm._s(_vm.__("Active Gateway", "erp"))
                                      )
                                    ]
                                  ),
                                  _vm._v(" "),
                                  _c("multi-select", {
                                    attrs: {
                                      options: _vm.fieldOptions,
                                      multiple: false,
                                      id: "erp-sms-selected-gateway"
                                    },
                                    on: { select: _vm.onSelect },
                                    model: {
                                      value: _vm.selectedField,
                                      callback: function($$v) {
                                        _vm.selectedField = $$v
                                      },
                                      expression: "selectedField"
                                    }
                                  })
                                ],
                                1
                              )
                            : _vm._e(),
                          _vm._v(" "),
                          _c(
                            "base-content-layout",
                            {
                              key: _vm.componentKey,
                              ref: "base",
                              attrs: {
                                section_id: _vm.section,
                                sub_section_id: _vm.subSection,
                                sub_sub_section_id: _vm.singleItem.id,
                                inputs: _vm.formFields,
                                single_option: _vm.singleItem.single_option,
                                hide_submit: true,
                                options: _vm.options
                              }
                            },
                            [
                              _vm.extraContent
                                ? _c(
                                    "div",
                                    {
                                      attrs: { slot: "extended-data" },
                                      slot: "extended-data"
                                    },
                                    [
                                      _vm._t("extended-data", function() {
                                        return [
                                          _vm.singleItem.id === "erp-dm"
                                            ? _c(
                                                "div",
                                                {
                                                  staticClass:
                                                    "wperp-form-group"
                                                },
                                                [
                                                  _c("label", {
                                                    attrs: {
                                                      for:
                                                        "dropbox-connection-test"
                                                    }
                                                  }),
                                                  _vm._v(" "),
                                                  _c(
                                                    "button",
                                                    {
                                                      staticClass:
                                                        "wperp-btn btn--secondary",
                                                      attrs: {
                                                        id:
                                                          "dropbox-connection-test"
                                                      },
                                                      on: {
                                                        click:
                                                          _vm.testConnection
                                                      }
                                                    },
                                                    [
                                                      _vm._v(
                                                        "\n                                    " +
                                                          _vm._s(
                                                            _vm.__(
                                                              "Test Dropbox Connection",
                                                              "erp"
                                                            )
                                                          ) +
                                                          "\n                                "
                                                      )
                                                    ]
                                                  )
                                                ]
                                              )
                                            : _vm._e()
                                        ]
                                      })
                                    ],
                                    2
                                  )
                                : _vm._e()
                            ]
                          )
                        ]
                      },
                      proxy: true
                    },
                    {
                      key: "footer",
                      fn: function() {
                        return [
                          !_vm.hideSubmit
                            ? _c(
                                "span",
                                { on: { click: _vm.onSubmit } },
                                [
                                  _c("submit-button", {
                                    attrs: {
                                      text: _vm.__("Save", "erp"),
                                      customClass: "pull-right"
                                    }
                                  })
                                ],
                                1
                              )
                            : _vm._e(),
                          _vm._v(" "),
                          _c(
                            "span",
                            { on: { click: _vm.toggleModal } },
                            [
                              _c("submit-button", {
                                staticStyle: { "margin-right": "7px" },
                                attrs: {
                                  text: _vm.__("Cancel", "erp"),
                                  customClass: "wperp-btn-cancel pull-right"
                                }
                              })
                            ],
                            1
                          )
                        ]
                      },
                      proxy: true
                    }
                  ],
                  null,
                  true
                )
              })
            : _vm._e()
        ],
        1
      )
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
    require("vue-hot-reload-api")      .rerender("data-v-16127a71", esExports)
  }
}

/***/ }),
/* 488 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_License_vue__ = __webpack_require__(239);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_d305506a_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_License_vue__ = __webpack_require__(489);
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
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_License_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_d305506a_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_License_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/Settings/assets/src/components/license/License.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-d305506a", Component.options)
  } else {
    hotAPI.reload("data-v-d305506a", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 489 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    { attrs: { id: "erp-license" } },
    [
      _c(
        "base-layout",
        { attrs: { section_id: _vm.section, sub_section_id: _vm.section } },
        [
          _c(
            "form",
            {
              on: {
                submit: function($event) {
                  $event.preventDefault()
                  return _vm.saveSettings.apply(null, arguments)
                }
              }
            },
            [
              _c("table", { staticClass: "erp-settings-table widefat" }, [
                _c("thead", [
                  _c(
                    "tr",
                    _vm._l(_vm.columns, function(column, index) {
                      return _c("th", { key: index }, [
                        _vm._v(
                          "\n                            " +
                            _vm._s(column) +
                            "\n                        "
                        )
                      ])
                    }),
                    0
                  )
                ]),
                _vm._v(" "),
                _vm.extensions
                  ? _c(
                      "tbody",
                      _vm._l(_vm.extensions, function(item, key) {
                        return _c(
                          "tr",
                          { key: key, attrs: { valign: "top" } },
                          [
                            _c("td", [
                              _c("strong", [_vm._v(_vm._s(item.name))])
                            ]),
                            _vm._v(" "),
                            _c("td", [_vm._v(_vm._s(item.version))]),
                            _vm._v(" "),
                            _c("td", [
                              _c("input", {
                                directives: [
                                  {
                                    name: "model",
                                    rawName: "v-model",
                                    value: item.license,
                                    expression: "item.license"
                                  }
                                ],
                                attrs: { type: "text" },
                                domProps: { value: item.license },
                                on: {
                                  input: function($event) {
                                    if ($event.target.composing) {
                                      return
                                    }
                                    _vm.$set(
                                      item,
                                      "license",
                                      $event.target.value
                                    )
                                  }
                                }
                              })
                            ]),
                            _vm._v(" "),
                            _c("td", {
                              domProps: { innerHTML: _vm._s(item.status) }
                            })
                          ]
                        )
                      }),
                      0
                    )
                  : _c("tbody", [
                      _c("tr", { attrs: { "col-span": _vm.numColumns } }, [
                        _c("th", [
                          _vm._v(_vm._s(_vm.__("No extensions found.", "erp")))
                        ])
                      ])
                    ])
              ]),
              _vm._v(" "),
              _c(
                "div",
                { staticClass: "wperp-form-group" },
                [
                  _c("submit-button", {
                    attrs: { text: _vm.__("Save Changes", "erp") }
                  }),
                  _vm._v(" "),
                  _c("div", { staticClass: "clearfix" })
                ],
                1
              )
            ]
          )
        ]
      )
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
    require("vue-hot-reload-api")      .rerender("data-v-d305506a", esExports)
  }
}

/***/ }),
/* 490 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// Initial state
var state = {
  loader: false
}; // Getters

var getters = {
  getStatus: function getStatus(state) {
    return state.loader;
  }
}; // Actions

var actions = {
  setSpinner: function setSpinner(_ref, data) {
    var commit = _ref.commit;
    commit('setSpinner', data);
  }
}; // Mutations

var mutations = {
  setSpinner: function setSpinner(state, data) {
    state.loader = data;
  }
};
/* harmony default export */ __webpack_exports__["a"] = ({
  namespaced: true,
  state: state,
  getters: getters,
  actions: actions,
  mutations: mutations
});

/***/ }),
/* 491 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var state = function state() {
  return {
    data: null
  };
};

var getters = {
  formDatas: function formDatas(state) {
    return state.data;
  }
};
var actions = {
  setFormData: function setFormData(_ref, data) {
    var commit = _ref.commit;
    commit('setFormData', data);
  }
};
var mutations = {
  setFormData: function setFormData(state, data) {
    state.data = data;
  }
};
/* harmony default export */ __webpack_exports__["a"] = ({
  namespaced: true,
  state: state,
  getters: getters,
  actions: actions,
  mutations: mutations
});

/***/ }),
/* 492 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "a", function() { return getRequest; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "b", function() { return postRequest; });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__store_store__ = __webpack_require__(240);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_axios__ = __webpack_require__(41);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_axios___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_axios__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_sweetalert2__ = __webpack_require__(85);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_sweetalert2___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_2_sweetalert2__);



var axiosConfig = __WEBPACK_IMPORTED_MODULE_1_axios___default.a.create({
  baseURL: erp_settings_var.rest.root + erp_settings_var.rest.version + '/settings/v1',
  headers: {
    'X-WP-Nonce': erp_settings_var.rest.nonce
  }
});
var getRequest = function getRequest(url) {
  var data = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
  var silent = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;

  if (!silent) {
    __WEBPACK_IMPORTED_MODULE_0__store_store__["a" /* default */].dispatch('spinner/setSpinner', true);
  }

  return new Promise(function (resolve, reject) {
    __WEBPACK_IMPORTED_MODULE_0__store_store__["a" /* default */].dispatch('spinner/setSpinner', true);
    axiosConfig.get(url, {
      params: data
    }).then(function (response) {
      __WEBPACK_IMPORTED_MODULE_0__store_store__["a" /* default */].dispatch('spinner/setSpinner', false);
      resolve(response.data);
    }).catch(function (errors) {
      if (!silent) {
        __WEBPACK_IMPORTED_MODULE_0__store_store__["a" /* default */].dispatch('spinner/setSpinner', false);
        __WEBPACK_IMPORTED_MODULE_2_sweetalert2___default()({
          position: 'center',
          type: 'warning',
          title: errors.response.data.message ? errors.response.data.message : errors.response.data,
          showConfirmButton: false,
          timer: 2500
        });
      }

      resolve(false);
    });
  });
};
var postRequest = function postRequest(url) {
  var data = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
  var silent = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
  var multipart = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : false;

  if (!silent) {
    __WEBPACK_IMPORTED_MODULE_0__store_store__["a" /* default */].dispatch('spinner/setSpinner', true);
  }

  return new Promise(function (resolve, reject) {
    __WEBPACK_IMPORTED_MODULE_0__store_store__["a" /* default */].dispatch('spinner/setSpinner', true);
    var header;

    if (multipart) {
      header = {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      };
    }

    axiosConfig.post(url, data, header).then(function (response) {
      __WEBPACK_IMPORTED_MODULE_0__store_store__["a" /* default */].dispatch('spinner/setSpinner', false);
      resolve(response.data);
    }).catch(function (errors) {
      if (!silent) {
        __WEBPACK_IMPORTED_MODULE_0__store_store__["a" /* default */].dispatch('spinner/setSpinner', false);
        __WEBPACK_IMPORTED_MODULE_2_sweetalert2___default()({
          position: 'center',
          type: 'warning',
          title: errors.response.data.message ? errors.response.data.message : errors.response.data,
          showConfirmButton: false,
          timer: 2500
        });
      }

      resolve(false);
    });
  });
};

/***/ })
],[429]);