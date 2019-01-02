import Vue from 'vue'
// import Vuex from 'vuex'
import VeeValidate from 'vee-validate'
import App from './App.vue'
import router from './router'
import VueSweetalert2 from 'vue-sweetalert2';

Vue.config.productionTip = false

Vue.use(VeeValidate);
Vue.use(VueSweetalert2);

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
    render: h => h(App)
});

