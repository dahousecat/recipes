<template>
    <div role="document" class="modal-overlay fade-out fade-in" :aria-labelledby="labelId" v-if="show">
        <div class="modal fade-out fade-in" style="" tabindex="0">
            <div class="modal-content">
                <slot></slot>
            </div>
        </div>
        <div class="modal-header fade-out fade-in">
            <div class="container">
                <slot name="title" :id="labelId"></slot>
            </div>
        </div>
        <button @click="$emit.close()" type="button" class="modal-close fade-out fade-in">
            x <span class="visuallyhidden">Close (Esc)</span>
        </button>
        <div class="modal-footer fade-out fade-in">
            <div class="container">
                <slot name="footer"></slot>
            </div>
        </div>
    </div>
</template>

<script type="text/javascript">
    export default {
        data() {
            return {
                labelId: '',
                id: '',
            }
        },
        props: {
            show: {
                type: [Boolean],
                default: false,
            },
        },
        created() {
            let token = Math.random().toString(36).substring(7);
            this.labelId = 'modal-label-' + token;
        }
    }
</script>