// export default [
//     {path: '/', redirect: '/home'},
//
//     {
//         path: '/home',
//         name: 'home-index',
//         component: require('./pages/home/index.vue').default,
//         meta: {
//             resource: 'home',
//             createTitle: () => 'Home',
//         },
//     },
// ];

import VueRouter, {createRouter, createWebHistory} from 'vue-router';
import Home from "./pages/home/index.vue";

export const HOME = 'home';

const routes = [
    { path: '/', redirect: '/home' },
    { path: '/', name: HOME, component: Home, meta: { resource: 'home', createTitle: () => 'Home' } },
];


const router = createRouter({
    history: createWebHistory(),
    routes,
    mode: 'history',
    base: '/logger-ui',
});


router.beforeEach((to, from, next) => {
    to.meta.title = to.meta.createTitle(to.params);

    document.title = "Logger UI - " + to.meta.title;

    next();
});

export default router;
