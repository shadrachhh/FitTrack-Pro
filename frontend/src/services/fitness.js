import { apiFetch } from './api.js';
import { store } from './store.js';
import { calculateWorkoutVolume } from './helpers.js';

export const loadExercises = async (filters = {}) => {
    const params = new URLSearchParams();

    if (filters.search) {
        params.set('search', filters.search);
    }

    if (filters.page) {
        params.set('page', filters.page);
    }

    if (filters.per_page) {
        params.set('per_page', filters.per_page);
    }

    const response = await apiFetch(`/api/exercises${params.toString() ? `?${params.toString()}` : ''}`);
    store.exercises = response.data || [];
    store.exerciseMeta = response.meta || store.exerciseMeta;
    return store.exercises;
};

export const loadWorkouts = async (filters = {}) => {
    const params = new URLSearchParams();

    if (filters.workout_date) {
        params.set('workout_date', filters.workout_date);
    }

    if (filters.exercise_id) {
        params.set('exercise_id', filters.exercise_id);
    }

    if (filters.page) {
        params.set('page', filters.page);
    }

    if (filters.per_page) {
        params.set('per_page', filters.per_page);
    }

    const response = await apiFetch(`/api/workouts${params.toString() ? `?${params.toString()}` : ''}`);
    store.workouts = response.data || [];
    store.workoutMeta = response.meta || store.workoutMeta;
    return store.workouts;
};

export const refreshDashboard = async () => {
    const workouts = await loadWorkouts({ page: 1, per_page: 50 });
    store.dashboard.totalWorkouts = store.workoutMeta.total;
    store.dashboard.totalEntries = workouts.reduce((sum, workout) => sum + workout.entries.length, 0);
    store.dashboard.totalVolume = workouts.reduce((sum, workout) => sum + calculateWorkoutVolume(workout), 0);
    store.dashboard.recentWorkouts = workouts.slice(0, 3);
};
