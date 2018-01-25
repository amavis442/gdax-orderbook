<template>
    <div>
        {{ heartbeat }}
    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        name: "botheartbeat",
        data() {
            return {
                heartbeat: '',
                timer: '',
                errors: [],
                message: ''
                            }
        },
        created: function () {
            this.timer = setInterval( this.fetchIndicators, 1000)
        },
        methods: {
            fetchIndicators: function () {
                this.errors = [];

                axios.get('/heartbeat')
                    .then(response => {
                        this.heartbeat = response.data;
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