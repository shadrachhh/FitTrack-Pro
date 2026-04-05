<?php

namespace App\Controllers\Api;

use App\Framework\ApiResponse;
use App\Services\ExerciseService;
use InvalidArgumentException;
use Throwable;

class ExerciseApiController
{
    public function __construct(private readonly ExerciseService $exerciseService)
    {
    }

    public function index(array $filters = []): never
    {
        $result = $this->exerciseService->getExercises($filters);

        ApiResponse::json([
            'data' => $result['data'],
            'meta' => $result['meta'],
        ]);
    }

    public function store(array $input): never
    {
        try {
            $exercise = $this->exerciseService->createExercise(
                $input['name'] ?? '',
                $input['muscle_group'] ?? '',
                $input['description'] ?? ''
            );

            ApiResponse::json([
                'message' => 'Exercise created successfully.',
                'data' => $exercise,
            ], 201);
        } catch (InvalidArgumentException $e) {
            ApiResponse::json(['message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            ApiResponse::json(['message' => 'Unable to create exercise.'], 500);
        }
    }

    public function update(int $id, array $input): never
    {
        try {
            $exercise = $this->exerciseService->updateExercise(
                $id,
                $input['name'] ?? '',
                $input['muscle_group'] ?? '',
                $input['description'] ?? ''
            );

            ApiResponse::json([
                'message' => 'Exercise updated successfully.',
                'data' => $exercise,
            ]);
        } catch (InvalidArgumentException $e) {
            ApiResponse::json(['message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            ApiResponse::json(['message' => 'Unable to update exercise.'], 500);
        }
    }

    public function delete(int $id): never
    {
        try {
            $this->exerciseService->deleteExercise($id);

            ApiResponse::json([
                'message' => 'Exercise deleted successfully.',
            ]);
        } catch (InvalidArgumentException $e) {
            ApiResponse::json(['message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            ApiResponse::json(['message' => 'Unable to delete exercise.'], 500);
        }
    }
}
