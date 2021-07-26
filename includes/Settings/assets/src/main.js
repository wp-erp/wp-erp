import App from './App.vue';
import router from './router';
import store from './store/store';
import { createHooks } from '@wordpress/hooks';
window.erpSettingsHooks = createHooks();

/* global settings_get_lib */
const Vue            = settings_get_lib('Vue');
const VueSweetalert2 = settings_get_lib('VueSweetalert2');
const Loading        = settings_get_lib('Loading');
const Vuelidate      = settings_get_lib('Vuelidate');
const commonMixins   = settings_get_lib('commonMixins');
const i18nMixin      = settings_get_lib('i18nMixin');
const clickOutside   = settings_get_lib('clickOutside');

// config
Vue.config.productionTip = false;

// vue uses
Vue.use(VueSweetalert2);
Vue.use(Loading);
Vue.use(Vuelidate);

// mixin
Vue.mixin(commonMixins);
Vue.mixin(i18nMixin);

// vue click outside directive
Vue.directive('click-outside', clickOutside);

import {getRequest, postRequest} from './request';

(function () {
    window.postRequest = postRequest;
    window.getRequest = getRequest;
})();

const settingsContainer = document.getElementById('erp-settings');

if ( settingsContainer !== null ) {
    window.erp_settings_vue_instance = new Vue( {
        el: '#erp-settings',
        router,
        store,
        render: h => h(App)
    } );
}
