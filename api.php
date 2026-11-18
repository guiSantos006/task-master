<?php
// api.php - O PHP agora é apenas um Provedor de Dados (API)
require_once __DIR__ . '/src/model/Task.php';

// Configuramos o cabeçalho para que o navegador entenda que estamos enviando JSON
header('Content-Type: application/json');

$pdo = new PDO('sqlite:' . __DIR__ . '/tasks.sqlite');
$model = new Task($pdo);

$action = $_GET['action'] ?? 'list';

try {
    switch ($action) {
        case 'list':
            // Retorna a lista de tarefas formatada em JSON
            echo json_encode($model->getAll());
            break;

        case 'create':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!is_array($data)) {
                throw new Exception('Dados da tarefa inválidos.');
            }
            $model->save(
                $data['title'] ?? '',
                $data['description'] ?? '',
                $data['due_date'] ?? ''
            );
            echo json_encode(['status' => 'success']);
            break;

        case 'update':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!is_array($data) || empty($data['id'])) {
                throw new Exception('Dados da tarefa inválidos.');
            }
            $model->update(
                $data['id'],
                $data['title'] ?? '',
                $data['description'] ?? '',
                $data['due_date'] ?? ''
            );
            echo json_encode(['status' => 'success']);
            break;

        case 'complete':
            $model->complete($_GET['id']);
            echo json_encode(['status' => 'success']);
            break;

        case 'delete':
            $model->delete($_GET['id']);
            echo json_encode(['status' => 'success']);
            break;

        default:
            http_response_code(404);
            echo json_encode(['error' => 'Ação desconhecida.']);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}