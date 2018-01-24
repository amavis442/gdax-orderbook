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
                <td> {{ indicator.name }}</td>
                <td>{{ indicator.timestamp }}</td>
                <td>
                    <span>
                    {{ indicator.signal }}
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
                    {name: '' , signal: 0, timestamp: ''}
                ],
                timer: ''
            }
        },
        created: function () {
            this.timer = setInterval(this.fetchIndicators('/getindicators'),1000)
        },

        mounted() {
            this.fetchIndicators('/getindicators');
        },
        methods: {
            fetchIndicators: function (page_url) {
                let vm = this;
                page_url = page_url || '/getindicators';
                axios.get(page_url)
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