import HTTP from 'admin/http'

// initial state
const state = {
  customers: []
}

// getters
const getters = {
    getCustomers: (state, getters, rootState) => {
        return state.customers
    }
}

// actions
const actions = {
    fetchCustomers({ state, commit }) {
        HTTP.get('/customers', { params: { per_page: 10 } }).then(response => {            
            commit('setCustomers', response.data)
        })
    },
}

// mutations
const mutations = {
    setCustomers(state, items) {        
        items.forEach(item => {
            state.customers.push({
                id  : item.id,
                name: `${item.first_name} ${item.last_name}`
            })
        })
    },
}

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations
}
