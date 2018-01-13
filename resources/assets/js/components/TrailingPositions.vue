<template>
    <div>
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

        <table class="table table-striped">
            <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Pair</th>
                <th>Boughtfor</th>
                <th>Currentprice</th>
                <th>Trailingstop</th>
                <th>Profit</th>
                <th>Triggers</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for='position in positions'>
                <td>{{ position.id }}</td>
                <td>{{ position.created_at }}</td>
                <td>{{ position.pair }}</td>
                <td>&euro; {{ parseFloat(position.amount).toFixed(2) }}</td>
                <td>&euro; {{ parseFloat(position.currentprice).toFixed(2)}}</td>
                <td>&euro; {{ parseFloat(position.trailingstopprice).toFixed(2)}}</td>
                <td>{{ profit(position) }}</td>
                <td>{{ position.status}} / {{ triggerMsg(position) }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    export default {
        name: "trailingpositions",
        data() {
            return {
                positions: [
                    {id: 0, created_at: '', pair: 'BTC-EUR', amount: 0.0, size: 0.0, status: '', sellfor: 0.0, trailingstop: 1.0, watch: true, currentprice: 0.0, trailingstopprice: 0.0, trailingstoptrigger: 0}
                ],
                timerTrailing: '',
                message: '',
                errors: [],
            }
        },
        created: function () {
            this.timerTrailing = setInterval(this.fetchTrailing, 1000)
        },
        mounted() {
            this.fetchTrailing();
        },
        methods: {
            fetchTrailing: function () {
                axios.get('/gettrailing')
                    .then(response => {
                        // JSON responses are automatically parsed.
                        this.positions = response.data;
                    })
                    .catch(e => {
                        this.errors.push(e)
                    })
            },
            cancelAutoUpdate: function () {
                clearInterval(this.timerTrailing)
            },
            profit: function (position) {
                let cost = parseFloat(position.size) * parseFloat(position.open);
                let sold = parseFloat(position.size) * parseFloat(position.currentprice);
                let profit = parseFloat(sold - cost).toFixed(2);

                return profit
            },
            beforeDestroy() {
                clearInterval(this.timerTrailing)
            },
            triggerMsg: function (position) {
                if (position.trailingstoptrigger == -1) {
                    return "SELL"
                }
                if (position.trailingstoptrigger == 0) {
                    return "HOLD"
                }
                if (position.trailingstoptrigger == 1) {
                    return "BUY"
                }

            }
        },

    }
</script>

<style scoped>

</style>