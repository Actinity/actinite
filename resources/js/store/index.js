import { createStore } from 'vuex';

import Types from './Types.js';
import Tree from './Tree.js';
import Editor from './Editor.js';
import Auth from './Auth.js';
import Assets from './Assets.js';
import Vimeo from './Vimeo.js';

export default createStore({
    state: {
        features: [],
    },
    mutations: {
        setFeatures(state,value) {
            state.features = value;
        }
    },
    getters: {
        supports: (state) => (feature) => {
            return ~state.features.indexOf(feature);
        }
    },
    modules: {
        Types,
        Tree,
        Editor,
        Auth,
        Assets,
        Vimeo
    }
})
