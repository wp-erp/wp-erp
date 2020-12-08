import index from "./index";
import TaxReport from "./TaxReport";

import Router from 'vue-router';
import Vue from "vue";

Vue.use(Router);

const routes = index.concat(TaxReport);
export default new Router({
    mode: "hash",
    routes: routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        } else {
            return {
                x: 0,
                y: 0
            };
        }
    }
});
