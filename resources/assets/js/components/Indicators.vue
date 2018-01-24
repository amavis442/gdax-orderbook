<template>
    <div>
        <table class="table table-striped">
            <thead>
            <tr>
                <th>Indicator</th>
                <th>signal</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for='indicator in indicators'>
                <td> {{ indicator.name }}</td>
                <td>
                    <span class="{{ indicator.styleclass }}">
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
                    {name: '' , signal: 0, styleclass: ''}
                ],
                timer: ''
            }
        },
        created: function () {
            this.timer = setInterval(this.fetchIndicators('/getindicators'),10000)
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