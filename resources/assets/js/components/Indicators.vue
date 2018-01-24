<template>
    <div>
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
                    <span class="label" v-bind:class="{ 'label-danger': indicator.issell, 'label-success':  indicator.isbuy }">
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
                    {indicatorname: '', indicatorsignal: 0, timestamp: '', issell: false, isbuy: false}
                ],
                timer: '',
                errors: [],
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