<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/ReportController.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método HTTP no permitido. Solo POST es aceptado.');
    }

    $database = new Database();
    $db = $database->getConnection();

    $controller = new ReportController($db);

    $filtro = $_POST['filtro'] ?? '';
    $valor = $_POST['valor'] ?? '';
    $formato = $_POST['formato'] ?? '';

    // Validación de entrada
    if (empty($filtro) || empty($valor) || empty($formato)) {
        throw new Exception('Filtro, valor y formato son obligatorios.');
    }

    if (!in_array($filtro, ['producto', 'proveedor', 'vencimiento'])) {
        throw new Exception('Filtro inválido. Debe ser producto, proveedor o vencimiento.');
    }

    if (!in_array(strtolower($formato), ['pdf', 'excel', 'csv'])) {
        throw new Exception('Formato inválido. Debe ser pdf, excel o csv.');
    }

    $filename = $controller->generateReport($filtro, $valor, $formato);

    echo json_encode([
        'success' => true,
        'message' => 'Reporte generado con éxito.',
        'reporte' => $filename
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Error al generar reporte.',
        'details' => $e->getMessage()
    ]);
}
