<?php

namespace App\Controllers\Api;

use App\Framework\ApiResponse;
use App\Framework\JwtHelper;
use App\Services\UserService;
use InvalidArgumentException;
use Throwable;

class AuthApiController
{
    public function __construct(private readonly UserService $userService)
    {
    }

    public function register(array $input): never
    {
        try {
            $user = $this->userService->register(
                $input['name'] ?? '',
                $input['email'] ?? '',
                $input['password'] ?? ''
            );

            ApiResponse::json([
                'message' => 'Registration successful.',
                'user' => $user->toSessionArray(),
            ], 201);
        } catch (InvalidArgumentException $e) {
            ApiResponse::json(['message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            ApiResponse::json(['message' => 'Unable to register user.'], 500);
        }
    }

    public function login(array $input): never
    {
        try {
            $user = $this->userService->login(
                $input['email'] ?? '',
                $input['password'] ?? ''
            );

            $userData = $user->toSessionArray();
            $token = JwtHelper::encode([
                'sub' => $userData['id'],
                'email' => $userData['email'],
                'role' => $userData['role'] ?? 'user',
                'exp' => time() + 7200,
            ]);

            ApiResponse::json([
                'message' => 'Login successful.',
                'token' => $token,
                'user' => $userData,
            ]);
        } catch (InvalidArgumentException $e) {
            ApiResponse::json(['message' => $e->getMessage()], 401);
        } catch (Throwable $e) {
            ApiResponse::json(['message' => 'Unable to log in.'], 500);
        }
    }
}
