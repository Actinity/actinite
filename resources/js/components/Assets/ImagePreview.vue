<template>
<div>
    <div class="ac-img-preview">
        <img
            ref="img"
            :src="assetUrl"
            @click="captureFocusPoint($event)"
        />

        <i class="fas fa-crosshairs ac-img-crosshairs" v-if="settingFocalPoint" :style="crosshairStyle"></i>

    </div>

    <div v-if="settingFocalPoint" class="ac-img-preview-focal-tools">

        <div class="ac-img-preview-focal-buttons">
            <button class="btn btn-sm btn-primary" @click="saveFocalPoint">Save</button>
            <button class="btn btn-sm btn-primary" @click="cancelEditingFocalPoint">Cancel</button>
        </div>

        <div
            class="ac-img-preview-focal-preview"
            :style="previewStyle"
        />

    </div>
    <div v-else class="ac-img-preview-focal-bar">
        Focal point:
        <a href="#" @click.prevent="setFocalPoint" v-text="focalPointSet ? 'Change' : 'Set'" />
        <a href="#" @click.prevent="clearFocalPoint" v-if="focalPointSet">Clear</a>
    </div>

</div>
</template>
<script>
export default {
	props: [
		'asset'
	],
    watch: {
        asset() {
            this.settingFocalPoint = false;
            this.focalX = this.asset.pos_x || null;
            this.focalY = this.asset.pos_y || null;
        }
    },
    data() {
        return {
            focalX: this.asset.pos_x || null,
            focalY: this.asset.pos_y || null,
            settingFocalPoint: false
        }
    },
    computed: {
        focalPointSet() {
            return this.focalX !== null && this.focalY !== null;
        },
        assetUrl() {
            return this.$store.getters.assetPath(this.asset.path);
        },
        previewStyle() {
            return {
                backgroundImage: 'url("' + this.assetUrl + '")',
                backgroundPosition: this.focalX+'% '+this.focalY+'%'
            }
        },
        crosshairStyle() {
            return {
                left: 'calc('+this.focalX+'% - 10px)',
                top: 'calc('+this.focalY+'% - 10px)',
            }
        }
    },
    methods: {
        setFocalPoint() {
            this.focalX = this.focalX || 50;
            this.focalY = this.focalY || 50;
            this.settingFocalPoint = true;
        },
        clearFocalPoint() {
            this.focalX = null;
            this.focalY = null;

            if(this.asset.pos_x !== null || this.asset.pos_y !== null) {
                this.saveFocalPoint();
            }
        },
        captureFocusPoint(e) {
            if(this.settingFocalPoint) {
                this.focalX = Math.round((e.offsetX / this.$refs.img.clientWidth) * 100);
                this.focalY = Math.round((e.offsetY / this.$refs.img.clientHeight)*100);
            }
        },
        saveFocalPoint() {

            axios.put('/actinite/api/assets/'+this.asset.id,{
                pos_x: this.focalX,
                pos_y: this.focalY
            })
                .then(r => {
                    this.settingFocalPoint = false;
                    this.$emit('update',r.data);
                })
                .catch(e => {
                    alert('Unable to save. Refresh and try again');
                });

        },
        cancelEditingFocalPoint() {
            this.settingFocalPoint = false;
            this.focalX = this.asset.pos_x || null;
            this.focalY = this.asset.pos_y || null;
        }
    }
}
</script>
<style lang="scss">
.ac-img-preview {
    position: relative;
    cursor: default;
    display: inline-block;
    user-select: none;
}
.ac-img-preview img {
    max-width:100%;
    height: auto;
    max-height:200px;
}
.ac-img-crosshairs {
    position: absolute;
    background-color: rgba(255,255,255,0.5);
    color: #f00;
    width: 20px;
    height: 20px;
    text-align: center;
    padding: 2px;
    border-radius: 100%;
}
.ac-img-preview-focal-bar {
    background: #ddd;
    color: #444;
    padding: 0 5px;

    a {
        display: inline-block;
        padding: 0 5px;
    }
}
.ac-img-preview-focal-tools {
    padding: 5px;
    background: #ddd;
    text-align: center;
    button {
        margin-right: 5px;
    }
}
.ac-img-preview-focal-preview {
    margin: 15px auto 5px auto;
    background-size: cover;
    width: 150px;
    height: 150px;
}
.ac-img-preview img {
    -webkit-user-drag: none;
}
</style>