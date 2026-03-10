<?php
require_once 'Task.php';

class TaskController
{
    // Валидация входных данных
    private function validate($data, $isUpdate = false)
    {
        $errors = [];
        if (!$isUpdate || isset($data['title'])) {
            if (empty(trim($data['title'] ?? ''))) {
                $errors['title'] = 'Title cannot be empty';
            }
        }
        return $errors;
    }

    // GET /tasks
    public function index()
    {
        header('Content-Type: application/json');
        echo json_encode(Task::all());
    }

    // GET /tasks/{id}
    public function show($id)
    {
        header('Content-Type: application/json');
        $task = Task::find($id);
        if ($task) {
            echo json_encode($task);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Task not found']);
        }
    }

    // POST /tasks
    public function store()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true) ?: [];

        $errors = $this->validate($data);
        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['errors' => $errors]);
            return;
        }

        $id = Task::create($data);
        http_response_code(201);
        echo json_encode(['message' => 'Task created', 'id' => $id]);
    }

    // PUT /tasks/{id}
    public function update($id)
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true) ?: [];

        // Проверяем существование задачи
        if (!Task::find($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'Task not found']);
            return;
        }

        $errors = $this->validate($data, true);
        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['errors' => $errors]);
            return;
        }

        if (Task::update($id, $data)) {
            echo json_encode(['message' => 'Task updated']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'No data to update']);
        }
    }

    // DELETE /tasks/{id}
    public function destroy($id)
    {
        header('Content-Type: application/json');
        if (!Task::find($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'Task not found']);
            return;
        }

        Task::delete($id);
        echo json_encode(['message' => 'Task deleted']);
    }
}