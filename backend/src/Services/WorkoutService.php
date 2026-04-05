<?php

namespace App\Services;

use App\Repositories\ExerciseRepository;
use App\Repositories\WorkoutEntryRepository;
use App\Repositories\WorkoutRepository;
use InvalidArgumentException;

class WorkoutService
{
    public function __construct(
        private readonly WorkoutRepository $workoutRepository,
        private readonly WorkoutEntryRepository $workoutEntryRepository,
        private readonly ExerciseRepository $exerciseRepository
    ) {
    }

    public function createWorkout(int $userId, string $workoutDate, array $entries): array
    {
        if ($userId <= 0) {
            throw new InvalidArgumentException('A valid user is required.');
        }

        $workoutDate = trim($workoutDate);

        if ($workoutDate === '') {
            throw new InvalidArgumentException('Workout date is required.');
        }

        $normalizedEntries = $this->normalizeEntries($entries);

        if ($normalizedEntries === []) {
            throw new InvalidArgumentException('Please add at least one valid exercise entry.');
        }

        $workoutId = $this->workoutRepository->createWorkout($userId, $workoutDate);

        foreach ($normalizedEntries as $entry) {
            $this->workoutEntryRepository->createEntry(
                $workoutId,
                $entry['exercise_id'],
                $entry['sets'],
                $entry['reps'],
                $entry['weight']
            );
        }

        $workout = $this->workoutRepository->findById($workoutId);

        if ($workout === null) {
            throw new InvalidArgumentException('Workout could not be retrieved after creation.');
        }

        $workouts = $this->attachEntries([$workout]);

        return $workouts[0];
    }

    public function getWorkoutsForUser(int $userId, array $filters = []): array
    {
        $workoutDate = trim((string) ($filters['workout_date'] ?? ''));
        $exerciseId = (int) ($filters['exercise_id'] ?? 0);
        $page = max(1, (int) ($filters['page'] ?? 1));
        $perPage = (int) ($filters['per_page'] ?? 5);
        $perPage = min(max($perPage, 1), 50);

        if ($exerciseId > 0 && $this->exerciseRepository->findById($exerciseId) === null) {
            throw new InvalidArgumentException('Selected exercise filter is invalid.');
        }

        $total = $this->workoutRepository->countByUserIdWithFilters(
            $userId,
            $workoutDate !== '' ? $workoutDate : null,
            $exerciseId > 0 ? $exerciseId : null
        );

        $workouts = $this->workoutRepository->getWorkoutsByUserId(
            $userId,
            $workoutDate !== '' ? $workoutDate : null,
            $exerciseId > 0 ? $exerciseId : null,
            $page,
            $perPage
        );

        return [
            'data' => $this->attachEntries($workouts),
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => max(1, (int) ceil($total / $perPage)),
                'workout_date' => $workoutDate,
                'exercise_id' => $exerciseId > 0 ? $exerciseId : '',
            ],
        ];
    }

    public function getDashboardStats(int $userId): array
    {
        $totalWorkouts = $this->workoutRepository->countByUserId($userId);
        $latestWorkouts = $this->workoutRepository->getLatestWorkoutsByUserId($userId, 3);

        return [
            'totalWorkouts' => $totalWorkouts,
            'latestWorkouts' => $this->attachEntries($latestWorkouts),
        ];
    }

    private function normalizeEntries(array $entries): array
    {
        $exerciseIds = $entries['exercise_id'] ?? [];
        $setsList = $entries['sets'] ?? [];
        $repsList = $entries['reps'] ?? [];
        $weightList = $entries['weight'] ?? [];

        $normalized = [];

        foreach ($exerciseIds as $index => $exerciseId) {
            $exerciseId = (int) $exerciseId;
            $sets = isset($setsList[$index]) ? (int) $setsList[$index] : 0;
            $reps = isset($repsList[$index]) ? (int) $repsList[$index] : 0;
            $weight = isset($weightList[$index]) && $weightList[$index] !== '' ? (float) $weightList[$index] : 0.0;

            if ($exerciseId === 0 && $sets === 0 && $reps === 0 && $weight === 0.0) {
                continue;
            }

            if ($exerciseId <= 0) {
                throw new InvalidArgumentException('Please select an exercise for every workout row.');
            }

            if ($sets <= 0 || $reps <= 0) {
                throw new InvalidArgumentException('Sets and reps must be greater than zero.');
            }

            if ($weight < 0) {
                throw new InvalidArgumentException('Weight cannot be negative.');
            }

            if ($this->exerciseRepository->findById($exerciseId) === null) {
                throw new InvalidArgumentException('One of the selected exercises does not exist.');
            }

            $normalized[] = [
                'exercise_id' => $exerciseId,
                'sets' => $sets,
                'reps' => $reps,
                'weight' => $weight,
            ];
        }

        return $normalized;
    }

    private function attachEntries(array $workouts): array
    {
        if ($workouts === []) {
            return [];
        }

        $workoutIds = array_map(
            static fn (array $workout): int => (int) $workout['id'],
            $workouts
        );

        $entries = $this->workoutEntryRepository->getEntriesByWorkoutIds($workoutIds);
        $entriesByWorkoutId = [];

        foreach ($entries as $entry) {
            $workoutId = (int) $entry['workout_id'];
            $entriesByWorkoutId[$workoutId][] = $entry;
        }

        foreach ($workouts as &$workout) {
            $workout['entries'] = $entriesByWorkoutId[(int) $workout['id']] ?? [];
        }

        return $workouts;
    }
}
