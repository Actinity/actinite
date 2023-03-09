<template>
    <navigation></navigation>

    <router-view></router-view>

    <div id="ac_footer">
        <div id="ac_footer_left">
        </div>
        <div id="ac_footer_right">

        </div>
    </div>
</template>
<script>
import Navigation from "./Navigation";
export default {
    components: {Navigation},
    props: [
        'user',
        'assetRoot',
        'roots'
    ],
    mounted() {
        this.$store.commit('Auth/setUser',this.user);
        this.$store.commit('setAssetRoot',this.assetRoot);
        this.$store.dispatch('Types/load');
        this.$store.commit('Tree/setSiteRoots',this.roots);

        setInterval(this.keepalive,600000);

    },
    methods: {
        keepalive() {
            axios.get('/actinite/api/keepalive');
        }
    }
}
</script>
