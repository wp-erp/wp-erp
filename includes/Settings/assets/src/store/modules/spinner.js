// Initial state
const state = {
    loader: false
};

// Getters
const getters = {
    getStatus: (state) => state.loader
};

// Actions
const actions = {
    setSpinner({ commit }, data) {
        commit('setSpinner', data);
    }
};

// Mutations
const mutations = {
    setSpinner(state, data) {
        state.loader = data;
    }
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
