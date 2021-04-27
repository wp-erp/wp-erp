import './i18n';
import Vue from 'vue';
import VueSweetalert2 from 'vue-sweetalert2';
import Loading from 'vue-loading-overlay';
import HTTP from 'admin/http';
import Vuelidate from 'vuelidate';
import { createHooks } from '@wordpress/hooks';
import Swal from 'sweetalert2' ;

// global settings var
window.settings = {
    libs: {}
};

// assign libs to window for global use
window.settings.libs['Vue']                 = Vue;
window.settings.libs['VueSweetalert2']      = VueSweetalert2;
window.settings.libs['Loading']             = Loading;
window.settings.libs['HTTP']                = HTTP;
window.settings.libs['FileUpload']          = FileUpload;
window.settings.libs['Vuelidate']           = Vuelidate;
window.settings.libs['PieChart']            = PieChart;
window.settings.libs['VueClipboards']       = VueClipboards;
window.settings.libs['Swal']                = Swal;

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
