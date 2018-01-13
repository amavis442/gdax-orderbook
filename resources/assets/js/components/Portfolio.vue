<template>

    <table class="table table-striped">
        <thead>
        <tr>
            <th>Wallet</th>
            <th>Currency</th>
            <th>Koers</th>
            <th>Waarde</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for='wallet in wallets'>
            <td>{{ wallet.name }}</td>
            <td><span v-if='wallet.name == "EUR"'>&euro;</span> {{ wallet.balance }}
            </td>
            <td>
                <span class="label label-default">&euro; {{ wallet.koers }}</span>
            </td>
            <td>
                &euro; {{wallet.waarde }}
            </td>
        </tr>
        </tbody>
        <tfoot>
        <tr>
            <td colspan='4'>
                <div class='pull-right'>
                    Portfolio waarde: &euro; {{ portfolio }}
                </div>
            </td>
        </tr>
        </tfoot>
    </table>
</template>

<script>
    import axios from 'axios';

    export default {
        name: "portfolio",
        data() {
            return {
                greeting: '',
                portfolio: 0.0,
                wallets: [
                    {name: 'EUR', balance: 0.0, koers: 0.0, waarde: 0.0},
                    {name: 'BTC', balance: 0.0, koers: 0.0, waarde: 0.0},
                    {name: 'ETH', balance: 0.0, koers: 0.0, waarde: 0.0},
                    {name: 'LTC', balance: 0.0, koers: 0.0, waarde: 0.0}
                ],
                timer: ''
            }
        },
        created: function () {
            this.timer = setInterval(this.fetchWallets, 3000)
        },

        mounted() {
            axios.get('/getwallets')
                .then(response => {
                    // JSON responses are automatically parsed.
                    this.wallets = response.data.wallets;
                    this.portfolio = response.data.portfolio;
                })
                .catch(e => {
                    this.errors.push(e)
                })
        },

        methods: {

            fetchWallets: function () {

                axios.get('/getwallets')
                    .then(response => {
                        // JSON responses are automatically parsed.
                        this.wallets = response.data.wallets;
                        this.portfolio = response.data.portfolio;

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
