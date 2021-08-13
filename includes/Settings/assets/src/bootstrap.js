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
import { generateFormDataFromObject } from "./utils/FormDataHandler"
import HTTP from './http';
import Dropdown from './components/base/Dropdown.vue';
import Datepicker from './components/base/DatePicker.vue';
import MultiSelect from './components/select/MultiSelect.vue';
import SubmitButton from './components/base/SubmitButton.vue';
import BaseLayout from './components/layouts/BaseLayout.vue';
import RadioSwitch from './components/layouts/partials/Switch.vue';
import Modal from './components/base/Modal.vue';
import Tooltip from './components/base/Tooltip.vue';
import BaseContentLayout from './components/layouts/BaseContentLayout.vue';
import VueDropify from 'vue-dropify';
import draggable from 'vuedraggable'
import VueTrix from "vue-trix";

// global for settings var
window.settings = {
    libs: {}
};

// assign libs to window for global use
window.settings.libs['Vue']                        = Vue;
window.settings.libs['VueSweetalert2']             = VueSweetalert2;
window.settings.libs['Loading']                    = Loading;
window.settings.libs['commonMixins']               = commonMixins;
window.settings.libs['i18nMixin']                  = i18nMixin;
window.settings.libs['HTTP']                       = HTTP;
window.settings.libs['Vuelidate']                  = Vuelidate;
window.settings.libs['Swal']                       = Swal;
window.settings.libs['clickOutside']               = clickOutside;
window.settings.libs['generateFormDataFromObject'] = generateFormDataFromObject;
window.settings.libs['Datepicker']                 = Datepicker;
window.settings.libs['Dropdown']                   = Dropdown;
window.settings.libs['VueDropify']                 = VueDropify;
window.settings.libs['SubmitButton']               = SubmitButton;
window.settings.libs['BaseContentLayout']          = BaseContentLayout;
window.settings.libs['BaseLayout']                 = BaseLayout;
window.settings.libs['MultiSelect']                = MultiSelect;
window.settings.libs['Modal']                      = Modal;
window.settings.libs['Draggable']                  = draggable;
window.settings.libs['VueTrix']                    = VueTrix;
window.settings.libs['RadioSwitch']                = RadioSwitch;
window.settings.libs['Tooltip']                    = Tooltip;

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
