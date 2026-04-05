import { reactive } from 'vue';

export const store = reactive({
    token: localStorage.getItem('fittrack_token') || '',
    user: JSON.parse(localStorage.getItem('fittrack_user') || 'null'),
    exercises: [],
    workouts: [],
    exerciseMeta: { page: 1, per_page: 10, total: 0, total_pages: 1, search: '' },
    workoutMeta: { page: 1, per_page: 5, total: 0, total_pages: 1, workout_date: '', exercise_id: '' },
    dashboard: { totalWorkouts: 0, totalEntries: 0, totalVolume: 0, recentWorkouts: [] },
    flash: { type: '', message: '' }
});

let flashTimeoutId = null;

export const setAuth = (token, user) => {
    store.token = token;
    store.user = user;
    localStorage.setItem('fittrack_token', token);
    localStorage.setItem('fittrack_user', JSON.stringify(user));
};

export const clearAuth = () => {
    store.token = '';
    store.user = null;
    store.exercises = [];
    store.workouts = [];
    store.exerciseMeta = { page: 1, per_page: 10, total: 0, total_pages: 1, search: '' };
    store.workoutMeta = { page: 1, per_page: 5, total: 0, total_pages: 1, workout_date: '', exercise_id: '' };
    store.dashboard = { totalWorkouts: 0, totalEntries: 0, totalVolume: 0, recentWorkouts: [] };
    localStorage.removeItem('fittrack_token');
    localStorage.removeItem('fittrack_user');
};

export const flash = (type, message) => {
    if (flashTimeoutId) {
        clearTimeout(flashTimeoutId);
    }

    store.flash.type = type;
    store.flash.message = message;
    flashTimeoutId = window.setTimeout(() => {
        store.flash.type = '';
        store.flash.message = '';
        flashTimeoutId = null;
    }, 4000);
};

export const clearFlash = () => {
    if (flashTimeoutId) {
        clearTimeout(flashTimeoutId);
        flashTimeoutId = null;
    }

    store.flash.type = '';
    store.flash.message = '';
};
