import Vue from 'vue'
import { createHooks } from '@wordpress/hooks'


// global acct var
window.acct = {
    libs: {}
};


// assign libs to window for global use
window.acct.libs['Vue'] = Vue;


// get lib reference from window
window.acct_get_lib = function(lib) {
    return window.acct.libs[lib];
};


// hook manipulation
acct.hooks  = createHooks();

acct.addFilter = (hookName, namespace, component, priority = 10) => {
    acct.hooks.addFilter(hookName, namespace, (components) => {
        components.push(component);
        return components;
    }, priority );
};
