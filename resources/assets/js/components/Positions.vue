<template>
    <div>
        <table class="table table-striped">
            <thead>
            <tr>
                <th>Date</th>
                <th>Pair</th>
                <th>Side</th>
                <th>Size</th>
                <th>Price</th>
                <th>Status</th>
                <th>Sell for</th>
                <th>Trailing stop</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for='position in positions'>
                <td>{{ position.created_at }}</td>
                <td>{{ position.pair }}</td>
                <td>{{ position.side}}</td>
                <td>{{ position.size }}</td>
                <td>{{ position.amount}}</td>
                <td>{{ position.position }}</td>
                <td></td>
                <td></td>
            </tr>
            </tbody>
        </table>

        <div class="pagination">
            <button class="btn btn-default" @click="fetchpositions(pagination.prev_page_url)"
                    :disabled="!pagination.prev_page_url">
                Previous
            </button>
            <span>Page {{pagination.current_page}} of {{pagination.last_page}}</span>
            <button class="btn btn-default" @click="fetchpositions(pagination.next_page_url)"
                    :disabled="!pagination.next_page_url">Next
            </button>
        </div>
    </div>
</template>

<script>
    export default {
        name: "positions",
        data() {
            return {
                positions: [
                    {created_at: '', pair: 'EUR', side: 'buy', amount: 0.0, size: 0.0, position: ''}
                ],
                timer: '',
                pagination: {}
            }
        },
        mounted() {
            this.fetchpositions();
        },
        methods: {
            fetchpositions: function (page_url) {
                let vm = this;
                page_url = page_url || '/getpositions';


                axios.get(page_url)
                    .then(response => {
                        // JSON responses are automatically parsed.
                        this.positions = response.data.data;
                        this.makePagination(response.data);
                    })
                    .catch(e => {
                        this.errors.push(e)
                    })
            },
            makePagination: function (data) {
                let pagination = {
                    current_page: data.current_page,
                    last_page: data.last_page,
                    next_page_url: data.next_page_url,
                    prev_page_url: data.prev_page_url
                };
                this.pagination = pagination;
            }
            ,
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