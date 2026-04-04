<?php

namespace App\Controllers;

use App\Framework\Session;
use App\Framework\View;
use App\Services\UserService;
use InvalidArgumentException;
use Throwable;

class AuthController
{
    public function __construct(private readonly UserService $userService)
    {
    }

    public function showLogin(): string
    {
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        return View::render('auth/login', [
            'error' => Session::getFlash('error'),
            'success' => Session::getFlash('success'),
            'old' => Session::getFlash('old', []),
        ]);
    }

    public function login(): void
    {
        try {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = $this->userService->login($email, $password);

            Session::set('user', $user->toSessionArray());
            Session::flash('success', 'Welcome back, ' . $user->name . '!');

            $this->redirect('/dashboard');
        } catch (InvalidArgumentException $e) {
            Session::flash('error', $e->getMessage());
            Session::flash('old', ['email' => $_POST['email'] ?? '']);
            $this->redirect('/login');
        } catch (Throwable $e) {
            Session::flash('error', 'Something went wrong while signing you in.');
            Session::flash('old', ['email' => $_POST['email'] ?? '']);
            $this->redirect('/login');
        }
    }

    public function showRegister(): string
    {
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        return View::render('auth/register', [
            'error' => Session::getFlash('error'),
            'success' => Session::getFlash('success'),
            'old' => Session::getFlash('old', []),
        ]);
    }

    public function register(): void
    {
        try {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = $this->userService->register($name, $email, $password);

            Session::set('user', $user->toSessionArray());
            Session::flash('success', 'Your account has been created successfully.');

            $this->redirect('/dashboard');
        } catch (InvalidArgumentException $e) {
            Session::flash('error', $e->getMessage());
            Session::flash('old', [
                'name' => $_POST['name'] ?? '',
                'email' => $_POST['email'] ?? '',
            ]);
            $this->redirect('/register');
        } catch (Throwable $e) {
            Session::flash('error', 'Something went wrong while creating your account.');
            Session::flash('old', [
                'name' => $_POST['name'] ?? '',
                'email' => $_POST['email'] ?? '',
            ]);
            $this->redirect('/register');
        }
    }

    public function logout(): void
    {
        Session::destroy();
        Session::start();
        Session::flash('success', 'You have been logged out.');

        $this->redirect('/login');
    }

    private function isAuthenticated(): bool
    {
        return Session::has('user');
    }

    private function redirect(string $path): never
    {
        header('Location: ' . $path);
        exit;
    }
}
