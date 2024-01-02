import {Cloudinary} from "@cloudinary/url-gen";
import { limitFit } from "@cloudinary/url-gen/actions/resize";

export default {
	namespaced: true,
	state: {
		root: '/storage',
		maxUploadSize: -1,
		assets: {},
		cloudinaryName: null
	},
	getters: {
		cloudinaryName(state) {
			return state.cloudinaryName;
		},
		root(state) {
			return state.root;
		},
		maxUploadSize(state) {
			return state.maxUploadSize;
		},
		asset: (state) => (id) => {
			return state.assets['a'+id] || null;
		},
		path: (state) => (asset) => {
			return state.root + (typeof(asset) === 'string' ? asset : asset.path);
		},
		url: (state,getters) => (url,width,height) => {

			if(url.match(/^cloudinary:\/\//)) {
				const cld = new Cloudinary({cloud: { cloudName: getters.cloudinaryName}})
				return cld.image(url.slice(13)).resize(limitFit().width(width || 600).height(height || 400)).toURL();
			}
			return url;
		}
	},
	mutations: {
		setCloudinaryName(state,name) {
			state.cloudinaryName = name;
		},
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