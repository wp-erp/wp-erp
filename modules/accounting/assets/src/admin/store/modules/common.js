// initial state
const state = {
    erp_pro_activated: false
};

const mutations = {
    setProStatus(state, status) {
        state.erp_pro_activated = status;
    }
};


export default {
    namespaced: true,
    state,
    mutations
};