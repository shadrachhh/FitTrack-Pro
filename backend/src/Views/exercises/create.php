<?php
$user = $user ?? [];
$old = $old ?? [];
$isAdmin = ($user['role'] ?? 'user') === 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Exercise</title>
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
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h1 class="h3 mb-4">Add Exercise</h1>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>

                        <form method="POST" action="/exercises/create">
                            <div class="mb-3">
                                <label for="name" class="form-label">Exercise Name</label>
                                <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($old['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="muscle_group" class="form-label">Muscle Group</label>
                                <input type="text" id="muscle_group" name="muscle_group" class="form-control" value="<?= htmlspecialchars($old['muscle_group'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea id="description" name="description" class="form-control" rows="4"><?= htmlspecialchars($old['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <a href="/exercises" class="btn btn-outline-secondary">Back</a>
                                <button type="submit" class="btn btn-primary">Save Exercise</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
