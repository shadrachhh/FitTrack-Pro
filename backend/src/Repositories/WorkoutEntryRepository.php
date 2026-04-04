<?php

namespace App\Repositories;

use App\Framework\Repository;
use PDOException;
use RuntimeException;

class WorkoutEntryRepository extends Repository
{
    public function createEntry(int $workoutId, int $exerciseId, int $sets, int $reps, float $weight): void
    {
        try {
            $statement = $this->connection->prepare(
                'INSERT INTO workout_entries (workout_id, exercise_id, sets, reps, weight)
                 VALUES (:workout_id, :exercise_id, :sets, :reps, :weight)'
            );

            $statement->execute([
                'workout_id' => $workoutId,
                'exercise_id' => $exerciseId,
                'sets' => $sets,
                'reps' => $reps,
                'weight' => $weight,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException('Unable to create workout entry.', 0, $e);
        }
    }

    public function getEntriesByWorkoutIds(array $workoutIds): array
    {
        if ($workoutIds === []) {
            return [];
        }

        try {
            $placeholders = implode(',', array_fill(0, count($workoutIds), '?'));
            $statement = $this->connection->prepare(
                "SELECT we.id, we.workout_id, we.exercise_id, we.sets, we.reps, we.weight, e.name AS exercise_name
                 FROM workout_entries we
                 INNER JOIN exercises e ON e.id = we.exercise_id
                 WHERE we.workout_id IN ($placeholders)
                 ORDER BY we.id ASC"
            );

            foreach (array_values($workoutIds) as $index => $workoutId) {
                $statement->bindValue($index + 1, (int) $workoutId, \PDO::PARAM_INT);
            }

            $statement->execute();

            return $statement->fetchAll();
        } catch (PDOException $e) {
            throw new RuntimeException('Unable to retrieve workout entries.', 0, $e);
        }
    }
}
