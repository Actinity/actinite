export default {
	namespaced: true,
	state: {
		root: '/storage',
		maxUploadSize: -1,
		assets: {}
	},
	getters: {
		maxUploadSize(state) {
			return state.maxUploadSize;
		},
		asset: (state) => (id) => {
			return state.assets['a'+id] || null;
		},
		path: (state) => (asset, maxWidth) => {

			let path = state.root + asset.path;

			if(maxWidth && asset.width > maxWidth) {
				asset.sizes.forEach(width => {
					if(width <= maxWidth) {
						path = state.root + '/assets/'+asset.id+'/'+asset.sha+'/t/'+width+'.webp';
					}
				});
			}

			return path;
		}
	},
	mutations: {
		setMaxUploadSize(state,size) {
			state.maxUploadSize = size;
		},
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