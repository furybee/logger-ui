export default [
    { path: '/', redirect: '/home' },

    {
        path: '/home',
        name: 'home-index',
        component: require('./pages/home/index').default,
        meta: {
            resource: 'home',
            createTitle: () => 'Home',
        },
    },
];
