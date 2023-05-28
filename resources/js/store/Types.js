export default {
    namespaced: true,
    state: {
        all: [],
        branchable: new Set,
    },
    getters: {
        branchable: (state) => (type) => {
            return state.branchable.has(type);
        },
        all(state) {
            return state.all
        },
        archives(state) {
            return state.all.filter(t => t.is_archive).map(t => t.type);
        },
        icon: (state) => (type) => {
            let match = _.find(state.all,{type:type});
            return match ? match.icon : 'fas fa-sitemap';
        },
        get: (state) => (type) => {
            return _.find(state.all,{type:type});
        },
        name: (state,getters) => (type) => {
            let resolved = getters.get(type);
            return resolved ? resolved.name : type;
        },
        fields: (state) => (type) => {
            let match = _.find(state.all,{type:type});
            return match ? match.fields : [];
        },
        templates: (state) => (type) => {
            let match = _.find(state.all,{type:type});
            return match ? match.pageTemplates : [];
        },
        availableChildren: (state,getters) => (type) => {
            let thisType = getters.get(type),ret = [];
            if(thisType && thisType.childTypes) {
                thisType.childTypes.forEach(t => {
                    ret.push(getters.get(t));
                })
            }
            return ret;
        },
        allowingChildren: (state) => (types) => {
            types = _.isArray(types) ? types : [types];
            return state.all.filter(t => {
                return t.childTypes.filter(t2 => types.includes(t2)).length > 0;
            });
        }
    },
    mutations: {
        setBranchable(state,type) {
            state.branchable.add(type);
        },
        setOne(state,type) {
            let name = type.type.split('\\').pop().replace(/([a-z])([A-Z])/g, '$1 $2');
            let base = {};
            type.fields.forEach(f => {
                base[f.name] = null;
            })
            type = {name:name,base:base,...type};

            state.all.push(type);
        }
    },
    actions: {
        load({commit}) {
            axios.get('/actinite/api/types')
                .then(r => {
                    r.data.forEach(type => {
                        commit('setOne',type);

                        if(type.childTypes && type.childTypes.length) {
                            commit('setBranchable',type.type);
                        }

                    });

                })
                .catch(e => {
                    console.log(e,e.response);
                });
        }
    }
}
