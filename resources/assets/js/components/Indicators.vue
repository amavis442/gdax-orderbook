<template>
    <div>
        <div class="page-header">
            <h2>{{ pair }}</h2>
        </div>


        <div v-if="errors.length > 0 ">
            <div class="alert alert-danger" role="alert">
                <ul v-for="error in errors">
                    <li>{{ error }}</li>
                </ul>
            </div>
        </div>

        <ul class="nav nav-tabs">
            <li role="presentation" v-bind:class="{ active: isActiveBCTEUR }"
                v-on:click="togglePair(1)"><a href="#">BTC-EUR</a></li>
            <li role="presentation" v-bind:class="{ active: isActiveETHEUR }"
                v-on:click="togglePair(2)"><a href="#">ETH-EUR</a></li>
            <li role="presentation" v-bind:class="{ active: isActiveLTCEUR }"
                v-on:click="togglePair(3)"><a href="#">LTC-EUR</a></li>
        </ul>



        <table class="table table-striped">
            <thead>
            <tr>
                <th>Indicator</th>
                <th>Timestamp</th>
                <th>Signal</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for='indicator in indicators'>
                <td>{{ indicator.indicatorname }}</td>
                <td>{{ indicator.timestamp }}</td>
                <td>
                    <span class="label"
                          v-bind:class="{ 'label-danger': indicator.issell, 'label-success':  indicator.isbuy, 'label-default':  indicator.ishold }">
                        {{ indicator.indicatorsignal }}
                    </span>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        name: "indicators",
        data() {
            return {
                indicators: [
                    {
                        indicatorname: '',
                        indicatorsignal: 0,
                        timestamp: '',
                        issell: false,
                        isbuy: false,
                        ishold: true
                    }
                ],
                timer: '',
                errors: [],
                message: '',
                pair: 'ETH-EUR',
                isActiveBCTEUR: false,
                isActiveETHEUR: true,
                isActiveLTCEUR: false
            }
        },
        created: function () {
            this.timer = setInterval( this.fetchIndicators, 5000)
        },
        methods: {
            fetchIndicators: function () {
                this.errors = [];

                axios.get('/getindicators?pair='+ this.pair)
                    .then(response => {
                        this.indicators = response.data[this.pair];
                    })
                    .catch(e => {
                        this.errors.push(e)
                    })
            },
            togglePair: function (togglePair) {
                this.isActiveBCTEUR = false;
                this.isActiveETHEUR = false;
                this.isActiveLTCEUR = false;

                switch (togglePair) {
                    case 1:
                        this.pair = 'BTC-EUR';
                        this.fetchIndicators('BTC-EUR');
                        this.isActiveBCTEUR = true;
                        break;
                    case 2:
                        this.pair = 'ETH-EUR';
                        this.fetchIndicators('ETH-EUR');
                        this.isActiveETHEUR = true;
                        break;
                    case 3:
                        this.pair = 'LTC-EUR';
                        this.fetchIndicators('LTC-EUR');
                        this.isActiveLTCEUR = true;
                        break;
                    default:
                        this.pair = 'ETH-EUR';
                        this.fetchIndicators('ETH-EUR');
                        this.isActiveETHEUR = true;
                        break;
                }
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