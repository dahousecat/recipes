<template>
    <div id="ingredient-list">

        <div class="ingredient-list__form">

            <div class="ingredient-list__form-row">
                <label for="search-term" class="ingredient-list__form-label">Search term</label>
                <input type="text" id="search-term" v-model="term" class="ingredient-list__form-text" />
                <button @click="refresh" class="ingredient-list__form-button" title="Reload ingredient list">
                    <i class="fa fa-refresh ingredient-list__form-button-icon" aria-hidden="true"></i>
                </button>
            </div>

            <div class="ingredient-list__form-desc">
                Try search using a simple term. E.g. Milk, not Whole milk.
            </div>

        </div>

        <div class="ingredient-list__empty-msg" v-if="!Object.keys(ndb.groups).length">
            No search results found for {{lastSearchedTerm}}. Try using the American word, e.g. Zucchini not Courgette.
        </div>

        <ul class="ingredient-list">
            <li v-for="(group, key, index) in ndb.groups">

                <span class="ingredient-list__heading"
                      :title="'Show just ' + key"
                      @click="groupClick(key)">
                    {{key}}
                </span>

                <ul class="ingredient-list__child-list">
                    <li v-for="(item, index) in group" class="ingredient-list__row">
                        <a @click="selectNdbIngredient(item)">{{item.name}}</a>
                    </li>
                </ul>

            </li>
        </ul>
    </div>
</template>

<script type="text/javascript">
    import { get, post } from '../helpers/api';
    import { EventBus } from '../event-bus';

    export default {
        data() {
            return {
                term: '',
                lastSearchedTerm: '',
                group: false,
            };
        },
        props: {
            ndb: {
                type: [Object],
                default: {},
            },
            ingredient: {
                type: [String],
                default: '',
            },
        },
        created() {
            this.term = this.lastSearchedTerm = this.ingredient;
        },
        methods: {
            groupClick(key) {
                this.group = key;
                this.fetch();
            },
            refresh() {
                this.group = false;
                this.fetch();
            },
            fetch() {
                EventBus.$emit('modalLoading', true);
                let url = '/api/ndb/search/' + this.term;
                if(this.group) {
                    url = url + '?group=' + this.group;
                }
                get(url)
                    .then((res) => {
                        this.lastSearchedTerm = this.term;
                        EventBus.$emit('modalLoading', false);
                        this.ndb.groups = res.data.groups;
                    })
                    .catch(function (error) {
                        EventBus.$emit('modalLoading', false);
                        console.log(error);
                    });
            },
            selectNdbIngredient(item) {
                EventBus.$emit('modalLoading', true);
                get('/api/ndb/view/' + item.id)
                    .then((res) => {
                        EventBus.$emit('modalLoading', false);
                        EventBus.$emit('modalActive', true);
                        this.$emit('updateNutrients', res.data);
                        this.$emit('close');
                    })
                    .catch(function (error) {
                        EventBus.$emit('modalLoading', false);
                        console.log(error);
                        this.$emit('close');

                    });
            },
        }
    }
</script>

<style lang="scss">
    @import "../../sass/variables/breakpoints";

    .ingredient-list {
        margin: 0;
        list-style-type: none;
        padding: 1rem 2rem 2rem;

        @include mq($from: l) {
            column-count: 2;
        }
    }

    .ingredient-list__heading {
        background-color: darken(darkgreen, 3);
        padding: 0.4rem 0.8rem;
        display: block;
        color: white;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 200ms;

        &:hover {
            background-color: darken(darkgreen, 6);
        }
    }

    .ingredient-list__row {

        a {
            padding: 0.4rem;
            cursor: pointer;
            display: block;

            &:hover {
                background-color: lighten(lightgrey, 10);
            }
        }
    }

    .ingredient-list__child-list {
        list-style-type: none;
        margin: 0.5rem 0;
        padding: 0rem 0 0 1rem;
    }

    .ingredient-list__form {
        background-color: lighten(lightgrey, 10);
        margin: -2rem 0rem 1rem 0rem;
        padding: 1.6rem 3rem;
    }

    .ingredient-list__form-row {

    }

    .ingredient-list__empty-msg {
        padding: 1rem 2rem 2rem 2rem;
    }

    .ingredient-list__form-label {
        margin: 0;
        line-height: 1.8;
    }

    .ingredient-list__form-text {
        max-width: 20rem;
        padding: 0.4rem 0.5rem;
        border: 1px solid lightgrey;
    }

    .ingredient-list__form-button {
        height: 2.8rem;
        width: 3rem;
        border: none;
        background-color: darkgrey;
        cursor: pointer;
        transition: 200ms background-color;

        &:hover {
            background-color: darken(darkgrey, 5);
        }
    }

    .ingredient-list__form-button-icon {
        transition: transform 800ms ease-in-out;

        .ingredient-list__form-button:hover & {
            transform: rotate(360deg);
        }
    }

    .ingredient-list__form-desc {
        font-size: 1.2rem;
        margin-top: 0.4rem;
    }

</style>