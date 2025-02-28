pluginWebpack([0],[
/* 0 */
/***/ (function(module, exports) {

module.exports = wp.element;

/***/ }),
/* 1 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__wordpress_element__ = __webpack_require__(0);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__wordpress_element___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__wordpress_element__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__components_SetupWizard__ = __webpack_require__(2);
throw new Error("Cannot find module \"@wordpress/dom-ready\"");






var SettingsPage = function SettingsPage() {
  return /*#__PURE__*/__WEBPACK_IMPORTED_MODULE_0__wordpress_element___default.a.createElement("div", null, "Placeholder for settings page");
};

__WEBPACK_IMPORTED_MODULE_2__wordpress_dom_ready___default()(function () {
  var root = Object(__WEBPACK_IMPORTED_MODULE_0__wordpress_element__["createRoot"])(document.getElementById('unadorned-announcement-bar-settings'));
  root.render( /*#__PURE__*/__WEBPACK_IMPORTED_MODULE_0__wordpress_element___default.a.createElement(SettingsPage, null));
}); // const setupWizardRoot = document.getElementById('erp-setup-wizard-root');
// if (setupWizardRoot) {
//     render(<SetupWizard />, setupWizardRoot);
// }

/***/ }),
/* 2 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_slicedToArray__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_slicedToArray___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_slicedToArray__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__wordpress_element__ = __webpack_require__(0);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__wordpress_element___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1__wordpress_element__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__wordpress_i18n__ = __webpack_require__(9);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__wordpress_i18n___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_2__wordpress_i18n__);



var steps = [{
  key: 'introduction',
  name: Object(__WEBPACK_IMPORTED_MODULE_2__wordpress_i18n__["__"])('Introduction', 'erp'),
  component: null
}, {
  key: 'basic',
  name: Object(__WEBPACK_IMPORTED_MODULE_2__wordpress_i18n__["__"])('Basic', 'erp'),
  component: null
}, {
  key: 'module',
  name: Object(__WEBPACK_IMPORTED_MODULE_2__wordpress_i18n__["__"])('Module', 'erp'),
  component: null
}, {
  key: 'email',
  name: Object(__WEBPACK_IMPORTED_MODULE_2__wordpress_i18n__["__"])('E-Marketing', 'erp'),
  component: null
}, {
  key: 'department',
  name: Object(__WEBPACK_IMPORTED_MODULE_2__wordpress_i18n__["__"])('Departments', 'erp'),
  component: null
}, {
  key: 'designation',
  name: Object(__WEBPACK_IMPORTED_MODULE_2__wordpress_i18n__["__"])('Designations', 'erp'),
  component: null
}, {
  key: 'workdays',
  name: Object(__WEBPACK_IMPORTED_MODULE_2__wordpress_i18n__["__"])('Work Days', 'erp'),
  component: null
}, {
  key: 'next_steps',
  name: Object(__WEBPACK_IMPORTED_MODULE_2__wordpress_i18n__["__"])('Ready!', 'erp'),
  component: null
}];

var SetupWizard = function SetupWizard() {
  var _useState = Object(__WEBPACK_IMPORTED_MODULE_1__wordpress_element__["useState"])(0),
      _useState2 = __WEBPACK_IMPORTED_MODULE_0__babel_runtime_helpers_slicedToArray___default()(_useState, 2),
      currentStep = _useState2[0],
      setCurrentStep = _useState2[1];

  var handleNext = function handleNext() {
    if (currentStep < steps.length - 1) {
      setCurrentStep(currentStep + 1);
    }
  };

  var handlePrevious = function handlePrevious() {
    if (currentStep > 0) {
      setCurrentStep(currentStep - 1);
    }
  };

  var handleSkip = function handleSkip() {
    handleNext();
  };

  return /*#__PURE__*/__WEBPACK_IMPORTED_MODULE_1__wordpress_element___default.a.createElement("div", {
    className: "erp-setup-wizard"
  }, /*#__PURE__*/__WEBPACK_IMPORTED_MODULE_1__wordpress_element___default.a.createElement("div", {
    className: "erp-setup-wizard-steps"
  }, /*#__PURE__*/__WEBPACK_IMPORTED_MODULE_1__wordpress_element___default.a.createElement("ol", {
    className: "erp-setup-steps"
  }, steps.map(function (step, index) {
    return /*#__PURE__*/__WEBPACK_IMPORTED_MODULE_1__wordpress_element___default.a.createElement("li", {
      key: step.key,
      className: "\n                                ".concat(index === currentStep ? 'active' : '', "\n                                ").concat(index < currentStep ? 'done' : '', "\n                            ")
    }, /*#__PURE__*/__WEBPACK_IMPORTED_MODULE_1__wordpress_element___default.a.createElement("a", {
      href: "#".concat(step.key)
    }, step.name));
  }))), /*#__PURE__*/__WEBPACK_IMPORTED_MODULE_1__wordpress_element___default.a.createElement("div", {
    className: "erp-setup-content"
  }, /*#__PURE__*/__WEBPACK_IMPORTED_MODULE_1__wordpress_element___default.a.createElement("div", {
    className: "step-content"
  }, steps[currentStep].component), /*#__PURE__*/__WEBPACK_IMPORTED_MODULE_1__wordpress_element___default.a.createElement("div", {
    className: "erp-setup-actions step"
  }, currentStep > 0 && /*#__PURE__*/__WEBPACK_IMPORTED_MODULE_1__wordpress_element___default.a.createElement("button", {
    className: "button button-large",
    onClick: handlePrevious
  }, Object(__WEBPACK_IMPORTED_MODULE_2__wordpress_i18n__["__"])('Previous', 'erp')), /*#__PURE__*/__WEBPACK_IMPORTED_MODULE_1__wordpress_element___default.a.createElement("button", {
    className: "button-primary button button-large button-next",
    onClick: handleNext
  }, currentStep === steps.length - 1 ? Object(__WEBPACK_IMPORTED_MODULE_2__wordpress_i18n__["__"])('Finish', 'erp') : Object(__WEBPACK_IMPORTED_MODULE_2__wordpress_i18n__["__"])('Continue', 'erp')), currentStep < steps.length - 1 && /*#__PURE__*/__WEBPACK_IMPORTED_MODULE_1__wordpress_element___default.a.createElement("button", {
    className: "button button-large button-next",
    onClick: handleSkip
  }, Object(__WEBPACK_IMPORTED_MODULE_2__wordpress_i18n__["__"])('Skip this step', 'erp')))));
};

/* unused harmony default export */ var _unused_webpack_default_export = (SetupWizard);

/***/ }),
/* 3 */,
/* 4 */,
/* 5 */,
/* 6 */,
/* 7 */,
/* 8 */,
/* 9 */
/***/ (function(module, exports) {

module.exports = wp.i18n;

/***/ })
],[1]);