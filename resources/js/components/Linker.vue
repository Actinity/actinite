<template>
<modal
    ref="modal"
    width="90%"
    fullheight
>
    <div class="modal-header">
        <div class="ac-editor-tabs">
            <a
                href="#"
                class="ac-editor-tab"
                v-for="(label,slug) in tabs"
                v-text="label"
                @click.prevent="tab = slug"
                :class="tab === slug ? 'ac-editor-tab-selected' : ''"
            />
        </div>
    </div>

    <div class="modal-body modal-body-scrolling">

        <div v-show="tab === 'nodes'">
            <tree
                ref="tree"
                :root="null"
                @select="selectNode"
                :selected="selectedNodes"
                :unrestricted="true"
                :pages-only="true"
            ></tree>
        </div>

        <div v-show="tab === 'files'">

            <table class="table table-hover table-striped">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Size</th>
                    <th>Uploaded</th>
                </tr>
                </thead>
                <tbody>
                <tr
                    v-for="file in files"
                    @click="select(file)"
                    class="file-option"
                    :class="file.id === selectedAsset ? 'file-selected' : ''"
                >
                    <td>{{ file.file_name }}</td>
                    <td>{{ $filters.fileSize(file.size) }}</td>
                    <td>{{ file.created_at }}</td>
                </tr>
                </tbody>
            </table>

        </div>
    </div>

    <div class="modal-footer modal-footer-row" v-if="tab === 'files'">
        <file-upload @upload="uploaded"></file-upload>
    </div>


</modal>
</template>
<script>
import Modal from "./Ui/Modal";
import Tree from "./Tree/Tree";
import FileUpload from "./Ui/FileUpload";
export default {
    data() {
        return {
            url: '',
            callback: () => {},
            meta: {},
            files: [],
            tab: 'nodes',
            selectedNodes: [],
            selectedAsset: null,
            tabs: {
                nodes: 'Pages',
                files: 'Files'
            }
        }
    },
    mounted() {
        this.$mitt.on('linker:open',(e) => {
            this.callback = e.callback;
            this.url = e.url;
            this.callback = e.callback;
            this.meta = e.meta;
            this.selectedNodes = [];
            this.tab = 'nodes';

            let assetMatch = this.url.match(/assets\/(\d+)\//);

            if(assetMatch) {
                this.tab = 'files';
                this.selectedAsset = parseInt(assetMatch[1]);
            } else {
                let matches = this.url.match(/^cms:\/\/node\/(\d+)$/);

                if(matches) {
                    const id = parseInt(matches[1]);
                    this.$nextTick(() => {
                        this.selectedNodes = [id];
                        this.$refs.tree.loadTo(id);
                    });
                }
            }

            this.open();
        });
    },
    unmounted() {
        this.$mitt.off('linker:open');
    },
    methods: {
        save() {
            this.callback(this.url,this.meta);
            this.$refs.modal.close();
        },
        open() {
            this.$refs.modal.open();
            this.loadFiles();
        },
        loadFiles() {
            axios.get('/actinite/api/assets?type=file')
                .then(r => {
                    this.files = r.data.data;
                })
        },
        selectNode(nodeId) {
            this.url = 'cms://node/'+nodeId;
            this.save();
        },
        select(file) {
            this.url = this.$store.getters.assetPath(file.path);
            this.meta.title = file.file_name;
            this.save();
        },
        uploaded(file) {
            this.files.push(file);
            this.select(file);
        }
    },
    components: {
        FileUpload,
        Tree,
        Modal
    }
}
</script>
