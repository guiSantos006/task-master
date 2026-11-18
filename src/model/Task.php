<?php
class Task {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // Busca todas as tarefas
    public function getAll() {
        // rowid also gives existing SQLite rows an identifier when the legacy id
        // column was declared as INT AUTO_INCREMENT instead of INTEGER PRIMARY KEY.
        $stmt = $this->pdo->query("SELECT rowid AS id, title, description, due_date, done FROM tasks ORDER BY rowid DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Salva uma nova tarefa (agora com os campos do desafio!)
    public function save($title, $description, $dueDate) {
        if (empty(trim($title)) || empty(trim($dueDate))) {
            throw new Exception("Título e Data são obrigatórios.");
        }
       
        $stmt = $this->pdo->prepare("INSERT INTO tasks (title, description, due_date) VALUES (:title, :description, :due_date)");
        $stmt->bindValue(':title', $title);
        $stmt->bindValue(':description', $description);
        $stmt->bindValue(':due_date', $dueDate);
        return $stmt->execute();
    }

    public function complete($id) {
        $stmt = $this->pdo->prepare("UPDATE tasks SET done = 1 WHERE rowid = :id");
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function update($id, $title, $description, $dueDate) {
        if (empty(trim($title)) || empty(trim($dueDate))) {
            throw new Exception("Título e Data são obrigatórios.");
        }

        $stmt = $this->pdo->prepare(
            "UPDATE tasks SET title = :title, description = :description, due_date = :due_date WHERE rowid = :id"
        );
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->bindValue(':title', $title);
        $stmt->bindValue(':description', $description);
        $stmt->bindValue(':due_date', $dueDate);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM tasks WHERE rowid = :id");
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>