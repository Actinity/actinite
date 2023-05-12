<template>
    <div v-if="node" class="tree-row">

        <span v-for="spacer in spacers" class="tree-spacer"></span>
        <div class="tree-body">

            <!--
                @contextmenu.prevent="toggleStash"
            -->

            <div
                class="tree-content"
                @mouseenter="mouseEnter"
                @mouseleave="mouseLeave"
                @click="menuIsOpen=false"
                :class="{
                    'tree-selected': ~selectedNodes.indexOf(node.id),
                    'tree-not-selectable': !isSelectable(node),
                    'tree-has-children': hasChildren,
                    'tree-routable': node.page_template
                }"
            >

                <div class="tree-toggle" @click="toggle">
                    <i class="fas fa-caret-down" v-if="isOpen"></i>
                    <i class="fas fa-caret-right" v-else-if="hasChildren"></i>
                </div>

                <div class="tree-stash" @click="toggleStash" v-if="stashMode">
                    <i class="fas fa-check-square tree-icon-stashed" v-if="nodeIsStashed"></i>
                    <i class="far fa-minus-square" v-else-if="hasStashedChildren"></i>
                    <i class="far fa-check-square" v-else-if="hasStashedParent"></i>
                    <i class="far fa-square" v-else></i>
                </div>

                <div class="tree-icon"><i :class="icon"></i></div>

                <div class="tree-info">
                    <a class="tree-label" :href="'/admin/editor/'+node.id" @click.prevent="trySelect(node)">{{ node.name }}</a>

                    <div class="tree-focus" v-if="!unrestricted && focused && focused.id === node.id" @click="setFocus(null)">
                        <i class="fas fa-eye-slash"></i>
                    </div>

                    <div class="tree-status">
                        <i
                            class="fas fa-lock"
                            v-if="!node.is_ready"
                            title="Locked to prevent accidental publish"
                        ></i>
                        <i v-else-if="node.published_at && dirty" class="fas fa-pen-square text-info" title="Unpublished"></i>
                        <i v-else-if="!node.published_at" class="fas fa-square text-info" title="Unpublished"></i>
                    </div>

                    <div class="tree-tools" v-if="editable">
                        <div class="tree-tools-icon" @click.stop="menuIsOpen = !menuIsOpen"><i class="fas fa-cog"></i></div>

                        <div class="tree-tools-menu" v-show="menuIsOpen">
                            <div class="tree-tool" v-if="canAddChildren" @click="addTo">
                                <i class="fas fa-plus-circle"></i>
                                <div class="tree-tool-label">
                                    Add child
                                </div>
                            </div>
                            <div class="tree-tool" v-if="canAddSiblings" @click="addBefore">
                                <i class="fas fa-arrow-circle-up tree-tool-icon"></i>
                                <div class="tree-tool-label">
                                    Insert before
                                </div>
                            </div>
                            <div class="tree-tool" v-if="canAddSiblings" @click="addAfter">
                                <i class="fas fa-arrow-circle-down tree-tool-icon"></i>
                                <div class="tree-tool-label">
                                    Insert after
                                </div>
                            </div>
                            <div
                                class="tree-tool"
                                v-if="canFocus"
                                @click="setFocus(node)"
                            >
                                <i class="fas fa-eye"></i>
                                <div class="tree-tool-label">
                                    Focus
                                </div>
                            </div>
                            <div class="tree-tool" v-if="focused && focused.id === node.id" @click="setFocus(null)">
                                <i class="fas fa-eye"></i>
                                <div class="tree-tool-label">
                                    Clear focus
                                </div>
                            </div>
                            <div class="tree-tool" v-if="canSort" @click="$emit('sort',parent)">
                                <i class="fas fa-sort"></i>
                                <div class="tree-tool-label">
                                    Change order
                                </div>
                            </div>
                            <div class="tree-tool" v-if="canMove" @click="editor.move(node)">
                                <i class="fas fa-arrows-alt"></i>
                                <div class="tree-tool-label">
                                    Move
                                </div>
                            </div>

                            <div class="tree-tool" @click="stash">
                                <i class="fas fa-hand-pointer tree-tool-icon"></i>
                                <div class="tree-tool-label">
                                    Select
                                </div>
                            </div>
                            <div class="tree-tool" v-if="canTrash" @click="trash(node)">
                                <i class="fas fa-trash tree-tool-icon"></i>
                                <div class="tree-tool-label">
                                    Delete
                                </div>
                            </div>

                        </div>
                    </div>

                </div>



            </div>

            <template v-if="isOpen" v-for="child in children">
                <branch
                    :node="child"
                    :depth="depth+1"
                    :open="open"
                    :parent="node"
                    @open="openNow"
                    @close="close"
                    @select="select"
                    @sort="$emit('sort',$event)"
                    :forced-order="isAnArchive"
                ></branch>
            </template>
        </div>

    </div>
</template>
<script>
export default {
    props: {
        node: { type: Object },
        parent: { default: null },
        depth: { type: Number, default: 0 },
        open: { default: [] },
        forcedOrder: { type: Boolean, default: false }
    },
    name: 'branch',
    data() {
        return {
            menuIsOpen: false
        }
    },
    computed: {
        nodeIsStashed() {
            return ~this.$store.getters['Editor/stashedNodes'].map(n => n.id).indexOf(this.node.id);
        },
        hasStashedChildren() {
            if(!this.hasChildren) {
                return false;
            }
            for (const n of this.$store.getters['Editor/stashedNodes']) {
                if(n.lft > this.node.lft && n.rgt < this.node.rgt) {
                    return true;
                }
            }
            return false;
        },
        hasStashedParent() {
            for (const n of this.$store.getters['Editor/stashedNodes']) {
                if(n.lft < this.node.lft && n.rgt > this.node.rgt) {
                    return true;
                }
            }
            return false;
        },
        isAnArchive() {
            return !!~this.$store.getters['Types/archives'].indexOf(this.node.type);
        },
        focused() {
            return this.$store.getters['Tree/focused'];
        },
        isRoot() {
            return ~this.$store.getters['Tree/userRoots'].indexOf(this.node.id);
        },
        isSiteRoot() {
            return ~this.$store.getters['Tree/siteRoots'].indexOf(this.node.id);
        },
        canSort() {
            return !this.forcedOrder && this.node && !this.isRoot;
        },
        canTrash() {
            return this.node.parent_id && !this.isRoot;
        },
        canAddChildren() {
            return this.node && this.$store.getters['Types/branchable'](this.node.type);
        },
        canMove() {
            return !this.isRoot;
        },
        canFocus() {
            return this.hasChildren && !this.focused && !this.isSiteRoot;
        },
        canAddSiblings() {
            return !this.forcedOrder
                && this.parent
                && this.$store.getters['Types/branchable'](this.parent.type);
        },
        dirty() {
            return this.node.current_sha !== this.node.published_sha;
        },
        icon() {
            return this.$store.getters['Types/icon'](this.node.type);
        },
        isOpen() {
            return this.open && ~this.open.indexOf(this.node.id);
        },
        spacers() {
            return new Array(this.depth-1);
        },
        hasChildren() {
            return (this.node.rgt - this.node.lft) > 1;
        },
        canLoadChildren() {
            return !this.isOpen && this.hasChildren;
        },
        children() {
            return this.$store.getters['Tree/children'](this.node.id);
        }
    },
    methods: {
        toggleStash() {
            this.$store.dispatch('Editor/toggleStashedNode',this.node);
        },
        stash() {
            this.$store.dispatch('Editor/stashNode',this.node);
        },
        setFocus(node) {
            this.$store.commit('Tree/focus',node);
            this.$store.dispatch('Tree/load',node.id);
            this.menuIsOpen = false;
        },
        addTo() {
            this.editor.addNode({parent:this.node});
            this.openNow(this.node.id);
        },
        addAfter() {
            this.editor.addNode({parent:this.parent,after:this.node});
        },
        addBefore() {
            this.editor.addNode({parent:this.parent,before:this.node});
        },
        openNow(id) {
            this.$emit('open',id);
            this.menuIsOpen = false;
        },
        close(id) {
            this.$emit('close',id);
        },
        toggle() {
            this.$emit(this.isOpen ? 'close' : 'open',this.node.id);
        },
        isSelectable(node) {
            if(this.cannotSelect && ~this.cannotSelect.indexOf(node.id)) {
                return false;
            }
            if(this.pagesOnly && !node.page_template) {
                return false;
            }
            if(this.selectable.length && !~this.selectable.indexOf(node.type)) {
                return false;
            }
            if(this.onlyBranchable && !this.canAddChildren) {
                return false;
            }
            return true;
        },
        trySelect(node) {
            if(!this.isSelectable(node)) {
                return;
            }
            this.select(node.id);
        },
        select(nodeId) {
            this.$emit('select',nodeId);
        },
        trash(node) {
            if(confirm('Are you sure you want to delete '+node.name+'?')) {
                this.$store.dispatch('Tree/trash',node);
            }
        }
    },
    inject: [
        'cannotSelect',
        'onlyBranchable',
        'selectable',
        'unrestricted',
        'pagesOnly',
        'selectedNodes',
        'stashedNodeIds',
        'editable',
        'editor',
        'stashMode'
    ]

}
</script>

<script setup>
</script>