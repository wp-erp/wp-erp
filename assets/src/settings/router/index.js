import Vue from 'vue';
import Router from 'vue-router';
import GeneralSettings from 'settings/components/general/GeneralSettings.vue';

Vue.use(Router);

export default new Router({
    linkActiveClass: 'router-link-active',
    routes: settings.hooks.applyFilters('erp_settings_admin_routes', [
        {
            path: '/',
            component: GeneralSettings,
            children: [
                {
                    path: '/general',
                    name: 'GeneralSettings',
                    component: GeneralSettings,
                    // alias: '/'
                }
            ]
        }
    ])
});
