/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

//Vue.component('portfolio', require('./components/Portfolio.vue'));

const app = new Vue({
    el: '#app',
    components: {
        'portfolio': require('./components/Portfolio.vue'),
        'positions': require('./components/Positions.vue'),
        'orders': require('./components/Orders.vue'),
        'trailingpositions': require('./components/TrailingPositions.vue'),
        'botsettings': require('./components/BotSettings.vue'),
        'indicators': require('./components/Indicators.vue'),
        'botcurrentprices': require('./components/BotCurrentPrices.vue'),
        'botheartbeat': require('./components/BotHeartbeat.vue')

    }
});
