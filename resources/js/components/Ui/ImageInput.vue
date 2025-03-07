<template>
<div>
    <div @click="edit" class="d-inline-block position-relative image-input" style="cursor: pointer;">
        <img :src="localPath" v-if="localValue" class="ac-editor-image" />
        <button v-else class="btn btn-outline-light">Click to browse</button>


	    <div
		    v-if="this.localValue"
		    @click.stop="trash"
            class="image-trash"
	    >
		        <i class="fas fa-trash"></i>
	    </div>

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
        },
	    trash() {
			this.localValue = null;
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
<style>
.image-trash {
	background-color: rgba(255,255,255,0.9);
	padding: 10px 15px;
	top: 0;
	right: 0;
	position: absolute;
	pointer-events: none;
	transition: 0.15s opacity ease-out;
	opacity: 0;
}
.image-input:hover .image-trash {
	opacity: 1;
	pointer-events: auto;
}
.image-trash:hover {
	color: #f00;
}
</style>