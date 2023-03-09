<template>
<div>
    <div @click="edit" class="d-inline-block" style="cursor: pointer;">
        <img :src="$store.getters.assetPath(localValue)" v-if="localValue" class="ac-editor-image" />
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
                asset: this.localValue,
                type: 'image'
            })
        },
        edited(payload) {
            this.localValue = payload ? payload.path : null;
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
        }
    }
}
</script>
