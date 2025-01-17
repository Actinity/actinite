<template>
<div>

	<button
		class="btn btn-sm btn-info mx-2"
		v-if="node.is_ready && (hasChildren || node.published_sha !== node.current_sha)"
		@click="publish"
	>Publish</button>

	<button
		class="btn btn-sm btn-dark mx-2"
		v-if="!node.is_protected && node.published_sha"
		@click.prevent="unpublish"
	>Unpublish
	</button>

	<modal ref="modal">

		<div class="modal-title">Publish {{ node.name }}</div>

		<label class="d-block">
			<input type="radio" v-model="deep" :value="false" /> Only this node
		</label>

		<label class="d-block">
			<input type="radio" v-model="deep" :value="true" /> Include all children
		</label>

		<div class="mt-3">
			<span v-if="publishing">Publishing...</span>
			<button v-else class="btn btn-primary" @click.once="doPublish">Publish</button>
		</div>
	</modal>

</div>
</template>
<script>
import Modal from "./Ui/Modal.vue";
export default {
	props: {
		node: {}
	},
	data() {
		return {
			deep: false,
			publishing: true
		}
	},
	computed: {
		hasChildren() {
			return this.node.rgt - this.node.lft > 1;
		}
	},
	methods: {
		unpublish() {
			this.$store.dispatch('Editor/unpublish',this.node.id);
		},
		publish() {
			if(!this.hasChildren) {
				this.doPublish();
				return;
			}
			this.deep = false;
			this.publishing = false;
			this.$refs.modal.open();
		},
		doPublish() {
			this.publishing = true;
			this.$store.dispatch( 'Editor/publish',{node:this.node.id,deep:this.deep});
		}
	},
	components: {
		Modal
	}
}
</script>