pluginWebpack([3],{

/***/ 532:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__wordpress_hooks__ = __webpack_require__(32);

window.acct = {};
acct.hooks = Object(__WEBPACK_IMPORTED_MODULE_0__wordpress_hooks__["createHooks"])();

acct.addFilter = function (hookName, namespace, component) {
  var priority = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 10;
  acct.hooks.addFilter(hookName, namespace, function (components) {
    components.push(component);
    return components;
  }, priority);
};

/***/ })

},[532]);