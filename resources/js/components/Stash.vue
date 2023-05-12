<template>
<div>

    <a href="#" @click.prevent="clear">Clear</a>
    <a href="#" @click.prevent="$emit('move',nodes)">Move</a>

	<div v-for="node in nodes">

		<i :class="$store.getters['Types/icon'](node.type)"></i>
		{{ node.name }}
		<a href="#" @click.prevent="$store.commit('Editor/unstashNode',node)"><i class="fas fa-minus-circle"></i></a>
	</div>

</div>
</template>
<script>
export default {
	watch: {
		nodes(is,was) {
			if(!is.length) {
				this.$emit('empty');
			}
		}
	},
	computed: {
		nodes() {
			return this.$store.getters['Editor/stashedNodes'];
		},
		count() {
			return this.nodes.length;
		},
		clear() {
			this.$store.commit('Editor/clearStashedNodes');
		}
	}
}
</script>