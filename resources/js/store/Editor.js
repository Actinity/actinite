import { snapshotNode} from "../helpers.js";

export default {
    namespaced: true,
    state: {
        node: null,
        fieldData: null,
        snapshot: null,
        isOpen: false,
        tab: 'Content',
        related: [],
        loading: false,
        openNewNodesAutomatically: true,
        stashedNodes: [],
        softSelected: null
    },
    getters: {
        stashedNodes(state) {
            return state.stashedNodes;
        },
        loading(state) {
            return state.loading;
        },
        currentSnap(state) {
            return snapshotNode(state.node || {}, state.fieldData || {});
        },
        isOpen(state) {
            return state.isOpen;
        },
        tab(state) {
            return state.tab;
        },
        openNewNodesAutomatically(state) {
            return state.openNewNodesAutomatically;
        },
        snapshot(state) {
            return state.snapshot;
        },
        nodeId(state) {
           return state.node ? state.node.id : null;
        },
        node(state) {
            return state.node;
        },
        related(state) {
            return state.related;
        },
        relatedNode: (state) => (id) => {
            return _.find(state.relations || [],{id: id});
        },
        fieldData(state) {
            return state.fieldData;
        },
        isDirty(state,getters) {
            return state.node && getters.currentSnap !== state.snapshot;
        },
        tinyMceKey(state) {
            return 'lu19z1dueznghq14u3ih469tr6eolsvg4avky68wcgqrul4z';
        },
        tinyMceConfig(state)
        {
            return {
                menubar: false,
                statusbar: false,
                entity_encoding: 'raw',
                relative_urls: false,
                max_height: 500,
                plugins: 'advlist autolink lists link image charmap preview anchor \
                    searchreplace visualblocks code quickbars \
                    media table code acimage autoresize',
                toolbar:
                    'formatselect | bold italic | \
                    alignleft aligncenter alignright alignjustify | \
                    bullist numlist outdent indent | removeformat acimage media code link unlink',
                quickbars_insert_toolbar: 'acimage media | hr',
                quickbars_selection_toolbar: 'bold italic | link unlink | h2 h3',
                quickbars_image_toolbar: 'acimage alignleft aligncenter alignright link unlink',
                contextmenu: 'link acimage',
                paste_block_drop: true,
                content_style: "img { max-width: 100%; }",
                media_alt_source: false,
                file_picker_callback: (callback,value,meta) => {
                    window.$mitt.emit('linker:open',{
                        callback: callback,
                        url: value,
                        meta: meta
                    });
                },
                init_instance_callback: (editor) => {
                    editor.shortcuts.add('meta+s','Save',(e) => {
                        window.$mitt.emit('trySave');
                    });
                },
                media_poster: false,
                external_plugins: {
                    acimage: '/actinite/resources/tinymce/acimage'
                },
                //paste_as_text: true
            }
        }
    },
    mutations: {
        setRelated(state,value) {
            state.related = value;
        },
        setLoading(state,value) {
            state.loading = value;
        },
        setOpenNewNodesAutomatically(state,value) {
            state.openNewNodesAutomatically = value;
        },
        set(state,node) {
            state.node = node;
            state.snapshot = snapshotNode(node, node.data);
        },
        setTab(state,tab) {
            state.tab = tab;
        },
        setFieldData(state,fields) {
            state.fieldData = fields;
        },
        updateField(state,payload) {
            state.fieldData[payload.field] = payload.value;
        },
        close(state) {
            state.isOpen = false;
            state.node = null;
            state.snapshot = null;
            state.working = null;
        },
        setIsOpen(state,value) {
            state.isOpen = value;
        },
        clearStashedNodes(state) {
            state.stashedNodes = [];
        },
        stashNode(state,node) {
            if(_.findIndex(state.stashedNodes,{id:node.id}) === -1) {
                state.stashedNodes = [...state.stashedNodes,node];
            }
        },
        setSoftSelected(state,node) {
            state.softSelected = node;
        },
        unstashNode(state,node) {
            state.stashedNodes = state.stashedNodes.filter(n => n.id !== node.id);
        }
    },
    actions: {
        stashSoftSelected({commit,state}) {
            if(state.softSelected) {
                commit('stashNode',state.softSelected);
            }
        },
        unstashSoftSelected({commit,state}) {
            if(state.softSelected) {
                commit('unstashNode',state.softSelected);
            }
        },
        stashNode({commit,state},node) {
            commit('stashNode',node);
        },
        toggleStashedNode({commit,state},node) {
            let idx = state.stashedNodes.findIndex(n => n.id === node.id);
            if(~idx) {
                commit('unstashNode',node);
            } else {
                commit('stashNode',node);
            }
        },
        saveIfDirty({getters,dispatch}) {
            if(getters.node && getters.isDirty) {
                dispatch('save');
            }
        },
        edit({commit,getters,rootGetters},nodeId) {
            commit('setIsOpen',true);
            commit('setLoading',true);
            axios.get('/actinite/api/nodes/'+nodeId)
                .then(r => {
                    let fieldData = r.data.data;

                    commit('setFieldData',fieldData);
                    commit('setTab','Content');
                    commit('set',r.data);
                    commit('setRelated',r.data.ac_related);
                    commit('setLoading',false);
                    commit('Tree/update',r.data,{root:true});
                    commit('Assets/merge',r.data.assets,{root:true});
                });
        },
        save({getters,commit}) {
            let payload = {...getters.node,data:getters.fieldData};
            axios.put('/actinite/api/nodes/'+getters.nodeId,payload)
                .then(r => {
                    commit('set',r.data);
                    commit('Tree/update',r.data,{root:true});
                })
                .catch(e => {
                    alert('Unable to save');
                    console.log(e,e.response);
                });
        },
        add({getters,commit,dispatch},payload) {
            // Save the new node

            axios.post('/actinite/api/nodes',payload)
                .then(r => {
                    if(!getters.isDirty && getters.openNewNodesAutomatically) {
                        dispatch('edit',r.data.id);
                    } else {
                        dispatch('reloadRelated');
                    }
                   dispatch('Tree/load',r.data.parent_id,{root:true});
                })
                .catch(e => {
                    alert('Unable to save');
                });
        },
        reloadRelated({getters,commit}) {
            if(getters.node) {
                axios.get('/actinite/api/nodes/' + getters.node.id)
                    .then(r => {
                        commit('setRelated',r.data.ac_related);
                    });
            }
        },
        publish({commit,getters,rootGetters},nodes) {

            axios.post('/actinite/api/publish',{nodes:nodes})
                .then(r => {
                    r.data.forEach((payload) => {
                        let node = rootGetters['Tree/byId'](payload.id);
                        node.current_sha = payload.current_sha;
                        node.published_sha = payload.published_sha;
                        node.published_at = payload.published_at;

                        commit('Tree/update',node,{root:true});

                        if(getters.nodeId === node.id) {
                            commit('set',node);
                        }
                    });
                })
                .catch(e => {
                    console.log(e,e.response);
                    alert('Unable to publish');
                });
        },
        unpublish({commit,getters,rootGetters},nodeId) {
            axios.delete('/actinite/api/publish/'+nodeId)
                .then(r => {
                    let node = rootGetters['Tree/byId'](nodeId);
                    node.published_sha = null;
                    node.published_at = null;
                    commit('Tree/update',node,{root:true});

                    if(getters.nodeId === node.id) {
                        commit('set',node);
                    }
                })
                .catch(e => {
                    console.log(e,e.response);
                    alert('Unable to unpublish');
                });
        }
    }
}
