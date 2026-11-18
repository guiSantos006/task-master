<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../model/Task.php';

class TaskModelTest extends TestCase {
    public function testNaoPodeSalvarTarefaSemTitulo() {
        $pdoMock = $this->createMock(PDO::class); // Fingimos que o banco existe
        $model = new Task($pdoMock);
       
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Título e Data são obrigatórios.");
       
        $model->save("", "Fazer compras", "2026-12-31"); // Deve disparar exceção
    }

    public function testCrudFuncionaComIdsDoSchemaSqliteLegado() {
        $pdo = new PDO('sqlite::memory:');
        $pdo->exec(
            'CREATE TABLE tasks (id INT AUTO_INCREMENT PRIMARY KEY, title TEXT NOT NULL, ' .
            'description TEXT, due_date TEXT NOT NULL, done INTEGER DEFAULT 0)'
        );
        $pdo->exec(
            "INSERT INTO tasks (title, description, due_date) VALUES ('Tarefa antiga', '', '2026-12-31')"
        );
        $model = new Task($pdo);

        $oldTask = $model->getAll()[0];
        $this->assertNotEmpty($oldTask['id']);
        $model->save('Nova tarefa', '', '2027-01-01');
        $newTask = $model->getAll()[0];

        $model->update($newTask['id'], 'Tarefa editada', '', '2027-01-02');
        $model->complete($newTask['id']);
        $this->assertSame('Tarefa editada', $model->getAll()[0]['title']);
        $this->assertSame(1, (int) $model->getAll()[0]['done']);

        $model->delete($newTask['id']);
        $this->assertCount(1, $model->getAll());
    }
}
?>