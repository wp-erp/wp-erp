import Vue from 'vue'
import VeeValidate from 'vee-validate'
import App from './App.vue'
import router from './router'
import VueSweetalert2 from 'vue-sweetalert2'
import commonMixins from './mixins/common'
import store from './store/store'
import Loading from 'vue-loading-overlay';
// import { createHooks } from '@wordpress/hooks';
// import accounting from 'accounting';

Vue.config.productionTip = false

// Vue uses
Vue.use(VeeValidate);
Vue.use(VueSweetalert2);
Vue.use(Loading);

Vue.mixin(commonMixins);

// Vue click outside directive
Vue.directive('click-outside', {
    bind(el, binding, vnode) {
        const bubble = binding.modifiers.bubble
        const handler = e => {
            if ( bubble || ( ! el.contains( e.target ) && el !== e.target ) ) {
                binding.value(e)
            }
        }

        el.__vueClickOutside__ = handler
        document.addEventListener('click', handler)
    },

    unbind(el, binding) {
        document.removeEventListener('click', el.__vueClickOutside__)
        el.__vueClickOutside__ = null
    }
})

/* eslint-disable no-new */
new Vue({
    el: '#erp-accounting',
    router,
    store,
    render: h => h(App)
});

