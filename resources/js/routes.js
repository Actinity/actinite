import store from './store';

function requireAdmin(to,from,next) {
    if(store.getters['Auth/isAdmin']) {
        next();
    } else {
        next('/');
    }
}

export default [
    { path: '/admin', component: require('./components/Home').default },
    {
        path: '/admin/editor',
        component: require('./components/Editor').default,
        children: [
            { path: ':id', component: require('./components/Editor').default }
        ]
    },
    {
        path: '/admin/users',
        component: require('./components/Users/UserIndex').default,
        beforeEnter: requireAdmin
    }
]

