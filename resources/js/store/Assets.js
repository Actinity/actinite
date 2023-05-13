export default {
	namespaced: true,
	state: {
		assets: {}
	},
	getters: {
		asset: (state) => (id) => {
			return state.assets['a'+id] || null;
		}
	},
	mutations: {
		merge(state,assets) {
			assets.forEach(a => {
				state.assets['a'+a.id] = a;
			});
		}
	}
}