<template>
    <div class="container py-5">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
            <div>
                <div class="text-uppercase text-muted small fw-semibold">Exercise Library</div>
                <h1 class="display-6 fw-bold mb-1">Exercises from the backend API</h1>
                <p class="text-muted mb-0">Admins can create, edit, and delete exercises directly from this Vue screen.</p>
            </div>
            <div class="col-lg-4 px-0">
                <input v-model.trim="filter" class="form-control" placeholder="Search exercises" @input="applySearch">
            </div>
        </div>
        <div v-if="error" class="alert alert-danger">{{ error }}</div>
        <div v-if="canManage" class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h4 mb-0">{{ form.id ? 'Edit Exercise' : 'Create Exercise' }}</h2>
                    <button v-if="form.id" class="btn btn-sm btn-outline-secondary" @click="resetForm">Cancel Edit</button>
                </div>
                <form @submit.prevent="submitExercise">
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">Name</label><input v-model.trim="form.name" class="form-control" required></div>
                        <div class="col-md-4"><label class="form-label">Muscle Group</label><input v-model.trim="form.muscle_group" class="form-control" required></div>
                        <div class="col-md-4"><label class="form-label">Description</label><input v-model.trim="form.description" class="form-control"></div>
                        <div class="col-12"><button class="btn btn-dark" :disabled="saving">{{ saving ? 'Saving...' : submitLabel }}</button></div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h4 mb-0">All Exercises</h2>
                    <span class="badge text-bg-dark">{{ store.exerciseMeta.total }}</span>
                </div>
                <div v-if="loading" class="text-muted">Loading exercises...</div>
                <div v-else-if="store.exercises.length === 0" class="text-muted">No exercises found.</div>
                <div v-else class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>Name</th><th>Muscle Group</th><th>Description</th><th v-if="canManage" class="text-end">Actions</th></tr></thead>
                        <tbody>
                            <tr v-for="exercise in store.exercises" :key="exercise.id">
                                <td class="fw-semibold">{{ exercise.name }}</td>
                                <td>{{ exercise.muscle_group }}</td>
                                <td>{{ exercise.description || 'No description' }}</td>
                                <td v-if="canManage" class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button class="btn btn-sm btn-outline-dark" @click="editExercise(exercise)">Edit</button>
                                        <button class="btn btn-sm btn-outline-danger" @click="deleteExercise(exercise)">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-4">
                    <div class="text-muted small">
                        Page {{ store.exerciseMeta.page }} of {{ store.exerciseMeta.total_pages }}
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-secondary btn-sm" :disabled="store.exerciseMeta.page <= 1 || loading" @click="changePage(store.exerciseMeta.page - 1)">Previous</button>
                        <button class="btn btn-outline-secondary btn-sm" :disabled="store.exerciseMeta.page >= store.exerciseMeta.total_pages || loading" @click="changePage(store.exerciseMeta.page + 1)">Next</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';

import { apiFetch } from '../services/api.js';
import { loadExercises } from '../services/fitness.js';
import { clearFlash, flash, store } from '../services/store.js';
import { blankExercise, isAdmin } from '../services/helpers.js';

const loading = ref(true);
const saving = ref(false);
const error = ref('');
const filter = ref('');
const form = reactive(blankExercise());
let searchDebounceId = null;

const canManage = computed(() => isAdmin(store.user));
const submitLabel = computed(() => (form.id ? 'Update Exercise' : 'Create Exercise'));

const resetForm = () => {
    Object.assign(form, blankExercise());
};

const fetchExercises = async (page = 1) => {
    loading.value = true;
    error.value = '';

    try {
        await loadExercises({
            search: filter.value,
            page,
            per_page: store.exerciseMeta.per_page
        });
    } catch (caughtError) {
        error.value = caughtError.message;
    } finally {
        loading.value = false;
    }
};

const applySearch = () => {
    if (searchDebounceId) {
        clearTimeout(searchDebounceId);
    }

    searchDebounceId = window.setTimeout(() => {
        fetchExercises(1);
    }, 250);
};

const changePage = async (page) => {
    await fetchExercises(page);
};

const editExercise = (exercise) => {
    Object.assign(form, exercise);
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const submitExercise = async () => {
    error.value = '';
    saving.value = true;

    try {
        if (form.id) {
            await apiFetch(`/api/exercises/${form.id}`, { method: 'PUT', body: JSON.stringify(form) });
            flash('success', 'Exercise updated successfully.');
        } else {
            await apiFetch('/api/exercises', { method: 'POST', body: JSON.stringify(form) });
            flash('success', 'Exercise created successfully.');
        }

        resetForm();
        await fetchExercises();
    } catch (caughtError) {
        error.value = caughtError.message;
    } finally {
        saving.value = false;
    }
};

const deleteExercise = async (exercise) => {
    error.value = '';

    if (!window.confirm(`Delete "${exercise.name}"?`)) {
        return;
    }

    try {
        await apiFetch(`/api/exercises/${exercise.id}`, { method: 'DELETE' });
        flash('success', 'Exercise deleted successfully.');
        await fetchExercises();
    } catch (caughtError) {
        error.value = caughtError.message;
    }
};

onMounted(async () => {
    clearFlash();
    filter.value = store.exerciseMeta.search || '';
    await fetchExercises(store.exerciseMeta.page);
});
</script>
