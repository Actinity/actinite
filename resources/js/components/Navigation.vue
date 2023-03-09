<template>
<div id="ac_header">

    <div class="ac-nav-tabs">
        <div class="ac-nav-tab">
            <strong>Actinite</strong>
        </div>

        <template v-for="tab in tabs">
            <router-link
                :to="tab.slug"
                class="ac-nav-tab"
                v-text="tab.name"
            />
        </template>
    </div>

    <div class="ac-nav-meta">
        <div class="ac-nav-tab">
            {{ $store.getters['Auth/name'] }}
        </div>
        <a href="/actinite/logout" class="ac-nav-tab">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>

</div>
</template>
<script>
export default {
    computed: {
        csrf() {
            return window.axios.$d
        },
        tabs() {
            return this.$store.getters['Auth/tabs'];
        }
    }
}
</script>
<style lang="scss">
#ac_header {
    display: flex;
}
.ac-nav-tabs,.ac-nav-meta {
    display: flex;
}
.ac-nav-tabs {
    flex: 1;
}
.ac-nav-meta {
    justify-content: flex-end;
}

.ac-nav-tab {
    padding: 1rem;
    color: #fff;
}

a.ac-nav-tab {

    text-decoration: none;

    &:hover {
        background-color: #999;
    }

    &.router-link-active {
        background-color: #fff;
        color: #444;
    }
}



</style>
