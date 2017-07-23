<template>
    <div id="ingredient-list">
        <h2 slot="title">Select the closest match</h2>

        <div class="ingredient-list__form">

            <div class="ingredient-list__form-row">
                <label for="search-term" class="ingredient-list__form-label">Search term</label>
                <input type="text" id="search-term" v-model="term" class="ingredient-list__form-text" />
                <button @click="refresh" class="ingredient-list__form-button">
                    <i class="fa fa-refresh" aria-hidden="true"></i>
                </button>
            </div>

            <div class="ingredient-list__form-desc">
                Try search using a simple term. E.g. Milk, not Whole milk.
            </div>

        </div>


        <ul class="ingredient-list">
            <li v-for="(group, key, index) in ndb.groups">

                <span class="ingredient-list__heading" @click="groupClick(key)">{{key}}</span>

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
    import { loading } from '../helpers/misc';
    import { get, post } from '../helpers/api';

    export default {
        data() {
            return {
                term: '',
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
            this.term = this.ingredient;
        },
        methods: {
            groupClick(key) {
                this.group = key;
                this.refresh();
            },
            refresh() {
                loading(true, 'ingredient-list');
                let url = '/api/ndb/search/' + this.term;
                if(this.group) {
                    url = url + '?group=' + this.group;
                }
                get(url)
                    .then((res) => {
                        loading(false, 'ingredient-list');
                        this.ndb.groups = res.data.groups;
                    })
                    .catch(function (error) {
                        loading(false, 'ingredient-list');
                        console.log(error);
                    });
            },
            selectNdbIngredient(item) {
                loading(true, 'ingredient-list');
                get('/api/ndb/view/' + item.id)
                    .then((res) => {
                        loading(false, 'ingredient-list');
                        this.$emit('updateNutrients', res.data);
                        this.$emit('close');
                    })
                    .catch(function (error) {
                        loading(false, 'ingredient-list');
                        console.log(error);
                        this.$emit('close');

                    });
            },
        }
    }
</script>