export default {
    namespaced: true,
    state: {
        user: {}
    },
    getters: {
        name(state) {
            return state.user.name;
        },
        rootNodes(state) {
            return state.user.restrict_to_nodes;
        },
        isAdmin(state) {
            return state.user.is_admin;
        },
        tabs(state) {
            let tabs = [];

            if(state.user.is_admin) {
                tabs.push({slug: '/actinite', name: 'Home'});
            }

            tabs.push({slug: '/actinite/editor', name: 'Editor'});

            if(state.user.is_admin) {
                tabs.push({slug: '/actinite/users',name: 'Users'});
            }

            return tabs;
        }
    },
    mutations: {
        setUser(state,data) {
            state.user = {
                is_admin: false,
                restrict_to_nodes: null,
                ...data
            };
        }
    }
}
