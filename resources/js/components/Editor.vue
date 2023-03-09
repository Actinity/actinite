<template>
    <div class="ac-editor-frame" :class="{'ac-editor-open':$store.getters['Editor/isOpen']}">

        <div class="ac-browser-pane" ref="browserPane">

            <div class="ac-editor-tabs">
                <a
                    v-for="(label,key) in tabs"
                    href="#"
                    class="ac-editor-tab"
                    :class="{'ac-editor-tab-selected':tab === key}"
                    @click.prevent="changeTab(key)"
                    v-text="label"
                />
            </div>

            <related
                v-show="tab === 'related'"
            />

            <tree
                class="tree-primary-navigation"
                v-show="tab === 'tree'"
                ref="tree"
                :root="null"
                @select="go"
                :editable="true"
                :selected="selectedNodes"
            ></tree>
        </div>

        <div class="ac-editor-pane" v-if="$store.getters['Editor/node']" ref="editorPane">
            <node
                v-if="$store.getters['Editor/node']"
                @close="closeEditor"
            ></node>
        </div>

        <asset-browser></asset-browser>
        <linker></linker>
        <add-node ref="addNode"></add-node>
        <move-node ref="mover"></move-node>

    </div>
</template>
<script>
import Tree from "./Tree/Tree";
import Node from "./Node";
import AssetBrowser from "./AssetBrowser";
import Related from "./Related";
import Linker from "./Linker";
import AddNode from "./Tree/AddNode.vue";
import MoveNode from "./Tree/MoveNode.vue";
export default {
    beforeRouteUpdate(to,from) {
        if(!this.dirty || confirm('Are you sure? Changes will be lost.')) {
            this.edit(to.params.id);
        }
    },
    beforeRouteLeave(to,from) {
        if(this.dirty && !confirm('Are you sure? Changes will be lost.')) {
            return false;
        }
        this.$store.commit('Editor/close');
    },
    data() {
        return {
            tab: 'tree',
            tabs: {
                tree: 'Tree',
                related: 'Related'
            }
        }
    },
    watch: {
        node(is) {
            if(is) {
                if(is.id != this.$route.params.id) {
                    this.$router.push('/admin/editor/'+is.id);
                }
            } else {
                this.$router.push('/admin/editor');
            }
        }
    },
    mounted() {
        this.$refs.tree.loadHome();

        if(this.$route.params.id) {
            this.$refs.tree.loadTo(this.$route.params.id);
            this.edit(this.$route.params.id);
        }

    },
    computed: {
        selectedNodes() {
            return [this.$store.getters['Editor/nodeId']];
        },
        dirty() {
            return this.$store.getters['Editor/nodeId'] && this.$store.getters['Editor/isDirty'];
        },
        editing() {
            return !!this.node;
        },
        node() {
            return this.$store.getters['Editor/node'];
        }
    },
    methods: {
        closeEditor() {
            this.$store.commit('Editor/close');
            this.$router.push('/admin/editor');
            this.tab = 'tree';
        },
        edit(id) {
            if(id) {
                this.$store.dispatch('Editor/edit',id);
                this.$nextTick(() => {
                    if(this.$refs.editorPane) {
                        this.$refs.editorPane.scrollTo(0,0);
                    }
                });
            }
        },
        go(id) {
            if(id) {
                this.$router.push('/admin/editor/'+id);
            } else {
                this.$router.push('/admin');
            }
        },
        changeTab(tab) {
            this.tab = tab;
            this.$refs.browserPane.scrollTo(0,0);
        },
        addNode(options) {
            this.$refs.addNode.open(options);
        },
        move(node) {
            this.$refs.mover.open(node);
        }
    },
    provide() {
        return {
            editor: this
        }
    },
    components: {
        Linker,
        Related,
        AssetBrowser,
        Node,
        Tree,
        AddNode,
        MoveNode
    }
}
</script>
