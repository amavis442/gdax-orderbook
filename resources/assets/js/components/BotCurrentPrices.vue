<template>
    <div>
        <span v-for='price in prices'><span class="label label-default">{{ price.pair}}</span> <span class="label label-primary">{{ price.price}}</span></span>
    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        name: "botcurrentprice",
        data() {
            return {
                prices: [
                    {
                        pair: '',
                        price: ''
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
                        this.prices = response.data;
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