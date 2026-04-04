<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Error</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4 text-center">
                        <h1 class="h3 mb-3">Something went wrong</h1>
                        <p class="text-muted mb-0">
                            <?= htmlspecialchars($message ?? 'An unexpected error occurred.', ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
