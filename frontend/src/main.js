// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import router from './router'
import iView from 'iview';
import 'iview/dist/styles/iview.css';

import {Tools} from './assets/js/common'

Vue.config.productionTip = false;

Vue.use(iView);

router.beforeEach((to, from, next) => {
    document.documentElement.scrollTop = document.body.scrollTop = 0;
    let token = Tools.CookieHelper.getToken();
    if (to.path === '/login' && token && token !== 'null' && token !== 'undefined') {
        next({path: '/'})
    } else if ((!token || token === 'null' || token === 'undefined') && to.path !== '/login') {
        next({path: '/login'})
    } else {
        next()
    }
});

/* eslint-disable no-new */
new Vue({
    data: {},
    el: '#app',
    router,
    // components: {App},
    // template: '<App/>',

    template: '<router-view></router-view>',

    methods: {},
    mounted: function () {

    }
});




