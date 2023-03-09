<template>
<div class="form-group" v-if="node">
    <label class="form-label">Related: {{ $store.getters['Types/name'](relation.related_type) }}</label>
    <div v-for="node in nodes" class="ac-related">
        <a href="#" @click="$router.push('/admin/editor/'+node.id)">
        <i :class="$store.getters['Types/icon'](node.type)"></i>
        {{ node.name }}

            <i class="fas fa-arrow-right"></i>
        </a>
    </div>

    <p class="mt-2" v-if="relation.allow_multiple">
        <button
            @click="add"
            class="btn btn-primary"
        >
            <i :class="type.icon" style="margin-right:5px;"></i>

            Add {{ type.name }}
        </button>
    </p>
</div>
</template>
<script>
export default {
    props: [
        'relation'
    ],
    computed: {
        type() {
            return this.$store.getters['Types/get'](this.relation.related_type);
        },
        node() {
            return this.$store.getters['Editor/node'];
        },
        nodes() {
            return _.filter(this.$store.getters['Editor/related'] || [],n => n.relation_name === this.relation.name);
        }
    },
    methods: {
        add() {
            this.editor.addNode({
                type: this.relation.related_type,
                relatedTo: {relationName: this.relation.name, target: this.node.id}
            });
        }
    },
    inject: [
        'editor'
    ]
}
</script>
