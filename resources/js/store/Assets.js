export default {
	namespaced: true,
	state: {
		root: '/storage',
		assets: {}
	},
	getters: {
		asset: (state) => (id) => {
			return state.assets['a'+id] || null;
		},
		path: (state) => (asset,width) => {

			if(width) {
				return state.root + '/assets/'+asset.id+'/'+asset.sha+'/t/'+width+'.webp';
			}

			return state.root + asset.path;
		}
	},
	mutations: {
		setRoot(state,rootPath) {
			state.root = rootPath;
		},
		merge(state,assets) {
			assets.forEach(a => {
				state.assets['a'+a.id] = a;
			});
		}
	}
}