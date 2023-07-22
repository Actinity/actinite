<template>
    <div class="ac-editor-frame" :class="{'ac-editor-open':$store.getters['Editor/isOpen']}">

        <div class="ac-browser-pane" ref="browserPane">

            <div class="ac-editor-tabs" v-if="node">
                <a
                    href="#"
                    class="ac-editor-tab"
                    :class="{'ac-editor-tab-selected':tab === 'tree'}"
                    @click.prevent="changeTab('tree')"
                >Tree</a>

                <a
                    href="#"
                    class="ac-editor-tab"
                    :class="{'ac-editor-tab-selected':tab === 'related'}"
                    @click.prevent="changeTab('related')"
                >Related</a>

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
                :stashed="stashedNodeIds"
                :stash-mode="stashMode"
            ></tree>

            <stash
                v-show="tab === 'stash'"
                @move="moveStashedNodes"
                @empty="tab = 'tree'"
            ></stash>
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
        <move-node ref="mover" @complete="stashMode = false"></move-node>

        <teleport to="#ac_footer_left">
	        <template v-if="$store.getters.supports('bulk-ops')">
            <div v-if="stashMode" class="bulk-bar">
                <button class="btn btn-secondary" @click="toggleStashMode">
                    <i class="fas fa-check-square"></i>
                    {{ stashedCount }} selected
                </button>

                <button class="btn btn-secondary" @click="moveStashedNodes" :disabled="!stashedCount">Move</button>
            </div>


            <button class="btn btn-bulk" @click="toggleStashMode" v-else>
                <i class="fas fa-square"></i>
                Select
            </button>
	        </template>


        </teleport>


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
import Stash from "./Stash.vue";
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
            stashMode: false,
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
                    this.$router.push('/actinite/editor/'+is.id);
                }
            } else {
                this.$router.push('/actinite/editor');
            }
        }
    },
    mounted() {
        this.$refs.tree.loadHome();

        if(this.$route.params.id) {
            this.$refs.tree.loadTo(this.$route.params.id);
            this.edit(this.$route.params.id);
        }

        this.$mitt.on('key:plus',() => {
            this.$store.dispatch('Editor/stashSoftSelected');
        });
        this.$mitt.on('key:minus',() => {
            this.$store.dispatch('Editor/unstashSoftSelected');
        });

    },
    computed: {
        selectedNodes() {
            return [this.$store.getters['Editor/nodeId']];
        },
        stashedNodeIds() {
            return this.$store.getters['Editor/stashedNodes'].map(n => n.id);
        },
        dirty() {
            return this.$store.getters['Editor/nodeId'] && this.$store.getters['Editor/isDirty'];
        },
        editing() {
            return !!this.node;
        },
        node() {
            return this.$store.getters['Editor/node'];
        },
        stashedCount() {
            return this.$store.getters['Editor/stashedNodes'].length;
        }
    },
    methods: {
        toggleStashMode() {
            this.$store.commit('Editor/clearStashedNodes');
            this.stashMode = !this.stashMode;
        },
        closeEditor() {
            this.$store.commit('Editor/close');
            this.$router.push('/actinite/editor');
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
                this.$router.push('/actinite/editor/'+id);
            } else {
                this.$router.push('/actinite');
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
            this.$refs.mover.open([node]);
        },
        moveStashedNodes() {
            this.$refs.mover.open(this.$store.getters['Editor/stashedNodes']);
        }
    },
    provide() {
        return {
            editor: this
        }
    },
    components: {
        Stash,
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
