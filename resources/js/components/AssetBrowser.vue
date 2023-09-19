<template>
<pagination-wrapper ref="pagination" @page="updatePage">
<modal ref="modal" width="90%" fullheight>

    <div class="row mb-5">
        <div class="col-4">
            <h3>{{ type === 'audio' ? 'Audio files' : 'Images' }}</h3>
        </div>
        <div class="col-5">
            <input
                type="search"
                class="form-control"
                v-model="searchText"
                placeholder="Search..."
            />
        </div>
    </div>

    <div class="asset-browser">

        <div class="asset-list" ref="list">
            <div
                v-for="asset in assets"
                class="asset"
                @click="select(asset)"
                @dblclick="save"
            >
                <div class="asset-preview">
                    <img
                        v-if="asset.type === 'image'"
                        style="max-width:100%;height: auto;max-height:200px"
                        :src="$store.getters['Assets/path'](asset,450)"
                    />

                    <i class="fas fa-headphones asset-icon"
                        v-if="asset.type === 'audio'"
                    ></i>

                </div>

                <div class="asset-name">{{ asset.file_name }}</div>
            </div>
        </div>


        <div v-if="selectedModel" class="asset-selected" ref="preview">
            <image-preview
                v-if="selectedModel && type === 'image'"
                :asset="selectedModel"
                @update="updateSelected($event)"
            ></image-preview>

            <dl>
                <dt>Name</dt>
                <dd>{{ selectedModel.file_name }}</dd>
                <dt>Size</dt>
                <dd>{{ $filters.fileSize(selectedModel.size) }}</dd>
                <template v-if="selectedModel.width">
                    <dt>Dimensions</dt>
                    <dd>{{ selectedModel.width }}px x {{ selectedModel.height}}px</dd>
                </template>
                <template v-if="selectedModel.duration">
                    <dt>Duration</dt>
                    <dd>{{ duration(selectedModel.duration) }}</dd>
                </template>
            </dl>

            <p>
                <button class="btn btn-primary" @click="save">Select</button>
            </p>
        </div>

    </div>

    <div class="modal-footer modal-footer-row">
        <div class="row">
            <div class="col-3">
                <file-upload @upload="uploaded" :accept="type === 'audio' ? '.mp3,.m4a' : 'image/*'"></file-upload>
            </div>
            <div class="col-9 d-flex justify-content-end">
                <pagination-stats class="mx-3"></pagination-stats>
                <paginator></paginator>
            </div>
        </div>
    </div>
</modal>
</pagination-wrapper>
</template>
<script>
import Modal from "./Ui/Modal";
import PaginationWrapper from "./Ui/Pagination/PaginationWrapper";
import Paginator from "./Ui/Pagination/Paginator";
import PaginationStats from "./Ui/Pagination/PaginationStats";
import FileUpload from "./Ui/FileUpload";
import ImagePreview from "./Assets/ImagePreview.vue";
export default {
    mounted() {
        this.$mitt.on('assets:open',(e) => {
            this.callback = e.callback;

            this.type = e.type;
			this.maxWidth = e.maxWidth || 3000;
            if(e.asset) {
	            if(typeof(e.asset) === 'string') {
					let id = e.asset.match(/\/assets\/(\d+)\//);
					this.selected = id[1];
	            } else {
		            this.selected = e.asset;
	            }
            }
            this.open();
        });
    },
    unmounted() {
        this.$mitt.off('assets:open');
    },
    created() {
        this.bufferedSearch = _.debounce(this.search,300);
    },
    watch: {
        searchText() {
            this.bufferedSearch();
        }
    },
    data() {
        return {
            page: 1,
            selectedModel: null,
            selected: null,
            type: null,
            assets: [],
            searchText: '',
            callback: () => {},
	        maxWidth: 3000
        }
    },
    methods: {
        duration(seconds) {
            if(!seconds) {
                return '-';
            }
            return new Date(seconds * 1000).toISOString().slice(11,19);
        },
        updatePage($event) {
           this.page = $event;
           this.load();
        },
        search() {
            this.page = 1;
            this.load();
        },
        save() {
            const assetToSave = {
                ...this.selectedModel,
                url: this.$store.getters['Assets/path'](this.selectedModel,this.maxWidth)
            };

            this.callback(assetToSave);
            this.$store.commit('Assets/merge',[this.selectedModel]);
            this.$refs.modal.close();
        },
        select(asset) {
            this.selectedModel = _.clone(asset);
            this.$nextTick(() => {
                this.$refs.preview.scrollTo(0,0);
            })
        },
        open() {
            this.selectedModel = null;
            this.$refs.modal.open();
            this.load();
            this.loadModel();
        },
        loadModel() {
            if(this.selected) {
                axios.get('/actinite/api/assets/'+this.selected)
                    .then(r => {
                        this.selectedModel = r.data;
                    });
            }
        },
        updateSelected(asset) {
            this.selectedModel = asset;
            let idx = this.assets.findIndex(a => a.id === asset.id);
            if(~idx) {
                this.assets.splice(idx,1,asset);
            }
        },
        load() {
            axios.get('/actinite/api/assets',{
                params: {
                    type: this.type,
                    search: this.searchText,
                    page: this.page
                }
            })
                .then(r => {
                    this.assets = r.data.data;
                    this.$refs.pagination.update(r.data);
                    this.$refs.list.scrollTo(0,0);
                })
                .catch(e => {
                    alert('Unable to load assets. Refresh and try again?');
                })
        },
        uploaded(asset) {
            this.assets.unshift(asset);
            this.select(asset);
            this.$refs.list.scrollTo(0,0);
        }
    },
    components: {
        ImagePreview,
        FileUpload,
        PaginationStats,
        Paginator,
        PaginationWrapper,
        Modal
    }
}
</script>
<style lang="scss">
.asset-browser {
    flex: 1;
    overflow: hidden;
    display: flex;
}
.asset-list {
    display: grid; /* 1 */
    grid-template-columns: repeat(auto-fill, 32%); /* 2 */
    grid-gap: 1rem; /* 3 */
    justify-content: space-between; /* 4 */
    overflow-x: hidden;
    overflow-y: auto;
    width: 100%;
}
.asset-selected {
    padding: 1rem;
    overflow-y: auto;
    min-width: 30%;
}
.asset {
    flex-basis: 32%;
    margin-bottom: 1.33333%;
    border: 1px solid #eee;
    padding: 1rem;
    cursor: pointer;
    transition: all linear 0.15s;
    display: flex;
    flex-direction: column;

    &:hover {
        background-color: #f8f8f8;
        transform: scale(1.05,1.05);
    }

}
.asset-preview {
    text-align: center;
    flex-grow: 1;
    margin-bottom: 1rem;
}
.asset-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}
.asset-name {
    margin: -1rem;
    margin-top: 1rem;
    background-color: #f8f8f8;
    font-size: 1.4rem;
    padding: .5rem 1rem;
}
</style>
