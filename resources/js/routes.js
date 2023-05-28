import store from './store';

function requireAdmin(to,from,next) {
    if(store.getters['Auth/isAdmin']) {
        next();
    } else {
        next('/');
    }
}

export default [
    { path: '/actinite', component: require('./components/Home').default },
    {
        path: '/actinite/editor',
        component: require('./components/Editor').default,
        children: [
            { path: ':id', component: require('./components/Editor').default }
        ]
    },
    {
        path: '/actinite/users',
        component: require('./components/Users/UserIndex').default,
        beforeEnter: requireAdmin
    }
]

