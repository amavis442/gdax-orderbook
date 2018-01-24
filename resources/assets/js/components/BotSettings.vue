<template>
    <div>
        <div class="panel panel-default">
            <div class="panel-heading">Settings {{ botsettings.pair}}</div>
            <div class="panel-body">
                <div v-if="message.length > 0">
                    <div class="alert alert-success" role="alert">{{ message}}</div>
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
                        v-on:click="togglePair(botsettings,1)"><a href="#">BTC-EUR</a></li>
                    <li role="presentation" v-bind:class="{ active: isActiveETHEUR }"
                        v-on:click="togglePair(botsettings,2)"><a href="#">ETH-EUR</a></li>
                    <li role="presentation" v-bind:class="{ active: isActiveLTCEUR }"
                        v-on:click="togglePair(botsettings,3)"><a href="#">LTC-EUR</a></li>
                </ul>


                <div class="row">
                    <div class="col-lg-6">
                        <label class="radio-inline">
                            <input type="radio" value="1" v-model="botsettings.botactive"
                                   v-on:click="toggle(botsettings,1)"> On
                        </label>
                        <label class="radio-inline">
                            <input type="radio" value="0" v-model="botsettings.botactive"
                                   v-on:click="toggle(botsettings,0)">
                            Off
                        </label>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="max_orders">Max orders</label>
                            <input type="text" class="form-control" id="max_orders" v-model="botsettings.max_orders"
                                   placeholder="1">
                        </div>

                        <div class="form-group">
                            <label for="order_minimal_size">Minimal order size</label>
                            <input type="text" class="form-control" id="order_minimal_size"
                                   placeholder="0.001"
                                   v-model="botsettings.minimal_order_size">
                        </div>

                        <div class="form-group">
                            <label for="trailingstop">Trailingstop</label>
                            <div class="input-group">
                                <div class="input-group-addon">&euro;</div>
                                <input type="text" class="form-control" id="trailingstop"
                                       placeholder="trailingstop"
                                       v-model="botsettings.trailingstop">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="tradebottomlimit">Trade bottom limit</label>
                            <div class="input-group">
                                <div class="input-group-addon">&euro;</div>
                                <input type="text" class="form-control" id="tradebottomlimit"
                                       placeholder="15000"
                                       v-model="botsettings.tradebottomlimit">

                            </div>
                        </div>
                        <div class="form-group">
                            <label for="tradetoplimit">Trade top limit</label>
                            <div class="input-group">
                                <div class="input-group-addon">&euro;</div>
                                <input type="text" class="form-control" id="tradetoplimit"
                                       placeholder="15000"
                                       v-model="botsettings.tradetoplimit">

                            </div>
                        </div>
                        <div class="form-group">
                            <label for="sellstradle">Sell stradle</label>
                            <div class="input-group">
                                <div class="input-group-addon">&euro;</div>
                                <input type="text" class="form-control" id="sellstradle"
                                       placeholder="15000"
                                       v-model="botsettings.sellstradle">

                            </div>
                        </div>

                        <div class="form-group">
                            <label for="buystradle">Buy stradle</label>
                            <div class="input-group">
                                <div class="input-group-addon">&euro;</div>
                                <input type="text" class="form-control" id="buystradle"
                                       placeholder="15000"
                                       v-model="botsettings.buystradle">
                            </div>
                        </div>
                        <button class="btn btn-default" v-on:click="updateSetting(botsettings)">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        name: "botsettings",
        data() {
            return {
                botsettings: [
                    {
                        pair: 'ETH-BTC',
                        trailingstop: 30.00,
                        max_orders: 1,
                        tradebottomlimit: 800,
                        tradetoplimit: 820,
                        minimal_order_size: 0.01,
                        sellstradle: 0.07,
                        buystradle: 0.03,
                        botactive: 0
                    }
                ],
                message: '',
                errors: [],
                isActiveBCTEUR: true,
                isActiveETHEUR: false,
                isActiveLTCEUR: false

            }
        },
        created: function () {
            this.fetchSetting('BTC-EUR')
        },
        methods: {
            fetchSetting: function (pair) {
                let vm = this;
                let page_url = '/getsetting?pair=' + pair;

                axios.get(page_url)
                    .then(response => {
                        console.log(response.data);
                        // JSON responses are automatically parsed.
                        this.botsettings = response.data;

                    }).catch(e => {
                    this.errors.push(e)
                })
            },
            toggle: function (botsettings, toggle) {
                console.log('Toggle ' + toggle);
                botsettings.botactive = toggle;
            },
            togglePair: function (botsettings, togglePair) {
                this.isActiveBCTEUR = false;
                this.isActiveETHEUR = false;
                this.isActiveLTCEUR = false;

                switch (togglePair) {
                    case 1:
                        this.fetchSetting('BTC-EUR');
                        this.isActiveBCTEUR = true;
                        break;
                    case 2:
                        this.fetchSetting('ETH-EUR');
                        this.isActiveETHEUR = true;
                        break;
                    case 3:
                        this.fetchSetting('LTC-EUR');
                        this.isActiveLTCEUR = true;
                        break;
                    default:
                        this.fetchSetting('BTC-EUR');
                        this.isActiveBCTEUR = true;
                        break;
                }
            },
            updateSetting: function (botsettings) {
                this.message = '';
                axios.post('/updatesetting', botsettings)
                    .then(response => {
                        // JSON responses are automatically parsed.
                        if (response.data.result == 'ok') {
                            this.message = 'Setting for ' + botsettings.pair + ' succesfully updated...';
                        } else {
                            this.message = 'Setting for ' + botsettings.pair + ' update failed...';
                        }
                    })
                    .catch(e => {
                        this.errors.push(e)
                    })
            },
        },
        beforeDestroy() {
            clearInterval(this.timer)
        }
    }
</script>

<style scoped>

</style>