import { createRouter, createWebHashHistory } from 'vue-router';

import LoginView from '../components/LoginView.vue';
import RegisterView from '../components/RegisterView.vue';
import DashboardView from '../components/DashboardView.vue';
import ExercisesView from '../components/ExercisesView.vue';
import WorkoutsView from '../components/WorkoutsView.vue';
import WorkoutCreateView from '../components/WorkoutCreateView.vue';
import AppLayout from '../components/AppLayout.vue';
import PublicShell from '../components/PublicShell.vue';
import { clearFlash, store } from '../services/store.js';

const routes = [
    {
        path: '/',
        component: AppLayout,
        children: [
            { path: '', redirect: '/dashboard' },
            { path: 'dashboard', component: DashboardView, meta: { requiresAuth: true } },
            { path: 'exercises', component: ExercisesView, meta: { requiresAuth: true } },
            { path: 'workouts', component: WorkoutsView, meta: { requiresAuth: true } },
            { path: 'workouts/create', component: WorkoutCreateView, meta: { requiresAuth: true } }
        ]
    },
    {
        path: '/',
        component: PublicShell,
        children: [
            { path: 'login', component: LoginView, meta: { guestOnly: true } },
            { path: 'register', component: RegisterView, meta: { guestOnly: true } }
        ]
    }
];

export const router = createRouter({
    history: createWebHashHistory(),
    routes
});

router.beforeEach((to, from, next) => {
    if (to.meta.requiresAuth && !store.token) {
        clearFlash();
        next('/login');
        return;
    }

    if (to.meta.guestOnly && store.token) {
        next('/dashboard');
        return;
    }

    next();
});
