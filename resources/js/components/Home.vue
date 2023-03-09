<template>
<div>I'm the routed home</div>
</template>
<script>
export default {
    mounted() {
        if(!this.$store.getters['Auth/isAdmin']) {
            this.$router.push('/admin/editor');
        }
    }

}
</script>
