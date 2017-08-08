<template>
    <div class="home">

        <div class="row--s">

            <home-panel :recipes="recipes" :title="'All recipes'"></home-panel>

            <home-panel
                    :recipes="vitamin_c.recipes"
                    :attributeType="vitamin_c.attributeType">
            </home-panel>

        </div>

        <div class="row--s">

            <home-panel
                    :recipes="fibre.recipes"
                    :attributeType="fibre.attributeType">
            </home-panel>

        </div>

    </div>

</template>
<script type="text/javascript">
    import Vue from 'vue';
    import { formatNumber } from '../helpers/misc';
    import { get } from '../helpers/api'
    import HomePanel from '../components/HomePanel.vue';

    export default {
        components: {
            HomePanel,
        },
        data() {
            return {
                recipes: [],
                vitamin_c: [],
                fibre: [],
            }
        },
        created() {
            get('/api/recipes?with[]=vitamin_c&with[]=fibre')
                .then((res) => {
                    Vue.set(this.$data, 'recipes', res.data.recipes);
                    Vue.set(this.$data, 'vitamin_c', res.data.vitamin_c);
                    Vue.set(this.$data, 'fibre', res.data.fibre);
                    this.$emit('finishedLoading');
                })
        },
        methods: {
            formatNumber(number) {
                return formatNumber(number);
            }
        }
    }
</script>
