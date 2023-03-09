<template>
<div>
<slot @page="updatePage"></slot>
</div>
</template>
<script>
export default {
    props: [
        'ref'
    ],
    methods: {
        update(data) {
            this.pagination_data.current_page = data.current_page;
            this.pagination_data.last_page = data.last_page;
            this.pagination_data.from = data.from;
            this.pagination_data.to = data.to;
            this.pagination_data.total = data.total;

            if(data.current_page > data.last_page) {
                this.$emit('page',1);
            }
        },
        updatePage(v) {
            this.$emit('page',v);
        }
    },
    data() {
        return {
            pagination_data: {
                last_page: 1,
                current_page: 1,
                from: 0,
                to: 0,
                total: 0
            }
        }
    },
    provide() {
        return {
            pagination_data: this.pagination_data,
            pagination_wrapper: this
        }
    }
}
</script>
