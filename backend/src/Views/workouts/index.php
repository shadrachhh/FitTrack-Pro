<?php
$user = $user ?? [];
$workouts = $workouts ?? [];
$exercises = $exercises ?? [];
$filters = $filters ?? [];
$isAdmin = ($user['role'] ?? 'user') === 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workouts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/dashboard">FitTrack Pro</a>
            <div class="navbar-nav me-auto">
                <a class="nav-link" href="/dashboard">Dashboard</a>
                <a class="nav-link active" href="/workouts">Workouts</a>
                <a class="nav-link" href="/exercises"><?= $isAdmin ? 'Manage Exercises' : 'Exercises' ?></a>
            </div>
            <span class="navbar-text text-white me-3">
                <?= htmlspecialchars($user['name'] ?? 'User', ENT_QUOTES, 'UTF-8') ?>
            </span>
            <form method="POST" action="/logout">
                <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
            </form>
        </div>
    </nav>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">My Workouts</h1>
                <p class="text-muted mb-0">Review and filter your workout history.</p>
            </div>
            <a href="/workouts/create" class="btn btn-primary">Add Workout</a>
        </div>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <form method="GET" action="/workouts" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="workout_date" class="form-label">Filter by Date</label>
                        <input
                            type="date"
                            id="workout_date"
                            name="workout_date"
                            class="form-control"
                            value="<?= htmlspecialchars((string) ($filters['workout_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                        >
                    </div>
                    <div class="col-md-4">
                        <label for="exercise_id" class="form-label">Filter by Exercise</label>
                        <select id="exercise_id" name="exercise_id" class="form-select">
                            <option value="">All exercises</option>
                            <?php foreach ($exercises as $exercise): ?>
                                <option
                                    value="<?= (int) $exercise['id'] ?>"
                                    <?= (string) ($filters['exercise_id'] ?? '') === (string) $exercise['id'] ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($exercise['name'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-dark">Apply Filters</button>
                        <a href="/workouts" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($workouts === []): ?>
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 text-center">
                    No workouts found for the selected filters.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($workouts as $workout): ?>
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="h5 mb-0">Workout Date: <?= htmlspecialchars($workout['workout_date'], ENT_QUOTES, 'UTF-8') ?></h2>
                            <span class="badge text-bg-secondary"><?= count($workout['entries']) ?> exercise<?= count($workout['entries']) === 1 ? '' : 's' ?></span>
                        </div>

                        <?php if ($workout['entries'] === []): ?>
                            <p class="text-muted mb-0">No entries saved for this workout.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Exercise</th>
                                            <th>Sets</th>
                                            <th>Reps</th>
                                            <th>Weight (kg)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($workout['entries'] as $entry): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($entry['exercise_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= (int) $entry['sets'] ?></td>
                                                <td><?= (int) $entry['reps'] ?></td>
                                                <td><?= htmlspecialchars((string) $entry['weight'], ENT_QUOTES, 'UTF-8') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
