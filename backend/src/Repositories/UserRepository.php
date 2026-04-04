<?php

namespace App\Repositories;

use App\Framework\Repository;
use App\Models\User;
use PDOException;
use RuntimeException;

class UserRepository extends Repository
{
    public function createUser(string $name, string $email, string $password): User
    {
        try {
            $statement = $this->connection->prepare(
                'INSERT INTO users (name, email, password, role, created_at) VALUES (:name, :email, :password, :role, NOW())'
            );

            $statement->execute([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => 'user',
            ]);

            $user = $this->findByEmail($email);

            if ($user === null) {
                throw new RuntimeException('User was created but could not be retrieved.');
            }

            return $user;
        } catch (PDOException $e) {
            throw new RuntimeException('Unable to create user.', 0, $e);
        }
    }

    public function findByEmail(string $email): ?User
    {
        try {
            $statement = $this->connection->prepare(
                'SELECT id, name, email, password, role, created_at FROM users WHERE email = :email LIMIT 1'
            );

            $statement->execute(['email' => $email]);

            $user = $statement->fetch();

            return $user ? User::fromArray($user) : null;
        } catch (PDOException $e) {
            throw new RuntimeException('Unable to retrieve user.', 0, $e);
        }
    }
}
