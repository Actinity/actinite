<template>
<div>
    <div @click="edit" class="d-inline-block" style="cursor: pointer;">
        <img :src="localPath" v-if="localValue" class="ac-editor-image" />
        <div v-else>Click to browse</div>
    </div>

</div>
</template>
<script>

export default {
    props: [
        'modelValue',
    ],
    methods: {
        edit() {
            this.$mitt.emit('assets:open',{
                callback: this.edited,
                asset: this.isCustomUrl ? null : this.localValue,
                type: 'image'
            });
        },
        edited(asset) {
            this.localValue = asset ? 'cloudinary://'+asset.uuid : null;
        }
    },
    computed: {
		isCustomUrl() {
			return this.localValue && this.localValue.match(/^https?/);
		},
        localPath() {
            if(this.localValue) {
				return this.$store.getters['Assets/url'](this.localValue);
            }

            return 'about:blank';
        },
        localValue: {
            get() {
                return this.modelValue;
            },
            set(v) {
                this.$emit('update:modelValue',v);
            }
        }
    }
}
</script>
