const { createApp, reactive } = Vue;
const { createRouter, createWebHashHistory } = VueRouter;

const store = reactive({
    token: localStorage.getItem('fittrack_token') || '',
    user: JSON.parse(localStorage.getItem('fittrack_user') || 'null'),
    exercises: [],
    workouts: [],
    dashboard: { totalWorkouts: 0, totalEntries: 0, totalVolume: 0, recentWorkouts: [] },
    flash: { type: '', message: '' }
});

const blankExercise = () => ({ id: null, name: '', muscle_group: '', description: '' });
const blankEntry = () => ({ exercise_id: '', sets: 3, reps: 10, weight: 0 });
const isAdmin = () => (store.user?.role || 'user') === 'admin';
const formatDate = (value) => {
    if (!value) {
        return 'No date';
    }

    const parsed = new Date(`${value}T00:00:00`);
    return Number.isNaN(parsed.getTime())
        ? value
        : parsed.toLocaleDateString(undefined, { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' });
};
const formatWeight = (value) => `${Number(value || 0).toFixed(Number(value || 0) % 1 === 0 ? 0 : 2)} kg`;
const calculateWorkoutVolume = (workout) => workout.entries.reduce(
    (sum, entry) => sum + (Number(entry.sets) * Number(entry.reps) * Number(entry.weight || 0)),
    0
);
let flashTimeoutId = null;
const flash = (type, message) => {
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
const clearFlash = () => {
    if (flashTimeoutId) {
        clearTimeout(flashTimeoutId);
        flashTimeoutId = null;
    }

    store.flash.type = '';
    store.flash.message = '';
};

const setAuth = (token, user) => {
    store.token = token;
    store.user = user;
    localStorage.setItem('fittrack_token', token);
    localStorage.setItem('fittrack_user', JSON.stringify(user));
};

const clearAuth = () => {
    store.token = '';
    store.user = null;
    store.exercises = [];
    store.workouts = [];
    store.dashboard = { totalWorkouts: 0, totalEntries: 0, totalVolume: 0, recentWorkouts: [] };
    localStorage.removeItem('fittrack_token');
    localStorage.removeItem('fittrack_user');
};

const apiFetch = async (path, options = {}) => {
    const headers = {
        ...(options.body !== undefined ? { 'Content-Type': 'application/json' } : {}),
        ...(options.headers || {})
    };

    if (store.token) {
        headers.Authorization = `Bearer ${store.token}`;
    }

    const response = await fetch(path, { ...options, headers });
    const data = await response.json().catch(() => ({}));

    if (response.status === 401) {
        clearAuth();
    }

    if (!response.ok) {
        throw new Error(data.message || 'Request failed.');
    }

    return data;
};

const loadExercises = async () => {
    const response = await apiFetch('/api/exercises');
    store.exercises = response.data || [];
    return store.exercises;
};

const loadWorkouts = async (filters = {}) => {
    const params = new URLSearchParams();

    if (filters.workout_date) {
        params.set('workout_date', filters.workout_date);
    }

    if (filters.exercise_id) {
        params.set('exercise_id', filters.exercise_id);
    }

    const response = await apiFetch(`/api/workouts${params.toString() ? `?${params.toString()}` : ''}`);
    store.workouts = response.data || [];
    return store.workouts;
};

const refreshDashboard = async () => {
    const workouts = await loadWorkouts();
    store.dashboard.totalWorkouts = workouts.length;
    store.dashboard.totalEntries = workouts.reduce((sum, workout) => sum + workout.entries.length, 0);
    store.dashboard.totalVolume = workouts.reduce((sum, workout) => sum + calculateWorkoutVolume(workout), 0);
    store.dashboard.recentWorkouts = workouts.slice(0, 3);
};

const PublicShell = {
    template: `
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
                                <router-view></router-view>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `
};

const AppLayout = {
    methods: {
        logout() {
            clearAuth();
            flash('success', 'You have been logged out.');
            this.$router.push('/login');
        }
    },
    template: `
        <div class="app-shell min-vh-100">
            <nav v-if="store.token" class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
                <div class="container">
                    <router-link class="navbar-brand fw-semibold" to="/dashboard">FitTrack Pro</router-link>
                    <div class="navbar-nav me-auto">
                        <router-link class="nav-link" to="/dashboard">Dashboard</router-link>
                        <router-link class="nav-link" to="/workouts">Workouts</router-link>
                        <router-link class="nav-link" to="/workouts/create">Log Workout</router-link>
                        <router-link class="nav-link" to="/exercises">Exercises</router-link>
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
            <router-view></router-view>
        </div>
    `,
    setup() {
        return { store };
    }
};

const LoginView = {
    data() {
        return { email: '', password: '', loading: false, error: '' };
    },
    methods: {
        async submit() {
            this.error = '';
            clearFlash();
            this.loading = true;

            try {
                const response = await apiFetch('/api/login', {
                    method: 'POST',
                    body: JSON.stringify({ email: this.email, password: this.password })
                });

                setAuth(response.token, response.user);
                flash('success', `Welcome back, ${response.user.name}.`);
                this.$router.push('/dashboard');
            } catch (error) {
                this.error = error.message;
            } finally {
                this.loading = false;
            }
        }
    },
    template: `
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
            <p class="mt-4 mb-0 text-muted">Need an account? <router-link to="/register">Register here</router-link>.</p>
        </div>
    `
};

const RegisterView = {
    data() {
        return { form: { name: '', email: '', password: '' }, loading: false, error: '', success: '' };
    },
    methods: {
        async submit() {
            this.error = '';
            this.success = '';
            this.loading = true;

            try {
                const response = await apiFetch('/api/register', {
                    method: 'POST',
                    body: JSON.stringify(this.form)
                });

                this.success = response.message || 'Account created successfully.';
                this.form = { name: '', email: '', password: '' };
            } catch (error) {
                this.error = error.message;
            } finally {
                this.loading = false;
            }
        }
    },
    template: `
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
            <p class="mt-4 mb-0 text-muted">Already registered? <router-link to="/login">Go to login</router-link>.</p>
        </div>
    `
};

const DashboardView = {
    data() {
        return { loading: true, error: '' };
    },
    computed: {
        averageVolume() {
            if (store.dashboard.totalWorkouts === 0) {
                return 0;
            }

            return Math.round(store.dashboard.totalVolume / store.dashboard.totalWorkouts);
        }
    },
    async mounted() {
        clearFlash();

        try {
            await refreshDashboard();
            await loadExercises();
        } catch (error) {
            this.error = error.message;
        } finally {
            this.loading = false;
        }
    },
    methods: {
        formatDate,
        formatWeight,
        calculateWorkoutVolume
    },
    template: `
        <div class="container py-5">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
                <div>
                    <div class="text-uppercase text-muted small fw-semibold">Overview</div>
                    <h1 class="display-6 fw-bold mb-1">Welcome, {{ store.user?.name }}</h1>
                    <p class="text-muted mb-0">This frontend reads from the same backend API as the PHP application.</p>
                </div>
                <div class="d-flex gap-2">
                    <router-link class="btn btn-dark" to="/workouts/create">Log New Workout</router-link>
                    <router-link class="btn btn-outline-secondary" to="/exercises">Browse Exercises</router-link>
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
                        <router-link class="btn btn-sm btn-outline-dark" to="/workouts">View All</router-link>
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
    `,
    setup() {
        return { store };
    }
};

const ExercisesView = {
    data() {
        return { loading: true, saving: false, error: '', filter: '', form: blankExercise() };
    },
    computed: {
        canManage() {
            return isAdmin();
        },
        submitLabel() {
            return this.form.id ? 'Update Exercise' : 'Create Exercise';
        },
        filteredExercises() {
            const query = this.filter.trim().toLowerCase();

            if (query === '') {
                return store.exercises;
            }

            return store.exercises.filter((exercise) =>
                `${exercise.name} ${exercise.muscle_group} ${exercise.description || ''}`.toLowerCase().includes(query)
            );
        }
    },
    async mounted() {
        clearFlash();
        await this.fetchExercises();
    },
    methods: {
        resetForm() {
            this.form = blankExercise();
        },
        async fetchExercises() {
            this.loading = true;
            this.error = '';

            try {
                await loadExercises();
            } catch (error) {
                this.error = error.message;
            } finally {
                this.loading = false;
            }
        },
        editExercise(exercise) {
            this.form = { ...exercise };
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        async submitExercise() {
            this.error = '';
            this.saving = true;

            try {
                if (this.form.id) {
                    await apiFetch(`/api/exercises/${this.form.id}`, { method: 'PUT', body: JSON.stringify(this.form) });
                    flash('success', 'Exercise updated successfully.');
                } else {
                    await apiFetch('/api/exercises', { method: 'POST', body: JSON.stringify(this.form) });
                    flash('success', 'Exercise created successfully.');
                }

                this.resetForm();
                await this.fetchExercises();
            } catch (error) {
                this.error = error.message;
            } finally {
                this.saving = false;
            }
        },
        async deleteExercise(exercise) {
            this.error = '';

            if (!window.confirm(`Delete "${exercise.name}"?`)) {
                return;
            }

            try {
                await apiFetch(`/api/exercises/${exercise.id}`, { method: 'DELETE' });
                flash('success', 'Exercise deleted successfully.');
                await this.fetchExercises();
            } catch (error) {
                this.error = error.message;
            }
        }
    },
    template: `
        <div class="container py-5">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
                <div>
                    <div class="text-uppercase text-muted small fw-semibold">Exercise Library</div>
                    <h1 class="display-6 fw-bold mb-1">Exercises from the backend API</h1>
                    <p class="text-muted mb-0">Admins can create, edit, and delete exercises directly from this Vue screen.</p>
                </div>
                <div class="col-lg-4 px-0">
                    <input v-model.trim="filter" class="form-control" placeholder="Search exercises">
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
                        <span class="badge text-bg-dark">{{ filteredExercises.length }}</span>
                    </div>
                    <div v-if="loading" class="text-muted">Loading exercises...</div>
                    <div v-else-if="filteredExercises.length === 0" class="text-muted">No exercises found.</div>
                    <div v-else class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead><tr><th>Name</th><th>Muscle Group</th><th>Description</th><th v-if="canManage" class="text-end">Actions</th></tr></thead>
                            <tbody>
                                <tr v-for="exercise in filteredExercises" :key="exercise.id">
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
                </div>
            </div>
        </div>
    `,
    setup() {
        return { store };
    }
};

const WorkoutCreateView = {
    data() {
        return {
            loadingExercises: true,
            saving: false,
            error: '',
            form: { workout_date: new Date().toISOString().slice(0, 10), entries: [blankEntry()] }
        };
    },
    async mounted() {
        clearFlash();

        try {
            await loadExercises();
        } catch (error) {
            this.error = error.message;
        } finally {
            this.loadingExercises = false;
        }
    },
    methods: {
        addRow() {
            this.form.entries.push(blankEntry());
        },
        removeRow(index) {
            if (this.form.entries.length === 1) {
                this.form.entries = [blankEntry()];
                return;
            }

            this.form.entries.splice(index, 1);
        },
        async submitWorkout() {
            this.error = '';
            this.saving = true;

            try {
                await apiFetch('/api/workouts', {
                    method: 'POST',
                    body: JSON.stringify({
                        workout_date: this.form.workout_date,
                        exercise_id: this.form.entries.map((entry) => entry.exercise_id),
                        sets: this.form.entries.map((entry) => entry.sets),
                        reps: this.form.entries.map((entry) => entry.reps),
                        weight: this.form.entries.map((entry) => entry.weight)
                    })
                });

                await refreshDashboard();
                flash('success', 'Workout created successfully.');
                this.$router.push('/workouts');
            } catch (error) {
                this.error = error.message;
            } finally {
                this.saving = false;
            }
        }
    },
    template: `
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
    `,
    setup() {
        return { store };
    }
};

const WorkoutsView = {
    data() {
        return {
            loading: true,
            loadingExercises: true,
            error: '',
            filters: { workout_date: '', exercise_id: '' }
        };
    },
    async mounted() {
        clearFlash();

        try {
            await loadExercises();
        } catch (error) {
            this.error = error.message;
        } finally {
            this.loadingExercises = false;
        }

        await this.fetchWorkouts();
    },
    methods: {
        formatDate,
        formatWeight,
        calculateWorkoutVolume,
        async fetchWorkouts() {
            this.loading = true;
            this.error = '';

            try {
                await loadWorkouts(this.filters);
            } catch (error) {
                this.error = error.message;
            } finally {
                this.loading = false;
            }
        },
        async resetFilters() {
            this.filters = { workout_date: '', exercise_id: '' };
            await this.fetchWorkouts();
        }
    },
    template: `
        <div class="container py-5">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
                <div>
                    <div class="text-uppercase text-muted small fw-semibold">Workout History</div>
                    <h1 class="display-6 fw-bold mb-1">Workouts from the API</h1>
                    <p class="text-muted mb-0">Filter your workout history by date or exercise.</p>
                </div>
                <router-link class="btn btn-dark" to="/workouts/create">Log New Workout</router-link>
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
            </div>
        </div>
    `,
    setup() {
        return { store };
    }
};

const routes = [
    {
        path: '/',
        component: AppLayout,
        children: [
            { path: '', redirect: '/dashboard' },
            { path: 'dashboard', component: DashboardView, meta: { requiresAuth: true } },
            { path: 'exercises', component: ExercisesView, meta: { requiresAuth: true } },
            { path: 'workouts', component: WorkoutsView, meta: { requiresAuth: true } },
            { path: 'workouts/create', component: WorkoutCreateView, meta: { requiresAuth: true } }
        ]
    },
    {
        path: '/',
        component: PublicShell,
        children: [
            { path: 'login', component: LoginView, meta: { guestOnly: true } },
            { path: 'register', component: RegisterView, meta: { guestOnly: true } }
        ]
    }
];

const router = createRouter({
    history: createWebHashHistory(),
    routes
});

router.beforeEach((to, from, next) => {
    if (to.meta.requiresAuth && !store.token) {
        clearFlash();
        next('/login');
        return;
    }

    if (to.meta.guestOnly && store.token) {
        next('/dashboard');
        return;
    }

    next();
});

createApp({ template: '<router-view></router-view>' }).use(router).mount('#app');
