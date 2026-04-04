<?php

namespace App\Repositories;

use App\Framework\Repository;
use PDOException;
use RuntimeException;

class WorkoutRepository extends Repository
{
    public function createWorkout(int $userId, string $workoutDate): int
    {
        try {
            $statement = $this->connection->prepare(
                'INSERT INTO workouts (user_id, workout_date, created_at) VALUES (:user_id, :workout_date, NOW())'
            );

            $statement->execute([
                'user_id' => $userId,
                'workout_date' => $workoutDate,
            ]);

            return (int) $this->connection->lastInsertId();
        } catch (PDOException $e) {
            throw new RuntimeException('Unable to create workout.', 0, $e);
        }
    }

    public function getWorkoutsByUserId(int $userId, ?string $workoutDate = null, ?int $exerciseId = null): array
    {
        try {
            $sql = 'SELECT DISTINCT w.id, w.user_id, w.workout_date, w.created_at
                    FROM workouts w';
            $parameters = ['user_id' => $userId];

            if ($exerciseId !== null) {
                $sql .= ' INNER JOIN workout_entries we ON we.workout_id = w.id';
            }

            $sql .= ' WHERE w.user_id = :user_id';

            if ($workoutDate !== null && $workoutDate !== '') {
                $sql .= ' AND w.workout_date = :workout_date';
                $parameters['workout_date'] = $workoutDate;
            }

            if ($exerciseId !== null) {
                $sql .= ' AND we.exercise_id = :exercise_id';
                $parameters['exercise_id'] = $exerciseId;
            }

            $sql .= ' ORDER BY w.workout_date DESC, w.id DESC';

            $statement = $this->connection->prepare($sql);
            $statement->execute($parameters);

            return $statement->fetchAll();
        } catch (PDOException $e) {
            throw new RuntimeException('Unable to retrieve workouts.', 0, $e);
        }
    }

    public function countByUserId(int $userId): int
    {
        try {
            $statement = $this->connection->prepare(
                'SELECT COUNT(*) FROM workouts WHERE user_id = :user_id'
            );

            $statement->execute(['user_id' => $userId]);

            return (int) $statement->fetchColumn();
        } catch (PDOException $e) {
            throw new RuntimeException('Unable to count workouts.', 0, $e);
        }
    }

    public function getLatestWorkoutsByUserId(int $userId, int $limit): array
    {
        try {
            $statement = $this->connection->prepare(
                'SELECT id, user_id, workout_date, created_at
                 FROM workouts
                 WHERE user_id = :user_id
                 ORDER BY workout_date DESC, id DESC
                 LIMIT :limit'
            );

            $statement->bindValue(':user_id', $userId, \PDO::PARAM_INT);
            $statement->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $statement->execute();

            return $statement->fetchAll();
        } catch (PDOException $e) {
            throw new RuntimeException('Unable to retrieve latest workouts.', 0, $e);
        }
    }

    public function findById(int $id): ?array
    {
        try {
            $statement = $this->connection->prepare(
                'SELECT id, user_id, workout_date, created_at
                 FROM workouts
                 WHERE id = :id
                 LIMIT 1'
            );

            $statement->execute(['id' => $id]);

            $workout = $statement->fetch();

            return $workout ?: null;
        } catch (PDOException $e) {
            throw new RuntimeException('Unable to retrieve workout.', 0, $e);
        }
    }
}
