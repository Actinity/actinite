<template>
<div class="ui-radio-group">
    <label v-for="option in localOptions" @click="localValue = option.key" class="ui-radio">
        <i class="fas fa-check-circle" v-if="localValue === option.key"></i>
        <i class="far fa-circle" v-else></i>
        {{ option.value }}
    </label>
</div>
</template>
<script>
export default {
    props: [
        'modelValue',
        'options'
    ],
    computed: {
        localValue: {
            get() { return this.modelValue || null },
            set(v) { this.$emit('update:modelValue',v) }
        },
        localOptions() {
            let options = [];
            for(const [key,value] of Object.entries(this.options)) {
                options.push({key: key,value: value});
            }
            return options;
        }
    }
}
</script>
