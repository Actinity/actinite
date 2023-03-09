<template>
<select v-model="localValue" class="form-control">
    <option selected disabled :value="null">Select...</option>
    <option
        v-for="(label,key) in localOptions"
        :value="key"
        v-text="label"
    />
</select>
</template>
<script>
export default {
    props: [
        'modelValue',
        'options'
    ],
    computed: {
        localValue: {
            get() {
                return this.modelValue || null;
            },
            set(v) {
                this.$emit('update:modelValue',v);
            }
        },
        localOptions() {
            return this.options;
        }
    }
}
</script>
