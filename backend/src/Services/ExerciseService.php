<?php

namespace App\Services;

use App\Repositories\ExerciseRepository;
use InvalidArgumentException;

class ExerciseService
{
    public function __construct(private readonly ExerciseRepository $exerciseRepository)
    {
    }

    public function getAllExercises(): array
    {
        return $this->exerciseRepository->getAllExercises();
    }

    public function createExercise(string $name, string $muscleGroup, string $description): array
    {
        [$name, $muscleGroup, $description] = $this->validateExerciseData($name, $muscleGroup, $description);

        return $this->exerciseRepository->createExercise($name, $muscleGroup, $description);
    }

    public function getExerciseById(int $id): array
    {
        $exercise = $this->exerciseRepository->findById($id);

        if ($exercise === null) {
            throw new InvalidArgumentException('Exercise not found.');
        }

        return $exercise;
    }

    public function updateExercise(int $id, string $name, string $muscleGroup, string $description): array
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('Exercise not found.');
        }

        if ($this->exerciseRepository->findById($id) === null) {
            throw new InvalidArgumentException('Exercise not found.');
        }

        [$name, $muscleGroup, $description] = $this->validateExerciseData($name, $muscleGroup, $description);

        return $this->exerciseRepository->updateExercise($id, $name, $muscleGroup, $description);
    }

    public function deleteExercise(int $id): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('Exercise not found.');
        }

        if ($this->exerciseRepository->findById($id) === null) {
            throw new InvalidArgumentException('Exercise not found.');
        }

        if ($this->exerciseRepository->isUsedInWorkoutEntries($id)) {
            throw new InvalidArgumentException('This exercise cannot be deleted because it is already used in a workout.');
        }

        $this->exerciseRepository->deleteExercise($id);
    }

    private function validateExerciseData(string $name, string $muscleGroup, string $description): array
    {
        $name = trim($name);
        $muscleGroup = trim($muscleGroup);
        $description = trim($description);

        if ($name === '' || $muscleGroup === '') {
            throw new InvalidArgumentException('Exercise name and muscle group are required.');
        }

        if (mb_strlen($name) > 100 || mb_strlen($muscleGroup) > 100) {
            throw new InvalidArgumentException('Name and muscle group must be 100 characters or fewer.');
        }

        return [$name, $muscleGroup, $description];
    }
}
