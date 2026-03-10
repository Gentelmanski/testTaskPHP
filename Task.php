<?php
class Task
{
    private static $pdo = null;

    // Получение соединения с БД (создание таблицы при первом обращении)
    private static function getDB()
    {
        if (self::$pdo === null) {
            $dbFile = __DIR__ . '/database.sqlite';
            $exists = file_exists($dbFile);
            self::$pdo = new PDO('sqlite:' . $dbFile);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            if (!$exists) {
                // Создаём таблицу tasks
                self::$pdo->exec("
                    CREATE TABLE tasks (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        title TEXT NOT NULL,
                        description TEXT,
                        status TEXT DEFAULT 'pending',
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                    )
                ");
            }
        }
        return self::$pdo;
    }

    // Получить все задачи
    public static function all()
    {
        $stmt = self::getDB()->query("SELECT * FROM tasks ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Найти задачу по id
    public static function find($id)
    {
        $stmt = self::getDB()->prepare("SELECT * FROM tasks WHERE id = ?");
        $stmt->execute([$id]);
        $task = $stmt->fetch(PDO::FETCH_ASSOC);
        return $task ?: null;
    }

    // Создать задачу
    public static function create($data)
    {
        $db = self::getDB();
        $stmt = $db->prepare("INSERT INTO tasks (title, description, status) VALUES (?, ?, ?)");
        $stmt->execute([
            $data['title'],
            $data['description'] ?? null,
            $data['status'] ?? 'pending'
        ]);
        return $db->lastInsertId();
    }

    // Обновить задачу
    public static function update($id, $data)
    {
        $fields = [];
        $params = [];

        if (isset($data['title'])) {
            $fields[] = "title = ?";
            $params[] = $data['title'];
        }
        if (isset($data['description'])) {
            $fields[] = "description = ?";
            $params[] = $data['description'];
        }
        if (isset($data['status'])) {
            $fields[] = "status = ?";
            $params[] = $data['status'];
        }

        if (empty($fields)) {
            return false;
        }

        $fields[] = "updated_at = CURRENT_TIMESTAMP";
        $sql = "UPDATE tasks SET " . implode(', ', $fields) . " WHERE id = ?";
        $params[] = $id;

        $stmt = self::getDB()->prepare($sql);
        return $stmt->execute($params);
    }

    // Удалить задачу
    public static function delete($id)
    {
        $stmt = self::getDB()->prepare("DELETE FROM tasks WHERE id = ?");
        return $stmt->execute([$id]);
    }
}