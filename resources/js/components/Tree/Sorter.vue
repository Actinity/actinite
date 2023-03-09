<template>
    <modal ref="modal" auto-close>
        <div class="modal-title">Reorder {{ this.node.name }}</div>

        <p>Drag and drop to reorder</p>

        <draggable
            v-model="children"
            item-key="id"
        >
            <template #item="{element}">
                <div class="sortable-row">
                    <i class="fas fa-bars sortable-handle"></i>
                    <i :class="$store.getters['Types/icon'](element.type)"></i>

                    {{ element.name }}</div>
            </template>
        </draggable>

        <div class="modal-buttons">
            <button class="btn btn-primary" @click="save">Save</button>
        </div>
    </modal>
</template>

<script>
import Modal from "../Ui/Modal.vue";
import draggable from 'vuedraggable';

export default {
    data() {
        return {
            node: null,
            children: []
        }
    },
    methods: {
        open(node) {
            this.node = node;
            this.loadChildren();
            this.$refs.modal.open();
        },
        loadChildren() {
            this.children = [];
            this.$store.getters['Tree/children'](this.node.id).forEach(node => {
                this.children.push({id: node.id,name: node.name, type: node.type });
            });
        },
        save() {
            axios.post(
                '/actinite/api/nodes/'+this.node.id+'/order',
                this.children.map(c => c.id)
            ).then(r => {
                this.$refs.modal.close();
                this.$store.dispatch('Tree/load',this.node.id);
            })
            .catch(e => {
                alert('Unable to save');
            });
        }
    },
    components: {
        Modal,
        draggable
    }
}
</script>
