import Vue from 'vue';
import Vuex from 'vuex';
// import sales from './modules/sales';

Vue.use(Vuex);

const debug = process.env.NODE_ENV !== 'production';

const store = new Vuex.Store({
    modules: {
        // sales,
    },
    strict: debug,
    plugins: []
});

export default store;
