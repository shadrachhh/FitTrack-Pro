# FitTrack Pro

## Authentic Use Case

FitTrack Pro is designed for gym members who want one place to log workouts, review exercise history, and track recent activity. The main use case is a user who trains several times per week and wants to store workout dates, exercises, sets, reps, and weight in a structured application instead of scattered notes. The admin role supports a trainer or gym staff member who manages the shared exercise library for all users.

This project goes beyond the lecture examples by combining:

- a PHP MVC backend
- a JWT-protected REST API
- a Vue single-page frontend
- role-based authorization
- workout filtering and pagination

## Project Architecture

### Backend

The backend is built with plain PHP using MVC-style structure:

- `Controllers`
- `Services`
- `Repositories`
- `Framework`
- `Views`

It handles:

- authentication
- workout management
- exercise management
- dashboard data
- API responses for the Vue frontend

### Frontend

The frontend is built with:

- Vue 3
- Vue Router
- Vite
- Bootstrap 5

It uses the backend API instead of directly rendering PHP views.

## AI Disclosure Statement

AI tools were used as a development assistant during this project for:

- brainstorming structure improvements
- checking integration issues between the PHP API and the Vue frontend
- improving README documentation
- reviewing code for bugs and missing rubric requirements

All generated suggestions were manually reviewed, adjusted, and tested before being kept in the project. The final code structure, feature choices, debugging decisions, and explanation of the application remain my own responsibility. I understand the codebase and can explain the routing, service layer, repositories, JWT authentication, and frontend API integration.

## Features

### User Features

- Register and log in through the API
- Token-based frontend authentication
- View all available exercises
- Create workouts with multiple exercise entries
- View workout history with filters and pagination
- See dashboard statistics and recent workouts in Vue

### Admin Features

- Create exercises from the Vue frontend
- Edit exercises from the Vue frontend
- Delete exercises when they are not already used in workouts

## User Roles

The application supports two roles through the `users.role` column:

- `user`
  A normal user can view exercises, create workouts, and view dashboard information.

- `admin`
  An admin can do everything a normal user can do, plus create, edit, and delete exercises.

## Technologies Used

- PHP 8.2
- MySQL 8
- Apache
- PDO
- Vue 3
- Vue Router
- Vite
- Bootstrap 5
- Docker
- Docker Compose

## Installation Instructions

### 1. Clone the project

```bash
git clone <your-repository-url>
cd Fittrack-pro
```

### 2. Start the backend containers

```bash
docker compose up --build
```

This starts:

- the PHP backend on `http://localhost:8000`
- phpMyAdmin on `http://localhost:8080`
- MySQL on port `3307`

### 3. Install the Vue frontend

```bash
cd frontend
npm install
```

### 4. Run the Vue frontend in development

```bash
npm run dev
```

By default Vite runs on:

`http://localhost:5173/spa/`

The dev server proxies `/api` requests to the PHP backend at `http://localhost:8000`.

### 5. Build the Vue frontend for Docker/Apache

```bash
cd frontend
npm run build
```

The built files are written to `frontend/dist` and served by Apache at:

`http://localhost:8000/spa/`

### 6. Log in or register

Create a new account from the Vue frontend or from the PHP pages, then log in with the same backend user data.

## Database Setup

The project uses MySQL through Docker and expects the following tables:

- `users`
- `exercises`
- `workouts`
- `workout_entries`

### Required user role column

The `users` table should include a `role` column so the application can distinguish admins from normal users.

Example:

```sql
ALTER TABLE users
ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'user';
```

### Tables used by the application

```sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(100),
  password VARCHAR(255),
  role VARCHAR(20) NOT NULL DEFAULT 'user',
  created_at TIMESTAMP
);

CREATE TABLE exercises (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  muscle_group VARCHAR(100),
  description TEXT
);

CREATE TABLE workouts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  workout_date DATE,
  created_at TIMESTAMP
);

CREATE TABLE workout_entries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  workout_id INT,
  exercise_id INT,
  sets INT,
  reps INT,
  weight DECIMAL(5,2)
);
```

## Default Docker Access

- PHP app URL: `http://localhost:8000`
- Vue SPA URL: `http://localhost:8000/spa/`
- phpMyAdmin URL: `http://localhost:8080`
- MySQL port: `3307`

## Frontend API Configuration

The Vue frontend reads the backend API base URL from `VITE_API_BASE_URL`.

Example local override in `frontend/.env`:

```env
VITE_API_BASE_URL=http://localhost:8000
```

If `VITE_API_BASE_URL` is left empty, the frontend uses relative `/api/...` paths. This works for:

- Apache/Docker deployment at `http://localhost:8000/spa/`
- local Vite development because `vite.config.js` proxies `/api` to `http://localhost:8000`

## REST API Notes

The backend API follows REST-style naming and supports JSON error responses.

- `GET` endpoints support filtering and pagination
- `POST` endpoints return the created object
- protected endpoints require `Authorization: Bearer <token>`
- admin-only actions are enforced in the backend, not only in the frontend

## Project Structure

```text
Fittrack-pro/
|-- backend/
|   |-- public/
|   |   |-- .htaccess
|   |   |-- app.php
|   |   `-- index.php
|   |-- src/
|   |   |-- Controllers/
|   |   |-- Framework/
|   |   |-- Models/
|   |   |-- Repositories/
|   |   |-- Services/
|   |   `-- Views/
|   |-- apache.conf
|   `-- Dockerfile
|-- frontend/
|   |-- src/
|   |-- public/
|   |-- package.json
|   `-- vite.config.js
|-- docker-compose.yml
`-- README.md
```

### Folder Explanation

- `Controllers`
  Handles requests, calls services, and returns views or redirects.

- `Services`
  Contains validation and business logic.

- `Repositories`
  Contains SQL queries and database operations using PDO.

- `Views`
  Contains Bootstrap-based user interface files.

- `Framework`
  Contains shared core utilities such as `Database`, `Session`, `View`, and the base `Repository`.

- `Models`
  Contains model classes such as `User`.

## API Endpoints

The Vue frontend uses these backend endpoints:

- `POST /api/register`
- `POST /api/login`
- `GET /api/exercises`
- `POST /api/exercises`
- `PUT /api/exercises/{id}`
- `DELETE /api/exercises/{id}`
- `GET /api/workouts`
- `POST /api/workouts`

Example supported query parameters:

- `GET /api/exercises?search=press&page=1&per_page=10`
- `GET /api/workouts?workout_date=2026-04-01&exercise_id=3&page=1&per_page=5`

## Application Modules

### Authentication

- User registration
- User login
- Password hashing
- JWT token generation for API access
- role-based authorization in protected backend routes

### Dashboard

- Total workout count
- Recent workouts
- Quick action buttons

### Exercise Management

- View all exercises
- Admin-only create form
- Admin-only edit form
- Admin-only delete action

### Workouts

- Create a workout by date
- Add multiple entries to a workout
- Store sets, reps, and weight
- View workouts grouped with all related exercise rows

## MVC Flow

The project follows this flow:

`Controller -> Service -> Repository -> Database`

Example:

1. A request goes to a controller.
2. The controller sends data to a service.
3. The service validates and applies business rules.
4. The repository runs SQL through PDO.
5. The controller returns a view or redirects.


## Manual Testing Guide

### 1. Register a normal user

Vue frontend:

`http://localhost:8000/spa/`

or PHP page:

`http://localhost:8000/register`

Create a standard account.

### 2. Create an admin account

Update the `role` field for a user in phpMyAdmin:

```sql
UPDATE users SET role = 'admin' WHERE email = 'admin@example.com';
```

### 3. Log in as admin

Check that the exercise section allows:

- create
- edit
- delete

### 4. Add exercises

Examples:

- Bench Press
- Squat
- Deadlift
- Pull Up

### 5. Log a workout

Go to:

`http://localhost:8000/spa/#/workouts/create`

Select exercises and enter:

- sets
- reps
- weight

### 6. View workout history

Go to:

`http://localhost:8000/spa/#/workouts`

Check that:

- workouts are listed clearly
- date and exercise filters work
- pagination buttons load the next page correctly

### 7. Check the dashboard

Go to:

`http://localhost:8000/spa/#/dashboard`

 it shows:

- total workouts
- recent workouts
- quick action buttons

## Author

Name: Terkuma Uker
student number: 714168
Inholland University of applied sciences 
