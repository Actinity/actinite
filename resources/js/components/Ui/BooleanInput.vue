<template>
<div @click="localValue = true" class="ui-radio">
    <i :class="localValue ? 'fas fa-check-circle' : 'far fa-circle'"></i> Yes
</div>
<div @click="localValue = false" class="ui-radio">
    <i :class="localValue ? 'far fa-circle' : 'fas fa-check-circle'"></i> No
</div>
</template>
<script>
export default {
    props: [
        'modelValue'
    ],
    computed: {
        localValue: {
            get() {
                return !!this.modelValue;
            },
            set(v) {
                this.$emit('update:modelValue',v);
            }
        }
    }
}
</script>
