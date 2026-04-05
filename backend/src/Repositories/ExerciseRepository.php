<?php

namespace App\Repositories;

use App\Framework\Repository;
use PDOException;
use RuntimeException;

class ExerciseRepository extends Repository
{
    public function getExercises(?string $search = null, int $page = 1, int $perPage = 10): array
    {
        try {
            $offset = max(0, ($page - 1) * $perPage);
            $parameters = [];
            $sql = 'SELECT id, name, muscle_group, description FROM exercises';

            if ($search !== null && $search !== '') {
                $sql .= ' WHERE name LIKE :search OR muscle_group LIKE :search OR description LIKE :search';
                $parameters['search'] = '%' . $search . '%';
            }

            $sql .= ' ORDER BY name ASC LIMIT :limit OFFSET :offset';

            $statement = $this->connection->prepare($sql);

            foreach ($parameters as $key => $value) {
                $statement->bindValue(':' . $key, $value);
            }

            $statement->bindValue(':limit', $perPage, \PDO::PARAM_INT);
            $statement->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $statement->execute();

            return $statement->fetchAll();
        } catch (PDOException $e) {
            throw new RuntimeException('Unable to retrieve exercises.', 0, $e);
        }
    }

    public function countExercises(?string $search = null): int
    {
        try {
            $parameters = [];
            $sql = 'SELECT COUNT(*) FROM exercises';

            if ($search !== null && $search !== '') {
                $sql .= ' WHERE name LIKE :search OR muscle_group LIKE :search OR description LIKE :search';
                $parameters['search'] = '%' . $search . '%';
            }

            $statement = $this->connection->prepare($sql);

            foreach ($parameters as $key => $value) {
                $statement->bindValue(':' . $key, $value);
            }

            $statement->execute();

            return (int) $statement->fetchColumn();
        } catch (PDOException $e) {
            throw new RuntimeException('Unable to count exercises.', 0, $e);
        }
    }

    public function createExercise(string $name, string $muscleGroup, string $description): array
    {
        try {
            $statement = $this->connection->prepare(
                'INSERT INTO exercises (name, muscle_group, description) VALUES (:name, :muscle_group, :description)'
            );

            $statement->execute([
                'name' => $name,
                'muscle_group' => $muscleGroup,
                'description' => $description,
            ]);

            $exercise = $this->findById((int) $this->connection->lastInsertId());

            if ($exercise === null) {
                throw new RuntimeException('Exercise was created but could not be retrieved.');
            }

            return $exercise;
        } catch (PDOException $e) {
            throw new RuntimeException('Unable to create exercise.', 0, $e);
        }
    }

    public function findById(int $id): ?array
    {
        try {
            $statement = $this->connection->prepare(
                'SELECT id, name, muscle_group, description FROM exercises WHERE id = :id LIMIT 1'
            );

            $statement->execute(['id' => $id]);

            $exercise = $statement->fetch();

            return $exercise ?: null;
        } catch (PDOException $e) {
            throw new RuntimeException('Unable to retrieve exercise.', 0, $e);
        }
    }

    public function updateExercise(int $id, string $name, string $muscleGroup, string $description): array
    {
        try {
            $statement = $this->connection->prepare(
                'UPDATE exercises
                 SET name = :name, muscle_group = :muscle_group, description = :description
                 WHERE id = :id'
            );

            $statement->execute([
                'id' => $id,
                'name' => $name,
                'muscle_group' => $muscleGroup,
                'description' => $description,
            ]);

            $exercise = $this->findById($id);

            if ($exercise === null) {
                throw new RuntimeException('Exercise was updated but could not be retrieved.');
            }

            return $exercise;
        } catch (PDOException $e) {
            throw new RuntimeException('Unable to update exercise.', 0, $e);
        }
    }

    public function deleteExercise(int $id): void
    {
        try {
            $statement = $this->connection->prepare(
                'DELETE FROM exercises WHERE id = :id'
            );

            $statement->execute(['id' => $id]);
        } catch (PDOException $e) {
            throw new RuntimeException('Unable to delete exercise.', 0, $e);
        }
    }

    public function isUsedInWorkoutEntries(int $id): bool
    {
        try {
            $statement = $this->connection->prepare(
                'SELECT COUNT(*) FROM workout_entries WHERE exercise_id = :exercise_id'
            );

            $statement->execute(['exercise_id' => $id]);

            return (int) $statement->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException('Unable to verify exercise usage.', 0, $e);
        }
    }
}
