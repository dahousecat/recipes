<template>
	<div class="image-upload">
		<image-preview :preview="value" @close="$emit('input', null)"
			 v-if="value"></image-preview>
		<div class="image-upload__form" v-else>
			<input class="image__input" type="file" accept="image/*" @change="upload">
		</div>
	</div>
</template>
<script type="text/javascript">
	import ImagePreview from './ImagePreview.vue'
	export default {
		components: {
			ImagePreview
		},
		props: {
			value: {
			    type: [String, File],
			    default: null
			}
		},
		methods: {

			upload(e) {
				const files = e.target.files
				if (files && files.length > 0) {
				  this.$emit('input', files[0])
				}
			}
		}
	}
</script>
<style lang="scss">
	.image__input {
		max-width: 22rem;
	}
	.image {

	}
	.image__preview {
		position: relative;
	}

	.image__close {
		position: absolute;
		right: 0;
		top: 0;
	}
</style>