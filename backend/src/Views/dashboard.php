<?php
$user = $user ?? [];
$latestWorkouts = $latestWorkouts ?? [];
$totalWorkouts = $totalWorkouts ?? 0;
$isAdmin = ($user['role'] ?? 'user') === 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/dashboard">FitTrack Pro</a>
            <div class="navbar-nav me-auto">
                <a class="nav-link active" href="/dashboard">Dashboard</a>
                <a class="nav-link" href="/workouts">Workouts</a>
                <a class="nav-link" href="/exercises"><?= $isAdmin ? 'Manage Exercises' : 'Exercises' ?></a>
            </div>
            <span class="navbar-text text-white me-3">
                <?= htmlspecialchars($user['name'] ?? 'User', ENT_QUOTES, 'UTF-8') ?>
            </span>
            <form method="POST" action="/logout" class="ms-auto ms-lg-0">
                <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
            </form>
        </div>
    </nav>

    <div class="container py-5">
        <?php if (!empty($success)): ?>
            <div class="alert alert-success" role="alert">
                <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h1 class="h3">Welcome, <?= htmlspecialchars($user['name'] ?? 'User', ENT_QUOTES, 'UTF-8') ?></h1>
                        <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                        <p class="mb-0"><strong>Member Since:</strong> <?= htmlspecialchars($user['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <span class="badge text-bg-dark"><?= htmlspecialchars(strtoupper($user['role'] ?? 'user'), ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body p-4">
                        <h2 class="h6 text-muted">Total Workouts</h2>
                        <p class="display-6 mb-0"><?= (int) $totalWorkouts ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body p-4">
                        <h2 class="h6 text-muted">Quick Actions</h2>
                        <div class="d-grid gap-2">
                            <a href="/workouts/create" class="btn btn-primary">Log Workout</a>
                            <a href="/workouts" class="btn btn-outline-secondary">View Workouts</a>
                            <?php if ($isAdmin): ?>
                                <a href="/exercises/create" class="btn btn-outline-dark">Add Exercise</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="h5 mb-0">Latest 3 Workouts</h2>
                            <a href="/workouts" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>

                        <?php if ($latestWorkouts === []): ?>
                            <p class="text-muted mb-0">No workouts recorded yet.</p>
                        <?php else: ?>
                            <?php foreach ($latestWorkouts as $workout): ?>
                                <div class="border rounded p-3 mb-3">
                                    <p class="fw-semibold mb-2">
                                        Workout Date: <?= htmlspecialchars($workout['workout_date'], ENT_QUOTES, 'UTF-8') ?>
                                    </p>
                                    <?php if ($workout['entries'] === []): ?>
                                        <p class="text-muted mb-0">No exercise entries found.</p>
                                    <?php else: ?>
                                        <ul class="mb-0">
                                            <?php foreach ($workout['entries'] as $entry): ?>
                                                <li>
                                                    <?= htmlspecialchars($entry['exercise_name'], ENT_QUOTES, 'UTF-8') ?>
                                                    -
                                                    <?= (int) $entry['sets'] ?> sets,
                                                    <?= (int) $entry['reps'] ?> reps,
                                                    <?= htmlspecialchars((string) $entry['weight'], ENT_QUOTES, 'UTF-8') ?> kg
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
