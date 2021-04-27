import Vue from 'vue';
import Router from 'vue-router';

Vue.use(Router);

/* global settings */
export default new Router({
    linkActiveClass: 'router-link-active',
    routes: settings.hooks.applyFilters('erp_settings_admin_routes', [
        {
            // path: '/',
            // component: DashBoard,
            // children: [
            //     {
            //         path: '/dashboard',
            //         name: 'DashBoard',
            //         component: DashBoard,
            //         alias: '/'
            //     }
            // ]
        }
    ])
});
