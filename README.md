# FitTrack Pro

## Project Title

FitTrack Pro - Workout Tracking Web Application

## Description

FitTrack Pro is a fitness tracking web application built for the Web Development 2 assignment. The application allows users to register, log in, browse exercises, log workouts, and review training history. It also includes an admin role for managing the shared exercise library.

The project combines:

- a server-rendered PHP MVC application
- a small Vue single-page application
- a MySQL database
- Docker for local development
- Bootstrap for styling

The main goal of the application is to help users keep a clear record of their workouts, including which exercises they performed, how many sets and reps they completed, and how much weight they used.

## Main Features

### Authentication

- User registration
- User login
- User logout
- Session-based authentication for the PHP MVC interface
- JWT-based authentication for the API and Vue SPA

### Dashboard

- Total workouts overview
- Recent workouts overview
- Quick action buttons
- Role-aware navigation

### Exercise Management

- View all exercises
- Create exercises
- Edit exercises
- Delete exercises
- Admin-only protection for exercise management actions

### Workout Tracking

- Create workouts by date
- Add multiple workout entries to one workout
- Store sets, reps, and weight per exercise
- View workouts grouped with related exercise entries
- Filter workout history by date and exercise

### Frontend and API

- Vue-based SPA with multiple components
- Vue Router navigation
- Shared frontend state using a reactive store
- REST-style API endpoints with JSON responses
- Error messages returned when API requests fail

## User Roles

The system supports two user roles using the `users.role` column.

### User

A normal user can:

- register and log in
- view exercises
- create workouts
- view workout history
- use the dashboard

### Admin

An admin can do everything a normal user can do, plus:

- create exercises
- edit exercises
- delete exercises

## Technologies Used

- PHP 8.2
- MySQL 8
- PDO
- Apache
- Docker
- Docker Compose
- Bootstrap 5
- Vue 3
- Vue Router 4
- JWT

## Installation Instructions

### 1. Clone the project

```bash
git clone <your-repository-url>
cd Fittrack-pro
```

### 2. Start Docker containers

```bash
docker compose up --build
```

### 3. Open the project

Server-rendered PHP app:

`http://localhost:8000`

Vue SPA:

`http://localhost:8000/spa/index.html`

phpMyAdmin:

`http://localhost:8080`

### 4. Stop the containers

```bash
docker compose down
```

## Database Setup

The application uses MySQL and expects the following tables:

- `users`
- `exercises`
- `workouts`
- `workout_entries`

### Required Role Column

The `users` table must include a `role` column.

```sql
ALTER TABLE users
ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'user';
```

### Full Table Structure

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

## Default Access Information

- PHP application: `http://localhost:8000`
- Vue SPA: `http://localhost:8000/spa/index.html`
- phpMyAdmin: `http://localhost:8080`
- MySQL port: `3307`
- Database name: `fittrack`
- Database host inside Docker: `db`
- MySQL username: `root`
- MySQL password: `root`

## Demo Login Accounts

Use the following accounts to test the application.

### Admin Account

- Name: `Terkuma Uker`
- Email: `terkumauker50@gmail.com`
- Password: `08060636202`
- Role: `admin`

### Normal User Account

- Name: `Romeny Leito`
- Email: `romeny.leito@hotmail.com`
- Password: `08060636202`
- Role: `user`

## Database Export Note

- The SQL database export should be included in the project root before final submission.
- The exported database file is included in the project root as `fittrack.sql`.

## Project Structure

```text
Fittrack-pro/
|-- backend/
|   |-- public/
|   |   |-- .htaccess
|   |   |-- app.php
|   |   |-- index.php
|   |   `-- spa/
|   |       |-- index.html
|   |       `-- app.js
|   |-- src/
|   |   |-- Controllers/
|   |   |   `-- Api/
|   |   |-- Framework/
|   |   |-- Models/
|   |   |-- Repositories/
|   |   |-- Services/
|   |   `-- Views/
|   |-- apache.conf
|   `-- Dockerfile
|-- docker-compose.yml
|-- fittrack.sql
`-- README.md
```

## Folder Explanation

### `Controllers`

Handles incoming requests and decides which service or view should be used.

### `Controllers/Api`

Contains API controllers for JSON-based endpoints.

### `Services`

Contains application logic and validation rules.

### `Repositories`

Contains PDO-based database queries.

### `Views`

Contains Bootstrap-styled PHP templates for the server-rendered interface.

### `Framework`

Contains shared infrastructure such as:

- `Database`
- `Session`
- `View`
- `Repository`
- `ApiResponse`
- `JwtHelper`

### `Models`

Contains application models such as `User`.

## Backend Architecture

The backend follows MVC and a service layer pattern.

Request flow:

`Controller -> Service -> Repository -> Database`

This means:

- controllers handle request/response logic
- services contain business rules and validation
- repositories contain SQL and database access
- views only display data

## Server-Rendered Routes

### Authentication

- `GET /login`
- `POST /login`
- `GET /register`
- `POST /register`
- `POST /logout`

### Dashboard

- `GET /dashboard`

### Exercises

- `GET /exercises`
- `GET /exercises/create`
- `POST /exercises/create`
- `GET /exercises/edit?id={id}`
- `POST /exercises/edit?id={id}`
- `POST /exercises/delete`

### Workouts

- `GET /workouts`
- `GET /workouts/create`
- `POST /workouts/create`

## API Endpoints

### Authentication API

- `POST /api/register`
- `POST /api/login`

### Exercise API

- `GET /api/exercises`
- `POST /api/exercises`
- `PUT /api/exercises/{id}`
- `DELETE /api/exercises/{id}`

### Workout API

- `GET /api/workouts`
- `POST /api/workouts`

### API Notes

- API responses are JSON
- authentication uses JWT tokens
- protected endpoints require `Authorization: Bearer <token>`
- admin-only exercise changes are checked in the backend
- workout listing supports filtering by date and exercise

## Vue SPA

The Vue SPA is included to support the rubric requirements around frontend components, routing, and state management.

The SPA does not need a separate top-level `frontend/` folder for this assignment because it is served directly from the PHP application under `backend/public/spa`. This keeps the project simple while still demonstrating:

- Vue components
- frontend routing
- shared frontend state
- API integration with JWT authentication

### Included SPA Views

- Login view
- Dashboard view
- Exercises view
- Workouts view
- Workout create view

### Frontend Features

- Vue Router for navigation
- shared reactive store for token, user, exercises, and workouts
- API integration with JWT
- SPA workout creation connected to the backend API
- protected SPA routes with login redirects
- SPA dashboard metrics for workouts, entries, and volume
- exercise search inside the SPA
- Bootstrap styling

## Styling and UI

The application uses Bootstrap 5 for styling.

UI improvements included:

- consistent navbar
- cards for dashboard and content sections
- responsive layout
- clearer spacing and alignment
- role-aware labels such as `Manage Exercises`
- improved SPA login and register screens
- improved SPA workout summaries and filters
- stronger SPA presentation for demo and grading

## Recent Corrections

The following corrections were made while finalizing the project:

- fixed SPA authentication so API requests can use JWT tokens properly
- added backend fallback handling for the `Authorization` header in Apache/PHP
- completed the SPA workout creation flow
- completed SPA login, register, dashboard, exercises, and workout history flow
- updated the README to reflect the final project structure and final feature set

## Manual Testing Guide

### 1. Register a normal user

Open:

`http://localhost:8000/register`

Create a standard user account.

### 2. Make a user admin

Open phpMyAdmin and run:

```sql
UPDATE users SET role = 'admin' WHERE email = 'admin@example.com';
```

### 3. Log in as admin

Check that you can:

- create exercises
- edit exercises
- delete exercises

### 4. Add exercises

Examples:

- Bench Press
- Squat
- Deadlift
- Shoulder Press

### 5. Create workouts

Open:

`http://localhost:8000/workouts/create`

Add a date and workout entries with:

- exercise
- sets
- reps
- weight

### 6. View workout history

Open:

`http://localhost:8000/workouts`

Confirm that workouts are shown clearly and grouped correctly.

### 7. Test workout filters

Filter by:

- date
- exercise

### 8. Test dashboard

Open:

`http://localhost:8000/dashboard`

Confirm that it shows:

- total workouts
- recent workouts
- quick actions

### 9. Test the SPA

Open:

`http://localhost:8000/spa`

Check that:

- login works through the API
- dashboard loads
- exercises load
- workouts load
- workout creation works after login
- router navigation works

## Recommended Demo Flow

If you need to demonstrate the project during review or submission, this is a good order:

1. Show the login and register pages
2. Log in as a normal user and show the dashboard
3. Show workout creation and workout history
4. Show filtering by date and exercise
5. Log in as an admin and show exercise management
6. Open the Vue SPA and demonstrate login, routing, and API-backed views
7. Briefly explain the MVC flow and the API flow

## Submission Checklist

Before submitting, make sure the following are true:

- Docker containers start correctly with `docker compose up --build`
- the `users` table contains a `role` column
- at least one admin user exists for demonstrating exercise management
- the PHP MVC interface works at `http://localhost:8000`
- the Vue SPA works at `http://localhost:8000/spa`
- login, register, logout, workouts, and exercise management all function
- API routes respond with JSON
- the SQL export file is included in the project root beside `README.md`
- the README is included in the project root
- you can explain the code structure in your own words

## Screenshots

You can add screenshots here before final submission.

Suggested screenshots:

- Login page
- Register page
- Dashboard
- Exercise list
- Exercise edit page
- Workout create page
- Workout history page
- Vue SPA dashboard
## Rubric-Relevant Summary

This project includes the key elements needed for rubric categories:

- CSS framework usage through Bootstrap
- structured frontend components in Vue
- frontend routing with Vue Router
- frontend state management with a reactive store
- working backend routes and API endpoints
- GET, POST, PUT, and DELETE API support
- JWT authentication for the API
- role-based authorization in the backend
- MVC, routing, namespaces, and autoloading in the backend

## Notes for Submission

This project is intentionally simple, readable, and explainable. It does not try to over-engineer the assignment. The main focus is on meeting the course requirements with a clean structure and working functionality.

Strong points of the submission:

- clear use case
- clean MVC backend
- repository and service separation
- role-based access control
- Vue frontend with multiple views
- JSON API with JWT
- Docker-based setup
- Bootstrap styling
## Author

Terkuma Uker
Inholland University of applied sciences 
