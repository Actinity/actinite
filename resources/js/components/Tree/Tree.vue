<template>
<div class="tree" :class="pagesOnly ? 'tree-pages-only' : ''">
    <branch
        v-for="child in children"
        :node="child"
        :depth="1"
        :open="localOpen"
        @open="open"
        @close="close"
        @select="$emit('select',$event)"
        @sort="sort($event)"
        @move="move($event)"
    />

    <sorter ref="sorter"></sorter>
</div>
</template>
<script>
import Branch from './Branch.vue';
import Sorter from "./Sorter.vue";
import { computed } from 'vue';
export default {
    props: {
        root: { type: Number },
        selected: { default: [] },
        editable: { type: Boolean, default: false },
        unrestricted: { type: Boolean, default: false },
        pagesOnly: { type: Boolean, default: false },
        selectable: { default: null },
        branchable: { type: Boolean, default: false },
        allowsChildren: { default: null },
        cannotSelect: { default: null },
        alreadyOpen: { default: [] }
    },
    data() {
        return {
            localOpen: this.alreadyOpen
        }
    },
    mounted() {
        if(this.unrestricted) {
            this.loadHome();
        }

        if(this.roots.length === 1) {
            this.localOpen = [...this.roots];
        }
    },
    watch: {
        roots(is) {
            if(is.length === 1) {
                this.localOpen = Object.values(is);
            }
        }
    },
    computed: {
        roots() {
            return this.$store.getters[this.unrestricted ? 'Tree/siteRoots' : 'Tree/focusedRoots'];
        },
        selectableTypes() {
            return this.selectable || [];
        },
        selectedNodes() {
            return this.selected || [];
        },
        children() {
            let nodes = [];
            this.roots.forEach(nodeId => {
                nodes.push(this.$store.getters['Tree/byId'](nodeId));
            });
            return nodes;
        }
    },
    methods: {
        sort(node) {
            this.$refs.sorter.open(node);
        },
        open(id) {
            this.localOpen.push(id);
            this.$store.dispatch('Tree/load',id);
        },
        close(id) {
            this.localOpen = _.without(this.localOpen,id);
        },
        loadTo(nodeId) {
            axios.get('/actinite/api/tree/to/'+nodeId,{
                params: { unrestricted: true }
            })
                .then(r => {
                    this.$store.commit('Tree/merge',r.data.nodes);
                    this.localOpen = r.data.open;
                });
        },
        loadHome() {
            this.$store.dispatch('Tree/load');
        }
    },
    components: {
        Sorter,
        Branch
    },
    provide() {
        return {
            onlyBranchable: this.branchable,
            selectable: this.selectableTypes,
            unrestricted: this.unrestricted,
            pagesOnly: this.pagesOnly,
            selectedNodes: computed(() => this.selectedNodes),
            editable: this.editable,
            onlyAllowsChildren: this.allowsChildren,
            cannotSelect: this.cannotSelect || []
        }
    },
    inject: [
        'editor'
    ]
}
</script>
<style>
.add-node {
    background-color: #fff;
}
</style>
