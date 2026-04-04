<?php

namespace App\Controllers;

use App\Framework\Session;
use App\Framework\View;
use App\Services\ExerciseService;
use InvalidArgumentException;
use Throwable;

class ExerciseController
{
    public function __construct(private readonly ExerciseService $exerciseService)
    {
    }

    public function index(): string
    {
        return View::render('exercises/index', [
            'user' => Session::get('user'),
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error'),
            'exercises' => $this->exerciseService->getAllExercises(),
        ]);
    }

    public function showCreate(): string
    {
        $this->ensureAdmin();

        return View::render('exercises/create', [
            'user' => Session::get('user'),
            'error' => Session::getFlash('error'),
            'old' => Session::getFlash('old', []),
        ]);
    }

    public function create(): void
    {
        $this->ensureAdmin();

        try {
            $this->exerciseService->createExercise(
                $_POST['name'] ?? '',
                $_POST['muscle_group'] ?? '',
                $_POST['description'] ?? ''
            );

            Session::flash('success', 'Exercise created successfully.');
            $this->redirect('/exercises');
        } catch (InvalidArgumentException $e) {
            Session::flash('error', $e->getMessage());
            Session::flash('old', [
                'name' => $_POST['name'] ?? '',
                'muscle_group' => $_POST['muscle_group'] ?? '',
                'description' => $_POST['description'] ?? '',
            ]);
            $this->redirect('/exercises/create');
        } catch (Throwable $e) {
            Session::flash('error', 'Something went wrong while saving the exercise.');
            Session::flash('old', [
                'name' => $_POST['name'] ?? '',
                'muscle_group' => $_POST['muscle_group'] ?? '',
                'description' => $_POST['description'] ?? '',
            ]);
            $this->redirect('/exercises/create');
        }
    }

    public function showEdit(): string
    {
        $this->ensureAdmin();

        try {
            $exercise = $this->exerciseService->getExerciseById((int) ($_GET['id'] ?? 0));
        } catch (InvalidArgumentException $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect('/exercises');
        }

        return View::render('exercises/edit', [
            'user' => Session::get('user'),
            'error' => Session::getFlash('error'),
            'exercise' => $exercise,
            'old' => Session::getFlash('old', []),
        ]);
    }

    public function update(): void
    {
        $this->ensureAdmin();

        $id = (int) ($_GET['id'] ?? 0);

        try {
            $this->exerciseService->updateExercise(
                $id,
                $_POST['name'] ?? '',
                $_POST['muscle_group'] ?? '',
                $_POST['description'] ?? ''
            );

            Session::flash('success', 'Exercise updated successfully.');
            $this->redirect('/exercises');
        } catch (InvalidArgumentException $e) {
            Session::flash('error', $e->getMessage());
            Session::flash('old', [
                'name' => $_POST['name'] ?? '',
                'muscle_group' => $_POST['muscle_group'] ?? '',
                'description' => $_POST['description'] ?? '',
            ]);
            $this->redirect('/exercises/edit?id=' . $id);
        } catch (Throwable $e) {
            Session::flash('error', 'Something went wrong while updating the exercise.');
            Session::flash('old', [
                'name' => $_POST['name'] ?? '',
                'muscle_group' => $_POST['muscle_group'] ?? '',
                'description' => $_POST['description'] ?? '',
            ]);
            $this->redirect('/exercises/edit?id=' . $id);
        }
    }

    public function delete(): void
    {
        $this->ensureAdmin();

        try {
            $this->exerciseService->deleteExercise((int) ($_POST['id'] ?? 0));
            Session::flash('success', 'Exercise deleted successfully.');
        } catch (InvalidArgumentException $e) {
            Session::flash('error', $e->getMessage());
        } catch (Throwable $e) {
            Session::flash('error', 'Something went wrong while deleting the exercise.');
        }

        $this->redirect('/exercises');
    }

    private function ensureAdmin(): void
    {
        $user = Session::get('user', []);

        if (($user['role'] ?? 'user') !== 'admin') {
            Session::flash('error', 'Only admins can manage exercises.');
            $this->redirect('/exercises');
        }
    }

    private function redirect(string $path): never
    {
        header('Location: ' . $path);
        exit;
    }
}
