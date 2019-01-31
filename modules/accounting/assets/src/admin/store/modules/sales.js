import HTTP from 'admin/http'

// initial state
const state = {
  customers: []
}

// getters
const getters = {
    getCustomers: (state) => {
        return state.customers
    }
}

// actions
const actions = {
    fetchCustomers: async({ commit }) => {        
        let {status, data} = await HTTP.get('/people', {
            params: { 
                type: 'customer',
                per_page: 10,
                page: 1 // *offset issue
            }
        })

        if (200 == status) {
            commit('setCustomers', data)
        }
    },

    fillCustomers({ state, commit, dispatch }, data) {        
        commit('setCustomers', data)

        if ( ! state.customers.length ) {            
            dispatch('fetchCustomers')
        }
    }
}

// mutations
const mutations = {
    setCustomers(state, items) {
        state.customers = []

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
