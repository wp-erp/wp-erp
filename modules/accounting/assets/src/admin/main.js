import Vue from 'vue'
import App from './App.vue'
import router from './router'
import menuFix from './utils/admin-menu-fix'

Vue.config.productionTip = false

/* eslint-disable no-new */
new Vue({
    el: '#erp-accounting',
    router,
    render: h => h(App)
});


// fix the admin menu for the slug "vue-app"
menuFix('accounting');
