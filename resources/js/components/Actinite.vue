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
	    'cloudinaryName',
        'roots',
        'maxUploadSize',
	    'features'
    ],
    mounted() {
        this.$store.commit('Auth/setUser',this.user);
        this.$store.commit('Assets/setRoot',this.assetRoot);
		this.$store.commit('Assets/setCloudinaryName',this.cloudinaryName);
        this.$store.commit('Assets/setMaxUploadSize',parseInt(this.maxUploadSize));
        this.$store.dispatch('Types/load');
        this.$store.commit('Tree/setSiteRoots',this.roots);
		this.$store.commit('setFeatures',this.features || []);

        setInterval(this.keepalive,600000);

    },
    methods: {
        keepalive() {
            axios.get('/actinite/api/keepalive');
        }
    }
}
</script>
