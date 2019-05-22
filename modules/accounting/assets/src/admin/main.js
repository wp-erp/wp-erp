import VeeValidate from 'vee-validate'
import App from './App.vue'
import router from './router'
import store from './store/store'
import VueSweetalert2 from 'vue-sweetalert2'
import commonMixins from './mixins/common'
import Loading from 'vue-loading-overlay'
import HTTP from 'admin/http'
import Datepicker from 'admin/components/base/Datepicker.vue'
import Dropdown from 'admin/components/base/Dropdown.vue'
import FileUpload from 'admin/components/base/FileUpload.vue'
import ShowErrors from 'admin/components/base/ShowErrors.vue'
import SubmitButton from 'admin/components/base/SubmitButton.vue'
import MultiSelect from 'admin/components/select/MultiSelect.vue'
import SelectAccounts from 'admin/components/select/SelectAccounts.vue'
import ListTable from 'admin/components/list-table/ListTable.vue'
import TimePicker from 'admin/components/timepicker/TimePicker.vue'

// get lib reference
let Vue = acct_get_lib('Vue');

// config
Vue.config.productionTip = false

// vue uses
Vue.use(VeeValidate);
Vue.use(VueSweetalert2);
Vue.use(Loading);

// mixin
Vue.mixin(commonMixins);

// vue click outside directive
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
