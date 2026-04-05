<?php

namespace App\Controllers\Api;

use App\Framework\ApiResponse;
use App\Services\WorkoutService;
use InvalidArgumentException;
use Throwable;

class WorkoutApiController
{
    public function __construct(private readonly WorkoutService $workoutService)
    {
    }

    public function index(int $userId, array $filters): never
    {
        try {
            $result = $this->workoutService->getWorkoutsForUser($userId, $filters);

            ApiResponse::json([
                'data' => $result['data'],
                'meta' => $result['meta'],
            ]);
        } catch (InvalidArgumentException $e) {
            ApiResponse::json(['message' => $e->getMessage()], 422);
        }
    }

    public function store(int $userId, array $input): never
    {
        try {
            $workout = $this->workoutService->createWorkout(
                $userId,
                $input['workout_date'] ?? '',
                [
                    'exercise_id' => $input['exercise_id'] ?? [],
                    'sets' => $input['sets'] ?? [],
                    'reps' => $input['reps'] ?? [],
                    'weight' => $input['weight'] ?? [],
                ]
            );

            ApiResponse::json([
                'message' => 'Workout created successfully.',
                'data' => $workout,
            ], 201);
        } catch (InvalidArgumentException $e) {
            ApiResponse::json(['message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            ApiResponse::json(['message' => 'Unable to create workout.'], 500);
        }
    }
}
