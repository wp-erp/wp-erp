import HTTP from 'admin/http';

// initial state
const state = {
    customers: [],
    taxRateID: 0,
    discount: 0,
    invoiceTotalAmount: 0
};

// getters
const getters = {
    getCustomers: (state) => state.customers,
    getTaxRateID: (state) => state.taxRateID,
    getDiscount: (state) => state.discount,
    getInvoiceTotalAmount: (state) => state.invoiceTotalAmount
};

// actions
const actions = {
    fetchCustomers: async({ commit }) => {
        const { status, data } = await HTTP.get('/people', {
            params: {
                type: 'customer',
                per_page: 10,
                page: 1 // *offset issue
            }
        });

        if (status === 200) {
            commit('setCustomers', data);
        }
    },

    fillCustomers({ state, commit, dispatch }, data) {
        commit('setCustomers', data);

        if (!state.customers.length) {
            dispatch('fetchCustomers');
        }
    },

    setTaxRateID({ commit }, data) {
        commit('setTaxRateID', data);
    },

    setDiscount({ commit }, data) {
        commit('setDiscount', data);
    },

    setInvoiceTotalAmount({ commit }, data) {
        commit('setInvoiceTotalAmount', data);
    }
};

// mutations
const mutations = {
    setCustomers(state, items) {
        state.customers = [];

        items.forEach(item => {
            state.customers.push({
                id: item.id,
                name: `${item.first_name} ${item.last_name}`
            });
        });
    },

    setTaxRateID(state, id) {
        state.taxRateID = id;
    },

    setDiscount(state, amount) {
        state.discount = amount;
    },

    setInvoiceTotalAmount(state, amount) {
        state.invoiceTotalAmount = amount;
    }
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
