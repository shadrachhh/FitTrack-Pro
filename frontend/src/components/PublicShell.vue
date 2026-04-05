<template>
    <div class="auth-shell min-vh-100 d-flex align-items-center">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-xl-11">
                    <div class="row g-0 bg-white rounded-4 overflow-hidden shadow-lg auth-panel">
                        <div class="col-lg-5 auth-showcase text-white p-4 p-lg-5">
                            <div class="text-uppercase small fw-semibold text-warning mb-3">FitTrack Pro</div>
                            <h1 class="display-6 fw-bold mb-3">A Vue frontend that is already connected to your PHP API.</h1>
                            <p class="text-white-50 mb-4">Sign in once and manage workouts, exercises, and role-based actions from the SPA.</p>
                            <div class="d-flex flex-wrap gap-2 mb-4">
                                <span class="badge rounded-pill text-bg-light">JWT auth</span>
                                <span class="badge rounded-pill text-bg-light">Workout logging</span>
                                <span class="badge rounded-pill text-bg-light">Exercise admin</span>
                            </div>
                            <a class="link-light" href="/login">Open server-rendered app</a>
                        </div>
                        <div class="col-lg-7 p-4 p-lg-5">
                            <router-view />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
</script>
