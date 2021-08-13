import Vue      from 'vue';
import Vuex     from 'vuex';
import sales    from './modules/sales';
import expense  from './modules/expense';
import purchase from './modules/purchase';
import spinner  from './modules/spinner';
import combo    from './modules/combo-btn';
import common   from './modules/common';

Vue.use(Vuex);

const debug = process.env.NODE_ENV !== 'production';

const store = new Vuex.Store({
    modules: {
        sales,
        expense,
        purchase,
        spinner,
        combo,
        common,
    },
    strict: debug,
    plugins: []
});

export default store;
