<template>
    <div class="ui-textarea">
        <textarea
            v-model="localValue"
            :readonly="readonly"
            :disabled="disabled"
            @focus="isFocused=true;setHeight()"
            @blur="isFocused=false;setHeight()"
            class="form-control"
            :style="css"
            ref="input"
            :placeholder="placeholder || ''"
        ></textarea>

        <div class="ta-shadow-wrapper">
            <div class="form-control ta-shadow" ref="shadow" v-text="shadowValue"></div>
        </div>
    </div>
</template>
<script>
export default {
    props: [
        'minHeight',
        'modelValue',
        'placeholder',
        'readonly',
        'disabled'
    ],
    data() {
        return {
            height: this.minimumHeight,
            isFocused: false
        }
    },
    mounted() {
        this.ping();
    },
    created() {
        this.bufferedPing = _.debounce(this.ping,100);
    },
    watch: {
        modelValue() {
            this.bufferedPing();
        }
    },
    computed: {
        minimumHeight() {
            return this.minHeight || (this.isFocused ? 60 : 30);
        },
        shadowValue() {
            return this.localValue + '.'; // Add a character to fix weirdness with blank lines.
        },
        css() {
            return {height:this.height + 'px'};
        },
        localValue: {
            get() {
                return this.modelValue || '';
            },
            set(v) {
                this.$emit('update:modelValue',v);
                this.setHeight();
            }
        }
    },
    methods: {
        focus() {
            this.$refs.input.focus();
        },
        setHeight() {
            if(this.$refs.shadow) {
                this.height = Math.max(this.minimumHeight,this.$refs.shadow.clientHeight + (this.isFocused ? 20 : 2));
            }
        },
        ping() {
            this.$nextTick(this.setHeight);
        }
    }
}
</script>
<style lang="scss">
.ui-textarea {
	textarea {
		overflow: hidden;
		resize: none;
		line-height: 1.5em;
	}
	.ta-shadow {
		white-space: pre-wrap;
		height: auto;
		line-height: 1.5em;
	}
	.ta-shadow-wrapper {
		height: 0;
		overflow: hidden;
		z-index: -1;
		max-width: 100%;
	}
}
</style>