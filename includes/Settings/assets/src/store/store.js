import Vue from 'vue';
import Vuex from 'vuex';
import spinner from './modules/spinner';
import formdata from './modules/formdata';

Vue.use(Vuex);

const debug = process.env.NODE_ENV !== 'production';

const store = new Vuex.Store({
    modules: {
        spinner,
        formdata
    },
    strict: debug,
    plugins: []
});

export default store;
