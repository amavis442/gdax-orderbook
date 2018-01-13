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
                <th>Size</th>
                <th>Price</th>
                <th>Status</th>
                <th>Sell for</th>
                <th>Trailing stop</th>
                <th>Watch</th>
                <th></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <tr v-for='position in positions'>
                <td>{{ position.id }}</td>
                <td>{{ position.created_at }}</td>
                <td>{{ position.pair }}</td>
                <td>{{ position.size}}</td>
                <td>&euro; {{ parseFloat(position.amount).toFixed(2) }}</td>
                <td>{{ position.status}}</td>
                <td>
                    <div class="input-group">
                        <div class="input-group-addon">&euro;</div>
                        <input type="text" class="form-control" v-model="position.sellfor">
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <div class="input-group-addon">&euro;</div>
                        <input type="text" class="form-control" v-model="position.trailingstop">
                    </div>
                </td>
                <td><input type="radio" value="1" v-model="position.watch" v-on:click="toggle(position,1)"> On <input type="radio" value="0" v-model="position.watch" v-on:click="toggle(position,0)"> Off</td>
                <td>
                    <button class="btn btn-default" v-on:click="updatePosition(position)">update</button>
                </td>
                <td>
                    <button class="btn btn-danger" v-on:click="placeTrailingOrder(position)">Trailing</button>
                </td>
                <td>
                    <button class="btn btn-danger" v-on:click="placeSellOrder(position)">Sell</button>
                </td>
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
                    {id: 0, created_at: '', pair: 'EUR', amount: 0.0, size: 0.0, status: '', sellfor: 0.0, trailingstop: 1.0, watch: true}
                ],
                timer: '',
                pagination: {},
                message: '',
                errors: [],
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
            },
            toggle: function (position, toggle) {
                console.log('Toggle ' + toggle);
                position.watch = toggle;
            },
            updatePosition: function (position) {
                console.log('Sellfor ' + position.sellfor + ' Trailingstop ' + position.trailingstop + ' watch ' + position.watch);

                this.message = '';
                axios.post('/updateposition', {id: position.id, sellfor: position.sellfor, trailingstop: position.trailingstop, watch: position.watch})
                    .then(response => {
                        // JSON responses are automatically parsed.
                        if (response.data.result == 'ok') {
                            this.message = 'Position ' + position.id + ' succesfully updated...';
                        } else {
                            this.message = 'Position ' + position.id + ' update failed...';
                        }

                        //this.positions = response.data.data;
                        //this.makePagination(response.data);
                    })
                    .catch(e => {
                        this.errors.push(e)
                    })
            },
            placeSellOrder: function (position) {
                console.log('Sellfor ' + position.sellfor + ' Trailingstop ' + position.trailingstop + ' watch ' + position.watch);

                this.message = '';
                this.errors = [];

                if (position.sellfor < 0 || !position.sellfor) {
                    this.errors.push('First give the amount for the sell');
                    return;
                }

                axios.post('/sellposition', {id: position.id, sellfor: position.sellfor, trailingstop: position.trailingstop, watch: position.watch})
                    .then(response => {
                        // JSON responses are automatically parsed.
                        if (response.data.result == 'ok') {
                            this.message = 'Sell order for position ' + position.id + ' succesfully placed...';
                        } else {
                            this.message = 'Placing sell order for position ' + position.id + ' failed '.response.data.msg;
                        }
                    })
                    .catch(e => {
                        this.errors.push(e)
                    })
            },
            placeTrailingOrder: function (position) {
                console.log('Sellfor ' + position.sellfor + ' Trailingstop ' + position.trailingstop + ' watch ' + position.watch);

                this.message = '';
                this.errors = [];

                if (position.trailingstop < 0 || !position.trailingstop) {
                    this.errors.push('First give the amount for the sell');
                    return;
                }


                axios.post('/trailingposition', {id: position.id, sellfor: position.sellfor, trailingstop: position.trailingstop, watch: position.watch})
                    .then(response => {
                        // JSON responses are automatically parsed.
                        if (response.data.result == 'ok') {
                            this.message = 'Trailing sell order for position ' + position.id + ' succesfully placed...';
                        } else {
                            this.errors.push('Placing trailing sell order for position ' + position.id + ' failed '.response.data.msg);
                        }
                    })
                    .catch(e => {
                        this.errors.push(e)
                    })
            },
            beforeDestroy() {
                clearInterval(this.timer)
            }
        }
    }
</script>

<style scoped>

</style>