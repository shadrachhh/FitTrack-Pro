<?php

namespace App\Controllers;

use App\Framework\Session;
use App\Framework\View;
use App\Services\ExerciseService;
use App\Services\WorkoutService;
use InvalidArgumentException;
use Throwable;

class WorkoutController
{
    public function __construct(
        private readonly WorkoutService $workoutService,
        private readonly ExerciseService $exerciseService
    ) {
    }

    public function index(): string
    {
        $user = Session::get('user');

        try {
            $filters = [
                'workout_date' => $_GET['workout_date'] ?? '',
                'exercise_id' => $_GET['exercise_id'] ?? '',
            ];

            return View::render('workouts/index', [
                'user' => $user,
                'success' => Session::getFlash('success'),
                'error' => Session::getFlash('error'),
                'workouts' => $this->workoutService->getWorkoutsForUser((int) $user['id'], $filters),
                'exercises' => $this->exerciseService->getAllExercises(),
                'filters' => $filters,
            ]);
        } catch (InvalidArgumentException $e) {
            return View::render('workouts/index', [
                'user' => $user,
                'success' => Session::getFlash('success'),
                'error' => $e->getMessage(),
                'workouts' => [],
                'exercises' => $this->exerciseService->getAllExercises(),
                'filters' => [
                    'workout_date' => $_GET['workout_date'] ?? '',
                    'exercise_id' => $_GET['exercise_id'] ?? '',
                ],
            ]);
        }
    }

    public function showCreate(): string
    {
        return View::render('workouts/create', [
            'user' => Session::get('user'),
            'error' => Session::getFlash('error'),
            'old' => Session::getFlash('old', []),
            'exercises' => $this->exerciseService->getAllExercises(),
        ]);
    }

    public function create(): void
    {
        $user = Session::get('user');

        try {
            $this->workoutService->createWorkout(
                (int) $user['id'],
                $_POST['workout_date'] ?? '',
                [
                    'exercise_id' => $_POST['exercise_id'] ?? [],
                    'sets' => $_POST['sets'] ?? [],
                    'reps' => $_POST['reps'] ?? [],
                    'weight' => $_POST['weight'] ?? [],
                ]
            );

            Session::flash('success', 'Workout saved successfully.');
            $this->redirect('/workouts');
        } catch (InvalidArgumentException $e) {
            Session::flash('error', $e->getMessage());
            Session::flash('old', [
                'workout_date' => $_POST['workout_date'] ?? '',
                'exercise_id' => $_POST['exercise_id'] ?? [],
                'sets' => $_POST['sets'] ?? [],
                'reps' => $_POST['reps'] ?? [],
                'weight' => $_POST['weight'] ?? [],
            ]);
            $this->redirect('/workouts/create');
        } catch (Throwable $e) {
            Session::flash('error', 'Something went wrong while saving the workout.');
            Session::flash('old', [
                'workout_date' => $_POST['workout_date'] ?? '',
                'exercise_id' => $_POST['exercise_id'] ?? [],
                'sets' => $_POST['sets'] ?? [],
                'reps' => $_POST['reps'] ?? [],
                'weight' => $_POST['weight'] ?? [],
            ]);
            $this->redirect('/workouts/create');
        }
    }

    private function redirect(string $path): never
    {
        header('Location: ' . $path);
        exit;
    }
}
