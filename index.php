<?php
// Включаем отображение ошибок для разработки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'TaskController.php';

// Простейшая маршрутизация по URI и методу запроса
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Если запрос к корню, отдаём HTML-интерфейс
if ($uri === '/' || $uri === '/index.php') {
    readfile('frontend.html');
    exit;
}

// Разбор пути для API (/tasks, /tasks/{id})
$path = explode('/', trim($uri, '/'));
if ($path[0] !== 'tasks') {
    http_response_code(404);
    echo json_encode(['error' => 'Not Found']);
    exit;
}

$controller = new TaskController();

// Определяем ID, если передан
$id = isset($path[1]) ? (int)$path[1] : null;

// Маршрутизация по методам
switch ($method) {
    case 'GET':
        if ($id) {
            $controller->show($id);
        } else {
            $controller->index();
        }
        break;
    case 'POST':
        $controller->store();
        break;
    case 'PUT':
        if ($id) {
            $controller->update($id);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'ID required for update']);
        }
        break;
    case 'DELETE':
        if ($id) {
            $controller->destroy($id);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'ID required for delete']);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}