<?php
$user = $user ?? [];
$old = $old ?? [];
$exercises = $exercises ?? [];
$isAdmin = ($user['role'] ?? 'user') === 'admin';
$oldExerciseIds = $old['exercise_id'] ?? ['', '', ''];
$oldSets = $old['sets'] ?? ['', '', ''];
$oldReps = $old['reps'] ?? ['', '', ''];
$oldWeight = $old['weight'] ?? ['', '', ''];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Workout</title>
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
        <div class="row justify-content-center">
            <div class="col-xl-10">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h1 class="h3 mb-4">Create Workout</h1>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>

                        <?php if ($exercises === []): ?>
                            <div class="alert alert-warning">
                                No exercises are available yet. <?= $isAdmin ? 'Please add an exercise first.' : 'Please ask an admin to add exercises first.' ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="/workouts/create">
                            <div class="mb-4">
                                <label for="workout_date" class="form-label">Workout Date</label>
                                <input type="date" id="workout_date" name="workout_date" class="form-control" value="<?= htmlspecialchars($old['workout_date'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>

                            <h2 class="h5 mb-3">Workout Entries</h2>
                            <p class="text-muted">Fill in up to three exercise rows for this workout.</p>
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Exercise</th>
                                            <th>Sets</th>
                                            <th>Reps</th>
                                            <th>Weight (kg)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php for ($i = 0; $i < 3; $i++): ?>
                                            <tr>
                                                <td>
                                                    <select name="exercise_id[]" class="form-select">
                                                        <option value="">Select exercise</option>
                                                        <?php foreach ($exercises as $exercise): ?>
                                                            <option value="<?= (int) $exercise['id'] ?>" <?= (string) ($oldExerciseIds[$i] ?? '') === (string) $exercise['id'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($exercise['name'], ENT_QUOTES, 'UTF-8') ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" min="0" name="sets[]" class="form-control" value="<?= htmlspecialchars((string) ($oldSets[$i] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                                </td>
                                                <td>
                                                    <input type="number" min="0" name="reps[]" class="form-control" value="<?= htmlspecialchars((string) ($oldReps[$i] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                                </td>
                                                <td>
                                                    <input type="number" min="0" step="0.01" name="weight[]" class="form-control" value="<?= htmlspecialchars((string) ($oldWeight[$i] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                                </td>
                                            </tr>
                                        <?php endfor; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex gap-2">
                                <a href="/workouts" class="btn btn-outline-secondary">Back</a>
                                <button type="submit" class="btn btn-primary">Save Workout</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
