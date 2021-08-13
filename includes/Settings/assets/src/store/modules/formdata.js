const state = () => ({
    data : null
})

const getters = {
    formDatas: state => state.data
};

const actions = {
    setFormData({ commit }, data) {
        commit('setFormData', data);
    }
}

const mutations = {
    setFormData: (state, data) => {
        state.data = data
    }
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}
