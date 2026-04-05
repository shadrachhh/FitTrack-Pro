<template>
    <div class="container py-5">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
            <div>
                <div class="text-uppercase text-muted small fw-semibold">Overview</div>
                <h1 class="display-6 fw-bold mb-1">Welcome, {{ store.user?.name }}</h1>
                <p class="text-muted mb-0">This frontend reads from the same backend API as the PHP application.</p>
            </div>
            <div class="d-flex gap-2">
                <RouterLink class="btn btn-dark" to="/workouts/create">Log New Workout</RouterLink>
                <RouterLink class="btn btn-outline-secondary" to="/exercises">Browse Exercises</RouterLink>
            </div>
        </div>
        <div v-if="error" class="alert alert-danger">{{ error }}</div>
        <div class="row g-4 mb-4">
            <div class="col-md-4"><div class="card border-0 shadow-sm h-100"><div class="card-body p-4"><div class="text-muted small text-uppercase fw-semibold mb-2">Total Workouts</div><div class="display-5 fw-bold">{{ store.dashboard.totalWorkouts }}</div></div></div></div>
            <div class="col-md-4"><div class="card border-0 shadow-sm h-100"><div class="card-body p-4"><div class="text-muted small text-uppercase fw-semibold mb-2">Logged Entries</div><div class="display-5 fw-bold">{{ store.dashboard.totalEntries }}</div></div></div></div>
            <div class="col-md-4"><div class="card border-0 shadow-sm h-100"><div class="card-body p-4"><div class="text-muted small text-uppercase fw-semibold mb-2">Average Volume</div><div class="display-6 fw-bold">{{ averageVolume }} kg</div></div></div></div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h4 mb-0">Recent Workouts</h2>
                    <RouterLink class="btn btn-sm btn-outline-dark" to="/workouts">View All</RouterLink>
                </div>
                <div v-if="loading" class="text-muted">Loading dashboard data...</div>
                <div v-else-if="store.dashboard.recentWorkouts.length === 0" class="text-muted">No workouts yet. Use the workout form to create one.</div>
                <div v-else>
                    <div v-for="workout in store.dashboard.recentWorkouts" :key="workout.id" class="border rounded-3 p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center gap-3 mb-2">
                            <div class="fw-semibold">Workout Date: {{ formatDate(workout.workout_date) }}</div>
                            <div class="badge text-bg-dark">{{ Math.round(calculateWorkoutVolume(workout)) }} kg volume</div>
                        </div>
                        <ul class="mb-0">
                            <li v-for="entry in workout.entries" :key="entry.id">{{ entry.exercise_name }}: {{ entry.sets }} sets x {{ entry.reps }} reps @ {{ formatWeight(entry.weight) }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';

import { loadExercises, refreshDashboard } from '../services/fitness.js';
import { clearFlash, store } from '../services/store.js';
import { calculateWorkoutVolume, formatDate, formatWeight } from '../services/helpers.js';

const loading = ref(true);
const error = ref('');

const averageVolume = computed(() => {
    if (store.dashboard.totalWorkouts === 0) {
        return 0;
    }

    return Math.round(store.dashboard.totalVolume / store.dashboard.totalWorkouts);
});

onMounted(async () => {
    clearFlash();

    try {
        await refreshDashboard();
        await loadExercises();
    } catch (caughtError) {
        error.value = caughtError.message;
    } finally {
        loading.value = false;
    }
});
</script>
