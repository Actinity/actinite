<template>

    <editor
        :api-key="$store.getters['Editor/tinyMceKey']"
        :init="$store.getters['Editor/tinyMceConfig']"
        v-model="value"
    />

</template>
<script>
import Editor from '@tinymce/tinymce-vue';
export default {
    props: [
        'modelValue'
    ],
    computed: {
        value: {
            get() {
                return this.modelValue || '';
            },
            set(v) {
                this.$emit('update:modelValue',v);
            }
        }
    },
    components: {
        Editor
    }
}
</script>
