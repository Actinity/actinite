<template>
    <modal ref="modal" :auto-close="!moving" :prevent-close="moving" width="600px">
        <div class="modal-title">Move {{ title }}</div>
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
                :cannot-select="unSelectable"
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
            nodes: [],
            selected: [],
            moving: true
        }
    },
    computed: {
        title() {
            if(this.nodes.length === 1) {
                return this.nodes[0].name;
            }
            return this.nodes.length + ' nodes';
        },
        parentTypes() {
            let types = _.intersection(this.nodes.map(n => n.type));
            return this.$store.getters['Types/allowingChildren'](types).map(t => t.type);
        },
        parents() {
            return _.uniq(this.nodes.map(n => n.parent_id));
        },
        singleParent() {
            return this.parents.length === 1;
        },
        unSelectable() {
            if(this.singleParent) {
                return [
                    this.nodes[0].id,
                    this.nodes[0].parent_id
                ];
            }

            return this.nodes.map(n => n.id);
        },
        targetName() {
            return this.selected.length ? this.$store.getters['Tree/byId'](this.selected[0]).name : 'Choose...';
        }
    },
    methods: {
        open(nodes) {
            this.nodes = nodes;
            this.selected = [];
            this.moving = false;
            this.$refs.modal.open();
        },
        go() {
            if(!this.selected.length) {
                return;
            }
            this.moving = true;
            let targetId = this.selected[0],
                toRefresh = [
                    ...this.parents,
                    targetId
                ];

            axios.post(`/actinite/api/move-node`,{
                nodes: this.nodes.map(n => n.id),
                parent: targetId
            })
                .then(r => {
                    // Reload the affected parents
                    toRefresh.forEach(parentId => {
                        this.$store.dispatch('Tree/load',parentId);
                    });

                    this.$store.commit('Editor/clearStashedNodes');
                    this.$refs.modal.forceClose();
                    this.$emit('complete');
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
