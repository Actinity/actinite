<template>
<div class="pagination-stats">
    <span v-if="pagination_data.to && pagination_data.from">
            Showing {{ pagination_data.from }} - {{ pagination_data.to}} of {{ pagination_data.total }}
        </span>
    <span v-else>No results</span>
</div>
</template>
<script>
export default {
    inject: ['pagination_data']
}
</script>
