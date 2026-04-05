<template>
    <div class="container py-5">
        <div class="mb-4">
            <div class="text-uppercase text-muted small fw-semibold">Workout Logger</div>
            <h1 class="display-6 fw-bold mb-1">Create a workout in Vue</h1>
            <p class="text-muted mb-0">This form sends a POST request to the backend workout endpoint.</p>
        </div>
        <div v-if="error" class="alert alert-danger">{{ error }}</div>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div v-if="loadingExercises" class="text-muted">Loading exercise options...</div>
                <form v-else @submit.prevent="submitWorkout">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Workout Date</label>
                            <input v-model="form.workout_date" type="date" class="form-control" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h4 mb-0">Entries</h2>
                        <button type="button" class="btn btn-outline-dark btn-sm" @click="addRow">Add Exercise Row</button>
                    </div>
                    <div v-for="(entry, index) in form.entries" :key="index" class="border rounded-3 p-3 mb-3">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">Exercise</label>
                                <select v-model="entry.exercise_id" class="form-select" required>
                                    <option value="">Select exercise</option>
                                    <option v-for="exercise in store.exercises" :key="exercise.id" :value="exercise.id">{{ exercise.name }}</option>
                                </select>
                            </div>
                            <div class="col-md-2"><label class="form-label">Sets</label><input v-model.number="entry.sets" type="number" min="1" class="form-control" required></div>
                            <div class="col-md-2"><label class="form-label">Reps</label><input v-model.number="entry.reps" type="number" min="1" class="form-control" required></div>
                            <div class="col-md-2"><label class="form-label">Weight</label><input v-model.number="entry.weight" type="number" min="0" step="0.01" class="form-control"></div>
                            <div class="col-md-2"><button type="button" class="btn btn-outline-danger w-100" @click="removeRow(index)">Remove</button></div>
                        </div>
                    </div>
                    <button class="btn btn-dark" :disabled="saving">{{ saving ? 'Saving workout...' : 'Save Workout' }}</button>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';

import { apiFetch } from '../services/api.js';
import { loadExercises, refreshDashboard } from '../services/fitness.js';
import { clearFlash, flash, store } from '../services/store.js';
import { blankEntry } from '../services/helpers.js';

const router = useRouter();
const loadingExercises = ref(true);
const saving = ref(false);
const error = ref('');
const form = reactive({
    workout_date: new Date().toISOString().slice(0, 10),
    entries: [blankEntry()]
});

const addRow = () => {
    form.entries.push(blankEntry());
};

const removeRow = (index) => {
    if (form.entries.length === 1) {
        form.entries = [blankEntry()];
        return;
    }

    form.entries.splice(index, 1);
};

const submitWorkout = async () => {
    error.value = '';
    saving.value = true;

    try {
        await apiFetch('/api/workouts', {
            method: 'POST',
            body: JSON.stringify({
                workout_date: form.workout_date,
                exercise_id: form.entries.map((entry) => entry.exercise_id),
                sets: form.entries.map((entry) => entry.sets),
                reps: form.entries.map((entry) => entry.reps),
                weight: form.entries.map((entry) => entry.weight)
            })
        });

        await refreshDashboard();
        flash('success', 'Workout created successfully.');
        router.push('/workouts');
    } catch (caughtError) {
        error.value = caughtError.message;
    } finally {
        saving.value = false;
    }
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
});
</script>
