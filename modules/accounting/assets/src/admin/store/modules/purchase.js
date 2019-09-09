import HTTP from 'admin/http';

// initial state
const state = {
    vendors: []
};

// getters
const getters = {
    getVendors: (state) => {
        return state.vendors;
    }
};

// actions
const actions = {
    fetchVendors: async({ commit }) => {
        const { status, data } = await HTTP.get('/people', {
            params: {
                type: 'vendor',
                per_page: 10,
                page: 1 // *offset issue
            }
        });

        if (status === 200) {
            commit('setVendors', data);
        }
    },

    fillVendors({ state, commit, dispatch }, data) {
        commit('setVendors', data);

        if (!state.vendors.length) {
            dispatch('fetchVendors');
        }
    }
};

// mutations
const mutations = {
    setVendors(state, items) {
        state.vendors = [];

        items.forEach(item => {
            state.vendors.push({
                id: item.id,
                name: `${item.first_name} ${item.last_name}`
            });
        });
    }
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
