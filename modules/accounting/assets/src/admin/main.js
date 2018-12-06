import Vue from 'vue'
import Vuex from 'vuex'
import VueSweetalert2 from 'vue-sweetalert2';
import App from './App.vue'
import router from './router'

Vue.config.productionTip = false

/* eslint-disable no-new */
new Vue({
    el: '#erp-accounting',
    router,
    render: h => h(App)
});

