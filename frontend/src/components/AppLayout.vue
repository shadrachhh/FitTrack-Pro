<template>
    <div class="app-shell min-vh-100">
        <nav v-if="store.token" class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
            <div class="container">
                <RouterLink class="navbar-brand fw-semibold" to="/dashboard">FitTrack Pro</RouterLink>
                <div class="navbar-nav me-auto">
                    <RouterLink class="nav-link" to="/dashboard">Dashboard</RouterLink>
                    <RouterLink class="nav-link" to="/workouts">Workouts</RouterLink>
                    <RouterLink class="nav-link" to="/workouts/create">Log Workout</RouterLink>
                    <RouterLink class="nav-link" to="/exercises">Exercises</RouterLink>
                </div>
                <div class="d-flex align-items-center gap-3 text-white">
                    <div class="text-end">
                        <div class="fw-semibold">{{ store.user?.name }}</div>
                        <div class="small text-white-50 text-uppercase">{{ store.user?.role }}</div>
                    </div>
                    <button class="btn btn-outline-light btn-sm" @click="logout">Logout</button>
                </div>
            </div>
        </nav>
        <div v-if="store.token && store.flash.message" class="container pt-4">
            <div class="alert" :class="store.flash.type === 'success' ? 'alert-success' : 'alert-danger'">
                {{ store.flash.message }}
            </div>
        </div>
        <router-view />
    </div>
</template>

<script setup>
import { RouterLink, useRouter } from 'vue-router';

import { clearAuth, flash, store } from '../services/store.js';

const router = useRouter();

const logout = () => {
    clearAuth();
    flash('success', 'You have been logged out.');
    router.push('/login');
};
</script>
