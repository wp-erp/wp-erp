pluginWebpack([3],{

/***/ 111:
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

/***/ 112:
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

/***/ 113:
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 114:
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

/***/ 115:
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

/***/ 116:
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 117:
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

/***/ 118:
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

/***/ 119:
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

/***/ 120:
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

/***/ 122:
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

/***/ 123:
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

/***/ 124:
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

/***/ 125:
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

/***/ 126:
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

/***/ 127:
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

/***/ 128:
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

/***/ 129:
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

/***/ 22:
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

/***/ 25:
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

/***/ 346:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__i18n__ = __webpack_require__(347);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_vue__ = __webpack_require__(19);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_vuelidate__ = __webpack_require__(182);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_vuelidate___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_2_vuelidate__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_sweetalert2__ = __webpack_require__(85);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_sweetalert2___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_3_sweetalert2__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4_vue_sweetalert2__ = __webpack_require__(183);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5_vue_loading_overlay__ = __webpack_require__(86);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5_vue_loading_overlay___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_5_vue_loading_overlay__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6__wordpress_hooks__ = __webpack_require__(40);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_7__mixins_common__ = __webpack_require__(379);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_8__mixins_i18n__ = __webpack_require__(380);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_9__directive_directives__ = __webpack_require__(381);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_10__utils_FormDataHandler__ = __webpack_require__(25);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_11__http__ = __webpack_require__(382);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_12__components_base_Dropdown_vue__ = __webpack_require__(87);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_13__components_base_DatePicker_vue__ = __webpack_require__(36);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_14__components_select_MultiSelect_vue__ = __webpack_require__(48);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_15__components_base_SubmitButton_vue__ = __webpack_require__(22);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_16__components_layouts_BaseLayout_vue__ = __webpack_require__(4);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_17__components_layouts_partials_Switch_vue__ = __webpack_require__(58);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_18__components_base_Modal_vue__ = __webpack_require__(60);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_19__components_base_Tooltip_vue__ = __webpack_require__(55);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_20__components_layouts_BaseContentLayout_vue__ = __webpack_require__(37);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_21_vue_dropify__ = __webpack_require__(121);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_21_vue_dropify___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_21_vue_dropify__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_22_vuedraggable__ = __webpack_require__(423);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_22_vuedraggable___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_22_vuedraggable__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_23_vue_trix__ = __webpack_require__(92);























 // global for settings var

window.settings = {
  libs: {}
}; // assign libs to window for global use

window.settings.libs['Vue'] = __WEBPACK_IMPORTED_MODULE_1_vue__["default"];
window.settings.libs['VueSweetalert2'] = __WEBPACK_IMPORTED_MODULE_4_vue_sweetalert2__["a" /* default */];
window.settings.libs['Loading'] = __WEBPACK_IMPORTED_MODULE_5_vue_loading_overlay___default.a;
window.settings.libs['commonMixins'] = __WEBPACK_IMPORTED_MODULE_7__mixins_common__["a" /* default */];
window.settings.libs['i18nMixin'] = __WEBPACK_IMPORTED_MODULE_8__mixins_i18n__["a" /* default */];
window.settings.libs['HTTP'] = __WEBPACK_IMPORTED_MODULE_11__http__["a" /* default */];
window.settings.libs['Vuelidate'] = __WEBPACK_IMPORTED_MODULE_2_vuelidate___default.a;
window.settings.libs['Swal'] = __WEBPACK_IMPORTED_MODULE_3_sweetalert2___default.a;
window.settings.libs['clickOutside'] = __WEBPACK_IMPORTED_MODULE_9__directive_directives__["a" /* clickOutside */];
window.settings.libs['generateFormDataFromObject'] = __WEBPACK_IMPORTED_MODULE_10__utils_FormDataHandler__["a" /* generateFormDataFromObject */];
window.settings.libs['Datepicker'] = __WEBPACK_IMPORTED_MODULE_13__components_base_DatePicker_vue__["a" /* default */];
window.settings.libs['Dropdown'] = __WEBPACK_IMPORTED_MODULE_12__components_base_Dropdown_vue__["a" /* default */];
window.settings.libs['VueDropify'] = __WEBPACK_IMPORTED_MODULE_21_vue_dropify___default.a;
window.settings.libs['SubmitButton'] = __WEBPACK_IMPORTED_MODULE_15__components_base_SubmitButton_vue__["a" /* default */];
window.settings.libs['BaseContentLayout'] = __WEBPACK_IMPORTED_MODULE_20__components_layouts_BaseContentLayout_vue__["a" /* default */];
window.settings.libs['BaseLayout'] = __WEBPACK_IMPORTED_MODULE_16__components_layouts_BaseLayout_vue__["a" /* default */];
window.settings.libs['MultiSelect'] = __WEBPACK_IMPORTED_MODULE_14__components_select_MultiSelect_vue__["a" /* default */];
window.settings.libs['Modal'] = __WEBPACK_IMPORTED_MODULE_18__components_base_Modal_vue__["a" /* default */];
window.settings.libs['Draggable'] = __WEBPACK_IMPORTED_MODULE_22_vuedraggable___default.a;
window.settings.libs['VueTrix'] = __WEBPACK_IMPORTED_MODULE_23_vue_trix__["a" /* default */];
window.settings.libs['RadioSwitch'] = __WEBPACK_IMPORTED_MODULE_17__components_layouts_partials_Switch_vue__["a" /* default */];
window.settings.libs['Tooltip'] = __WEBPACK_IMPORTED_MODULE_19__components_base_Tooltip_vue__["a" /* default */]; // get lib reference from window

window.settings_get_lib = function (lib) {
  return window.settings.libs[lib];
}; // hook manipulation

/* global settings */


settings.hooks = Object(__WEBPACK_IMPORTED_MODULE_6__wordpress_hooks__["a" /* createHooks */])();

settings.addFilter = function (hookName, namespace, component) {
  var priority = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 10;
  settings.hooks.addFilter(hookName, namespace, function (components) {
    components.push(component);
    return components;
  }, priority);
};

/***/ }),

/***/ 347:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__wordpress_i18n__ = __webpack_require__(39);

/* global erpSettings */

Object(__WEBPACK_IMPORTED_MODULE_0__wordpress_i18n__["b" /* setLocaleData */])(erpSettings.locale_data, 'erp'); // hook other add-on locale

window.settings_add_locale = function (name, localeData) {
  Object(__WEBPACK_IMPORTED_MODULE_0__wordpress_i18n__["b" /* setLocaleData */])(localeData, name);
};

window.__ = __WEBPACK_IMPORTED_MODULE_0__wordpress_i18n__["a" /* __ */];
window.sprintf = __WEBPACK_IMPORTED_MODULE_0__wordpress_i18n__["c" /* sprintf */];

/***/ }),

/***/ 36:
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

/***/ 37:
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

/***/ 379:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_typeof__ = __webpack_require__(12);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_typeof___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_typeof__);

var dateFormat = erp_settings_var.date_format;
/* harmony default export */ __webpack_exports__["a"] = ({
  methods: {
    showAlert: function showAlert(type, message) {
      this.$swal({
        position: 'center',
        type: type,
        title: message,
        showConfirmButton: false,
        timer: 1500
      });
    },
    getFileName: function getFileName(path) {
      // eslint-disable-next-line no-useless-escape
      return path.replace(/^.*[\\\/]/, '');
    },
    decodeHtml: function decodeHtml(str) {
      var regex = /^[A-Za-z0-9 ]+$/;

      if (regex.test(str)) {
        return str;
      }

      var txt = document.createElement('textarea');
      txt.innerHTML = str;
      return txt.value;
    },
    formatDate: function formatDate(d) {
      if (!d) {
        return '';
      }

      var date = new Date(d),
          month = date.getMonth() + 1,
          day = date.getDate(),
          year = date.getFullYear();

      if (month.toString().length < 2) {
        month = '0' + month;
      }

      if (day.toString().length < 2) {
        day = '0' + day;
      }

      switch (dateFormat) {
        case 'd/m/Y':
          // -- 31/12/2020
          return [day, month, year].join('/');

        case 'm/d/Y':
          // -- 12/31/2020
          return [month, day, year].join('/');

        case 'm-d-Y':
          // -- 12-31-2020
          return [month, day, year].join('-');

        case 'd-m-Y':
          // -- 31-12-2020
          return [day, month, year].join('-');

        case 'Y-m-d':
          // -- 2020-12-31
          return [year, month, day].join('-');

        case 'd.m.Y':
          // -- 31.12.2020
          return [day, month, year].join('.');

        default:
          return date.toDateString().replace(/^\S+\s/, '');
      }
    },

    /**
     * Check if item is array or not
     *
     * @param object|array item
     *
     * @return boolean
     */
    isArray: function isArray(item) {
      return !!item && item.constructor === Array;
    },

    /**
     * Check if item is object or not
     *
     * @param object|array item
     *
     * @return boolean
     */
    isObject: function isObject(item) {
      return !!item && item.constructor === Object;
    },

    /**
    * Get Section title after label modification
    *
    * @param object menu item
    *
    * @reurn string section formatted title
    */
    getSectionTitle: function getSectionTitle(menu) {
      var label = menu.label;

      switch (menu.id) {
        case 'erp-crm':
        case 'erp-hr':
        case 'erp-ac':
          label += ' Management';
          break;

        default:
          break;
      }

      return label;
    },
    isEmpty: function isEmpty(value) {
      return !value || __WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_typeof___default()(value) === 'object' && (value.length === 0 || Object.keys(value).length === 0);
    }
  }
});

/***/ }),

/***/ 380:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony default export */ __webpack_exports__["a"] = ({
  /* global __, sprintf */
  methods: {
    __: function (_) {
      function __(_x, _x2) {
        return _.apply(this, arguments);
      }

      __.toString = function () {
        return _.toString();
      };

      return __;
    }(function (text, domain) {
      return __(text, domain);
    }),
    sprintf: function (_sprintf) {
      function sprintf(_x3) {
        return _sprintf.apply(this, arguments);
      }

      sprintf.toString = function () {
        return _sprintf.toString();
      };

      return sprintf;
    }(function (fmt) {
      for (var _len = arguments.length, args = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
        args[_key - 1] = arguments[_key];
      }

      return sprintf.apply(void 0, [fmt].concat(args));
    })
  }
});

/***/ }),

/***/ 381:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "a", function() { return clickOutside; });
var clickOutside = {
  bind: function bind(el, binding, vnode) {
    var bubble = binding.modifiers.bubble;

    var handler = function handler(e) {
      if (bubble || !el.contains(e.target) && el !== e.target) {
        binding.value(e);
      }
    };

    el.__vueClickOutside__ = handler;
    document.addEventListener('click', handler);
  },
  unbind: function unbind(el, binding) {
    document.removeEventListener('click', el.__vueClickOutside__);
    el.__vueClickOutside__ = null;
  }
};

/***/ }),

/***/ 382:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_axios__ = __webpack_require__(41);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_axios___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_axios__);

/* global for settings */

/* harmony default export */ __webpack_exports__["a"] = (__WEBPACK_IMPORTED_MODULE_0_axios___default.a.create({
  baseURL: erp_settings_var.rest.root + erp_settings_var.rest.version + '/settings/v1',
  headers: {
    'X-WP-Nonce': erp_settings_var.rest.nonce
  }
}));

/***/ }),

/***/ 4:
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

/***/ 42:
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

/***/ 47:
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

/***/ 48:
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

/***/ 49:
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

/***/ 50:
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

/***/ 51:
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

/***/ 52:
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

/***/ 53:
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

/***/ 54:
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

/***/ 55:
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

/***/ 56:
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

/***/ 57:
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

/***/ 58:
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

/***/ 59:
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

/***/ 60:
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

/***/ 61:
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

/***/ 87:
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


/***/ })

},[346]);