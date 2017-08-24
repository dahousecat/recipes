<template>
    <div role="document" class="modal-overlay" :aria-labelledby="labelId" v-if="show">

        <div class="modal" :class="active ? 'modal--active' : 'modal--inactive'" tabindex="0">

            <div class="modal__header">

                <h1 class="modal__title" :id="labelId">{{title}}</h1>

                <button @click="close()" type="button" class="modal__close" v-if="dismissible">
                    <i class="fa fa-times"></i> <span class="visuallyhidden">Close (Esc)</span>
                </button>
            </div>

            <div class="modal__content" :class="modalLoading ? 'modal__content--loading' : ''">
                <slot></slot>
            </div>

            <div class="modal__footer" v-if="$slots.footer">
                <slot name="footer"></slot>
            </div>

        </div>

    </div>
</template>

<script type="text/javascript">
    import { EventBus } from '../event-bus';

    export default {
        data() {
            return {
                labelId: '',
                id: '',
                modalLoading: false,
                body: document.querySelector('body'),
                active: true,
            }
        },
        props: {
            show: {
                type: [Boolean],
                default: false,
            },
            dismissible: {
                type: [Boolean],
                default: true,
            },
            activeProp: {
                type: [Boolean],
                default: true,
            },
        },
        computed: {
            title() {
                if(typeof this.$slots.title === 'undefined') {
                    return '';
                }
                return this.$slots.title['0'].children['0'].text;
            },
        },
        watch: {
            show() {
                // Move modal to the top of the DOM so it avoids the blur
                if(this.$el.parentNode.constructor.name === 'HTMLDivElement') {
                    this.$el.parentNode.removeChild(this.$el);
                    document.body.appendChild(this.$el);
                }

                EventBus.$emit('blurWrapper', this.show);
                this.body.classList.toggle('noscroll', this.show);
            },
            activeProp() {
                this.active = this.activeProp;
            }
        },
        created() {
            let token = Math.random().toString(36).substring(7);
            this.labelId = 'modal-label-' + token;

            EventBus.$on('modalLoading', value => {
                this.modalLoading = value;
            });

            EventBus.$on('modalActive', value => {
                this.active = value;
                console.log('receive modalActive');
            });
        },
        methods: {
            close() {
                EventBus.$emit('blurWrapper', false);
                EventBus.$emit('modalActive', true);
                this.$emit('close');
            },
        }
    }
</script>

<style lang="scss">
    @import "../../sass/variables/breakpoints";
    @import "../../sass/variables/mixins";

    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal__blur {
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        filter: blur(0.5rem);
    }

    .modal {
        margin: 0 auto;
        max-width: 97rem;
        max-height: calc(100vh - 4rem);
        position: relative;
        outline: none;

        @include mq($from: xs) {
            top: 50%;
            transform: translateY(-50%);
        }
    }

    .modal--inactive {
        filter: blur(0.1rem);
    }

    .modal__header {
        background-color: darken(darkgreen, 3);
        height: 5rem;
    }

    .modal__title {
        color: white;
        font-size: 1.8rem;
        padding: 1rem 0 0 2rem;
    }

    .modal__footer {
        bottom: 0;
        background-color: white;
        padding: 1rem;
        text-align: right;
    }

    .modal__content {
        max-height: calc(100vh - 15rem);
        overflow-x: hidden;
        padding-top: $gutter;
        background-color: white;
    }

    .modal__content--loading {
        &::before {
            content: '';
            position: fixed;
            top: 5rem;
            right: 0;
            bottom: 0;
            left: 0;
            background-color: rgba(0, 0, 0, 0.3);
            opacity: 1;
            pointer-events: none;
            transition: opacity 500ms;
            z-index: 10;
        }
        &::after {
            @include loading;
            z-index: 200;
        }
    }

    .modal__close {
        position: absolute;
        top: 0;
        right: 0;
        border: none;
        background: transparent;
        color: white;
        padding: 1.6rem;
        cursor: pointer;

        &:hover {
            background-color: darkgreen;
        }
    }

</style>
