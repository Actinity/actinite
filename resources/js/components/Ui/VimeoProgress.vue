<template>
<div class="col-form-label vimeo-upload-progress" v-if="display">
	<div class="vimeo-progress-bar" :style="progressStyle"></div>
	<div class="vimeo-progress-label">{{ text }}</div>
</div>
</template>
<script>
export default {
	watch: {
		uploading(is,was) {
			if(is && !was) {
				this.display = true;
				this.text = 'Uploading';
			}
			if(was && !is) {
				this.text = 'Uploaded';
				setTimeout(() => {
					this.display = false;
				},2000);
			}
		}
	},
	data() {
		return {
			display: false,
			text: 'Uploading'
		}
	},
	computed: {
		uploading() {
			return this.$store.getters['Vimeo/uploading'];
		},
		progressStyle() {
			return {width: this.$store.getters['Vimeo/progress']+'%'};
		}
	}
}
</script>