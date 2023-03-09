<template>
<transition name="fade">
    <div class="modal-wrapper" v-if="isOpen" @click.self="tryAutoClose" :class="fullheight ? 'modal-full-height' : ''">
        <div class="modal-content" :style="customStyle">
            <slot></slot>
            <div class="modal-close" @click="close" v-if="!preventClose"><i class="fa fa-times"></i></div>
        </div>
    </div>
</transition>
</template>
<script>
export default {
    props: {
        autoClose: Boolean,
        preventClose: Boolean,
        width: {default: null},
        fullheight: Boolean
    },
    data: () => ({
        isOpen: false,
    }),
    computed: {
        customStyle() {
            let style = {};
            if(this.width) {
                style.maxWidth = this.width;
            }
            return style;
        }
    },
    methods: {
        tryAutoClose() {
            if(this.autoClose) {
                this.close();
            }
        },
        open() {
            this.isOpen = true;
            this.$mitt.on('key:escape',this.tryAutoClose);
            //window.lockBodyScroll();
        },

        forceClose() {
            this.isOpen = false;
            this.$emit('close');
            this.$mitt.off('key:escape',this.tryAutoClose);
        },

        close() {
            if(this.isOpen && !this.preventClose) {
                this.forceClose();
            }
        },
    },
}
</script>
