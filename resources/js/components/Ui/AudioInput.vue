<template>
<div>
    <template v-if="path">
        <audio :src="path" controls />
        <div>{{ fileName }}
        <a href="#" @click.prevent="edit">Change</a>
        </div>
    </template>
    <a href="#" @click.prevent="edit" v-else>Click to browse</a>

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
                asset: this.localValue ? this.localValue.match(/assets\/(\d+)/)[1] : null,
                type: 'audio'
            })
        },
        edited(asset) {
            this.localValue = asset ? asset.path : null;
        }
    },
    computed: {
		path() {
			return this.localValue ? this.$store.getters['Assets/path'](this.localValue) : null;
		},
	    fileName() {
			return this.path ? this.path.split('/').pop() : null;
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
