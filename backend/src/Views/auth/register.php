<?php
$old = $old ?? [];
$name = htmlspecialchars($old['name'] ?? '', ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h1 class="h3 text-center mb-4">Create Account</h1>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success" role="alert">
                                <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="/register">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?= $name ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= $email ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="form-text">Use at least 6 characters.</div>
                            </div>

                            <button type="submit" class="btn btn-success w-100">Register</button>
                        </form>

                        <p class="text-center mt-3 mb-0">
                            Already have an account?
                            <a href="/login">Login</a>
                        </p>

                        <hr class="my-4">

                        <p class="text-center text-muted small mb-2">
                            You can also test the Vue single-page application.
                        </p>
                        <a href="/spa" class="btn btn-outline-dark w-100">Open SPA Version</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
