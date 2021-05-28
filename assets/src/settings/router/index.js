import Vue from 'vue';
import Router from 'vue-router';
import GeneralSettings from 'settings/components/general/GeneralSettings.vue';
import HRWorkDays from 'settings/components/hr-workdays/HRWorkDays.vue';

Vue.use(Router);

export default new Router({
    linkActiveClass: 'router-link-active',
    routes: settings.hooks.applyFilters('erp_settings_admin_routes', [
        {
            path: '/',
            component: GeneralSettings,
            children: [
                {
                    path: 'general',
                    name: 'GeneralSettings',
                    component: GeneralSettings
                }
            ]
        },
        {
            path: '/hr',
            component: HRWorkDays,
            children: [
                {
                    path: 'workdays',
                    name: 'HRWorkDays',
                    component: HRWorkDays
                },
                {
                    path: 'leave',
                    name: 'HRWorkDays',
                    component: HRWorkDays
                },
                {
                    path: 'leave-years',
                    name: 'HRWorkDays',
                    component: HRWorkDays
                },
            ]
        }
    ])
});
