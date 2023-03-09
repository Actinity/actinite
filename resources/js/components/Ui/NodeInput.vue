<template>
<div class="form-control" @click="open">
    {{ text }}
</div>
    <modal ref="modal">
        <h3 class="modal-title">Select {{ label || relation }}</h3>
        <tree
            @select="selectNode"
            :selected="localSelected"
            :selectable="types"
        />
    </modal>
</template>
<script>
import Tree from "../Tree/Tree.vue";
import Modal from "./Modal.vue";

export default {
    props: [
        'label',
        'modelValue',
        'relation',
        'types'
    ],
    computed: {
        localSelected() {
            return this.localValue ? [this.localValue] : [];
        },
        localValue: {
            get() {
                return this.modelValue;
            },
            set(v) {
                this.$emit('update:modelValue',v);
            }
        },
        text() {
            if(!this.localValue) {
                return "Not set";
            }
            let node = this.$store.getters['Editor/relatedNode'](this.localValue);
            if(node) {
                return node.name;
            }
            node = this.$store.getters['Tree/byId'](this.localValue);
            if(node) {
                return node.name;
            }

            return this.localValue;

        }
    },
    methods: {
        open() {
            this.$refs.modal.open();
        },
        selectNode(nodeId) {
            this.localValue = nodeId;
            this.$refs.modal.close();
        }
    },
    components: {
        Modal,
        Tree
    }
}
</script>
