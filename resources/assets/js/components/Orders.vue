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
                <th>Order id</th>
                <th>Position id</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for='order in orders'>
                <td> {{ order.created_at }}</td>
                <td>{{ order.pair }}</td>
                <td>{{ order.side}}</td>
                <td>{{ order.size }}</td>
                <td>{{ order.amount}}</td>
                <td>{{order.status }}</td>
                <td>{{order.order_id}}</td>
                <td>{{order.position_id}}</td>
            </tr>
            </tbody>
        </table>

        <div class="pagination">
            <button class="btn btn-default" @click="fetchOrders(pagination.prev_page_url)"
                    :disabled="!pagination.prev_page_url">
                Previous
            </button>
            <span>Page {{pagination.current_page}} of {{pagination.last_page}}</span>
            <button class="btn btn-default" @click="fetchOrders(pagination.next_page_url)"
                    :disabled="!pagination.next_page_url">Next
            </button>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        name: "orders",
        data() {
            return {
                orders: [
                    {created_at: '' ,pair: 'EUR', side: 'buy', amount: 0.0, size: 0.0, status: '', order_id: '', position_id: 0}
                ],
                timer: '',
                page: 1,
                pagination: {}
            }
        },

        created: function () {

        },

        mounted() {
            this.fetchOrders();
        },
        methods: {
            fetchOrders: function (page_url) {
                let vm = this;
                page_url = page_url || '/getorders';


                axios.get(page_url)
                    .then(response => {
                        // JSON responses are automatically parsed.
                        this.orders = response.data.data;
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