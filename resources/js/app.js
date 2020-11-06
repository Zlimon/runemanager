/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

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

Vue.use(UniqueId);

Vue.component('skillhiscore', require('./components/SkillHiscore.vue').default);
Vue.component('bosshiscore', require('./components/BossHiscore.vue').default);
Vue.component('accounthiscore', require('./components/AccountHiscore.vue').default);
Vue.component('accountskillhiscore', require('./components/AccountSkillHiscore.vue').default);
Vue.component('accountbosshiscore', require('./components/AccountBossHiscore.vue').default);
Vue.component('notification', require('./components/Notification.vue').default);

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
