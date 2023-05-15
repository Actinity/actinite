<template>
    <div>
        <template v-if="asset">
            <audio :src="$store.getters['Assets/path'](asset)" controls />
            <div>{{ asset.file_name }}
            <a href="#" @click.prevent="edit">Change</a>
            </div>
        </template>
        <a href="#" @click.prevent="edit" v-else>Click to browse</a>

    </div>
</template>
<script>
export default {
    props: [
        'modelValue',
    ],
    methods: {
        edit() {
            this.$mitt.emit('assets:open',{
                callback: this.edited,
                asset: this.localValue,
                type: 'audio'
            })
        },
        edited(asset) {
            this.localValue = asset ? asset.id : null;
        }
    },
    computed: {
        asset() {
            return this.localValue ? this.$store.getters['Assets/asset'](this.localValue) : null;
        },
        localValue: {
            get() {
                return this.modelValue;
            },
            set(v) {
                this.$emit('update:modelValue',v);
            }
        }
    }
}
</script>
