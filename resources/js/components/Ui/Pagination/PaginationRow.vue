<template>
<div class="row">
    <div class="col-6 d-flex align-self-center">
        <pagination-stats></pagination-stats>
    </div>

    <div class="col-6">
        <paginator class="justify-content-end"></paginator>
    </div>
</div>
</template>
<script>
import PaginationStats from "./PaginationStats";
import Paginator from "./Paginator";
export default {
    components: {
        Paginator,
        PaginationStats
    }
}
</script>
