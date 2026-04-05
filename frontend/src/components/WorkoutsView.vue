<template>
    <div class="container py-5">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
            <div>
                <div class="text-uppercase text-muted small fw-semibold">Workout History</div>
                <h1 class="display-6 fw-bold mb-1">Workouts from the API</h1>
                <p class="text-muted mb-0">Filter your workout history by date or exercise.</p>
            </div>
            <RouterLink class="btn btn-dark" to="/workouts/create">Log New Workout</RouterLink>
        </div>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4"><label class="form-label">Workout Date</label><input v-model="filters.workout_date" type="date" class="form-control"></div>
                    <div class="col-md-4">
                        <label class="form-label">Exercise</label>
                        <select v-model="filters.exercise_id" class="form-select" :disabled="loadingExercises">
                            <option value="">All exercises</option>
                            <option v-for="exercise in store.exercises" :key="exercise.id" :value="String(exercise.id)">{{ exercise.name }}</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button class="btn btn-dark" @click="fetchWorkouts">Apply Filters</button>
                        <button class="btn btn-outline-secondary" @click="resetFilters">Reset</button>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="error" class="alert alert-danger">{{ error }}</div>
        <div v-if="loading" class="text-muted">Loading workouts...</div>
        <div v-else-if="store.workouts.length === 0" class="card border-0 shadow-sm"><div class="card-body p-4 text-center">No workouts found.</div></div>
        <div v-else>
            <div v-for="workout in store.workouts" :key="workout.id" class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div><div class="text-uppercase text-muted small fw-semibold">Workout</div><div class="h4 mb-0">{{ formatDate(workout.workout_date) }}</div></div>
                        <div class="text-end">
                            <div class="text-muted">{{ workout.entries.length }} exercises</div>
                            <div class="small fw-semibold">{{ Math.round(calculateWorkoutVolume(workout)) }} kg volume</div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead><tr><th>Exercise</th><th>Sets</th><th>Reps</th><th>Weight</th></tr></thead>
                            <tbody>
                                <tr v-for="entry in workout.entries" :key="entry.id">
                                    <td class="fw-semibold">{{ entry.exercise_name }}</td>
                                    <td>{{ entry.sets }}</td>
                                    <td>{{ entry.reps }}</td>
                                    <td>{{ formatWeight(entry.weight) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="text-muted small">
                    Page {{ store.workoutMeta.page }} of {{ store.workoutMeta.total_pages }}.
                    {{ store.workoutMeta.total }} workout(s) found.
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm" :disabled="store.workoutMeta.page <= 1 || loading" @click="changePage(store.workoutMeta.page - 1)">Previous</button>
                    <button class="btn btn-outline-secondary btn-sm" :disabled="store.workoutMeta.page >= store.workoutMeta.total_pages || loading" @click="changePage(store.workoutMeta.page + 1)">Next</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { RouterLink } from 'vue-router';

import { loadExercises, loadWorkouts } from '../services/fitness.js';
import { clearFlash, store } from '../services/store.js';
import { calculateWorkoutVolume, formatDate, formatWeight } from '../services/helpers.js';

const loading = ref(true);
const loadingExercises = ref(true);
const error = ref('');
const filters = reactive({ workout_date: '', exercise_id: '' });

const fetchWorkouts = async (page = 1) => {
    loading.value = true;
    error.value = '';

    try {
        await loadWorkouts({
            ...filters,
            page,
            per_page: store.workoutMeta.per_page
        });
    } catch (caughtError) {
        error.value = caughtError.message;
    } finally {
        loading.value = false;
    }
};

const resetFilters = async () => {
    filters.workout_date = '';
    filters.exercise_id = '';
    await fetchWorkouts(1);
};

const changePage = async (page) => {
    await fetchWorkouts(page);
};

onMounted(async () => {
    clearFlash();

    try {
        await loadExercises();
    } catch (caughtError) {
        error.value = caughtError.message;
    } finally {
        loadingExercises.value = false;
    }

    filters.workout_date = store.workoutMeta.workout_date || '';
    filters.exercise_id = store.workoutMeta.exercise_id || '';
    await fetchWorkouts(store.workoutMeta.page);
});
</script>
