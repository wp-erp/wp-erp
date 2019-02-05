import Vue from 'vue'
import Vuex from 'vuex'
import sales from './modules/sales'
import purchase from './modules/purchase'

Vue.use(Vuex)

const debug = process.env.NODE_ENV !== 'production'

export default new Vuex.Store({
  modules: {
    sales,
    purchase,
  },
  strict : debug,
  plugins: []
})
