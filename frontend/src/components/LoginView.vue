<template>
    <div>
        <h2 class="display-6 fw-bold mb-2">Sign in</h2>
        <p class="text-muted mb-4">Use your backend account to access the connected Vue frontend.</p>
        <div v-if="error" class="alert alert-danger">{{ error }}</div>
        <form @submit.prevent="submit">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input v-model.trim="email" type="email" class="form-control form-control-lg" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input v-model="password" type="password" class="form-control form-control-lg" required>
            </div>
            <button class="btn btn-dark w-100 py-2" :disabled="loading">{{ loading ? 'Signing in...' : 'Login' }}</button>
        </form>
        <p class="mt-4 mb-0 text-muted">Need an account? <RouterLink to="/register">Register here</RouterLink>.</p>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';

import { apiFetch } from '../services/api.js';
import { clearFlash, flash, setAuth } from '../services/store.js';

const router = useRouter();
const email = ref('');
const password = ref('');
const loading = ref(false);
const error = ref('');

const submit = async () => {
    error.value = '';
    clearFlash();
    loading.value = true;

    try {
        const response = await apiFetch('/api/login', {
            method: 'POST',
            body: JSON.stringify({ email: email.value, password: password.value })
        });

        setAuth(response.token, response.user);
        flash('success', `Welcome back, ${response.user.name}.`);
        router.push('/dashboard');
    } catch (caughtError) {
        error.value = caughtError.message;
    } finally {
        loading.value = false;
    }
};
</script>
