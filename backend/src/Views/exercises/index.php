<?php
$user = $user ?? [];
$exercises = $exercises ?? [];
$isAdmin = ($user['role'] ?? 'user') === 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exercises</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/dashboard">FitTrack Pro</a>
            <div class="navbar-nav me-auto">
                <a class="nav-link" href="/dashboard">Dashboard</a>
                <a class="nav-link" href="/workouts">Workouts</a>
                <a class="nav-link active" href="/exercises"><?= $isAdmin ? 'Manage Exercises' : 'Exercises' ?></a>
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
                <h1 class="h3 mb-1">Exercises</h1>
                <p class="text-muted mb-0">
                    <?= $isAdmin ? 'Create, update, and remove exercises for all users.' : 'Browse available exercises before logging workouts.' ?>
                </p>
            </div>
            <?php if ($isAdmin): ?>
                <a href="/exercises/create" class="btn btn-primary">Add Exercise</a>
            <?php endif; ?>
        </div>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Name</th>
                                <th>Muscle Group</th>
                                <th>Description</th>
                                <?php if ($isAdmin): ?>
                                    <th class="text-end">Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($exercises === []): ?>
                                <tr>
                                    <td colspan="<?= $isAdmin ? '4' : '3' ?>" class="text-center py-4">No exercises added yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($exercises as $exercise): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($exercise['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($exercise['muscle_group'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($exercise['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                        <?php if ($isAdmin): ?>
                                            <td class="text-end">
                                                <a href="/exercises/edit?id=<?= (int) $exercise['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                <form method="POST" action="/exercises/delete" class="d-inline">
                                                    <input type="hidden" name="id" value="<?= (int) $exercise['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                </form>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
