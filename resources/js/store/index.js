import { createStore } from 'vuex';

import Types from './Types.js';
import Tree from './Tree.js';
import Editor from './Editor.js';
import Auth from './Auth.js';
import Assets from './Assets.js';

export default createStore({
    state: {
        assetRoot: '/storage'
    },
    getters: {
        assetRoot(state) {
            return state.assetRoot;
        },
        assetPath: (state) => (path) => {
            return state.assetRoot + path;
        }
    },
    mutations: {
        setAssetRoot(state,value) {
            state.assetRoot = value;
        }
    },
    modules: {
        Types,
        Tree,
        Editor,
        Auth,
        Assets
    }
})
