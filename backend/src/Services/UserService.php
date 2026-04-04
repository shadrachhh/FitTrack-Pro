<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use InvalidArgumentException;
use RuntimeException;

class UserService
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    public function register(string $name, string $email, string $password): User
    {
        $name = trim($name);
        $email = strtolower(trim($email));

        $this->validateRegistrationInput($name, $email, $password);

        if ($this->userRepository->findByEmail($email) !== null) {
            throw new InvalidArgumentException('An account with that email already exists.');
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        if ($hashedPassword === false) {
            throw new RuntimeException('Unable to secure the password.');
        }

        return $this->userRepository->createUser($name, $email, $hashedPassword);
    }

    public function login(string $email, string $password): User
    {
        $email = strtolower(trim($email));

        if ($email === '' || $password === '') {
            throw new InvalidArgumentException('Email and password are required.');
        }

        $user = $this->userRepository->findByEmail($email);

        if ($user === null || !password_verify($password, $user->password)) {
            throw new InvalidArgumentException('Invalid email or password.');
        }

        return $user;
    }

    private function validateRegistrationInput(string $name, string $email, string $password): void
    {
        if ($name === '' || $email === '' || $password === '') {
            throw new InvalidArgumentException('All fields are required.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Please enter a valid email address.');
        }

        if (mb_strlen($name) > 100) {
            throw new InvalidArgumentException('Name must be 100 characters or fewer.');
        }

        if (mb_strlen($password) < 6) {
            throw new InvalidArgumentException('Password must be at least 6 characters long.');
        }
    }
}
