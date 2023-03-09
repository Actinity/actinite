<template>
    <modal ref="modal" :auto-close="!moving" :prevent-close="moving" width="600px">
        <div class="modal-title">Move {{ node.name }}</div>
        <div v-if="moving">
            Hold tight, this will take a minute...
            <div class="ellipser"></div>
        </div>

        <div v-else>
            <div class="row">
                <div class="col-8">
                    To: <strong>{{ targetName }}</strong>
                </div>
                <div class="col-4 text-right">
                    <button class="btn btn-primary" @click="go" :disabled="!selected.length"
                        :title="selected.length ? 'Move the node' : 'Select a node first'"
                    >Go</button>
                </div>
            </div>
            <p>
            </p>
            <tree
                @select="selectParent($event)"
                :selectable="parentTypes"
                :selected="selected"
                :cannot-select="cannotSelect"
            />

        </div>
    </modal>
</template>
<script>
import Modal from "../Ui/Modal.vue";
import Tree from "./Tree.vue";

export default {
    data() {
        return {
            node: null,
            selected: [],
            moving: true
        }
    },
    computed: {
        parentTypes() {
            return this.$store.getters['Types/allowingChildren'](this.node.type).map(t => t.type);
        },
        cannotSelect() {
            return [
                this.node.id,
                this.node.parent_id
            ];
        },
        targetName() {
            return this.selected.length ? this.$store.getters['Tree/byId'](this.selected[0]).name : 'Choose...';
        }
    },
    methods: {
        open(node) {
            this.node = node;
            this.selected = [];
            this.moving = false;
            this.$refs.modal.open();
        },
        go() {
            if(!this.selected.length) {
                return;
            }
            this.moving = true;
            let oldParent = 0 + this.node.parent_id, parentId = this.selected[0];
            axios.post(`/actinite/api/move-node?node=${this.node.id}&parent=${parentId}`)
                .then(r => {
                    // Tree will need to reload the old parent, and the new one

                    this.$store.dispatch('Tree/load',oldParent);
                    this.$store.dispatch('Tree/load',parentId);

                    // No need to update the editor as it doesn't manage parent information

                    this.$refs.modal.forceClose();
                })
                .catch(e => {
                    this.moving = false;
                    alert('Unable to move node');
                    console.log(e.response);
                });
        },
        selectParent(parentId) {
            this.selected = [parentId];
        }
    },
    components: {Tree, Modal}

}
</script>
