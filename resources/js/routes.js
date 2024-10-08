import {createRouter, createWebHistory} from 'vue-router';
import Home from "./pages/home/Index.vue";

export const HOME = 'home';

const routes = [
    { path: '/logger-ui', redirect: '/home' },
    { path: '/logger-ui/home', name: HOME, component: Home, meta: { resource: 'home', createTitle: () => 'Home' } },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    mode: 'history',
    base: '/logger-ui',
});

router.beforeEach((to, from, next) => {
    // to.meta.title = to.meta.createTitle(to.params);

    // document.title = "Logger UI - " + to.meta.title;

    next();
});

export default router;
