<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\Api\AuthApiController;
use App\Controllers\Api\ExerciseApiController;
use App\Controllers\Api\WorkoutApiController;
use App\Controllers\ExerciseController;
use App\Controllers\WorkoutController;
use App\Framework\ApiResponse;
use App\Framework\JwtHelper;
use App\Framework\Session;
use App\Framework\View;
use App\Repositories\ExerciseRepository;
use App\Repositories\UserRepository;
use App\Repositories\WorkoutEntryRepository;
use App\Repositories\WorkoutRepository;
use App\Services\ExerciseService;
use App\Services\WorkoutService;
use App\Services\UserService;

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDirectory = __DIR__ . '/../src/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDirectory . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

Session::start();

$authController = new AuthController(
    new UserService(
        new UserRepository()
    )
);

$exerciseRepository = new ExerciseRepository();
$exerciseService = new ExerciseService($exerciseRepository);
$workoutService = new WorkoutService(
    new WorkoutRepository(),
    new WorkoutEntryRepository(),
    $exerciseRepository
);

$exerciseController = new ExerciseController($exerciseService);
$workoutController = new WorkoutController($workoutService, $exerciseService);
$authApiController = new AuthApiController(new UserService(new UserRepository()));
$exerciseApiController = new ExerciseApiController($exerciseService);
$workoutApiController = new WorkoutApiController($workoutService);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

try {
    if (str_starts_with($path, '/api/')) {
        $input = json_decode(file_get_contents('php://input') ?: '[]', true);
        $input = is_array($input) ? $input : [];
        $authorizationHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $bearerToken = str_starts_with($authorizationHeader, 'Bearer ')
            ? substr($authorizationHeader, 7)
            : '';

        $getApiUser = static function () use ($bearerToken): array {
            if ($bearerToken === '') {
                ApiResponse::json(['message' => 'Authentication token is required.'], 401);
            }

            try {
                return JwtHelper::decode($bearerToken);
            } catch (Throwable $e) {
                ApiResponse::json(['message' => 'Invalid or expired token.'], 401);
            }
        };

        $ensureAdminApiUser = static function (array $apiUser): void {
            if (($apiUser['role'] ?? 'user') !== 'admin') {
                ApiResponse::json(['message' => 'Admin access required.'], 403);
            }
        };

        if ($path === '/api/register' && $method === 'POST') {
            $authApiController->register($input);
        }

        if ($path === '/api/login' && $method === 'POST') {
            $authApiController->login($input);
        }

        if ($path === '/api/exercises' && $method === 'GET') {
            $getApiUser();
            $exerciseApiController->index();
        }

        if ($path === '/api/exercises' && $method === 'POST') {
            $apiUser = $getApiUser();
            $ensureAdminApiUser($apiUser);
            $exerciseApiController->store($input);
        }

        if (preg_match('#^/api/exercises/(\d+)$#', $path, $matches) === 1 && $method === 'PUT') {
            $apiUser = $getApiUser();
            $ensureAdminApiUser($apiUser);
            $exerciseApiController->update((int) $matches[1], $input);
        }

        if (preg_match('#^/api/exercises/(\d+)$#', $path, $matches) === 1 && $method === 'DELETE') {
            $apiUser = $getApiUser();
            $ensureAdminApiUser($apiUser);
            $exerciseApiController->delete((int) $matches[1]);
        }

        if ($path === '/api/workouts' && $method === 'GET') {
            $apiUser = $getApiUser();
            $workoutApiController->index((int) ($apiUser['sub'] ?? 0), [
                'workout_date' => $_GET['workout_date'] ?? '',
                'exercise_id' => $_GET['exercise_id'] ?? '',
            ]);
        }

        if ($path === '/api/workouts' && $method === 'POST') {
            $apiUser = $getApiUser();
            $workoutApiController->store((int) ($apiUser['sub'] ?? 0), $input);
        }

        ApiResponse::json(['message' => 'API endpoint not found.'], 404);
    }

    if ($path === '/') {
        header('Location: ' . (Session::has('user') ? '/dashboard' : '/login'));
        exit;
    }

    if ($path === '/login' && $method === 'GET') {
        echo $authController->showLogin();
        exit;
    }

    if ($path === '/login' && $method === 'POST') {
        $authController->login();
    }

    if ($path === '/register' && $method === 'GET') {
        echo $authController->showRegister();
        exit;
    }

    if ($path === '/register' && $method === 'POST') {
        $authController->register();
    }

    if ($path === '/logout' && $method === 'POST') {
        $authController->logout();
    }

    if ($path === '/dashboard' && $method === 'GET') {
        if (!Session::has('user')) {
            Session::flash('error', 'Please log in to continue.');
            header('Location: /login');
            exit;
        }

        $user = Session::get('user');
        $dashboardStats = $workoutService->getDashboardStats((int) $user['id']);

        echo View::render('dashboard', [
            'user' => $user,
            'success' => Session::getFlash('success'),
            'totalWorkouts' => $dashboardStats['totalWorkouts'],
            'latestWorkouts' => $dashboardStats['latestWorkouts'],
        ]);
        exit;
    }

    if (
        in_array(
            $path,
            ['/exercises', '/exercises/create', '/exercises/edit', '/exercises/delete', '/workouts', '/workouts/create'],
            true
        ) && !Session::has('user')
    ) {
        Session::flash('error', 'Please log in to continue.');
        header('Location: /login');
        exit;
    }

    if ($path === '/exercises' && $method === 'GET') {
        echo $exerciseController->index();
        exit;
    }

    if ($path === '/exercises/create' && $method === 'GET') {
        echo $exerciseController->showCreate();
        exit;
    }

    if ($path === '/exercises/create' && $method === 'POST') {
        $exerciseController->create();
    }

    if ($path === '/exercises/edit' && $method === 'GET') {
        echo $exerciseController->showEdit();
        exit;
    }

    if ($path === '/exercises/edit' && $method === 'POST') {
        $exerciseController->update();
    }

    if ($path === '/exercises/delete' && $method === 'POST') {
        $exerciseController->delete();
    }

    if ($path === '/workouts' && $method === 'GET') {
        echo $workoutController->index();
        exit;
    }

    if ($path === '/workouts/create' && $method === 'GET') {
        echo $workoutController->showCreate();
        exit;
    }

    if ($path === '/workouts/create' && $method === 'POST') {
        $workoutController->create();
    }

    http_response_code(404);
    echo 'Page not found.';
} catch (Throwable $e) {
    http_response_code(500);
    echo View::render('errors/500', [
        'message' => 'Please try again later.',
    ]);
}
