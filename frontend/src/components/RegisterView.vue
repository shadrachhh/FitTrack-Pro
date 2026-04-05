<template>
    <div>
        <h2 class="display-6 fw-bold mb-2">Create account</h2>
        <p class="text-muted mb-4">This form talks directly to the PHP API, so new users work in both interfaces.</p>
        <div v-if="error" class="alert alert-danger">{{ error }}</div>
        <div v-if="success" class="alert alert-success">{{ success }}</div>
        <form @submit.prevent="submit">
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input v-model.trim="form.name" class="form-control form-control-lg" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input v-model.trim="form.email" type="email" class="form-control form-control-lg" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input v-model="form.password" type="password" class="form-control form-control-lg" required>
            </div>
            <button class="btn btn-dark w-100 py-2" :disabled="loading">{{ loading ? 'Creating account...' : 'Register' }}</button>
        </form>
        <p class="mt-4 mb-0 text-muted">Already registered? <RouterLink to="/login">Go to login</RouterLink>.</p>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { RouterLink } from 'vue-router';

import { apiFetch } from '../services/api.js';

const form = reactive({ name: '', email: '', password: '' });
const loading = ref(false);
const error = ref('');
const success = ref('');

const submit = async () => {
    error.value = '';
    success.value = '';
    loading.value = true;

    try {
        const response = await apiFetch('/api/register', {
            method: 'POST',
            body: JSON.stringify(form)
        });

        success.value = response.message || 'Account created successfully.';
        form.name = '';
        form.email = '';
        form.password = '';
    } catch (caughtError) {
        error.value = caughtError.message;
    } finally {
        loading.value = false;
    }
};
</script>
