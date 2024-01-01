<template>
<div v-if="ready" class="ac-editor">

    <div class="node-settings">

        <a href="#" @click.prevent="close" class="ac-editor-close"><i class="fas fa-times"></i></a>

        <div class="form-group row">
            <label class="form-label col-form-label col-2">Name</label>
            <div class="col-9">
                <input class="form-control" type="text" maxlength="255" v-model="node.name" />
            </div>
        </div>

        <div class="form-group row" v-if="node.is_ready">
            <label class="form-label col-form-label col-2">Publish date</label>

            <div class="col-5">
                <datepicker
                    v-model="publishedAt"
                    autoApply
                    hideInputIcon
                    closeOnAutoApply
                    clearable
                    input-class-name="form-control"
                    format="EEE MMM dd yyyy, HH:mm"
                />
            </div>
            <div class="col-4 col-form-label">


                <button
                    class="btn btn-sm btn-info mx-2"
                    v-if="node.published_sha !== node.current_sha"
                    @click="publish"
                >Publish now</button>

                <button
                    class="btn btn-sm btn-dark mx-2"
                    v-if="!node.is_protected && node.published_sha"
                    @click.prevent="$store.dispatch('Editor/unpublish',node.id)"
                    >Unpublish
                </button>

            </div>
        </div>

        <div class="form-group row" v-if="templates.length">
            <label class="form-label col-form-label col-2">Template</label>
            <div class="col-5">
                <select v-model="node.page_template" class="form-control">
                    <option v-for="template in templates" :value="template">
                        {{ template ? $filters.unslug(template) : 'None' }}
                    </option>
                </select>
            </div>
            <div class="col-4 col-form-label" v-if="node.page_template">
                <a :href="'/actinite/switch-mode/draft?return_to='+node.path" target="_blank">Open</a>
            </div>
        </div>

    </div>

    <div class="ac-editor-tabs node-editor-tabs" v-if="tabs.length > 1">
        <a
            href="#"
            class="ac-editor-tab"
            v-for="label in tabs"
            :class="{'ac-editor-tab-selected':label === tab}"
            v-text="label"
            @click.prevent="setTab(label)"
        />
    </div>

    <!--
    <pre
        v-text="JSON.stringify($store.getters['Editor/snapshot'],null,4)"
        style="white-space:pre-wrap;max-width:800px;"
    />

    <pre
        v-text="JSON.stringify($store.getters['Editor/currentSnap'],null,4)"
        style="white-space:pre-wrap;max-width:800px;"
    />
    -->

    <div v-if="fields.length === 0" class="editor-no-fields">
        Nothing to edit
    </div>

    <template v-else v-for="field in fields">
    <div
        v-show="show(field)"
        class="form-group"
        :class="{'row': field.inline && false}"
    >
        <div class="form-label" :class="{'col-form-label col-3':field.inline && false}">
	        {{ field.label }} <!--- {{ field.type }}-->
	        <span v-if="field.instructions" class="text-muted d-inline-block pl-2" style="font-weight:normal;">{{ field.instructions }}</span>

        </div>

        <div :style="fieldStyle(field)" :class="{'col-9':field.inline && false}">
            <html-input
                v-if="field.type === 'html'"
                v-model="fieldData[field.name]"
            ></html-input>

	        <video-input
		        v-else-if="field.type === 'video'"
		        v-model="fieldData[field.name]"
	        />

            <boolean-input
                v-else-if="field.type === 'boolean'"
                v-model="fieldData[field.name]"
            />

            <date-input
                v-else-if="field.type === 'date' || field.type === 'datetime'"
                v-model="fieldData[field.name]"
                :show-time="field.type === 'datetime'"
            />

            <template v-else-if="field.type === 'select'">

                <radio-input
                    v-if="Object.values(field.options).length < 4"
                    v-model="fieldData[field.name]"
                    :options="field.options"
                />

                <select-input
                    v-else
                    v-model="fieldData[field.name]"
                    :options="field.options"
                />

            </template>

            <image-input
                v-else-if="field.type === 'image'"
                v-model="fieldData[field.name]"
            />

            <audio-input
                v-else-if="field.type === 'audio'"
                v-model="fieldData[field.name]"
            />

            <node-input
                v-else-if="field.type === 'node'"
                v-model="fieldData[field.name]"
                :relation="field.relation"
                :label="field.label"
                :types="field.types"
            />

	        <resizable-text
		        v-else-if="field.type === 'textarea'"
		        v-model="fieldData[field.name]"
	        />

            <input v-else
               class="form-control"
               type="text"
               maxlength="255"
               :value="fieldData[field.name]"
               @input="update(field.name,$event)"
            />
        </div>
    </div>
    </template>

    <teleport to="#ac_footer_right">

		<vimeo-progress></vimeo-progress>

        <!--<div v-text="isDirty ? 'DIRTY!' : 'Status: Published'"></div>-->
        <button class="btn btn-primary" :disabled="!isDirty" @click="save">Save changes</button>
    </teleport>

</div>
</template>
<script>
import Datepicker from '@vuepic/vue-datepicker';
import BooleanInput from './Ui/BooleanInput';
import HtmlInput from "./Ui/HtmlInput";
import { mapGetters } from 'vuex';
import SelectInput from "./Ui/SelectInput";
import ImageInput from "./Ui/ImageInput";
import AudioInput from "./Ui/AudioInput";
import { parseISO,formatISO9075 } from 'date-fns';
import DateInput from "./Ui/DateInput.vue";
import NodeInput from "./Ui/NodeInput.vue";
import RadioInput from "./Ui/RadioInput.vue";
import ResizableText from "./Ui/ResizableText.vue";
import VideoInput from "./Ui/VideoInput.vue";
import VimeoProgress from "./Ui/VimeoProgress.vue";
export default {
    computed: {
        ...mapGetters('Editor',[
            'isDirty',
            'fieldData',
            'tab'
        ]),
        fields() {
            return this.$store.getters['Types/fields'](this.node.type);
        },
        publishedAt: {
            get() {
                return this.node.published_at ? parseISO(this.node.published_at) : null;
            },
            set(v) {
                this.node.published_at = v ? formatISO9075(v) : null;
            }
        },
        node: {
            get() {
                return this.$store.getters['Editor/node'];
            },
            set(v) {
                this.$store.commit('Editor/update',v);
            }
        },
        ready() {
            return this.node && !this.$store.getters['Editor/loading'];
        },
        tabs() {
            return _.chain(this.fields).map('tab').uniq().map(t => t ? t : 'Content').value();
        },
        templates() {
            return this.$store.getters['Types/templates'](this.node.type);
        }
    },
    methods: {
        fieldStyle(field) {
            let style = {};
            if(field.width) {
                style.maxWidth = field.width;
            }
            return style;
        },
        setTab(tab) {
            this.$store.commit('Editor/setTab',tab);
        },
        show(field) {
            return !field.hidden && this.checkFieldLogic(field) && this.checkTabLogic(field);
        },
        checkTabLogic(field) {
            return (field.tab || 'Content') === this.tab;
        },
        checkFieldLogic(field) {
            if(!field.logic) {
                return true;
            }
            return jsonLogic.apply(field.logic,this.fieldData);
        },
        close() {
            this.$emit('close');
        },
        save() {
            this.$store.dispatch('Editor/save');
        },
        update(field,e) {
            this.$store.commit('Editor/updateField',{field:field,value:e.target.value});
        },
        publish() {
            this.$store.dispatch('Editor/publish',[this.node.id]);
        }
    },
    components: {
	    VimeoProgress,
	    VideoInput,
	    ResizableText,
        RadioInput,
        NodeInput,
        DateInput,
        AudioInput,
        ImageInput,
        SelectInput,
        HtmlInput,
        BooleanInput,
        Datepicker
    }
}
</script>
