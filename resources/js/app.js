/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import Vue from 'vue'

import UniqueId from 'vue-unique-id';
import 'advanced-laravel-vue-paginate/dist/advanced-laravel-vue-paginate.css';
import CKEditor from '@ckeditor/ckeditor5-vue2';
import VueSweetalert2 from 'vue-sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

Vue.use(require('./bootstrap')); // Load bootstrap.js
Vue.use(UniqueId);
Vue.use(require('advanced-laravel-vue-paginate'));
Vue.use(require('vue-moment'));
Vue.use( CKEditor );
Vue.use(VueSweetalert2);

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('announcementall', require('./components/notification/AnnouncementAll.vue').default);
Vue.component('accountevent', require('./components/notification/AccountEvent.vue').default);
Vue.component('broadcast', require('./components/notification/Broadcast.vue').default);

Vue.component('skillhiscore', require('./components/SkillHiscore.vue').default); // Fetch all skill hiscores
Vue.component('collectionhiscore', require('./components/CollectionHiscore.vue').default); // Fetch all collection hiscores

Vue.component('accounthiscore', require('./components/AccountHiscore.vue').default); // Component to switch between account hiscores

Vue.component('accountskillhiscore', require('./components/account/SkillHiscore.vue').default); // Fetch all account skill hiscores
Vue.component('accountbosshiscore', require('./components/account/BossHiscore.vue').default); // Fetch all account boss hiscores

Vue.component('equipment', require('./components/account/Equipment.vue').default);
Vue.component('quests', require('./components/account/Quests.vue').default);
Vue.component('bank', require('./components/account/Bank.vue').default);

Vue.component('onlinestatus', require('./components/OnlineStatus.vue').default);

Vue.component('newscreate', require('./components/NewsCreate.vue').default);

Vue.component('calendar', require('./components/Calendar.vue').default);
Vue.component('calendaredit', require('./components/CalendarEdit.vue').default);

Vue.component('groupaccount', require('./components/group/Account').default);
Vue.component('groupbank', require('./components/group/GroupBank').default);

// Pages
// PRE CLEANUP TEMPORARILY PLACEMENT
Vue.component('PageAdminUserIndex', require('./pages/admin/user/Index').default);
Vue.component('PageAdminUserShow', require('./pages/admin/user/Show').default);
Vue.component('PageAdminUserEdit', require('./pages/admin/user/Edit').default);

Vue.component('PageAdminAccountIndex', require('./pages/admin/account/Index').default);
Vue.component('PageAdminAccountCreate', require('./pages/admin/account/Create').default);
Vue.component('PageAdminAccountShow', require('./pages/admin/account/Show').default);

Vue.component('PageAdminNewsIndex', require('./pages/admin/news/Index').default);
Vue.component('PageAdminNewsShow', require('./pages/admin/news/Show').default);
Vue.component('PageAdminNewsEdit', require('./pages/admin/news/Edit').default);
// PRE CLEANUP TEMPORARILY PLACEMENT

Vue.component('PageGroupShow', require('./pages/group/Show').default);
Vue.component('PageAdminSettingResourcePack', require('./pages/admin/setting/ResourcePack').default);
Vue.component('PageAccountCompare', require('./pages/account/AccountCompare').default);

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
