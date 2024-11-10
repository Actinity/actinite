<template>
<div>
	<div class="input-group">

		<div class="input-group-append">
			<label class="d-block btn btn-primary video-upload-button">
				<input type="file" @change="start" accept="video/*" ref="input" />
				<i class="fas fa-upload"></i>
			</label>
		</div>

		<input type="text" class="form-control" v-model="localValue" />

		<div class="input-group-prepend" v-if="nodeHasImage && videoId">
			<button class="btn" title="Reload thumbnail" @click="thumbnail"><i class="fas fa-camera"></i></button>
		</div>

	</div>
</div>
</template>
<script>
export default {
	props: [
		'modelValue',
		'nodeHasImage'
	],
	watch: {
		uploading(is,was) {
			if(was && !is) {
				this.$refs.input.value = null;
			}
		}
	},
	computed: {
		localValue: {
			get() {
				return this.modelValue;
			},
			set(v) {
				this.$emit('update:modelValue',v);
			}
		},
		uploading() {
			return this.$store.getters['Vimeo/uploading'];
		},
		videoId() {
			let matches = this.localValue?.match(/vimeo\.com\/(\d+)/);
			if(matches && matches.length > 0) {
				return matches[1];
			}
			return null;
		}
	},
	methods: {
		thumbnail() {
			axios.get('/actinite/api/vimeo/thumbnail/'+this.videoId)
				.then(r => {

					if(r.data.status !== 'available') {
						alert('Video is still being processed')
						return;
					}

					for(let i = 0; i < r.data.pictures.sizes.length; i++) {
						const size = r.data.pictures.sizes[i];
						if(size.width >= 640) {
							this.$emit('thumbnail',size.link);
							break;
						}
					}
				})
		},
		start() {
			this.$store.dispatch('Vimeo/start', this.$refs.input.files[0])
				.then(url => {
					this.localValue = url;
				})
				.catch(e => {
					alert(e);
				});
		}
	}
}
</script>
<style scoped>
.video-upload-button {
	position: relative;
	overflow: hidden;
}
.video-upload-button input {
	position: absolute;
	top: -1000px;
}
</style>