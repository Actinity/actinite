export default {
	namespaced: true,
	state: {
		uploading: false,
		progress: 0,
		uploadUrl: null
	},
	getters: {
		uploading(state) {
			return state.uploading;
		},
		progress(state) {
			return state.progress;
		}
	},
	mutations: {
		setUploadUrl(state, url) {
			state.uploadUrl = url;
		},
		setUploading(state,value) {
			state.uploading = value;
		},
		setProgress(state,value) {
			state.progress = value;
		}
	},
	actions: {
		upload({state,commit}, file) {

			let xhr = new XMLHttpRequest();

			xhr.open('PATCH',state.uploadUrl);
			xhr.setRequestHeader('Tus-Resumable','1.0.0');
			xhr.setRequestHeader('Upload-Offset','0');
			xhr.setRequestHeader('Content-Type','application/offset+octet-stream');

			xhr.upload.onprogress = function(e) {
				if (e.lengthComputable) {
					commit('setProgress',(e.loaded / e.total) * 100);
				}
			};

			xhr.onload = function() {
				commit('setUploading',false);
			};

			xhr.send(file);
		},
		start({commit, dispatch}, file) {
			commit('setUploading',true);
			commit('setProgress',0);

			return new Promise((resolve,reject) => {
				axios.post('/actinite/api/vimeo',{
					size: file.size,
					name: file.name.split('.').slice(0,-1).join('.')
				})
					.then(r => {
						if(r.data.error) {
							reject(r.data.error);
							return;
						}

						commit('setUploadUrl',r.data.upload_url);
						dispatch('upload', file);
						resolve(r.data.player_url);

					})
					.catch(e => {
						reject('Unable to upload, refresh and try again');
					});
			});
		}
	}
}