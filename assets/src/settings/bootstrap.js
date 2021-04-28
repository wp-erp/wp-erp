import './i18n';
import Vue from 'vue';
import Vuelidate from 'vuelidate';
import Swal from 'sweetalert2' ;
import VueSweetalert2 from 'vue-sweetalert2';
import Loading from 'vue-loading-overlay';
import { createHooks } from '@wordpress/hooks';
import commonMixins from './mixins/common';
import i18nMixin from './mixins/i18n';
import { clickOutside } from './directive/directives';
import HTTP from './http';

// global for settings var
window.settings = {
    libs: {}
};

// assign libs to window for global use
window.settings.libs['Vue']                 = Vue;
window.settings.libs['VueSweetalert2']      = VueSweetalert2;
window.settings.libs['Loading']             = Loading;
window.settings.libs['commonMixins']        = commonMixins;
window.settings.libs['i18nMixin']           = i18nMixin;
window.settings.libs['HTTP']                = HTTP;
window.settings.libs['Vuelidate']           = Vuelidate;
window.settings.libs['Swal']                = Swal;
window.settings.libs['clickOutside']        = clickOutside;

// get lib reference from window
window.settings_get_lib = function(lib) {
    return window.settings.libs[lib];
};

// hook manipulation
/* global settings */
settings.hooks = createHooks();

settings.addFilter = (hookName, namespace, component, priority = 10) => {
    settings.hooks.addFilter(hookName, namespace, (components) => {
        components.push(component);
        return components;
    }, priority);
};
