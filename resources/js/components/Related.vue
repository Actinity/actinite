<template>
<div class="ac-related">
    <div v-if="!node">No node selected</div>

    <div v-if="node">

        <div v-if="relations && !relations.length">
            No relations for this node
        </div>

        <div v-for="relation in relations">
            <relation-editor :relation="relation"></relation-editor>
        </div>
    </div>
</div>
</template>
<script>
import RelationEditor from "./RelationEditor";
export default {
    computed: {
        node() {
            return this.$store.getters['Editor/node'];
        },
        relations() {
            return this.node.ac_relations;
        }
    },
    components: {
        RelationEditor
    }
}
</script>
