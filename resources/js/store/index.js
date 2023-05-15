import { createStore } from 'vuex';

import Types from './Types.js';
import Tree from './Tree.js';
import Editor from './Editor.js';
import Auth from './Auth.js';
import Assets from './Assets.js';

export default createStore({
    modules: {
        Types,
        Tree,
        Editor,
        Auth,
        Assets
    }
})
