<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/ReportController.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $db = $database->getConnection();
    $controller = new ReportController($db);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método HTTP no permitido. Solo POST es aceptado.');
    }

    if (!isset($_POST['filtro']) || !in_array($_POST['filtro'], ['producto', 'proveedor', 'vencimiento'])) {
        $filters = $controller->getFilters();
        echo json_encode(['success' => true, 'data' => $filters]);
    } else {
        $filtro = $_POST['filtro'];
        $values = $controller->getFilterValues($filtro);
        echo json_encode(['success' => true, 'data' => $values]);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar la solicitud.',
        'details' => $e->getMessage()
    ]);
}
