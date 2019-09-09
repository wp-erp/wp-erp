// Initial state
const state = {
    btnID: null
};

// Getters
const getters = {
    getBtnID: (state) => state.btnID
};

// Actions
const actions = {
    setBtnID({ commit }, data) {
        commit('setBtnID', data);
    }
};

// Mutations
const mutations = {
    setBtnID(state, data) {
        state.btnID = data;
    }
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
