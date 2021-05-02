/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

import Vue from 'vue'

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

import UniqueId from 'vue-unique-id';
import 'advanced-laravel-vue-paginate/dist/advanced-laravel-vue-paginate.css'

Vue.use(UniqueId);
Vue.use(require('advanced-laravel-vue-paginate'));
Vue.use(require('vue-moment'));


Vue.component('announcementall', require('./components/Notification/AnnouncementAll.vue').default);
Vue.component('accountevent', require('./components/Notification/AccountEvent.vue').default);
Vue.component('broadcast', require('./components/Notification/Broadcast.vue').default);


Vue.component('skillhiscore', require('./components/SkillHiscore.vue').default);
Vue.component('bosshiscore', require('./components/BossHiscore.vue').default);
Vue.component('accounthiscore', require('./components/AccountHiscore.vue').default);
Vue.component('accountskillhiscore', require('./components/AccountSkillHiscore.vue').default);
Vue.component('accountbosshiscore', require('./components/AccountBossHiscore.vue').default);

Vue.component('equipment', require('./components/Equipment.vue').default);
Vue.component('bank', require('./components/Bank.vue').default);
Vue.component('quests', require('./components/Quests.vue').default);
Vue.component('onlinestatus', require('./components/OnlineStatus.vue').default);

Vue.component(
    'passport-clients',
    require('./components/passport/Clients.vue').default
);

Vue.component(
    'passport-authorized-clients',
    require('./components/passport/AuthorizedClients.vue').default
);

Vue.component(
    'passport-personal-access-tokens',
    require('./components/passport/PersonalAccessTokens.vue').default
);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
});
