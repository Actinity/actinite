<template>
<div class="ui-date-input">
    <div class="deflector" v-if="isOpen"></div>
    <div class="ui-date-wrapper">
        <datepicker
            v-model="localValue"
            autoApply
            hideInputIcon
            closeOnAutoApply
            clearable
            input-class-name="form-control"
            format="MMM do, yyyy"
            @open="isOpen = true"
            @closed="isOpen = false"
            :enable-time-picker="false"
        />
    </div>
</div>
</template>
<script>
import Datepicker from '@vuepic/vue-datepicker';
import { parse, format } from 'date-fns';
export default {
    props: [
        'modelValue'
    ],
    data() {
        // Track whether the picker is open, so we can show a deflector to prevent
        // TinyMCE iframes from capturing clicks and leaving the picker up.
        return {
            isOpen: false
        }
    },
    computed: {
        localValue: {
            get() {
                return this.modelValue ? parse(this.modelValue,'yyyy-MM-dd',new Date()) : null;
            },
            set(v) {
                let date = v ? format(v,'yyyy-MM-dd') : null;
                this.$emit('update:modelValue',date);
            }
        }
    },
    components: {
        Datepicker
    }
}
</script>
<style lang="scss">
.ui-date-input {
    .deflector {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 9998;
    }
}
</style>
