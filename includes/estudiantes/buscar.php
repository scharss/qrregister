<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

// Verificar que el usuario está autenticado y es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Obtener y validar el documento
$documento = htmlspecialchars(trim($_GET['documento'] ?? ''), ENT_QUOTES, 'UTF-8');
if (empty($documento)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Documento no válido']);
    exit;
}

$db = new Database();
$conn = $db->connect();

try {
    $stmt = $conn->prepare("
        SELECT e.*, g.nombre as grupo_nombre 
        FROM estudiantes e 
        LEFT JOIN grupos g ON e.grupo_id = g.id AND g.activo = 1
        WHERE e.documento = ? AND e.activo = 1
    ");
    $stmt->execute([$documento]);
    $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$estudiante) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Estudiante no encontrado']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'estudiante' => $estudiante
    ]);

} catch (PDOException $e) {
    error_log("Error al buscar estudiante: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al buscar el estudiante'
    ]);
} 