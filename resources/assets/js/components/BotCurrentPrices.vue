<template>
    <div>
        <span v-for='price in prices'>
            <span class="label label-default">{{ price.pair}}</span> :
            <span class="label label-primary">&euro;{{ price.currentprice}}</span> |
        </span>
    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        name: "botcurrentprices",
        data() {
            return {
                prices: [
                    {
                        pair: '',
                        currentprice: ''
                    }
                ],
                timer: '',
                errors: []
            }
        },
        created: function () {
            this.timer = setInterval(this.fetchPrices, 1000)
        },
        methods: {
            fetchPrices: function () {
                this.errors = [];
                axios.get('/currentprices')
                    .then(response => {
                        this.prices = response.data.currentprices;
                    })
                    .catch(e => {
                        this.errors.push(e)
                    })
            },
            cancelAutoUpdate: function () {
                clearInterval(this.timer)
            }
        },
        beforeDestroy() {
            clearInterval(this.timer)
        }
    }
</script>

<style scoped>

</style>