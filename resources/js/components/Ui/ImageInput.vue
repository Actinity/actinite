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
                asset: this.localValue,
                type: 'image'
            });
        },
        edited(asset) {
            this.localValue = asset ? asset.id : null;
        }
    },
    computed: {
        localPath() {
            if(this.localValue) {
                let asset = this.$store.getters['Assets/asset'](this.localValue);

                if(asset) {
                    return this.$store.getters['Assets/path'](asset);
                }
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
