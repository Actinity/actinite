export default {
    namespaced: true,
    state: {
        nodes: {},
        roots: [],
        siteRoots: [],
        open: [],
        initialised: false,
        focusOn: null
    },
    getters: {
        byId: (state) => (id) => {
            return state.nodes['n'+id];
        },
        initialised(state) {
            return state.initialised;
        },
        children: (state,getters,rootState,rootGetters) => (id) => {
            let nodes = Object.values(state.nodes).filter(n => n.parent_id === id),parent = getters.byId(id);
            if(~rootGetters['Types/archives'].indexOf(parent.type)) {
                return _.sortBy(nodes,'published_at').reverse();
            } else {
                return _.sortBy(nodes,'ordering');
            }
        },
        isOpen: (state) => (id) => {
            return ~state.open.indexOf(id);
        },
        roots(state,getters) {
            return getters.userRoots;
        },
        focusedRoots(state,getters) {
            return state.focusOn ? [state.focusOn.id] : getters.userRoots;
        },
        siteRoots(state) {
            return state.siteRoots;
        },
        userRoots(state,getters,rootState,rootGetters) {
            return rootGetters['Auth/rootNodes'] || state.siteRoots;
        },
        focused(state) {
            return state.focusOn;
        }
    },
    mutations: {
        focus(state,node) {
            state.focusOn = node;
        },
        open(state,nodeId) {
            state.open.push(nodeId);
            state.open = _.uniq(state.open);
        },
        setOpen(state,values) {
            state.open = values;
        },
        setRoots(state,value) {
            state.roots = value;
        },
        close(state,nodeId) {
            state.open = _.without(state.open,nodeId);
        },
        merge(state,nodes) {
            nodes.forEach(node => {
                state.nodes['n'+node.id] = node;
            });
        },
        setInitialised(state) {
            state.initialised = true;
        },
        remove(state,nodeId) {
            delete state.nodes['n'+nodeId];
        },
        update(state,node) {
            state.nodes['n'+node.id] = _.clone(node);
        },
        setSiteRoots(state,values) {
            state.siteRoots = values;
        }
    },
    actions: {
        load({commit,getters,dispatch},nodeId) {
            axios.get('/actinite/api/tree' + (nodeId ? '/from/' + nodeId : ''),{
                params: {
                    //unrestricted: true
                }
            })
                .then(r => {
                    if(!nodeId) {
                        commit('setRoots',_.map(r.data.nodes,'id'));
                        r.data.nodes.forEach(n => {
                            dispatch('load',n.id);
                        });
                    }
                    commit('merge',r.data.nodes);
                    if(r.data.parent) {
                        commit('merge',[r.data.parent]);
                    }
                    commit('setInitialised');
                })
        },
        trash({commit,rootGetters,dispatch},node) {
            axios.delete('/actinite/api/nodes/'+node.id)
                .then(r => {
                    commit('remove',node.id);
                    if(node.id === rootGetters['Editor/nodeId']) {
                        commit('Editor/close',null,{root:true});
                    }
                    dispatch('Editor/reloadRelated',null,{root:true});
                })
                .catch(e => {
                    alert('Unable to delete. Refresh and try again');
                });
        }
    }
}
