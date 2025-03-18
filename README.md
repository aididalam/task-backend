## Task Backend - README


This repository provides a backend for managing tasks. It includes APIs for handling user authentication and CRUD operations for tasks. The backend is built using **PHP with the Lumen framework**, **MySQL** as the database, **JWT-based authentication**, and **WebSocket** for real-time communication.

Parent Repository: [https://github.com/aididalam/task-manager](https://github.com/aididalam/task-manager)


---

### Key Features:
- **JWT Authentication**: Secure authentication with JWT tokens.
- **Task Management**: CRUD (Create, Read, Update, Delete) operations for tasks.
- **Search & Filtering**: Search and filtering functionality for tasks.
- **WebSocket Integration**: Real-time updates via WebSocket when tasks are created, updated, or deleted.
- **Unit Tests**: Basic unit tests for API endpoints.

---

## Setup

1. **Clone the repository**

   ```bash
   git clone https://github.com/aididalam/task-backend
   cd task-backend
   ```

2. **Install Dependencies**

   Run the following command to install all dependencies:

   ```bash
   composer install
   ```

3. **Set up Environment Variables**

   Create a `.env` file and add the following variables:

   ```env
   APP_ENV=local
   APP_DEBUG=true
   APP_URL=http://localhost
   DB_CONNECTION=mysql
   DB_HOST=db
   DB_PORT=3306
   DB_DATABASE=taskmanager
   DB_USERNAME=taskmanager
   DB_PASSWORD=taskmanager
   JWT_SECRET=e5qmgYAsFLAH3rFHEL2YWtN427xGkUffSTrX1CCtN8Mzgav0WF2J09NwuXnI46Fm
   ```

4. **Run Migrations**

   Apply database migrations to create the necessary tables:

   ```bash
   php artisan migrate
   ```

5. **Run the Application**

   Start the PHP built-in server:

   ```bash
   php -S 0.0.0.0:8330 -t public server
   ```

6. **Run WebSocket Server**

   To start the WebSocket server, run the following command:

   ```bash
   php websocket.php
   ```

7. **Run Unit Tests**

   If you need to run unit tests, use the following command:

   ```bash
   vendor/bin/phpunit
   ```

---

## Routes and Authentication

The API has JWT-based authentication, and all task-related routes are protected by this authentication.

### Authentication Routes

| HTTP Method | Route        | Action         |
|-------------|--------------|----------------|
| `POST`      | `/login`     | User Login     |
| `POST`      | `/logout`    | User Logout    |
| `POST`      | `/register`  | User Registration |
| `POST`      | `/refresh`   | Refresh Token  |
| `GET`       | `/me`        | Get User Info  |

### Task Routes (Protected)

These routes require the user to be authenticated with JWT.

| HTTP Method | Route          | Action               |
|-------------|----------------|----------------------|
| `POST`      | `/tasks`       | Create a Task        |
| `GET`       | `/tasks`       | Get Tasks (with filters) |
| `PUT`       | `/tasks/{id}`  | Update a Task        |
| `DELETE`    | `/tasks/{id}`  | Delete a Task        |

---

## Task Model

Each task has the following fields:

| Field        | Type      | Description                     |
|--------------|-----------|---------------------------------|
| `id`         | Integer   | Unique Task Identifier (Auto-increment) |
| `user_id`    | Integer   | ID of the user who owns the task |
| `name`       | String    | Task Name (Required)            |
| `description`| String    | Task Description (Required)     |
| `status`     | Enum      | Task Status (`To Do`, `In Progress`, `Done`) |
| `due_date`   | Date      | Task Due Date (Required)        |

---

## TaskController

### Methods

1. **`index`** (GET `/tasks`)
   - Fetches tasks with optional filters.
   - Stores query parameters in the `task_query_params` table for persistent filtering.

2. **`store`** (POST `/tasks`)
   - Creates a new task with validation on `name`, `description`, `status`, and `due_date`.
   - Publishes a WebSocket message notifying of the task addition.

3. **`update`** (PUT `/tasks/{id}`)
   - Updates an existing task. Only the user who owns the task can update it.
   - Publishes a WebSocket message notifying of the task update.

4. **`destroy`** (DELETE `/tasks/{id}`)
   - Deletes a task by ID. Only the user who owns the task can delete it.
   - Publishes a WebSocket message notifying of the task deletion.

---

## WebSocket Integration

WebSocket functionality is integrated using the **Ratchet** library. Task changes (add, update, delete) trigger real-time notifications via WebSocket.

### WebSocket Notifications

- **Event Type:** `task_added`
  - Triggered when a new task is created.

- **Event Type:** `task_update`
  - Triggered when a task is updated.

- **Event Type:** `task_delete`
  - Triggered when a task is deleted.

WebSocket client is implemented in the `TaskController`. It connects to `ws://localhost:8080` and sends the message with task details.

---

## Authentication Controller (`AuthController`)

### Methods

1. **`login`** (POST `/login`)
   - Authenticates the user and returns a JWT token.
  
2. **`register`** (POST `/register`)
   - Registers a new user with email, password, and name.

3. **`me`** (GET `/me`)
   - Returns the details of the authenticated user.

4. **`logout`** (POST `/logout`)
   - Logs the user out and invalidates the JWT token.

5. **`refresh`** (POST `/refresh`)
   - Refreshes the user's JWT token if valid.

---

## Database Migrations

### 1. `2025_03_14_212824_create_users_table.php`

This migration creates the `users` table with fields for `name`, `email`, and `password`.

### 2. `2025_03_14_220229_create_tasks_table.php`

This migration creates the `tasks` table with the required fields for tasks (`user_id`, `name`, `description`, `status`, `due_date`).

### 3. `2025_03_16_085730_create_task_query_params_table.php`

This migration creates the `task_query_params` table to store user-specific query parameters for filtering tasks.

---

## Additional Notes

- **JWT Authentication**: The API uses JWT-based authentication. The token is returned upon successful login and must be included in the `Authorization` header of subsequent requests as `Bearer <token>`.
  
- **WebSocket Notifications**: Task updates, additions, and deletions will trigger WebSocket notifications to any connected clients. Ensure that the WebSocket server is running to receive real-time updates.

- **Error Handling**: If a task is not found when updating or deleting, a 404 error is returned. Unauthorized actions return a 401 error.

---

## Conclusion

This API allows users to manage their tasks effectively with JWT-based authentication and WebSocket integration for real-time notifications. Use the provided methods to authenticate, create, update, delete, and retrieve tasks with various filtering options.

