<template>
    <div>
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
                <td> {{ indicator.indicatorname }}</td>
                <td>{{ indicator.timestamp }}</td>
                <td>
                    <span>
                        <div v-if="indicator.indicatorsignal == 'sell'">
                            <span class="alert alert-danger">{{ indicator.indicatorsignal }}</span>
                        </div>

                        <div v-if="indicator.indicatorsignal == 'buy'">
                            <span class="alert alert-success">{{ indicator.indicatorsignal }}</span>
                        </div>

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
                    {indicatorname: '', indicatorsignal: 0, timestamp: ''}
                ],
                timer: ''
            }
        },
        created: function () {
            this.timer = setInterval(this.fetchIndicators(), 2000)
        },
        methods: {
            fetchIndicators: function () {
                axios.get('/getindicators')
                    .then(response => {
                        this.indicators = response.data;
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