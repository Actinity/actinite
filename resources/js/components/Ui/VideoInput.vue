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
	</div>
</div>
</template>
<script>
export default {
	props: [
		'modelValue'
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
		}
	},
	methods: {
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