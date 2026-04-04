const { createApp, reactive, computed } = Vue;
const { createRouter, createWebHashHistory } = VueRouter;

const store = reactive({
    token: localStorage.getItem('fittrack_token') || '',
    user: JSON.parse(localStorage.getItem('fittrack_user') || 'null'),
    exercises: [],
    workouts: [],
    error: '',
    success: ''
});

const apiFetch = async (path, options = {}) => {
    const headers = {
        'Content-Type': 'application/json',
        ...(options.headers || {})
    };

    if (store.token) {
        headers.Authorization = `Bearer ${store.token}`;
    }

    const response = await fetch(path, {
        ...options,
        headers
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
        throw new Error(data.message || 'Request failed.');
    }

    return data;
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
    localStorage.removeItem('fittrack_token');
    localStorage.removeItem('fittrack_user');
};

const AppLayout = {
    computed: {
        isLoggedIn() {
            return !!store.token;
        },
        isAdmin() {
            return (store.user?.role || 'user') === 'admin';
        },
        roleLabel() {
            return this.isAdmin ? 'Manage Exercises' : 'Exercises';
        }
    },
    methods: {
        logout() {
            clearAuth();
            this.$router.push('/login');
        }
    },
    template: `
        <div>
            <nav class="navbar navbar-expand-lg navbar-dark bg-dark" v-if="isLoggedIn">
                <div class="container">
                    <a class="navbar-brand" href="#/dashboard">FitTrack Pro</a>
                    <div class="navbar-nav me-auto">
                        <router-link class="nav-link" to="/dashboard">Dashboard</router-link>
                        <router-link class="nav-link" to="/workouts">Workouts</router-link>
                        <router-link class="nav-link" to="/exercises">{{ roleLabel }}</router-link>
                    </div>
                    <span class="navbar-text text-white me-3">{{ store.user?.name }}</span>
                    <button class="btn btn-outline-light btn-sm" @click="logout">Logout</button>
                </div>
            </nav>
            <router-view></router-view>
        </div>
    `,
    setup() {
        return { store };
    }
};

const LoginView = {
    data() {
        return {
            email: '',
            password: '',
            loading: false,
            error: ''
        };
    },
    methods: {
        async submit() {
            this.error = '';
            this.loading = true;

            try {
                const response = await apiFetch('/api/login', {
                    method: 'POST',
                    body: JSON.stringify({
                        email: this.email,
                        password: this.password
                    })
                });

                setAuth(response.token, response.user);
                this.$router.push('/dashboard');
            } catch (error) {
                this.error = error.message;
            } finally {
                this.loading = false;
            }
        }
    },
    template: `
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h1 class="h3 text-center mb-4">SPA Login</h1>
                            <div v-if="error" class="alert alert-danger">{{ error }}</div>
                            <form @submit.prevent="submit">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input v-model="email" type="email" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <input v-model="password" type="password" class="form-control" required>
                                </div>
                                <button class="btn btn-primary w-100" :disabled="loading">
                                    {{ loading ? 'Signing in...' : 'Login' }}
                                </button>
                            </form>
                            <p class="text-center mt-3 mb-0">
                                <a href="/login">Open server-rendered version</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `
};

const DashboardView = {
    data() {
        return {
            workouts: [],
            loading: true,
            error: ''
        };
    },
    async mounted() {
        try {
            const response = await apiFetch('/api/workouts');
            this.workouts = response.data.slice(0, 3);
        } catch (error) {
            this.error = error.message;
        } finally {
            this.loading = false;
        }
    },
    computed: {
        totalWorkouts() {
            return store.workouts.length || this.workouts.length;
        }
    },
    template: `
        <div class="container py-5">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body p-4">
                            <h2 class="h6 text-muted">Total Workouts</h2>
                            <p class="display-6 mb-0">{{ totalWorkouts }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body p-4">
                            <h2 class="h6 text-muted">Quick Actions</h2>
                            <div class="d-grid gap-2">
                                <router-link class="btn btn-primary" to="/workouts">View Workouts</router-link>
                                <router-link class="btn btn-outline-secondary" to="/exercises">View Exercises</router-link>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body p-4">
                            <h2 class="h6 text-muted">Role</h2>
                            <p class="fs-4 mb-0 text-uppercase">{{ store.user?.role }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3">Recent Workouts</h2>
                    <div v-if="loading" class="text-muted">Loading workouts...</div>
                    <div v-else-if="error" class="alert alert-danger mb-0">{{ error }}</div>
                    <div v-else-if="workouts.length === 0" class="text-muted">No workouts found.</div>
                    <div v-else>
                        <div v-for="workout in workouts" :key="workout.id" class="border rounded p-3 mb-3">
                            <div class="fw-semibold mb-2">Workout Date: {{ workout.workout_date }}</div>
                            <ul class="mb-0">
                                <li v-for="entry in workout.entries" :key="entry.id">
                                    {{ entry.exercise_name }} - {{ entry.sets }} sets, {{ entry.reps }} reps, {{ entry.weight }} kg
                                </li>
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
        return {
            loading: true,
            error: '',
            form: {
                name: '',
                muscle_group: '',
                description: ''
            }
        };
    },
    computed: {
        isAdmin() {
            return (store.user?.role || 'user') === 'admin';
        }
    },
    async mounted() {
        await this.loadExercises();
    },
    methods: {
        async loadExercises() {
            this.loading = true;
            this.error = '';

            try {
                const response = await apiFetch('/api/exercises');
                store.exercises = response.data;
            } catch (error) {
                this.error = error.message;
            } finally {
                this.loading = false;
            }
        },
        async createExercise() {
            try {
                await apiFetch('/api/exercises', {
                    method: 'POST',
                    body: JSON.stringify(this.form)
                });

                this.form = { name: '', muscle_group: '', description: '' };
                await this.loadExercises();
            } catch (error) {
                this.error = error.message;
            }
        },
        async deleteExercise(id) {
            try {
                await apiFetch(`/api/exercises/${id}`, { method: 'DELETE' });
                await this.loadExercises();
            } catch (error) {
                this.error = error.message;
            }
        }
    },
    template: `
        <div class="container py-5">
            <div class="card shadow-sm border-0 mb-4" v-if="isAdmin">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3">Create Exercise</h2>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <input v-model="form.name" class="form-control" placeholder="Exercise name">
                        </div>
                        <div class="col-md-4">
                            <input v-model="form.muscle_group" class="form-control" placeholder="Muscle group">
                        </div>
                        <div class="col-md-4">
                            <input v-model="form.description" class="form-control" placeholder="Description">
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary" @click="createExercise">Save Exercise</button>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="error" class="alert alert-danger">{{ error }}</div>
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h1 class="h4 mb-3">Exercises</h1>
                    <div v-if="loading" class="text-muted">Loading exercises...</div>
                    <div v-else class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Muscle Group</th>
                                    <th>Description</th>
                                    <th v-if="isAdmin" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="exercise in store.exercises" :key="exercise.id">
                                    <td>{{ exercise.name }}</td>
                                    <td>{{ exercise.muscle_group }}</td>
                                    <td>{{ exercise.description }}</td>
                                    <td v-if="isAdmin" class="text-end">
                                        <button class="btn btn-sm btn-outline-danger" @click="deleteExercise(exercise.id)">Delete</button>
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

const WorkoutsView = {
    data() {
        return {
            loading: true,
            error: '',
            filters: {
                workout_date: '',
                exercise_id: ''
            }
        };
    },
    async mounted() {
        await this.loadExercises();
        await this.loadWorkouts();
    },
    methods: {
        async loadExercises() {
            try {
                const response = await apiFetch('/api/exercises');
                store.exercises = response.data;
            } catch (error) {
                this.error = error.message;
            }
        },
        async loadWorkouts() {
            this.loading = true;
            this.error = '';

            try {
                const params = new URLSearchParams();

                if (this.filters.workout_date) {
                    params.set('workout_date', this.filters.workout_date);
                }

                if (this.filters.exercise_id) {
                    params.set('exercise_id', this.filters.exercise_id);
                }

                const query = params.toString() ? `?${params.toString()}` : '';
                const response = await apiFetch(`/api/workouts${query}`);
                store.workouts = response.data;
            } catch (error) {
                this.error = error.message;
            } finally {
                this.loading = false;
            }
        }
    },
    template: `
        <div class="container py-5">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h1 class="h4 mb-3">Workout History</h1>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Date</label>
                            <input v-model="filters.workout_date" type="date" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Exercise</label>
                            <select v-model="filters.exercise_id" class="form-select">
                                <option value="">All exercises</option>
                                <option v-for="exercise in store.exercises" :key="exercise.id" :value="String(exercise.id)">
                                    {{ exercise.name }}
                                </option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button class="btn btn-dark" @click="loadWorkouts">Apply Filters</button>
                            <button class="btn btn-outline-secondary" @click="filters.workout_date=''; filters.exercise_id=''; loadWorkouts();">Reset</button>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="error" class="alert alert-danger">{{ error }}</div>
            <div v-if="loading" class="text-muted">Loading workouts...</div>
            <div v-else-if="store.workouts.length === 0" class="card shadow-sm border-0">
                <div class="card-body p-4 text-center">No workouts found.</div>
            </div>
            <div v-else>
                <div v-for="workout in store.workouts" :key="workout.id" class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <div class="fw-semibold mb-3">Workout Date: {{ workout.workout_date }}</div>
                        <table class="table table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Exercise</th>
                                    <th>Sets</th>
                                    <th>Reps</th>
                                    <th>Weight</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="entry in workout.entries" :key="entry.id">
                                    <td>{{ entry.exercise_name }}</td>
                                    <td>{{ entry.sets }}</td>
                                    <td>{{ entry.reps }}</td>
                                    <td>{{ entry.weight }}</td>
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

const routes = [
    { path: '/', redirect: '/login' },
    { path: '/login', component: LoginView },
    { path: '/dashboard', component: DashboardView },
    { path: '/exercises', component: ExercisesView },
    { path: '/workouts', component: WorkoutsView }
];

const router = createRouter({
    history: createWebHashHistory(),
    routes
});

router.beforeEach((to, from, next) => {
    if (to.path !== '/login' && !store.token) {
        next('/login');
        return;
    }

    if (to.path === '/login' && store.token) {
        next('/dashboard');
        return;
    }

    next();
});

createApp(AppLayout).use(router).mount('#app');
