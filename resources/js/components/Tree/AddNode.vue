<template>
<modal ref="modal" :width="modalWidth" auto-close>
    <div class="modal-title">{{ title }}</div>

    <div class="row">
        <div class="col-6" v-if="showTree">

            <tree
                @select="selectParent($event)"
                :selected="selected"
                :branchable="true"
                :selectable="this.parentTypes"
            />

        </div>
        <div :class="showTree ? 'col-6' : 'col-12'">

            <div v-if="after || before" class="mb-5 alert alert-info">
                {{ after ? 'After' : 'Before' }} {{ after ? after.name : before.name }}
            </div>

            <div class="ac-option-list mb-3">
                <div
                    v-for="type in availableChildTypes"
                    class="ac-option"
                    :class="type.type === newType ? 'ac-selected': ''"
                    @click="newType = type.type"
                >
                    <i :class="type.icon"></i>
                    {{ type.name }}
                </div>
            </div>

            <div class="form-group">
                <label>Name</label>
                <input
                    type="text"
                    class="form-control"
                    maxlength="255"
                    ref="nameInput"
                    v-model="newName"
                    @keyup.enter="save"
                />
            </div>

            <div class="form-group" v-if="pageTemplates.length > 1">
                <label>Template</label>
                <select class="form-control" v-model="pageTemplate">
                    <option
                        v-for="template in pageTemplates"
                        :value="template"
                        v-text="template ? $filters.unslug(template) : 'None'"
                    />
                </select>
            </div>

        </div>
    </div>


    <div class="modal-buttons">

        <div class="d-inline-block mx-5" v-if="!$store.getters['Editor/isDirty']">
            <label>
                <input type="checkbox" v-model="openAutomatically" value="1" />
                Open in Editor
            </label>
        </div>
        <button class="btn btn-primary" :disabled="!canSave" @click="save">Add</button>
    </div>

</modal>
</template>
<script>
import Modal from "../Ui/Modal";
import Tree from "./Tree.vue";
export default {
    data() {
        return {
            node: {},
            newType: null,
            forcedType: null,
            newName: null,
            pageTemplate: null,
            after: null,
            before: null,
            mustSelectNode: false
        }
    },
    watch: {
        pageTemplates(v) {
            this.autoSelectTemplate();
        },
        newType() {
            this.autoSelectName();
        },
        node() {
            this.autoSelectName();
            this.autoSelectType();
        }
    },
    computed: {
        parentTypes() {
            if(this.forcedType) {
                return this.$store.getters['Types/allowingChildren'](this.forcedType).map(t => t.type);
            }
            return null;
        },
        showTree() {
            return this.mustSelectNode;
        },
        modalWidth() {
            return this.showTree ? '900px' : null;
        },
        payload() {
            let payload = {
                type: this.newType,
                name: this.newName,
                page_template: this.pageTemplate,
                parent_id: this.node ? this.node.id : null,
            };
            if(this.after) {
                payload['after'] = this.after.id;
            }
            if(this.before) {
                payload['before'] = this.before.id;
            }
            if(this.relatedTo) {
                payload['related'] = this.relatedTo;
            }
            return payload;
        },
        json() {
            return JSON.stringify(this.payload,null,4);
        },
        title() {
            return this.node ? 'Add child to '+this.node.name : 'Add a node';
        },
        availableChildTypes() {
            let types = [];
            if(this.forcedType) {
                return [this.$store.getters['Types/get'](this.forcedType)];
            }
            if(this.node) {
                types = this.$store.getters['Types/availableChildren'](this.node.type);
            }
            return types;
        },
        pageTemplates() {
            if(this.newType) {
                let type = this.$store.getters['Types/get'](this.newType);
                return type.pageTemplates;
            }
            return [];
        },
        canSave() {
            return this.node && this.newType && this.newName;
        },
        selected() {
            return this.node ? [this.node.id] : [];
        },
        openAutomatically: {
            get() {
                return this.$store.getters['Editor/openNewNodesAutomatically'];
            },
            set(v) {
                this.$store.commit('Editor/setOpenNewNodesAutomatically',!!v);
            }
        }
    },
    methods: {
        selectParent(node) {
            this.node = this.$store.getters['Tree/byId'](node);
        },
        open(options) {
            this.node = options.parent || null;
            this.mustSelectNode = !options.parent;
            this.after = options.after || null;
            this.before = options.before || null;
            this.newType = options.type || null;
            this.forcedType = options.type || null;
            this.relatedTo = options.relatedTo || null;
            this.newName = null;
            this.pageTemplate = null;
            this.$refs.modal.open();
            this.$nextTick(() => {
                this.autoSelectName();
                this.autoSelectType();
            });
        },
        autoSelectName() {
            this.$nextTick(() => {
                this.$refs.nameInput.focus();
            });
        },
        autoSelectType() {
            if(this.availableChildTypes.length > 0) {
                this.newType = this.availableChildTypes[0].type;
            } else {
                this.newType = null;
            }
            this.autoSelectTemplate();
        },
        autoSelectTemplate() {
            if(this.pageTemplates.length > 0) {
                this.pageTemplate = this.pageTemplates[0];
            }
        },
        save() {
            if(this.canSave) {
                this.$store.dispatch('Editor/add',this.payload);
                this.$refs.modal.close();
            }
        }
    },
    components: {
        Tree,
        Modal
    }
}
</script>
