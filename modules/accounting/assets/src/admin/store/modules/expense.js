import HTTP from 'admin/http';

// initial state
const state = {
    people: []
};

// getters
const getters = {
    getPeople: (state) => state.people
};

// actions
const actions = {
    fetchPeople: async({ commit }) => {
        const { status, data } = await HTTP.get('/people', {
            params: {
                type: 'all',
                per_page: 10,
                page: 1 // *offset issue
            }
        });

        if (status === 200) {
            commit('setPeople', data);
        }
    },

    fillPeople({ state, commit, dispatch }, data) {
        commit('setPeople', data);

        if (!state.people.length) {
            dispatch('fetchPeople');
        }
    }
};

// mutations
const mutations = {
    setPeople(state, items) {
        state.people = [];

        items.forEach(item => {
            state.people.push({
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
