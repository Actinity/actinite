<template>
    <div>
        <template v-if="localValue">
            <audio :src="$store.getters.assetPath(localValue)" v-if="localValue" controls />
            <div>{{ fileName }}
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
        edited(payload) {
            this.localValue = payload ? payload.path : null;
        }
    },
    computed: {
        fileName() {
            return this.localValue ? this.localValue.split('/').pop() : '';
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
