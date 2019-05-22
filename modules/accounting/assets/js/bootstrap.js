pluginWebpack([3],{

/***/ 539:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vue__ = __webpack_require__(17);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__wordpress_hooks__ = __webpack_require__(32);

 // global acct var

window.acct = {
  libs: {}
}; // assign libs to window for global use

window.acct.libs['Vue'] = __WEBPACK_IMPORTED_MODULE_0_vue__["default"]; // get lib reference from window

window.acct_get_lib = function (lib) {
  return window.acct.libs[lib];
}; // hook manipulation


acct.hooks = Object(__WEBPACK_IMPORTED_MODULE_1__wordpress_hooks__["createHooks"])();

acct.addFilter = function (hookName, namespace, component) {
  var priority = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 10;
  acct.hooks.addFilter(hookName, namespace, function (components) {
    components.push(component);
    return components;
  }, priority);
};

/***/ })

},[539]);