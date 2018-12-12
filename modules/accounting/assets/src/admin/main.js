import Vue from 'vue'
// import Vuex from 'vuex'
import VeeValidate from 'vee-validate'
import App from './App.vue'
import router from './router'

Vue.config.productionTip = false

Vue.use(VeeValidate);

/* eslint-disable no-new */
new Vue({
    el: '#erp-accounting',
    router,
    render: h => h(App)
});

